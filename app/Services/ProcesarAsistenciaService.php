<?php

namespace App\Services;

use App\Models\Empleado;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProcesarAsistenciaService
{
    protected string $horaRegex = '/\\b(2[0-3]|[01]?\\d):([0-5]\\d)\\b/u';
    protected string $horaEntradaLimite = '08:45:00'; 
    protected bool $persistRegistros = true;
    protected array $collectedRegistros = [];

    public function process(string $path, bool $persist = true, callable $onSheetProgress = null): array
    {
        $this->persistRegistros = $persist;
        $this->collectedRegistros = [];

        Log::info("Iniciando procesamiento de archivo: $path");

        try {
            // Detección inteligente del tipo de archivo
            $inputFileType = IOFactory::identify($path);
            $reader = IOFactory::createReader($inputFileType);
            
            // Si es CSV, configurar delimitadores comunes si falla el default
            if ($inputFileType === 'Csv') {
                /** @var Csv $reader */
                $reader->setDelimiter(',');
                $reader->setEnclosure('"');
                $reader->setSheetIndex(0);
            }

            // Cargar archivo (sin solo-lectura para mejor compatibilidad de fechas)
            $spreadsheet = $reader->load($path);
        } catch (\Exception $e) {
            Log::error("Error crítico cargando archivo: " . $e->getMessage());
            throw new \Exception("Error leyendo el archivo. Asegúrate de que no esté corrupto. Detalles: " . $e->getMessage());
        }

        $sheets = $spreadsheet->getAllSheets();
        $totalRegistros = 0;
        $empleadosProcesados = 0;
        $periodoGlobal = null;

        foreach ($sheets as $index => $sheet) {
            $sheetTitle = $sheet->getTitle();
            Log::info("Analizando hoja: {$sheetTitle}");

            // 1. Detectar Periodo (Buscamos en las primeras 50 filas)
            $periodo = $this->parsePeriodo($sheet) ?? $periodoGlobal;
            if ($periodo && !$periodoGlobal) {
                $periodoGlobal = $periodo;
                Log::info("Periodo global detectado: " . $periodo['inicio']->toDateString() . " - " . $periodo['fin']->toDateString());
            }

            // 2. Mapear días (1.0, 2.0...)
            $dayColumns = $this->mapDayColumns($sheet);
            
            if (!$periodo || empty($dayColumns)) {
                Log::warning("Saltando hoja '{$sheetTitle}': No se encontró periodo o columnas de días válidas.");
                continue;
            }

            $highestRow = $sheet->getHighestDataRow();
            $empleadoActual = null;

            for ($row = 1; $row <= $highestRow; $row++) {
                $rowValues = $this->getRowValues($sheet, $row);

                // A. Buscar cabecera de empleado
                if ($this->isEmpleadoHeader($rowValues)) {
                    $nuevoId = $this->extraerValorPosterior($rowValues, ['No', 'Num', 'ID', 'Empleado']);
                    $nuevoNombre = $this->extraerValorPosterior($rowValues, ['Nombre', 'Name']);

                    // Solo cambiamos si encontramos un ID válido
                    if (!empty($nuevoId)) {
                        if ($empleadoActual) $empleadosProcesados++;
                        $empleadoActual = ['no' => $nuevoId, 'nombre' => $nuevoNombre];
                    }
                    continue;
                }

                // B. Buscar nombre si viene separado (y ya tenemos ID pero no nombre)
                if ($empleadoActual && empty($empleadoActual['nombre']) && $this->isNombreHeader($rowValues)) {
                    $empleadoActual['nombre'] = $this->extraerValorPosterior($rowValues, ['Nombre', 'Name']);
                    continue;
                }

                // C. Procesar Checadas
                if ($empleadoActual && $this->filaTieneChecadas($rowValues)) {
                    $totalRegistros += $this->procesarFilaDeChecadas($rowValues, $dayColumns, $periodo, $empleadoActual);
                }
            }
            if ($empleadoActual) $empleadosProcesados++; // Contar el último

            if ($onSheetProgress) {
                $onSheetProgress(['total' => count($sheets), 'indice' => $index + 1]);
            }
        }

        return [
            'total_registros' => $totalRegistros,
            'empleados_procesados' => $empleadosProcesados,
            'periodo' => $periodoGlobal,
            'registros' => $this->collectedRegistros
        ];
    }

    // --- Lógica Principal de Procesamiento ---

    protected function procesarFilaDeChecadas(array $rowValues, array $dayColumns, array $periodo, array $empleado): int
    {
        $count = 0;
        foreach ($dayColumns as $day => $colIdx) {
            $raw = $rowValues[$colIdx] ?? '';
            // Limpieza básica: si está vacío o no parece hora, saltar
            if (empty($raw) || !preg_match($this->horaRegex, $raw)) continue;

            $horas = $this->extraerHoras($raw);
            if (count($horas) < 1) continue;

            $fecha = $this->construirFecha($periodo, $day);
            
            // Buscar ID de empleado en DB (solo si persiste)
            $empleadoId = $this->persistRegistros ? $this->buscarEmpleadoId($empleado['no'], $empleado['nombre']) : null;
            
            // Pares Entrada/Salida
            // Asumimos paridad: Entrada -> Salida -> Entrada -> Salida
            for ($i = 0; $i < count($horas); $i += 2) {
                $entrada = $horas[$i];
                $salida = $horas[$i+1] ?? null;

                // Regla de Retardo 8:45
                $esRetardo = false;
                if ($entrada) {
                    try {
                        $esRetardo = Carbon::parse($entrada)->format('H:i:s') > $this->horaEntradaLimite;
                    } catch (\Exception $e) {}
                }

                if ($this->persistRegistros) {
                    // PROTECCIÓN DE DATOS: Verificar si ya existe el registro y si fue modificado manualmente
                    $existing = DB::table('asistencias')
                        ->where('empleado_no', $empleado['no'])
                        ->where('fecha', $fecha->toDateString())
                        // Usamos entrada como parte de la llave única lógica del turno
                        ->where('entrada', $entrada) 
                        ->first();

                    // Si ya existe y fue modificado manualmente (ej: tipo vacaciones), NO LO TOCAMOS.
                    if ($existing && $existing->tipo_registro !== 'asistencia') {
                        continue; 
                    }

                    // Guardar o Actualizar
                    DB::table('asistencias')->updateOrInsert(
                        [
                            'empleado_no' => $empleado['no'],
                            'fecha' => $fecha->toDateString(),
                            'entrada' => $entrada, 
                        ],
                        [
                            'nombre' => $empleado['nombre'] ?? 'Desconocido',
                            'salida' => $salida,
                            'checadas' => json_encode($horas),
                            'empleado_id' => $empleadoId,
                            'tipo_registro' => 'asistencia', // Default
                            'es_retardo' => $esRetardo,
                            'updated_at' => now(),
                        ]
                    );
                } else {
                    $this->collectedRegistros[] = compact('empleado', 'fecha', 'entrada', 'salida');
                }
                $count++;
            }
        }
        return $count;
    }

    // --- Helpers de Extracción ---

    protected function parsePeriodo(Worksheet $sheet): ?array
    {
        for ($row = 1; $row <= 50; $row++) { // Escaneo ampliado
            $rowVals = $this->getRowValues($sheet, $row);
            $joined = implode(' ', $rowVals);
            
            // Regex ajustado para aceptar espacios extras y formatos YYYY/MM/DD o YYYY-MM-DD
            if (preg_match('/(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})\s*[~-]\s*(?:(\d{4})[\/\-])?(\d{1,2})[\/\-](\d{1,2})/', $joined, $m)) {
                try {
                    $year = (int)$m[1];
                    $start = Carbon::create($year, (int)$m[2], (int)$m[3]);
                    
                    // A veces el fin solo trae MM/DD, a veces YYYY/MM/DD.
                    // Si el regex captura año en grupo 4, usarlo, si no, usar el del inicio.
                    $endYear = !empty($m[4]) ? (int)$m[4] : $year;
                    $endMonth = (int)$m[5];
                    $endDay = (int)$m[6];

                    // Manejo de fecha fin inválida (ej: 31 nov -> 1 dic o fin de mes real)
                    try {
                        $end = Carbon::create($endYear, $endMonth, $endDay);
                    } catch (\Exception $ex) {
                        $end = $start->copy()->endOfMonth();
                    }
                    return ['inicio' => $start, 'fin' => $end];
                } catch (\Throwable $e) { continue; }
            }
        }
        return null;
    }

    protected function mapDayColumns(Worksheet $sheet): array
    {
        $highestCol = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
        for ($row = 1; $row <= 30; $row++) { 
            $map = [];
            for ($col = 1; $col <= $highestCol; $col++) {
                // Obtenemos valor calculado. En CSV numéricos pueden venir como strings "1.0"
                $cell = $sheet->getCell(Coordinate::stringFromColumnIndex($col) . $row);
                $val = $cell->getValue(); 
                
                if (is_numeric($val)) {
                    $num = (int)$val;
                    // Filtro estricto: solo días válidos (1-31)
                    if ($num >= 1 && $num <= 31) {
                        $map[$num] = $col - 1; // Base 0
                    }
                }
            }
            // Si encontramos secuencia lógica (al menos 5 días seguidos o salteados)
            if (count($map) >= 5) return $map;
        }
        return [];
    }

    protected function extraerHoras(string $cellValue): array
    {
        // Limpiar saltos de línea y espacios raros
        $normalized = preg_replace('/\r\n?|\n/u', ' ', $cellValue);
        preg_match_all($this->horaRegex, $normalized, $matches);
        
        $horas = [];
        foreach ($matches[0] ?? [] as $h) {
            try {
                $horas[] = Carbon::createFromFormat('H:i', $h)->format('H:i:s');
            } catch (\Exception $e) {}
        }
        return $horas;
    }

    protected function construirFecha(array $periodo, int $day): Carbon
    {
        // Clonar fecha inicio
        $date = $periodo['inicio']->copy()->day($day);
        
        // Si el día es menor al día de inicio (ej: inicio 25 Oct, estamos procesando día 2), 
        // asumimos que es del mes siguiente.
        if ($day < $periodo['inicio']->day) {
            $date->addMonth();
        }
        
        // Corrección de año si cruzamos diciembre
        if ($periodo['inicio']->month == 12 && $date->month == 1) {
            $date->addYear();
        }

        return $date;
    }

    protected function buscarEmpleadoId(string $no, ?string $nombre): ?int
    {
        // Prioridad ID
        $emp = Empleado::where('id_empleado', $no)->orWhere('id_empleado', (int)$no)->first();
        if ($emp) return $emp->id;
        
        // Fallback Nombre (búsqueda insensible a mayúsculas/espacios)
        if ($nombre) {
            $cleanName = str_replace(' ', '', Str::lower($nombre));
            $emp = Empleado::whereRaw("LOWER(REPLACE(nombre, ' ', '')) LIKE ?", ["%{$cleanName}%"])->first();
            if ($emp) return $emp->id;
        }
        return null;
    }

    protected function getRowValues(Worksheet $sheet, int $row): array
    {
        $highestCol = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
        $values = [];
        for ($col = 1; $col <= $highestCol; $col++) {
            $val = $sheet->getCell(Coordinate::stringFromColumnIndex($col) . $row)->getValue(); // getValue es más seguro para CSV que getCalculatedValue
            $values[] = trim((string)$val);
        }
        return $values;
    }

    protected function isEmpleadoHeader(array $vals): bool {
        $s = implode(' ', $vals);
        // Busca patrones como "No :", "Num", "ID :"
        return (bool)preg_match('/\b(No|Num|ID)\s*[:\.]/i', $s);
    }

    protected function isNombreHeader(array $vals): bool {
        $s = implode(' ', $vals);
        return (bool)preg_match('/\b(Nombre|Name)\s*[:\.]/i', $s);
    }

    protected function filaTieneChecadas(array $vals): bool {
        foreach ($vals as $v) { if (preg_match($this->horaRegex, $v)) return true; }
        return false;
    }

    protected function extraerValorPosterior(array $vals, array $keys): ?string {
        foreach ($vals as $i => $val) {
            foreach ($keys as $key) {
                // Coincidencia laxa (contiene el texto)
                if (stripos($val, $key) !== false) {
                    // Buscar en las siguientes 3 celdas algo que no esté vacío ni sea solo signos
                    for ($j = $i + 1; $j < min(count($vals), $i + 4); $j++) {
                        $c = trim($vals[$j]);
                        if (!empty($c) && $c !== ':' && $c !== '.') return $c;
                    }
                }
            }
        }
        return null;
    }
}
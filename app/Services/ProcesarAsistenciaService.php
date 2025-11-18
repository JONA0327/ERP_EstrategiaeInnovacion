<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Empleado;
use Carbon\Carbon;

/**
 * Servicio para procesar archivos de asistencia exportados desde relojes SecureCore/ZKTeco.
 *
 * Recorre todas las hojas del archivo Excel, detecta bloques de empleados y normaliza
 * cada par entrada/salida en la tabla `asistencias`.
 *
 * Estructura esperada de cada registro:
 *  - empleado_no (string)
 *  - nombre (string)
 *  - fecha (date YYYY-MM-DD)
 *  - entrada (time HH:MM:SS nullable)
 *  - salida (time HH:MM:SS nullable)
 *  - checadas (json: lista completa de todas las checadas crudas del día)
 *  - empleado_id (nullable: relación con modelo Empleado si se resuelve)
 *  - created_at / updated_at (timestamps manejados por DB::table insert)
 *
 * Reglas clave:
 * 1. No descartar ninguna checada aunque falte salida.
 * 2. Generar un registro por cada par (linea1 = entrada, linea2 = salida o null).
 * 3. Fecha compuesta de año/mes del periodo + número de día (columna mapeada 1..31).
 * 4. Guardar siempre la lista completa de checadas del día (se repetirá en cada registro de ese día)
 *    para trazabilidad completa.
 * 5. Resolver empleado_id primero por número (empleado_no) y si no, por nombre flexible.
 */
class ProcesarAsistenciaService
{
    /** @var int Máximo de filas a escanear para detectar periodo */
    protected int $maxPeriodoScanRows = 30;
    /** @var int Máximo de columnas a escanear para detectar periodo */
    protected int $maxPeriodoScanCols = 15;
    /** @var string Regex para detectar valor de hora HH:MM */
    protected string $horaRegex = '/\\b(2[0-3]|[01]?\\d):([0-5]\\d)\\b/';

    /**
     * Procesa un archivo Excel de asistencias.
     *
     * @param string $path Ruta absoluta al archivo.
     * @return array Resumen [total_registros, empleados_procesados, hojas_procesadas, periodo => [inicio, fin]]
     */
    public function process(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheets = $spreadsheet->getAllSheets();

        $totalRegistros = 0;
        $empleadosProcesados = 0;
        $hojasProcesadas = 0;
        $periodoGlobal = null;

        foreach ($sheets as $sheet) {
            /** @var Worksheet $sheet */
            $hojasProcesadas++;

            // 1. Detectar periodo (solo una vez global, si múltiples hojas comparten).
            $periodo = $this->parsePeriodo($sheet);
            if ($periodo && !$periodoGlobal) {
                $periodoGlobal = $periodo;
            }
            if (!$periodoGlobal) {
                Log::warning('No se detectó periodo en hoja: ' . $sheet->getTitle());
                continue; // Sin periodo no podemos construir fechas confiables.
            }

            // 2. Mapear columnas de días.
            $dayMap = $this->mapDayColumns($sheet);
            if (empty($dayMap)) {
                Log::warning('No se detectaron columnas de días en hoja: ' . $sheet->getTitle());
                continue;
            }

            // 3. Recorrer filas para bloques de empleados.
            $highestRow = $sheet->getHighestDataRow();
            $currentEmpleado = null; // ['no' => string, 'nombre' => string]
            $capturando = false; // Dentro de sección de checadas de un empleado.

            for ($row = 1; $row <= $highestRow; $row++) {
                $rowValues = $this->getRowValues($sheet, $row);

                // Detectar inicio de bloque empleado.
                if ($this->isEmpleadoHeader($rowValues)) {
                    $empleadoNo = $this->extraerValorPosterior($rowValues, 'No');
                    $currentEmpleado = ['no' => $empleadoNo ?: ''];
                    $capturando = true;
                    continue; // Siguiente fila para buscar nombre.
                }

                // Dentro de bloque, intentar capturar nombre si aún no.
                if ($capturando && $currentEmpleado && empty($currentEmpleado['nombre']) && $this->isNombreHeader($rowValues)) {
                    $nombre = $this->extraerValorPosterior($rowValues, 'Nombre');
                    $currentEmpleado['nombre'] = $nombre ?: 'DESCONOCIDO';
                    continue;
                }

                // Si estamos en modo captura y hay checadas en la fila.
                if ($capturando && $currentEmpleado && $this->filaTieneChecadas($rowValues)) {
                    // Procesar cada celda de día que tenga datos.
                    foreach ($dayMap as $dayNum => $colIndex) {
                        $raw = $rowValues[$colIndex] ?? null;
                        if (!$raw || !preg_match($this->horaRegex, $raw)) {
                            continue;
                        }
                        $parsed = $this->parseChecadasCelda($raw);
                        if (empty($parsed['pairs'])) {
                            continue;
                        }

                        $fecha = $this->construirFecha($periodoGlobal, $dayNum);
                        $empleadoId = $this->resolverEmpleadoId($currentEmpleado['no'], $currentEmpleado['nombre']);

                        // Insertar cada par como registro.
                        foreach ($parsed['pairs'] as $pair) {
                            DB::table('asistencias')->insert([
                                'empleado_no' => $currentEmpleado['no'],
                                'nombre' => $currentEmpleado['nombre'],
                                'fecha' => $fecha->toDateString(),
                                'entrada' => $pair['entrada'] ? $pair['entrada'] . ':00' : null,
                                'salida' => $pair['salida'] ? $pair['salida'] . ':00' : null,
                                'checadas' => json_encode($parsed['all']),
                                'empleado_id' => $empleadoId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $totalRegistros++;
                        }
                    }
                }

                // Heurística: fin de bloque si aparece otra cabecera de empleado más adelante.
                if ($capturando && $this->isEmpleadoHeader($rowValues) && $currentEmpleado) {
                    $empleadosProcesados++;
                    // Reiniciamos para nuevo bloque.
                    $empleadoNo = $this->extraerValorPosterior($rowValues, 'No');
                    $currentEmpleado = ['no' => $empleadoNo ?: ''];
                    // Nombre se capturará en siguientes filas.
                }
            }

            // Contabilizar último empleado si se procesó algo.
            if ($capturando && $currentEmpleado) {
                $empleadosProcesados++;
            }
        }

        return [
            'total_registros' => $totalRegistros,
            'empleados_procesados' => $empleadosProcesados,
            'hojas_procesadas' => $hojasProcesadas,
            'periodo' => $periodoGlobal,
        ];
    }

    /**
     * Detecta el periodo buscando celdas que contengan 'Periodo' y rango tipo YYYY/MM/DD ~ MM/DD.
     * @param Worksheet $sheet
     * @return array|null ['inicio' => Carbon, 'fin' => Carbon]
     */
    public function parsePeriodo(Worksheet $sheet): ?array
    {
        $maxRow = min($sheet->getHighestDataRow(), $this->maxPeriodoScanRows);
        $maxCol = min($sheet->getHighestDataColumn(), $this->columnIndexToLetter($this->maxPeriodoScanCols));
        $maxColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($maxCol);

        for ($row = 1; $row <= $maxRow; $row++) {
            for ($col = 1; $col <= $maxColIndex; $col++) {
                $value = (string) $sheet->getCellByColumnAndRow($col, $row)->getValue();
                if (Str::contains(Str::lower($value), 'periodo')) {
                    // Intentar extraer fechas.
                    // Formato esperado: 2025/10/01 ~ 10/31 (CTC)
                    if (preg_match('/(\\d{4})\\/(\\d{2})\\/(\\d{2}).*?~.*?(\\d{2})\\/(\\d{2})/u', $value, $m)) {
                        $year = (int) $m[1];
                        $month = (int) $m[2];
                        $dayStart = (int) $m[3];
                        $monthEnd = (int) $m[4]; // Puede repetirse el mes
                        $dayEnd = (int) $m[5];
                        // Si el segundo mes no coincide asumimos mismo mes; si coincide seguimos.
                        $start = Carbon::create($year, $month, $dayStart, 0, 0, 0);
                        $end = Carbon::create($year, $monthEnd ?: $month, $dayEnd, 0, 0, 0);
                        return ['inicio' => $start, 'fin' => $end];
                    }
                }
            }
        }
        return null;
    }

    /**
     * Identifica fila con días (1..31) y devuelve mapping [dayNumber => columnIndex].
     * @param Worksheet $sheet
     * @return array
     */
    public function mapDayColumns(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestDataRow();
        $highestCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
        for ($row = 1; $row <= $highestRow; $row++) {
            $numbers = [];
            for ($col = 1; $col <= $highestCol; $col++) {
                $value = trim((string) $sheet->getCellByColumnAndRow($col, $row)->getValue());
                if ($value !== '' && ctype_digit($value)) {
                    $num = (int) $value;
                    if ($num >= 1 && $num <= 31) {
                        $numbers[$num] = $col - 1; // Usaremos índice base 0 para arrays de filas.
                    }
                }
            }
            if (count($numbers) >= 5) { // Heurística: suficiente densidad de días.
                ksort($numbers);
                return $numbers;
            }
        }
        return [];
    }

    /** Determina si una fila contiene cabecera de empleado (token 'No :'). */
    public function isEmpleadoHeader(array $rowValues): bool
    {
        $joined = implode(' ', $rowValues);
        return (bool) preg_match('/\bNo\s*:/u', $joined);
    }

    /** Determina si una fila contiene cabecera de nombre (token 'Nombre :'). */
    public function isNombreHeader(array $rowValues): bool
    {
        $joined = implode(' ', $rowValues);
        return (bool) preg_match('/\bNombre\s*:/u', $joined);
    }

    /** Indica si la fila parece contener checadas (al menos un HH:MM y posible salto de línea). */
    public function filaTieneChecadas(array $rowValues): bool
    {
        foreach ($rowValues as $value) {
            if (!is_string($value) || $value === '') continue;
            if (preg_match($this->horaRegex, $value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Parsea una celda de checadas devolviendo pares y lista completa.
     * @param string $raw
     * @return array ['all' => [...], 'pairs' => [ ['entrada'=>t1,'salida'=>t2|null], ... ]]
     */
    public function parseChecadasCelda(string $raw): array
    {
        // Normalizar saltos de línea (pueden venir como \r\n, \n, \r)
        $normalized = preg_replace('/\r\n?|\n/u', "\n", $raw);
        $lines = array_values(array_filter(array_map('trim', explode("\n", $normalized)), fn($l) => $l !== ''));
        // Filtrar sólo patrones hora.
        $times = array_values(array_filter($lines, fn($l) => preg_match($this->horaRegex, $l)));
        $pairs = [];
        for ($i = 0; $i < count($times); $i += 2) {
            $entrada = $times[$i] ?? null;
            $salida = $times[$i + 1] ?? null; // Puede ser null si número impar.
            $pairs[] = [
                'entrada' => $entrada,
                'salida' => $salida,
            ];
        }
        return ['all' => $times, 'pairs' => $pairs];
    }

    /** Construye fecha combinando periodo y día numérico. */
    protected function construirFecha(array $periodo, int $dayNum): Carbon
    {
        $start = $periodo['inicio'];
        // Si dayNum < día inicio, asumir mismo mes (el reloj usualmente cubre mismo mes)
        return Carbon::create($start->year, $start->month, $dayNum, 0, 0, 0);
    }

    /** Obtiene valores crudos de una fila como array indexado base 0. */
    protected function getRowValues(Worksheet $sheet, int $row): array
    {
        $highestCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
        $values = [];
        for ($col = 1; $col <= $highestCol; $col++) {
            $values[] = (string) $sheet->getCellByColumnAndRow($col, $row)->getValue();
        }
        return $values;
    }

    /** Extrae el valor siguiente a un token tipo 'No :' o 'Nombre :'. */
    protected function extraerValorPosterior(array $rowValues, string $token): ?string
    {
        for ($i = 0; $i < count($rowValues); $i++) {
            if (preg_match('/^' . preg_quote($token, '/') . '\s*:/u', $rowValues[$i])) {
                // Buscar siguiente celda no vacía.
                for ($j = $i + 1; $j < count($rowValues); $j++) {
                    $candidate = trim((string) $rowValues[$j]);
                    if ($candidate !== '') {
                        return $candidate;
                    }
                }
            }
        }
        return null;
    }

    /** Resuelve empleado_id por número o nombre normalizado flexible. */
    protected function resolverEmpleadoId(?string $empleadoNo, ?string $nombre): ?int
    {
        $empleadoNo = trim((string) $empleadoNo);
        if ($empleadoNo !== '') {
            $byNo = Empleado::where('id_empleado', $empleadoNo)->orWhere('numero', $empleadoNo)->first();
            if ($byNo) return $byNo->id;
        }
        if (!$nombre) return null;
        $norm = $this->normalizarNombre($nombre);
        // Construir variantes (mayúsculas, minúsculas, capitalizada)
        $variants = array_unique([
            $norm,
            Str::lower($norm),
            Str::upper($norm),
            Str::ucfirst(Str::lower($norm)),
        ]);
        $query = Empleado::query();
        $query->where(function ($q) use ($variants) {
            foreach ($variants as $v) {
                $q->orWhereRaw('REPLACE(LOWER(nombre), " ", "") = ?', [Str::lower($v)]);
            }
        });
        $found = $query->first();
        return $found?->id;
    }

    /** Normaliza nombre eliminando espacios y caracteres no alfabéticos básicos. */
    protected function normalizarNombre(string $nombre): string
    {
        $nombre = trim($nombre);
        // Remover espacios y símbolos, dejar letras y números.
        $nombre = preg_replace('/[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9]/u', '', $nombre);
        return $nombre ?: 'desconocido';
    }

    /** Convierte índice de columna numérico a letra (A, B, ...). */
    protected function columnIndexToLetter(int $index): string
    {
        $dividend = $index;
        $columnName = '';
        while ($dividend > 0) {
            $modulo = ($dividend - 1) % 26;
            $columnName = chr(65 + $modulo) . $columnName;
            $dividend = (int) (($dividend - $modulo) / 26);
        }
        return $columnName;
    }
}

?>
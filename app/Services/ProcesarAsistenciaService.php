<?php

namespace App\Services;

use App\Models\Empleado;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Servicio para procesar archivos Excel de asistencia provenientes de relojes SecureCore/ZKTeco.
 *
 * El servicio recorre todas las hojas del archivo, identifica los bloques de cada empleado y
 * genera un registro normalizado en la tabla `asistencias` por cada par entrada/salida, sin
 * descartar checadas incompletas.
 */
class ProcesarAsistenciaService
{
    /** @var int Máximo de filas a escanear para encontrar el periodo. */
    protected int $maxPeriodoScanRows = 40;

    /** @var int Máximo de columnas a escanear para encontrar el periodo. */
    protected int $maxPeriodoScanCols = 20;

    /** @var string Expresión regular para validar horas en formato HH:MM. */
    protected string $horaRegex = '/\\b(2[0-3]|[01]?\\d):([0-5]\\d)\\b/u';

    /** @var string|null Área objetivo para resolver empleados (se prioriza Recursos Humanos). */
    protected ?string $areaObjetivo = 'Recursos Humanos';

    /**
    * Procesa un archivo Excel de asistencias.
    *
    * @param string $path Ruta absoluta al archivo.
    * @return array{total_registros:int, empleados_procesados:int, hojas_procesadas:int, periodo:array|null}
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

            // 1. Detectar periodo por hoja (se usa como global si es el primero encontrado).
            $periodoHoja = $this->parsePeriodo($sheet);
            if ($periodoHoja && !$periodoGlobal) {
                $periodoGlobal = $periodoHoja;
            }
            $periodo = $periodoHoja ?? $periodoGlobal;
            if (!$periodo) {
                Log::warning('No se detectó periodo en la hoja: ' . $sheet->getTitle());
                continue;
            }

            // 2. Mapear columnas que representan días 1..31.
            $dayColumns = $this->mapDayColumns($sheet);
            if (empty($dayColumns)) {
                Log::warning('No se detectaron columnas de días en la hoja: ' . $sheet->getTitle());
                continue;
            }

            $highestRow = $sheet->getHighestDataRow();
            $empleadoActual = null; // ['no' => string, 'nombre' => string|null]

            for ($row = 1; $row <= $highestRow; $row++) {
                $rowValues = $this->getRowValues($sheet, $row);

                if ($this->isEmpleadoHeader($rowValues)) {
                    if ($empleadoActual) {
                        $empleadosProcesados++;
                    }
                    $empleadoActual = [
                        'no' => $this->extraerValorPosterior($rowValues, 'No') ?? '',
                        'nombre' => null,
                    ];
                    continue;
                }

                if ($empleadoActual && !$empleadoActual['nombre'] && $this->isNombreHeader($rowValues)) {
                    $empleadoActual['nombre'] = $this->extraerValorPosterior($rowValues, 'Nombre') ?? 'DESCONOCIDO';
                    continue;
                }

                if ($empleadoActual && $this->filaTieneChecadas($rowValues)) {
                    $totalRegistros += $this->procesarFilaDeChecadas($rowValues, $dayColumns, $periodo, $empleadoActual);
                }
            }

            if ($empleadoActual) {
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
     * Detecta el periodo buscando celdas que contengan "Periodo" y un rango tipo YYYY/MM/DD ~ MM/DD.
     */
    public function parsePeriodo(Worksheet $sheet): ?array
    {
        $maxRow = min($sheet->getHighestDataRow(), $this->maxPeriodoScanRows);
        $maxColIndex = Coordinate::columnIndexFromString(
            min($sheet->getHighestDataColumn(), $this->columnIndexToLetter($this->maxPeriodoScanCols))
        );

        for ($row = 1; $row <= $maxRow; $row++) {
            for ($col = 1; $col <= $maxColIndex; $col++) {
                $value = (string) $sheet->getCellByColumnAndRow($col, $row)->getValue();
                if (!Str::contains(Str::lower($value), 'periodo')) {
                    continue;
                }

                if (preg_match('/(\\d{4})\\/(\\d{2})\\/(\\d{2}).*?~.*?(\\d{2})\\/(\\d{2})/u', $value, $matches)) {
                    $year = (int) $matches[1];
                    $monthStart = (int) $matches[2];
                    $dayStart = (int) $matches[3];
                    $monthEnd = (int) $matches[4];
                    $dayEnd = (int) $matches[5];

                    $start = Carbon::create($year, $monthStart, $dayStart, 0, 0, 0);
                    $end = Carbon::create($year, $monthEnd ?: $monthStart, $dayEnd, 0, 0, 0);

                    return [
                        'inicio' => $start,
                        'fin' => $end,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Identifica la fila que contiene la secuencia de días (1..31) y devuelve el mapa [día => índice_de_columna_base_0].
     */
    public function mapDayColumns(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestDataRow();
        $highestCol = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        for ($row = 1; $row <= $highestRow; $row++) {
            $map = [];
            for ($col = 1; $col <= $highestCol; $col++) {
                $value = trim((string) $sheet->getCellByColumnAndRow($col, $row)->getValue());
                if ($value === '' || !ctype_digit($value)) {
                    continue;
                }
                $num = (int) $value;
                if ($num < 1 || $num > 31) {
                    continue;
                }
                $map[$num] = $col - 1; // Se usa base 0 para alinearse con getRowValues().
            }

            if (count($map) >= 5) { // heurística mínima para considerar que es la fila de días.
                ksort($map);
                return $map;
            }
        }

        return [];
    }

    /** Determina si una fila contiene la cabecera de empleado (token "No :"). */
    public function isEmpleadoHeader(array $rowValues): bool
    {
        $joined = implode(' ', $rowValues);
        return (bool) preg_match('/\bNo\s*:/iu', $joined);
    }

    /** Determina si una fila contiene la cabecera de nombre (token "Nombre :"). */
    public function isNombreHeader(array $rowValues): bool
    {
        $joined = implode(' ', $rowValues);
        return (bool) preg_match('/\bNombre\s*:/iu', $joined);
    }

    /** Indica si la fila parece contener checadas (horas y saltos de línea). */
    public function filaTieneChecadas(array $rowValues): bool
    {
        foreach ($rowValues as $value) {
            if (!is_string($value) || $value === '') {
                continue;
            }
            if (str_contains($value, "\n") || str_contains($value, "\r")) {
                return true;
            }
            if (preg_match($this->horaRegex, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parsea una celda de checadas devolviendo todas las ocurrencias y los pares entrada/salida.
     *
     * @return array{all: string[], pairs: array<int,array{entrada:?string,salida:?string}>}
     */
    public function parseChecadasCelda(string $raw): array
    {
        $normalized = preg_replace('/\r\n?|\n/u', "\n", $raw);
        $lines = array_values(array_filter(array_map('trim', explode("\n", (string) $normalized)), static fn ($line) => $line !== ''));
        $times = array_values(array_filter($lines, fn ($line) => preg_match($this->horaRegex, $line)));

        $pairs = [];
        $total = count($times);
        for ($i = 0; $i < $total; $i += 2) {
            $entrada = $times[$i] ?? null;
            $salida = $times[$i + 1] ?? null;
            $pairs[] = [
                'entrada' => $entrada,
                'salida' => $salida,
            ];
        }

        return [
            'all' => $times,
            'pairs' => $pairs,
        ];
    }

    /** Procesa una fila que contiene checadas y retorna cuántos registros se generaron. */
    protected function procesarFilaDeChecadas(array $rowValues, array $dayColumns, array $periodo, array $empleado): int
    {
        $total = 0;
        foreach ($dayColumns as $dayNumber => $colIndex) {
            $raw = $rowValues[$colIndex] ?? null;
            if (!$raw || !preg_match($this->horaRegex, (string) $raw)) {
                continue;
            }

            $parsed = $this->parseChecadasCelda((string) $raw);
            if (empty($parsed['pairs'])) {
                continue;
            }

            $fecha = $this->construirFecha($periodo, $dayNumber);
            $empleadoId = $this->resolverEmpleadoId($empleado['no'], $empleado['nombre']);

            foreach ($parsed['pairs'] as $pair) {
                DB::table('asistencias')->insert([
                    'empleado_no' => $empleado['no'],
                    'nombre' => $empleado['nombre'] ?? 'DESCONOCIDO',
                    'fecha' => $fecha->toDateString(),
                    'entrada' => $this->formatearHora($pair['entrada']),
                    'salida' => $this->formatearHora($pair['salida']),
                    'checadas' => json_encode($parsed['all']),
                    'empleado_id' => $empleadoId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $total++;
            }
        }

        return $total;
    }

    /** Construye la fecha combinando el periodo detectado con el número de día. */
    protected function construirFecha(array $periodo, int $dayNum): Carbon
    {
        $start = $periodo['inicio'];
        $end = $periodo['fin'];

        $month = $start->month;
        if ($end->month !== $start->month && $dayNum > $end->day) {
            $month = $start->month;
        } elseif ($end->month !== $start->month && $dayNum <= $end->day) {
            $month = $end->month;
        }

        return Carbon::create($start->year, $month, $dayNum, 0, 0, 0);
    }

    /** Devuelve los valores crudos de una fila como arreglo base 0. */
    protected function getRowValues(Worksheet $sheet, int $row): array
    {
        $highestCol = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
        $values = [];
        for ($col = 1; $col <= $highestCol; $col++) {
            $values[] = (string) $sheet->getCellByColumnAndRow($col, $row)->getValue();
        }

        return $values;
    }

    /** Extrae el valor ubicado después de un token tipo "No :" o "Nombre :". */
    protected function extraerValorPosterior(array $rowValues, string $token): ?string
    {
        $total = count($rowValues);
        for ($i = 0; $i < $total; $i++) {
            if (preg_match('/^' . preg_quote($token, '/') . '\s*:/iu', $rowValues[$i])) {
                for ($j = $i + 1; $j < $total; $j++) {
                    $candidate = trim((string) $rowValues[$j]);
                    if ($candidate !== '') {
                        return $candidate;
                    }
                }
            }
        }

        return null;
    }

    /** Resuelve el id del empleado por número o nombre (incluye coincidencias por iniciales y variantes). */
    protected function resolverEmpleadoId(?string $empleadoNo, ?string $nombre): ?int
    {
        $empleadoNo = $empleadoNo ? trim($empleadoNo) : '';
        if ($empleadoNo !== '') {
            $byNo = $this->buscarEmpleadoPorNumero($empleadoNo);
            if ($byNo) {
                return $byNo->id;
            }
        }

        if ($nombre) {
            $byName = $this->buscarEmpleadoPorNombre($nombre);
            if ($byName) {
                return $byName->id;
            }
        }

        return null;
    }

    /** Busca un empleado por número oficial o código alterno. */
    protected function buscarEmpleadoPorNumero(string $empleadoNo): ?Empleado
    {
        return Empleado::query()
            ->when($this->areaObjetivo, fn ($q) => $q->where('area', $this->areaObjetivo))
            ->where(function ($q) use ($empleadoNo) {
                $q->where('id_empleado', $empleadoNo)
                    ->orWhere('numero', $empleadoNo)
                    ->orWhere('user_id', $empleadoNo);
            })
            ->first();
    }

    /**
     * Busca un empleado por nombre aplicando heurísticas flexibles:
     *  - Coincidencia exacta sin espacios
     *  - Coincidencia por mayúsculas/minúsculas
     *  - Coincidencia por iniciales concatenadas
     */
    protected function buscarEmpleadoPorNombre(string $nombre): ?Empleado
    {
        $variants = $this->generarVariantesNombre($nombre);
        $normalizedTargets = array_map(fn ($variant) => Str::lower(str_replace(' ', '', $variant)), $variants);

        $query = Empleado::query()
            ->when($this->areaObjetivo, fn ($q) => $q->where('area', $this->areaObjetivo))
            ->where(function ($q) use ($normalizedTargets) {
                foreach ($normalizedTargets as $variant) {
                    $like = '%' . $variant . '%';
                    $q->orWhereRaw('LOWER(REPLACE(nombre, " ", "")) LIKE ?', [$like]);
                    $q->orWhereRaw('LOWER(nombre) LIKE ?', [$like]);
                }
            });

        $matches = $query->get();
        if ($matches->isEmpty()) {
            return null;
        }

        $target = $normalizedTargets[0];
        $scored = $matches->mapWithKeys(function (Empleado $empleado) use ($target) {
            $nombreNormalizado = Str::lower(str_replace(' ', '', $empleado->nombre));
            $score = 0;
            similar_text($target, $nombreNormalizado, $score);

            return [$empleado->id => ['empleado' => $empleado, 'score' => $score]];
        });

        return collect($scored)
            ->sortByDesc(fn ($item) => $item['score'])
            ->map(fn ($item) => $item['empleado'])
            ->first();
    }

    /** Normaliza un nombre eliminando caracteres no alfanuméricos y dobles espacios. */
    protected function normalizarNombre(string $nombre): string
    {
        $nombre = trim($nombre);
        $nombre = preg_replace('/[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9\s]/u', ' ', $nombre);
        $nombre = preg_replace('/\s+/', ' ', $nombre);

        return $nombre;
    }

    /** Devuelve las iniciales concatenadas de un nombre (ej. "Mariana Lopez Perez" => "MLP"). */
    protected function extraerIniciales(string $nombre): string
    {
        $parts = preg_split('/\s+/u', trim($nombre));
        $initials = array_map(static fn ($part) => Str::substr($part, 0, 1), array_filter($parts));

        return implode('', $initials);
    }

    /** Genera variantes de nombre para búsquedas tolerantes a mayúsculas, minúsculas e iniciales. */
    protected function generarVariantesNombre(string $nombre): array
    {
        $normalized = $this->normalizarNombre($nombre);
        $initials = $this->extraerIniciales($normalized);
        $variants = [
            $normalized,
            str_replace(' ', '', $normalized),
            Str::upper($normalized),
            Str::lower($normalized),
            Str::ucfirst(Str::lower($normalized)),
            Str::upper($initials),
            Str::lower($initials),
        ];

        // Adicional: combinar primeras palabras para cubrir abreviaturas tipo "MLOP".
        $parts = array_values(array_filter(preg_split('/\s+/u', $normalized)));
        if (count($parts) >= 2) {
            $variants[] = Str::upper(Str::substr($parts[0], 0, 1) . Str::substr($parts[1], 0, 1));
        }
        if (count($parts) >= 3) {
            $variants[] = Str::upper(Str::substr($parts[0], 0, 1) . Str::substr($parts[1], 0, 1) . Str::substr($parts[2], 0, 1));
        }

        return array_values(array_unique(array_filter($variants)));
    }

    /** Convierte HH:MM a HH:MM:SS y admite valores nulos. */
    protected function formatearHora(?string $hora): ?string
    {
        if (!$hora) {
            return null;
        }

        try {
            return Carbon::createFromFormat('H:i', $hora)->format('H:i:s');
        } catch (\Throwable $e) {
            Log::warning('Hora con formato inválido: ' . $hora);
            return null;
        }
    }

    /** Convierte el índice de columna numérico a su letra (A, B, ...). */
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

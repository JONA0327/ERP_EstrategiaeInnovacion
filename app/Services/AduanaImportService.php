<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\IOFactory;
use App\Models\Logistica\Aduana;

class AduanaImportService
{
    /**
     * Importar aduanas desde un archivo Word
     *
     * @param string $filePath Ruta del archivo a importar
     * @return array Resultado de la importación
     */
    public function import(string $filePath): array
    {
        try {
            // Verificar que el archivo existe
            if (!file_exists($filePath)) {
                throw new \Exception("El archivo no existe: {$filePath}");
            }

            // Cargar el documento Word
            $doc = IOFactory::load($filePath);
            $rows = [];

            // Expresión regular para extraer información de aduanas
            // Formato esperado: "99 9 Denominación de la aduana" o "99 Denominación"
            $regex = '/^(\d{2})\s+(\d{1})?\s*(.+)$/u';

            // Procesar todas las secciones del documento
            foreach ($doc->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $this->processElement($element, $regex, $rows);
                }
            }

            // Filtrar y validar los datos extraídos
            $rows = $this->validateAndCleanRows($rows);

            // Insertar los datos en la base de datos usando transacción
            $totalImported = 0;

            DB::transaction(function () use ($rows, &$totalImported) {
                // Limpiar tabla existente (opcional)
                // Aduana::truncate();

                foreach ($rows as $row) {
                    // Verificar si ya existe la combinación aduana + sección
                    $existing = Aduana::where('aduana', $row['aduana'])
                                     ->where('seccion', $row['seccion'])
                                     ->first();

                    if (!$existing) {
                        Aduana::create([
                            'aduana' => $row['aduana'],
                            'seccion' => $row['seccion'],
                            'denominacion' => $row['denominacion'],
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        $totalImported++;
                    }
                }
            });

            return [
                'success' => true,
                'total_processed' => count($rows),
                'total_imported' => $totalImported,
                'total_skipped' => count($rows) - $totalImported,
                'rows' => $rows,
                'message' => "Importación completada. {$totalImported} aduanas importadas, " . (count($rows) - $totalImported) . " omitidas por duplicados."
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage(),
                'total_processed' => 0,
                'total_imported' => 0,
                'total_skipped' => 0,
                'rows' => []
            ];
        }
    }

    /**
     * Procesar elemento del documento Word recursivamente
     */
    private function processElement($element, $regex, &$rows)
    {
        // Si el elemento tiene método getText
        if (method_exists($element, 'getText')) {
            $text = $element->getText();
            $this->extractDataFromText($text, $regex, $rows);
        }
        
        // Si el elemento tiene elementos hijos
        if (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $childElement) {
                $this->processElement($childElement, $regex, $rows);
            }
        }
        
        // Procesar tablas si existen
        if (method_exists($element, 'getRows')) {
            foreach ($element->getRows() as $row) {
                foreach ($row->getCells() as $cell) {
                    $this->processElement($cell, $regex, $rows);
                }
            }
        }
    }

    /**
     * Extraer datos del texto usando regex
     */
    private function extractDataFromText($text, $regex, &$rows)
    {
        // Procesar cada línea del texto
        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            
            // Saltar líneas vacías o muy cortas
            if (empty($line) || strlen($line) < 3) {
                continue;
            }

            // Aplicar regex para extraer datos
            if (preg_match($regex, $line, $matches)) {
                $rows[] = [
                    'aduana' => $matches[1],
                    'seccion' => $matches[2] ?? '0',
                    'denominacion' => trim($matches[3])
                ];
            }
        }
    }

    /**
     * Validar y limpiar los datos extraídos
     */
    private function validateAndCleanRows($rows)
    {
        $cleanRows = [];
        
        foreach ($rows as $row) {
            // Validar que los campos requeridos existan
            if (empty($row['aduana']) || empty($row['denominacion'])) {
                continue;
            }
            
            // Limpiar y normalizar datos
            $cleanRow = [
                'aduana' => str_pad($row['aduana'], 2, '0', STR_PAD_LEFT),
                'seccion' => $row['seccion'] ?: '0',
                'denominacion' => trim(preg_replace('/\s+/', ' ', $row['denominacion'])),
                'patente' => null, // Se puede completar manualmente después
                'pais' => null     // Se puede completar manualmente después
            ];
            
            // Evitar duplicados
            $key = $cleanRow['aduana'] . '_' . $cleanRow['seccion'];
            if (!isset($cleanRows[$key])) {
                $cleanRows[$key] = $cleanRow;
            }
        }
        
        return array_values($cleanRows);
    }

    /**
     * Obtener estadísticas de aduanas
     */
    public function getStats(): array
    {
        return [
            'total_aduanas' => Aduana::count(),
            'por_pais' => Aduana::select('pais', DB::raw('count(*) as total'))
                                ->groupBy('pais')
                                ->get()
                                ->pluck('total', 'pais')
                                ->toArray(),
            'ultimas_importadas' => Aduana::latest()
                                          ->limit(5)
                                          ->get()
                                          ->map(function ($aduana) {
                                              return [
                                                  'id' => $aduana->id,
                                                  'codigo' => $aduana->aduana . $aduana->seccion,
                                                  'denominacion' => $aduana->denominacion,
                                                  'fecha' => $aduana->created_at->format('d/m/Y H:i')
                                              ];
                                          })
                                          ->toArray()
        ];
    }
}

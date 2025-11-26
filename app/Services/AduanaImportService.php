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
            // Cargar el documento Word
            $doc = IOFactory::load($filePath);
            $rows = [];

            // Expresión regular para extraer información de aduanas
            // Formato esperado: "99 9 Denominación de la aduana"
            $regex = '/^(\d{2})\s+(\d)?\s+(.+)$/u';

            // Procesar todas las secciones del documento
            foreach ($doc->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    // Verificar si el elemento tiene texto
                    if (method_exists($element, 'getText')) {
                        $text = $element->getText();

                        // Procesar cada línea del texto
                        foreach (explode("\n", $text) as $line) {
                            $line = trim($line);

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
                }
            }

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

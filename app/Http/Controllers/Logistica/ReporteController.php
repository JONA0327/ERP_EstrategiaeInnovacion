<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Logistica\OperacionLogistica;
use App\Models\Logistica\PedimentoOperacion;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReporteController extends Controller
{
    /**
     * Mostrar vista principal de reportes
     */
    public function index()
    {
        return view('Logistica.reportes.index');
    }

    /**
     * Generar Excel de Matriz de Seguimiento
     */
    public function generarExcelMatriz(Request $request)
    {
        try {
            $query = OperacionLogistica::query();
            
            // Aplicar filtros
            if ($request->fecha_inicio) {
                $query->where('fecha_embarque', '>=', $request->fecha_inicio);
            }
            
            if ($request->fecha_fin) {
                $query->where('fecha_embarque', '<=', $request->fecha_fin);
            }
            
            if ($request->cliente) {
                $query->where('cliente', 'like', '%' . $request->cliente . '%');
            }
            
            $operaciones = $query->orderBy('fecha_embarque', 'desc')->get();
            
            // Crear Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Configurar título
            $sheet->setCellValue('A1', 'MATRIZ DE SEGUIMIENTO LOGÍSTICO');
            $sheet->mergeCells('A1:M1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Información del reporte
            $sheet->setCellValue('A2', 'Generado: ' . now()->format('d/m/Y H:i:s'));
            if ($request->fecha_inicio || $request->fecha_fin) {
                $fechaInfo = 'Período: ';
                if ($request->fecha_inicio) $fechaInfo .= Carbon::parse($request->fecha_inicio)->format('d/m/Y');
                if ($request->fecha_inicio && $request->fecha_fin) $fechaInfo .= ' - ';
                if ($request->fecha_fin) $fechaInfo .= Carbon::parse($request->fecha_fin)->format('d/m/Y');
                $sheet->setCellValue('A3', $fechaInfo);
            }
            
            // Encabezados
            $headers = [
                'ID', 'No. Pedimento', 'Clave', 'Cliente', 'Ejecutivo',
                'Fecha Embarque', 'Estatus', 'Naviera', 'Vessel',
                'Voyage', 'ETA', 'ETD', 'Observaciones'
            ];

            $row = 5;
            foreach ($headers as $col => $header) {
                $columnLetter = Coordinate::stringFromColumnIndex($col + 1);
                $sheet->setCellValue("{$columnLetter}{$row}", $header);
            }
            
            // Estilo encabezados
            $sheet->getStyle("A{$row}:M{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:M{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E3F2FD');
            
            // Datos
            $row++;
            foreach ($operaciones as $operacion) {
                $sheet->setCellValue("A{$row}", $operacion->id);
                $sheet->setCellValue("B{$row}", $operacion->no_pedimento);
                $sheet->setCellValue("C{$row}", $operacion->clave);
                $sheet->setCellValue("D{$row}", $operacion->cliente);
                $sheet->setCellValue("E{$row}", $operacion->ejecutivo);
                $sheet->setCellValue("F{$row}", $operacion->fecha_embarque ? Carbon::parse($operacion->fecha_embarque)->format('d/m/Y') : '');
                $sheet->setCellValue("G{$row}", $operacion->estatus);
                $sheet->setCellValue("H{$row}", $operacion->naviera);
                $sheet->setCellValue("I{$row}", $operacion->vessel);
                $sheet->setCellValue("J{$row}", $operacion->voyage);
                $sheet->setCellValue("K{$row}", $operacion->eta ? Carbon::parse($operacion->eta)->format('d/m/Y') : '');
                $sheet->setCellValue("L{$row}", $operacion->etd ? Carbon::parse($operacion->etd)->format('d/m/Y') : '');
                $sheet->setCellValue("M{$row}", $operacion->observaciones);
                $row++;
            }
            
            // Ajustar columnas
            foreach (range('A', 'M') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Bordes
            $sheet->getStyle("A5:M" . ($row - 1))->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            
            // Generar archivo
            $writer = new Xlsx($spreadsheet);
            $filename = 'matriz_seguimiento_' . date('Y-m-d_H-i-s') . '.xlsx';
            $temp_file = tempnam(sys_get_temp_dir(), $filename);
            
            $writer->save($temp_file);
            
            return response()->download($temp_file, $filename)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al generar el reporte: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generar Excel de Pedimentos
     */
    public function generarExcelPedimentos(Request $request)
    {
        try {
            $query = OperacionLogistica::with(['pedimentoOperacion']);
            
            // Aplicar filtros
            if ($request->estado_pago) {
                if ($request->estado_pago === 'pagado') {
                    $query->whereHas('pedimentoOperacion', function($q) {
                        $q->where('estado_pago', 'pagado');
                    });
                } elseif ($request->estado_pago === 'pendiente') {
                    $query->where(function($q) {
                        $q->whereDoesntHave('pedimentoOperacion')
                          ->orWhereHas('pedimentoOperacion', function($subQ) {
                              $subQ->where('estado_pago', 'pendiente');
                          });
                    });
                }
            }
            
            if ($request->tipo_operacion) {
                if ($request->tipo_operacion === 'importacion') {
                    $query->whereIn('clave', ['A1', 'A3', 'A4']); // Claves típicas de importación
                } elseif ($request->tipo_operacion === 'exportacion') {
                    $query->whereIn('clave', ['B1', 'B2', 'B5']); // Claves típicas de exportación
                }
            }
            
            if ($request->clave) {
                $query->where('clave', $request->clave);
            }
            
            if ($request->fecha_pago_inicio || $request->fecha_pago_fin) {
                $query->whereHas('pedimentoOperacion', function($q) use ($request) {
                    if ($request->fecha_pago_inicio) {
                        $q->where('fecha_pago', '>=', $request->fecha_pago_inicio);
                    }
                    if ($request->fecha_pago_fin) {
                        $q->where('fecha_pago', '<=', $request->fecha_pago_fin);
                    }
                });
            }
            
            if ($request->moneda) {
                $query->whereHas('pedimentoOperacion', function($q) use ($request) {
                    $q->where('moneda', $request->moneda);
                });
            }
            
            $operaciones = $query->orderBy('fecha_embarque', 'desc')->get();
            
            // Crear Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Configurar título
            $sheet->setCellValue('A1', 'REPORTE DE CONTROL DE PAGOS DE PEDIMENTOS');
            $sheet->mergeCells('A1:P1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Información del reporte
            $sheet->setCellValue('A2', 'Generado: ' . now()->format('d/m/Y H:i:s'));
            
            $filtrosInfo = [];
            if ($request->estado_pago) $filtrosInfo[] = 'Estado: ' . ($request->estado_pago === 'pagado' ? 'Pagados' : 'Pendientes');
            if ($request->tipo_operacion) $filtrosInfo[] = 'Tipo: ' . ucfirst($request->tipo_operacion);
            if ($request->clave) $filtrosInfo[] = 'Clave: ' . $request->clave;
            if ($request->moneda) $filtrosInfo[] = 'Moneda: ' . $request->moneda;
            
            if (!empty($filtrosInfo)) {
                $sheet->setCellValue('A3', 'Filtros aplicados: ' . implode(' | ', $filtrosInfo));
            }
            
            // Encabezados
            $headers = [
                'No. Pedimento', 'Clave', 'Tipo Op.', 'Cliente', 'Ejecutivo',
                'Fecha Embarque', 'Estado Pago', 'Fecha Pago', 'Monto', 'Moneda',
                'Días Proceso', 'Naviera', 'Vessel', 'Observaciones Pago', 'Observaciones Op.'
            ];

            $row = 5;
            foreach ($headers as $col => $header) {
                $columnLetter = Coordinate::stringFromColumnIndex($col + 1);
                $sheet->setCellValue("{$columnLetter}{$row}", $header);
            }
            
            // Estilo encabezados
            $sheet->getStyle("A{$row}:O{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:O{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E8F5E8');
            
            // Datos
            $row++;
            $totalMontoPagado = 0;
            $pedimentosPagados = 0;
            $pedimentosPendientes = 0;
            $tiempoProcesoTotal = 0;
            $operacionesConTiempo = 0;
            
            foreach ($operaciones as $operacion) {
                $pedimento = $operacion->pedimentoOperacion;
                $estadoPago = $pedimento ? $pedimento->estado_pago : 'pendiente';
                $fechaPago = $pedimento && $pedimento->fecha_pago ? Carbon::parse($pedimento->fecha_pago) : null;
                $monto = $pedimento ? $pedimento->monto : null;
                $moneda = $pedimento ? $pedimento->moneda : 'MXN';
                
                // Calcular días de proceso
                $diasProceso = '';
                if ($operacion->fecha_embarque && $fechaPago) {
                    $diasProc = Carbon::parse($operacion->fecha_embarque)->diffInDays($fechaPago);
                    $diasProceso = $diasProc;
                    
                    if ($request->incluir_tiempos) {
                        $tiempoProcesoTotal += $diasProc;
                        $operacionesConTiempo++;
                    }
                }
                
                // Determinar tipo de operación
                $tipoOperacion = '';
                if (in_array($operacion->clave, ['A1', 'A3', 'A4'])) {
                    $tipoOperacion = 'Importación';
                } elseif (in_array($operacion->clave, ['B1', 'B2', 'B5'])) {
                    $tipoOperacion = 'Exportación';
                } else {
                    $tipoOperacion = 'Otro';
                }
                
                $sheet->setCellValue("A{$row}", $operacion->no_pedimento);
                $sheet->setCellValue("B{$row}", $operacion->clave);
                $sheet->setCellValue("C{$row}", $tipoOperacion);
                $sheet->setCellValue("D{$row}", $operacion->cliente);
                $sheet->setCellValue("E{$row}", $operacion->ejecutivo);
                $sheet->setCellValue("F{$row}", $operacion->fecha_embarque ? Carbon::parse($operacion->fecha_embarque)->format('d/m/Y') : '');
                $sheet->setCellValue("G{$row}", $estadoPago === 'pagado' ? '✅ Pagado' : '⏳ Pendiente');
                $sheet->setCellValue("H{$row}", $fechaPago ? $fechaPago->format('d/m/Y') : '');
                $sheet->setCellValue("I{$row}", $monto ? number_format($monto, 2) : '');
                $sheet->setCellValue("J{$row}", $moneda);
                $sheet->setCellValue("K{$row}", $diasProceso);
                $sheet->setCellValue("L{$row}", $operacion->naviera);
                $sheet->setCellValue("M{$row}", $operacion->vessel);
                $sheet->setCellValue("N{$row}", $pedimento ? $pedimento->observaciones : '');
                $sheet->setCellValue("O{$row}", $operacion->observaciones);
                
                // Colores por estado
                if ($estadoPago === 'pagado') {
                    $sheet->getStyle("G{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('D4EDDA');
                    $pedimentosPagados++;
                    if ($monto) $totalMontoPagado += $monto;
                } else {
                    $sheet->getStyle("G{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('FFF3CD');
                    $pedimentosPendientes++;
                }
                
                $row++;
            }
            
            // Agregar estadísticas al final
            $row += 2;
            $sheet->setCellValue("A{$row}", 'ESTADÍSTICAS DEL REPORTE');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
            
            $row++;
            $sheet->setCellValue("A{$row}", 'Total Pedimentos:');
            $sheet->setCellValue("B{$row}", count($operaciones));
            
            $row++;
            $sheet->setCellValue("A{$row}", 'Pedimentos Pagados:');
            $sheet->setCellValue("B{$row}", $pedimentosPagados);
            
            $row++;
            $sheet->setCellValue("A{$row}", 'Pedimentos Pendientes:');
            $sheet->setCellValue("B{$row}", $pedimentosPendientes);
            
            $row++;
            $sheet->setCellValue("A{$row}", 'Monto Total Pagado:');
            $sheet->setCellValue("B{$row}", '$' . number_format($totalMontoPagado, 2));
            
            if ($request->incluir_tiempos && $operacionesConTiempo > 0) {
                $row++;
                $sheet->setCellValue("A{$row}", 'Tiempo Promedio Proceso:');
                $sheet->setCellValue("B{$row}", round($tiempoProcesoTotal / $operacionesConTiempo, 1) . ' días');
            }
            
            // Ajustar columnas
            foreach (range('A', 'O') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Bordes
            $sheet->getStyle("A5:O" . ($row - 8))->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            
            // Generar archivo
            $writer = new Xlsx($spreadsheet);
            $filename = 'reporte_pedimentos_' . date('Y-m-d_H-i-s') . '.xlsx';
            $temp_file = tempnam(sys_get_temp_dir(), $filename);
            
            $writer->save($temp_file);
            
            return response()->download($temp_file, $filename)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al generar el reporte: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener claves de pedimento para filtros
     */
    public function getClaves()
    {
        $claves = OperacionLogistica::select('clave')
            ->distinct()
            ->whereNotNull('clave')
            ->orderBy('clave')
            ->get()
            ->map(function($item) {
                $descripciones = [
                    'A1' => 'Importación Definitiva',
                    'A3' => 'Importación Temporal',
                    'A4' => 'Depósito Fiscal',
                    'B1' => 'Exportación Definitiva',
                    'B2' => 'Exportación Temporal',
                    'B5' => 'Exportación de Maquiladora'
                ];
                
                return [
                    'clave' => $item->clave,
                    'descripcion' => $descripciones[$item->clave] ?? 'Descripción no disponible'
                ];
            });
        
        return response()->json($claves);
    }

    /**
     * Obtener clientes para filtros
     */
    public function getClientes()
    {
        $clientes = OperacionLogistica::select('cliente')
            ->distinct()
            ->whereNotNull('cliente')
            ->where('cliente', '!=', '')
            ->orderBy('cliente')
            ->get()
            ->map(function($item) {
                return ['nombre' => $item->cliente];
            });
        
        return response()->json($clientes);
    }
}
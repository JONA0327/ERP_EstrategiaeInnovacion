<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class ExcelReportService
{
    private $spreadsheet;
    private $worksheet;
    private $currentRow = 1;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->worksheet = $this->spreadsheet->getActiveSheet();
    }

    /**
     * Generar reporte completo de operaciones logísticas con gráficos y diseño moderno
     */
    public function generateLogisticsReport($operaciones, $filtros = [], $estadisticas = [])
    {
        // 1. Configurar propiedades del documento
        $this->setDocumentProperties();

        // 2. Crear portada profesional
        $this->createCoverPage($filtros, $estadisticas);

        // 3. Crear hoja de resumen ejecutivo con gráficos
        $this->createExecutiveSummary($estadisticas);

        // 4. Crear hoja de datos detallados
        $this->createDataSheet($operaciones);

        // 5. Crear hoja de análisis temporal
        $this->createTemporalAnalysis($operaciones);

        // 6. Crear hoja de performance por ejecutivo
        $this->createExecutivePerformance($operaciones);

        return $this->spreadsheet;
    }

    /**
     * Configurar propiedades del documento
     */
    private function setDocumentProperties()
    {
        $this->spreadsheet->getProperties()
            ->setCreator('Estrategia e Innovación - Sistema de Logística')
            ->setLastModifiedBy('Sistema ERP')
            ->setTitle('Reporte de Operaciones Logísticas')
            ->setSubject('Análisis de Performance Logística')
            ->setDescription('Reporte detallado de operaciones con análisis de performance y métricas')
            ->setKeywords('logística, operaciones, performance, análisis')
            ->setCategory('Reportes Ejecutivos');
    }

    /**
     * Crear portada profesional
     */
    private function createCoverPage($filtros, $estadisticas)
    {
        $sheet = $this->worksheet;
        $sheet->setTitle('Portada');

        // Logo y header principal
        $sheet->mergeCells('A1:H5');
        $sheet->setCellValue('A1', 'REPORTE DE OPERACIONES LOGÍSTICAS');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 28,
                'bold' => true,
                'color' => ['rgb' => '2E4BC6']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_GRADIENT_LINEAR,
                'startColor' => ['rgb' => 'F8F9FA'],
                'endColor' => ['rgb' => 'E3F2FD']
            ]
        ]);

        // Información del reporte
        $sheet->setCellValue('A7', 'ESTRATEGIA E INNOVACIÓN');
        $sheet->getStyle('A7')->applyFromArray([
            'font' => ['size' => 16, 'bold' => true, 'color' => ['rgb' => '495057']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->mergeCells('A7:H7');

        // Fecha y período
        $sheet->setCellValue('A10', 'Fecha de Generación:');
        $sheet->setCellValue('C10', now()->format('d/m/Y H:i'));
        $sheet->setCellValue('A11', 'Período del Reporte:');
        $fechaInicio = $filtros['fecha_desde'] ?? 'Inicio';
        $fechaFin = $filtros['fecha_hasta'] ?? 'Actual';
        $sheet->setCellValue('C11', "{$fechaInicio} - {$fechaFin}");

        // Estadísticas resumen en la portada
        $this->addCoverStatistics($sheet, $estadisticas);

        // Estilo para la información
        $sheet->getStyle('A10:C11')->applyFromArray([
            'font' => ['size' => 12, 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);
    }

    /**
     * Agregar estadísticas a la portada
     */
    private function addCoverStatistics($sheet, $estadisticas)
    {
        $row = 15;

        // Header de estadísticas
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'RESUMEN EJECUTIVO');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['size' => 18, 'bold' => true, 'color' => ['rgb' => '2E4BC6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $row += 2;

        $stats = [
            'Total de Operaciones' => $estadisticas['total'] ?? 0,
            'En Proceso' => $estadisticas['en_proceso'] ?? 0,
            'Completadas' => $estadisticas['completadas'] ?? 0,
            'Fuera de Métrica' => $estadisticas['fuera_metrica'] ?? 0
        ];

        foreach ($stats as $label => $value) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("C{$row}", $value);

            // Estilos para las métricas
            $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                'font' => ['size' => 12, 'bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FA']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);

            $row++;
        }
    }

    /**
     * Crear hoja de resumen ejecutivo con gráficos
     */
    private function createExecutiveSummary($estadisticas)
    {
        $summarySheet = $this->spreadsheet->createSheet();
        $summarySheet->setTitle('Resumen Ejecutivo');

        // Header del resumen
        $summarySheet->mergeCells('A1:H1');
        $summarySheet->setCellValue('A1', 'ANÁLISIS DE PERFORMANCE - DASHBOARD EJECUTIVO');
        $summarySheet->getStyle('A1')->applyFromArray([
            'font' => ['size' => 20, 'bold' => true, 'color' => ['rgb' => '2E4BC6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E3F2FD']]
        ]);

        // KPIs principales
        $this->createKPICards($summarySheet, $estadisticas);

        // Gráfico de distribución por status
        $this->createStatusChart($summarySheet, $estadisticas);

        // Gráfico de tendencias temporales
        $this->createTrendsChart($summarySheet, $estadisticas);
    }

    /**
     * Crear gráfico de tendencias temporales
     */
    private function createTrendsChart($sheet, $estadisticas)
    {
        // Datos de ejemplo para tendencias (últimos 6 meses)
        $meses = ['Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $tendencias = [
            'Completadas' => [45, 52, 48, 61, 58, ($estadisticas['completadas'] ?? 0)],
            'En Proceso' => [12, 15, 18, 14, 16, ($estadisticas['en_proceso'] ?? 0)],
            'Fuera Métrica' => [8, 6, 9, 7, 5, ($estadisticas['fuera_metrica'] ?? 0)]
        ];

        $row = 28; // Posición debajo del gráfico de pie

        // Título del gráfico
        $sheet->mergeCells('A' . ($row - 1) . ':F' . ($row - 1));
        $sheet->setCellValue('A' . ($row - 1), 'TENDENCIAS ÚLTIMOS 6 MESES');
        $sheet->getStyle('A' . ($row - 1))->applyFromArray([
            'font' => ['size' => 14, 'bold' => true, 'color' => ['rgb' => '2E4BC6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Encabezados
        $sheet->setCellValue('A' . $row, 'Mes');
        $col = 'B';
        foreach (array_keys($tendencias) as $serie) {
            $sheet->setCellValue($col . $row, $serie);
            $col++;
        }

        // Datos
        foreach ($meses as $index => $mes) {
            $currentRow = $row + 1 + $index;
            $sheet->setCellValue('A' . $currentRow, $mes);

            $col = 'B';
            foreach ($tendencias as $serie => $datos) {
                $sheet->setCellValue($col . $currentRow, $datos[$index]);
                $col++;
            }
        }

        // Aplicar formato a los headers
        $headerRange = 'A' . $row . ':D' . $row;
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E4BC6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Aplicar formato alternado a las filas de datos
        for ($i = 1; $i <= count($meses); $i++) {
            $dataRow = $row + $i;
            if ($i % 2 == 0) {
                $sheet->getStyle('A' . $dataRow . ':D' . $dataRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8F9FA');
            }
        }

        // Crear gráfico de líneas para tendencias
        $this->createLineChart($sheet, 'A' . $row . ':D' . ($row + count($meses)), 'F' . ($row - 1) . ':L' . ($row + 15), 'Tendencias de Operaciones');
    }

    /**
     * Crear gráfico de líneas
     */
    private function createLineChart($sheet, $dataRange, $chartPosition, $title)
    {
        // Configurar categorías (meses)
        $categories = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $sheet->getTitle() . '!' . 'A29:A34', null, 6)
        ];

        // Configurar series de datos
        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $sheet->getTitle() . '!' . 'B28:D28', null, 3)
        ];

        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $sheet->getTitle() . '!' . 'B29:B34', null, 6),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $sheet->getTitle() . '!' . 'C29:C34', null, 6),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $sheet->getTitle() . '!' . 'D29:D34', null, 6)
        ];

        // Crear serie de datos
        $series = new DataSeries(
            DataSeries::TYPE_LINECHART,
            DataSeries::GROUPING_STANDARD,
            range(0, count($dataSeriesValues) - 1),
            $dataSeriesLabels,
            $categories,
            $dataSeriesValues
        );

        // Configurar propiedades de la serie
        $series->setPlotDirection(DataSeries::DIRECTION_COL);

        // Configurar área de ploteo
        $plotArea = new PlotArea(null, [$series]);

        // Configurar leyenda
        $legend = new Legend(Legend::POSITION_BOTTOM, null, false);

        // Crear título del gráfico
        $chartTitle = new Title($title);

        // Crear el gráfico
        $chart = new Chart('trendChart', $chartTitle, $legend, $plotArea);
        $chart->setTopLeftPosition($chartPosition);
        $chart->setBottomRightPosition(chr(ord(substr($chartPosition, 0, 1)) + 6) . (intval(substr($chartPosition, 1)) + 16));

        // Agregar gráfico a la hoja
        $sheet->addChart($chart);
    }

    /**
     * Crear tarjetas KPI en el resumen
     */
    private function createKPICards($sheet, $estadisticas)
    {
        $row = 4;

        $kpis = [
            ['titulo' => 'TOTAL OPERACIONES', 'valor' => $estadisticas['total'] ?? 0, 'color' => '2196F3'],
            ['titulo' => 'EFICIENCIA (%)', 'valor' => number_format(($estadisticas['completadas'] ?? 0) / max(($estadisticas['total'] ?? 1), 1) * 100, 1) . '%', 'color' => '4CAF50'],
            ['titulo' => 'EN PROCESO', 'valor' => $estadisticas['en_proceso'] ?? 0, 'color' => 'FF9800'],
            ['titulo' => 'FUERA DE MÉTRICA', 'valor' => $estadisticas['fuera_metrica'] ?? 0, 'color' => 'F44336']
        ];

        $col = 'A';
        foreach ($kpis as $kpi) {
            // Crear tarjeta KPI
            $range = $col . $row . ':' . chr(ord($col) + 1) . ($row + 2);
            $sheet->mergeCells($range);

            $sheet->setCellValue($col . $row, $kpi['titulo']);
            $sheet->setCellValue($col . ($row + 1), $kpi['valor']);

            // Estilos para la tarjeta
            $sheet->getStyle($range)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $kpi['color']]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['rgb' => '000000']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);

            $sheet->getStyle($col . $row)->getFont()->setSize(10);
            $sheet->getStyle($col . ($row + 1))->getFont()->setSize(18);

            $col = chr(ord($col) + 3); // Saltar 3 columnas para el siguiente KPI
        }
    }

    /**
     * Crear gráfico de distribución por status
     */
    private function createStatusChart($sheet, $estadisticas)
    {
        // Datos para el gráfico
        $row = 10;
        $sheet->setCellValue('A' . $row, 'Status');
        $sheet->setCellValue('B' . $row, 'Cantidad');

        $data = [
            ['En Proceso', $estadisticas['en_proceso'] ?? 0],
            ['Completadas', $estadisticas['completadas'] ?? 0],
            ['Fuera de Métrica', $estadisticas['fuera_metrica'] ?? 0]
        ];

        $currentRow = $row + 1;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $currentRow, $item[0]);
            $sheet->setCellValue('B' . $currentRow, $item[1]);
            $currentRow++;
        }

        // Crear gráfico de dona/pie
        $this->createPieChart($sheet, 'A' . $row . ':B' . ($currentRow - 1), 'D10:I25', 'Distribución por Status');
    }

    /**
     * Crear gráfico circular (pie/dona)
     */
    private function createPieChart($sheet, $dataRange, $chartPosition, $title)
    {
        // Configurar series de datos
        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $sheet->getTitle() . '!' . 'A11:A13', null, 3)
        ];

        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $sheet->getTitle() . '!' . 'B11:B13', null, 3)
        ];

        // Crear categorías para el gráfico
        $plotCategories = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $sheet->getTitle() . '!' . 'A11:A13', null, 3)
        ];

        // Crear serie de datos
        $series = new DataSeries(
            DataSeries::TYPE_PIECHART,
            DataSeries::GROUPING_STANDARD,
            range(0, count($dataSeriesValues) - 1),
            $dataSeriesLabels,
            $plotCategories,
            $dataSeriesValues
        );

        // Configurar área de ploteo
        $plotArea = new PlotArea(null, [$series]);

        // Configurar leyenda
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);

        // Crear título del gráfico
        $chartTitle = new Title($title);

        // Crear el gráfico
        $chart = new Chart('chart1', $chartTitle, $legend, $plotArea);
        $chart->setTopLeftPosition($chartPosition);
        $chart->setBottomRightPosition(chr(ord(substr($chartPosition, 0, 1)) + 5) . (intval(substr($chartPosition, 1)) + 15));

        // Agregar gráfico a la hoja
        $sheet->addChart($chart);
    }

    /**
     * Crear hoja de datos detallados
     */
    private function createDataSheet($operaciones)
    {
        $dataSheet = $this->spreadsheet->createSheet();
        $dataSheet->setTitle('Datos Detallados');

        // Configurar encabezados
        $headers = [
            'ID', 'Cliente', 'Ejecutivo', 'Fecha Creación', 'ETA', 'Agente Aduanal',
            'Pedimento', 'Guía/BL', 'Transporte', 'Status Calculado', 'Status Manual',
            'Días Transcurridos', 'Target Días', 'Resultado', 'Comentarios',
            'Post-Operaciones Completas', 'Post-Operaciones Pendientes'
        ];

        // Escribir encabezados con estilo
        foreach ($headers as $col => $header) {
            $cellRef = chr(65 + $col) . '1';
            $dataSheet->setCellValue($cellRef, $header);
        }

        // Estilo para encabezados
        $headerRange = 'A1:' . chr(65 + count($headers) - 1) . '1';
        $dataSheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E4BC6']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Escribir datos
        $row = 2;
        foreach ($operaciones as $operacion) {
            $dataSheet->setCellValue('A' . $row, $operacion->id);
            $dataSheet->setCellValue('B' . $row, $operacion->cliente);
            $dataSheet->setCellValue('C' . $row, $operacion->ejecutivo);
            $dataSheet->setCellValue('D' . $row, $operacion->created_at->format('d/m/Y'));
            $dataSheet->setCellValue('E' . $row, $operacion->eta ? $operacion->eta->format('d/m/Y') : 'N/A');
            $dataSheet->setCellValue('F' . $row, $operacion->agente_aduanal);
            $dataSheet->setCellValue('G' . $row, $operacion->pedimento);
            $dataSheet->setCellValue('H' . $row, $operacion->guia_bl ?? 'N/A');
            $dataSheet->setCellValue('I' . $row, $operacion->transporte);
            $dataSheet->setCellValue('J' . $row, $operacion->status_calculado);
            $dataSheet->setCellValue('K' . $row, $operacion->status_manual ?? $operacion->status_calculado);
            $dataSheet->setCellValue('L' . $row, $operacion->calcularDiasTranscurridos());
            $dataSheet->setCellValue('M' . $row, $operacion->dias_objetivo ?? 5);

            // Colorear resultado basado en performance
            $resultado = $this->calculateResult($operacion);
            $dataSheet->setCellValue('N' . $row, $resultado['texto']);
            $dataSheet->getStyle('N' . $row)->getFill()->setFillType(Fill::FILL_SOLID);
            $dataSheet->getStyle('N' . $row)->getFill()->getStartColor()->setRGB($resultado['color']);

            $dataSheet->setCellValue('O' . $row, $operacion->comentarios ?? '');
            $dataSheet->setCellValue('P' . $row, $operacion->postOperacionesCompletas ?? 0);
            $dataSheet->setCellValue('Q' . $row, $operacion->postOperacionesPendientes ?? 0);

            // Aplicar bordes alternados
            if ($row % 2 == 0) {
                $rowRange = 'A' . $row . ':Q' . $row;
                $dataSheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID);
                $dataSheet->getStyle($rowRange)->getFill()->getStartColor()->setRGB('F8F9FA');
            }

            $row++;
        }

        // Autoajustar columnas
        foreach (range('A', 'Q') as $col) {
            $dataSheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * Crear análisis temporal
     */
    private function createTemporalAnalysis($operaciones)
    {
        $temporalSheet = $this->spreadsheet->createSheet();
        $temporalSheet->setTitle('Análisis Temporal');

        $temporalSheet->mergeCells('A1:F1');
        $temporalSheet->setCellValue('A1', 'ANÁLISIS DE TENDENCIAS Y PERFORMANCE TEMPORAL');
        $temporalSheet->getStyle('A1')->applyFromArray([
            'font' => ['size' => 16, 'bold' => true, 'color' => ['rgb' => '2E4BC6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E3F2FD']]
        ]);

        // Análisis por mes
        $monthlyData = $this->processMonthlyData($operaciones);
        $this->createMonthlyChart($temporalSheet, $monthlyData);
    }

    /**
     * Procesar datos mensuales
     */
    private function processMonthlyData($operaciones)
    {
        $monthlyData = [];

        foreach ($operaciones as $operacion) {
            $month = $operacion->created_at->format('Y-m');
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = ['total' => 0, 'completadas' => 0, 'en_proceso' => 0];
            }

            $monthlyData[$month]['total']++;

            if ($operacion->status_manual === 'Done' || $operacion->status_calculado === 'Done') {
                $monthlyData[$month]['completadas']++;
            } else {
                $monthlyData[$month]['en_proceso']++;
            }
        }

        return $monthlyData;
    }

    /**
     * Crear gráfico mensual
     */
    private function createMonthlyChart($sheet, $monthlyData)
    {
        $row = 4;

        // Headers
        $sheet->setCellValue('A' . $row, 'Mes');
        $sheet->setCellValue('B' . $row, 'Total');
        $sheet->setCellValue('C' . $row, 'Completadas');
        $sheet->setCellValue('D' . $row, 'En Proceso');

        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E4BC6']],
            'font' => ['color' => ['rgb' => 'FFFFFF']]
        ]);

        $currentRow = $row + 1;
        foreach ($monthlyData as $month => $data) {
            $sheet->setCellValue('A' . $currentRow, $month);
            $sheet->setCellValue('B' . $currentRow, $data['total']);
            $sheet->setCellValue('C' . $currentRow, $data['completadas']);
            $sheet->setCellValue('D' . $currentRow, $data['en_proceso']);
            $currentRow++;
        }
    }

    /**
     * Crear análisis de performance por ejecutivo
     */
    private function createExecutivePerformance($operaciones)
    {
        $execSheet = $this->spreadsheet->createSheet();
        $execSheet->setTitle('Performance Ejecutivos');

        $execSheet->mergeCells('A1:F1');
        $execSheet->setCellValue('A1', 'ANÁLISIS DE PERFORMANCE POR EJECUTIVO');
        $execSheet->getStyle('A1')->applyFromArray([
            'font' => ['size' => 16, 'bold' => true, 'color' => ['rgb' => '2E4BC6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E3F2FD']]
        ]);

        // Procesar datos por ejecutivo
        $execData = $this->processExecutiveData($operaciones);
        $this->renderExecutiveTable($execSheet, $execData);
    }

    /**
     * Procesar datos por ejecutivo
     */
    private function processExecutiveData($operaciones)
    {
        $execData = [];

        foreach ($operaciones as $operacion) {
            $ejecutivo = $operacion->ejecutivo ?? 'Sin Asignar';

            if (!isset($execData[$ejecutivo])) {
                $execData[$ejecutivo] = [
                    'total' => 0,
                    'completadas' => 0,
                    'en_proceso' => 0,
                    'fuera_metrica' => 0,
                    'promedio_dias' => 0,
                    'total_dias' => 0
                ];
            }

            $execData[$ejecutivo]['total']++;
            $execData[$ejecutivo]['total_dias'] += $operacion->calcularDiasTranscurridos();

            if ($operacion->status_manual === 'Done') {
                $execData[$ejecutivo]['completadas']++;
            } elseif ($operacion->status_manual === 'Out of Metric') {
                $execData[$ejecutivo]['fuera_metrica']++;
            } else {
                $execData[$ejecutivo]['en_proceso']++;
            }
        }

        // Calcular promedios
        foreach ($execData as $ejecutivo => $data) {
            $execData[$ejecutivo]['promedio_dias'] = $data['total'] > 0 ?
                round($data['total_dias'] / $data['total'], 1) : 0;
            $execData[$ejecutivo]['eficiencia'] = $data['total'] > 0 ?
                round(($data['completadas'] / $data['total']) * 100, 1) : 0;
        }

        return $execData;
    }

    /**
     * Renderizar tabla de ejecutivos
     */
    private function renderExecutiveTable($sheet, $execData)
    {
        $row = 4;
        $headers = ['Ejecutivo', 'Total Ops', 'Completadas', 'En Proceso', 'Fuera Métrica', 'Promedio Días', 'Eficiencia %'];

        foreach ($headers as $col => $header) {
            $sheet->setCellValue(chr(65 + $col) . $row, $header);
        }

        $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E4BC6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $currentRow = $row + 1;
        foreach ($execData as $ejecutivo => $data) {
            $sheet->setCellValue('A' . $currentRow, $ejecutivo);
            $sheet->setCellValue('B' . $currentRow, $data['total']);
            $sheet->setCellValue('C' . $currentRow, $data['completadas']);
            $sheet->setCellValue('D' . $currentRow, $data['en_proceso']);
            $sheet->setCellValue('E' . $currentRow, $data['fuera_metrica']);
            $sheet->setCellValue('F' . $currentRow, $data['promedio_dias']);
            $sheet->setCellValue('G' . $currentRow, $data['eficiencia'] . '%');

            // Colorear eficiencia
            $eficienciaColor = $data['eficiencia'] >= 80 ? '4CAF50' : ($data['eficiencia'] >= 60 ? 'FF9800' : 'F44336');
            $sheet->getStyle('G' . $currentRow)->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle('G' . $currentRow)->getFill()->getStartColor()->setRGB($eficienciaColor);

            $currentRow++;
        }

        // Autoajustar columnas
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * Calcular resultado de operación
     */
    private function calculateResult($operacion)
    {
        $dias = $operacion->calcularDiasTranscurridos();
        $target = $operacion->dias_objetivo ?? 5;

        if ($operacion->status_manual === 'Done') {
            if ($dias <= $target) {
                return ['texto' => 'Completado a Tiempo', 'color' => '4CAF50'];
            } else {
                return ['texto' => 'Completado con Retraso', 'color' => 'FF9800'];
            }
        } elseif ($operacion->status_manual === 'Out of Metric') {
            return ['texto' => 'Fuera de Métrica', 'color' => 'F44336'];
        } else {
            if ($dias <= $target) {
                return ['texto' => 'En Tiempo', 'color' => '2196F3'];
            } elseif ($dias <= $target + 2) {
                return ['texto' => 'En Riesgo', 'color' => 'FF9800'];
            } else {
                return ['texto' => 'Con Retraso', 'color' => 'F44336'];
            }
        }
    }

    /**
     * Guardar archivo Excel
     */
    public function save($filename)
    {
        $writer = new Xlsx($this->spreadsheet);

        // Asegurar que el directorio existe
        $directory = dirname($filename);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $writer->save($filename);
        return $filename;
    }

    /**
     * Generar y retornar el contenido del archivo
     */
    public function output()
    {
        $writer = new Xlsx($this->spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}

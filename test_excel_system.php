<?php

// Archivo de prueba para verificar el funcionamiento del sistema de Excel mejorado
// Este script debe ejecutarse desde el directorio raíz del proyecto Laravel

// Comprobar que existe el autoloader de Composer
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "❌ Error: No se encontró el autoloader de Composer\n";
    echo "Ejecuta: composer install\n";
    exit(1);
}

require_once __DIR__ . '/vendor/autoload.php';

// Verificar que PhpSpreadsheet está instalado
try {
    $reflection = new ReflectionClass('PhpOffice\PhpSpreadsheet\Spreadsheet');
    echo "✅ PhpSpreadsheet está instalado correctamente\n";
    echo "   Ubicación: " . dirname($reflection->getFileName()) . "\n";
} catch (ReflectionException $e) {
    echo "❌ Error: PhpSpreadsheet no está instalado\n";
    echo "Ejecuta: composer require phpoffice/phpspreadsheet\n";
    exit(1);
}

// Verificar servicios de Excel
$services = [
    'app/Services/ExcelReportService.php',
    'app/Services/ExcelChartService.php'
];

foreach ($services as $service) {
    if (file_exists(__DIR__ . '/' . $service)) {
        echo "✅ Servicio encontrado: {$service}\n";
    } else {
        echo "❌ Servicio faltante: {$service}\n";
    }
}

// Verificar archivos CSS
$cssFiles = [
    'public/css/logistica/export-styles.css'
];

foreach ($cssFiles as $cssFile) {
    if (file_exists(__DIR__ . '/' . $cssFile)) {
        echo "✅ CSS encontrado: {$cssFile}\n";
    } else {
        echo "❌ CSS faltante: {$cssFile}\n";
    }
}

// Verificar rutas en web.php
$webRoutes = file_get_contents(__DIR__ . '/routes/web.php');
if (strpos($webRoutes, 'export-excel') !== false) {
    echo "✅ Ruta de Excel exportada encontrada en web.php\n";
} else {
    echo "❌ Falta agregar la ruta de Excel en web.php\n";
}

// Verificar vista de reportes
$reportesView = __DIR__ . '/resources/views/Logistica/reportes.blade.php';
if (file_exists($reportesView)) {
    $content = file_get_contents($reportesView);
    
    $checks = [
        'export-styles.css' => 'CSS de estilos de exportación',
        'exportExcelProfesional' => 'Función de exportación profesional',
        'export-dropdown' => 'Clase CSS del dropdown',
        'export-option' => 'Clases CSS de opciones'
    ];
    
    foreach ($checks as $needle => $description) {
        if (strpos($content, $needle) !== false) {
            echo "✅ {$description} encontrado en la vista\n";
        } else {
            echo "❌ {$description} faltante en la vista\n";
        }
    }
} else {
    echo "❌ Vista de reportes no encontrada\n";
}

// Crear un Excel de prueba simple
try {
    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Prueba del Sistema Excel');
    $sheet->setCellValue('A2', 'Si puedes ver esto, PhpSpreadsheet funciona correctamente');
    
    $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $testFile = __DIR__ . '/storage/app/test_excel_system.xlsx';
    
    // Crear directorio si no existe
    $dir = dirname($testFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $writer->save($testFile);
    
    if (file_exists($testFile)) {
        echo "✅ Archivo Excel de prueba creado exitosamente\n";
        echo "   Ubicación: {$testFile}\n";
        
        // Limpiar archivo de prueba
        unlink($testFile);
    }
    
} catch (Exception $e) {
    echo "❌ Error al crear Excel de prueba: " . $e->getMessage() . "\n";
}

echo "\n=== RESUMEN ===\n";
echo "Sistema de Excel Profesional para Logística\n";
echo "- Servicios: ExcelReportService, ExcelChartService\n";
echo "- Características: Gráficos avanzados, múltiples hojas, KPIs\n";
echo "- Formatos: Excel profesional, Dashboard ejecutivo, CSV mejorado\n";
echo "- UI: Dropdown moderno con efectos visuales\n";
echo "\nPara probar el sistema:\n";
echo "1. Visita la página de reportes de logística\n";
echo "2. Haz clic en 'Exportar' y selecciona 'Excel Profesional'\n";
echo "3. Verifica que se genere un archivo con gráficos y formato profesional\n";

?>
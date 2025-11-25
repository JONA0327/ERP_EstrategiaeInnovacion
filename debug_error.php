<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Logistica\OperacionLogistica;

try {
    echo "=== PROBANDO ERROR AL GUARDAR OPERACIÓN ===\n";
    
    $operacion = new OperacionLogistica([
        'operacion' => 'IMPORTACION',
        'cliente' => 'Test Cliente',
        'ejecutivo' => 'Test Ejecutivo',
        'tipo_operacion_enum' => 'Terrestre',
        'fecha_embarque' => '2025-11-20',
        'fecha_arribo_aduana' => '2025-11-23',
        'target' => 3
    ]);
    
    echo "Probando actualizarStatusAutomaticamente...\n";
    $resultado = $operacion->actualizarStatusAutomaticamente(false);
    echo "Resultado: " . json_encode($resultado) . "\n";
    echo "SUCCESS\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
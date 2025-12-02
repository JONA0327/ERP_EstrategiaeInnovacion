<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Logistica\OperacionLogisticaController;

echo "=== DEBUG ENDPOINT COMENTARIOS ===\n\n";

try {
    $controller = new OperacionLogisticaController();
    $response = $controller->obtenerHistorialComentarios(21);
    $data = $response->getData(true);
    
    echo "📊 Response completa:\n";
    print_r($data);
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
    echo "📂 Archivo: " . $e->getFile() . "\n";
}
<?php
// Script para probar el nuevo sistema de observaciones

// Simular una petición al controlador
require_once __DIR__ . '/bootstrap/app.php';

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

echo "=== PRUEBA DEL NUEVO SISTEMA DE OBSERVACIONES ===\n\n";

try {
    // Simular autenticación básica
    \Illuminate\Support\Facades\Config::set('database.default', 'mysql');
    
    // Probar directamente el método del controlador
    $controller = new \App\Http\Controllers\Logistica\OperacionLogisticaController();
    
    // Verificar que el método existe
    if (method_exists($controller, 'obtenerHistorialObservaciones')) {
        echo "✅ Método obtenerHistorialObservaciones existe\n";
    } else {
        echo "❌ Método obtenerHistorialObservaciones no existe\n";
    }
    
    if (method_exists($controller, 'updateObservacionesHistorial')) {
        echo "✅ Método updateObservacionesHistorial existe\n";
    } else {
        echo "❌ Método updateObservacionesHistorial no existe\n";
    }
    
    echo "\n=== VERIFICACIÓN DE RUTAS ===\n";
    
    // Verificar que las rutas están definidas
    $routeCollection = \Illuminate\Support\Facades\Route::getRoutes();
    $rutasObservaciones = [];
    
    foreach ($routeCollection as $route) {
        $uri = $route->uri();
        if (strpos($uri, 'observaciones') !== false) {
            $rutasObservaciones[] = $route->methods()[0] . ' ' . $uri;
        }
    }
    
    if (count($rutasObservaciones) > 0) {
        echo "✅ Rutas de observaciones encontradas:\n";
        foreach ($rutasObservaciones as $ruta) {
            echo "   - {$ruta}\n";
        }
    } else {
        echo "❌ No se encontraron rutas de observaciones\n";
    }
    
    echo "\n✅ Verificación completada\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
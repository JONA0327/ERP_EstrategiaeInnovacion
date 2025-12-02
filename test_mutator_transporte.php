<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Logistica\OperacionLogistica;

echo "=== PRUEBA DE MUTATOR DE TRANSPORTE ===\n\n";

// Buscar una operación para probar
$operacion = OperacionLogistica::find(20);

if (!$operacion) {
    echo "❌ No hay operaciones para probar\n";
    exit;
}

echo "📋 Operación de prueba: ID {$operacion->id}\n";
echo "🚚 Transporte actual: '{$operacion->transporte}'\n\n";

// Probar diferentes casos
$casosPrueba = [
    'fedex express',
    'Dhl International',
    'UPS ground',
    'MAERSK',
    'fedex GROUND',
    'Ups Express Saver'
];

foreach ($casosPrueba as $caso) {
    echo "🧪 Probando: '{$caso}' -> ";
    
    $operacion->transporte = $caso;
    echo "'{$operacion->transporte}'\n";
}

echo "\n✅ Guardando cambios en la base de datos...\n";
$operacion->transporte = 'fedex express internacional';
$operacion->save();

echo "💾 Resultado guardado: '{$operacion->fresh()->transporte}'\n";
echo "\n🎯 El mutator convierte automáticamente a mayúsculas cualquier texto que se asigne al campo transporte.\n";
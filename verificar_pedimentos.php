<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Logistica\OperacionLogistica;

echo "=== OPERACIONES CON PEDIMENTO ===\n\n";

$operaciones = OperacionLogistica::whereNotNull('no_pedimento')
    ->where('no_pedimento', '!=', '')
    ->limit(5)
    ->get(['id', 'no_pedimento', 'cliente']);

foreach ($operaciones as $op) {
    echo "🔹 ID: {$op->id} | Pedimento: {$op->no_pedimento} | Cliente: {$op->cliente}\n";
}

if ($operaciones->count() == 0) {
    echo "❌ No hay operaciones con pedimento registrado\n";
    echo "\nVamos a actualizar la operación #20 con un pedimento de prueba:\n";
    
    $op20 = OperacionLogistica::find(20);
    if ($op20) {
        $op20->update(['no_pedimento' => '25 24 6788 6738646']);
        echo "✅ Operación #20 actualizada con pedimento: 25 24 6788 6738646\n";
    }
}
<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Logistica\OperacionLogistica;
use App\Models\Logistica\OperacionComentario;

echo "=== VERIFICAR DATOS EXISTENTES ===\n\n";

echo "📊 Total operaciones: " . OperacionLogistica::count() . "\n";
echo "📝 Total comentarios: " . OperacionComentario::count() . "\n\n";

$operaciones = OperacionLogistica::limit(5)->get(['id', 'cliente', 'no_pedimento']);
echo "🔹 Operaciones existentes:\n";
foreach ($operaciones as $op) {
    echo "  - ID: {$op->id} | Cliente: {$op->cliente} | Pedimento: {$op->no_pedimento}\n";
}

$comentarios = OperacionComentario::limit(5)->get(['id', 'operacion_logistica_id', 'usuario_nombre']);
echo "\n💬 Comentarios existentes:\n";
foreach ($comentarios as $com) {
    echo "  - ID: {$com->id} | Operación: {$com->operacion_logistica_id} | Usuario: {$com->usuario_nombre}\n";
}
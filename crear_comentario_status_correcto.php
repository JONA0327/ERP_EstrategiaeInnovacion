<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Logistica\OperacionLogistica;

$operacion = OperacionLogistica::find(19);

echo "=== CREANDO COMENTARIO CON STATUS CORRECTO ===\n\n";

// Crear un nuevo comentario que refleje el status actual calculado
$nuevoComentario = $operacion->crearComentario(
    "Status actualizado: La operación está fuera de métrica - " . date('Y-m-d H:i:s'),
    'actualizacion_automatica',
    ['nombre' => 'Sistema Automático']
);

// Actualizar manualmente el status_en_momento para que refleje el status calculado
$nuevoComentario->update([
    'status_en_momento' => $operacion->status_calculado // "Out of Metric"
]);

echo "✅ Nuevo comentario creado con ID: {$nuevoComentario->id}\n";
echo "📊 Status en momento: {$nuevoComentario->status_en_momento}\n";
echo "💬 Comentario: {$nuevoComentario->comentario}\n";
echo "🎯 Tipo acción: {$nuevoComentario->tipo_accion}\n";
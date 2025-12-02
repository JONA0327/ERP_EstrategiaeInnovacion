<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Logistica\OperacionComentario;

echo "=== VERIFICACIÓN DE COMENTARIOS MÁS RECIENTES ===\n\n";

// Verificar comentarios de la operación #20
$operacionId = 20;

echo "📋 TODOS LOS COMENTARIOS DE LA OPERACIÓN #20:\n";
$todosComentarios = OperacionComentario::where('operacion_logistica_id', $operacionId)
    ->orderBy('created_at', 'desc')
    ->get();

foreach ($todosComentarios as $comentario) {
    echo "  - ID: {$comentario->id} | Usuario: {$comentario->usuario_nombre} | Fecha: {$comentario->created_at}\n";
    echo "    Comentario: " . substr($comentario->comentario, 0, 50) . "...\n\n";
}

echo "🎯 COMENTARIO MÁS RECIENTE (GENERAL):\n";
$masRecienteGeneral = OperacionComentario::where('operacion_logistica_id', $operacionId)
    ->orderBy('created_at', 'desc')
    ->first();
if ($masRecienteGeneral) {
    echo "  - ID: {$masRecienteGeneral->id} | Usuario: {$masRecienteGeneral->usuario_nombre}\n";
}

echo "\n👤 COMENTARIO MÁS RECIENTE (SOLO EJECUTIVO):\n";
$masRecienteEjecutivo = OperacionComentario::where('operacion_logistica_id', $operacionId)
    ->whereNotIn('usuario_nombre', ['Sistema', 'Sistema Automático', 'Sistema de Prueba'])
    ->orderBy('created_at', 'desc')
    ->first();
if ($masRecienteEjecutivo) {
    echo "  - ID: {$masRecienteEjecutivo->id} | Usuario: {$masRecienteEjecutivo->usuario_nombre}\n";
    echo "  - Este es el comentario que debería ser editable\n";
} else {
    echo "  - No hay comentarios del ejecutivo\n";
}
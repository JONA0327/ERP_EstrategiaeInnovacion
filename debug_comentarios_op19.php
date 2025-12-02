<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Logistica\OperacionLogistica;
use App\Models\Logistica\OperacionComentario;
use Illuminate\Support\Facades\DB;

echo "=== VERIFICACIÓN DE COMENTARIOS OPERACIÓN #19 ===\n\n";

// Obtener la operación
$op = OperacionLogistica::find(19);
if (!$op) { 
    echo "❌ Operación no encontrada\n"; 
    exit; 
}

echo "📋 Campo 'comentarios' (texto): " . substr($op->comentarios ?? 'NULL', 0, 100) . "...\n\n";

// Verificar comentarios en tabla operacion_comentarios
$comentarios = OperacionComentario::where('operacion_logistica_id', 19)
    ->orderBy('created_at', 'desc')
    ->get();

echo "📝 COMENTARIOS EN TABLA 'operacion_comentarios': " . $comentarios->count() . "\n";
foreach ($comentarios as $c) {
    echo "  - ID: {$c->id} | Acción: '{$c->accion}' | Fecha: {$c->created_at} | Usuario: '{$c->usuario}'\n";
    echo "    Comentario: " . substr($c->comentario, 0, 80) . "...\n\n";
}

// Verificar historial reciente
echo "📊 HISTORIAL RECIENTE EN 'historico_matriz_sgm':\n";
$historial = DB::table('historico_matriz_sgm')
    ->where('operacion_logistica_id', 19)
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();
    
foreach ($historial as $h) {
    echo "  - {$h->created_at} | " . substr($h->observaciones ?? 'Sin observaciones', 0, 80) . "...\n";
}

// Verificar el endpoint que usa el frontend
echo "\n🔍 SIMULANDO LLAMADA AL ENDPOINT:\n";
try {
    $comentariosEndpoint = OperacionComentario::where('operacion_logistica_id', 19)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($comentario) {
            return [
                'id' => $comentario->id,
                'accion' => $comentario->accion,
                'comentario' => $comentario->comentario,
                'usuario' => $comentario->usuario,
                'created_at' => $comentario->created_at->format('Y-m-d H:i:s'),
                'icono' => $comentario->icono_accion
            ];
        });
        
    echo "✅ Endpoint devolvería: " . $comentariosEndpoint->count() . " comentarios\n";
    foreach ($comentariosEndpoint as $c) {
        echo "  - {$c['icono']} {$c['created_at']} | {$c['accion']} | " . substr($c['comentario'], 0, 50) . "...\n";
    }
} catch (Exception $e) {
    echo "❌ Error en endpoint: " . $e->getMessage() . "\n";
}
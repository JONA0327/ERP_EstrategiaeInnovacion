<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Logistica\OperacionLogistica;
use App\Models\Logistica\OperacionComentario;

echo "=== PRUEBA DE ACTUALIZACIÓN EN TIEMPO REAL ===\n\n";

$operacion = OperacionLogistica::find(19);
if (!$operacion) {
    echo "❌ Operación no encontrada\n";
    exit;
}

echo "📋 Estado ANTES de la actualización:\n";
echo "   - Campo comentarios: " . substr($operacion->comentarios ?? 'NULL', 0, 60) . "...\n";
echo "   - Comentarios en tabla: " . $operacion->comentarios()->count() . "\n\n";

// Simular una actualización del campo comentarios
$nuevoComentario = "Mercancia actualizada - " . date('Y-m-d H:i:s');
echo "🔄 Actualizando comentarios a: $nuevoComentario\n\n";

// Actualizar directamente
$comentarioAnterior = $operacion->comentarios;
$operacion->comentarios = $nuevoComentario;

// Verificar si hay cambios para crear nuevo comentario
if ($comentarioAnterior !== $nuevoComentario) {
    echo "✅ Detectado cambio en comentario, creando nueva entrada...\n";
    
    // Usar el método crearComentario del modelo
    $nuevoRegistro = $operacion->crearComentario(
        $nuevoComentario, 
        'edicion_comentario',
        ['nombre' => 'Sistema de Prueba']
    );
    
    echo "   - Nuevo comentario ID: {$nuevoRegistro->id}\n";
}

// Guardar la operación
$operacion->save();

echo "\n📋 Estado DESPUÉS de la actualización:\n";
$operacion->refresh();
echo "   - Campo comentarios: " . substr($operacion->comentarios ?? 'NULL', 0, 60) . "...\n";
echo "   - Comentarios en tabla: " . $operacion->comentarios()->count() . "\n\n";

// Mostrar todos los comentarios
echo "📝 TODOS LOS COMENTARIOS:\n";
$todosComentarios = $operacion->comentarios()->orderBy('created_at', 'desc')->get();
foreach ($todosComentarios as $c) {
    echo "  - {$c->created_at} | {$c->accion} | " . substr($c->comentario, 0, 50) . "...\n";
}

echo "\n🔍 PRUEBA DE ENDPOINT:\n";
$comentariosEndpoint = $operacion->comentariosCronologicos;
echo "   - Endpoint devolvería: " . $comentariosEndpoint->count() . " comentarios\n";
foreach ($comentariosEndpoint as $c) {
    echo "     * {$c->icono_accion} {$c->created_at} | " . substr($c->comentario, 0, 40) . "...\n";
}
<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Logistica\OperacionComentario;

echo "=== DIAGNÓSTICO DEL COMENTARIO ID: 5 ===\n\n";

$comentario = OperacionComentario::find(5);

if (!$comentario) {
    echo "❌ Comentario ID: 5 no encontrado\n";
    exit;
}

echo "📋 Información del comentario:\n";
echo "  - ID: {$comentario->id}\n";
echo "  - Operación: {$comentario->operacion_logistica_id}\n";
echo "  - Usuario: '{$comentario->usuario_nombre}'\n";
echo "  - Tipo acción: '{$comentario->tipo_accion}'\n";
echo "  - Fecha: {$comentario->created_at}\n";
echo "  - Comentario: " . substr($comentario->comentario, 0, 100) . "...\n\n";

// Verificar si es del sistema
$esDelSistema = in_array($comentario->usuario_nombre, ['Sistema', 'Sistema Automático', 'Sistema de Prueba']);
echo "🤖 ¿Es del sistema?: " . ($esDelSistema ? 'SÍ' : 'NO') . "\n";

// Verificar cuál es el más reciente del ejecutivo
$masRecienteEjecutivo = OperacionComentario::where('operacion_logistica_id', $comentario->operacion_logistica_id)
    ->whereNotIn('usuario_nombre', ['Sistema', 'Sistema Automático', 'Sistema de Prueba'])
    ->orderBy('created_at', 'desc')
    ->first();

echo "\n👤 Comentario más reciente del ejecutivo:\n";
if ($masRecienteEjecutivo) {
    echo "  - ID: {$masRecienteEjecutivo->id}\n";
    echo "  - Usuario: {$masRecienteEjecutivo->usuario_nombre}\n";
    echo "  - ¿Es el ID 5?: " . ($masRecienteEjecutivo->id == 5 ? 'SÍ' : 'NO') . "\n";
} else {
    echo "  - No hay comentarios del ejecutivo\n";
}

// Simular la validación del controlador
echo "\n🔍 Simulación de validación:\n";
if ($esDelSistema) {
    echo "❌ ERROR 403: No se pueden editar comentarios del sistema\n";
} else {
    echo "✅ VALIDACIÓN: Comentario del ejecutivo, editable\n";
}

echo "\n💡 Diagnóstico:\n";
if ($esDelSistema) {
    echo "El error 403 es porque el comentario es del sistema y no debería ser editable.\n";
    echo "Esto indica que el frontend está tratando de editar un comentario incorrecto.\n";
} else {
    echo "El comentario debería ser editable. El error puede ser por otra causa.\n";
}
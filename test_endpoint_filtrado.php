<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Probar con la operación #20 que aparece en la imagen
use App\Http\Controllers\Logistica\OperacionLogisticaController;

echo "=== PRUEBA DEL ENDPOINT FILTRADO - OPERACIÓN #20 ===\n\n";

$controller = new OperacionLogisticaController();
$response = $controller->obtenerHistorialComentarios(20);
$data = $response->getData(true);

echo "✅ Respuesta del endpoint:\n";
echo "📊 Success: " . ($data['success'] ? 'true' : 'false') . "\n";
echo "📝 Total comentarios (después del filtro): " . count($data['comentarios']) . "\n";
echo "🔢 Pedimento: " . ($data['operacion']['no_pedimento'] ?? 'Sin pedimento') . "\n\n";

if (isset($data['comentarios']) && is_array($data['comentarios'])) {
    foreach ($data['comentarios'] as $comentario) {
        echo "🔸 ID: {$comentario['id']}\n";
        echo "   📅 Fecha: {$comentario['fecha_formateada']}\n";
        echo "   🎯 Tipo: {$comentario['tipo_accion']}\n";
        echo "   🎨 Ícono: {$comentario['icono_accion']}\n";
        echo "   👤 Usuario: {$comentario['usuario_nombre']}\n";
        echo "   💬 Comentario: " . substr($comentario['comentario'], 0, 60) . "...\n";
        echo "   📊 Status: {$comentario['status_en_momento']}\n\n";
    }
} else {
    echo "❌ No hay comentarios o estructura incorrecta\n";
}

echo "🔗 Este resultado debe mostrar solo comentarios del ejecutivo, sin los del Sistema.\n";
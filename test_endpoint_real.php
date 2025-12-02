<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simular la respuesta del controlador
use App\Http\Controllers\Logistica\OperacionLogisticaController;
use Illuminate\Http\Request;

echo "=== PRUEBA DEL ENDPOINT REAL ===\n\n";

$controller = new OperacionLogisticaController();
$response = $controller->obtenerHistorialComentarios(19);
$data = $response->getData(true);

echo "✅ Respuesta del endpoint:\n";
echo "📊 Success: " . ($data['success'] ? 'true' : 'false') . "\n";
echo "📝 Total comentarios: " . count($data['comentarios']) . "\n\n";

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

echo "🔗 URL del endpoint: /logistica/operaciones/19/comentarios-historial\n";
echo "\nEsta es la respuesta exacta que recibe el frontend JavaScript.\n";
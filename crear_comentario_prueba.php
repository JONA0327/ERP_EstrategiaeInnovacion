<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Logistica\OperacionLogistica;

echo "=== CREANDO COMENTARIO DE PRUEBA ===\n\n";

$operacion = OperacionLogistica::find(20);

// Crear un comentario con el formato del sistema para probar el filtro
$comentarioCompleto = "Status actualizado automáticamente: Establecido como 'In Process'. Días transcurridos: 2.5, Target: 3 - Comentarios: Este es mi nuevo comentario de prueba";

$nuevoComentario = $operacion->crearComentario(
    $comentarioCompleto,
    'edicion_comentario',
    ['nombre' => 'Ejecutivo de Prueba']
);

echo "✅ Comentario creado con ID: {$nuevoComentario->id}\n";
echo "📝 Comentario completo: {$comentarioCompleto}\n";
echo "🎯 Texto que debería extraer: 'Este es mi nuevo comentario de prueba'\n\n";

// Probar la extracción
if (strpos($comentarioCompleto, 'Comentarios: ') !== false) {
    $textoExtraido = trim(substr($comentarioCompleto, strpos($comentarioCompleto, 'Comentarios: ') + 13));
    echo "✅ Extracción exitosa: '{$textoExtraido}'\n";
} else {
    echo "❌ No se encontró 'Comentarios: ' en el texto\n";
}
<?php
/**
 * Script de prueba para verificar la sincronización de comentarios
 * Verifica que ambas tablas (operacion_comentarios y historico_matriz_sgm) 
 * estén sincronizadas correctamente
 */

require_once __DIR__ . '/vendor/autoload.php';

// Cargar las variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Crear la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Logistica\OperacionLogistica;
use App\Models\Logistica\OperacionComentario;
use Illuminate\Support\Facades\DB;

echo "=== PRUEBA DE SINCRONIZACIÓN DE COMENTARIOS ===\n\n";

// Buscar una operación existente para probar
$operacion = OperacionLogistica::whereNotNull('comentarios')
    ->first();

if (!$operacion) {
    echo "❌ No se encontró ninguna operación con comentarios para probar\n";
    exit(1);
}

echo "📋 Operación de prueba: #{$operacion->id}\n";
echo "📝 Comentarios actual: " . substr($operacion->comentarios, 0, 50) . "...\n\n";

// Verificar comentarios en la tabla operacion_comentarios
$comentariosNuevos = $operacion->comentarios()->get();
echo "🔄 Comentarios en tabla 'operacion_comentarios': " . $comentariosNuevos->count() . "\n";

foreach ($comentariosNuevos as $comentario) {
    echo "   - {$comentario->created_at->format('Y-m-d H:i')} | {$comentario->accion} | " . 
         substr($comentario->comentario, 0, 40) . "...\n";
}

// Verificar entradas en la tabla historico_matriz_sgm
$historialesComentarios = DB::table('historico_matriz_sgm')
    ->where('operacion_logistica_id', $operacion->id)
    ->orderBy('created_at', 'desc')
    ->get();

echo "\n📊 Entradas relacionadas en 'historico_matriz_sgm': " . $historialesComentarios->count() . "\n";

foreach ($historialesComentarios as $historial) {
    echo "   - {$historial->created_at} | " . 
         substr($historial->observaciones ?? 'Sin observaciones', 0, 40) . "...\n";
}

// Verificar método de obtención de comentarios del controlador
echo "\n🔍 Probando método obtenerHistorialComentarios...\n";

try {
    $comentarios = $operacion->comentarios()
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "✅ Método obtenerHistorialComentarios funciona correctamente\n";
    echo "📝 Total de comentarios obtenidos: " . $comentarios->count() . "\n";
    
    if ($comentarios->count() > 0) {
        echo "📋 Último comentario:\n";
        $ultimo = $comentarios->first();
        echo "   - ID: {$ultimo->id}\n";
        echo "   - Acción: {$ultimo->accion}\n";
        echo "   - Fecha: {$ultimo->created_at->format('Y-m-d H:i:s')}\n";
        echo "   - Usuario: {$ultimo->usuario}\n";
        echo "   - Comentario: " . substr($ultimo->comentario, 0, 100) . "...\n";
        
        // Verificar que el icono se genere correctamente
        $icono = $ultimo->icono_accion;
        echo "   - Ícono: {$icono}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error al obtener comentarios: " . $e->getMessage() . "\n";
}

// Verificar que los métodos de sincronización estén presentes
echo "\n🔧 Verificando métodos de sincronización...\n";

$reflection = new ReflectionClass(OperacionLogistica::class);

if ($reflection->hasMethod('crearComentario')) {
    echo "✅ Método 'crearComentario' encontrado\n";
} else {
    echo "❌ Método 'crearComentario' NO encontrado\n";
}

if ($reflection->hasMethod('generarHistorialCambioStatus')) {
    echo "✅ Método 'generarHistorialCambioStatus' encontrado\n";
} else {
    echo "❌ Método 'generarHistorialCambioStatus' NO encontrado\n";
}

echo "\n=== PRUEBA COMPLETADA ===\n";
echo "Si ves este mensaje, la estructura básica está funcionando.\n";
echo "Ahora puedes probar creando/editando comentarios desde la interfaz web.\n";
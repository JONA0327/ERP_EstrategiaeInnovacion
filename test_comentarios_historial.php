<?php

require_once 'vendor/autoload.php';

use App\Models\Logistica\OperacionLogistica;
use App\Models\Logistica\OperacionComentario;

// Configurar conexión a la base de datos
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PRUEBA DEL SISTEMA DE COMENTARIOS CON HISTORIAL ===\n\n";

try {
    // Buscar una operación existente
    $operacion = OperacionLogistica::first();
    
    if (!$operacion) {
        echo "❌ No se encontraron operaciones en la base de datos.\n";
        exit(1);
    }
    
    echo "✅ Operación encontrada: {$operacion->operacion}\n";
    echo "   Cliente: {$operacion->cliente}\n";
    echo "   Status actual: {$operacion->status_actual}\n\n";
    
    // Crear un comentario de prueba usando el nuevo sistema
    echo "🔄 Creando comentario de prueba...\n";
    
    $comentario = $operacion->crearComentario(
        'Comentario de prueba para verificar el sistema de historial',
        'Prueba manual',
        'Sistema de Pruebas',
        1 // ID usuario de prueba
    );
    
    if ($comentario) {
        echo "✅ Comentario creado exitosamente con ID: {$comentario->id}\n\n";
        
        // Verificar el historial
        echo "🔍 Verificando historial de comentarios:\n";
        $historial = $operacion->comentariosCronologicos;
        
        echo "   Total de comentarios: " . $historial->count() . "\n\n";
        
        foreach ($historial as $index => $comentarioHistorial) {
            echo "   Comentario #" . ($index + 1) . ":\n";
            echo "   - ID: {$comentarioHistorial->id}\n";
            echo "   - Texto: {$comentarioHistorial->comentario}\n";
            echo "   - Tipo: {$comentarioHistorial->tipo_accion}\n";
            echo "   - Usuario: {$comentarioHistorial->usuario_nombre}\n";
            echo "   - Status en momento: {$comentarioHistorial->status_en_momento}\n";
            echo "   - Fecha: {$comentarioHistorial->fecha_formateada}\n\n";
        }
        
        // Probar la función del controlador
        echo "🔄 Probando método del controlador...\n";
        
        $controller = new \App\Http\Controllers\Logistica\OperacionLogisticaController();
        $response = $controller->obtenerHistorialComentarios($operacion->id);
        
        $responseData = json_decode($response->getContent(), true);
        
        if ($responseData['success']) {
            echo "✅ Método del controlador funciona correctamente\n";
            echo "   Comentarios retornados: " . count($responseData['comentarios']) . "\n";
        } else {
            echo "❌ Error en método del controlador: {$responseData['message']}\n";
        }
        
    } else {
        echo "❌ Error al crear comentario\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error durante la prueba: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . " línea " . $e->getLine() . "\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";
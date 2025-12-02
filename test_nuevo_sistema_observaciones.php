<?php
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Configuración de la base de datos
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'sistematicket',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== TEST NUEVO SISTEMA DE OBSERVACIONES ===\n\n";

try {
    // 1. Verificar que existe la tabla operaciones_logisticas
    $operacionTest = Capsule::table('operaciones_logisticas')->first();
    if (!$operacionTest) {
        echo "❌ No hay operaciones logísticas en la tabla\n";
        exit;
    }
    
    $operacionId = $operacionTest->id;
    echo "✅ Usando operación ID: {$operacionId}\n";
    echo "   Operación: {$operacionTest->operacion}\n";
    echo "   Comentarios actuales: " . ($operacionTest->comentarios ?? 'N/A') . "\n\n";
    
    // 2. Verificar historial existente
    $historialExistente = Capsule::table('historico_matriz_sgm')
        ->where('operacion_logistica_id', $operacionId)
        ->whereNotNull('observaciones')
        ->where('observaciones', '!=', '')
        ->orderBy('created_at', 'desc')
        ->get();
        
    echo "📋 Historial de observaciones existente: " . count($historialExistente) . " registros\n";
    foreach ($historialExistente as $registro) {
        echo "   - " . $registro->observaciones . " (Status: {$registro->status})\n";
    }
    echo "\n";
    
    // 3. Simular creación de nueva observación
    $nuevaObservacion = "Observación de prueba del nuevo sistema - " . date('Y-m-d H:i:s');
    
    // Primero actualizar en la operación
    Capsule::table('operaciones_logisticas')
        ->where('id', $operacionId)
        ->update(['comentarios' => $nuevaObservacion]);
        
    echo "✅ Comentarios actualizados en operación principal\n";
    
    // Obtener el historial más reciente
    $historialReciente = Capsule::table('historico_matriz_sgm')
        ->where('operacion_logistica_id', $operacionId)
        ->orderBy('created_at', 'desc')
        ->first();
        
    if ($historialReciente) {
        // Actualizar las observaciones en el historial más reciente
        Capsule::table('historico_matriz_sgm')
            ->where('id', $historialReciente->id)
            ->update(['observaciones' => $nuevaObservacion]);
            
        echo "✅ Observaciones actualizadas en historial (ID: {$historialReciente->id})\n";
    } else {
        echo "❌ No se encontró historial reciente para actualizar\n";
    }
    
    // 4. Verificar que los cambios se aplicaron correctamente
    $operacionActualizada = Capsule::table('operaciones_logisticas')
        ->where('id', $operacionId)
        ->first();
        
    $historialActualizado = Capsule::table('historico_matriz_sgm')
        ->where('operacion_logistica_id', $operacionId)
        ->orderBy('created_at', 'desc')
        ->first();
        
    echo "\n=== VERIFICACIÓN FINAL ===\n";
    echo "Comentarios en operación: " . $operacionActualizada->comentarios . "\n";
    echo "Observaciones en historial: " . ($historialActualizado->observaciones ?? 'N/A') . "\n";
    
    if ($operacionActualizada->comentarios === $historialActualizado->observaciones) {
        echo "✅ SINCRONIZACIÓN CORRECTA: Los datos coinciden\n";
    } else {
        echo "❌ ERROR DE SINCRONIZACIÓN: Los datos no coinciden\n";
    }
    
    // 5. Simular obtención de historial completo como lo haría la API
    echo "\n=== SIMULACIÓN DE API obtenerHistorialObservaciones ===\n";
    $historialCompleto = Capsule::table('historico_matriz_sgm as h')
        ->leftJoin('empleados as e', 'h.empleado_id', '=', 'e.id')
        ->where('h.operacion_logistica_id', $operacionId)
        ->whereNotNull('h.observaciones')
        ->where('h.observaciones', '!=', '')
        ->select([
            'h.id',
            'h.observaciones',
            'h.status',
            'h.created_at',
            'h.updated_at',
            'e.nombre as empleado_nombre'
        ])
        ->orderBy('h.created_at', 'asc')
        ->get();
        
    echo "Registros encontrados: " . count($historialCompleto) . "\n";
    foreach ($historialCompleto as $reg) {
        $empleado = $reg->empleado_nombre ?? 'Sistema';
        $fecha = date('d/m/Y H:i', strtotime($reg->created_at));
        echo "- [{$fecha}] {$empleado}: {$reg->observaciones}\n";
    }
    
    echo "\n✅ Test completado exitosamente\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
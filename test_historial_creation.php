<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->boot();

use App\Models\Logistica\OperacionLogistica;
use App\Models\Logistica\HistoricoMatrizSgm;

echo "=== PRUEBA DE CREACIÓN DE HISTORIAL AUTOMÁTICO ===\n";

// Buscar una operación de prueba
$operacion = OperacionLogistica::first();

if (!$operacion) {
    echo "❌ No hay operaciones en la base de datos\n";
    exit;
}

echo "📋 Operación encontrada: ID {$operacion->id}\n";
echo "   Cliente: {$operacion->cliente}\n";
echo "   No Pedimento: {$operacion->no_pedimento}\n";

// Verificar si tiene historial
$historialCount = $operacion->historicoMatrizSgm()->count();
echo "📊 Registros de historial: {$historialCount}\n";

if ($historialCount === 0) {
    echo "⚠️  Esta operación no tiene historial. Debería generarse automáticamente.\n";
} else {
    echo "✅ Esta operación ya tiene historial.\n";
    
    // Mostrar el historial
    $historial = $operacion->historicoMatrizSgm()->orderBy('created_at', 'desc')->get();
    foreach ($historial as $registro) {
        echo "   📅 {$registro->created_at->format('d/m/Y H:i')} - {$registro->operacion_status} - {$registro->observaciones}\n";
    }
}

// Buscar operaciones del mismo cliente
$operacionesCliente = OperacionLogistica::where('cliente', $operacion->cliente)
    ->with('historicoMatrizSgm')
    ->get();

echo "\n🔍 Operaciones del cliente '{$operacion->cliente}':\n";
foreach ($operacionesCliente as $op) {
    $histCount = $op->historicoMatrizSgm->count();
    echo "   ID {$op->id} - {$op->operacion} - No Ped: {$op->no_pedimento} - Historial: {$histCount} registros\n";
}

echo "\n✅ Prueba completada\n";
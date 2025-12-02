<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Logistica\OperacionLogistica;

$operacion = OperacionLogistica::find(19);

echo "=== STATUS ACTUAL OPERACIÓN #19 ===\n\n";
echo "📊 Status Calculado: " . ($operacion->status_calculado ?? 'NULL') . "\n";
echo "👤 Status Manual: " . ($operacion->status_manual ?? 'NULL') . "\n";
echo "🎯 Status Actual (Accessor): " . ($operacion->status_actual ?? 'NULL') . "\n";
echo "🎨 Color Status: " . ($operacion->color_status ?? 'NULL') . "\n";
echo "📅 Target: " . ($operacion->target ?? 'NULL') . "\n";
echo "📈 Resultado: " . ($operacion->resultado ?? 'NULL') . "\n";
echo "⏱️ Días transcurridos: " . ($operacion->dias_transcurridos_calculados ?? 'NULL') . "\n\n";

// Forzar recalculo del status
echo "🔄 Recalculando status...\n";
$resultado = $operacion->calcularStatusPorDias();
echo "📊 Nuevo status calculado: {$resultado['status']}\n";
echo "🎨 Nuevo color: {$resultado['color']}\n";
echo "📈 Días: {$resultado['dias_transcurridos']}\n\n";

// Actualizar y guardar
$operacion->status_calculado = $resultado['status'];
$operacion->color_status = $resultado['color'];
$operacion->dias_transcurridos_calculados = $resultado['dias_transcurridos'];
$operacion->save();

echo "✅ Status actualizado en base de datos\n";
echo "🎯 Status final: " . $operacion->status_actual . "\n";
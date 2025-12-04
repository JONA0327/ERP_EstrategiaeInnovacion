<?php
// Script para actualizar el estado del pedimento A1

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Logistica\Pedimento;

echo "=== ACTUALIZANDO ESTADO DE PEDIMENTO A1 ===\n";

// Buscar el pedimento A1
$pedimentoA1 = Pedimento::where('clave', 'A1')->first();

if ($pedimentoA1) {
    echo "Pedimento A1 encontrado:\n";
    echo "- Estado actual: {$pedimentoA1->estado_pago}\n";
    echo "- Categoría actual: {$pedimentoA1->categoria}\n";
    
    // Actualizar el estado
    $pedimentoA1->estado_pago = 'por_pagar';
    $pedimentoA1->save();
    
    echo "- Estado actualizado a: por_pagar\n";
    echo "✅ Actualización completada\n";
} else {
    echo "❌ No se encontró el pedimento A1\n";
}

// Verificar el resultado
echo "\n=== VERIFICACIÓN FINAL ===\n";
$pedimentoA1 = Pedimento::where('clave', 'A1')->first();
if ($pedimentoA1) {
    echo "Estado final de A1: {$pedimentoA1->estado_pago}\n";
}
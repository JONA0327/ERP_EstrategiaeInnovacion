<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Logistica\OperacionLogistica;
use App\Models\Logistica\Pedimento;

echo "=== ANÁLISIS DETALLADO DE PEDIMENTOS ===\n";

// 1. Operaciones únicas por no_pedimento
echo "\n1. Operaciones por no_pedimento (pueden ser duplicados):\n";
$operaciones = OperacionLogistica::whereNotNull('no_pedimento')
    ->where('no_pedimento', '!=', '')
    ->select('id', 'no_pedimento', 'clave', 'cliente')
    ->get();

echo "Total operaciones con pedimento: " . $operaciones->count() . "\n";
foreach($operaciones as $op) {
    echo "- Op#{$op->id}: {$op->no_pedimento} (clave: {$op->clave})\n";
}

// 2. Pedimentos únicos (sin duplicados)
echo "\n2. Pedimentos únicos:\n";
$pedimentosUnicos = OperacionLogistica::whereNotNull('no_pedimento')
    ->where('no_pedimento', '!=', '')
    ->pluck('no_pedimento')
    ->unique();

echo "Total pedimentos únicos: " . $pedimentosUnicos->count() . "\n";
foreach($pedimentosUnicos as $ped) {
    echo "- {$ped}\n";
}

// 3. Verificar si hay duplicados en clave
echo "\n3. Verificar duplicados por clave:\n";
$duplicados = OperacionLogistica::whereNotNull('no_pedimento')
    ->where('no_pedimento', '!=', '')
    ->select('no_pedimento', 'clave', \DB::raw('COUNT(*) as count'))
    ->groupBy('no_pedimento', 'clave')
    ->having('count', '>', 1)
    ->get();

if($duplicados->count() > 0) {
    echo "❌ Pedimentos duplicados encontrados:\n";
    foreach($duplicados as $dup) {
        echo "- {$dup->no_pedimento} aparece {$dup->count} veces\n";
    }
} else {
    echo "✅ No hay duplicados en la tabla de operaciones\n";
}

// 4. Estados de los registros en tabla pedimentos
echo "\n4. Estados de pedimentos reales:\n";
foreach($pedimentosUnicos as $pedimento) {
    $registro = Pedimento::where('clave', $pedimento)->first();
    if($registro) {
        echo "- {$pedimento}: {$registro->estado_pago}\n";
    } else {
        echo "- {$pedimento}: SIN REGISTRO\n";
    }
}

echo "\n=== ANÁLISIS COMPLETADO ===\n";
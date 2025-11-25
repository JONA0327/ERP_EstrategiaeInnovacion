<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Logistica\OperacionLogistica;
use Carbon\Carbon;

echo "=== PRUEBA DE NUEVA LÓGICA CORPORATIVA ===\n\n";

// Escenarios de prueba según el nuevo flujo
$escenarios = [
    [
        'nombre' => 'Operación recién creada (Fase 1) - Solo datos base',
        'dias_registro' => 1,
        'target' => 3,
        'con_arribo_aduana' => false,
        'esperado_status' => 'En Proceso',
        'esperado_color' => 'amarillo'
    ],
    [
        'nombre' => 'Operación dentro target con arribo aduana (Fase 2)',
        'dias_registro' => 2,
        'target' => 3,
        'con_arribo_aduana' => true,
        'esperado_status' => 'En Proceso',
        'esperado_color' => 'amarillo'
    ],
    [
        'nombre' => 'Operación fuera de target con arribo aduana',
        'dias_registro' => 5,
        'target' => 3,
        'con_arribo_aduana' => true,
        'esperado_status' => 'Fuera de Métrica',
        'esperado_color' => 'rojo'
    ],
    [
        'nombre' => 'Operación completada (Fase 3) - Con fecha arribo planta',
        'dias_registro' => 4,
        'target' => 3,
        'con_arribo_aduana' => true,
        'con_arribo_planta' => true,
        'esperado_status' => 'Done',
        'esperado_color' => 'verde'
    ]
];

foreach ($escenarios as $i => $escenario) {
    echo "Escenario " . ($i+1) . ": {$escenario['nombre']}\n";
    echo "----------------------------------------\n";
    
    $operacionTest = new OperacionLogistica([
        'operacion' => 'IMPORTACION',
        'cliente' => 'Cliente Test',
        'ejecutivo' => 'Ejecutivo Test',
        'tipo_operacion_enum' => 'Terrestre',
        'fecha_embarque' => Carbon::now()->subDays($escenario['dias_registro']),
        'target' => $escenario['target'],
        'no_pedimento' => 'TEST-' . ($i+1),
        'proveedor_o_cliente' => 'Proveedor Test',
        'no_factura' => 'FAC-001',
        'clave' => 'CLAVE-001',
        'referencia_interna' => 'REF-001',
        'aduana' => 'ADUANA-001',
        'agente_aduanal' => 'AGENTE-001'
    ]);
    
    if ($escenario['con_arribo_aduana']) {
        $operacionTest->fecha_arribo_aduana = Carbon::now()->subDays($escenario['dias_registro'] - 1);
    }
    
    if (isset($escenario['con_arribo_planta']) && $escenario['con_arribo_planta']) {
        $operacionTest->fecha_arribo_planta = Carbon::now();
    }
    
    $operacionTest->created_at = Carbon::now()->subDays($escenario['dias_registro']);
    $operacionTest->updated_at = Carbon::now()->subDays($escenario['dias_registro']);
    
    $resultado = $operacionTest->calcularStatusPorDias();
    
    echo "Configuración:\n";
    echo "  - Días desde registro: {$escenario['dias_registro']}\n";
    echo "  - Target: {$escenario['target']} días\n";
    echo "  - Con arribo aduana: " . ($escenario['con_arribo_aduana'] ? 'SÍ' : 'NO') . "\n";
    if (isset($escenario['con_arribo_planta'])) {
        echo "  - Con arribo planta: " . ($escenario['con_arribo_planta'] ? 'SÍ' : 'NO') . "\n";
    }
    
    echo "\nResultado:\n";
    echo "  - Status calculado: {$resultado['status']} (Color: {$resultado['color']})\n";
    echo "  - Días transcurridos: {$resultado['dias_transcurridos']}\n";
    echo "  - Esperado: {$escenario['esperado_status']} ({$escenario['esperado_color']})\n";
    
    $correcto = ($resultado['status'] === $escenario['esperado_status'] && 
                 $resultado['color'] === $escenario['esperado_color']);
    
    echo "  - ✓ " . ($correcto ? 'CORRECTO' : 'ERROR') . "\n\n";
}

echo "=== RESUMEN FLUJO CORPORATIVO ===\n";
echo "FASE 1 - Creación: Solo datos base obligatorios (12 campos)\n";
echo "FASE 2 - Seguimiento: Se agregan fechas de proceso\n"; 
echo "FASE 3 - Cierre: Se completan fechas finales\n";
echo "\nCalculos automáticos por días desde registro vs target\n";
echo "Estado sin arribo aduana = En Proceso (amarillo)\n";
echo "Estado con arribo planta = Done (verde)\n";
echo "=== FIN DE PRUEBAS ===\n";
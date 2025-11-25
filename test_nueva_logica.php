<?php

require_once 'vendor/autoload.php';

// Configurar la aplicación Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Logistica\OperacionLogistica;
use Carbon\Carbon;

echo "=== PRUEBA DE NUEVA LÓGICA DE CÁLCULO DE STATUS ===\n\n";

// Crear operación de prueba
echo "1. Creando operación de prueba...\n";

$operacionPrueba = new OperacionLogistica([
    'operacion' => 'IMPORTACION',
    'cliente' => 'Cliente Test',
    'ejecutivo' => 'Ejecutivo Test',
    'tipo_operacion_enum' => 'Terrestre',
    'fecha_embarque' => Carbon::now()->subDays(5),
    'fecha_arribo_aduana' => Carbon::now()->subDays(2),
    'target' => 3, // Target de 3 días
    'no_pedimento' => 'TEST-001'
]);

// Simular que fue creada hace 4 días
$operacionPrueba->created_at = Carbon::now()->subDays(4);
$operacionPrueba->updated_at = Carbon::now()->subDays(4);

echo "Operación creada:\n";
echo "- Fecha registro: " . $operacionPrueba->created_at->format('Y-m-d H:i:s') . "\n";
echo "- Fecha embarque: " . $operacionPrueba->fecha_embarque->format('Y-m-d') . "\n";
echo "- Fecha arribo aduana: " . $operacionPrueba->fecha_arribo_aduana->format('Y-m-d') . "\n";
echo "- Target: {$operacionPrueba->target} días\n";
echo "- Fecha actual: " . Carbon::now()->format('Y-m-d H:i:s') . "\n\n";

// Probar el nuevo cálculo
echo "2. Probando nueva lógica de cálculo...\n";

$resultado = $operacionPrueba->calcularStatusPorDias();

echo "Resultado del cálculo:\n";
echo "- Status: {$resultado['status']}\n";
echo "- Color: {$resultado['color']}\n";
echo "- Días transcurridos: {$resultado['dias_transcurridos']}\n";
echo "- Target: {$resultado['target']}\n";
echo "- Hubo cambio: " . ($resultado['cambio'] ? 'SÍ' : 'NO') . "\n\n";

// Simular diferentes escenarios
echo "3. Simulando diferentes escenarios...\n\n";

$escenarios = [
    [
        'nombre' => 'Operación DENTRO del target (1 día)',
        'dias_registro' => 1,
        'target' => 3,
        'esperado' => 'amarillo'
    ],
    [
        'nombre' => 'Operación FUERA del target (5 días)',
        'dias_registro' => 5,
        'target' => 3,
        'esperado' => 'rojo'
    ],
    [
        'nombre' => 'Operación SIN fecha arribo (nueva)',
        'dias_registro' => 1,
        'target' => 3,
        'sin_arribo' => true,
        'esperado' => 'sin_fecha'
    ]
];

foreach ($escenarios as $i => $escenario) {
    echo "Escenario " . ($i+1) . ": {$escenario['nombre']}\n";
    
    $operacionTest = new OperacionLogistica([
        'operacion' => 'IMPORTACION',
        'cliente' => 'Cliente Test',
        'ejecutivo' => 'Ejecutivo Test',
        'tipo_operacion_enum' => 'Terrestre',
        'fecha_embarque' => Carbon::now()->subDays($escenario['dias_registro']),
        'target' => $escenario['target'],
        'no_pedimento' => 'TEST-' . ($i+2)
    ]);
    
    if (!isset($escenario['sin_arribo'])) {
        $operacionTest->fecha_arribo_aduana = Carbon::now()->subDays($escenario['dias_registro'] - 1);
    }
    
    $operacionTest->created_at = Carbon::now()->subDays($escenario['dias_registro']);
    $operacionTest->updated_at = Carbon::now()->subDays($escenario['dias_registro']);
    
    $resultadoTest = $operacionTest->calcularStatusPorDias();
    
    echo "  - Status calculado: {$resultadoTest['status']} (Color: {$resultadoTest['color']})\n";
    echo "  - Días transcurridos: {$resultadoTest['dias_transcurridos']}\n";
    echo "  - Esperado: {$escenario['esperado']}\n";
    echo "  - ✓ " . ($resultadoTest['color'] === $escenario['esperado'] ? 'CORRECTO' : 'ERROR') . "\n\n";
}

// Probar generación de historial
echo "4. Probando generación de historial...\n";

try {
    $operacionPrueba->generarHistorialCambioStatus($resultado);
    echo "✓ Historial generado correctamente\n";
} catch (Exception $e) {
    echo "✗ Error generando historial: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DE PRUEBAS ===\n";
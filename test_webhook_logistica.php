<?php
// Test del nuevo webhook de logística

echo "=== TEST WEBHOOK LOGÍSTICA ===\n\n";

$webhookUrl = 'https://n8n.autodevsystems.com/webhook-test/erp_estrategiaeinnovacion';

// Datos de prueba similares a los que se envían realmente
$payload = [
    'tipo' => 'reporte_logistica',
    'timestamp' => date('c'),
    'email' => [
        'destinatarios' => ['test@ejemplo.com', 'otro@ejemplo.com'],
        'correos_cc' => ['cc@ejemplo.com'],
        'asunto' => 'Reporte de Prueba - Logística',
        'mensaje' => 'Este es un mensaje de prueba del sistema de logística.',
        'remitente' => 'sistemas@estrategiaeinnovacion.com.mx',
        'nombre_remitente' => 'Sistema de Logística - TEST'
    ],
    'datos_adicionales' => [
        'incluir_datos' => true,
        'formato_datos' => 'csv',
        'operaciones_ids' => [1, 2, 3],
        'usuario_envio' => [
            'id' => 1,
            'name' => 'Usuario de Prueba',
            'email' => 'test@test.com'
        ]
    ],
    'archivo' => [
        'nombre' => 'reporte_test.csv',
        'mime_type' => 'text/csv',
        'size' => 1024,
        'contenido_base64' => base64_encode('Contenido,de,prueba\n1,2,3\n4,5,6')
    ]
];

echo "Enviando payload al webhook: {$webhookUrl}\n\n";
echo "Datos a enviar:\n";
echo json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

// Usar cURL para enviar la petición
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhookUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen(json_encode($payload))
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "=== RESULTADO ===\n";
echo "Código HTTP: {$httpCode}\n";

if ($error) {
    echo "❌ Error cURL: {$error}\n";
} else {
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "✅ Webhook enviado exitosamente\n";
    } else {
        echo "❌ Error HTTP: {$httpCode}\n";
    }
    
    echo "Respuesta del servidor:\n";
    echo $response . "\n";
}

echo "\n=== TEST COMPLETADO ===\n";
<?php
// public_html/api/webhook.php
error_log('[API/WEBHOOK] ========== Webhook recibido ==========');
error_log('[API/WEBHOOK] Método: ' . $_SERVER['REQUEST_METHOD']);

require_once __DIR__ . '/../../private/controllers/WebhookController.php';

$input = file_get_contents('php://input');
error_log('[API/WEBHOOK] Raw input: ' . $input);

$data = json_decode($input, true);
error_log('[API/WEBHOOK] JSON decodificado: ' . ($data ? json_encode($data) : 'FALLÓ - ' . json_last_error_msg()));

if ($data) {
    error_log('[API/WEBHOOK] Procesando webhook con WebhookController...');
    $controller = new WebhookController();
    $result = $controller->handleWebhook($data);
    error_log('[API/WEBHOOK] Resultado del controller: ' . ($result ? 'true' : 'false'));
} else {
    error_log('[API/WEBHOOK] ⚠️ No se recibieron datos válidos');
}

error_log('[API/WEBHOOK] Respondiendo 200 OK');
http_response_code(200);
echo "OK";
?>

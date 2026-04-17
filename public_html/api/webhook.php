<?php
// public_html/api/webhook.php

require_once __DIR__ . '/../../private/controllers/WebhookController.php';

$input = file_get_contents('php://input');

$data = json_decode($input, true);

if ($data) {
    $controller = new WebhookController();
    $result = $controller->handleWebhook($data);
} else {
}

http_response_code(200);
echo "OK";
?>

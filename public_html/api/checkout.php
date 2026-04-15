<?php
// public_html/api/checkout.php
header('Content-Type: application/json');

require_once __DIR__ . '/../../private/controllers/CheckoutController.php';

error_log('[API/CHECKOUT] ========== Nueva petición ==========');
error_log('[API/CHECKOUT] Método: ' . $_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    error_log('[API/CHECKOUT] Raw input recibido: ' . $input);
    
    $data = json_decode($input, true);
    error_log('[API/CHECKOUT] JSON decodificado: ' . ($data ? 'OK' : 'FALLÓ - json_last_error: ' . json_last_error_msg()));

    if (isset($data['customer']) && isset($data['cart'])) {
        error_log('[API/CHECKOUT] Datos válidos - customer: ' . json_encode($data['customer']));
        error_log('[API/CHECKOUT] Datos válidos - cart items: ' . count($data['cart']));
        
        $controller = new CheckoutController();
        $result = $controller->processCheckout($data['customer'], $data['cart']);
        
        error_log('[API/CHECKOUT] Resultado del controller: ' . json_encode($result));
        echo json_encode($result);
        exit;
    }

    error_log('[API/CHECKOUT] Formato de datos inválido');
    echo json_encode(['success' => false, 'message' => 'Invalid data format']);
    exit;
}

error_log('[API/CHECKOUT] Método no permitido: ' . $_SERVER['REQUEST_METHOD']);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
?>

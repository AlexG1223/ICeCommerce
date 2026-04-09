<?php
// public_html/api/checkout.php
header('Content-Type: application/json');

require_once __DIR__ . '/../../private/controllers/CheckoutController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (isset($data['customer']) && isset($data['cart'])) {
        $controller = new CheckoutController();
        $result = $controller->processCheckout($data['customer'], $data['cart']);
        echo json_encode($result);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid data format']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Method not allowed']);
?>

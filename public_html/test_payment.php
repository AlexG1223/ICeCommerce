<?php
/**
 * SCRIPT DE PRUEBA DE NOTIFICACIÓN DE PAGO
 * Simula una compra exitosa sin pasar por Mercado Pago ni gastar dinero.
 */

// Ajustamos las rutas para que funcione desde public_html
require_once __DIR__ . '/../private/config/database.php';
require_once __DIR__ . '/../private/config/settings.php';
require_once __DIR__ . '/../private/models/Order.php';
require_once __DIR__ . '/../private/services/MailerService.php';

if (php_sapi_name() !== 'cli') {
    echo "<pre>"; // Formato para navegador
}

echo "--- INICIANDO SIMULACIÓN DE COMPRA ---\n\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos. Verifica las credenciales en settings.php.");
    }
    
    $orderModel = new Order($db);

    // 1. Datos ficticios para la orden
    $testData = [
        'name' => 'Cliente de Prueba',
        'phone' => '099000000',
        'email' => 'agcarnelli2023@gmail.com',
        'address' => 'Dirección de Prueba 123, Montevideo',
        'notes' => 'Esta es una compra simulada de prueba.',
        'total' => 1500.50,
        'payment_method' => 'mercadopago',
        'preference_id' => 'TEST_PREFERENCE_' . time(),
        'shipping_agency' => 'Agencia Central (DAC)'
    ];

    // Buscamos un producto válido en la DB
    $stmt = $db->query("SELECT id, name, price FROM products WHERE is_active = 1 LIMIT 1");
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("ERROR: No se encontró ningún producto activo en la base de datos para la prueba.");
    }

    $cartItems = [
        [
            'id' => $product['id'],
            'name' => $product['name'],
            'quantity' => 2,
            'price' => $product['price']
        ]
    ];

    echo "1. Creando orden de prueba...\n";
    $order_id = $orderModel->create($testData, $cartItems);

    if (!$order_id) {
        throw new Exception("No se pudo crear la orden.");
    }
    echo "   OK! Orden #$order_id creada.\n\n";

    echo "2. Simulando aprobación de pago...\n";
    $orderModel->updatePaymentStatusByOrderId($order_id, 'approved');
    echo "   OK! Estado actualizado.\n\n";

    echo "3. Enviando notificación por correo...\n";
    $orderData = $orderModel->getOrderById($order_id);
    $orderItems = $orderModel->getOrderDetailsById($order_id);

    $mailer = new MailerService();
    $result = $mailer->sendPurchaseNotification($orderData, $orderItems, MAIL_ADMIN_NOTIFICATIONS);

    if ($result) {
        echo "   ¡ÉXITO! El correo ha sido enviado a " . MAIL_ADMIN_NOTIFICATIONS . ".\n";
    } else {
        echo "   ERROR: El servicio de correo falló.\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n--- SIMULACIÓN FINALIZADA ---";

if (php_sapi_name() !== 'cli') {
    echo "</pre>";
}
?>

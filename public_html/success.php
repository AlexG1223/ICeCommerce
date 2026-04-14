<?php
// success.php
require_once __DIR__ . '/../private/config/database.php';
require_once __DIR__ . '/../private/models/Order.php';
require_once __DIR__ . '/../private/services/MailerService.php';

$orderId = isset($_GET['external_reference']) ? $_GET['external_reference'] : null;
$status = isset($_GET['status']) ? $_GET['status'] : null;

if ($orderId && $status === 'approved') {
    $db = (new Database())->getConnection();
    $orderModel = new Order($db);
    
    $orderData = $orderModel->getOrderById($orderId);
    
    // Only process if the order exists and is still pending
    if ($orderData && $orderData['payment_status'] === 'pending') {
        // Update order status to completed
        $orderModel->updatePaymentStatusByOrderId($orderId, 'completed');
        
        // Fetch items and send Email Notification
        $orderItems = $orderModel->getOrderDetailsById($orderId);
        $mailer = new MailerService();
        $mailer->sendPurchaseNotification($orderData, $orderItems, 'agcarnelli2023@gmail.com');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Pago Exitoso! - Impresos Carnelli</title>
    <link rel="stylesheet" href="globals/main.css">
    <style>
        .result-container {
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
            background: var(--surface-color);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        .icon-success {
            font-size: 60px;
            color: #4caf50;
            margin-bottom: var(--spacing-md);
        }
        h1 {
            color: var(--brand-black);
            margin-bottom: var(--spacing-md);
        }
        p {
            color: var(--brand-dark-gray);
            margin-bottom: var(--spacing-lg);
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo">IMPRESOS CARNELLI</a>
        </div>
    </header>

    <div class="container">
        <div class="result-container">
            <div class="icon-success">✓</div>
            <h1>¡Pago Exitoso!</h1>
            <p>Tu orden ha sido procesada correctamente. En breve recibirás un correo con la confirmación y los detalles de tu pedido.</p>
            <a href="index.php" class="btn-primary">Volver al inicio</a>
        </div>
    </div>
    
    <script>
        // Ensure cart is clear
        localStorage.removeItem('carnelli_cart');
    </script>
</body>
</html>

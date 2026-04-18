<?php
// success.php


require_once __DIR__ . '/../private/config/database.php';
require_once __DIR__ . '/../private/config/settings.php';
require_once __DIR__ . '/../private/models/Order.php';
require_once __DIR__ . '/../private/services/MailerService.php';

$orderId = isset($_GET['external_reference']) ? $_GET['external_reference'] : null;
$status = isset($_GET['status']) ? $_GET['status'] : null;
$paymentId = isset($_GET['payment_id']) ? $_GET['payment_id'] : null;



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
        $emailResult = $mailer->sendPurchaseNotification($orderData, $orderItems, MAIL_ADMIN_NOTIFICATIONS);


        // --- Fetch API Creamos OT en programa de Gestión ---

        $apiUrl = MANAGEMENT_API_URL;
        $otData = [
            'order_id' => $orderId,
            'customer' => $orderData['customer_name'],
            'total' => $orderData['total']
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($otData)
            ]
        ];
        $context = stream_context_create($options);
        $otResult = @file_get_contents($apiUrl, false, $context);

    } else {

    }
} else {

}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel='icon' type="image/png" href="/public/tienda/assets/img/iclogo.png">
    <title>¡Pago Exitoso! - Impresos Carnelli</title>
    <link rel="stylesheet" href="globals/main.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <a href="index.php" class="logo">Impresos Carnelli</a>
            <div class="header-actions">
                <a href="https://www.impresoscarnelli.com/page/" class="nav-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    <span class="hide-mobile">Inicio</span>
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="result-container">
            <div class="icon-success">✓</div>
            <h1>¡Pago Exitoso!</h1>
            <p>Tu orden ha sido procesada correctamente. En breve recibirás un correo con la confirmación y los detalles
                de tu pedido.</p>
            <a href="index.php" class="btn-primary">Volver al inicio</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/whatsapp.php'; ?>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        localStorage.removeItem('carnelli_cart');
    </script>
</body>

</html>
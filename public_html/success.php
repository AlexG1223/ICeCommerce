<?php
// success.php
error_log('[SUCCESS] ========== Página de éxito cargada ==========');
error_log('[SUCCESS] Query params: ' . json_encode($_GET));

require_once __DIR__ . '/../private/config/database.php';
require_once __DIR__ . '/../private/config/settings.php';
require_once __DIR__ . '/../private/models/Order.php';
require_once __DIR__ . '/../private/services/MailerService.php';

$orderId = isset($_GET['external_reference']) ? $_GET['external_reference'] : null;
$status = isset($_GET['status']) ? $_GET['status'] : null;
$paymentId = isset($_GET['payment_id']) ? $_GET['payment_id'] : null;

error_log('[SUCCESS] external_reference (orderId): ' . ($orderId ?? 'NULL'));
error_log('[SUCCESS] status: ' . ($status ?? 'NULL'));
error_log('[SUCCESS] payment_id: ' . ($paymentId ?? 'NULL'));

if ($orderId && $status === 'approved') {
    error_log('[SUCCESS] ✅ Status es approved - Procesando orden #' . $orderId);
    $db = (new Database())->getConnection();
    $orderModel = new Order($db);
    
    $orderData = $orderModel->getOrderById($orderId);
    error_log('[SUCCESS] Orden encontrada: ' . ($orderData ? 'SÍ | payment_status actual: ' . $orderData['payment_status'] : 'NO'));
    
    // Only process if the order exists and is still pending
    if ($orderData && $orderData['payment_status'] === 'pending') {
        error_log('[SUCCESS] Orden está en pending → Actualizando a completed');
        
        // Update order status to completed
        $orderModel->updatePaymentStatusByOrderId($orderId, 'completed');
        error_log('[SUCCESS] ✅ Estado actualizado a completed');
        
        // Fetch items and send Email Notification
        $orderItems = $orderModel->getOrderDetailsById($orderId);
        error_log('[SUCCESS] Items de la orden: ' . count($orderItems));
        
        error_log('[SUCCESS] 📧 Enviando email de notificación...');
        $mailer = new MailerService();
        $emailResult = $mailer->sendPurchaseNotification($orderData, $orderItems, MAIL_ADMIN_NOTIFICATIONS);
        error_log('[SUCCESS] Email resultado: ' . ($emailResult ? '✅ Enviado' : '❌ Falló'));
        
        // --- Fetch API Creamos OT en programa de Gestión ---
        error_log('[SUCCESS] 🔧 Creando OT en programa de gestión...');
        $apiUrl = MANAGEMENT_API_URL;
        $otData = [
            'order_id' => $orderId,
            'customer' => $orderData['customer_name'],
            'total'    => $orderData['total']
        ];
        error_log('[SUCCESS] OT data: ' . json_encode($otData));
        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($otData)
            ]
        ];
        $context = stream_context_create($options);
        $otResult = @file_get_contents($apiUrl, false, $context);
        error_log('[SUCCESS] OT resultado: ' . ($otResult !== false ? '✅ ' . $otResult : '❌ Error en la llamada'));
    } else {
        error_log('[SUCCESS] ⚠️ Orden no procesada - ' . (!$orderData ? 'No encontrada en DB' : 'Status ya es: ' . $orderData['payment_status']));
    }
} else {
    error_log('[SUCCESS] ⚠️ No se procesó - orderId: ' . ($orderId ?? 'NULL') . ' | status: ' . ($status ?? 'NULL'));
}
error_log('[SUCCESS] ========== Procesamiento PHP finalizado ==========');
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
        console.log('[SUCCESS] 🟢 Página de éxito cargada');
        console.log('[SUCCESS] URL completa:', window.location.href);
        console.log('[SUCCESS] Query params:', window.location.search);
        
        // Parse and log URL params
        const urlParams = new URLSearchParams(window.location.search);
        console.log('[SUCCESS] external_reference:', urlParams.get('external_reference'));
        console.log('[SUCCESS] status:', urlParams.get('status'));
        console.log('[SUCCESS] payment_id:', urlParams.get('payment_id'));
        console.log('[SUCCESS] payment_type:', urlParams.get('payment_type'));
        
        // Ensure cart is clear
        const cartBefore = localStorage.getItem('carnelli_cart');
        console.log('[SUCCESS] 🛒 Carrito antes de limpiar:', cartBefore ? JSON.parse(cartBefore) : 'ya vacío');
        localStorage.removeItem('carnelli_cart');
        console.log('[SUCCESS] ✅ Carrito limpiado de localStorage');
    </script>
</body>
</html>

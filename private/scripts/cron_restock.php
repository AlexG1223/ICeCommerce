<?php
/**
 * Cron Script: Restock Expired Orders
 * 
 * Este script busca pedidos con estado 'pending' que tengan más de 1 hora de antigüedad,
 * restaura el stock de los productos y marca el pedido como 'expired'.
 * 
 * Ejecución recomendada: Cada 1 hora vía Crontab.
 * php c:\xampp\htdocs\eCommerce\private\scripts\cron_restock.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';

// Verificamos que se ejecute por CLI para mayor seguridad (o añadir un token si es por URL)
if (php_sapi_name() !== 'cli' && !isset($_GET['token'])) {
    die("Acceso no autorizado.");
}

try {
    $db = (new Database())->getConnection();
    
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos.");
    }

    $orderModel = new Order($db);

    // Definimos el tiempo de expiración (ej: 1 hora)
    $interval = '1 HOUR';

    // 1. Buscamos IDs de pedidos pendientes antiguos
    // Nota: Asumimos que la columna se llama 'created_at'. Si falla, verificar el nombre en la DB.
    $query = "SELECT id FROM orders 
              WHERE payment_status = 'pending' 
              AND created_at < DATE_SUB(NOW(), INTERVAL $interval)";
    
    $stmt = $db->query($query);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = 0;
    foreach ($orders as $orderRow) {
        $orderId = $orderRow['id'];

        // Restaurar Stock
        $orderModel->restoreStockByOrderId($orderId);

        // Actualizar estado a 'expired'
        $orderModel->updatePaymentStatusByOrderId($orderId, 'expired');

        $count++;
    }

    $logMessage = "[" . date('Y-m-d H:i:s') . "] Cron Restock: " . $count . " pedidos procesados con éxito.\n";
    echo $logMessage;
    
    // Guardar en archivo de log
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    file_put_contents($logDir . '/cron.log', $logMessage, FILE_APPEND);

} catch (Exception $e) {
    $errorMessage = "[" . date('Y-m-d H:i:s') . "] Error en Cron Restock: " . $e->getMessage() . "\n";
    echo $errorMessage;
    
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    file_put_contents($logDir . '/cron.log', $errorMessage, FILE_APPEND);
}


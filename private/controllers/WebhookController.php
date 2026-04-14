<?php
// private/controllers/WebhookController.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Order.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if(file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

class WebhookController {
    private $db;
    private $order;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->order = new Order($this->db);
    }

    public function handleWebhook($data) {
        // If payment webhook
        if(isset($data['type']) && $data['type'] === 'payment') {
            $payment_id = $data['data']['id'];
            
            try {
                if(class_exists('\MercadoPago\SDK')) {
                    \MercadoPago\SDK::setAccessToken('APP_USR-2016608383710992-041214-0a5f4b7332f489a8b5e658e8e7ce17eb-3331693746');
                    $payment = \MercadoPago\Payment::find_by_id($payment_id);
                    
                    if($payment) {
                        $status = $payment->status; // 'approved', 'rejected', 'refunded', 'cancelled', 'in_process'
                        $order_id = $payment->external_reference;
                        
                        if ($order_id) {
                            $this->order->updatePaymentStatusByOrderId($order_id, $status);
                            
                            if ($status === 'approved') {
                                $this->sendEmailNotification($order_id);
                            } else if (in_array($status, ['rejected', 'cancelled', 'refunded'])) {
                                $this->order->restoreStockByOrderId($order_id);
                            }
                        }
                    }
                }
            } catch(Exception $e) { }

            return true;
        }
        return false;
    }

    private function sendEmailNotification($order_id) {
        $query = "SELECT * FROM orders WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$order_id]);
        $orderInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$orderInfo) return false;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Change if using another service
            $mail->SMTPAuth   = true;
            $mail->Username   = 'prueba@gmail.com'; // El correo desde el que sale
            $mail->Password   = 'XXXXXXXXX'; // CONTRASEÑA DE APLICACIÓN
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('prueba@gmail.com', 'Impresos Carnelli');
            $mail->addAddress('agcarnelli2023@gmail.com', 'Admin');

            $mail->isHTML(true);
            $mail->Subject = 'Nuevo pedido #' . $order_id . ' en Impresos Carnelli';
            $mail->Body    = "
                <h3>¡Nuevo pedido aprobado desde Mercado Pago!</h3>
                <p><strong>Pedido ID:</strong> {$order_id}</p>
                <p><strong>Cliente:</strong> {$orderInfo['customer_name']}</p>
                <p><strong>Teléfono:</strong> {$orderInfo['customer_phone']}</p>
                <p><strong>Email:</strong> {$orderInfo['customer_email']}</p>
                <p><strong>Agencia de envío:</strong> {$orderInfo['shipping_agency']}</p>
                <p><strong>Dirección:</strong> {$orderInfo['customer_address']}</p>
                <p><strong>Total:</strong> $" . number_format($orderInfo['total'], 2) . "</p>
                <p><strong>Notas:</strong> {$orderInfo['notes']}</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            // log error
        }
    }
}
?>

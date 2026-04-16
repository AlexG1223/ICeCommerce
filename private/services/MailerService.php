<?php
// private/services/MailerService.php
require_once __DIR__ . '/../config/settings.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

class MailerService {
    private $mail;

    public function __construct() {
        error_log('[MailerService] Inicializando PHPMailer...');
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->Host       = MAIL_HOST;
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = MAIL_USER;
        $this->mail->Password   = MAIL_PASS;
        $this->mail->SMTPSecure = (MAIL_PORT == 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = MAIL_PORT;
        
        $this->mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $this->mail->CharSet = 'UTF-8';
        error_log('[MailerService] ✅ PHPMailer configurado - Host: ' . $this->mail->Host . ' | Puerto: ' . $this->mail->Port);
    }

    public function sendPurchaseNotification($orderData, $orderItems, $targetEmail = 'agcarnelli2023@gmail.com') {
        error_log('[MailerService::sendPurchaseNotification] ========== Enviando notificación de compra ==========');
        error_log('[MailerService::sendPurchaseNotification] Orden #' . $orderData['id'] . ' | Destino: ' . $targetEmail);
        error_log('[MailerService::sendPurchaseNotification] Cliente: ' . $orderData['customer_name'] . ' | Total: ' . $orderData['total']);
        error_log('[MailerService::sendPurchaseNotification] Items en la orden: ' . count($orderItems));
        try {
            $this->mail->addAddress($targetEmail);

            $this->mail->isHTML(true);
            $this->mail->Subject = 'Nueva Compra Exitosa - Orden #' . $orderData['id'];

            $html = "<h2>¡Nueva Compra Registrada!</h2>";
            $html .= "<p><strong>Orden ID:</strong> {$orderData['id']}</p>";
            $html .= "<p><strong>Cliente:</strong> {$orderData['customer_name']}</p>";
            $html .= "<p><strong>Email:</strong> {$orderData['customer_email']}</p>";
            $html .= "<p><strong>Teléfono:</strong> {$orderData['customer_phone']}</p>";
            $html .= "<p><strong>Dirección:</strong> {$orderData['customer_address']}</p>";
            $html .= "<p><strong>Agencia de Envío:</strong> {$orderData['shipping_agency']}</p>";
            if (!empty($orderData['notes'])) {
                $html .= "<p><strong>Notas:</strong> {$orderData['notes']}</p>";
            }

            $html .= "<h3>Detalle de Artículos:</h3>";
            $html .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; min-width: 400px;'>";
            $html .= "<tr style='background-color:#f0f0f0;'><th>Producto</th><th>Cantidad</th><th>Precio Unit.</th><th>Subtotal</th></tr>";

            foreach ($orderItems as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $productName = isset($item['name']) ? $item['name'] : 'Mod.' . $item['product_id'];
                $html .= "<tr>";
                $html .= "<td>{$productName}</td>";
                $html .= "<td>{$item['quantity']}</td>";
                $html .= "<td>\$" . number_format($item['unit_price'], 2) . "</td>";
                $html .= "<td>\$" . number_format($subtotal, 2) . "</td>";
                $html .= "</tr>";
            }

            $html .= "<tr><td colspan='3' align='right'><strong>Total:</strong></td><td><strong>\$" . number_format($orderData['total'], 2) . "</strong></td></tr>";
            $html .= "</table>";

            $this->mail->Body = $html;

            $result = $this->mail->send();
            error_log('[MailerService::sendPurchaseNotification] ✅ Email enviado exitosamente');
            return $result;
        } catch (Exception $e) {
            error_log('[MailerService::sendPurchaseNotification] ❌ Error al enviar email: ' . $e->getMessage());
            error_log('[MailerService::sendPurchaseNotification] ❌ Mailer Error: ' . $this->mail->ErrorInfo);
            return false;
        }
    }
}

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
    }

    public function sendPurchaseNotification($orderData, $orderItems, $targetEmail = 'agcarnelli2023@gmail.com') {
        try {
            // Limpiar destinatarios previos por si acaso
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            
            // Si el target es el admin, también agregamos al cliente como destinatario (o viceversa)
            // Para asegurar que ambos reciban la notificación
            $this->mail->addAddress($targetEmail);
            
            // Si el targetEmail es el del admin y tenemos el email del cliente, lo agregamos en copia
            if ($targetEmail === MAIL_ADMIN_NOTIFICATIONS && !empty($orderData['customer_email'])) {
                $this->mail->addCC($orderData['customer_email']);
            }

            $this->mail->isHTML(true);
            $this->mail->Subject = 'Confirmación de Compra - Impresos Carnelli - Orden #' . $orderData['id'];

            $html = $this->generateOrderHtml($orderData, $orderItems);
            $this->mail->Body = $html;

            $result = $this->mail->send();
            return $result;
        } catch (Exception $e) {
            error_log("Error enviando correo: " . $this->mail->ErrorInfo);
            return false;
        }
    }

    private function generateOrderHtml($orderData, $orderItems) {
        $html = "
        <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #eee; padding: 20px;'>
            <h2 style='color: #000; border-bottom: 2px solid #eee; padding-bottom: 10px;'>¡Gracias por tu compra!</h2>
            <p>Se ha registrado una nueva orden en <strong>Impresos Carnelli</strong>.</p>
            
            <table style='width: 100%; margin-bottom: 20px;'>
                <tr>
                    <td style='vertical-align: top; width: 50%;'>
                        <strong>Detalles de la Orden:</strong><br>
                        ID: #{$orderData['id']}<br>
                        Fecha: " . date('d/m/Y H:i') . "<br>
                        Estado: Pago Aprobado
                    </td>
                    <td style='vertical-align: top; width: 50%;'>
                        <strong>Datos del Cliente:</strong><br>
                        {$orderData['customer_name']}<br>
                        {$orderData['customer_email']}<br>
                        Tel: {$orderData['customer_phone']}
                    </td>
                </tr>
            </table>

            <div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>
                <strong>Dirección de Envío:</strong><br>
                {$orderData['customer_address']}<br>
                <strong>Agencia:</strong> {$orderData['shipping_agency']}
            </div>";

            if (!empty($orderData['notes'])) {
                $html .= "<p><strong>Notas:</strong><br><i>" . nl2br(htmlspecialchars($orderData['notes'])) . "</i></p>";
            }

            $html .= "
            <h3 style='border-bottom: 1px solid #eee; padding-bottom: 5px;'>Resumen de Productos</h3>
            <table style='width: 100%; border-collapse: collapse;'>
                <thead>
                    <tr style='background: #f0f0f0;'>
                        <th style='text-align: left; padding: 10px; border: 1px solid #ddd;'>Producto</th>
                        <th style='text-align: center; padding: 10px; border: 1px solid #ddd;'>Cant.</th>
                        <th style='text-align: right; padding: 10px; border: 1px solid #ddd;'>Precio</th>
                        <th style='text-align: right; padding: 10px; border: 1px solid #ddd;'>Total</th>
                    </tr>
                </thead>
                <tbody>";

            foreach ($orderItems as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $productName = isset($item['name']) ? $item['name'] : 'Producto ID: ' . $item['product_id'];
                $html .= "
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$productName}</td>
                        <td style='padding: 10px; border: 1px solid #ddd; text-align: center;'>{$item['quantity']}</td>
                        <td style='padding: 10px; border: 1px solid #ddd; text-align: right;'>\$" . number_format($item['unit_price'], 2) . "</td>
                        <td style='padding: 10px; border: 1px solid #ddd; text-align: right;'>\$" . number_format($subtotal, 2) . "</td>
                    </tr>";
            }

            $html .= "
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan='3' style='padding: 10px; border: 1px solid #ddd; text-align: right;'><strong>Total:</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd; text-align: right; background: #f0f0f0;'><strong>\$" . number_format($orderData['total'], 2) . "</strong></td>
                    </tr>
                </tfoot>
            </table>
            
            <p style='margin-top: 20px; font-size: 14px; text-align: center;'>
                <a href='" . BASE_URL . "' style='color: #000; text-decoration: underline;'>Volver a la tienda</a>
            </p>
            <p style='margin-top: 10px; font-size: 11px; color: #999; text-align: center;'>
                &copy; " . date('Y') . " Impresos Carnelli. Todos los derechos reservados.<br>
                Este es un correo automático, por favor no respondas directamente.
            </p>
        </div>";

        return $html;
    }
}

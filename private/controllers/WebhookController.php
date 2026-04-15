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
        error_log('[WebhookController] ========== handleWebhook INICIO ==========');
        error_log('[WebhookController] Tipo recibido: ' . (isset($data['type']) ? $data['type'] : 'NO DEFINIDO'));
        error_log('[WebhookController] Data completa: ' . json_encode($data));

        // If payment webhook
        if(isset($data['type']) && $data['type'] === 'payment') {
            $payment_id = $data['data']['id'];
            error_log('[WebhookController] 💳 Payment ID: ' . $payment_id);
            
            try {
                if(class_exists('\MercadoPago\SDK')) {
                    error_log('[WebhookController] ✅ SDK MercadoPago disponible');
                    \MercadoPago\SDK::setAccessToken('APP_USR-2016608383710992-041214-0a5f4b7332f489a8b5e658e8e7ce17eb-3331693746');
                    $payment = \MercadoPago\Payment::find_by_id($payment_id);
                    
                    if($payment) {
                        $status = $payment->status; // 'approved', 'rejected', 'refunded', 'cancelled', 'in_process'
                        $order_id = $payment->external_reference;
                        error_log('[WebhookController] Estado del pago: ' . $status);
                        error_log('[WebhookController] External reference (order_id): ' . $order_id);
                        
                        if ($order_id) {
                            $orderData = $this->order->getOrderById($order_id);
                            $previousStatus = $orderData ? $orderData['payment_status'] : null;
                            error_log('[WebhookController] Estado anterior de la orden: ' . ($previousStatus ?? 'NULL'));
                            error_log('[WebhookController] Transición: ' . ($previousStatus ?? 'NULL') . ' → ' . $status);

                            $this->order->updatePaymentStatusByOrderId($order_id, $status);
                            error_log('[WebhookController] ✅ Estado actualizado en DB');
                            
                            if ($status === 'approved' && $previousStatus === 'pending') {
                                error_log('[WebhookController] 📧 Pago aprobado desde pending → Enviando email de notificación');
                                $this->sendEmailNotification($order_id);
                            } else if (in_array($status, ['rejected', 'cancelled', 'refunded']) && !in_array($previousStatus, ['rejected', 'cancelled', 'refunded', 'completed'])) {
                                error_log('[WebhookController] 🔄 Pago ' . $status . ' → Restaurando stock');
                                $this->order->restoreStockByOrderId($order_id);
                            } else {
                                error_log('[WebhookController] ℹ️ Sin acción adicional para esta transición');
                            }
                        } else {
                            error_log('[WebhookController] ⚠️ No se encontró external_reference en el pago');
                        }
                    } else {
                        error_log('[WebhookController] ❌ No se pudo recuperar el pago desde MercadoPago');
                    }
                } else {
                    error_log('[WebhookController] ❌ SDK MercadoPago NO disponible');
                }
            } catch(Exception $e) {
                error_log('[WebhookController] ❌ Excepción: ' . $e->getMessage());
                error_log('[WebhookController] ❌ Trace: ' . $e->getTraceAsString());
            }

            error_log('[WebhookController] ========== handleWebhook FIN ==========');
            return true;
        }
        error_log('[WebhookController] ⚠️ Tipo no es payment, ignorando');
        error_log('[WebhookController] ========== handleWebhook FIN ==========');
        return false;
    }

    private function sendEmailNotification($order_id) {
        error_log('[WebhookController::sendEmail] ========== Enviando email para Orden #' . $order_id . ' ==========');
        $query = "SELECT * FROM orders WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$order_id]);
        $orderInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$orderInfo) {
            error_log('[WebhookController::sendEmail] ❌ Orden no encontrada en DB');
            return false;
        }
        error_log('[WebhookController::sendEmail] ✅ Orden encontrada - Cliente: ' . $orderInfo['customer_name'] . ' | Total: ' . $orderInfo['total']);

        // --- Fetch API Creamos OT en programa de Gestión ---
        error_log('[WebhookController::sendEmail] Creando OT en programa de gestión...');
        $this->createOTInManagementProgram($order_id, $orderInfo);

        $mail = new PHPMailer(true);
        try {
            error_log('[WebhookController::sendEmail] Configurando PHPMailer...');
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
            error_log('[WebhookController::sendEmail] ✅ Email enviado correctamente');
        } catch (Exception $e) {
            error_log('[WebhookController::sendEmail] ❌ Error al enviar email: ' . $e->getMessage());
            error_log('[WebhookController::sendEmail] ❌ Mailer Error: ' . $mail->ErrorInfo);
        }
    }

    private function createOTInManagementProgram($order_id, $orderInfo) {
        error_log('[WebhookController::createOT] Creando OT para Orden #' . $order_id);
        $api_url = 'https://api.tuprogramadegestion.com/ots';
        $data = [
            'order_id' => $order_id,
            'customer' => $orderInfo['customer_name'],
            'total'    => $orderInfo['total'],
            'notes'    => $orderInfo['notes']
        ];
        error_log('[WebhookController::createOT] Datos a enviar: ' . json_encode($data));

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data)
            ]
        ];
        $context  = stream_context_create($options);
        try {
            $result = @file_get_contents($api_url, false, $context);
            error_log('[WebhookController::createOT] ' . ($result !== false ? '✅ OT creada - Respuesta: ' . $result : '❌ Error al contactar API'));
        } catch (Exception $e) {
            error_log('[WebhookController::createOT] ❌ Excepción: ' . $e->getMessage());
        }
    }
}
?>

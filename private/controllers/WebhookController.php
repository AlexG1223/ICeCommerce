<?php
// private/controllers/WebhookController.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../services/MailerService.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
                if(class_exists('\MercadoPago\MercadoPagoConfig')) {
                    error_log('[WebhookController] ✅ SDK MercadoPago disponible');
                    \MercadoPago\MercadoPagoConfig::setAccessToken(MP_ACCESS_TOKEN);
                    
                    // Note: In V3, we use PaymentClient
                    $client = new \MercadoPago\Client\Payment\PaymentClient();
                    $payment = $client->get($payment_id);
                    
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
        $orderData = $this->order->getOrderById($order_id);

        if (!$orderData) {
            error_log('[WebhookController::sendEmail] ❌ Orden no encontrada en DB');
            return false;
        }
        
        $orderItems = $this->order->getOrderDetailsById($order_id);

        // --- Fetch API Creamos OT en programa de Gestión ---
        $this->createOTInManagementProgram($order_id, $orderData);

        $mailer = new MailerService();
        $result = $mailer->sendPurchaseNotification($orderData, $orderItems, MAIL_ADMIN_NOTIFICATIONS);
        
        if ($result) {
            error_log('[WebhookController::sendEmail] ✅ Email enviado correctamente vía MailerService');
        } else {
            error_log('[WebhookController::sendEmail] ❌ Error al enviar email');
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

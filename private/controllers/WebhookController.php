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

        // If payment webhook
        if(isset($data['type']) && $data['type'] === 'payment') {
            $payment_id = $data['data']['id'];
            
            try {
                if(class_exists('\MercadoPago\MercadoPagoConfig')) {
                    \MercadoPago\MercadoPagoConfig::setAccessToken(MP_ACCESS_TOKEN);
                    
                    // Note: In V3, we use PaymentClient
                    $client = new \MercadoPago\Client\Payment\PaymentClient();
                    $payment = $client->get($payment_id);
                    
                    if($payment) {
                        $status = $payment->status; // 'approved', 'rejected', 'refunded', 'cancelled', 'in_process'
                        $order_id = $payment->external_reference;
                        
                        if ($order_id) {
                            $orderData = $this->order->getOrderById($order_id);
                            $previousStatus = $orderData ? $orderData['payment_status'] : null;

                            $this->order->updatePaymentStatusByOrderId($order_id, $status);
                            
                            if ($status === 'approved' && $previousStatus === 'pending') {
                                $this->sendEmailNotification($order_id);
                            } else if (in_array($status, ['rejected', 'cancelled', 'refunded']) && !in_array($previousStatus, ['rejected', 'cancelled', 'refunded', 'completed'])) {
                                $this->order->restoreStockByOrderId($order_id);
                            } else {
                            }
                        } else {
                        }
                    } else {
                    }
                } else {
                }
            } catch(Exception $e) {
            }

            return true;
        }
        return false;
    }

    private function sendEmailNotification($order_id) {
        $orderData = $this->order->getOrderById($order_id);

        if (!$orderData) {
            return false;
        }
        
        $orderItems = $this->order->getOrderDetailsById($order_id);

        // --- Fetch API Creamos OT en programa de Gestión ---
        $this->createOTInManagementProgram($order_id, $orderData);

        $mailer = new MailerService();
        $result = $mailer->sendPurchaseNotification($orderData, $orderItems, MAIL_ADMIN_NOTIFICATIONS);
        
        if ($result) {
        } else {
        }
    }

    private function createOTInManagementProgram($order_id, $orderInfo) {
        $api_url = MANAGEMENT_API_URL;
        
        // Fetch order items to build a detail string
        $orderItems = $this->order->getOrderDetailsById($order_id);
        $itemStrings = [];
        foreach ($orderItems as $item) {
            $itemStrings[] = $item['quantity'] . "x " . $item['name'];
        }
        $detalle = "Pedido Web #" . $order_id . ": " . implode(", ", $itemStrings);

        $data = [
            'api_token'         => MANAGEMENT_API_KEY,
            'id_cliente'        => 1, // Default Web Client ID in ICSoftware
            'fecha_ingreso'     => date('Y-m-d'),
            'detalle_trabajo'   => $detalle,
            'presupuesto'       => $orderInfo['total'],
            'direccion_entrega' => $orderInfo['customer_address'],
            'aclaracion_entrega'=> "Envío por: " . $orderInfo['shipping_agency'] . ". Notas: " . $orderInfo['notes'],
            'sector_destino'    => 'DISEÑO'
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        $context  = stream_context_create($options);
        try {
            $result = @file_get_contents($api_url, false, $context);
        } catch (Exception $e) {
        }
    }
}
?>

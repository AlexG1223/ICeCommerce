<?php
// private/controllers/CheckoutController.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../models/Order.php';

// Simulate Composer autoload for MP (in real scenario, we require vendor/autoload.php)
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

class CheckoutController
{
    private $db;
    private $order;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->order = new Order($this->db);
    }

    public function processCheckout($customerData, $cartItems)
    {

        $total = 0;
        foreach ($cartItems as $item) {
            $total += ($item['price'] * $item['quantity']);
        }

        $customerData['total'] = $total;
        $customerData['preference_id'] = null;
        $customerData['payment_method'] = 'mercadopago'; // Enforce MP


        // 1. Verificar stock
        if (!$this->order->checkStockBeforeCheckout($cartItems)) {
            return ['success' => false, 'message' => 'Parece que no queda stock disponible'];
        }

        // 2. Crear orden en DB
        $orderId = $this->order->create($customerData, $cartItems);
        if (!$orderId) {
            return ['success' => false, 'message' => 'Error al crear la orden en base de datos.'];
        }

        // 3. Crear preferencia en Mercado Pago
        try {
            if (class_exists('\MercadoPago\MercadoPagoConfig')) {
                \MercadoPago\MercadoPagoConfig::setAccessToken(MP_ACCESS_TOKEN);

                $client = new \MercadoPago\Client\Preference\PreferenceClient();
                $items = [];

                foreach ($cartItems as $cartItem) {
                    $items[] = [
                        "title" => $cartItem['name'],
                        "quantity" => (int) $cartItem['quantity'],
                        "unit_price" => (float) $cartItem['price'],
                        "currency_id" => "UYU"
                    ];
                }

                $preferenceData = [
                    "items" => $items,
                    "back_urls" => [
                        "success" => BASE_URL . "/success.php",
                        "failure" => BASE_URL . "/failure.php",
                        "pending" => BASE_URL . "/pending.php"
                    ],
                    "auto_return" => "approved",
                    "external_reference" => (string) $orderId,
                    "notification_url" => BASE_URL . "/api/webhook.php"
                ];

                $preference = $client->create($preferenceData);

                $customerData['preference_id'] = $preference->id;
                $preference_url = $preference->init_point;


                // Update preference_id in DB
                $this->order->updatePreferenceId($orderId, $preference->id);
            } else {
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error al contactar con la pasarela de pago: ' . $e->getMessage()];
        }

        $result = [
            'success' => true,
            'order_id' => $orderId,
            'preference_id' => $customerData['preference_id'],
            'preference_url' => isset($preference_url) ? $preference_url : null
        ];
        return $result;
    }
}
?>

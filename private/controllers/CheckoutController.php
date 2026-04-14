<?php
// private/controllers/CheckoutController.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';

// Simulate Composer autoload for MP (in real scenario, we require vendor/autoload.php)
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

class CheckoutController
{
    private $db;
    private $order;
    private $mp_access_token = 'APP_USR-2016608383710992-041214-0a5f4b7332f489a8b5e658e8e7ce17eb-3331693746';

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

        if (!$this->order->checkStockBeforeCheckout($cartItems)) {
            return ['success' => false, 'message' => 'Parece que no queda stock disponible'];
        }

        $orderId = $this->order->create($customerData, $cartItems);
        if (!$orderId) {
            return ['success' => false, 'message' => 'Error al crear la orden en base de datos.'];
        }
        try {
            if (class_exists('\MercadoPago\MercadoPagoConfig')) {
                \MercadoPago\MercadoPagoConfig::setAccessToken($this->mp_access_token);

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

                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/eCommerce/public_html';

                $preferenceData = [
                    "items" => $items,
                    "back_urls" => [
                        "success" => $baseUrl . "/success.php",
                        "failure" => $baseUrl . "/failure.php",
                        "pending" => $baseUrl . "/pending.php"
                    ],
                    "auto_return" => "approved",
                    "external_reference" => (string) $orderId
                ];

                $preference = $client->create($preferenceData);

                $customerData['preference_id'] = $preference->id;
                $preference_url = $preference->init_point;

                // Update preference_id in DB
                $this->order->updatePreferenceId($orderId, $preference->id);
            }
        } catch (\Exception $e) {
            // Return error if MP fails (e.g. wrong credentials)
            return ['success' => false, 'message' => 'Error al contactar con la pasarela de pago: ' . $e->getMessage()];
        }

        return [
            'success' => true,
            'order_id' => $orderId,
            'preference_id' => $customerData['preference_id'],
            'preference_url' => isset($preference_url) ? $preference_url : null
        ];
    }
}
?>
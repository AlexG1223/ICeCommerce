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
        error_log('[CheckoutController] ========== processCheckout INICIO ==========');
        error_log('[CheckoutController] customerData: ' . json_encode($customerData));
        error_log('[CheckoutController] cartItems: ' . json_encode($cartItems));

        $total = 0;
        foreach ($cartItems as $item) {
            $total += ($item['price'] * $item['quantity']);
        }

        $customerData['total'] = $total;
        $customerData['preference_id'] = null;
        $customerData['payment_method'] = 'mercadopago'; // Enforce MP

        error_log('[CheckoutController] Total calculado: ' . $total);

        // 1. Verificar stock
        error_log('[CheckoutController] Verificando stock...');
        if (!$this->order->checkStockBeforeCheckout($cartItems)) {
            error_log('[CheckoutController] ❌ Stock insuficiente');
            return ['success' => false, 'message' => 'Parece que no queda stock disponible'];
        }
        error_log('[CheckoutController] ✅ Stock OK');

        // 2. Crear orden en DB
        error_log('[CheckoutController] Creando orden en DB...');
        $orderId = $this->order->create($customerData, $cartItems);
        if (!$orderId) {
            error_log('[CheckoutController] ❌ Error al crear orden en DB');
            return ['success' => false, 'message' => 'Error al crear la orden en base de datos.'];
        }
        error_log('[CheckoutController] ✅ Orden creada con ID: ' . $orderId);

        // 3. Crear preferencia en Mercado Pago
        try {
            error_log('[CheckoutController] Verificando clase MercadoPago...');
            if (class_exists('\MercadoPago\MercadoPagoConfig')) {
                error_log('[CheckoutController] ✅ Clase MercadoPago encontrada');
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
                    "external_reference" => (string) $orderId
                ];

                error_log('[CheckoutController] Enviando preferencia a MP: ' . json_encode($preferenceData));
                $preference = $client->create($preferenceData);

                $customerData['preference_id'] = $preference->id;
                $preference_url = $preference->init_point;

                error_log('[CheckoutController] ✅ Preferencia creada - ID: ' . $preference->id);
                error_log('[CheckoutController] ✅ init_point URL: ' . $preference_url);

                // Update preference_id in DB
                $this->order->updatePreferenceId($orderId, $preference->id);
                error_log('[CheckoutController] ✅ preference_id actualizado en DB');
            } else {
                error_log('[CheckoutController] ⚠️ Clase MercadoPago NO encontrada - no se creará preferencia');
            }
        } catch (\Exception $e) {
            error_log('[CheckoutController] ❌ Excepción MP: ' . $e->getMessage());
            error_log('[CheckoutController] ❌ Trace: ' . $e->getTraceAsString());
            return ['success' => false, 'message' => 'Error al contactar con la pasarela de pago: ' . $e->getMessage()];
        }

        $result = [
            'success' => true,
            'order_id' => $orderId,
            'preference_id' => $customerData['preference_id'],
            'preference_url' => isset($preference_url) ? $preference_url : null
        ];
        error_log('[CheckoutController] ✅ Resultado final: ' . json_encode($result));
        error_log('[CheckoutController] ========== processCheckout FIN ==========');
        return $result;
    }
}
?>
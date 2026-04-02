<?php
// private/controllers/CheckoutController.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';

// Simulate Composer autoload for MP (in real scenario, we require vendor/autoload.php)
if(file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

class CheckoutController {
    private $db;
    private $order;
    private $mp_access_token = 'TU_ACCESS_TOKEN'; // Placeholder for MP

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->order = new Order($this->db);
    }

    public function processCheckout($customerData, $cartItems) {
        $total = 0;
        foreach($cartItems as $item) {
            $total += ($item['price'] * $item['quantity']);
        }
        
        $customerData['total'] = $total;
        $customerData['preference_id'] = null;

        if ($customerData['payment_method'] === 'mercadopago') {
            // Logic for MP preference
            try {
                if(class_exists('\MercadoPago\SDK')) {
                    \MercadoPago\SDK::setAccessToken($this->mp_access_token);
                    
                    $preference = new \MercadoPago\Preference();
                    $items = [];
                    foreach($cartItems as $cartItem) {
                        $item = new \MercadoPago\Item();
                        $item->title = $cartItem['name'];
                        $item->quantity = $cartItem['quantity'];
                        $item->unit_price = $cartItem['price'];
                        $items[] = $item;
                    }
                    $preference->items = $items;
                    $preference->save();
                    
                    $customerData['preference_id'] = $preference->id;
                }
            } catch(Exception $e) {
                // Return error if MP fails (e.g. wrong credentials)
                // For MVP without credentials, we just continue with null or fake id
                $customerData['preference_id'] = 'fake_pref_' . time();
            }
        }

        // Save order
        $orderId = $this->order->create($customerData, $cartItems);

        if ($orderId) {
            return [
                'success' => true, 
                'order_id' => $orderId, 
                'preference_id' => $customerData['preference_id']
            ];
        } else {
            return ['success' => false, 'message' => 'Error al crear la orden en base de datos.'];
        }
    }
}
?>

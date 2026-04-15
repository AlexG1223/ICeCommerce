<?php
// private/models/Order.php

class Order {
    private $conn;
    private $table_name = "orders";
    private $details_table = "order_details";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function checkStockBeforeCheckout($cart_items) {
        error_log('[Order::checkStock] Verificando stock para ' . count($cart_items) . ' items');
        foreach($cart_items as $item) {
            $query = "SELECT stock FROM products WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$item['id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log('[Order::checkStock] Producto ID: ' . $item['id'] . ' | Stock en DB: ' . ($row ? $row['stock'] : 'NO ENCONTRADO') . ' | Cantidad pedida: ' . $item['quantity']);
            if(!$row || $row['stock'] < $item['quantity']) {
                error_log('[Order::checkStock] ❌ Stock insuficiente para producto ID: ' . $item['id']);
                return false;
            }
        }
        error_log('[Order::checkStock] ✅ Stock disponible para todos los items');
        return true;
    }

    public function create($data, $cart_items) {
        error_log('[Order::create] ========== Creando orden ==========');
        error_log('[Order::create] Cliente: ' . $data['name'] . ' | Email: ' . $data['email'] . ' | Total: ' . $data['total']);
        error_log('[Order::create] Método de pago: ' . $data['payment_method'] . ' | Agencia: ' . (isset($data['shipping_agency']) ? $data['shipping_agency'] : 'N/A'));
        try {
            $this->conn->beginTransaction();
            error_log('[Order::create] Transacción iniciada');

            $query = "INSERT INTO " . $this->table_name . " 
                      (customer_name, customer_phone, customer_email, customer_address, notes, total, payment_method, payment_status, preference_id, shipping_agency) 
                      VALUES (:name, :phone, :email, :address, :notes, :total, :payment_method, :payment_status, :preference_id, :shipping_agency)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':notes', $data['notes']);
            $stmt->bindParam(':total', $data['total']);
            $stmt->bindParam(':payment_method', $data['payment_method']);
            
            $agency = isset($data['shipping_agency']) ? $data['shipping_agency'] : null;
            $stmt->bindParam(':shipping_agency', $agency);
            
            $status = $data['payment_method'] === 'mercadopago' ? 'pending' : 'pending';
            $stmt->bindParam(':payment_status', $status);
            $stmt->bindParam(':preference_id', $data['preference_id']);
            
            if(!$stmt->execute()) {
                error_log('[Order::create] ❌ Error al ejecutar INSERT de orden');
                throw new Exception("Error creating order");
            }

            $order_id = $this->conn->lastInsertId();
            error_log('[Order::create] ✅ Orden insertada con ID: ' . $order_id);

            // Insert items
            foreach($cart_items as $item) {
                error_log('[Order::create] Insertando detalle - Producto ID: ' . $item['id'] . ' | Cant: ' . $item['quantity'] . ' | Precio: ' . $item['price']);
                $query_details = "INSERT INTO " . $this->details_table . " 
                                 (order_id, product_id, quantity, unit_price) 
                                 VALUES (:order_id, :product_id, :qty, :price)";
                $stmt_det = $this->conn->prepare($query_details);
                $stmt_det->bindParam(':order_id', $order_id);
                $stmt_det->bindParam(':product_id', $item['id']);
                $stmt_det->bindParam(':qty', $item['quantity']);
                $stmt_det->bindParam(':price', $item['price']);
                $stmt_det->execute();

                // Discount stock immediately to reserve it.
                error_log('[Order::create] Descontando stock para producto ID: ' . $item['id'] . ' | Cant: ' . $item['quantity']);
                $this->updateStock($item['id'], $item['quantity']);
            }

            $this->conn->commit();
            error_log('[Order::create] ✅ Transacción committed - Orden #' . $order_id . ' creada exitosamente');
            return $order_id;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log('[Order::create] ❌ ROLLBACK - Excepción: ' . $e->getMessage());
            return false;
        }
    }

    public function updatePreferenceId($order_id, $preference_id) {
        error_log('[Order::updatePreferenceId] Orden #' . $order_id . ' | preference_id: ' . $preference_id);
        $query = "UPDATE " . $this->table_name . " SET preference_id = :pref WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pref', $preference_id);
        $stmt->bindParam(':id', $order_id);
        $result = $stmt->execute();
        error_log('[Order::updatePreferenceId] ' . ($result ? '✅ Actualizado OK' : '❌ Falló la actualización'));
        return $result;
    }

    public function updatePaymentStatusByOrderId($order_id, $status) {
        error_log('[Order::updatePaymentStatusByOrderId] Orden #' . $order_id . ' | Nuevo status: ' . $status);
        $query = "UPDATE " . $this->table_name . " SET payment_status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $order_id);
        $result = $stmt->execute();
        error_log('[Order::updatePaymentStatusByOrderId] ' . ($result ? '✅ Status actualizado' : '❌ Falló'));
        return $result;
    }
    public function updatePaymentStatus($preference_id, $status) {
        error_log('[Order::updatePaymentStatus] preference_id: ' . $preference_id . ' | Nuevo status: ' . $status);
        $query = "UPDATE " . $this->table_name . " SET payment_status = :status WHERE preference_id = :pref";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':pref', $preference_id);
        $result = $stmt->execute();
        error_log('[Order::updatePaymentStatus] ' . ($result ? '✅ Status actualizado' : '❌ Falló'));
        return $result;
    }

    public function getOrderItemsByPreference($preference_id) {
        $query = "SELECT od.product_id, od.quantity FROM " . $this->details_table . " od
                  JOIN " . $this->table_name . " o ON o.id = od.order_id
                  WHERE o.preference_id = :pref";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pref', $preference_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function restoreStockByOrderId($order_id) {
        error_log('[Order::restoreStockByOrderId] ========== Restaurando stock para Orden #' . $order_id . ' ==========');
        $query = "SELECT product_id, quantity FROM " . $this->details_table . " WHERE order_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $order_id);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log('[Order::restoreStockByOrderId] Items encontrados: ' . count($items));

        foreach($items as $item) {
            error_log('[Order::restoreStockByOrderId] Restaurando +' . $item['quantity'] . ' unidades para producto ID: ' . $item['product_id']);
            $query_upd = "UPDATE products SET stock = stock + :qty WHERE id = :id";
            $stmt_upd = $this->conn->prepare($query_upd);
            $stmt_upd->bindParam(':qty', $item['quantity']);
            $stmt_upd->bindParam(':id', $item['product_id']);
            $stmt_upd->execute();

            $query_act = "UPDATE products SET is_active = 1 WHERE id = :id AND stock >= min_quantity AND is_active = 0";
            $stmt_act = $this->conn->prepare($query_act);
            $stmt_act->bindParam(':id', $item['product_id']);
            $stmt_act->execute();
            error_log('[Order::restoreStockByOrderId] ✅ Stock restaurado y reactivación evaluada para producto ID: ' . $item['product_id']);
        }
        error_log('[Order::restoreStockByOrderId] ========== Restauración completada ==========');
    }

    public function restoreStockByPreference($preference_id) {
        $items = $this->getOrderItemsByPreference($preference_id);
        foreach($items as $item) {
            $query_upd = "UPDATE products SET stock = stock + :qty WHERE id = :id";
            $stmt_upd = $this->conn->prepare($query_upd);
            $stmt_upd->bindParam(':qty', $item['quantity']);
            $stmt_upd->bindParam(':id', $item['product_id']);
            $stmt_upd->execute();

            // reactivate if now >= min_quantity
            $query_act = "UPDATE products SET is_active = 1 WHERE id = :id AND stock >= min_quantity AND is_active = 0";
            $stmt_act = $this->conn->prepare($query_act);
            $stmt_act->bindParam(':id', $item['product_id']);
            $stmt_act->execute();
        }
    }

    public function updateStock($product_id, $quantity) {
        error_log('[Order::updateStock] Descontando stock - Producto ID: ' . $product_id . ' | Cantidad: ' . $quantity);
        $query = "UPDATE products SET stock = stock - :qty WHERE id = :id AND stock >= :qty";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':qty', $quantity);
        $stmt->bindParam(':id', $product_id);
        $stmt->execute();
        $rowsAffected = $stmt->rowCount();
        error_log('[Order::updateStock] Filas afectadas: ' . $rowsAffected . ($rowsAffected > 0 ? ' ✅' : ' ❌ (stock insuficiente?)'));

        // Check if we need to deactivate
        $query_check = "UPDATE products SET is_active = 0 WHERE id = :id AND stock < min_quantity AND is_active = 1";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(':id', $product_id);
        $stmt_check->execute();
        if ($stmt_check->rowCount() > 0) {
            error_log('[Order::updateStock] ⚠️ Producto ID: ' . $product_id . ' desactivado (stock < min_quantity)');
        }
    }

    public function getOrderById($order_id) {
        error_log('[Order::getOrderById] Buscando orden #' . $order_id);
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$order_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log('[Order::getOrderById] ' . ($result ? '✅ Orden encontrada | Status: ' . $result['payment_status'] : '❌ Orden NO encontrada'));
        return $result;
    }

    public function getOrderDetailsById($order_id) {
        $query = "SELECT od.*, p.name FROM " . $this->details_table . " od
                  JOIN products p ON od.product_id = p.id
                  WHERE od.order_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

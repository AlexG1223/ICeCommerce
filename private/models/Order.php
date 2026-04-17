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
        foreach($cart_items as $item) {
            $query = "SELECT stock FROM products WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$item['id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(!$row || $row['stock'] < $item['quantity']) {
                return false;
            }
        }
        return true;
    }

    public function create($data, $cart_items) {
        try {
            $this->conn->beginTransaction();

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
                throw new Exception("Error creating order");
            }

            $order_id = $this->conn->lastInsertId();

            // Insert items
            foreach($cart_items as $item) {
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
                $this->updateStock($item['id'], $item['quantity']);
            }

            $this->conn->commit();
            return $order_id;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function updatePreferenceId($order_id, $preference_id) {
        $query = "UPDATE " . $this->table_name . " SET preference_id = :pref WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pref', $preference_id);
        $stmt->bindParam(':id', $order_id);
        $result = $stmt->execute();
        return $result;
    }

    public function updatePaymentStatusByOrderId($order_id, $status) {
        $query = "UPDATE " . $this->table_name . " SET payment_status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $order_id);
        $result = $stmt->execute();
        return $result;
    }
    public function updatePaymentStatus($preference_id, $status) {
        $query = "UPDATE " . $this->table_name . " SET payment_status = :status WHERE preference_id = :pref";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':pref', $preference_id);
        $result = $stmt->execute();
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
        $query = "SELECT product_id, quantity FROM " . $this->details_table . " WHERE order_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $order_id);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($items as $item) {
            $query_upd = "UPDATE products SET stock = stock + :qty WHERE id = :id";
            $stmt_upd = $this->conn->prepare($query_upd);
            $stmt_upd->bindParam(':qty', $item['quantity']);
            $stmt_upd->bindParam(':id', $item['product_id']);
            $stmt_upd->execute();

            $query_act = "UPDATE products SET is_active = 1 WHERE id = :id AND stock >= min_quantity AND is_active = 0";
            $stmt_act = $this->conn->prepare($query_act);
            $stmt_act->bindParam(':id', $item['product_id']);
            $stmt_act->execute();
        }
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
        $query = "UPDATE products SET stock = stock - :qty WHERE id = :id AND stock >= :qty";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':qty', $quantity);
        $stmt->bindParam(':id', $product_id);
        $stmt->execute();
        $rowsAffected = $stmt->rowCount();

        // Check if we need to deactivate
        $query_check = "UPDATE products SET is_active = 0 WHERE id = :id AND stock < min_quantity AND is_active = 1";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(':id', $product_id);
        $stmt_check->execute();
        if ($stmt_check->rowCount() > 0) {
        }
    }

    public function getOrderById($order_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$order_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
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

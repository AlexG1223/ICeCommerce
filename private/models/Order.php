<?php
// private/models/Order.php

class Order {
    private $conn;
    private $table_name = "orders";
    private $details_table = "order_details";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data, $cart_items) {
        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO " . $this->table_name . " 
                      (customer_name, customer_phone, customer_email, customer_address, notes, total, payment_method, payment_status, preference_id) 
                      VALUES (:name, :phone, :email, :address, :notes, :total, :payment_method, :payment_status, :preference_id)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':notes', $data['notes']);
            $stmt->bindParam(':total', $data['total']);
            $stmt->bindParam(':payment_method', $data['payment_method']);
            
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

                // If manual payment, we could discount stock now, or wait until admin confirms.
                // For MVP, manual payment discounts stock immediately. MP will discount via webhook or also immediately?
                // Given standard flow: MP discounts stock only after payment confirmation. Manual discounts stock on order creation to "reserve" it.
                // Let's keep it simple: we discount on creation for manual, and MP discounts on webhook.
                if($data['payment_method'] === 'manual') {
                    $this->updateStock($item['id'], $item['quantity']);
                }
            }

            $this->conn->commit();
            return $order_id;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function updatePaymentStatus($preference_id, $status) {
        $query = "UPDATE " . $this->table_name . " SET payment_status = :status WHERE preference_id = :pref";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':pref', $preference_id);
        return $stmt->execute();
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

    public function updateStock($product_id, $quantity) {
        $query = "UPDATE products SET stock = stock - :qty WHERE id = :id AND stock >= :qty";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':qty', $quantity);
        $stmt->bindParam(':id', $product_id);
        $stmt->execute();
    }
}
?>

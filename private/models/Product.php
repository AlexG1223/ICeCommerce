<?php
// private/models/Product.php

class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $category_id;
    public $name;
    public $description;
    public $price;
    public $stock;
    public $image;

    public $min_quantity;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.is_active = 1
                  ORDER BY p.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByCategory($category_id) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.category_id = ? AND p.is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $category_id);
        $stmt->execute();
        return $stmt;
    }

    public function search($keywords) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE (p.name LIKE ? OR p.description LIKE ?) AND p.is_active = 1";
        $stmt = $this->conn->prepare($query);
        $keywords = "%{$keywords}%";
        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->execute();
        return $stmt;
    }
    
    public function readOne($id) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->name = $row['name'];
            $this->price = $row['price'];
            $this->description = $row['description'];
            $this->image = $row['image'];
            $this->stock = $row['stock'];
            $this->min_quantity = $row['min_quantity'];
            return $row;
        }
        return false;
    }
}
?>

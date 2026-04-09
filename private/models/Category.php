<?php
// private/models/Category.php

class Category {
    private $conn;
    private $table_name = "categories";

    public $id;
    public $name;
    public $slug;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>

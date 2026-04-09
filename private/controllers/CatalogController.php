<?php
// private/controllers/CatalogController.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

class CatalogController {
    private $db;
    private $product;
    private $category;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->product = new Product($this->db);
        $this->category = new Category($this->db);
    }

    public function getProducts($categoryId = null, $searchQuery = null) {
        if ($searchQuery) {
            $stmt = $this->product->search($searchQuery);
        } else if ($categoryId) {
            $stmt = $this->product->readByCategory($categoryId);
        } else {
            $stmt = $this->product->readAll();
        }

        $products = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($products, $row);
        }
        return $products;
    }

    public function getCategories() {
        $stmt = $this->category->readAll();
        $categories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($categories, $row);
        }
        return $categories;
    }

    public function getProductDetail($id) {
        return $this->product->readOne($id);
    }
}
?>

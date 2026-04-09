<?php
// api/getProducts.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["success" => false, "message" => "No se pudo conectar a la base de datos."]);
    exit;
}

try {
    $query = "SELECT 
                p.id, p.name, p.description, p.price, p.stock, p.min_quantity, p.is_active,
                c.name as category_name,
                pi.url as main_image
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
              ORDER BY p.id DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $products
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false, 
        "message" => "Error en la consulta: " . $e->getMessage()
    ]);
}
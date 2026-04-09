<?php
// public_html/api/products.php
header('Content-Type: application/json');

require_once __DIR__ . '/../../private/controllers/CatalogController.php';

$controller = new CatalogController();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

if ($action === 'categories') {
    $categories = $controller->getCategories();
    echo json_encode(['success' => true, 'data' => $categories]);
    exit;
}

if ($action === 'list') {
    $category_id = isset($_GET['category']) ? $_GET['category'] : null;
    $search = isset($_GET['search']) ? $_GET['search'] : null;
    
    $products = $controller->getProducts($category_id, $search);
    echo json_encode(['success' => true, 'data' => $products]);
    exit;
}

if ($action === 'detail') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id > 0) {
        $product = $controller->getProductDetail($id);
        if ($product) {
            echo json_encode(['success' => true, 'data' => $product]);
            exit;
        }
    }
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>

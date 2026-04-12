<?php
// public_html/api/actions/saveProduct.php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

// Instanciamos la clase y obtenemos la conexión PDO
$database = new Database();
$db = $database->getConnection();

session_start();
if (isset($_POST["name"]) && isset($_POST["category_id"]) && isset($_POST["price"])) {

    $nombre = trim($_POST["name"]);
    $descripcion = isset($_POST["description"]) ? trim($_POST["description"]) : "";
    $precio = $_POST["price"];
    $stock = isset($_POST["stock"]) ? $_POST["stock"] : 0;
    $min_cant = isset($_POST["min_quantity"]) ? $_POST["min_quantity"] : 1;
    $id_cat = $_POST["category_id"];

    guardarProducto($db, $nombre, $descripcion, $precio, $stock, $min_cant, $id_cat);

} else {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos.']);
}

function guardarProducto($db, $nombre, $descripcion, $precio, $stock, $min_cant, $id_cat)
{
    try {
        $db->beginTransaction();

        // 1. Insertar Producto usando PDO
        $query = "INSERT INTO products (name, description, price, stock, min_quantity, category_id, is_active) 
                  VALUES (:name, :desc, :price, :stock, :min, :cat, 1)";

        $stmt = $db->prepare($query);

        $stmt->bindParam(':name', $nombre);
        $stmt->bindParam(':desc', $descripcion);
        $stmt->bindParam(':price', $precio);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':min', $min_cant);
        $stmt->bindParam(':cat', $id_cat);

        if ($stmt->execute()) {
            $id_producto = $db->lastInsertId();

            // 2. Manejo de imágenes (si existen)
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                procesarImagenes($db, $id_producto, $_FILES['images']);
            }

            $db->commit();

            echo json_encode([
                'status' => 'success',
                'message' => 'Producto guardado correctamente.',
                'id' => $id_producto
            ]);
        } else {
            $db->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'No se pudo ejecutar la inserción.']);
        }

    } catch (Exception $e) {
        if ($db->inTransaction())
            $db->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => 'Error en el servidor: ' . $e->getMessage()
        ]);
    }
}

function procesarImagenes($db, $id_producto, $fotos)
{
    $directorio = __DIR__ . '/../../../public_html/img/products/';

    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }

    foreach ($fotos['tmp_name'] as $indice => $tmp_name) {
        $nombre_archivo = time() . "_" . $fotos['name'][$indice];
        $ruta_final = $directorio . $nombre_archivo;
        $url_db = "assets/img/products/" . $nombre_archivo;

        if (move_uploaded_file($tmp_name, $ruta_final)) {
            // La primera imagen (índice 0) será la principal (is_primary = 1)
            $es_principal = ($indice === 0) ? 1 : 0;

            $query_img = "INSERT INTO product_images (product_id, url, is_primary) VALUES (?, ?, ?)";
            $stmt_img = $db->prepare($query_img);
            $stmt_img->execute([$id_producto, $url_db, $es_principal]);
        }
    }
}
?>
<?php
// private/actions/updateProduct.php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

$input = file_get_contents("php://input");
$data = json_decode($input, true);

// If it's a JSON request (e.g. from toggle status)
if ($data && isset($data["id"])) {
    $id = $data["id"];
    
    // Handle toggle status
    if (isset($data["is_active"])) {
        actualizarEstado($db, $id, $data["is_active"]);
        exit;
    }
}

// If it's a FormData request (e.g. from edit form)
if (isset($_POST["id"]) && isset($_POST["name"])) {
    $id = $_POST["id"];
    $nombre = trim($_POST["name"]);
    $descripcion = isset($_POST["description"]) ? trim($_POST["description"]) : "";
    $precio = $_POST["price"];
    $stock = isset($_POST["stock"]) ? $_POST["stock"] : 0;
    $min_cant = isset($_POST["min_quantity"]) ? $_POST["min_quantity"] : 1;
    $id_cat = $_POST["category_id"];
    $is_active = isset($_POST["is_active"]) ? 1 : 0;

    actualizarProducto($db, $id, $nombre, $descripcion, $precio, $stock, $min_cant, $id_cat, $is_active);
} else if ($data && isset($data["id"]) && !isset($data["is_active"])) {
     // This case might not be used if we use FormData for full edits
    echo json_encode(['status' => 'error', 'message' => 'Datos insuficientes para actualizar producto.']);
} else if (!$data && !isset($_POST["id"])) {
    echo json_encode(['status' => 'error', 'message' => 'ID de producto no proporcionado.']);
}

function actualizarEstado($db, $id, $is_active) {
    try {
        $query = "UPDATE products SET is_active = :is_active WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':is_active', $is_active);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Estado actualizado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el estado.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function actualizarProducto($db, $id, $nombre, $descripcion, $precio, $stock, $min_cant, $id_cat, $is_active) {
    try {
        $db->beginTransaction();

        $query = "UPDATE products 
                  SET name = :name, description = :desc, price = :price, stock = :stock, 
                      min_quantity = :min, category_id = :cat, is_active = :active 
                  WHERE id = :id";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $nombre);
        $stmt->bindParam(':desc', $descripcion);
        $stmt->bindParam(':price', $precio);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':min', $min_cant);
        $stmt->bindParam(':cat', $id_cat);
        $stmt->bindParam(':active', $is_active);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            // 1. Manejo de eliminación de imágenes
            if (isset($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $url) {
                    eliminarImagenFisica($url);
                    $query_del = "DELETE FROM product_images WHERE url = ? AND product_id = ?";
                    $stmt_del = $db->prepare($query_del);
                    $stmt_del->execute([$url, $id]);
                }
            }

            // 2. Manejo de imagen de portada (is_primary)
            if (isset($_POST['primary_image'])) {
                $primary_url = $_POST['primary_image'];
                // Resetear todas a 0
                $query_reset = "UPDATE product_images SET is_primary = 0 WHERE product_id = ?";
                $stmt_reset = $db->prepare($query_reset);
                $stmt_reset->execute([$id]);
                
                // Setear la elegida a 1
                $query_set = "UPDATE product_images SET is_primary = 1 WHERE url = ? AND product_id = ?";
                $stmt_set = $db->prepare($query_set);
                $stmt_set->execute([$primary_url, $id]);
            }

            // 3. Manejo de nuevas imágenes
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                procesarImagenes($db, $id, $_FILES['images']);
            }

            $db->commit();
            echo json_encode(['status' => 'success', 'message' => 'Producto actualizado correctamente.']);
        } else {
            $db->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el producto.']);
        }
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function eliminarImagenFisica($url) {
    // La URL viene como "public_html/img/products/..."
    $ruta_archivo = __DIR__ . '/../../' . $url;
    if (file_exists($ruta_archivo)) {
        unlink($ruta_archivo);
    }
}

function procesarImagenes($db, $id_producto, $fotos) {
    $directorio = __DIR__ . '/../../public_html/img/products/';
    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }

    foreach ($fotos['tmp_name'] as $indice => $tmp_name) {
        if (empty($tmp_name)) continue;
        $nombre_archivo = time() . "_" . $fotos['name'][$indice];
        $ruta_final = $directorio . $nombre_archivo;
        $url_db = "public_html/img/products/" . $nombre_archivo;

        if (move_uploaded_file($tmp_name, $ruta_final)) {
            // Check if there are already images to decide if this one is primary
            $query_check = "SELECT COUNT(*) FROM product_images WHERE product_id = ?";
            $stmt_check = $db->prepare($query_check);
            $stmt_check->execute([$id_producto]);
            $count = $stmt_check->fetchColumn();
            
            $es_principal = ($count == 0 && $indice === 0) ? 1 : 0;

            $query_img = "INSERT INTO product_images (product_id, url, is_primary) VALUES (?, ?, ?)";
            $stmt_img = $db->prepare($query_img);
            $stmt_img->execute([$id_producto, $url_db, $es_principal]);
        }
    }
}
?>

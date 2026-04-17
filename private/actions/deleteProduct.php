<?php
// public_html/api/actions/deleteProduct.php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (isset($data["id"])) {
    $id_producto = $data["id"];
    eliminarProducto($db, $id_producto);
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID de producto no proporcionado.']);
}

function eliminarProducto($db, $id)
{
    try {
        $db->beginTransaction();

        // A. Obtener las imágenes antes de borrar para eliminarlas del servidor
        $query_imgs = "SELECT url FROM product_images WHERE product_id = ?";
        $stmt_imgs = $db->prepare($query_imgs);
        $stmt_imgs->execute([$id]);
        $imagenes = $stmt_imgs->fetchAll(PDO::FETCH_ASSOC);

        // B. Eliminar el producto (esto borrará registros en product_images por la FK)
        $query_del = "DELETE FROM products WHERE id = ?";
        $stmt_del = $db->prepare($query_del);

        if ($stmt_del->execute([$id])) {

            // C. Si la base de datos borró con éxito, borramos los archivos físicos
            foreach ($imagenes as $img) {
                $ruta_archivo = __DIR__ . '/../../' . $img['url'];
                if (file_exists($ruta_archivo)) {
                    unlink($ruta_archivo);
                }
            }

            $db->commit();
            echo json_encode([
                'status' => 'success',
                'message' => 'Producto e imágenes eliminados correctamente.'
            ]);
        } else {
            $db->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar el producto de la base de datos.']);
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
?>

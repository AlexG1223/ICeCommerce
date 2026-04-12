<?php
require 'private/config/database.php';
$db = new Database();
$conn = $db->getConnection();
try {
    $conn->exec("ALTER TABLE orders ADD COLUMN shipping_agency VARCHAR(150) DEFAULT NULL");
    echo "DB Updated successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

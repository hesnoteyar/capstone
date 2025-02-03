<?php
// filepath: /d:/XAMPP/htdocs/capstone/admin/delete_product.php
session_start();
include '../authentication/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $product_id = $_DELETE['id'];

    if (empty($product_id)) {
        echo json_encode(['success' => false, 'error' => 'Product ID is required.']);
        exit;
    }

    $sql = "DELETE FROM product WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>
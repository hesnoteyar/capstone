<?php
session_start();
include '../authentication/db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'];
$requested_quantity = $data['requested_quantity'];

$stmt = $conn->prepare("SELECT quantity FROM product WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if ($product) {
    $available = $product['quantity'] >= $requested_quantity;
    echo json_encode([
        'available' => $available,
        'current_stock' => $product['quantity']
    ]);
} else {
    echo json_encode([
        'available' => false,
        'error' => 'Product not found'
    ]);
}

$stmt->close();
$conn->close();
?>

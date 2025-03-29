<?php
session_start();
include '../authentication/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'];
$requested_quantity = $data['quantity'];

$sql = "SELECT quantity FROM product WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $available = $row['quantity'] >= $requested_quantity;
    echo json_encode(['available' => $available]);
} else {
    echo json_encode(['available' => false]);
}

$stmt->close();
$conn->close();
?>

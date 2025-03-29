<?php
session_start();
include '../authentication/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'];
$quantity = $data['quantity'];

$sql = "UPDATE product SET quantity = quantity - ? WHERE product_id = ? AND quantity >= ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $quantity, $product_id, $quantity);
$result = $stmt->execute();

echo json_encode(['success' => $result]);

$stmt->close();
$conn->close();
?>

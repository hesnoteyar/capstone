<?php
session_start();
include '../authentication/db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'];
$quantity = $data['quantity'];

$stmt = $conn->prepare("UPDATE product SET quantity = quantity - ? WHERE product_id = ? AND quantity >= ?");
$stmt->bind_param("iii", $quantity, $product_id, $quantity);

$response = ['success' => false];

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = 'Quantity updated successfully';
    } else {
        $response['message'] = 'No update performed - insufficient stock';
    }
} else {
    $response['message'] = 'Error updating quantity';
}

echo json_encode($response);

$stmt->close();
$conn->close();
?>

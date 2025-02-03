<?php
// filepath: /d:/XAMPP/htdocs/capstone/admin/get_product_details.php
session_start();
include '../authentication/db.php';

$product_id = $_GET['id'];

$sql = "SELECT product_id, name, description, price, stock_quantity, category_id, image FROM product WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

$product['image'] = base64_encode($product['image']);

header('Content-Type: application/json');
echo json_encode($product);

$stmt->close();
$conn->close();
?>
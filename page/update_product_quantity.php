<?php
include '../authentication/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = $data['product_id'] ?? null;
    $quantity = $data['quantity'] ?? null;

    if ($productId && $quantity) {
        // First check if we have enough quantity
        $checkSql = "SELECT stock_quantity FROM product WHERE product_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("i", $productId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $product = $result->fetch_assoc();
        
        if (!$product) {
            echo json_encode(["success" => false, "message" => "Product not found"]);
            exit;
        }
        
        if ($product['stock_quantity'] < $quantity) {
            echo json_encode(["success" => false, "message" => "Not enough stock available. Only " . $product['stock_quantity'] . " items left."]);
            exit;
        }
        
        // Update the quantity
        $updateSql = "UPDATE product SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ii", $quantity, $productId);
        
        if ($updateStmt->execute()) {
            echo json_encode(["success" => true, "message" => "Quantity updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error updating quantity"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid request parameters"]);
    }
}
?>

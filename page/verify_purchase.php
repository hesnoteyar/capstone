<?php
session_start();
include '../authentication/db.php';

// Verify if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);
$product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;

if (!$product_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
    exit;
}

// Check if user has purchased this product
$sql = "SELECT * FROM purchase_history 
        WHERE user_id = ? AND product_id = ? 
        LIMIT 1";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User has purchased this product
    echo json_encode([
        'status' => 'success',
        'has_purchased' => true,
        'message' => 'You can review this product'
    ]);
} else {
    // User has not purchased this product
    echo json_encode([
        'status' => 'success',
        'has_purchased' => false,
        'message' => 'You need to purchase this product before leaving a review'
    ]);
}

$stmt->close();
$conn->close();
?>

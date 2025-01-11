<?php
session_start();
header('Content-Type: application/json');
include '..\authentication\db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['product_id'])) {
    $product_id = (int)$data['product_id'];

    // Fetch reviews for the product
    $stmt = $conn->prepare("SELECT r.rating, r.comment AS review_text, CONCAT(u.firstName, ' ', u.lastName) AS username, r.created_at 
                            FROM reviews r 
                            JOIN users u ON r.id = u.id 
                            WHERE r.product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $review_result = $stmt->get_result();

    $reviews = [];
    while ($review_row = $review_result->fetch_assoc()) {
        $reviews[] = $review_row;
    }
    $stmt->close();

    echo json_encode(['status' => 'success', 'reviews' => $reviews]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing product ID.']);
}

$conn->close(); 
?>

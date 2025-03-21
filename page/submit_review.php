<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');
include '../authentication/db.php';

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

// Check if the request is POST and contains the necessary data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            throw new Exception('Invalid JSON data received');
        }

        if (!isset($data['product_name'], $data['rating'], $data['review_text'])) {
            throw new Exception('Missing required fields');
        }

        $product_name = $conn->real_escape_string($data['product_name']);
        $user_id = (int)$_SESSION['id'];
        $rating = (int)$data['rating'];
        $review_text = $conn->real_escape_string($data['review_text']);

        // Validate the rating
        if ($rating < 1 || $rating > 5) {
            throw new Exception('Invalid rating value');
        }

        // Get product_id from product_name
        $product_query = "SELECT product_id FROM product WHERE name = ?";
        $stmt = $conn->prepare($product_query);
        $stmt->bind_param("s", $product_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Product not found');
        }

        $product_row = $result->fetch_assoc();
        $product_id = (int)$product_row['product_id'];

        // Check if user has already reviewed this product
        $check_query = "SELECT id FROM reviews WHERE product_id = ? AND id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $product_id, $user_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            // Update existing review
            $update_sql = "UPDATE reviews SET comment = ?, rating = ?, review_date = CURRENT_TIMESTAMP 
                          WHERE product_id = ? AND id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("siii", $review_text, $rating, $product_id, $user_id);
            $success = $update_stmt->execute();
        } else {
            // Insert new review
            $insert_sql = "INSERT INTO reviews (product_id, id, comment, rating) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iisi", $product_id, $user_id, $review_text, $rating);
            $success = $insert_stmt->execute();
        }

        if ($success) {
            echo json_encode(['status' => 'success', 'message' => 'Review submitted successfully']);
        } else {
            throw new Exception('Database error: ' . $conn->error);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?>

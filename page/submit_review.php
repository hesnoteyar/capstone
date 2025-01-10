<?php
session_start();
header('Content-Type: application/json');
include '..\authentication\db.php';

// Check if the request is POST and contains the necessary data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['product_name'], $data['rating'], $data['review_text'])) {
        $product_name = $conn->real_escape_string($data['product_name']);
        $user_id = $_SESSION['id']; // Use 'id' from session
        $rating = (int)$data['rating'];
        $review_text = $conn->real_escape_string($data['review_text']);

        // Validate the rating (must be between 1 and 5)
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid rating.']);
            exit;
        }

        // Get product_id from product_name
        $product_query = "SELECT product_id FROM Product WHERE name = '$product_name'";
        $product_result = $conn->query($product_query);
        if ($product_result->num_rows > 0) {
            $product_row = $product_result->fetch_assoc();
            $product_id = (int)$product_row['product_id'];

            // Insert the review into the database
            $sql = "INSERT INTO reviews (product_id, id, comment, rating) 
                    VALUES ($product_id, $user_id, '$review_text', $rating)";

            if ($conn->query($sql) === TRUE) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to submit review.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>

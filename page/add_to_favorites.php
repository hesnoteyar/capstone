<?php
session_start();
include '../authentication/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['product_id']) && isset($_SESSION['id'])) {
        $productId = (int)$data['product_id'];
        $userId = (int)$_SESSION['id'];
        $date = date('Y-m-d H:i:s');

        // Check if the product is already in the user's favorites
        $checkQuery = "SELECT * FROM favorites WHERE userid = ? AND productid = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Product is already in your favorites.']);
        } else {
            // Insert the product into the favorites table
            $insertQuery = "INSERT INTO favorites (userid, productid, date) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param('iis', $userId, $productId, $date);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Product added to favorites successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add product to favorites.']);
            }
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
<?php
session_start();
include '../authentication/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = $data['product_id'];
    $userId = $_SESSION['id']; // Assuming user_id is stored in session

    if ($productId && $userId) {
        $stmt = $conn->prepare("DELETE FROM favorites WHERE userid = ? AND productid = ?");
        $stmt->bind_param("ii", $userId, $productId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete favorite item.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product or user.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>

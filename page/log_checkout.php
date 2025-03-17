<?php
session_start();
include '../authentication/db.php'; // Include your database connection

// Check if the user is logged in by ensuring the user_id exists in the session
if (isset($_SESSION['id'])) {
    $id = $_SESSION['id']; // Get user_id from the session
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['action'], $data['item'], $data['total_price'])) {
        $action = $data['action'];
        $item = $data['item'];
        $activityDate = date('Y-m-d H:i:s'); // Current timestamp for activity_date

        // Insert into audit_logs table
        $auditQuery = "INSERT INTO audit_logs (user_id, action, item, activity_date) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($auditQuery);
        $stmt->bind_param("isss", $id, $action, $item, $activityDate);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to log checkout action']);
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User is not logged in']);
}

// Close the database connection
$conn->close();
?>

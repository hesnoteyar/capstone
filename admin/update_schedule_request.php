<?php
include '../authentication/db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];

    // Update the status of the schedule request
    $query = "UPDATE schedule_requests SET status = ? WHERE request_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $request_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
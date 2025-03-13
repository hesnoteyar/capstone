<?php
session_start();
include '../authentication/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id'])) {
    $user_id = $_SESSION['id'];
    $request_id = mysqli_real_escape_string($conn, $_POST['request_id']);
    
    $sql = "UPDATE service_inquiries SET status = 'Cancelled' WHERE id = ? AND user_id = ? AND status = 'Pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $request_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Request cancelled successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error cancelling request.";
        $_SESSION['message_type'] = "error";
    }
    
    header("Location: inquiry.php");
    exit();
}
?>

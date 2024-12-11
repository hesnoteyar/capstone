<?php
session_start(); // Start the session

// Include your database connection
include '..\authentication\db.php';

// Check if the user is logged in (optional)
if (isset($_SESSION['id'])) {
    $userId = $_SESSION['id'];

    // Insert into audit_logs for logout activity
    $action = 'LOGGED OUT';
    $item = 'N/A'; // No specific item associated with logout
    $auditQuery = "INSERT INTO audit_logs (user_id, action, item) VALUES (?, ?, ?)";
    $auditStmt = $conn->prepare($auditQuery);
    $auditStmt->bind_param("iss", $userId, $action, $item);
    
    if (!$auditStmt->execute()) {
        // Optionally handle logging failure
        error_log("Failed to log audit: " . $auditStmt->error);
    }

    // Close the prepared statement
    $auditStmt->close();
}

// Unset all of the session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to the login page
header("location: ..\index.php");
exit;
?>

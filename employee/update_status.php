<?php
session_start();
include '../authentication/db.php';

// Check if user is logged in and is a mechanic
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'Mechanic') {
    header("Location: ../index.php");
    exit;
}

$employee_id = $_SESSION['id'];
$employee_name = $_SESSION['firstName'] . " " . $_SESSION['lastName'];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $inquiry_id = isset($_POST['inquiry_id']) ? $_POST['inquiry_id'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    
    // Validate data
    if (empty($inquiry_id) || empty($status)) {
        header("Location: employee_inquiries.php?error=failed");
        exit;
    }
    
    // Make sure the status is valid
    $valid_statuses = ['Claimed', 'In Progress', 'Completed'];
    if (!in_array($status, $valid_statuses)) {
        header("Location: employee_inquiries.php?error=failed");
        exit;
    }
    
    // First verify that this inquiry is actually assigned to this mechanic
    $verify_query = "SELECT * FROM service_inquiries WHERE id = ? AND service_representative = ?";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("is", $inquiry_id, $employee_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // This inquiry is not assigned to the current mechanic
        header("Location: employee_inquiries.php?error=failed");
        exit;
    }
    
    // Update the status
    $update_query = "UPDATE service_inquiries SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $status, $inquiry_id);
    
    if ($stmt->execute()) {
        header("Location: employee_inquiries.php?success=status_updated");
    } else {
        header("Location: employee_inquiries.php?error=failed");
    }
    
    $stmt->close();
} else {
    // If not POST request, redirect to inquiries page
    header("Location: employee_inquiries.php");
}

$conn->close();
?>

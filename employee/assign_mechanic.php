<?php
session_start();
include '../authentication/db.php';

// Check if the user is a Head Mechanic
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Head Mechanic') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inquiry_id = $_POST['inquiry_id'];
    $mechanic_name = $_POST['mechanic_name'];
    
    // Update the inquiry with the assigned mechanic
    $update_query = "UPDATE service_inquiries SET service_representative = ?, status = 'Assigned' WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $mechanic_name, $inquiry_id);
    
    if ($stmt->execute()) {
        header("Location: employee_inquiries.php?success=assigned");
        exit;
    } else {
        header("Location: employee_inquiries.php?error=failed");
        exit;
    }
}

// Redirect if accessed directly
header("Location: employee_inquiries.php");
exit;

<?php
session_start();
include '../authentication/db.php';

// Check if user is logged in and is a Head Mechanic
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'Head Mechanic') {
    header('Location: ../authentication/login.php');
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $inquiry_id = mysqli_real_escape_string($conn, $_POST['inquiry_id']);
    $mechanic = mysqli_real_escape_string($conn, $_POST['mechanic']);
    
    // Update the inquiry with the assigned mechanic
    $update_query = "UPDATE service_inquiries SET 
                     service_representative = '$mechanic',
                     status = 'Claimed'
                     WHERE id = '$inquiry_id'";
                     
    if (mysqli_query($conn, $update_query)) {
        // Success
        header('Location: employee_inquiries.php?success=assigned');
        exit;
    } else {
        // Error
        header('Location: employee_inquiries.php?error=' . urlencode(mysqli_error($conn)));
        exit;
    }
} else {
    // If not a POST request, redirect to inquiries page
    header('Location: employee_inquiries.php');
    exit;
}
?>

<?php
session_start();
include '../authentication/db.php';

// Get employee info from session
$employee_id = $_SESSION['id'];
$employee_name = $_SESSION['firstName'] . " " . $_SESSION['lastName'];
$role = $_SESSION['role'];

// Get inquiry ID from POST data
if(isset($_POST['inquiry_id'])) {
    $inquiry_id = $_POST['inquiry_id'];
    
    // Determine which mechanic to assign
    if ($role == 'Head Mechanic' && isset($_POST['assigned_mechanic'])) {
        // Head mechanic is assigning to another mechanic
        $mechanic_id = $_POST['assigned_mechanic'];
        
        // Get the assigned mechanic's name
        if ($mechanic_id == $employee_id) {
            // Self-assignment
            $mechanic_name = $employee_name;
        } else {
            // Get the assigned mechanic's name from database
            $mechanic_query = "SELECT CONCAT(firstName, ' ', lastName) as fullName FROM employee WHERE id = ?";
            $stmt = mysqli_prepare($conn, $mechanic_query);
            mysqli_stmt_bind_param($stmt, "i", $mechanic_id);
            mysqli_stmt_execute($stmt);
            $mechanic_result = mysqli_stmt_get_result($stmt);
            $mechanic_data = mysqli_fetch_assoc($mechanic_result);
            $mechanic_name = $mechanic_data['fullName'];
        }
    } else {
        // Regular mechanic claiming for themselves
        $mechanic_id = $employee_id;
        $mechanic_name = $employee_name;
    }
    
    // Update the inquiry in the database
    $query = "UPDATE service_inquiries SET status = 'Claimed', service_representative = ?, mechanic_id = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sii", $mechanic_name, $mechanic_id, $inquiry_id);
    $result = mysqli_stmt_execute($stmt);
    
    if($result) {
        // Success
        header("Location: employee_inquiries.php?success=claimed");
    } else {
        // Error
        header("Location: employee_inquiries.php?error=claim_failed");
    }
} else {
    // No inquiry ID provided
    header("Location: employee_inquiries.php?error=no_inquiry");
}
?>
<?php
session_start();
include '../authentication/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inquiry_id = $_POST['inquiry_id'] ?? '';
    $mechanic_name = $_POST['mechanic_name'] ?? '';
    
    if (empty($inquiry_id) || empty($mechanic_name)) {
        header("Location: employee_inquiries.php?error=failed");
        exit;
    }
    
    // Update the service inquiry with the assigned mechanic
    $update_query = "UPDATE service_inquiries SET service_representative = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $mechanic_name, $inquiry_id);
    $result = mysqli_stmt_execute($stmt);
    
    if ($result) {
        // If assignment was successful, redirect to the PDF generation
        $head_mechanic_name = $_SESSION['firstName'] . " " . $_SESSION['lastName'];
        header("Location: generate_assignment_pdf.php?inquiry_id=" . $inquiry_id . "&mechanic=" . urlencode($mechanic_name) . "&head_mechanic=" . urlencode($head_mechanic_name));
        exit;
    } else {
        header("Location: employee_inquiries.php?error=failed");
        exit;
    }
} else {
    header("Location: employee_inquiries.php");
    exit;
}
?>

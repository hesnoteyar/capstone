<?php
session_start();
include '../authentication/db.php';

// Check if table exists and create if it doesn't
$check_table = "SHOW TABLES LIKE 'service_inquiries'";
$table_exists = mysqli_query($conn, $check_table);

if (mysqli_num_rows($table_exists) == 0) {
    // Table doesn't exist, create it
    $sql_create_table = file_get_contents('../database/service_inquiries.sql');
    if (!mysqli_query($conn, $sql_create_table)) {
        die("Error creating table: " . mysqli_error($conn));
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id'];
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $model = mysqli_real_escape_string($conn, $_POST['model']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);
    $service_type = mysqli_real_escape_string($conn, $_POST['service_type']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $preferred_date = mysqli_real_escape_string($conn, $_POST['preferred_date']);
    
    // Generate reference number
    $reference = 'SRV-' . date('Ymd') . '-' . rand(1000, 9999);
    
    $sql = "INSERT INTO service_inquiries (
        user_id, reference_number, brand, model, year_model, 
        service_type, description, contact_number, preferred_date, status
    ) VALUES (
        '$user_id', '$reference', '$brand', '$model', '$year',
        '$service_type', '$description', '$contact', '$preferred_date', 'Pending'
    )";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = "Service request submitted successfully! Reference Number: " . $reference;
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
    
    header("Location: inquiry.php");
    exit();
}
?>

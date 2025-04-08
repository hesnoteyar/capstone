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
    // Verify confirmation checkbox
    if (!isset($_POST['confirm_details'])) {
        $_SESSION['message'] = "Please confirm that all details are accurate.";
        $_SESSION['message_type'] = "error";
        header("Location: inquiry.php");
        exit();
    }

    // Handle file upload with better error checking
    if (!isset($_FILES['proof_image']) || empty($_FILES['proof_image']['tmp_name'])) {
        $_SESSION['message'] = "Please upload a proof of payment image.";
        $_SESSION['message_type'] = "error";
        header("Location: inquiry.php");
        exit();
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    $file_type = mime_content_type($_FILES['proof_image']['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        $_SESSION['message'] = "Invalid file type. Please upload a JPEG, PNG, or GIF image.";
        $_SESSION['message_type'] = "error";
        header("Location: inquiry.php");
        exit();
    }

    if ($_FILES['proof_image']['size'] > $max_size) {
        $_SESSION['message'] = "File is too large. Maximum size is 5MB.";
        $_SESSION['message_type'] = "error";
        header("Location: inquiry.php");
        exit();
    }

    $user_id = $_SESSION['id'];
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $model = mysqli_real_escape_string($conn, $_POST['model']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);
    $service_type = mysqli_real_escape_string($conn, $_POST['service_type']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $preferred_date = mysqli_real_escape_string($conn, $_POST['preferred_date']);
    
    // Read image file and convert to base64
    $proof_image = base64_encode(file_get_contents($_FILES['proof_image']['tmp_name']));
    
    // Generate reference number
    $reference = 'SRV-' . date('Ymd') . '-' . rand(1000, 9999);
    
    // Add error reporting
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    try {
        // Prepare the statement with image blob
        $stmt = $conn->prepare("INSERT INTO service_inquiries (
            user_id, reference_number, brand, model, year_model, 
            service_type, description, contact_number, preferred_date, status, proof
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("isssssssss", 
            $user_id, $reference, $brand, $model, $year,
            $service_type, $description, $contact, $preferred_date, $proof_image
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $_SESSION['message'] = "Service request submitted successfully! Reference Number: " . $reference;
        $_SESSION['message_type'] = "success";
        
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }

    $stmt->close();    
    header("Location: inquiry.php");
    exit();
}
?>

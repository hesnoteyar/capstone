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

    // Enhanced file upload debugging
    if (!isset($_FILES['proof_image'])) {
        $_SESSION['message'] = "No file was uploaded. Please try again.";
        $_SESSION['message_type'] = "error";
        header("Location: inquiry.php");
        exit();
    }

    // Check specific upload errors
    switch ($_FILES['proof_image']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
            $_SESSION['message'] = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $_SESSION['message'] = "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form";
            break;
        case UPLOAD_ERR_PARTIAL:
            $_SESSION['message'] = "The uploaded file was only partially uploaded";
            break;
        case UPLOAD_ERR_NO_FILE:
            $_SESSION['message'] = "No file was uploaded";
            break;
        default:
            $_SESSION['message'] = "Unknown upload error occurred (Code: " . $_FILES['proof_image']['error'] . ")";
    }

    if ($_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['message_type'] = "error";
        header("Location: inquiry.php");
        exit();
    }

    // Validate file type and size
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($_FILES['proof_image']['type'], $allowed_types)) {
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
    
    // Read image file for blob storage
    $proof_image = file_get_contents($_FILES['proof_image']['tmp_name']);
    
    // Generate reference number
    $reference = 'SRV-' . date('Ymd') . '-' . rand(1000, 9999);
    
    // Prepare the statement with image blob
    $stmt = $conn->prepare("INSERT INTO service_inquiries (
        user_id, reference_number, brand, model, year_model, 
        service_type, description, contact_number, preferred_date, status, proof
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)");

    $stmt->bind_param("issssssssb", 
        $user_id, $reference, $brand, $model, $year,
        $service_type, $description, $contact, $preferred_date, $proof_image
    );

    if ($stmt->execute()) {
        $_SESSION['message'] = "Service request submitted successfully! Reference Number: " . $reference;
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }

    $stmt->close();    
    header("Location: inquiry.php");
    exit();
}
?>

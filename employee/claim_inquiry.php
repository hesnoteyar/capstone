<?php
session_start();
include '../authentication/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['inquiry_id'])) {
    $inquiry_id = $_POST['inquiry_id'];
    $service_rep = $_SESSION['firstName'] . " " . $_SESSION['lastName'];

    // Update the service inquiry with the service representative
    $update_query = "UPDATE service_inquiries SET service_representative = '$service_rep', status = 'Claimed' WHERE id = '$inquiry_id'";
    
    if (mysqli_query($conn, $update_query)) {
        // Redirect back to the inquiry list with success message
        header("Location: employee_inquiries.php?success=claimed");
        exit;
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
} else {
    // If accessed directly without POST data
    header("Location: employee_inquiries.php");
    exit;
}
?>
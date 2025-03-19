<?php
session_start();
include '../authentication/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['inquiry_id'])) {
    $inquiry_id = $_POST['inquiry_id'];
    $service_rep = $_SESSION['firstName'] . " " . $_SESSION['lastName'];

    // Update the service inquiry with the service representative
    $update_query = "UPDATE service_inquiries SET service_representative = '$service_rep', status = 'Claimed' WHERE id = '$inquiry_id'";
    
    if (mysqli_query($conn, $update_query)) {
        header("Location: employee_inquiries.php");
        exit;
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
?>

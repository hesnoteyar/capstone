<?php
include '../authentication/db.php'; // Include your database connection
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = $_SESSION['id']; // Get the employee_id from the session
    $name = $_POST['name'];
    $leave_type = $_POST['leave_type'];
    $reason = $_POST['reason'];
    $leave_start_date = $_POST['leave_start_date'];
    $leave_end_date = $_POST['leave_end_date'];

    // Insert the leave request into the database
    $sql = "INSERT INTO leave_request (employee_id, employee_name, leave_type, leave_reason, leave_start_date, leave_end_date, approval_status) 
            VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $employee_id, $name, $leave_type, $reason, $leave_start_date, $leave_end_date);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Leave request submitted successfully.";
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: employee_leave.php");
    exit();
}
?>

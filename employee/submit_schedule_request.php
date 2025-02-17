<?php
// filepath: /d:/XAMPP/htdocs/capstone/employee/submit_schedule_request.php
include '../authentication/db.php'; // Include your database connection

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_SESSION['id'];
    $employee_name = $_POST['employee_name'];
    $requested_date = $_POST['requested_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $notes = $_POST['notes'];

    // Insert the schedule request into the schedule_requests table
    $query = "INSERT INTO schedule_requests (employee_id, employee_name, requested_date, start_time, end_time, status, notes) VALUES (?, ?, ?, ?, ?, 'Pending', ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssss", $employee_id, $employee_name, $requested_date, $start_time, $end_time, $notes);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Schedule request submitted successfully.";
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: employee_request.php");
    exit();
}
?>
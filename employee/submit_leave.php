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

    // Calculate the number of days between start and end dates
    $start = new DateTime($leave_start_date);
    $end = new DateTime($leave_end_date);
    $interval = $start->diff($end);
    $days = $interval->days + 1; // Including both start and end dates

    // Begin transaction
    $conn->begin_transaction();
    try {
        // Insert the leave request
        $sql = "INSERT INTO leave_request (employee_id, employee_name, leave_type, leave_reason, leave_start_date, leave_end_date, approval_status) 
                VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", $employee_id, $name, $leave_type, $reason, $leave_start_date, $leave_end_date);
        $stmt->execute();

        // Only deduct leaves for Sick Leave and Casual Leave
        if ($leave_type == 'Sick Leave' || $leave_type == 'Casual Leave') {
            // Update remaining leaves in employee table
            $update_sql = "UPDATE employee SET leaves = leaves - ? WHERE employee_id = ? AND leaves >= ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("iii", $days, $employee_id, $days);
            
            if (!$update_stmt->execute()) {
                throw new Exception("Insufficient leave balance");
            }
            $update_stmt->close();
        }

        $conn->commit();
        $_SESSION['success_message'] = "Leave request submitted successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }

    $stmt->close();
    $conn->close();

    header("Location: employee_leave.php");
    exit();
}
?>

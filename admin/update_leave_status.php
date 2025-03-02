<?php
// filepath: /d:/XAMPP/htdocs/capstone/admin/update_leave_status.php
include '../authentication/db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];
    $status = $data['status'];

    // Fetch the leave request details
    $leave_query = "SELECT employee_id, leave_start_date, leave_end_date FROM leave_request WHERE id = ?";
    $leave_stmt = $conn->prepare($leave_query);
    $leave_stmt->bind_param("i", $id);
    $leave_stmt->execute();
    $leave_stmt->bind_result($employee_id, $leave_start_date, $leave_end_date);
    $leave_stmt->fetch();
    $leave_stmt->close();

    // Calculate the number of leave days
    $start_date = new DateTime($leave_start_date);
    $end_date = new DateTime($leave_end_date);
    $interval = $start_date->diff($end_date);
    $number_of_days = $interval->days + 1; // Include the start date

    // Update the leave request status in the database
    $sql = "UPDATE leave_request SET approval_status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        // If the leave request is approved, deduct the leave days from the employee's leave balance
        if ($status == 'Approved') {
            $update_leave_balance_query = "UPDATE employee SET leaves = leaves - ? WHERE employee_id = ?";
            $update_leave_balance_stmt = $conn->prepare($update_leave_balance_query);
            $update_leave_balance_stmt->bind_param("ii", $number_of_days, $employee_id);
            $update_leave_balance_stmt->execute();
            $update_leave_balance_stmt->close();
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
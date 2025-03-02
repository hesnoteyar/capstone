<?php
include '../authentication/db.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo json_encode(['success' => false, 'message' => 'Please log in to record attendance.']);
    exit;
}

// Get data from POST request
$action = $_POST['action'];
$employee_id = $_POST['employee_id'];
$date = $_POST['date'];

if (empty($employee_id) || empty($date)) {
    echo json_encode(['success' => false, 'message' => 'Employee ID or Date is missing.']);
    exit;
}

if ($action == 'clock_in') {
    $check_in_time = $_POST['check_in_time'];

    if (empty($check_in_time)) {
        echo json_encode(['success' => false, 'message' => 'Clock-In time is missing.']);
        exit;
    }

    // Convert check_in_time to DATETIME
    $check_in_datetime = date('Y-m-d H:i:s', strtotime($date . ' ' . $check_in_time));

    // Insert clock-in time into the attendance table
    $stmt = $conn->prepare("INSERT INTO attendance (employee_id, check_in_time, date) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $employee_id, $check_in_datetime, $date);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Clock-In recorded successfully!']);
    } else {
        error_log("Error recording clock-in: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Error recording clock-in: ' . $stmt->error]);
    }
    $stmt->close();
}

if ($action == 'clock_out') {
    $check_out_time = $_POST['check_out_time'];

    if (empty($check_out_time)) {
        echo json_encode(['success' => false, 'message' => 'Clock-Out time is missing.']);
        exit;
    }

    // Convert check_out_time to DATETIME
    $check_out_datetime = date('Y-m-d H:i:s', strtotime($date . ' ' . $check_out_time));

    // Update the attendance record with clock-out time and calculate total hours worked
    $stmt = $conn->prepare("
        UPDATE attendance 
        SET check_out_time = ?, 
            total_hours = LEAST(TIMESTAMPDIFF(SECOND, check_in_time, ?) / 3600.0, 8),
            overtime_hours = GREATEST(TIMESTAMPDIFF(SECOND, check_in_time, ?) / 3600.0 - 8, 0)
        WHERE employee_id = ? 
        AND date = ? 
        AND check_out_time IS NULL
    ");

    $stmt->bind_param("sssis", $check_out_datetime, $check_out_datetime, $check_out_datetime, $employee_id, $date);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Clock-Out recorded successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No matching record found for clock-out.']);
        }
    } else {
        error_log("Error recording clock-out: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Error recording clock-out: ' . $stmt->error]);
    }
    $stmt->close();
}

$conn->close();
?>

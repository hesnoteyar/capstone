<?php
include '../authentication/db.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "Please log in to record attendance.";
    exit;
}

// Get data from POST request
$action = $_POST['action'];
$employee_id = $_POST['employee_id'];
$date = $_POST['date'];

if (empty($employee_id) || empty($date)) {
    echo "Employee ID or Date is missing.";
    exit;
}

if ($action == 'clock_in') {
    $check_in_time = $_POST['check_in_time'];

    if (empty($check_in_time)) {
        echo "Clock-In time is missing.";
        exit;
    }

    // Convert check_in_time to DATETIME
    $check_in_datetime = date('Y-m-d H:i:s', strtotime($date . ' ' . $check_in_time));

    // Insert clock-in time into the attendance table
    $stmt = $conn->prepare("INSERT INTO attendance (employee_id, check_in_time, date) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $employee_id, $check_in_datetime, $date);

    if ($stmt->execute()) {
        echo "Clock-In recorded successfully!";
    } else {
        error_log("Error recording clock-in: " . $stmt->error);
        echo "Error recording clock-in: " . $stmt->error;
    }
    $stmt->close();
}

if ($action == 'clock_out') {
    $check_out_time = $_POST['check_out_time'];

    if (empty($check_out_time)) {
        echo "Clock-Out time is missing.";
        exit;
    }

    // Convert check_out_time to DATETIME
    $check_out_datetime = date('Y-m-d H:i:s', strtotime($date . ' ' . $check_out_time));

    // Update the attendance record with clock-out time and calculate total hours worked
    $stmt = $conn->prepare("
        UPDATE attendance 
        SET check_out_time = ?, 
            total_hours = TIMESTAMPDIFF(SECOND, check_in_time, ?) / 3600.0
        WHERE employee_id = ? 
        AND date = ? 
        AND check_out_time IS NULL
    ");

    $stmt->bind_param("ssis", $check_out_datetime, $check_out_datetime, $employee_id, $date);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "Clock-Out recorded successfully!";
        } else {
            echo "No matching record found for clock-out.";
        }
    } else {
        error_log("Error recording clock-out: " . $stmt->error);
        echo "Error recording clock-out: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>
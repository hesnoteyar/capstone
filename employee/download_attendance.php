<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require('../fpdf/fpdf.php');
include '../authentication/db.php';

// Get employee ID from session
$employee_id = $_SESSION['id'];
$current_month = date('Y-m');

// Get employee details
$emp_query = "SELECT firstName, middleName, lastName FROM employee WHERE employee_id = ?";
$stmt = $conn->prepare($emp_query);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$emp_result = $stmt->get_result()->fetch_assoc();

// Get attendance data
$query = "SELECT 
    date,
    TIME_FORMAT(check_in_time, '%H:%i') as check_in,
    TIME_FORMAT(check_out_time, '%H:%i') as check_out,
    total_hours,
    overtime_hours
    FROM attendance 
    WHERE employee_id = ? 
    AND DATE_FORMAT(date, '%Y-%m') = ?
    ORDER BY date ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $employee_id, $current_month);
$stmt->execute();
$result = $stmt->get_result();

// Create PDF
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'Monthly Attendance Report', 0, 1, 'C');
        $this->Ln(10);
    }
}

$pdf = new PDF();
$pdf->AddPage();

// Employee Info
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Employee: ' . $emp_result['first_name'] . ' ' . $emp_result['last_name'], 0, 1);
$pdf->Cell(0, 10, 'Month: ' . date('F Y'), 0, 1);
$pdf->Ln(10);

// Table Header
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 10, 'Date', 1);
$pdf->Cell(30, 10, 'Check In', 1);
$pdf->Cell(30, 10, 'Check Out', 1);
$pdf->Cell(45, 10, 'Regular Hours', 1);
$pdf->Cell(45, 10, 'Overtime Hours', 1);
$pdf->Ln();

// Table Content
$pdf->SetFont('Arial', '', 10);
$total_hours = 0;
$total_overtime = 0;

while($row = $result->fetch_assoc()) {
    $pdf->Cell(40, 10, date('d M Y', strtotime($row['date'])), 1);
    $pdf->Cell(30, 10, $row['check_in'], 1);
    $pdf->Cell(30, 10, $row['check_out'], 1);
    $pdf->Cell(45, 10, number_format($row['total_hours'], 1), 1);
    $pdf->Cell(45, 10, number_format($row['overtime_hours'], 1), 1);
    $pdf->Ln();
    
    $total_hours += $row['total_hours'];
    $total_overtime += $row['overtime_hours'];
}

// Summary
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, 'Monthly Summary:', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 10, 'Total Regular Hours: ' . number_format($total_hours, 1), 0, 1);
$pdf->Cell(0, 10, 'Total Overtime Hours: ' . number_format($total_overtime, 1), 0, 1);
$pdf->Cell(0, 10, 'Total Working Hours: ' . number_format($total_hours + $total_overtime, 1), 0, 1);

// Output PDF
$pdf->Output('D', 'Attendance_Summary_' . date('F_Y') . '.pdf');

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require('../fpdf/fpdf.php');
include '../authentication/db.php';

class AttendancePDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Attendance Report', 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Month: ' . date('F Y'), 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Create PDF
$pdf = new AttendancePDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 10);

// Get employee details
$employee_id = $_SESSION['id'];
$current_month = date('Y-m');

// Get employee name
$emp_query = "SELECT CONCAT(firstname, ' ', lastname) as employee_name FROM users WHERE id = ?";
$stmt = $conn->prepare($emp_query);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$emp_result = $stmt->get_result()->fetch_assoc();

// Add employee info
$pdf->Cell(0, 10, 'Employee: ' . $emp_result['employee_name'], 0, 1);
$pdf->Ln(5);

// Table header
$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(40, 10, 'Date', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Check In', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Check Out', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Total Hours', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Overtime', 1, 1, 'C', true);

// Get attendance data
$query = "SELECT 
            DATE_FORMAT(date, '%d-%m-%Y') as att_date,
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

$pdf->SetFont('Arial', '', 10);
while($row = $result->fetch_assoc()) {
    $pdf->Cell(40, 10, $row['att_date'], 1, 0, 'C');
    $pdf->Cell(40, 10, $row['check_in'], 1, 0, 'C');
    $pdf->Cell(40, 10, $row['check_out'], 1, 0, 'C');
    $pdf->Cell(35, 10, number_format($row['total_hours'], 1), 1, 0, 'C');
    $pdf->Cell(35, 10, number_format($row['overtime_hours'], 1), 1, 1, 'C');
}

// Add summary
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Monthly Summary', 0, 1);

$summary_query = "SELECT 
                    SUM(total_hours) as total_hours,
                    COUNT(*) as present_days,
                    SUM(overtime_hours) as total_overtime
                 FROM attendance 
                 WHERE employee_id = ? 
                 AND DATE_FORMAT(date, '%Y-%m') = ?";
$stmt = $conn->prepare($summary_query);
$stmt->bind_param("is", $employee_id, $current_month);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 7, 'Total Working Days: ' . $summary['present_days'], 0, 1);
$pdf->Cell(0, 7, 'Total Hours: ' . number_format($summary['total_hours'], 1), 0, 1);
$pdf->Cell(0, 7, 'Total Overtime Hours: ' . number_format($summary['total_overtime'], 1), 0, 1);

// Output PDF
$pdf->Output('Attendance_Report_' . date('Y_m') . '.pdf', 'D');

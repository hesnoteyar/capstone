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
    AND DATE_FORMAT(date, '%Y-%m') COLLATE utf8mb4_general_ci = ? COLLATE utf8mb4_general_ci
    ORDER BY date ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $employee_id, $current_month);
$stmt->execute();
$result = $stmt->get_result();

// Create PDF
class PDF extends FPDF {
    function Header() {
        // Company Logo & Name
        $this->SetFont('Arial', 'B', 18);
        $this->Cell(0, 10, 'ABA RACING ONLINE', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'Attendance Management System', 0, 1, 'C');
        $this->Cell(0, 5, '123 Racing Street, Metro Manila', 0, 1, 'C');
        $this->Cell(0, 5, 'Contact: (123) 456-7890', 0, 1, 'C');
        
        // Report Title
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'MONTHLY ATTENDANCE REPORT', 0, 1, 'C');
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(10);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages(); // For page numbering
$pdf->AddPage();

// Employee Info
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(30, 7, 'Employee:', 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 7, $emp_result['firstName'] . ' ' . 
    ($emp_result['middleName'] ? $emp_result['middleName'] . ' ' : '') . 
    $emp_result['lastName'], 0, 1);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(30, 7, 'Period:', 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 7, date('F Y'), 0, 1);
$pdf->Ln(5);

// Table Header with improved styling
$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(45, 8, 'Date', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Time In', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Time Out', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Regular Hours', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Overtime', 1, 1, 'C', true);

// Table Content with improved formatting
$pdf->SetFont('Arial', '', 10);
$total_hours = 0;
$total_overtime = 0;

while($row = $result->fetch_assoc()) {
    // Format date to standard format
    $formatted_date = date('F d, Y', strtotime($row['date']));
    
    // Convert time to 12-hour format
    $time_in = date('h:i A', strtotime($row['check_in']));
    $time_out = date('h:i A', strtotime($row['check_out']));
    
    $pdf->Cell(45, 7, $formatted_date, 1, 0, 'L');
    $pdf->Cell(35, 7, $time_in, 1, 0, 'C');
    $pdf->Cell(35, 7, $time_out, 1, 0, 'C');
    $pdf->Cell(35, 7, number_format($row['total_hours'], 1), 1, 0, 'C');
    $pdf->Cell(35, 7, number_format($row['overtime_hours'], 1), 1, 1, 'C');
    
    $total_hours += $row['total_hours'];
    $total_overtime += $row['overtime_hours'];
}

// Summary with improved styling
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, 'MONTHLY SUMMARY', 0, 1, 'L');
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(50, 7, 'Total Regular Hours:', 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 7, number_format($total_hours, 1), 0, 1);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(50, 7, 'Total Overtime Hours:', 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 7, number_format($total_overtime, 1), 0, 1);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(50, 7, 'Total Working Hours:', 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 7, number_format($total_hours + $total_overtime, 1), 0, 1);

// Certification
$pdf->Ln(15);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 7, 'This is a computer-generated document. No signature is required.', 0, 1, 'C');

// Output PDF
$pdf->Output('D', 'Attendance_Summary_' . date('F_Y') . '.pdf');

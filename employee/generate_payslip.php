<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require('../fpdf/fpdf.php');
include '../authentication/db.php';

$employee_id = $_SESSION['id'];
$current_month = date('Y-m');

// Fetch employee and payroll data
$query = "SELECT e.*, p.* 
          FROM employee e 
          JOIN payroll p ON e.employee_id = p.employee_id 
          WHERE e.employee_id = ? 
          AND DATE_FORMAT(p.payroll_date, '%Y-%m') COLLATE utf8mb4_general_ci = ? COLLATE utf8mb4_general_ci";

$stmt = $conn->prepare($query);
$stmt->bind_param("is", $employee_id, $current_month);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

class PayslipPDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'EMPLOYEE PAYSLIP', 0, 1, 'C');
        $this->Cell(0, 10, date('F Y'), 0, 1, 'C');
        $this->Ln(10);
    }
}

$pdf = new PayslipPDF();
$pdf->AddPage();

// Header
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'ABA Racing: Motorcycle Parts and Repair Shop', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 5, 'Payslip - ' . date('F Y'), 0, 1, 'C');
$pdf->Ln(10);

// Employee Details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Employee Information', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);

// Loop through session data (excluding 'loggedin')
foreach ($_SESSION as $key => $value) {
    if ($key !== 'loggedin') {
        // Convert key to a readable format (e.g., 'firstName' -> 'First Name')
        $formattedKey = ucwords(str_replace('_', ' ', $key));
        
        // Display session values
        $pdf->Cell(50, 7, $formattedKey . ":", 0);
        $pdf->Cell(0, 7, $value, 0, 1);
    }
}

$pdf->Ln(10);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 7, 'This document is system-generated and does not require a signature.', 0, 1, 'C');

$pdf->Output('D', 'Payslip-' . date('F-Y') . '.pdf');

?>

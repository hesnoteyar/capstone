<?php
session_start();
require('../fpdf/fpdf.php');
include '../authentication/db.php';

$employee_id = $_SESSION['id'];
$current_month = date('Y-m');

// Fetch employee and payroll data
$query = "SELECT e.*, p.* 
          FROM employee e 
          JOIN payroll p ON e.employee_id = p.employee_id 
          WHERE e.employee_id = ? AND DATE_FORMAT(p.payroll_date, '%Y-%m') = ?";
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

// Employee Details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Employee Information', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(50, 7, 'Name:', 0);
$pdf->Cell(0, 7, $data['firstName'] . ' ' . $data['middleName'] . ' ' . $data['lastName'], 0, 1);
$pdf->Cell(50, 7, 'Position:', 0);
$pdf->Cell(0, 7, $data['role'], 0, 1);
$pdf->Cell(50, 7, 'Employee ID:', 0);
$pdf->Cell(0, 7, $data['employee_id'], 0, 1);

$pdf->Ln(10);

// Earnings
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Earnings', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(100, 7, 'Basic Salary:', 0);
$pdf->Cell(0, 7, 'PHP ' . number_format($data['salary'], 2), 0, 1);
$pdf->Cell(100, 7, 'Overtime Pay:', 0);
$pdf->Cell(0, 7, 'PHP ' . number_format($data['overtime_pay'], 2), 0, 1);

$pdf->Ln(5);

// Deductions
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Deductions', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(100, 7, 'SSS:', 0);
$pdf->Cell(0, 7, 'PHP ' . number_format($data['sss_deduction'], 2), 0, 1);
$pdf->Cell(100, 7, 'PhilHealth:', 0);
$pdf->Cell(0, 7, 'PHP ' . number_format($data['philhealth_deduction'], 2), 0, 1);
$pdf->Cell(100, 7, 'Pag-IBIG:', 0);
$pdf->Cell(0, 7, 'PHP ' . number_format($data['pagibig_deduction'], 2), 0, 1);

$pdf->Ln(10);

// Summary
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(100, 10, 'NET SALARY:', 0);
$pdf->Cell(0, 10, 'PHP ' . number_format($data['net_salary'], 2), 0, 1);

$pdf->Output('D', 'Payslip-' . date('F-Y') . '.pdf');
?>

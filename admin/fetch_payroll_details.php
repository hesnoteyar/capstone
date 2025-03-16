<?php
include '../authentication/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$payroll_id = $data['payroll_id'];

$sql = "SELECT p.*, 
        e.firstName as emp_firstName, e.middleName as emp_middleName, e.lastName as emp_lastName,
        e.profile_picture,
        p.sss_deduction, p.philhealth_deduction, p.pagibig_deduction
        FROM payroll p 
        LEFT JOIN employee e ON p.employee_id = e.employee_id
        WHERE p.payroll_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $payroll_id);
$stmt->execute();
$result = $stmt->get_result();
$payroll = $result->fetch_assoc();

// Convert BLOB to base64 if exists
if ($payroll && $payroll['profile_picture']) {
    $payroll['profile_picture'] = base64_encode($payroll['profile_picture']);
}

$response = [
    'success' => true,
    'data' => $payroll ?? null
];

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>

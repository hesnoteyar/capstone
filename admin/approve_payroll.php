<?php
include '../authentication/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$payroll_id = $data['payroll_id'];

$sql = "UPDATE payroll SET status = 'Approved' WHERE payroll_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $payroll_id);

$response = [];
if ($stmt->execute()) {
  $response['success'] = true;
} else {
  $response['success'] = false;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>

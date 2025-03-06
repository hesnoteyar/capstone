<?php
include '../authentication/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$payroll_id = $data['payroll_id'];
$admin_id = $data['admin_id'];

$sql = "UPDATE payroll SET status = 'Approved', admin_id = ? WHERE payroll_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $admin_id, $payroll_id);

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

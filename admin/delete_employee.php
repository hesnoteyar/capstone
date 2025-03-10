<?php
// filepath: /d:/XAMPP/htdocs/capstone/admin/delete_employee.php
include '../authentication/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$employee_id = $data['employee_id'];

$sql = "DELETE FROM employee WHERE employee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$success = $stmt->execute();

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete employee']);
}

$stmt->close();
$conn->close();
?>
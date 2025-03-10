<?php
// filepath: /d:/XAMPP/htdocs/capstone/admin/update_employee.php
include '../authentication/db.php';

$employee_id = $_POST['employee_id'];
$firstName = $_POST['firstName'];
$middleName = $_POST['middleName'];
$lastName = $_POST['lastName'];
$role = $_POST['role'];
$email = $_POST['email'];

$profile_picture = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
    $profile_picture = file_get_contents($_FILES['profile_picture']['tmp_name']);
}

$sql = "UPDATE employee SET firstName = ?, middleName = ?, lastName = ?, role = ?, email = ?";
if ($profile_picture) {
    $sql .= ", profile_picture = ?";
}
$sql .= " WHERE employee_id = ?";

$stmt = $conn->prepare($sql);
if ($profile_picture) {
    $stmt->bind_param("ssssssi", $firstName, $middleName, $lastName, $role, $email, $profile_picture, $employee_id);
} else {
    $stmt->bind_param("sssssi", $firstName, $middleName, $lastName, $role, $email, $employee_id);
}
$success = $stmt->execute();

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update employee']);
}

$stmt->close();
$conn->close();
?>
<?php
session_start();
include '..\authentication\db.php'; // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form inputs
    $first_name = trim($_POST['firstName']);
    $middle_name = trim($_POST['middleName']);
    $last_name = trim($_POST['lastName']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postalCode']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $repeat_password = $_POST['repeat_password'];
    $role = trim($_POST['role']);  // Capture the role

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($address) || empty($city) || empty($postal_code) || empty($email) || empty($password) || empty($repeat_password) || empty($role)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    if ($password !== $repeat_password) {
        $_SESSION['error_message'] = "Passwords do not match.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Check if the email already exists
    $stmt = $conn->prepare("SELECT employee_id FROM employee WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error_message'] = "Email already registered. Please use another email.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Get the current date for date_hired
        $date_hired = date('Y-m-d'); // Format: YYYY-MM-DD

        // Prepare an insert statement, including the role and date_hired fields
        $stmt = $conn->prepare("INSERT INTO employee (firstName, middleName, lastName, address, city, postalCode, email, password, role, date_hired) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $first_name, $middle_name, $last_name, $address, $city, $postal_code, $email, $hashed_password, $role, $date_hired);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Employee added successfully!";
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
            header("Location: " . $_SERVER['HTTP_REFERER']);
        }
    }

    $stmt->close();
}
$conn->close();
?>
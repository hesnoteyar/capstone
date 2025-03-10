<?php
session_start();
include '../authentication/db.php'; // Include your database connection file
require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form inputs
    $first_name = trim($_POST['firstName']);
    $last_name = trim($_POST['lastName']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postalCode']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $repeat_password = $_POST['repeat_password'];

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($address) || empty($city) || empty($postal_code) || empty($email) || empty($password) || empty($repeat_password)) {
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
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error_message'] = "Email already registered. Please use another email.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Store user data with placeholder OTP (e.g., NULL)
        $stmt = $conn->prepare("INSERT INTO users (firstName, lastName, address, city, postalCode, email, password, otp, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, NULL, 0)");
        $stmt->bind_param("sssssss", $first_name, $last_name, $address, $city, $postal_code, $email, $hashed_password);

        if ($stmt->execute()) {
            $_SESSION['email'] = $email; // Store email in session
            $_SESSION['success_message'] = "Registration successful. Please verify your account.";
            header("Location: ../index.php");
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
            header("Location: " . $_SERVER['HTTP_REFERER']);
        }
    }

    $stmt->close();
}
$conn->close();
?>

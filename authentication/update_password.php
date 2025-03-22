<?php
require_once '../includes/db_connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['otp_verified']) && isset($_SESSION['reset_email'])) {
    $email = $_SESSION['reset_email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match.";
        header("Location: /page/reset_password.php");
        exit();
    }

    // Hash the new password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Update password in database
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed_password, $email);
    
    if ($stmt->execute()) {
        // Mark OTP as used
        $stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        // Clear session variables
        unset($_SESSION['reset_email']);
        unset($_SESSION['otp_verified']);
        
        $_SESSION['success_message'] = "Password reset successful.";
        header("Location: /index.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error updating password.";
        header("Location: /page/reset_password.php");
        exit();
    }
}

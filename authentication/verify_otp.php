<?php
require_once '../authentication/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['reset_email'])) {
    $email = $_SESSION['reset_email'];
    $otp = $_POST['otp'];
    
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE email = ? AND otp = ? AND expiry > NOW() AND used = 0");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['otp_verified'] = true;
        header("Location: ../page/reset_password.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Invalid or expired OTP.";
        header("Location: ../page/verify_otp.php");
        exit();
    }
}

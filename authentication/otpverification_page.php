<?php
session_start();
include '..\authentication\db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $otp = trim($_POST['otp']);
    $email = $_SESSION['email'];

    if (empty($otp)) {
        $_SESSION['error_message'] = "OTP is required.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Validate OTP
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND otp = ? AND is_active = 0");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Activate the user
        $stmt = $conn->prepare("UPDATE users SET is_active = 1, otp = NULL WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $_SESSION['success_message'] = "Account successfully activated!";
        header("Location: ..\page\profile.php");
    } else {
        $_SESSION['error_message'] = "Invalid OTP.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
    }

    $stmt->close();
}
$conn->close();
?>

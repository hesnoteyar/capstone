<?php
session_start();
include '..\authentication\db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = trim($_POST['otp']);
    $user_id = $_SESSION['user_id']; // Assuming user ID is stored in the session after login

    if (empty($otp)) {
        $_SESSION['error_message'] = "OTP is required.";
        header("Location: otp_input.php");
        exit;
    }

    // Validate the OTP
    $stmt = $conn->prepare("SELECT otp FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($stored_otp);
    $stmt->fetch();
    $stmt->close();

    if ($stored_otp == $otp) {
        // Mark user as verified
        $stmt = $conn->prepare("UPDATE users SET is_active = 1, otp = NULL WHERE id = ?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "OTP verified successfully!";
            header("Location: ..\page\shop.php");
        } else {
            $_SESSION['error_message'] = "An error occurred. Please try again.";
            header("Location: otp_input.php");
        }

        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Invalid OTP.";
        header("Location: otp_input.php");
    }
}
$conn->close();

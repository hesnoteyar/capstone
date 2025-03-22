<?php
require '../vendor/PHPMailer/src/PHPMailer.php';
require '../vendor/PHPMailer/src/Exception.php';
require '../vendor/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../vendor/autoload.php';
require_once '../authentication/db.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    
    // Check if email exists in database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate OTP
        $otp = sprintf("%06d", mt_rand(0, 999999));
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        // Store OTP in database
        $stmt = $conn->prepare("INSERT INTO password_resets (email, otp, expiry) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $otp, $expiry);
        $stmt->execute();

        // Send email with OTP
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'racingaba@gmail.com'; // Replace with your email
            $mail->Password = 'rbwc tfbr qvvs tslh'; // Replace with your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('racingaba@gmail.com', 'ABA RACING E COMMERCE');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body = "Your OTP for password reset is: <b>{$otp}</b>. This code will expire in 15 minutes.";

            $mail->send();
            $_SESSION['reset_email'] = $email;
            header("Location: ../page/verify_otp.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            header("Location: ../index.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Email address not found.";
        header("Location: ../index.php");
        exit();
    }
}

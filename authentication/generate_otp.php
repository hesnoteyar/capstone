<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db.php'; // Include your database connection file
require '../vendor/autoload.php';

require '../vendor/PHPMailer/src/PHPMailer.php';
require '../vendor/PHPMailer/src/Exception.php';
require '../vendor/PHPMailer/src/SMTP.php';


if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // Generate OTP
    $otp = rand(100000, 999999);

    // Update OTP in the database
    $stmt = $conn->prepare("UPDATE users SET otp = ? WHERE email = ?");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("is", $otp, $email);

    if ($stmt->execute()) {
        try {
            // Send OTP email
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'racingaba@gmail.com';
            $mail->Password = 'rbwc tfbr qvvs tslh'; // Replace with your email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('racingaba@gmail.com', 'ABA RACING E COMMERCE');
            $mail->addAddress($email); // Recipient's email
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP for Registration';
            $mail->Body = "<h1>Hello!</h1>
                           <p>Your OTP for registration is: <h1>$otp</h1></p>
                           <p>Enter this OTP on the verification page to complete your registration.</p>
                           <p>This message is intended for the owner of the email address and contains confidential information</p>
                           <p><strong>--THIS IS AN ELECTRONICALLY GENERATED MESSAGE, PLEASE DO NOT REPLY--</strong></p>";

            $mail->send();

            $_SESSION['success_message'] = "OTP sent to your email.";
            header("Location: otp_input.php");
            exit;
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error sending OTP: " . $mail->ErrorInfo;
            header("Location: ../index.php");
            exit;
        }
    } else {
        $_SESSION['error_message'] = "Error updating OTP in the database.";
        header("Location: ../index.php");
        exit;
    }

    $stmt->close();
} else {
    $_SESSION['error_message'] = "Session expired. Please log in again.";
    header("Location: ../index.php");
    exit;
}
$conn->close();
?>
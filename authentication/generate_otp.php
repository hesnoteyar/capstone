<?php
session_start();
include '../authentication/db.php'; // Include your database connection file
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // Generate OTP
    $otp = rand(100000, 999999);

    // Update OTP in the database
    $stmt = $conn->prepare("UPDATE users SET otp = ? WHERE email = ?");
    $stmt->bind_param("is", $otp, $email);

    if ($stmt->execute()) {
        try {
            // Send OTP email
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'racingaba@gmail.com';
            $mail->Password = 'bvpp eodt xqmq hqcu'; // Replace with your email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('racingaba@gmail.com', 'ABA RACING E COMMERCE');
            $mail->addAddress($email); // Recipient's email
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP for Registration';
            $mail->Body = "<h1>Hello!</h1>
                           <p>Your OTP for registration is: <strong>$otp</strong></p>
                           <p>Enter this OTP on the verification page to complete your registration.</p>";

            $mail->send();

            $_SESSION['success_message'] = "OTP sent to your email.";
            header("Location: ..\authentication\otp_input.php");
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error sending OTP: " . $mail->ErrorInfo;
            header("Location: ../index.php");
        }
    } else {
        $_SESSION['error_message'] = "Error updating OTP in the database.";
        header("Location: ../index.php");
    }

    $stmt->close();
} else {
    $_SESSION['error_message'] = "Session expired. Please log in again.";
    header("Location: ../index.php");
}
$conn->close();
?>

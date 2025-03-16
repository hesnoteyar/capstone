<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '..\authentication\db.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = "Email and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, password, firstName, lastName FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $hashed_password, $first_name, $last_name);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                // Log in the user
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $id;
                $_SESSION['firstName'] = $first_name;
                $_SESSION['lastName'] = $last_name;

                session_write_close(); // Ensure session data is written
                header("Location: ..\page\shop.php");
                exit;
            } else {
                $error_message = "Invalid password.";
            }
        } else {
            $error_message = "No account found with that email.";
        }

        $stmt->close();
    }
}

$conn->close();

if (!empty($error_message)) {
    $_SESSION['error_message'] = $error_message;
    header("Location: ..\index.php");
    exit;
}
?>

<?php
session_start();
include 'db.php'; 

// Initialize the error message
$_SESSION['error_message'] = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form inputs
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Email and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT employee_id, password, firstName, lastName, role FROM employee WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $hashed_password, $first_name, $last_name, $role);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $id;
                $_SESSION['firstName'] = $first_name;
                $_SESSION['lastName'] = $last_name;
                $_SESSION['role'] = $role;

                header("Location: ../employee/employee_dashboard.php");
                exit;
            } else {
                $_SESSION['error_message'] = "Invalid password.";
            }
        } else {
            $_SESSION['error_message'] = "No account found with that email.";
        }

        $stmt->close();
    }
}

$conn->close();
header("Location: ../employee/employee_login.php");
exit;
?>

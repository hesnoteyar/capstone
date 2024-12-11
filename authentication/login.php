<?php
session_start();
include '..\authentication\db.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = "Email and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, email, password, firstName, lastName FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $user_email, $hashed_password, $first_name, $last_name);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                // Store user data in session
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $id;
                $_SESSION['email'] = $user_email; // Add email to session
                $_SESSION['firstName'] = $first_name;
                $_SESSION['lastName'] = $last_name;

                // Insert into audit_logs for successful login
                $action = 'LOGGED IN';
                $item = 'N/A'; // No specific item associated with login
                $auditQuery = "INSERT INTO audit_logs (user_id, action, item) VALUES (?, ?, ?)";
                $auditStmt = $conn->prepare($auditQuery);
                $auditStmt->bind_param("iss", $id, $action, $item);
                
                if (!$auditStmt->execute()) {
                    // Optionally handle logging failure
                    error_log("Failed to log audit: " . $auditStmt->error);
                }

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

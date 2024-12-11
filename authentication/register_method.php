<?php
session_start();
include '..\authentication\db.php'; // Include your database connection file

// Firebase API Key (Replace with your actual API Key)
$firebase_api_key = 'AIzaSyB39USZ0uO0LFqc3cYuGwidw1WfQsjFYxk';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form inputs
    $first_name = trim($_POST['firstName']);
    $last_name = trim($_POST['lastName']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postalCode']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $repeat_password = $_POST['repeat_password'];

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($address) || empty($city) || empty($postal_code) || empty($email) || empty($password) || empty($repeat_password)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    if ($password !== $repeat_password) {
        $_SESSION['error_message'] = "Passwords do not match.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Check if the email already exists in MySQL database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error_message'] = "Email already registered. Please use another email.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into the MySQL database
        $stmt = $conn->prepare("INSERT INTO users (firstName, lastName, address, city, postalCode, email, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $first_name, $last_name, $address, $city, $postal_code, $email, $hashed_password);
        $stmt->execute();

        // Send request to Firebase Authentication API
        $data = [
            'email' => $email,
            'password' => $password,
            'returnSecureToken' => true
        ];

        $url = 'https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=' . $firebase_api_key;

        $options = [
            'http' => [
                'header' => "Content-Type: application/json",
                'method' => 'POST',
                'content' => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response === FALSE) {
            $_SESSION['error_message'] = "Error with Firebase registration.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }

        // Parse the Firebase response
        $response_data = json_decode($response, true);

        if (isset($response_data['error'])) {
            $_SESSION['error_message'] = "Firebase error: " . $response_data['error']['message'];
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }

        // Send verification email
        $id_token = $response_data['idToken'];
        $verify_url = 'https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode?key=' . $firebase_api_key;
        $verify_data = [
            'requestType' => 'VERIFY_EMAIL',
            'idToken' => $id_token
        ];

        $verify_options = [
            'http' => [
                'header' => "Content-Type: application/json",
                'method' => 'POST',
                'content' => json_encode($verify_data)
            ]
        ];

        $verify_context = stream_context_create($verify_options);
        $verify_response = file_get_contents($verify_url, false, $verify_context);

        if ($verify_response === FALSE) {
            $_SESSION['error_message'] = "Error sending verification email.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }

        // Redirect user to login page after successful registration
        header("Location: ..\index.php");
    }

    $stmt->close();
}
$conn->close();
?>

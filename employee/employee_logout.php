<?php
session_start(); // Start the session

// Unset all of the session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to the login page
header("location: ../employee/employee_login.php");
exit;
?>
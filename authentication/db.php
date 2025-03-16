<?php
$host = 'localhost'; // Use 'localhost' for Hostinger, unless given a different hostname
$db = 'u697061521_abaracing'; // Your database name
$user = 'u697061521_abaracing'; // Your database username
$pass = 'Aba_R@c1ng'; 

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>

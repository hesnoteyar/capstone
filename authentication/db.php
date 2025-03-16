<?php
$host = 'srv605.hstgr.io'; 
$db = 'u697061521_abaracing	'; 
$user = 'u697061521_abaracing';
$pass = 'Aba_R@c1ng'; 

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<?php
session_start();
include '../authentication/db.php';
include '../page/topnavbar.php';

// Assuming user_id is stored in the session

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@1.1.4/dist/full.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <title>Purchase History</title>
    <style>

    </style>

</head>
<body class="bg-base-100 text-base-content">

    <?php include '../page/footer.php'; ?>
</body>
</html>
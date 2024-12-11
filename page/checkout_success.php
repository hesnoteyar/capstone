<?php
session_start();
include '..\authentication\db.php';

// Assuming user_id is stored in the session
$user_id = $_SESSION['id'];

// Check if there is a payment ID passed through the URL (this could be done via GET parameters)
if (isset($_GET['payment_id'])) {
    $payment_id = $_GET['payment_id'];

    // Here you might want to verify the payment status with PayMongo using their API
    // For now, we will assume the payment is successful and proceed to clear the cart

    // Delete items from the cart
    $delete_sql = "DELETE FROM cart WHERE user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $user_id);
    $delete_stmt->execute();

    // Optionally, you could fetch order details here to display to the user
} else {
    // If no payment ID is found, redirect to an error page or show a message
    header("Location: error.php?message=No payment information received.");
    exit;
}

// HTML Content for Success Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.14/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Payment Successful</title>
</head>
<body class="bg-base-200 font-sans">

<div class="flex flex-col items-center justify-center min-h-screen px-4">
    <div class="w-full max-w-md bg-white shadow-xl rounded-lg p-6 space-y-4 text-center">
        <h1 class="text-3xl font-extrabold text-green-600">Payment Successful!</h1>
        <p class="text-lg text-gray-700">Thank you for your purchase!</p>
        <p class="text-gray-500">Your order has been processed successfully.</p>
        
        <!-- Optionally, you can display order details here -->
        <!-- Example: -->
        <!-- <p class="text-gray-600">Order ID: <?= htmlspecialchars($order_id) ?></p> -->

        <div class="mt-4">
            <a href="../page/shop.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    </div>
</div>

</body>
</html>
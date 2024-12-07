<?php
    include '..\page\topnavbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.14/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-base-200 font-sans">

    <!-- Main Content Area -->
    <div class="flex items-center justify-center h-screen">
        <div class="text-center">
            <!-- Image -->
            <img src="..\media\cart 1.png" alt="Empty Cart" class="w-75 h-40 mx-auto mb-6">
            
            <!-- Heading -->
            <h1 class="text-2xl font-bold text-gray-800">Your Cart is Empty</h1>
            
            <!-- Subheading -->
            <p class="text-gray-500 mt-2">Looks like you haven’t added anything in your cart yet</p>
            
            <!-- Action Button (Optional) -->
            <a href="..\page\shop.php" class="btn btn-error  mt-6">Go to Shop</a>
        </div>
    </div>

</body>

<?php
    include '..\page\footer.php';
?>
</html>

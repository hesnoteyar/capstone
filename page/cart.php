<?php
session_start();
ob_start();
include '../page/topnavbar.php';
include '../authentication/db.php';

// Assuming user_id is stored in the session
$user_id = $_SESSION['id'] ?? null;

// Redirect if user is not logged in
if (!$user_id) {
    header("Location: ../page/login.php");
    exit;
}

// Fetch cart items
function fetchCartItems($conn, $user_id) {
    $sql = "SELECT * FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

$cart_items = fetchCartItems($conn, $user_id);
$item_count = $cart_items->num_rows;

// Handle item deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item_id'], $_POST['csrf_token'])) {
    $delete_item_id = (int)$_POST['delete_item_id'];
    $csrf_token = $_POST['csrf_token'];

    // Validate CSRF token
    if (hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $delete_sql = "DELETE FROM cart WHERE cart_id = ? AND user_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $delete_item_id, $user_id);
        $delete_stmt->execute();
        header("Location: cart.php");
        exit;
    } else {
        die("Invalid CSRF token.");
    }
}

// Generate a new CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@1.1.4/dist/full.css" rel="stylesheet">    
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Your Cart</title>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-base-200 font-sans flex flex-col min-h-screen">
    <div class="container mx-auto py-10 flex-grow">
        <?php if ($item_count > 0): ?>

            <!-- Cart Items -->
            <div class="flex flex-col gap-6">
                <?php
                $total_price = 0;
                while ($row = $cart_items->fetch_assoc()):
                    $product_total = $row['quantity'] * $row['price'];
                    $total_price += $product_total;
                ?>
                <div class="card lg:card-side bg-white shadow-xl p-4">
                    <figure>
                        <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>" class="w-32 h-32 rounded-lg object-cover">
                    </figure>
                    <div class="card-body">
                        <h2 class="card-title"><?= htmlspecialchars($row['product_name']) ?></h2>
                        <p class="text-gray-500">₱<?= number_format($row['price'], 2) ?> x <?= $row['quantity'] ?></p>
                        <p class="font-semibold text-gray-700">Total: ₱<?= number_format($product_total, 2) ?></p>
                        <div class="card-actions justify-end">
                            <button class="btn btn-error btn-sm" onclick="openModal(<?= $row['cart_id'] ?>)">Remove</button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Total and Checkout -->
            <div class="mt-10 p-6 bg-white shadow-lg rounded-lg flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">
                    Total Amount: <span class="text-red-600">₱<?= number_format($total_price, 2) ?></span>
                </h2>
                <button onclick="proceedToCheckout()" class="btn btn-error">Proceed to Checkout</button>
            </div>
        <?php else: ?>
            <!-- Empty Cart -->
            <div class="text-center">
                <img src="../media/cart 1.png" alt="Empty Cart" class="w-40 h-40 mx-auto mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Your Cart is Empty</h1>
                <p class="text-gray-500 mt-2">Looks like you haven’t added anything to your cart yet.</p>
                <a href="../page/shop.php" class="btn btn-primary mt-6">Go to Shop</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-box">
            <h2 class="text-lg font-bold">Are you sure you want to delete this item?</h2>
            <div class="modal-action">
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="delete_item_id" id="deleteItemId">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <button type="submit" class="btn btn-error">Yes, Delete</button>
                </form>
                <button class="btn" onclick="closeModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        // Open Modal
        function openModal(itemId) {
            document.getElementById('deleteItemId').value = itemId;
            document.getElementById('deleteModal').classList.add('modal-open');
        }

        // Close Modal
        function closeModal() {
            document.getElementById('deleteModal').classList.remove('modal-open');
        }

        async function proceedToCheckout() {
            try {
                const response = await fetch('create_checkout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                });

                const data = await response.json();

                if (data.checkout_url) {
                    window.open(data.checkout_url, '_blank');
                } else {
                    alert('Error: ' + (data.error || 'Unable to generate checkout link.'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An unexpected error occurred.');
            }
        }
    </script>

</body>

<?php include '../page/footer.php'; ?>
<?php ob_end_flush(); ?>
</html>

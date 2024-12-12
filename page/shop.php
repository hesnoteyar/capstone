<?php
ob_start();
session_start();
include '..\page\topnavbar.php';
include '..\authentication\db.php';

$category = isset($_GET['category']) ? $_GET['category'] : 'All';
$priceRange = isset($_GET['price_range']) ? $_GET['price_range'] : 10000;

$sql = "SELECT p.name AS product_name, p.description, p.image_url, p.price, c.name AS category_name 
        FROM Product p 
        JOIN Category c ON p.category_id = c.category_id 
        WHERE 1=1";

if ($category !== 'All') {
    $sql .= " AND LOWER(c.name) = LOWER('" . $conn->real_escape_string($category) . "')";
}

$sql .= " AND p.price <= " . (int)$priceRange;

$result = $conn->query($sql);
if ($result === false) {
    echo "Error: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
    
    <style>
        .modal-image { width: 400px; height: 300px; object-fit: cover; }
        .card-image { width: 100%; height: 200px; object-fit: cover; }
        .modal-title { font-size: 2rem; font-weight: bold; }
        .modal-description { margin-top: 1rem; font-size: 1rem; }
    </style>
    <script>
        function openModal(productName, description, categoryName, imageUrl, price) {
            document.getElementById('modal-product-name').textContent = productName;
            document.getElementById('modal-category').textContent = categoryName;
            document.getElementById('modal-description').textContent = description;
            document.getElementById('modal-image').src = imageUrl;
            document.getElementById('modal-price').textContent = `₱${price.toFixed(2)}`;
            document.getElementById('modal-price-hidden').value = price;
            document.getElementById('product-modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('product-modal').classList.add('hidden');
        }

function checkout() {
    fetch('check_user_status.php', {
        method: 'POST',
    })
    .then((response) => response.json())
    .then((data) => {
        if (data.status === 'success') {
            const quantity = document.getElementById('quantity').value;
            const productName = document.getElementById('modal-product-name').textContent;
            const price = parseFloat(document.getElementById('modal-price-hidden').value);
            const totalPrice = Math.round(price * quantity * 100); // PayMongo expects amount in centavos

            // Log the checkout action to the audit_logs table
            fetch('log_checkout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: data.id,  // Assuming user_id is in the response
                    action: 'CHECKOUT',
                    item: `${quantity} x ${productName}`,
                    total_price: totalPrice
                }),
            })
            .then((logResponse) => {
                if (!logResponse.ok) {
                    console.error('Error logging checkout action:', logResponse.status, logResponse.statusText);
                }
            })
            .catch((error) => {
                console.error('Error logging checkout action:', error.message);
            });

            // Proceed with the checkout
            fetch('create_paymongo_link.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    amount: totalPrice,
                    description: `${quantity} x ${productName}`,
                    currency: 'PHP',
                }),
            })
            .then((response) => {
                if (!response.ok) {
                    console.error('HTTP Error:', response.status, response.statusText);
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then((data) => {
                if (data.checkout_url) {
                    window.open(data.checkout_url, '_blank');
                } else {
                    console.error('Missing checkout URL in response:', data);
                    showBanner('error', 'Failed to retrieve checkout URL');
                }
            })
            .catch((error) => {
                console.error('Checkout Error:', error.message);
                showBanner('error', 'An error occurred during checkout. Please try again.');
            });
        } else {
            showBanner('error', data.message); // Show error message in banner if user is not active
        }
    })
    .catch((error) => {
        console.error('Validation Error:', error);
        showBanner('error', 'An error occurred while validating your account status.');
    });
}



        function showBanner(type, message) {
            const banner = document.createElement('div');
            banner.classList.add('alert', 'w-full', type === 'error' ? 'alert-error' : 'alert-success', 'fixed', 'top-5', 'left-0', 'z-50', 'p-2', 'text-m');

            // Set a specific width for the banner, e.g., 80% of the screen width or a fixed pixel width
            banner.style.width = '40%';  // Adjust this value to change the banner width
            banner.style.margin = '0 auto';  // Centers the banner

            const bannerContent = document.createElement('div');
            bannerContent.classList.add('flex', 'items-center');
            
            const icon = document.createElement('span');
            icon.classList.add('material-icons', 'mr-2');
            icon.textContent = type === '' ? 'error' : '';

            const text = document.createElement('span');
            text.textContent = message;
            
            bannerContent.appendChild(icon);
            bannerContent.appendChild(text);
            banner.appendChild(bannerContent);

            // Append to body
            document.body.appendChild(banner);

            // Optionally remove the banner after a few seconds
            setTimeout(() => {
                banner.remove();
            }, 5000); // Remove after 5 seconds
        }



        function addToCart() {
            const productName = document.getElementById('modal-product-name').textContent; // Get product name from modal
            const quantity = document.getElementById('quantity').value;

            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    product_name: productName,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message); // Show success or error message
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding to cart.');
            });
        }



    </script>
</head>
<body class="bg-base-100 text-base-content">
    
<div class="flex min-h-screen">
    <form action="" method="GET" class="w-1/4 bg-base-200 p-6 shadow-lg h-screen sticky top-0">
        <h3 class="font-bold text-2xl mb-6">Filters</h3>
        <div class="mb-6">
            <label class="block text-lg mb-2">Category</label>
            <select name="category" class="p-2 w-full border rounded-md">
                <option value="All" <?= $category === 'All' ? 'selected' : ''; ?>>All</option>
                <option value="Motorcycle" <?= $category === 'Motorcycle' ? 'selected' : ''; ?>>Motorcycle</option>
                <option value="Car" <?= $category === 'Car' ? 'selected' : ''; ?>>Car</option>
                <option value="Accessories" <?= $category === 'Accessories' ? 'selected' : ''; ?>>Accessories</option>
            </select>
        </div>
        <div class="mb-6">
            <label class="block text-lg mb-2">Price Range</label>
            <input type="range" name="price_range" min="500" max="10000" value="<?= $priceRange; ?>" class="accent-red-600 w-full">
        </div>
        <button type="submit" class="w-full bg-red-700 text-white py-3 rounded-md hover:bg-red-800 transition duration-300"> Apply Filters </button>
    </form>

    <div class="w-3/4 flex flex-wrap gap-4 p-6">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                    $productName = htmlspecialchars($row['product_name']);
                    $description = htmlspecialchars($row['description']);
                    $categoryName = htmlspecialchars($row['category_name']);
                    $imageUrl = htmlspecialchars($row['image_url']);
                    $price = (float)$row['price'];
                    ?>
                
                <div class="card bg-base-100 w-80 shadow-lg">
                    <figure>
                        <img src="<?= $imageUrl ?>" alt="<?= $productName ?>" class="card-image">
                    </figure>
                    <div class="card-body">
                        <h2 class="card-title"><?= $productName ?></h2>
                        <div class="badge badge-error text-white"><?= $categoryName ?></div>
                        <p>Price: ₱<?= number_format($price, 2) ?></p>
                        <button class="bg-red-700 text-white px-4 py-2 rounded-md hover:bg-red-800 transition duration-300"
                                onclick="openModal('<?= addslashes($productName) ?>', '<?= addslashes($description) ?>', '<?= addslashes($categoryName) ?>', '<?= addslashes($imageUrl) ?>', <?= $price ?>)">
                            View Details
                        </button>
                    </div>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <p>No products found.</p>
        <?php endif; ?>
        
        <?php $conn->close(); ?>
    </div>
</div>

<div id="product-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="card lg:card-side bg-base-100 shadow-xl max-w-4xl">
        <figure>
            <img id="modal-image" src="" alt="Product" class="modal-image">
        </figure>
        <div class="card-body">
            <h2 id="modal-product-name" class="modal-title"></h2>
            <div id="modal-category" class="badge badge-error text-white"></div>
            <p id="modal-description" class="modal-description"></p>
            <p>Price: <span id="modal-price"></span></p>
            <input type="hidden" id="modal-price-hidden">
            
            <div class="mt-4">
                <label for="quantity" class="block text-lg mb-2">Quantity</label>
                <input type="number" id="quantity" name="quantity" value="1" min="1" class="w-20 p-2 border rounded-md text-center">
            </div>

            <div class="card-actions justify-end">
                <button class="bg-red-700 text-white px-4 py-2 rounded-md hover:bg-red-800 transition duration-300"
                        onclick="closeModal()">Close
                </button>
                <button class="bg-red-700 text-white px-4 py-2 rounded-md hover:bg-red-800 transition duration-300"
                        onclick="checkout()">Checkout
                </button>
                <button class="bg-red-700 text-white px-4 py-2 rounded-md hover:bg-red-800 transition duration-300"
                        onclick="addToCart()">Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>



<?php include '..\page\footer.php'; ?>
<?php ob_end_flush(); ?>

</body>
</html>

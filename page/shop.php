<?php
ob_start();
session_start();
include '..\page\topnavbar.php';
include '..\authentication\db.php';

$category = isset($_GET['category']) ? $_GET['category'] : 'All';
$priceRange = isset($_GET['price_range']) ? $_GET['price_range'] : 10000;

$sql = "SELECT p.product_id, p.name AS product_name, p.description, p.image_url, p.price, c.name AS category_name 
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
        .modal-image { width: 100%; height: 400px; object-fit: cover; }
        .card-image { width: 100%; height: 200px; object-fit: cover; }
        .modal-title { font-size: 2rem; font-weight: bold; }
        .modal-description { margin-top: 1rem; font-size: 1rem; }
        .modal-content {
            max-height: 80vh;
            overflow-y: auto;
        }
        .no-scroll {
            overflow: hidden;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 1rem;
        }
        .modal-body {
            padding: 1rem 0;
        }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            border-top: 1px solid #e5e7eb;
            padding-top: 1rem;
        }
        .review-section {
            width: 100%;
        }
    </style>
    <script>
        function openModal(productName, description, categoryName, imageUrl, price, productId) {
            document.getElementById('modal-product-name').textContent = productName;
            document.getElementById('modal-category').textContent = categoryName;
            document.getElementById('modal-description').textContent = description;
            document.getElementById('modal-image').src = imageUrl;
            document.getElementById('modal-price').textContent = `₱${price.toFixed(2)}`;
            document.getElementById('modal-price-hidden').value = price;
            document.getElementById('product-modal').classList.remove('hidden');
            document.body.classList.add('no-scroll'); // Disable background scrolling

            // Fetch and display reviews
            fetchReviews(productId);
        }

        function closeModal() {
            document.getElementById('product-modal').classList.add('hidden');
            document.body.classList.remove('no-scroll'); // Enable background scrolling
        }

        function fetchReviews(productId) {
            fetch('fetch_reviews.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ product_id: productId })
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                return response.json(); // Read the response as JSON
            })
            .then(data => {
                console.log('Response data:', data);
                const reviewsContainer = document.getElementById('reviews-container');
                reviewsContainer.innerHTML = ''; // Clear existing reviews

                if (data.status === 'success') {
                    data.reviews.forEach(review => {
                        const reviewElement = document.createElement('div');
                        reviewElement.classList.add('p-4', 'border', 'rounded-md');

                        const ratingElement = document.createElement('div');
                        ratingElement.classList.add('flex', 'items-center', 'mb-2');

                        const ratingStars = document.createElement('div');
                        ratingStars.classList.add('rating', 'mr-2');
                        for (let i = 1; i <= 5; i++) {
                            const star = document.createElement('input');
                            star.type = 'radio';
                            star.classList.add('mask', 'mask-star-2', 'bg-warning');
                            star.disabled = true;
                            if (i <= review.rating) {
                                star.checked = true;
                            }
                            ratingStars.appendChild(star);
                        }

                        const username = document.createElement('span');
                        username.classList.add('text-gray-600');
                        username.textContent = review.username;

                        ratingElement.appendChild(ratingStars);
                        ratingElement.appendChild(username);

                        const reviewText = document.createElement('p');
                        reviewText.classList.add('text-gray-700');
                        reviewText.textContent = review.review_text;

                        reviewElement.appendChild(ratingElement);
                        reviewElement.appendChild(reviewText);

                        reviewsContainer.appendChild(reviewElement);
                    });
                } else {
                    const noReviews = document.createElement('p');
                    noReviews.textContent = 'No reviews found.';
                    reviewsContainer.appendChild(noReviews);
                }
            })
            .catch(error => {
                console.error('Error fetching reviews:', error);
            });
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
            icon.textContent = type === '' ? '' : '';

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
                showBanner(data.status === 'success' ? 'success' : 'error', data.message); // Show success or error message
            })
            .catch(error => {
                console.error('Error:', error);
                showBanner('error', 'An error occurred while adding to cart.');
            });
        }

        function submitReview() {
            const productName = document.getElementById('modal-product-name').textContent;
            const ratingInput = document.querySelector('input[name="rating"]:checked');
            const reviewText = document.getElementById('review-text').value;

            if (!ratingInput) {
                showBanner('error', 'Please select a rating.');
                return;
            }

            const rating = ratingInput.value;

            fetch('submit_review.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_name: productName,
                    rating: rating,
                    review_text: reviewText
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                return response.text(); // Read the response as text for debugging
            })
            .then(text => {
                console.log('Response text:', text);
                try {
                    const data = JSON.parse(text); // Parse the response as JSON
                    if (data.status === 'success') {
                        showBanner('success', 'Review submitted successfully!');
                        closeModal();
                    } else {
                        showBanner('error', 'Failed to submit review: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error parsing JSON:', error);
                    showBanner('error', 'An error occurred while submitting your review.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showBanner('error', 'An error occurred while submitting your review.');
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
                    $productId = (int)$row['product_id'];
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
                                onclick="openModal('<?= addslashes($productName) ?>', '<?= addslashes($description) ?>', '<?= addslashes($categoryName) ?>', '<?= addslashes($imageUrl) ?>', <?= $price ?>, <?= $productId ?>)">
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
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl p-6 modal-content">
        <div class="modal-header">
            <h2 id="modal-product-name" class="text-3xl font-bold"></h2>
            <button class="text-red-700 hover:text-red-00" onclick="closeModal()">
                <span class="material-icons">Close</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="flex flex-col lg:flex-row">
                <figure class="w-full lg:w-1/2">
                    <img id="modal-image" src="" alt="Product" class="modal-image rounded-lg object-cover">
                </figure>
                <div class="w-full lg:w-1/2 lg:pl-6">
                    <div id="modal-category" class="badge badge-error text-white mb-4"></div>
                    <p id="modal-description" class="text-gray-700 mb-4"></p>
                    <p class="text-lg font-semibold mb-4">Price: <span id="modal-price"></span></p>
                    <input type="hidden" id="modal-price-hidden">
                    
                    <div class="mb-6">
                        <label for="quantity" class="block text-lg mb-2 font-medium">Quantity</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" 
                               class="w-24 p-2 border rounded-md text-center">
                    </div>

                    <!-- Action Buttons -->
                    <div class="mb-6 flex space-x-3">
                        <button class="bg-red-700 text-white px-6 py-2 rounded-md hover:bg-red-800 transition duration-300"
                                onclick="checkout()">Checkout
                        </button>
                        <button class="bg-red-700 text-white px-6 py-2 rounded-md hover:bg-red-800 transition duration-300"
                                onclick="addToCart()">Add to Cart
                        </button>
                    </div>
                </div>
            </div>

            <!-- Review and Rating Section -->
            <div class="review-section">
                <h3 class="text-xl font-bold mb-4">Leave a Review</h3>
                
                <!-- Star Rating -->
                <div class="flex items-center mb-4">
                    <span class="text-gray-600 mr-3">Your Rating:</span>
                    <div class="rating">
                        <input type="radio" name="rating" class="mask mask-star-2 bg-warning" value="1">
                        <input type="radio" name="rating" class="mask mask-star-2 bg-warning" value="2">
                        <input type="radio" name="rating" class="mask mask-star-2 bg-warning" value="3">
                        <input type="radio" name="rating" class="mask mask-star-2 bg-warning" value="4">
                        <input type="radio" name="rating" class="mask mask-star-2 bg-warning" value="5">
                    </div>
                </div>

                <!-- Review Text Field -->
                <textarea id="review-text" class="textarea textarea-bordered w-full mb-4" 
                          rows="4" placeholder="Write your review here..."></textarea>
                
                <!-- Submit Button -->
                <button class="bg-red-700 text-white px-6 py-2 rounded-md hover:bg-red-800 transition duration-300"
                        onclick="submitReview()">Submit Review
                </button>
            </div>

            <div class="review-section">
                <h3 class="text-xl font-bold mb-4">Reviews</h3>
                <div id="reviews-container" class="space-y-4">
                    <!-- Reviews will be dynamically inserted here -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '..\page\footer.php'; ?>
<?php ob_end_flush(); ?>

</body>
</html>

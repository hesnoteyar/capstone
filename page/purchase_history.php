<?php
session_start();
include '../authentication/db.php';
include '../page/topnavbar.php';

// Assuming user_id is stored in the session
$user_id = $_SESSION['id'];

// Fetch purchase history for the user
$sql = "SELECT ph.product_name, ph.quantity, ph.price, ph.purchase_date, p.image, p.description 
        FROM purchase_history ph 
        JOIN product p ON ph.product_id = p.product_id 
        WHERE ph.user_id = ? 
        ORDER BY ph.purchase_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
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
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex: 1;
        }
        .modal-content::-webkit-scrollbar {
            display: none;
        }
        .modal-content {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        .card:hover {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }
    </style>
    <script>
        function openModal(productName, description, imageSrc, price, quantity, purchaseDate) {
            document.getElementById('modal-product-name').textContent = productName;
            document.getElementById('modal-description').textContent = description;
            document.getElementById('modal-image').src = imageSrc;
            document.getElementById('modal-price').textContent = `₱${price.toFixed(2)}`;
            document.getElementById('modal-quantity').textContent = quantity;
            document.getElementById('modal-purchase-date').textContent = purchaseDate;
            document.getElementById('product-modal').classList.remove('hidden');
            document.body.classList.add('no-scroll'); // Disable background scrolling

            // GSAP animations for modal elements
            gsap.from('.modal-header', { duration: 0.5, y: -50, opacity: 0, ease: 'power1.out' });
            gsap.from('.modal-body', { duration: 0.5, y: 50, opacity: 0, ease: 'power1.out', delay: 0.25 });
        }

        function closeModal() {
            document.getElementById('product-modal').classList.add('hidden');
            document.body.classList.remove('no-scroll'); // Enable background scrolling
        }

        document.addEventListener('DOMContentLoaded', function() {
            // GSAP animations for purchase history items
            gsap.from('.card', { duration: 0.5, y: 50, opacity: 0, ease: 'power1.out', stagger: 0.1 });
        });
    </script>
</head>
<body class="bg-base-100 text-base-content">
    <main class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Purchase History</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $productName = htmlspecialchars($row['product_name']);
                    $description = htmlspecialchars($row['description']);
                    $imageData = base64_encode($row['image']);
                    $imageSrc = 'data:image/jpeg;base64,' . $imageData;
                    $price = (float)$row['price'];
                    $quantity = (int)$row['quantity'];
                    $purchaseDate = htmlspecialchars($row['purchase_date']);
                    ?>
                    <div class="card bg-white shadow-xl">
                        <figure>
                            <img src="<?= $imageSrc ?>" alt="<?= $productName ?>" class="w-full h-48 object-cover">
                        </figure>
                        <div class="card-body">
                            <h2 class="card-title"><?= $productName ?></h2>
                            <p>Price: ₱<?= number_format($price, 2) ?></p>
                            <p>Quantity: <?= $quantity ?></p>
                            <p>Purchase Date: <?= $purchaseDate ?></p>
                            <button class="btn btn-error" onclick="openModal('<?= addslashes($productName) ?>', '<?= addslashes($description) ?>', '<?= $imageSrc ?>', <?= $price ?>, <?= $quantity ?>, '<?= $purchaseDate ?>')">View Details</button>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No purchase history found.</p>";
            }
            ?>
        </div>
    </main>

    <!-- Modal -->
    <div id="product-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl p-6 modal-content" style="max-height: 80vh; overflow-y: scroll;">
            <div class="modal-header flex justify-between items-center border-b pb-4">
                <h2 id="modal-product-name" class="text-3xl font-bold"></h2>
                <button class="text-red-700 hover:text-red-800" onclick="closeModal()">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <div class="modal-body mt-4">
                <figure class="w-full mb-4">
                    <img id="modal-image" src="" alt="Product" class="w-full h-64 object-cover rounded-lg">
                </figure>
                <p id="modal-description" class="text-gray-700 mb-4"></p>
                <p class="text-lg font-semibold">Price: <span id="modal-price"></span></p>
                <p class="text-lg font-semibold">Quantity: <span id="modal-quantity"></span></p>
                <p class="text-lg font-semibold">Purchase Date: <span id="modal-purchase-date"></span></p>
            </div>
        </div>
    </div>

    <?php include '../page/footer.php'; ?>
</body>
</html>
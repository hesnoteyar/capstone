<?php
// filepath: /d:/XAMPP/htdocs/capstone/admin/admin_inventory.php
session_start(); // Start the session to access session variables
include '..\admin\adminnavbar.php';
include '..\authentication\db.php';

// Fetch products from the database
$sql = "SELECT * FROM product";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.13/dist/full.min.css" rel="stylesheet" type="text/css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-base-100 text-base-content">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Inventory Management</h1>

        <!-- Add Product Button -->
        <button class="btn btn-primary mb-6" onclick="openAddProductModal()">Add Product</button>

        <!-- Product List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='card bg-white shadow-md rounded-lg p-4'>
                            <h2 class='text-xl font-bold mb-2'>" . htmlspecialchars($row['name']) . "</h2>
                            <p class='text-gray-700'>Description: " . htmlspecialchars($row['description']) . "</p>
                            <p class='text-gray-700'>Price: ₱" . number_format($row['price'], 2) . "</p>
                            <p class='text-gray-700'>Quantity: " . htmlspecialchars($row['stock_quantity']) . "</p>
                            <div class='card-actions mt-4'>
                                <button class='btn btn-secondary' onclick='openEditProductModal(" . $row['product_id'] . ")'>Edit</button>
                                <button class='btn btn-error' onclick='deleteProduct(" . $row['product_id'] . ")'>Delete</button>
                            </div>
                          </div>";
                }
            } else {
                echo "<p>No products found.</p>";
            }
            ?>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-box">
            <h2 class="text-xl font-bold mb-4">Add Product</h2>
            <form id="addProductForm">
                <div class="form-control mb-4">
                    <label class="label">Name</label>
                    <input type="text" name="name" class="input input-bordered" required>
                </div>
                <div class="form-control mb-4">
                    <label class="label">Description</label>
                    <textarea name="description" class="textarea textarea-bordered" required></textarea>
                </div>
                <div class="form-control mb-4">
                    <label class="label">Price</label>
                    <input type="number" name="price" class="input input-bordered" step="0.01" required>
                </div>
                <div class="form-control mb-4">
                    <label class="label">Quantity</label>
                    <input type="number" name="quantity" class="input input-bordered" required>
                </div>
                <div class="modal-action">
                    <button type="submit" class="btn btn-primary">Add</button>
                    <button type="button" class="btn" onclick="closeAddProductModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal">
        <div class="modal-box">
            <h2 class="text-xl font-bold mb-4">Edit Product</h2>
            <form id="editProductForm">
                <input type="hidden" name="product_id">
                <div class="form-control mb-4">
                    <label class="label">Name</label>
                    <input type="text" name="name" class="input input-bordered" required>
                </div>
                <div class="form-control mb-4">
                    <label class="label">Description</label>
                    <textarea name="description" class="textarea textarea-bordered" required></textarea>
                </div>
                <div class="form-control mb-4">
                    <label class="label">Price</label>
                    <input type="number" name="price" class="input input-bordered" step="0.01" required>
                </div>
                <div class="form-control mb-4">
                    <label class="label">Quantity</label>
                    <input type="number" name="quantity" class="input input-bordered" required>
                </div>
                <div class="modal-action">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn" onclick="closeEditProductModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddProductModal() {
            document.getElementById('addProductModal').classList.add('modal-open');
        }

        function closeAddProductModal() {
            document.getElementById('addProductModal').classList.remove('modal-open');
        }

        function openEditProductModal(productId) {
            fetch('get_product_details.php?id=' + productId)
                .then(response => response.json())
                .then(data => {
                    document.querySelector('#editProductForm [name="product_id"]').value = data.product_id;
                    document.querySelector('#editProductForm [name="name"]').value = data.name;
                    document.querySelector('#editProductForm [name="description"]').value = data.description;
                    document.querySelector('#editProductForm [name="price"]').value = data.price;
                    document.querySelector('#editProductForm [name="quantity"]').value = data.quantity;
                    document.getElementById('editProductModal').classList.add('modal-open');
                })
                .catch(error => console.error('Error fetching product details:', error));
        }

        function closeEditProductModal() {
            document.getElementById('editProductModal').classList.remove('modal-open');
        }

        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                fetch('delete_product.php?id=' + productId, { method: 'DELETE' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error deleting product: ' + data.error);
                        }
                    })
                    .catch(error => console.error('Error deleting product:', error));
            }
        }

        document.getElementById('addProductForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            fetch('add_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error adding product: ' + data.error);
                }
            })
            .catch(error => console.error('Error adding product:', error));
        });

        document.getElementById('editProductForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            fetch('edit_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error editing product: ' + data.error);
                }
            })
            .catch(error => console.error('Error editing product:', error));
        });
    </script>
</body>
<?php
    include '..\admin\admin_footer.php';
?>
</html>
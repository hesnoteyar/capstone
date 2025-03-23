<?php
session_start();
include '..\admin\adminnavbar.php';
include '..\authentication\db.php';

// Initialize pagination variables
$items_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$offset = ($page - 1) * $items_per_page;

// Modify the query to include search and pagination
$search_condition = $search ? "WHERE name LIKE '%$search%' OR description LIKE '%$search%'" : "";
$sql = "SELECT * FROM product $search_condition LIMIT $items_per_page OFFSET $offset";
$result = $conn->query($sql);

// Get total records for pagination
$total_records_sql = "SELECT COUNT(*) as count FROM product $search_condition";
$total_records_result = $conn->query($total_records_sql);
$total_records = $total_records_result->fetch_assoc()['count'];
$total_pages = ceil($total_records / $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-base-100 text-base-content">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Inventory Management</h1>

        <!-- Search and Add Product Controls -->
        <div class="flex justify-between items-center mb-6">
            <div class="form-control w-full max-w-xs">
                <input type="text" id="searchInput" 
                       placeholder="Search products..." 
                       class="input input-bordered w-full" 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <button class="btn btn-error" onclick="openAddProductModal()">Add Product</button>
        </div>

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
                echo "<p class='col-span-3 text-center'>No products found.</p>";
            }
            ?>
        </div>

        <!-- Pagination Controls -->
        <div class="flex justify-center space-x-2 mt-6">
            <?php if ($total_pages > 1): ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                       class="btn btn-sm <?php echo $i === $page ? 'btn-active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-box">
            <h2 class="text-xl font-bold mb-4">Add Product</h2>
            <form id="addProductForm" enctype="multipart/form-data">
                <div class="form-control mb-4">
                    <label class="label">Name</label>
                    <input type="text" name="name" class="input input-bordered" required>
                </div>
                <div class="form-control mb-4">
                    <label class="label">Image</label>
                    <input type="file" name="profile_picture" 
                           class="file-input file-input-bordered file-input-error w-full" 
                           accept="image/*" />
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
            <form id="editProductForm" enctype="multipart/form-data">
                <input type="hidden" name="product_id">
                <div class="form-control mb-4">
                    <label class="label">Name</label>
                    <input type="text" name="name" class="input input-bordered" required>
                </div>
                <div class="form-control mb-4">
                    <label class="label">Image</label>
                    <input type="file" name="profile_picture" 
                           class="file-input file-input-bordered file-input-error w-full" 
                           accept="image/*" />
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
        // Add search functionality
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchQuery = this.value.trim();
                window.location.href = `?search=${encodeURIComponent(searchQuery)}&page=1`;
            }
        });

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
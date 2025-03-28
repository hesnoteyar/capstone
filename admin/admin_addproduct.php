<?php
session_start();
include '../authentication/db.php';
include '../admin/adminnavbar.php';

// Get search query
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Pagination settings
$items_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Modified query with search and pagination
$search_condition = $search ? "WHERE name LIKE '%$search%' OR description LIKE '%$search%'" : "";
$sql = "SELECT product_id, name, description, price, stock_quantity, image FROM product $search_condition";
$count_sql = "SELECT COUNT(*) as total FROM product $search_condition";

// Get total records for pagination
$total_result = $conn->query($count_sql);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row['total'] / $items_per_page);

// Get products with limit
$sql .= " LIMIT $offset, $items_per_page";
$result = $conn->query($sql);

// Get stock summary
$stock_summary_sql = "SELECT 
    SUM(stock_quantity) as total_stock,
    COUNT(CASE WHEN stock_quantity = 0 THEN 1 END) as out_of_stock,
    COUNT(CASE WHEN stock_quantity <= 10 AND stock_quantity > 0 THEN 1 END) as low_stock,
    COUNT(CASE WHEN stock_quantity > 10 THEN 1 END) as in_stock
    FROM product";
$stock_summary = $conn->query($stock_summary_sql)->fetch_assoc();
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
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex: 1;
        }
        .alert {
            transition: opacity 0.5s ease-out;
        }
    </style>
</head>
<body class="bg-base-100 text-base-content">
    <div id="alert-container" class="fixed top-20 right-4 z-50 w-1/3"></div>
    <main class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Inventory Management</h1>

        <!-- Stock Summary -->
        <div class="stats shadow mb-6 w-full">
            <div class="stat">
                <div class="stat-title">Total Stock</div>
                <div class="stat-value text-primary"><?php echo $stock_summary['total_stock']; ?></div>
            </div>
            <div class="stat">
                <div class="stat-title">Out of Stock</div>
                <div class="stat-value text-error"><?php echo $stock_summary['out_of_stock']; ?></div>
            </div>
            <div class="stat">
                <div class="stat-title">Low Stock</div>
                <div class="stat-value text-warning"><?php echo $stock_summary['low_stock']; ?></div>
            </div>
            <div class="stat">
                <div class="stat-title">In Stock</div>
                <div class="stat-value text-success"><?php echo $stock_summary['in_stock']; ?></div>
            </div>
        </div>

        <!-- Search and Add Product -->
        <div class="flex justify-between items-center mb-6">
            <form class="w-1/3">
                <input type="text" name="search" placeholder="Search products..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                    class="input input-bordered w-full"
                    onkeypress="if(event.keyCode==13) this.form.submit();">
            </form>
            <button class="btn btn-error text-white" onclick="openAddProductModal()">Add Product</button>
        </div>

        <!-- Product List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $imageData = base64_encode($row['image']);
                    $imageSrc = 'data:image/jpeg;base64,' . $imageData;
                    $stockBadgeClass = $row['stock_quantity'] == 0 ? 'badge-error' : 
                                     ($row['stock_quantity'] <= 10 ? 'badge-warning' : 'badge-success');
                    $stockStatus = $row['stock_quantity'] == 0 ? 'Out of Stock' : 
                                 ($row['stock_quantity'] <= 10 ? 'Low Stock' : 'In Stock');
                    
                    echo "<div class='card bg-white shadow-md rounded-lg p-4'>
                            <div class='flex justify-between items-start'>
                                <div class='badge $stockBadgeClass'>$stockStatus</div>
                            </div>
                            <img src='" . $imageSrc . "' alt='" . htmlspecialchars($row['name']) . "' class='w-full h-48 object-cover mb-4'>
                            <h2 class='text-xl font-bold mb-2'>" . htmlspecialchars($row['name']) . "</h2>
                            <p class='text-gray-700'>Description: " . htmlspecialchars($row['description']) . "</p>
                            <p class='text-gray-700'>Price: ₱" . number_format($row['price'], 2) . "</p>
                            <p class='text-gray-700'>Quantity: " . htmlspecialchars($row['stock_quantity']) . "</p>
                            <div class='card-actions mt-4'>
                                <button class='btn btn-error' onclick='openEditProductModal(" . $row['product_id'] . ")'>Edit</button>
                                <button class='btn btn-error' onclick='openDeleteProductModal(" . $row['product_id'] . ")'>Delete</button>
                            </div>
                          </div>";
                }
            } else {
                echo "<p class='col-span-3 text-center'>No products found.</p>";
            }
            ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="flex justify-center mt-8 space-x-2">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                   class="btn btn-sm <?php echo ($page === $i) ? 'btn-error text-white' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </main>

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
                <div class="form-control mb-4">
                    <label class="label">Category</label>
                    <select name="category" class="select select-bordered" required>
                        <option value="Car">Car</option>
                        <option value="Motorcycle">Motorcycle</option>
                        <option value="Accessories">Accessories</option>
                    </select>
                </div>
                <div class="form-control mb-4">
                    <label class="label">Image</label>
                    <input type="file" name="image" class="input input-bordered" required>
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
                <div class="form-control mb-4">
                    <label class="label">Category</label>
                    <select name="category" class="select select-bordered" required>
                        <option value="Car">Car</option>
                        <option value="Motorcycle">Motorcycle</option>
                        <option value="Accessories">Accessories</option>
                    </select>
                </div>
                <div class="form-control mb-4">
                    <label class="label">Image</label>
                    <input type="file" name="image" class="input input-bordered">
                </div>
                <div class="modal-action">
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn" onclick="closeEditProductModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Product Modal -->
    <div id="deleteProductModal" class="modal">
        <div class="modal-box">
            <h2 class="text-xl font-bold mb-4">Delete Product</h2>
            <p>Are you sure you want to delete this product?</p>
            <div class="modal-action">
                <button id="confirmDeleteButton" class="btn btn-error">Delete</button>
                <button type="button" class="btn" onclick="closeDeleteProductModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        let productIdToDelete = null;

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alert-container');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} shadow-lg`;
            alert.innerHTML = `
                <div>
                    <span>${message}</span>
                </div>
            `;
            alertContainer.appendChild(alert);

            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 5000);
        }

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
                    document.querySelector('#editProductForm [name="quantity"]').value = data.stock_quantity;
                    document.querySelector('#editProductForm [name="category"]').value = data.category_id;
                    document.getElementById('editProductModal').classList.add('modal-open');
                })
                .catch(error => console.error('Error fetching product details:', error));
        }

        function closeEditProductModal() {
            document.getElementById('editProductModal').classList.remove('modal-open');
        }

        function openDeleteProductModal(productId) {
            productIdToDelete = productId;
            document.getElementById('deleteProductModal').classList.add('modal-open');
        }

        function closeDeleteProductModal() {
            productIdToDelete = null;
            document.getElementById('deleteProductModal').classList.remove('modal-open');
        }

        document.getElementById('confirmDeleteButton').addEventListener('click', function() {
            if (productIdToDelete) {
                fetch('delete_product.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${productIdToDelete}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        localStorage.setItem('alertMessage', 'Product deleted successfully');
                        localStorage.setItem('alertType', 'success');
                    } else {
                        localStorage.setItem('alertMessage', 'Error deleting product: ' + data.error);
                        localStorage.setItem('alertType', 'error');
                    }
                    location.reload();
                })
                .catch(error => {
                    console.error('Error deleting product:', error);
                    localStorage.setItem('alertMessage', 'Error deleting product');
                    localStorage.setItem('alertType', 'error');
                    location.reload();
                })
                .finally(() => {
                    closeDeleteProductModal();
                });
            }
        });

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
                    localStorage.setItem('alertMessage', 'Product added successfully');
                    localStorage.setItem('alertType', 'success');
                } else {
                    localStorage.setItem('alertMessage', 'Error adding product: ' + data.error);
                    localStorage.setItem('alertType', 'error');
                }
                location.reload();
            })
            .catch(error => {
                console.error('Error adding product:', error);
                localStorage.setItem('alertMessage', 'Error adding product');
                localStorage.setItem('alertType', 'error');
                location.reload();
            });
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
                    localStorage.setItem('alertMessage', 'Product updated successfully');
                    localStorage.setItem('alertType', 'success');
                } else {
                    localStorage.setItem('alertMessage', 'Error updating product: ' + data.error);
                    localStorage.setItem('alertType', 'error');
                }
                location.reload();
            })
            .catch(error => {
                console.error('Error updating product:', error);
                localStorage.setItem('alertMessage', 'Error updating product');
                localStorage.setItem('alertType', 'error');
                location.reload();
            });
        });

        // Display alert message if exists in localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const alertMessage = localStorage.getItem('alertMessage');
            const alertType = localStorage.getItem('alertType');
            if (alertMessage && alertType) {
                showAlert(alertMessage, alertType);
                localStorage.removeItem('alertMessage');
                localStorage.removeItem('alertType');
            }
        });
    </script>
    <?php include '../admin/admin_footer.php'; ?>
</body>
</html>
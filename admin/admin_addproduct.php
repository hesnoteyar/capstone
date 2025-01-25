<?php
// filepath: /d:/XAMPP/htdocs/capstone/admin/admin_addproduct.php
session_start(); // Start the session to access session variables
ob_start();
include '../authentication/db.php'; // Include your database connection
include '../admin/adminnavbar.php'; // Include the navbar

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productName = $_POST['product_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];

    // Map category names to category IDs
    $categoryMap = [
        'Car' => 1,
        'Motorcycle' => 2,
        'Accessories' => 3
    ];
    $categoryId = $categoryMap[$category];

    // Handle the image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDirectory = "../productimage/";
        // Ensure the target directory exists
        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0777, true);
        }
        $targetFile = $targetDirectory . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if file is an image
        if (getimagesize($_FILES["image"]["tmp_name"]) !== false) {
            // Move uploaded image to the target directory
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                // Insert the product into the database
                $query = "INSERT INTO product (name, description, price, stock_quantity, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssdiis", $productName, $description, $price, $stock, $categoryId, $targetFile);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Product added successfully!";
                } else {
                    $_SESSION['error_message'] = "Error adding product.";
                }

                $stmt->close();
                $conn->close();

                header("Location: admin_addproduct.php");
                exit;
            } else {
                $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
            }
        } else {
            $_SESSION['error_message'] = "File is not an image.";
        }
    } else {
        $_SESSION['error_message'] = "Error uploading image.";
    }

    header("Location: admin_addproduct.php");
    exit;
}
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
<body class="bg-base-200">
    <div class="container mx-auto p-6">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Add New Product</h2>
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div id="successBanner" class="alert alert-success shadow-lg mb-4">
                        <div>
                            <span><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div id="errorBanner" class="alert alert-error shadow-lg mb-4">
                        <div>
                            <span><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                <form action="admin_addproduct.php" method="POST" enctype="multipart/form-data">
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text">Name of the Product</span>
                        </label>
                        <input type="text" name="product_name" placeholder="Product Name" class="input input-bordered" required />
                    </div>
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text">Description</span>
                        </label>
                        <textarea name="description" placeholder="Product Description" class="textarea textarea-bordered" required></textarea>
                    </div>
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text">Price</span>
                        </label>
                        <input type="number" name="price" placeholder="Price" class="input input-bordered" required />
                    </div>
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text">Stock</span>
                        </label>
                        <input type="number" name="stock" placeholder="Stock" class="input input-bordered" required />
                    </div>
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text">Category</span>
                        </label>
                        <select name="category" class="select select-bordered" required>
                            <option value="Car">Car</option>
                            <option value="Motorcycle">Motorcycle</option>
                            <option value="Accessories">Accessories</option>
                        </select>
                    </div>
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text">Attach Image</span>
                        </label>
                        <input type="file" name="image" class="file-input file-input-bordered" accept="image/*" required />
                    </div>
                    <div class="form-control mt-6">
                        <button type="submit" class="btn btn-error">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // JavaScript to make the banners disappear after 5 seconds
        setTimeout(function() {
            const successBanner = document.getElementById('successBanner');
            const errorBanner = document.getElementById('errorBanner');
            if (successBanner) {
                successBanner.style.display = 'none';
            }
            if (errorBanner) {
                errorBanner.style.display = 'none';
            }
        }, 5000);
    </script>
</body>
    
<?php
    include '../admin/admin_footer.php';
    ob_end_flush();
?>
</html>
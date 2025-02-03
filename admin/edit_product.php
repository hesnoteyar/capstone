<?php
// filepath: /d:/XAMPP/htdocs/capstone/admin/edit_product.php
session_start();
include '../authentication/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
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
        // Validate image size and type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        if (in_array($_FILES['image']['type'], $allowedTypes) && $_FILES['image']['size'] <= $maxFileSize) {
            $image = file_get_contents($_FILES['image']['tmp_name']);

            // Update the product in the database with the new image
            $query = "UPDATE product SET name = ?, description = ?, price = ?, stock_quantity = ?, category_id = ?, image = ? WHERE product_id = ?";
            $stmt = $conn->prepare($query);

            if ($stmt) {
                $null = NULL; // Placeholder for sending BLOB data
                $stmt->bind_param("ssdiibi", $name, $description, $price, $quantity, $categoryId, $null, $product_id);
                $stmt->send_long_data(5, $image); // Send the BLOB data in chunks

                if ($stmt->execute()) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $stmt->error]);
                }

                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to prepare the SQL statement.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid image type or size.']);
        }
    } else {
        // Update the product without changing the image
        $query = "UPDATE product SET name = ?, description = ?, price = ?, stock_quantity = ?, category_id = ? WHERE product_id = ?";
        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param("ssdiii", $name, $description, $price, $quantity, $categoryId, $product_id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $stmt->error]);
            }

            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to prepare the SQL statement.']);
        }
    }

    $conn->close();
}
?>
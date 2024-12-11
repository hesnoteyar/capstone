<?php
session_start();
include '..\authentication\db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['id']; // Assume user_id is stored in session
    $productName = $_POST['product_name']; // Get product name from POST data
    $quantity = $_POST['quantity'];

    // Fetch product details from the product table using the product name
    $productQuery = "SELECT price, category_id, image_url FROM product WHERE name = ?";
    $stmt = $conn->prepare($productQuery);
    $stmt->bind_param("s", $productName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $price = $product['price'];
        $categoryId = $product['category_id'];
        $imageUrl = $product['image_url'];

        // Insert into cart table
        $insertQuery = "INSERT INTO cart (user_id, product_name, quantity, price, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("isidis", $userId, $productName, $quantity, $price, $categoryId, $imageUrl);
        
        if ($insertStmt->execute()) {
            // Log the action in the audit_logs table
            $action = 'ADD_TO_CART';
            $item = $productName; // The item being added
            $auditQuery = "INSERT INTO audit_logs (user_id, action, item) VALUES (?, ?, ?)";
            $auditStmt = $conn->prepare($auditQuery);
            $auditStmt->bind_param("iss", $userId, $action, $item);
            
            if (!$auditStmt->execute()) {
                // Optionally handle logging failure
                error_log("Failed to log audit: " . $auditStmt->error);
            }
            
            echo json_encode(['success' => true, 'message' => 'Product added to cart']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add product to cart']);
        }
        
        // Close statements
        $insertStmt->close();
        if (isset($auditStmt)) {
            $auditStmt->close();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
    }

    // Close the prepared statement for fetching product details
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Close the database connection
$conn->close();
?>

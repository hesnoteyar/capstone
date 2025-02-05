<?php
session_start();
include '../authentication/db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['id']; // Assume user_id is stored in session
    $productName = $_POST['product_name']; // Get product name from POST data
    $quantity = $_POST['quantity'];

    // Debugging: Log received data
    error_log("Received data - userId: $userId, productName: $productName, quantity: $quantity");

    // Fetch product details from the product table using the product name
    $productQuery = "SELECT price, category_id, image FROM product WHERE name = ?";
    $stmt = $conn->prepare($productQuery);
    $stmt->bind_param("s", $productName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $price = $product['price'];
        $categoryId = $product['category_id'];
        $image = $product['image'];

        // Debugging: Log fetched product details
        error_log("Fetched product details - price: $price, categoryId: $categoryId");

        // Insert into cart table
        $insertQuery = "INSERT INTO cart (user_id, product_name, quantity, price, category_id, image) VALUES (?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $null = NULL; // Placeholder for sending BLOB data
        $insertStmt->bind_param("isidib", $userId, $productName, $quantity, $price, $categoryId, $null);
        $insertStmt->send_long_data(5, $image); // Send the BLOB data in chunks
        
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
            // Debugging: Log insert error
            error_log("Failed to insert into cart: " . $insertStmt->error);
            echo json_encode(['success' => false, 'message' => 'Failed to add product to cart']);
        }
    } else {
        // Debugging: Log product not found
        error_log("Product not found: " . $productName);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
    }
}
?>

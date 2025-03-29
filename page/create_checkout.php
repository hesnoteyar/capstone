<?php
session_start();
include '../authentication/db.php';

// Assuming user_id is stored in the session
$user_id = $_SESSION['id'];

// Validate if the user's account is active
$sql_user = "SELECT is_active FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $user = $result_user->fetch_assoc();
    
    // Check if user is inactive
    if ($user['is_active'] == 0) {
        echo json_encode(['error' => 'Please verify your email before proceeding to checkout.']);
        exit;
    }
} else {
    echo json_encode(['error' => 'Invalid user.']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Fetch cart items with current product stock
    $sql = "SELECT c.*, p.quantity as stock_quantity, p.product_id 
            FROM cart c 
            JOIN product p ON c.product_name = p.name 
            WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $cart_items = [];
    $total_price = 0;

    // Check stock availability
    while ($row = $result->fetch_assoc()) {
        if ($row['quantity'] > $row['stock_quantity']) {
            $conn->rollback();
            echo json_encode(['error' => "Not enough stock for {$row['product_name']}. Available: {$row['stock_quantity']}"]);
            exit;
        }
        $cart_items[] = $row;
        $total_price += $row['price'] * $row['quantity'];
    }

    // Update product quantities
    $update_sql = "UPDATE product p 
                   JOIN cart c ON p.name = c.product_name 
                   SET p.quantity = p.quantity - c.quantity 
                   WHERE c.user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $user_id);
    $update_stmt->execute();

    // PayMongo API setup
    $paymongo_secret_key = 'sk_test_jMpSa2FZsGG3TWQo5TEsmc3K'; // Replace with your PayMongo secret key
    $paymongo_checkout_url = 'https://api.paymongo.com/v1/checkout_sessions';

    // Prepare request payload
    $checkout_data = [
        'data' => [
            'attributes' => [
                'line_items' => array_map(function ($item) {
                    return [
                        'name' => $item['product_name'],
                        'description' => $item['product_name'],
                        'amount' => $item['price'] * 100, // Convert to centavos
                        'currency' => 'PHP',
                        'quantity' => $item['quantity'],
                        
                    ];
                }, $cart_items),
                'payment_method_types' => ['card', 'gcash'],
                'amount' => $total_price * 100, // Convert to centavos
                'currency' => 'PHP',
                'description' => 'Cart Checkout',
                'redirect' => [
                    'success' => 'https://www.messenger.com/t/61550100155472', // Replace with your success URL
                    'failed' => 'https://yourwebsite.com/failed',   // Replace with your failure URL
                ],
                'metadata' => ['user_id' => $user_id]
            ]
        ]
    ];

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $paymongo_checkout_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($checkout_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($paymongo_secret_key . ':'),
        'Content-Type: application/json'
    ]);

    // Execute cURL and handle response
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $error_message = curl_error($ch);
        echo json_encode(['error' => $error_message]);
        exit;
    }

    curl_close($ch);

    // Decode and handle PayMongo response
    $response_data = json_decode($response, true);

    if ($http_code === 200 && isset($response_data['data']['attributes']['checkout_url'])) {
        $checkout_url = $response_data['data']['attributes']['checkout_url'];
        
        // Insert purchase details into the purchase_history table
        $stmt_purchase = $conn->prepare("INSERT INTO purchase_history (user_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($cart_items as $item) {
            $product_id = $item['product_id'];
            $product_name = $item['product_name'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            $stmt_purchase->bind_param("iisid", $user_id, $product_id, $product_name, $quantity, $price);
            
            if (!$stmt_purchase->execute()) {
                $conn->rollback();
                echo json_encode(['error' => 'Failed to record purchase']);
                exit;
            }
        }

        // Clear the cart
        $clear_cart = "DELETE FROM cart WHERE user_id = ?";
        $clear_stmt = $conn->prepare($clear_cart);
        $clear_stmt->bind_param("i", $user_id);
        $clear_stmt->execute();

        // Commit the transaction
        $conn->commit();
        
        echo json_encode(['checkout_url' => $checkout_url]);
    } else {
        $conn->rollback();
        echo json_encode(['error' => 'Failed to create checkout session']);
    }

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => 'An error occurred during checkout: ' . $e->getMessage()]);
}

$conn->close();
?>

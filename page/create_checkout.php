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

// Fetch cart items for the user
$sql = "SELECT p.product_id, c.product_name AS name, c.price, c.quantity 
        FROM cart c 
        JOIN product p ON c.product_name = p.name 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total_price = 0;

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total_price += $row['price'] * $row['quantity'];
}

// PayMongo API setup
$paymongo_secret_key = 'sk_test_jMpSa2FZsGG3TWQo5TEsmc3K'; // Replace with your PayMongo secret key
$paymongo_checkout_url = 'https://api.paymongo.com/v1/checkout_sessions';

// Prepare request payload
$checkout_data = [
    'data' => [
        'attributes' => [
            'line_items' => array_map(function ($item) {
                return [
                    'name' => $item['name'],
                    'description' => $item['name'],
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
        $product_name = $item['name'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        $stmt_purchase->bind_param("iisid", $user_id, $product_id, $product_name, $quantity, $price);
        
        // Execute and log any errors
        if (!$stmt_purchase->execute()) {
            echo json_encode(['error' => 'Failed to insert purchase details']);
            exit;
        }
    }
    $stmt_purchase->close();

    // Return the checkout URL as JSON
    echo json_encode(['checkout_url' => $checkout_url]);
} else {
    echo json_encode(['error' => 'Failed to create checkout session.']);
}
?>

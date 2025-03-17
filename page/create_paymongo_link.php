<?php
header('Content-Type: application/json');
session_start();
include '../authentication/db.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// PayMongo API Secret Key
$apiKey = 'sk_test_jMpSa2FZsGG3TWQo5TEsmc3K';

$requestData = json_decode(file_get_contents('php://input'), true);
$amount = $requestData['amount'] ?? null;
$description = $requestData['description'] ?? null;
$currency = $requestData['currency'] ?? 'PHP';

// Assuming user_id is stored in the session
$user_id = $_SESSION['id'];

if (!$amount || !$description) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

// PayMongo payload
$payload = [
    'data' => [
        'attributes' => [
            'amount' => $amount,
            'description' => $description,
            'currency' => $currency
        ]
    ]
];

// Send request to PayMongo API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.paymongo.com/v1/links');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . base64_encode($apiKey . ':')
]);

$response = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    $error_message = curl_error($ch);
    echo json_encode(['error' => 'An error occurred during checkout. Please try again.']);
    exit;
}

curl_close($ch);

// Decode and handle PayMongo response
$response_data = json_decode($response, true);

if ($statusCode === 200 || $statusCode === 201) {
    $checkoutUrl = $response_data['data']['attributes']['checkout_url'] ?? null;

    if ($checkoutUrl) {
        // Insert purchase details into the purchase_history table
        $stmt_purchase = $conn->prepare("INSERT INTO purchase_history (user_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
        
        // Assuming you have product details in the request data
        foreach ($requestData['products'] as $product) {
            $product_id = $product['product_id'];
            $product_name = $product['product_name'];
            $quantity = $product['quantity'];
            $price = $product['price'];
            $stmt_purchase->bind_param("iisid", $user_id, $product_id, $product_name, $quantity, $price);
            
            // Execute and log any errors
            if (!$stmt_purchase->execute()) {
                echo json_encode(['error' => 'Failed to insert purchase details']);
                exit;
            }
        }
        $stmt_purchase->close();

        echo json_encode(['checkout_url' => $checkoutUrl]);
        exit;
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Checkout URL not found']);
        exit;
    }
} else {
    echo json_encode(['error' => 'Failed to create checkout session']);
    exit;
}
?>
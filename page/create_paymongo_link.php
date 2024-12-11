<?php
header('Content-Type: application/json');

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

curl_close($ch);

// Handle success (200 OK or 201 Created)
if ($statusCode === 200 || $statusCode === 201) {
    $responseData = json_decode($response, true);
    $checkoutUrl = $responseData['data']['attributes']['checkout_url'] ?? null;

    if ($checkoutUrl) {
        echo json_encode(['checkout_url' => $checkoutUrl]);
        exit;
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Checkout URL not found']);
        exit;
    }
} else {
    http_response_code($statusCode);
    echo json_encode(['error' => 'Failed to create checkout link']);
}
?>
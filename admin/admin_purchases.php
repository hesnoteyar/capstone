<?php
session_start();
include 'adminnavbar.php';

// PayMongo API Credentials
$paymongo_secret_key = "sk_test_jMpSa2FZsGG3TWQo5TEsmc3K"; // Replace with your real secret key

// API request to PayMongo to get payments
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.paymongo.com/v1/payments");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Basic " . base64_encode($paymongo_secret_key . ":"),
    "Content-Type: application/json"
]);
$response = curl_exec($ch);
curl_close($ch);

// Decode the JSON response
$payments = json_decode($response, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.13/dist/full.min.css" rel="stylesheet" type="text/css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Purchases</title>
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body>
    <div class="p-8">
        <h1 class="text-3xl font-bold mb-6">Customer Purchases</h1>

        <!-- Purchases Table -->
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments['data'])): ?>
                        <?php foreach ($payments['data'] as $payment): ?>
                            <tr>
                                <td><?= $payment['id']; ?></td>
                                <td>₱<?= number_format($payment['attributes']['amount'] / 100, 2); ?></td>
                                <td>
                                    <?php
                                    $status = $payment['attributes']['status'];
                                    if ($status === "paid") {
                                        echo '<span class="badge badge-success">Completed</span>';
                                    } elseif ($status === "pending") {
                                        echo '<span class="badge badge-warning">Pending</span>';
                                    } else {
                                        echo '<span class="badge badge-error">Failed</span>';
                                    }
                                    ?>
                                </td>
                                <td><?= date("Y-m-d H:i:s", strtotime($payment['attributes']['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">No purchases found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
<?php include 'admin_footer.php'; ?>
</html>

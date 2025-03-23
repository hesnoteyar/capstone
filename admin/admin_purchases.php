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

// Helper function to format PayMongo date
function formatPayMongoDate($date_string) {
    $date = new DateTime($date_string);
    $date->setTimezone(new DateTimeZone('Asia/Manila')); // Set to Philippine timezone
    return $date->format('F j, Y, g:i A');
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
                        <th>Action</th>
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
                                <td><?= formatPayMongoDate($payment['attributes']['created_at']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-error" onclick='viewDetails(
                                        "<?= htmlspecialchars($payment['id'], ENT_QUOTES); ?>",
                                        "<?= htmlspecialchars('₱' . number_format($payment['attributes']['amount'] / 100, 2), ENT_QUOTES); ?>",
                                        "<?= htmlspecialchars($payment['attributes']['status'], ENT_QUOTES); ?>",
                                        "<?= htmlspecialchars(formatPayMongoDate($payment['attributes']['created_at']), ENT_QUOTES); ?>",
                                        "<?= htmlspecialchars($payment['attributes']['description'] ?? 'N/A', ENT_QUOTES); ?>"
                                    )'>
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No purchases found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="paymentModal" class="fixed inset-0 z-50 items-center justify-center bg-gray-900 bg-opacity-50" style="display: none;">
        <div class="modal-box bg-white rounded-lg p-6 w-96 relative mx-auto mt-20">
            <h2 class="text-xl font-bold mb-4">Payment Details</h2>
            <div class="space-y-2">
                <p><strong>Payment ID:</strong> <span id="modalPaymentId"></span></p>
                <p><strong>Amount:</strong> <span id="modalAmount"></span></p>
                <p><strong>Status:</strong> <span id="modalStatus"></span></p>
                <p><strong>Created At:</strong> <span id="modalDate"></span></p>
                <p><strong>Description:</strong> <span id="modalDescription"></span></p>
            </div>
            <div class="mt-4 text-right">
                <button class="btn btn-sm btn-error" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        function viewDetails(id, amount, status, date, description) {
            console.log('Opening modal with:', { id, amount, status, date, description });
            document.getElementById("modalPaymentId").textContent = id;
            document.getElementById("modalAmount").textContent = amount;
            document.getElementById("modalStatus").textContent = status;
            document.getElementById("modalDate").textContent = date;
            document.getElementById("modalDescription").textContent = description;
            
            const modal = document.getElementById("paymentModal");
            modal.style.display = "flex";
        }

        function closeModal() {
            document.getElementById("paymentModal").style.display = "none";
        }

        // Initialize modal events when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById("paymentModal");
            
            // Close modal when clicking outside
            modal.addEventListener("click", function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });

            // Close modal with escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
            });
        });
    </script>
</body>
<?php include 'admin_footer.php'; ?>
</html>

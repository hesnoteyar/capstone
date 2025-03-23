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
        body { 
            font-family: 'Poppins', sans-serif;
            background: #f3f4f6;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .table-container {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="min-h-screen p-4 md:p-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Customer Purchases</h1>
                <div class="stats shadow">
                    <div class="stat">
                        <div class="stat-title">Total Purchases</div>
                        <div class="stat-value text-primary"><?= count($payments['data'] ?? []) ?></div>
                    </div>
                </div>
            </div>

            <!-- Purchases Table -->
            <div class="table-container">
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr class="bg-base-200">
                                <th class="font-semibold text-gray-600">Payment ID</th>
                                <th class="font-semibold text-gray-600">Amount</th>
                                <th class="font-semibold text-gray-600">Status</th>
                                <th class="font-semibold text-gray-600">Created At</th>
                                <th class="font-semibold text-gray-600">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($payments['data'])): ?>
                                <?php foreach ($payments['data'] as $payment): ?>
                                    <tr class="hover:bg-base-100">
                                        <td class="font-medium"><?= $payment['id']; ?></td>
                                        <td class="font-medium">₱<?= number_format($payment['attributes']['amount'] / 100, 2); ?></td>
                                        <td>
                                            <?php
                                            $status = $payment['attributes']['status'];
                                            $statusClass = match($status) {
                                                'paid' => 'bg-success/10 text-success',
                                                'pending' => 'bg-warning/10 text-warning',
                                                default => 'bg-error/10 text-error'
                                            };
                                            $statusText = match($status) {
                                                'paid' => 'Completed',
                                                'pending' => 'Pending',
                                                default => 'Failed'
                                            };
                                            echo "<span class='status-badge $statusClass'>$statusText</span>";
                                            ?>
                                        </td>
                                        <td><?= date("F j, Y, g:i a", strtotime($payment['attributes']['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick='viewDetails(
                                                "<?= htmlspecialchars($payment['id'], ENT_QUOTES); ?>",
                                                "<?= htmlspecialchars('₱' . number_format($payment['attributes']['amount'] / 100, 2), ENT_QUOTES); ?>",
                                                "<?= htmlspecialchars($payment['attributes']['status'], ENT_QUOTES); ?>",
                                                "<?= htmlspecialchars(date("F j, Y, g:i a", strtotime($payment['attributes']['created_at'])), ENT_QUOTES); ?>",
                                                "<?= htmlspecialchars($payment['attributes']['description'] ?? 'N/A', ENT_QUOTES); ?>"
                                            )'>
                                                View Details
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-8 text-gray-500">No purchases found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="paymentModal" class="fixed inset-0 z-50 items-center justify-center bg-gray-900/75" style="display: none;">
        <div class="modal-box bg-white rounded-lg p-6 w-full max-w-md relative mx-auto mt-20 shadow-xl">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Payment Details</h2>
                <button onclick="closeModal()" class="btn btn-sm btn-circle btn-ghost">✕</button>
            </div>
            <div class="space-y-4">
                <div class="grid grid-cols-3 gap-4 items-center">
                    <strong class="text-gray-600">Payment ID:</strong>
                    <span id="modalPaymentId" class="col-span-2 font-medium"></span>
                </div>
                <div class="grid grid-cols-3 gap-4 items-center">
                    <strong class="text-gray-600">Amount:</strong>
                    <span id="modalAmount" class="col-span-2 font-medium"></span>
                </div>
                <div class="grid grid-cols-3 gap-4 items-center">
                    <strong class="text-gray-600">Status:</strong>
                    <span id="modalStatus" class="col-span-2 font-medium"></span>
                </div>
                <div class="grid grid-cols-3 gap-4 items-center">
                    <strong class="text-gray-600">Created At:</strong>
                    <span id="modalDate" class="col-span-2 font-medium"></span>
                </div>
                <div class="grid grid-cols-3 gap-4 items-center">
                    <strong class="text-gray-600">Description:</strong>
                    <span id="modalDescription" class="col-span-2 font-medium"></span>
                </div>
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

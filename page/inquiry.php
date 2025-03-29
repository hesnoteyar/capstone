<?php
session_start();
include '../authentication/db.php';

// Get user_id first
$user_id = $_SESSION['id'];

// Handle delete request
if (isset($_POST['delete_request'])) {
    $request_id = mysqli_real_escape_string($conn, $_POST['request_id']);
    $delete_sql = "DELETE FROM service_inquiries WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("ii", $request_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Request cancelled successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error cancelling request. " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }
    
    // Redirect after handling delete
    header("Location: inquiry.php");
    exit();
}

include '../page/topnavbar.php';

// Assuming user_id is stored in the session
$user_id = $_SESSION['id'];

// Fetch existing service requests for the user
$sql = "SELECT * FROM service_inquiries WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@1.1.4/dist/full.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Motorcycle Repair Inquiry</title>
    <style>
        :root {
            --primary: #dc2626;
            --primary-focus: #b91c1c;
        }
    </style>
</head>
<body class="min-h-screen bg-white">
    <!-- Hero Section with Red Gradient -->
    <div class="hero bg-gradient-to-r from-red-600 to-red-800 py-12 text-white" id="hero">
        <div class="hero-content text-center">
            <div>
                <h1 class="text-5xl font-bold">Motorcycle Repair Service</h1>
                <p class="py-6 text-xl opacity-90">Professional repair and maintenance services</p>
            </div>
        </div>
    </div>

    <!-- Notification Banner -->
    <?php if (isset($_SESSION['message'])): ?>
        <div id="alert-banner" class="fixed bottom-4 right-4 z-50 opacity-0 max-w-sm">
            <div class="alert <?php echo $_SESSION['message_type'] === 'success' ? 'alert-success' : 'alert-error'; ?> shadow-lg">
                <div class="flex items-center">
                    <?php if ($_SESSION['message_type'] === 'success'): ?>
                        <i class="fas fa-check-circle text-2xl"></i>
                    <?php else: ?>
                        <i class="fas fa-exclamation-circle text-2xl"></i>
                    <?php endif; ?>
                    <div class="ml-2">
                        <span class=""><?php echo $_SESSION['message']; ?></span>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="btn btn-ghost btn-xs">✕</button>
                </div>
            </div>
        </div>
        <?php 
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        ?>
    <?php endif; ?>

    <main class="container mx-auto p-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Enhanced Left Column -->
            <div class="space-y-6">
                <!-- Service Types Card -->
                <div class="card bg-white shadow-xl" id="services">
                    <div class="card-body p-6">
                        <h2 class="card-title text-2xl mb-6 flex items-center gap-2">
                            <i class="fas fa-tools text-red-600"></i>
                            Our Services
                        </h2>
                        
                        <!-- Service Cards Grid -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="stat bg-red-50 rounded-box p-4">
                                <div class="stat-figure text-red-600">
                                    <i class="fas fa-wrench text-2xl"></i>
                                </div>
                                <div class="stat-title">General</div>
                                <div class="stat-value text-red-600 text-lg">Service</div>
                            </div>
                            <div class="stat bg-red-50 rounded-box p-4">
                                <div class="stat-figure text-red-600">
                                    <i class="fas fa-cog text-2xl"></i>
                                </div>
                                <div class="stat-title">Engine</div>
                                <div class="stat-value text-red-600 text-lg">Repair</div>
                            </div>
                        </div>

                        <!-- Service List -->
                        <ul class="menu bg-base-200 rounded-box p-2">
                            <li>
                                <a class="flex items-center p-3 hover:bg-red-50 active:bg-red-100">
                                    <i class="fas fa-bolt text-red-600 w-6"></i>
                                    <span>Electrical System</span>
                                    <span class="badge badge-sm">Available</span>
                                </a>
                            </li>
                            <li>
                                <a class="flex items-center p-3 hover:bg-red-50 active:bg-red-100">
                                    <i class="fas fa-brake-disc text-red-600 w-6"></i>
                                    <span>Brake Service</span>
                                    <span class="badge badge-sm">Available</span>
                                </a>
                            </li>
                            <li>
                                <a class="flex items-center p-3 hover:bg-red-50 active:bg-red-100">
                                    <i class="fas fa-tire text-red-600 w-6"></i>
                                    <span>Tire Service</span>
                                    <span class="badge badge-sm">Available</span>
                                </a>
                            </li>
                        </ul>

                        <!-- Quick Stats -->
                        <div class="divider">Service Stats</div>
                        <div class="stats stats-vertical shadow">
                            <div class="stat">
                                <div class="stat-title">Response Time</div>
                                <div class="stat-value text-red-600">2-4h</div>
                                <div class="stat-desc">↘︎ 30 minutes</div>
                            </div>
                            <div class="stat">
                                <div class="stat-title">Satisfaction Rate</div>
                                <div class="stat-value text-red-600">98%</div>
                                <div class="stat-desc">↗︎ 2% more than last month</div>
                            </div>
                        </div>

                        <!-- Contact Card -->
                        <div class="alert mt-6">
                            <i class="fas fa-phone-alt text-red-600"></i>
                            <div>
                                <h3 class="font-bold">Need urgent help?</h3>
                                <div class="text-xs">Call us at +1234567890</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Inquiry Form -->
            <div class="lg:col-span-2">
                <!-- Existing Requests Moved Here -->
                <div class="card bg-white shadow-xl border border-gray-100 mb-8" id="existing-requests">
                    <div class="card-body">
                        <h2 class="card-title text-2xl mb-6 text-red-600">Your Service Requests</h2>
                        <?php if ($result->num_rows > 0): ?>
                            <div class="overflow-x-auto">
                                <table class="table table-zebra w-full">
                                    <thead>
                                        <tr>
                                            <th>Reference</th>
                                            <th>Vehicle</th>
                                            <th>Service</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td class="font-mono text-sm"><?php echo htmlspecialchars($row['reference_number']); ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($row['brand'] . ' ' . $row['model']); ?>
                                                    <br>
                                                    <span class="text-sm opacity-60">Year: <?php echo htmlspecialchars($row['year_model']); ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['service_type']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($row['preferred_date'])); ?></td>
                                                <td>
                                                    <?php
                                                    $status_classes = [
                                                        'Pending' => 'badge-warning',
                                                        'Approved' => 'badge-success',
                                                        'In Progress' => 'badge-info',
                                                        'Completed' => 'badge-success',
                                                        'Cancelled' => 'badge-error'
                                                    ];
                                                    $badge_class = $status_classes[$row['status']] ?? 'badge-ghost';
                                                    ?>
                                                    <div class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($row['status']); ?></div>
                                                </td>
                                                <td>
                                                    <?php if ($row['status'] === 'Pending'): ?>
                                                        <label for="cancel-modal-<?php echo $row['id']; ?>" 
                                                            class="btn btn-error btn-xs">
                                                            <i class="fas fa-times"></i> Cancel
                                                        </label>

                                                        <!-- Cancel Modal -->
                                                        <input type="checkbox" id="cancel-modal-<?php echo $row['id']; ?>" class="modal-toggle" />
                                                        <div class="modal">
                                                            <div class="modal-box relative">
                                                                <h3 class="text-lg font-bold">Cancel Service Request</h3>
                                                                <p class="py-4">Are you sure you want to cancel this service request?<br>
                                                                <span class="text-sm opacity-70">Reference: <?php echo htmlspecialchars($row['reference_number']); ?></span></p>
                                                                <div class="modal-action">
                                                                    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                                                                        <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                                                        <div class="flex gap-2">
                                                                            <label for="cancel-modal-<?php echo $row['id']; ?>" class="btn btn-ghost">No, Keep Request</label>
                                                                            <button type="submit" name="delete_request" class="btn btn-error">
                                                                                Yes, Cancel Request
                                                                            </button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert">
                                <i class="fas fa-info-circle"></i>
                                <span>No service requests found. Create your first request below.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card bg-white shadow-xl border border-gray-100" id="form">
                    <div class="card-body">
                        <h2 class="card-title text-2xl mb-6 text-red-600">Service Inquiry</h2>
                        <form action="submit_inquiry.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                            <!-- Two Column Form Layout -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-semibold text-gray-700">Brand</span>
                                    </label>
                                    <input type="text" name="brand" placeholder="e.g., Honda, Yamaha" 
                                        class="input input-bordered hover:border-red-500 focus:border-red-500 focus:ring-red-500" required>
                                </div>

                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-semibold text-gray-700">Model</span>
                                    </label>
                                    <input type="text" name="model" placeholder="e.g., CBR 150R" 
                                        class="input input-bordered hover:border-red-500 focus:border-red-500 focus:ring-red-500" required>
                                </div>
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Motorcycle Details</span>
                                </label>
                                <input type="text" name="brand" placeholder="Brand (e.g., Honda, Yamaha)" 
                                    class="input input-bordered mb-2" required>
                                <input type="text" name="model" placeholder="Model" 
                                    class="input input-bordered mb-2" required>
                                <input type="number" name="year" placeholder="Year Model" 
                                    class="input input-bordered" required>
                            </div>

                            <!-- Service Type -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Service Needed</span>
                                </label>
                                <select class="select select-bordered w-full" name="service_type" required>
                                    <option disabled selected>Select service type</option>
                                    <option>General Maintenance</option>
                                    <option>Engine Repair</option>
                                    <option>Electrical Repair</option>
                                    <option>Brake Service</option>
                                    <option>Tire Service</option>
                                </select>
                            </div>

                            <!-- Problem Description -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Problem Description</span>
                                </label>
                                <textarea class="textarea textarea-bordered h-24" 
                                    name="description" placeholder="Describe the issues..." required></textarea>
                            </div>

                            <!-- File Upload -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Upload Photos (Optional)</span>
                                    <span class="label-text-alt text-gray-500">Max 5MB per file</span>
                                </label>
                                <input type="file" name="photos[]" 
                                    class="file-input file-input-bordered file-input-error w-full" 
                                    accept="image/*"
                                    multiple />
                                <label class="label">
                                    <span class="label-text-alt text-gray-500">You can upload multiple photos of your motorcycle or the specific issues</span>
                                </label>
                            </div>

                            <!-- Contact Info -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Contact Details</span>
                                </label>
                                <input type="tel" name="contact" placeholder="Contact Number" 
                                    class="input input-bordered mb-2" required>
                                <input type="date" name="preferred_date" 
                                    class="input input-bordered" required>
                            </div>

                            <div class="mt-6">
                                <button class="btn btn-error w-full btn-lg bg-red-600 hover:bg-red-700 border-0 text-white">
                                    Submit Service Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Replace existing alert banner animation with this
        const alertBanner = document.getElementById('alert-banner');
        if (alertBanner) {
            gsap.fromTo(alertBanner, 
                {
                    opacity: 0,
                    x: 100
                },
                {
                    duration: 0.5,
                    opacity: 1,
                    x: 0,
                    ease: "back.out(1.7)"
                }
            );

            // Auto dismiss after 5 seconds
            setTimeout(() => {
                gsap.to(alertBanner, {
                    duration: 0.5,
                    opacity: 0,
                    x: 100,
                    ease: "power2.in",
                    onComplete: () => alertBanner.remove()
                });
            }, 5000);
        }

        // Enhanced GSAP animations
        gsap.from("#hero", {
            duration: 1.2,
            y: -50,
            opacity: 0,
            ease: "power3.out"
        });

        gsap.from("#services", {
            duration: 1,
            x: -50,
            opacity: 0,
            delay: 0.3,
            ease: "back.out(1.7)"
        });

        gsap.from("#form", {
            duration: 1,
            x: 50,
            opacity: 0,
            delay: 0.3,
            ease: "back.out(1.7)"
        });

        // Hover animations for service cards
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                gsap.to(card, {
                    duration: 0.3,
                    y: -5,
                    scale: 1.02,
                    ease: "power2.out"
                });
            });

            card.addEventListener('mouseleave', () => {
                gsap.to(card, {
                    duration: 0.3,
                    y: 0,
                    scale: 1,
                    ease: "power2.out"
                });
            });
        });

        // Add hover effect for service items
        document.querySelectorAll('.menu li a').forEach(item => {
            item.addEventListener('mouseenter', () => {
                gsap.to(item, {
                    duration: 0.2,
                    paddingLeft: '1.5rem',
                    ease: "power2.out"
                });
            });
            
            item.addEventListener('mouseleave', () => {
                gsap.to(item, {
                    duration: 0.2,
                    paddingLeft: '1rem',
                    ease: "power2.out"
                });
            });
        });
    </script>

    <?php include '../page/footer.php'; ?>
</body>
</html>
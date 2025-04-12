<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../employee/employee_topnavbar.php';
include '../authentication/db.php'; 
$employee_id = $_SESSION['id']; 
$employee_name = $_SESSION['firstName'] . " " . $_SESSION['lastName'];
$role = $_SESSION['role'];

// Check if user is either Head Mechanic or Mechanic
if ($role !== 'Head Mechanic' && $role !== 'Mechanic') {
    echo "<div class='container mx-auto p-4 text-center text-xl text-red-600 font-bold'>Only Head Mechanics and Mechanics can access this page</div>";
    exit;
}

// Fetch all mechanics from the employee table (for Head Mechanic)
if ($role === 'Head Mechanic') {
    $mechanics_query = "SELECT employee_id, CONCAT(firstName, ' ', lastName) as full_name FROM employee WHERE role = 'Mechanic'";
    $mechanics_result = mysqli_query($conn, $mechanics_query);

    $mechanics = [];
    while ($mechanic = mysqli_fetch_assoc($mechanics_result)) {
        $mechanics[] = $mechanic;
    }
}

// Fetch inquiries from the database
// For Head Mechanics - all inquiries, For Mechanics - only assigned to them
$query = "SELECT id, reference_number, brand, model, year_model, service_type, 
          preferred_date, contact_number, description, status, service_representative,
          proof, CONVERT(proof USING utf8) as proof_base64, progress FROM service_inquiries";

// Add condition for mechanics to only see their assigned inquiries
if ($role === 'Mechanic') {
    $query .= " WHERE service_representative = '$employee_name'";
}

$result = mysqli_query($conn, $query);

// Error handling
if (!$result) {
    $error_message = "Failed to fetch inquiries: " . mysqli_error($conn);
}

// Check for messages
$success_message = "";
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'claimed') {
        $success_message = "You have successfully claimed this inquiry!";
    } else if ($_GET['success'] == 'assigned') {
        $success_message = "Mechanic has been successfully assigned to this inquiry!";
    } else if ($_GET['success'] == 'progress_updated') {
        $success_message = "Progress has been successfully updated!";
    }
}

if (isset($_GET['error']) && $_GET['error'] == 'failed') {
    $error_message = "Operation failed. Please try again.";
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
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <title>Service Inquiries</title>
  <style>
    body { font-family: 'Poppins', sans-serif; }
    .banner { 
      position: fixed; 
      bottom: 20px; 
      right: 20px; 
      display: none; 
      width: auto; 
      max-width: 300px; 
      z-index: 1000;
      padding: 10px 15px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0px 4px 6px rgba(0,0,0,0.1); }
    .inquiry-row:hover { background-color: #f9fafb; }
    .progress-bar {
      height: 20px;
      background-color: #e2e8f0;
      border-radius: 10px;
      overflow: hidden;
    }
    .progress-fill {
      height: 100%;
      background-color: #10b981;
      border-radius: 10px;
      transition: width 0.5s ease-in-out;
    }
  </style>
</head>
<body class="bg-base-200">

    <div class="min-h-screen flex flex-col">
        <div class="flex-grow">
            <div class="container mx-auto p-6">
                <h1 class="text-3xl font-bold mb-6">
                    <?php echo ($role === 'Head Mechanic') ? 'Service Inquiries' : 'My Assigned Inquiries'; ?>
                </h1>
                
                <?php if (isset($error_message)): ?>
                <div id="errorBanner" class="alert alert-error banner">
                    <span><?php echo $error_message; ?></span>
                </div>
                <script>
                    document.getElementById('errorBanner').style.display = 'block';
                    setTimeout(() => document.getElementById('errorBanner').style.display = 'none', 3000);
                </script>
                <?php endif; ?>
                
                <?php if (!empty($success_message)): ?>
                <div id="successBanner" class="alert alert-success banner">
                    <span><?php echo $success_message; ?></span>
                </div>
                <script>
                    document.getElementById('successBanner').style.display = 'block';
                    setTimeout(() => document.getElementById('successBanner').style.display = 'none', 3000);
                </script>
                <?php endif; ?>
                
                <div class="mb-6">
                    <label class="mr-2 font-semibold">Filter by Status:</label>
                    <select id="statusFilter" class="select select-bordered">
                        <option value="all">All</option>
                        <option value="Pending">Pending</option>
                        <option value="Claimed">Claimed</option>
                        <?php if ($role === 'Mechanic'): ?>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="overflow-x-auto table-container">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Reference #</th>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>Service Type</th>
                                <th>Preferred Date</th>
                                <th>Status</th>
                                <?php if ($role === 'Mechanic'): ?>
                                <th>Progress</th>
                                <?php endif; ?>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="inquiryTable">
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="inquiry-row" data-status="<?php echo $row['status']; ?>">
                                <td><?php echo $row['reference_number']; ?></td>
                                <td><?php echo $row['brand']; ?></td>
                                <td><?php echo $row['model']; ?></td>
                                <td><?php echo $row['service_type']; ?></td>
                                <td><?php echo $row['preferred_date']; ?></td>
                                <td>
                                    <span class="badge 
                                        <?php 
                                        if ($row['status'] == 'Pending') echo 'badge-warning';
                                        elseif ($row['status'] == 'Claimed') echo 'badge-info';
                                        elseif ($row['status'] == 'In Progress') echo 'badge-primary';
                                        elseif ($row['status'] == 'Completed') echo 'badge-success';
                                        else echo 'badge-secondary';
                                        ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <?php if ($role === 'Mechanic'): ?>
                                <td>
                                    <?php 
                                    $progress = isset($row['progress']) ? intval($row['progress']) : 0;
                                    ?>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                                    </div>
                                    <div class="text-xs text-right mt-1"><?php echo $progress; ?>%</div>
                                </td>
                                <?php endif; ?>
                                <td>
                                    <?php 
                                    $modalData = $row;
                                    if (!empty($row['proof'])) {
                                        $modalData['proof'] = 'data:image/jpeg;base64,' . $row['proof_base64'];
                                    } else {
                                        $modalData['proof'] = null;
                                    }
                                    ?>
                                    <button class="btn btn-error btn-sm" 
                                            onclick='openInquiryModal(<?php echo json_encode($modalData); ?>)'>
                                        View
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php include '../employee/employee_footer.php'; ?>
    </div>

    <!-- Modal for viewing inquiry details -->
    <dialog id="inquiryModal" class="modal">
        <div class="modal-box max-w-4xl">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="font-bold text-2xl mb-4">Service Inquiry Details</h3>
            
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-lg">Reference #: <span id="ref-number"></span></h4>
                <span id="status-badge" class="badge text-lg p-3"></span>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <h5 class="text-lg font-bold mb-3">Vehicle Information</h5>
                    <div class="overflow-x-auto">
                        <table class="table table-compact w-full">
                            <tbody>
                                <tr>
                                    <td class="font-semibold w-1/3">Brand</td>
                                    <td id="brand"></td>
                                </tr>
                                <tr>
                                    <td class="font-semibold">Model</td>
                                    <td id="model"></td>
                                </tr>
                                <tr>
                                    <td class="font-semibold">Year</td>
                                    <td id="year"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div>
                    <h5 class="text-lg font-bold mb-3">Service Information</h5>
                    <div class="overflow-x-auto">
                        <table class="table table-compact w-full">
                            <tbody>
                                <tr>
                                    <td class="font-semibold w-1/3">Service Type</td>
                                    <td id="service-type"></td>
                                </tr>
                                <tr>
                                    <td class="font-semibold">Preferred Date</td>
                                    <td id="preferred-date"></td>
                                </tr>
                                <tr>
                                    <td class="font-semibold">Contact Number</td>
                                    <td id="contact-number"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="mt-6">
                <h5 class="text-lg font-bold mb-3">Service Description</h5>
                <div class="p-4 bg-base-200 rounded-lg">
                    <p id="description"></p>
                </div>
            </div>
            
            <div class="mt-6">
                <h5 class="text-lg font-bold mb-3">Proof of Downpayment</h5>
                <div id="proof-container" class="p-4 bg-base-200 rounded-lg flex justify-center">
                    <!-- Proof image will be inserted here -->
                </div>
            </div>
            
            <div class="mt-6">
                <h5 class="text-lg font-bold mb-3">Service Representative</h5>
                <p id="service-rep"></p>
            </div>
            
            <!-- Progress tracking section for mechanics -->
            <div class="mt-6" id="progress-update-section" style="display: none;">
                <h5 class="text-lg font-bold mb-3">Update Progress</h5>
                <form id="update-progress-form" method="POST" action="update_progress.php">
                    <input type="hidden" id="progress-inquiry-id" name="inquiry_id" value="">
                    
                    <div class="mt-3">
                        <label class="block mb-2">Current Progress:</label>
                        <div class="flex items-center gap-4">
                            <input type="range" id="progress-slider" name="progress" min="0" max="100" value="0" class="range range-primary" />
                            <span id="progress-value" class="text-lg font-bold">0%</span>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label class="block mb-2">Status:</label>
                        <select name="status" id="status-select" class="select select-bordered w-full">
                            <option value="Claimed">Claimed</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Update Progress</button>
                    </div>
                </form>
            </div>
            
            <!-- Add mechanic assignment section -->
            <div class="mt-6" id="mechanic-assignment-section" style="display: none;">
                <h5 class="text-lg font-bold mb-3">Assign Mechanic</h5>
                <form id="assign-mechanic-form" method="POST" action="assign_mechanic.php">
                    <input type="hidden" id="inquiry-id" name="inquiry_id" value="">
                    <select id="mechanic-select" name="mechanic_name" class="select select-bordered w-full">
                        <option value="">Select a Mechanic</option>
                        <?php if ($role === 'Head Mechanic'): ?>
                            <?php foreach ($mechanics as $mechanic): ?>
                                <option value="<?= $mechanic['full_name'] ?>"><?= $mechanic['full_name'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Assign Mechanic</button>
                    </div>
                </form>
            </div>
            
            <div class="modal-action" id="modal-actions">
                <!-- Action buttons will be added here by JavaScript -->
            </div>
        </div>
    </dialog>

    <!-- Add this new modal for zoomed image after the inquiry modal -->
    <dialog id="imageZoomModal" class="modal">
        <div class="modal-box max-w-5xl h-auto relative">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2 z-10">✕</button>
            </form>
            <div class="flex justify-center items-center">
                <img id="zoomedImage" class="max-w-full max-h-[80vh] object-contain" src="" alt="Zoomed proof" />
            </div>
        </div>
    </dialog>
    
    <script>
        // Add user role for JavaScript
        const userRole = "<?php echo $role; ?>";
        
        // Filter functionality
        document.getElementById('statusFilter').addEventListener('change', function() {
            const selectedStatus = this.value.toLowerCase();
            document.querySelectorAll('.inquiry-row').forEach(row => {
                row.style.display = (selectedStatus === 'all' || row.dataset.status.toLowerCase() === selectedStatus.toLowerCase()) ? '' : 'none';
            });
        });
        
        // Progress slider functionality
        if (document.getElementById('progress-slider')) {
            document.getElementById('progress-slider').addEventListener('input', function() {
                document.getElementById('progress-value').textContent = this.value + '%';
            });
        }
        
        // Modal functionality
        function openInquiryModal(inquiry) {
            const modal = document.getElementById('inquiryModal');
            
            // Set all the values
            document.getElementById('ref-number').textContent = inquiry.reference_number;
            document.getElementById('brand').textContent = inquiry.brand;
            document.getElementById('model').textContent = inquiry.model;
            document.getElementById('year').textContent = inquiry.year_model;
            document.getElementById('service-type').textContent = inquiry.service_type;
            document.getElementById('preferred-date').textContent = inquiry.preferred_date;
            document.getElementById('contact-number').textContent = inquiry.contact_number;
            document.getElementById('description').textContent = inquiry.description;
            document.getElementById('service-rep').textContent = inquiry.service_representative ? inquiry.service_representative : 'Unassigned';
            
            // Set status badge
            const statusBadge = document.getElementById('status-badge');
            statusBadge.textContent = inquiry.status;
            
            if (inquiry.status == 'Pending') {
                statusBadge.className = 'badge badge-warning text-lg p-3';
            } else if (inquiry.status == 'Claimed') {
                statusBadge.className = 'badge badge-info text-lg p-3';
            } else if (inquiry.status == 'In Progress') {
                statusBadge.className = 'badge badge-primary text-lg p-3';
            } else if (inquiry.status == 'Completed') {
                statusBadge.className = 'badge badge-success text-lg p-3';
            } else {
                statusBadge.className = 'badge badge-secondary text-lg p-3';
            }
            
            // Set the inquiry ID
            document.getElementById('inquiry-id').value = inquiry.id;
            
            // Show/hide sections based on role
            const mechanicAssignmentSection = document.getElementById('mechanic-assignment-section');
            const progressUpdateSection = document.getElementById('progress-update-section');
            
            // Handle mechanics view
            if (userRole === 'Mechanic') {
                mechanicAssignmentSection.style.display = 'none';
                
                if (inquiry.service_representative === "<?php echo $employee_name; ?>") {
                    progressUpdateSection.style.display = 'block';
                    document.getElementById('progress-inquiry-id').value = inquiry.id;
                    
                    // Set current progress
                    const currentProgress = inquiry.progress ? parseInt(inquiry.progress) : 0;
                    document.getElementById('progress-slider').value = currentProgress;
                    document.getElementById('progress-value').textContent = currentProgress + '%';
                    
                    // Set current status in dropdown
                    const statusSelect = document.getElementById('status-select');
                    for (let i = 0; i < statusSelect.options.length; i++) {
                        if (statusSelect.options[i].value === inquiry.status) {
                            statusSelect.selectedIndex = i;
                            break;
                        }
                    }
                } else {
                    progressUpdateSection.style.display = 'none';
                }
            } 
            // Handle head mechanic view
            else if (userRole === 'Head Mechanic') {
                progressUpdateSection.style.display = 'none';
                
                if (inquiry.status == 'Pending' && !inquiry.service_representative) {
                    mechanicAssignmentSection.style.display = 'block';
                } else {
                    mechanicAssignmentSection.style.display = 'none';
                }
                
                // Pre-select the current mechanic in the dropdown if one is assigned
                if (inquiry.service_representative) {
                    const selectElement = document.getElementById('mechanic-select');
                    for (let i = 0; i < selectElement.options.length; i++) {
                        if (selectElement.options[i].value === inquiry.service_representative) {
                            selectElement.selectedIndex = i;
                            break;
                        }
                    }
                }
            }
            
            // Update the proof image handling
            const proofContainer = document.getElementById('proof-container');
            proofContainer.innerHTML = ''; // Clear previous content
            
            if (inquiry.proof) {
                const img = document.createElement('img');
                img.src = inquiry.proof;
                img.style.maxHeight = '400px';
                img.style.maxWidth = '100%';
                img.classList.add('rounded-lg', 'object-contain', 'cursor-pointer', 'hover:opacity-90', 'transition-opacity');
                img.onclick = function() {
                    const zoomModal = document.getElementById('imageZoomModal');
                    const zoomedImage = document.getElementById('zoomedImage');
                    zoomedImage.src = inquiry.proof;
                    zoomModal.showModal();
                };
                img.onerror = function() {
                    proofContainer.innerHTML = '<p class="text-gray-400">Error loading image</p>';
                };
                proofContainer.appendChild(img);
            } else {
                proofContainer.innerHTML = '<p class="text-gray-400">No proof available</p>';
            }
            
            // Set action buttons
            const actionsContainer = document.getElementById('modal-actions');
            actionsContainer.innerHTML = '';
            
            if (userRole === 'Head Mechanic' && inquiry.status == 'Pending' && !inquiry.service_representative) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'claim_inquiry.php';
                
                const inquiryIdInput = document.createElement('input');
                inquiryIdInput.type = 'hidden';
                inquiryIdInput.name = 'inquiry_id';
                inquiryIdInput.value = inquiry.id;
                
                const buttonGroup = document.createElement('div');
                buttonGroup.className = 'flex gap-3';
                
                const claimButton = document.createElement('button');
                claimButton.type = 'submit';
                claimButton.className = 'btn btn-primary';
                claimButton.textContent = 'Claim';
                
                const closeButton = document.createElement('button');
                closeButton.type = 'button';
                closeButton.className = 'btn';
                closeButton.textContent = 'Close';
                closeButton.onclick = function() { modal.close(); };
                
                buttonGroup.appendChild(claimButton);
                buttonGroup.appendChild(closeButton);
                form.appendChild(inquiryIdInput);
                form.appendChild(buttonGroup);
                actionsContainer.appendChild(form);
            } else {
                const closeButton = document.createElement('button');
                closeButton.type = 'button';
                closeButton.className = 'btn';
                closeButton.textContent = 'Close';
                closeButton.onclick = function() { modal.close(); };
                actionsContainer.appendChild(closeButton);
            }
            
            // Open the modal
            modal.showModal();
        }
    </script>

</body>
</html>
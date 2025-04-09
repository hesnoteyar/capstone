<?php
session_start();

include '../employee/employee_topnavbar.php';
include '../authentication/db.php'; 
$employee_id = $_SESSION['id']; 
$employee_name = $_SESSION['firstName'] . " " . $_SESSION['lastName'];
$role = $_SESSION['role'];

if ($role !== 'Mechanic' && $role !== 'Head Mechanic') {
    echo "<div class='container mx-auto p-4 text-center text-xl text-red-600 font-bold'>You are not a Mechanic</div>";
    exit;
}

// Different queries based on role
if ($role === 'Head Mechanic') {
    // Head Mechanic sees all inquiries
    $query = "SELECT id, reference_number, brand, model, year_model, service_type, 
              preferred_date, contact_number, description, status, service_representative,
              proof, CONVERT(proof USING utf8) as proof_base64 FROM service_inquiries";
} else {
    // Regular Mechanic only sees inquiries assigned to them
    $query = "SELECT id, reference_number, brand, model, year_model, service_type, 
              preferred_date, contact_number, description, status, service_representative,
              proof, CONVERT(proof USING utf8) as proof_base64 FROM service_inquiries 
              WHERE service_representative = '$employee_name'";
}

$result = mysqli_query($conn, $query);

// Error handling
if (!$result) {
    $error_message = "Failed to fetch inquiries: " . mysqli_error($conn);
}

// Check for success messages
$success_message = "";
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'claimed') {
        $success_message = "You have successfully claimed this inquiry!";
    } elseif ($_GET['success'] == 'assigned') {
        $success_message = "You have successfully assigned a mechanic to this inquiry!";
    } elseif ($_GET['success'] == 'updated') {
        $success_message = "Inquiry status has been successfully updated!";
    }
}

// Get all mechanics for the dropdown (for Head Mechanic)
$mechanics = array();
if ($role === 'Head Mechanic') {
    $mechanic_query = "SELECT id, CONCAT(firstName, ' ', lastName) as mechanic_name FROM users WHERE role = 'Mechanic'";
    $mechanic_result = mysqli_query($conn, $mechanic_query);
    if ($mechanic_result) {
        while ($mechanic = mysqli_fetch_assoc($mechanic_result)) {
            $mechanics[] = $mechanic;
        }
    }
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
  </style>
</head>
<body class="bg-base-200">

    <div class="min-h-screen flex flex-col">
        <div class="flex-grow">
            <div class="container mx-auto p-6">
                <h1 class="text-3xl font-bold mb-6">
                    <?php echo $role === 'Head Mechanic' ? 'All Service Inquiries' : 'My Assigned Inquiries'; ?>
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
                                <?php if ($role === 'Head Mechanic'): ?>
                                <th>Assigned To</th>
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
                                    <span class="badge <?php 
                                        if ($row['status'] == 'Pending') echo 'badge-warning';
                                        elseif ($row['status'] == 'In Progress') echo 'badge-info';
                                        elseif ($row['status'] == 'Completed') echo 'badge-success';
                                        else echo 'badge-primary';
                                    ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <?php if ($role === 'Head Mechanic'): ?>
                                <td>
                                    <?php echo $row['service_representative'] ? $row['service_representative'] : 'Unassigned'; ?>
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
                                            onclick='openInquiryModal(<?php echo json_encode($modalData); ?>, "<?php echo $role; ?>")'>
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
            
            <div class="modal-action" id="modal-actions">
                <!-- Action buttons will be added here by JavaScript -->
            </div>
        </div>
    </dialog>

    <!-- Add this new modal for assigning mechanics -->
    <dialog id="assignMechanicModal" class="modal">
        <div class="modal-box">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="font-bold text-lg mb-4">Assign Mechanic</h3>
            <form id="assignMechanicForm" method="POST" action="assign_mechanic.php">
                <input type="hidden" name="inquiry_id" id="assign_inquiry_id" value="">
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text">Select Mechanic:</span>
                    </label>
                    <select name="mechanic" class="select select-bordered w-full" required>
                        <option value="" disabled selected>Choose a mechanic</option>
                        <?php foreach ($mechanics as $mechanic): ?>
                        <option value="<?php echo $mechanic['mechanic_name']; ?>"><?php echo $mechanic['mechanic_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-action">
                    <button type="submit" class="btn btn-primary">Assign</button>
                    <button type="button" class="btn" onclick="document.getElementById('assignMechanicModal').close()">Cancel</button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Add this new modal for updating inquiry status -->
    <dialog id="updateStatusModal" class="modal">
        <div class="modal-box">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="font-bold text-lg mb-4">Update Inquiry Status</h3>
            <form id="updateStatusForm" method="POST" action="update_inquiry_status.php">
                <input type="hidden" name="inquiry_id" id="update_inquiry_id" value="">
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text">New Status:</span>
                    </label>
                    <select name="status" class="select select-bordered w-full" required>
                        <option value="" disabled selected>Select status</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <div class="modal-action">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn" onclick="document.getElementById('updateStatusModal').close()">Cancel</button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Add the image zoom modal -->
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
        // Filter functionality
        document.getElementById('statusFilter').addEventListener('change', function() {
            const selectedStatus = this.value.toLowerCase();
            document.querySelectorAll('.inquiry-row').forEach(row => {
                row.style.display = (selectedStatus === 'all' || row.dataset.status.toLowerCase() === selectedStatus) ? '' : 'none';
            });
        });
        
        // Modal functionality
        function openInquiryModal(inquiry, role) {
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
            
            // Set status badge
            const statusBadge = document.getElementById('status-badge');
            statusBadge.textContent = inquiry.status;
            statusBadge.className = `badge ${inquiry.status == 'Pending' ? 'badge-warning' : 
                                    inquiry.status == 'In Progress' ? 'badge-info' : 
                                    inquiry.status == 'Completed' ? 'badge-success' : 'badge-primary'} text-lg p-3`;
            
            // Set action buttons based on role
            const actionsContainer = document.getElementById('modal-actions');
            actionsContainer.innerHTML = '';
            
            if (role === 'Head Mechanic') {
                // Head Mechanic actions - can assign mechanics to inquiries
                if (inquiry.status !== 'Completed') {
                    const assignButton = document.createElement('button');
                    assignButton.type = 'button';
                    assignButton.className = 'btn btn-primary';
                    assignButton.textContent = inquiry.service_representative ? 'Reassign Mechanic' : 'Assign Mechanic';
                    assignButton.onclick = function() {
                        document.getElementById('assign_inquiry_id').value = inquiry.id;
                        document.getElementById('assignMechanicModal').showModal();
                        modal.close();
                    };
                    actionsContainer.appendChild(assignButton);
                }
            } else if (role === 'Mechanic') {
                // Mechanic actions - can update status of assigned inquiries
                if (inquiry.status !== 'Completed' && inquiry.service_representative) {
                    const updateStatusButton = document.createElement('button');
                    updateStatusButton.type = 'button';
                    updateStatusButton.className = 'btn btn-primary';
                    updateStatusButton.textContent = 'Update Status';
                    updateStatusButton.onclick = function() {
                        document.getElementById('update_inquiry_id').value = inquiry.id;
                        document.getElementById('updateStatusModal').showModal();
                        modal.close();
                    };
                    actionsContainer.appendChild(updateStatusButton);
                }
            }
            
            // Close button for all roles
            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'btn';
            closeButton.textContent = 'Close';
            closeButton.onclick = function() { modal.close(); };
            actionsContainer.appendChild(closeButton);
            
            // Open the modal
            modal.showModal();
        }
    </script>

</body>
</html>
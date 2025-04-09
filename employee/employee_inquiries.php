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

// Fetch inquiries from the database - different queries based on role
$query = "SELECT id, reference_number, brand, model, year_model, service_type, 
          preferred_date, contact_number, description, status, service_representative,
          proof, CONVERT(proof USING utf8) as proof_base64 FROM service_inquiries";

// Filter inquiries for regular mechanics - only show assigned inquiries
if ($role === 'Mechanic') {
    $query .= " WHERE service_representative = '$employee_name'";
}

$result = mysqli_query($conn, $query);

// Fetch all mechanics for the assignment dropdown (for Head Mechanic)
$mechanics = [];
if ($role === 'Head Mechanic') {
    $mechanic_query = "SELECT id, CONCAT(firstName, ' ', lastName) as fullName FROM users WHERE role = 'Mechanic'";
    $mechanic_result = mysqli_query($conn, $mechanic_query);
    while ($mechanic = mysqli_fetch_assoc($mechanic_result)) {
        $mechanics[] = $mechanic;
    }
}

// Error handling
if (!$result) {
    $error_message = "Failed to fetch inquiries: " . mysqli_error($conn);
}

// Check for success messages
$success_message = "";
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'claimed') {
        $success_message = "You have successfully claimed this inquiry!";
    } else if ($_GET['success'] == 'assigned') {
        $success_message = "You have successfully assigned this inquiry!";
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
                <h1 class="text-3xl font-bold mb-6">Service Inquiries</h1>
                
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
                                    <span class="badge <?php echo $row['status'] == 'Pending' ? 'badge-warning' : 'badge-success'; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
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
        // Filter functionality
        document.getElementById('statusFilter').addEventListener('change', function() {
            const selectedStatus = this.value.toLowerCase();
            document.querySelectorAll('.inquiry-row').forEach(row => {
                row.style.display = (selectedStatus === 'all' || row.dataset.status.toLowerCase() === selectedStatus) ? '' : 'none';
            });
        });
        
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
            statusBadge.className = `badge ${inquiry.status == 'Pending' ? 'badge-warning' : 'badge-success'} text-lg p-3`;
            
            // Set action buttons
            const actionsContainer = document.getElementById('modal-actions');
            actionsContainer.innerHTML = '';
            
            <?php if ($role === 'Head Mechanic'): ?>
            if (inquiry.status == 'Pending' && !inquiry.service_representative) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'assign_inquiry.php';
                
                const inquiryIdInput = document.createElement('input');
                inquiryIdInput.type = 'hidden';
                inquiryIdInput.name = 'inquiry_id';
                inquiryIdInput.value = inquiry.id;
                
                const formGroup = document.createElement('div');
                formGroup.className = 'form-control mb-4';
                
                const label = document.createElement('label');
                label.className = 'label';
                label.innerHTML = '<span class="label-text">Assign to Mechanic:</span>';
                
                const selectGroup = document.createElement('div');
                selectGroup.className = 'flex gap-3';
                
                const select = document.createElement('select');
                select.name = 'mechanic';
                select.className = 'select select-bordered w-full';
                select.required = true;
                
                // Add default option
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Select a Mechanic';
                defaultOption.selected = true;
                defaultOption.disabled = true;
                select.appendChild(defaultOption);
                
                // Add mechanic options from PHP
                <?php foreach ($mechanics as $mechanic): ?>
                const option<?php echo $mechanic['id']; ?> = document.createElement('option');
                option<?php echo $mechanic['id']; ?>.value = "<?php echo $mechanic['fullName']; ?>";
                option<?php echo $mechanic['id']; ?>.textContent = "<?php echo $mechanic['fullName']; ?>";
                select.appendChild(option<?php echo $mechanic['id']; ?>);
                <?php endforeach; ?>
                
                // Add self-assignment option
                const selfOption = document.createElement('option');
                selfOption.value = "<?php echo $employee_name; ?>";
                selfOption.textContent = "Assign to myself";
                select.appendChild(selfOption);
                
                selectGroup.appendChild(select);
                formGroup.appendChild(label);
                formGroup.appendChild(selectGroup);
                
                const buttonGroup = document.createElement('div');
                buttonGroup.className = 'flex gap-3 mt-4';
                
                const assignButton = document.createElement('button');
                assignButton.type = 'submit';
                assignButton.className = 'btn btn-primary';
                assignButton.textContent = 'Assign';
                
                const closeButton = document.createElement('button');
                closeButton.type = 'button';
                closeButton.className = 'btn';
                closeButton.textContent = 'Close';
                closeButton.onclick = function() { modal.close(); };
                
                buttonGroup.appendChild(assignButton);
                buttonGroup.appendChild(closeButton);
                
                form.appendChild(inquiryIdInput);
                form.appendChild(formGroup);
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
            <?php else: ?>
            // For regular mechanic, just show close button
            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'btn';
            closeButton.textContent = 'Close';
            closeButton.onclick = function() { modal.close(); };
            actionsContainer.appendChild(closeButton);
            <?php endif; ?>
            
            // Open the modal
            modal.showModal();
        }
    </script>

</body>
</html>
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../employee/employee_topnavbar.php';
include '../authentication/db.php'; 
$employee_id = $_SESSION['id']; 
$employee_name = $_SESSION['firstName'] . " " . $_SESSION['lastName'];
$role = $_SESSION['role'];

// Restrict access to Head Mechanics only
if ($role !== 'Head Mechanic') {
    echo "<div class='container mx-auto p-4 text-center text-xl text-red-600 font-bold'>Only Head Mechanics can access this page</div>";
    exit;
}

// Fetch all mechanics from the employee table
$mechanics_query = "SELECT employee_id, CONCAT(firstName, ' ', lastName) as full_name FROM employee WHERE role = 'Mechanic'";
$mechanics_result = mysqli_query($conn, $mechanics_query);

$mechanics = [];
while ($mechanic = mysqli_fetch_assoc($mechanics_result)) {
    $mechanics[] = $mechanic;
}

// Fetch inquiries from the database
$query = "SELECT id, reference_number, brand, model, year_model, service_type, 
          preferred_date, contact_number, description, status, service_representative,
          proof, CONVERT(proof USING utf8) as proof_base64 FROM service_inquiries";
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
    }
}

if (isset($_GET['error']) && $_GET['error'] == 'failed') {
    $error_message = "Failed to assign mechanic. Please try again.";
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
            
            <!-- Add mechanic assignment section -->
            <div class="mt-6" id="mechanic-assignment-section">
                <h5 class="text-lg font-bold mb-3">Assign Mechanic</h5>
                <form id="assign-mechanic-form" method="POST" action="assign_mechanic.php">
                    <input type="hidden" id="inquiry-id" name="inquiry_id" value="">
                    <select id="mechanic-select" name="mechanic_name" class="select select-bordered w-full">
                        <option value="">Select a Mechanic</option>
                        <?php foreach ($mechanics as $mechanic): ?>
                            <option value="<?= $mechanic['full_name'] ?>"><?= $mechanic['full_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Assign Mechanic</button>
                    </div>
                </form>
            </div>
            
            <!-- Add PDF download button section -->
            <div class="mt-6" id="pdf-download-section" style="display: none;">
                <h5 class="text-lg font-bold mb-3">Assignment Document</h5>
                <a id="download-pdf-btn" href="#" class="btn btn-outline btn-success">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Download Assignment PDF
                </a>
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
            
            // Set the inquiry ID for the mechanic assignment form
            document.getElementById('inquiry-id').value = inquiry.id;
            
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
            
            // Show/hide PDF download button based on whether a mechanic is assigned
            const pdfDownloadSection = document.getElementById('pdf-download-section');
            const downloadPdfBtn = document.getElementById('download-pdf-btn');
            
            if (inquiry.service_representative) {
                pdfDownloadSection.style.display = 'block';
                const pdfUrl = `generate_assignment_pdf.php?inquiry_id=${inquiry.id}&mechanic=${encodeURIComponent(inquiry.service_representative)}&head_mechanic=${encodeURIComponent("<?php echo $employee_name; ?>")}`;
                downloadPdfBtn.href = pdfUrl;
            } else {
                pdfDownloadSection.style.display = 'none';
            }
            
            // Set action buttons
            const actionsContainer = document.getElementById('modal-actions');
            actionsContainer.innerHTML = '';
            
            if (inquiry.status == 'Pending' && !inquiry.service_representative) {
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
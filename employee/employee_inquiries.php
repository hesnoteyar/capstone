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

// Fetch inquiries from the database
$query = "SELECT * FROM service_inquiries";
$result = mysqli_query($conn, $query);

// Error handling
if (!$result) {
    $error_message = "Failed to fetch inquiries: " . mysqli_error($conn);
}

// Check for claim success message
$success_message = "";
if (isset($_GET['success']) && $_GET['success'] == 'claimed') {
    $success_message = "You have successfully claimed this inquiry!";
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
                                    <button class="btn btn-error btn-sm" 
                                            onclick="openInquiryModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">
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
                <h5 class="text-lg font-bold mb-3">Service Representative</h5>
                <p id="service-rep"></p>
            </div>
            
            <div class="modal-action" id="modal-actions">
                <!-- Action buttons will be added here by JavaScript -->
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
            
            // Set status badge
            const statusBadge = document.getElementById('status-badge');
            statusBadge.textContent = inquiry.status;
            statusBadge.className = `badge ${inquiry.status == 'Pending' ? 'badge-warning' : 'badge-success'} text-lg p-3`;
            
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
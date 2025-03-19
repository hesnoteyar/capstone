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
    .banner { position: fixed; bottom: 20px; right: 20px; display: none; }
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
                    setTimeout(() => document.getElementById('errorBanner').style.display = 'none', 5000);
                </script>
                <?php endif; ?>
                
                <div class="overflow-x-auto table-container">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Reference #</th>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="inquiryTable">
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="inquiry-row" data-status="<?php echo $row['status']; ?>">
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['reference_number']; ?></td>
                                <td><?php echo $row['brand']; ?></td>
                                <td><?php echo $row['model']; ?></td>
                                <td>
                                    <span class="badge <?php echo $row['status'] == 'Pending' ? 'badge-warning' : 'badge-success'; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm" onclick="openModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">View</button>
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

    <!-- Modal -->
    <div id="inquiryModal" class="modal hidden">
        <div class="modal-box">
            <h2 class="text-xl font-bold">Service Inquiry Details</h2>
            <p id="modalContent"></p>
            <div class="modal-action">
                <button class="btn" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        function openModal(data) {
            let content = `<strong>Reference #:</strong> ${data.reference_number}<br>
                           <strong>Brand:</strong> ${data.brand}<br>
                           <strong>Model:</strong> ${data.model}<br>
                           <strong>Year:</strong> ${data.year_model}<br>
                           <strong>Service Type:</strong> ${data.service_type}<br>
                           <strong>Description:</strong> ${data.description}<br>
                           <strong>Contact:</strong> ${data.contact_number}<br>
                           <strong>Preferred Date:</strong> ${data.preferred_date}<br>
                           <strong>Status:</strong> ${data.status}<br>
                           <strong>Service Rep:</strong> ${data.service_representative ? data.service_representative : 'Unassigned'}`;
            document.getElementById('modalContent').innerHTML = content;
            document.getElementById('inquiryModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('inquiryModal').classList.add('hidden');
        }
    </script>
</body>
</html>

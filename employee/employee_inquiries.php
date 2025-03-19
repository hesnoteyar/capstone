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
  </style>
</head>
<body class="bg-base-200">

    <div class="min-h-screen flex flex-col">
        <div class="flex-grow">
            <div class="container mx-auto p-4">
                <h1 class="text-2xl font-bold mb-4">Service Inquiries</h1>
                
                <?php if (isset($error_message)): ?>
                <div id="errorBanner" class="alert alert-error banner">
                    <span><?php echo $error_message; ?></span>
                </div>
                <script>
                    document.getElementById('errorBanner').style.display = 'block';
                    setTimeout(() => document.getElementById('errorBanner').style.display = 'none', 5000);
                </script>
                <?php endif; ?>
                
                <div class="mb-4">
                    <label class="mr-2">Filter by Status:</label>
                    <select id="statusFilter" class="select select-bordered">
                        <option value="all">All</option>
                        <option value="Pending">Pending</option>
                        <option value="Claimed">Claimed</option>
                    </select>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Reference #</th>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>Year</th>
                                <th>Service Type</th>
                                <th>Description</th>
                                <th>Contact</th>
                                <th>Preferred Date</th>
                                <th>Status</th>
                                <th>Service Rep</th>
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
                                <td><?php echo $row['year_model']; ?></td>
                                <td><?php echo $row['service_type']; ?></td>
                                <td><?php echo $row['description']; ?></td>
                                <td><?php echo $row['contact_number']; ?></td>
                                <td><?php echo $row['preferred_date']; ?></td>
                                <td>
                                    <span class="badge <?php echo $row['status'] == 'Pending' ? 'badge-warning' : 'badge-success'; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $row['service_representative'] ? $row['service_representative'] : 'Unassigned'; ?></td>
                                <td>
                                    <?php if($row['status'] == 'Pending' && empty($row['service_representative'])): ?>
                                    <form method="POST" action="claim_inquiry.php">
                                        <input type="hidden" name="inquiry_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">Claim</button>
                                    </form>
                                    <?php else: ?>
                                    <button class="btn btn-disabled btn-sm">Claimed</button>
                                    <?php endif; ?>
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

    <script>
        document.getElementById('statusFilter').addEventListener('change', function() {
            const selectedStatus = this.value.toLowerCase();
            document.querySelectorAll('.inquiry-row').forEach(row => {
                row.style.display = (selectedStatus === 'all' || row.dataset.status.toLowerCase() === selectedStatus) ? '' : 'none';
            });
        });
    </script>

</body>
</html>
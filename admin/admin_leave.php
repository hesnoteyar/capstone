<?php
// filepath: /d:/XAMPP/htdocs/capstone/admin/admin_leave.php
include '..\admin\adminnavbar.php';
include '../authentication/db.php'; // Include your database connection

// Get the filter status from the query string, default to 'All'
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'All';

// Check for success or error messages in the session
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.13/dist/full.min.css" rel="stylesheet" type="text/css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex: 1;
        }
        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>

    <title>Admin Leave Requests</title>
</head>
<body class="bg-base-200">
    <main class="container mx-auto p-6">
        <h2 class="text-2xl font-bold mb-4">Leave Requests</h2>

        <!-- Success and Error Messages -->
        <?php if ($success_message): ?>
            <div class="alert alert-success shadow-lg mb-4">
                <div>
                    <span><?php echo $success_message; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error shadow-lg mb-4">
                <div>
                    <span><?php echo $error_message; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filter Buttons -->
        <div class="mb-4">
            <a href="?status=All" class="btn <?= $filter_status == 'All' ? 'btn-primary' : 'btn-outline' ?>">All</a>
            <a href="?status=Pending" class="btn <?= $filter_status == 'Pending' ? 'btn-warning' : 'btn-outline' ?>">Pending</a>
            <a href="?status=Approved" class="btn <?= $filter_status == 'Approved' ? 'btn-success' : 'btn-outline' ?>">Approved</a>
            <a href="?status=Not Approved" class="btn <?= $filter_status == 'Not Approved' ? 'btn-error' : 'btn-outline' ?>">Denied</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            // Fetch leave requests based on the filter status
            $sql = "SELECT lr.*, e.leaves AS total_leaves, e.profile_picture FROM leave_request lr JOIN employee e ON lr.employee_id = e.employee_id";
            if ($filter_status != 'All') {
                $sql .= " WHERE lr.approval_status = ?";
            }
            $sql .= " ORDER BY lr.leave_start_date DESC";
            $stmt = $conn->prepare($sql);
            if ($filter_status != 'All') {
                $stmt->bind_param("s", $filter_status);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $id = $row['id'];
                    $employee_name = htmlspecialchars($row['employee_name']);
                    $leave_type = htmlspecialchars($row['leave_type']);
                    $leave_reason = htmlspecialchars($row['leave_reason']);
                    $leave_start_date = htmlspecialchars($row['leave_start_date']);
                    $leave_end_date = htmlspecialchars($row['leave_end_date']);
                    $approval_status = htmlspecialchars($row['approval_status']);
                    $total_leaves = htmlspecialchars($row['total_leaves']);
                    $profile_picture = !empty($row['profile_picture']) ? 'data:image/jpeg;base64,' . base64_encode($row['profile_picture']) : 'media/defaultpfp.jpg';
                    ?>
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body">
                            <div class="flex items-center mb-4">
                                <div class="avatar">
                                    <div class="mask mask-squircle w-12 h-12">
                                        <img src="<?= $profile_picture ?>" alt="<?= $employee_name ?>" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="card-title"><?= $employee_name ?></h3>
                                    <p><strong>Total Leaves:</strong> <?= $total_leaves ?></p>
                                </div>
                            </div>
                            <p><strong>Type of Leave:</strong> <?= $leave_type ?></p>
                            <p><strong>Reason:</strong> <?= $leave_reason ?></p>
                            <p><strong>Start:</strong> <?= $leave_start_date ?></p>
                            <p><strong>End:</strong> <?= $leave_end_date ?></p>
                            <p><strong>Status:</strong> <?= $approval_status ?></p>
                            <?php if ($approval_status == 'Pending'): ?>
                                <div class="card-actions justify-end">
                                    <button class="btn btn-success" onclick="updateLeaveStatus(<?= $id ?>, 'Approved')">Approve</button>
                                    <button class="btn btn-error" onclick="updateLeaveStatus(<?= $id ?>, 'Not Approved')">Deny</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No leave requests found.</p>";
            }
            $stmt->close();
            ?>
        </div>
    </main>

    <script>
        function updateLeaveStatus(id, status) {
            fetch('update_leave_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id, status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Set success message in session and reload the page
                    sessionStorage.setItem('success_message', 'Leave request ' + status.toLowerCase() + ' successfully.');
                    location.reload();
                } else {
                    // Set error message in session and reload the page
                    sessionStorage.setItem('error_message', 'Error updating leave status: ' + data.message);
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Set error message in session and reload the page
                sessionStorage.setItem('error_message', 'An error occurred while updating leave status.');
                location.reload();
            });
        }

        // Display messages from sessionStorage
        document.addEventListener('DOMContentLoaded', () => {
            const successMessage = sessionStorage.getItem('success_message');
            const errorMessage = sessionStorage.getItem('error_message');

            if (successMessage) {
                const successBanner = document.createElement('div');
                successBanner.className = 'alert alert-success shadow-lg mb-4';
                successBanner.innerHTML = `<div><span>${successMessage}</span></div>`;
                document.querySelector('main').insertBefore(successBanner, document.querySelector('.mb-4'));
                sessionStorage.removeItem('success_message');
            }

            if (errorMessage) {
                const errorBanner = document.createElement('div');
                errorBanner.className = 'alert alert-error shadow-lg mb-4';
                errorBanner.innerHTML = `<div><span>${errorMessage}</span></div>`;
                document.querySelector('main').insertBefore(errorBanner, document.querySelector('.mb-4'));
                sessionStorage.removeItem('error_message');
            }
        });
    </script>
</body>

<?php
include '..\admin\admin_footer.php';
?>
</html>
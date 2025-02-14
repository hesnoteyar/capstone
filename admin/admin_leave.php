<?php
include '..\admin\adminnavbar.php';
include '../authentication/db.php'; // Include your database connection
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            // Fetch all leave requests from the database
            $sql = "SELECT * FROM leave_request ORDER BY leave_start_date DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $id = $row['id'];
                    $employee_name = htmlspecialchars($row['employee_name']);
                    $leave_type = htmlspecialchars($row['leave_type']);
                    $leave_reason = htmlspecialchars($row['leave_reason']);
                    $leave_start_date = htmlspecialchars($row['leave_start_date']);
                    $leave_end_date = htmlspecialchars($row['leave_end_date']);
                    $leave_start_time = date("g:i A", strtotime($row['leave_start_time']));
                    $leave_end_time = date("g:i A", strtotime($row['leave_end_time']));
                    $approval_status = htmlspecialchars($row['approval_status']);
                    ?>
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body">
                            <h3 class="card-title"><?= $employee_name ?></h3>
                            <p><strong>Type of Leave:</strong> <?= $leave_type ?></p>
                            <p><strong>Reason:</strong> <?= $leave_reason ?></p>
                            <p><strong>Start:</strong> <?= $leave_start_date ?> <?= $leave_start_time ?></p>
                            <p><strong>End:</strong> <?= $leave_end_date ?> <?= $leave_end_time ?></p>
                            <p><strong>Status:</strong> <?= $approval_status ?></p>
                            <div class="card-actions justify-end">
                                <button class="btn btn-success" onclick="updateLeaveStatus(<?= $id ?>, 'Approved')">Approve</button>
                                <button class="btn btn-error" onclick="updateLeaveStatus(<?= $id ?>, 'Not Approved')">Deny</button>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No leave requests found.</p>";
            }
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
                    location.reload(); // Reload the page to reflect changes
                } else {
                    alert('Error updating leave status: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating leave status.');
            });
        }
    </script>
</body>

<?php
include '..\admin\admin_footer.php';
?>
</html>
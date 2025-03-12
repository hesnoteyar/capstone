<?php
// filepath: /d:/XAMPP/htdocs/capstone/admin/admin_schedule.php
include '..\admin\adminnavbar.php';
include '../authentication/db.php'; // Include your database connection

// Get the filter status from the query string, default to 'All'
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'All';
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
        .container {
            flex: 1;
        }
        footer {
            background-color: #f8f9fa;
            padding: 1rem;
            text-align: center;
        }
    </style>

    <title>Schedule Requests</title>
</head>
<body class="bg-base-200">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Schedule Requests</h1>
        
        <!-- Replace dropdown with filter buttons -->
        <div class="mb-4">
            <a href="?status=All" class="btn <?= $filter_status == 'All' ? 'btn-primary' : 'btn-outline' ?>">All</a>
            <a href="?status=Pending" class="btn <?= $filter_status == 'Pending' ? 'btn-warning' : 'btn-outline' ?>">Pending</a>
            <a href="?status=Approved" class="btn <?= $filter_status == 'Approved' ? 'btn-success' : 'btn-outline' ?>">Approved</a>
            <a href="?status=Rejected" class="btn <?= $filter_status == 'Rejected' ? 'btn-error' : 'btn-outline' ?>">Denied</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            // Modify query to use filter
            $sql = "SELECT * FROM schedule_requests";
            if ($filter_status != 'All') {
                $sql .= " WHERE status = ?";
            }
            $sql .= " ORDER BY requested_date DESC";
            
            $stmt = $conn->prepare($sql);
            if ($filter_status != 'All') {
                $stmt->bind_param("s", $filter_status);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $statusClass = '';
                    switch($row['status']) {
                        case 'Approved':
                            $statusClass = 'badge-success';
                            break;
                        case 'Rejected':
                            $statusClass = 'badge-error';
                            break;
                        case 'Pending':
                            $statusClass = 'badge-warning';
                            break;
                    }
                    
                    echo "<div class='card bg-base-100 shadow-xl' data-status='" . htmlspecialchars($row['status']) . "'>";
                    echo "<div class='card-body'>";
                    echo "<h2 class='card-title'>" . htmlspecialchars($row['employee_name']) . "</h2>";
                    echo "<p>Requested Date: " . htmlspecialchars($row['requested_date']) . "</p>";
                    echo "<p>Start Time: " . htmlspecialchars($row['start_time']) . "</p>";
                    echo "<p>End Time: " . htmlspecialchars($row['end_time']) . "</p>";
                    echo "<p>Notes: " . htmlspecialchars($row['notes']) . "</p>";
                    echo "<p>Status: <span class='badge " . $statusClass . "'>" . htmlspecialchars($row['status']) . "</span></p>";
                    echo "<div class='card-actions mt-4'>";
                    if ($row['status'] === 'Pending') {
                        echo "<button class='btn btn-success' onclick='updateStatus(" . $row['request_id'] . ", \"Approved\")'>Approve</button>";
                        echo "<button class='btn btn-error' onclick='updateStatus(" . $row['request_id'] . ", \"Rejected\")'>Deny</button>";
                    }
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p>No schedule requests found.</p>";
            }

            $stmt->close();
            $conn->close();
            ?>
        </div>
    </div>

    <script>
        function updateStatus(requestId, status) {
            fetch('update_schedule_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `request_id=${requestId}&status=${status}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the UI immediately
                    const card = document.querySelector(`[data-status="${data.previous_status}"]`);
                    if (card) {
                        const statusBadge = card.querySelector('.badge');
                        statusBadge.className = `badge badge-${status === 'Approved' ? 'success' : 'error'}`;
                        statusBadge.textContent = status;
                        card.setAttribute('data-status', status);
                        // Remove the action buttons
                        const actionButtons = card.querySelector('.card-actions');
                        actionButtons.innerHTML = '';
                    }
                    showBanner('success', 'Status updated successfully.');
                } else {
                    showBanner('error', 'Error updating status: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error updating status:', error);
                showBanner('error', 'Error updating status');
            });
        }

        function showBanner(type, message) {
            const banner = document.createElement('div');
            banner.className = `alert alert-${type} shadow-lg fixed top-5 left-5 p-4 text-md z-50`;
            banner.style.width = '50%'; // Set width to 50%
            banner.style.maxWidth = '600px'; // Optional: set a max width for larger screens

            const bannerContent = document.createElement('div');
            bannerContent.classList.add('flex', 'items-center');

            const icon = document.createElement('span');
            icon.classList.add('material-icons', 'mr-2');
            icon.textContent = type === 'error' ? 'error' : '';

            const text = document.createElement('span');
            text.textContent = message;

            bannerContent.appendChild(icon);
            bannerContent.appendChild(text);
            banner.appendChild(bannerContent);

            document.body.appendChild(banner);

            setTimeout(() => {
                banner.remove();
            }, 5000); // Remove after 5 seconds
        }
    </script>
    <?php
    include '..\admin\admin_footer.php';
    ?>
</body>
</html>
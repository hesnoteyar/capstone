<?php
session_start();
include '..\admin\adminnavbar.php';
include '..\authentication\db.php'; // Include your database connection

// Set the default sorting order
$orderBy = isset($_GET['sort']) && in_array($_GET['sort'], ['action', 'audit_id', 'user_id', 'activity_date']) ? $_GET['sort'] : 'activity_date';
$orderDirection = isset($_GET['direction']) && $_GET['direction'] === 'asc' ? 'ASC' : 'DESC';

// Fetch audit logs from the database with sorting
$auditQuery = "SELECT audit_id, user_id, action, item, activity_date FROM audit_logs ORDER BY $orderBy $orderDirection";
$auditLogs = $conn->query($auditQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.13/dist/full.min.css" rel="stylesheet" type="text/css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .dropdown-content {
            z-index: 50; /* Ensure dropdown is above other elements */
        }
    </style>
</head>
<body class="bg-base-100 min-h-screen flex flex-col">
    <div class="container mx-auto mt-10 flex-grow">
        <h1 class="text-2xl font-bold text-center mb-6">Admin Logs - Ecommerce</h1>

        <!-- Sorting Dropdown -->
        <div class="flex justify-end mb-4">
            <div class="dropdown relative"> <!-- Add relative positioning -->
                <label tabindex="0" class="btn btn-secondary btn-sm">Sort By</label>
                <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52 absolute mt-1"> <!-- Use absolute positioning -->
                    <li><a href="?sort=audit_id&direction=asc">Audit ID (Ascending)</a></li>
                    <li><a href="?sort=audit_id&direction=desc">Audit ID (Descending)</a></li>
                    <li><a href="?sort=user_id&direction=asc">User ID (Ascending)</a></li>
                    <li><a href="?sort=user_id&direction=desc">User ID (Descending)</a></li>
                    <li><a href="?sort=activity_date&direction=asc">Date (Oldest First)</a></li>
                    <li><a href="?sort=activity_date&direction=desc">Date (Newest First)</a></li>
                    <li><a href="?sort=action&direction=asc">Action (A-Z)</a></li>
                    <li><a href="?sort=action&direction=desc">Action (Z-A)</a></li>
                </ul>
            </div>
        </div>

        <!-- Audit Logs Table -->
        <div class="overflow-x-auto">
            <?php if ($auditLogs->num_rows > 0): ?>
                <table class="table w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-200 text-gray-800">
                            <th class="border border-gray-300 p-2">Audit ID</th>
                            <th class="border border-gray-300 p-2">User ID</th>
                            <th class="border border-gray-300 p-2">Action</th>
                            <th class="border border-gray-300 p-2">Item</th>
                            <th class="border border-gray-300 p-2">Activity Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $auditLogs->fetch_assoc()): ?>
                            <tr>
                                <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($row['audit_id']); ?></td>
                                <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($row['user_id']); ?></td>
                                <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($row['action']); ?></td>
                                <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($row['item']); ?></td>
                                <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($row['activity_date']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center text-gray-600">No audit logs found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer Section -->
    <?php include '..\admin\admin_footer.php'; ?>
</body>

</html>

<?php
$conn->close(); // Close the database connection
?>

<?php
    session_start(); // Start the session to access session variables
    include 'adminnavbar.php';
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

    <Title>Purchases</Title>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body>
    <div class="p-8">
        <h1 class="text-3xl font-bold mb-6">Customer Purchases</h1>
        
        <!-- Search and Filter Section -->
        <div class="flex gap-4 mb-6">
            <input type="text" placeholder="Search orders..." class="input input-bordered w-full max-w-xs" />
            <select class="select select-bordered w-full max-w-xs">
                <option disabled selected>Filter by Status</option>
                <option>Completed</option>
                <option>Pending</option>
                <option>Cancelled</option>
            </select>
        </div>

        <!-- Purchases Table -->
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#ORD-001</td>
                        <td>John Doe</td>
                        <td>2 items</td>
                        <td>₱1,500.00</td>
                        <td>2024-01-15</td>
                        <td><span class="badge badge-success">Completed</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary">View Details</button>
                        </td>
                    </tr>
                    <tr>
                        <td>#ORD-002</td>
                        <td>Jane Smith</td>
                        <td>1 item</td>
                        <td>₱750.00</td>
                        <td>2024-01-14</td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary">View Details</button>
                        </td>
                    </tr>
                    <tr>
                        <td>#ORD-003</td>
                        <td>Mike Johnson</td>
                        <td>3 items</td>
                        <td>₱2,250.00</td>
                        <td>2024-01-13</td>
                        <td><span class="badge badge-error">Cancelled</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary">View Details</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="join mt-4 flex justify-center">
            <button class="join-item btn">1</button>
            <button class="join-item btn btn-active">2</button>
            <button class="join-item btn">3</button>
            <button class="join-item btn">4</button>
        </div>
    </div>
</body>
<?php
    include 'admin_footer.php';
?>
</html>

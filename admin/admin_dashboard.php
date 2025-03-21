<?php
// filepath: /d:/XAMPP/htdocs/capstone/admin/admin_dashboard.php
session_start();
include '../admin/adminnavbar.php';
include '../authentication/db.php'; // Database connection

// Initialize arrays for monthly sales data
$monthly_sales_data = [];
$product_names = [];
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Fetch distinct product IDs and names
$sql_product_ids = "SELECT DISTINCT product_id, product_name FROM purchase_history";
$result_product_ids = $conn->query($sql_product_ids);

if ($result_product_ids->num_rows > 0) {
    while ($row = $result_product_ids->fetch_assoc()) {
        $product_id = $row['product_id'];
        $product_names[$product_id] = $row['product_name'];
        $monthly_sales_data[$product_id] = array_fill(0, 12, 0); // 12 months initialized to 0
    }
}

// Fetch monthly sales data for each product
foreach ($product_names as $product_id => $product_name) {
    $sql_monthly_sales = "SELECT MONTH(purchase_date) AS month, SUM(price * quantity) AS total_sales 
                          FROM purchase_history 
                          WHERE YEAR(purchase_date) = YEAR(CURDATE()) AND product_id = $product_id
                          GROUP BY month 
                          ORDER BY month";
    $result_monthly_sales = $conn->query($sql_monthly_sales);

    if ($result_monthly_sales->num_rows > 0) {
        while ($row = $result_monthly_sales->fetch_assoc()) {
            $monthly_sales_data[$product_id][$row['month'] - 1] = (float)$row['total_sales']; // Adjust index for zero-based array
        }
    }
}

// Initialize arrays for attendance data
$attendance_data = [];
$employee_names = [];

// Fetch distinct employee IDs and names
$sql_employee_ids = "SELECT DISTINCT e.employee_id, CONCAT(e.firstName, ' ', e.middleName, ' ', e.lastName) AS employee_name 
                     FROM attendance a 
                     JOIN employee e ON a.employee_id = e.employee_id";
$result_employee_ids = $conn->query($sql_employee_ids);

if ($result_employee_ids->num_rows > 0) {
    while ($row = $result_employee_ids->fetch_assoc()) {
        $employee_id = $row['employee_id'];
        $employee_names[$employee_id] = $row['employee_name'];
        $attendance_data[$employee_id] = array_fill(0, 12, 0); // 12 months initialized to 0
    }
}

// Fetch attendance data for each employee
foreach ($employee_names as $employee_id => $employee_name) {
    $sql_attendance = "SELECT MONTH(date) AS month, SUM(total_hours + overtime_hours) AS total_hours 
                       FROM attendance 
                       WHERE YEAR(date) = YEAR(CURDATE()) AND employee_id = $employee_id
                       GROUP BY month 
                       ORDER BY month";
    $result_attendance = $conn->query($sql_attendance);

    if ($result_attendance->num_rows > 0) {
        while ($row = $result_attendance->fetch_assoc()) {
            $attendance_data[$employee_id][$row['month'] - 1] = (float)$row['total_hours']; // Adjust index for zero-based array
        }
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            .btn.btn-error {
                background-color: #dc2626;
                color: white;
            }
        }
    </style>
</head>
<body>
    
    <!-- Flex container for charts -->
    <div class="flex justify-between space-x-1 px-4">
        <!-- Card for Employee Attendance Chart -->
        <div class="card w-full shadow-lg px-4">
            <div class="card-body">
                <h2 class="card-title">Employee Attendance</h2>
                <div id="attendanceChart" class="w-full"></div>
            </div>
        </div>

        <!-- Card for Monthly Sales Chart -->
        <div class="card w-full shadow-lg px-4">
            <div class="card-body">
                <h2 class="card-title">Monthly Sales</h2>
                <div id="monthlySalesChart" class="w-full"></div>
            </div>
        </div>
    </div>

    <!-- Card for the table -->
    <div class="card w-full shadow-lg mt-8 mx-auto px-4">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table">
                    <!-- Table Head -->
                    <thead>
                        <tr>
                            <th><label><input type="checkbox" class="checkbox" /></label></th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Address</th>
                            <th>City</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            // Fetch employees from the database
                            $sql = "SELECT employee_id, firstName, middleName, lastName, role, address, city, postalCode, profile_picture, email, date_hired 
                                    FROM employee";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                // Loop through each employee
                                while ($row = $result->fetch_assoc()) {
                                    $fullName = htmlspecialchars($row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName']);
                                    $role = htmlspecialchars($row['role']);
                                    $address = htmlspecialchars($row['address'] . ', ' . $row['postalCode']);
                                    $city = htmlspecialchars($row['city']);
                                    $email = htmlspecialchars($row['email']);
                                    $dateHired = htmlspecialchars($row['date_hired']);
                                    $profilePicture = !empty($row['profile_picture']) 
                                        ? 'data:image/jpeg;base64,' . base64_encode($row['profile_picture']) 
                                        : 'media\defaultpfp.jpg';

                                    echo "
                                    <tr>
                                        <th><label><input type='checkbox' class='checkbox' /></label></th>
                                        <td><div class='flex items-center gap-3'>
                                            <div class='avatar'>
                                                <div class='mask mask-squircle h-12 w-12'>
                                                    <img src='{$profilePicture}' alt='{$fullName}' />
                                                </div>
                                            </div>
                                            <div><div class='font-bold'>{$fullName}</div><div class='text-sm opacity-50'>{$role}</div></div></td>
                                        <td>{$role}</td>
                                        <td>{$address}</td>
                                        <td>{$city}</td>
                                        <th><button class='btn btn-error btn-xs' onclick='openModal(\"{$fullName}\", \"{$role}\", \"{$address}\", \"{$city}\", \"{$profilePicture}\", \"{$email}\", \"{$dateHired}\")'>Details</button></th></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>No employees found.</td></tr>";
                            }

                            $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <input type="checkbox" id="detailsModal" class="modal-toggle" />
    <div class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg" id="modalFullName"></h3>
            <div class="flex items-center mb-4">
                <img id="modalProfilePicture" src="" alt="Profile Picture" class="w-24 h-24 rounded-full mr-4">
                <div>
                    <p class="py-2 mt-2"><strong>Role:</strong> <span id="modalRole"></span></p>
                    <p class="py-2"><strong>Address:</strong> <span id="modalAddress"></span></p>
                    <p class="py-2"><strong>City:</strong> <span id="modalCity"></span></p>
                    <p class="py-2"><strong>Email:</strong> <span id="modalEmail"></span></p>
                    <p class="py-2"><strong>Date Hired:</strong> <span id="modalDateHired"></span></p>
                </div>
            </div>
            <div class="modal-action">
                <label for="detailsModal" class="btn btn-error">Close</label>
            </div>
        </div>
    </div>

    <script>
        function openModal(fullName, role, address, city, profilePicture, email, dateHired) {
            document.getElementById('modalFullName').innerText = fullName;
            document.getElementById('modalRole').innerText = role;
            document.getElementById('modalAddress').innerText = address;
            document.getElementById('modalCity').innerText = city;
            document.getElementById('modalProfilePicture').src = profilePicture;
            document.getElementById('modalEmail').innerText = email;
            document.getElementById('modalDateHired').innerText = new Date(dateHired).toLocaleDateString();
            document.getElementById('detailsModal').checked = true;
        }

        // Attendance Chart Options
        var attendanceOptions = {
            series: [
                <?php foreach ($attendance_data as $employee_id => $hours_data): ?>
                {
                    name: '<?php echo addslashes($employee_names[$employee_id]); ?>',
                    data: <?php echo json_encode($hours_data); ?>
                },
                <?php endforeach; ?>
            ],
            chart: { height: 400, type: 'line', zoom: { enabled: true } },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            title: { text: 'Employee Attendance (Hours)', align: 'left' },
            grid: { row: { colors: ['#f3f3f3', 'transparent'], opacity: 0.5 } },
            xaxis: { categories: <?php echo json_encode($months); ?> },
            yaxis: {
                min: 0,
                max: 300,
                labels: {
                    formatter: function(val) {
                        return val + " hours";
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " hours";
                    }
                }
            }
        };

        // Render Attendance Chart
        var attendanceChart = new ApexCharts(document.querySelector("#attendanceChart"), attendanceOptions);
        attendanceChart.render();

        // Monthly Sales Chart Options
        var monthlySalesOptions = {
            series: [
                <?php foreach ($monthly_sales_data as $product_id => $sales_data): ?>
                {
                    name: '<?php echo addslashes($product_names[$product_id]); ?>',
                    data: <?php echo json_encode($sales_data); ?>
                },
                <?php endforeach; ?>
            ],
            chart: { height: 400, type: 'line', zoom: { enabled: true } },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            title: { text: 'Monthly Sales (₱)', align: 'left' },
            grid: { row: { colors: ['#f3f3f3', 'transparent'], opacity: 0.5 } },
            xaxis: { categories: <?php echo json_encode($months); ?> },
            yaxis: {
                min: 0,
                max: 50000,
                labels: {
                    formatter: function(val) {
                        return "₱" + val.toLocaleString();
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return "₱" + val.toLocaleString();
                    }
                }
            }
        };

        // Render Monthly Sales Chart
        var monthlySalesChart = new ApexCharts(document.querySelector("#monthlySalesChart"), monthlySalesOptions);
        monthlySalesChart.render();
    </script>

</body>

<?php include '../admin/admin_footer.php'; ?>
</html>
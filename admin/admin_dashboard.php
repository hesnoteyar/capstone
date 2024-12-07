<?php
session_start();
include '..\admin\adminnavbar.php';
include '../authentication/db.php'; // Database connection

// Initialize an array for new employees count per month
$new_employees_data = array_fill(0, 12, 0); // 12 months initialized to 0
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Fetch new employees count from the database
$sql_new_employees = "SELECT MONTH(date_hired) AS month, COUNT(*) AS count 
                      FROM employee 
                      WHERE YEAR(date_hired) = YEAR(CURDATE()) 
                      GROUP BY month 
                      ORDER BY month";
$result_new_employees = $conn->query($sql_new_employees);

if ($result_new_employees->num_rows > 0) {
    while ($row = $result_new_employees->fetch_assoc()) {
        $new_employees_data[$row['month'] - 1] = (int)$row['count']; // Adjust index for zero-based array
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
        }
    </style>
</head>
<body>
    
    <!-- Flex container for charts -->
    <div class="flex justify-between space-x-1 px-4">
        <!-- Card for Attendance Chart -->
        <div class="card w-full shadow-lg px-4">
            <div class="card-body">
                <h2 class="card-title">Employee Attendance</h2>
                <div id="attendanceChart" class="w-full"></div>
            </div>
        </div>

        <!-- Card for New Employees Chart -->
        <div class="card w-full shadow-lg px-4">
            <div class="card-body">
                <h2 class="card-title">New Employees</h2>
                <div id="newEmployeesChart" class="w-full"></div>
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
                            $sql = "SELECT employee_id, firstName, middleName, lastName, role, address, city, postalCode, profile_picture 
                                    FROM employee";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                // Loop through each employee
                                while ($row = $result->fetch_assoc()) {
                                    $fullName = htmlspecialchars($row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName']);
                                    $role = htmlspecialchars($row['role']);
                                    $address = htmlspecialchars($row['address'] . ', ' . $row['postalCode']);
                                    $city = htmlspecialchars($row['city']);
                                    $profilePicture = htmlspecialchars($row['profile_picture'] ?: '../media/defaultpfp.jpg');

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
                                        <th><button class='btn btn-error btn-xs'>Details</button></th></tr>";
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

    <br>

    <script>
        // Attendance Chart Options
        var attendanceOptions = {
            series: [{ name: 'Attendance', data: [10, 41, 35, 51, 49, 62, 69, 91, 148] }],
            chart: { height: 400, type: 'line', zoom: { enabled: true } },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2, colors: ['#dc2626'] },
            title: { text: 'Employee Attendance', align: 'left' },
            grid: { row: { colors: ['#f3f3f3', 'transparent'], opacity: 0.5 } },
            xaxis: { categories:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] }
        };

        // Render Attendance Chart
        var attendanceChart = new ApexCharts(document.querySelector("#attendanceChart"), attendanceOptions);
        attendanceChart.render();

        // New Employees Chart Options
        var newEmployeesOptions = {
            series:[{ name:'New Employees', data : <?php echo json_encode($new_employees_data); ?> }],
            chart:{ height :400 , type:'line' , zoom :{ enabled :true }},
            dataLabels:{ enabled :false },
            stroke:{ curve:'smooth' , width :2 , colors:['#3b82f6']},
            title:{ text:'New Employees' , align:'left'},
            grid:{ row:{ colors:['#f3f3f3' ,'transparent'], opacity :0.5}},
            xaxis:{ categories : <?php echo json_encode($months); ?> },
            yaxis:{
                min: 0,
                max: 50,
            }
        };

        // Render New Employees Chart
        var newEmployeesChart = new ApexCharts(document.querySelector("#newEmployeesChart"), newEmployeesOptions);
        newEmployeesChart.render();
    </script>

</body>

<?php include '..\admin\admin_footer.php'; ?>
</html>
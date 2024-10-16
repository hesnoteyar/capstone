<?php
    include '..\admin\adminnavbar.php';
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
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>

</head>
<body>
    
    <!-- Flex container for charts -->
    <div class="flex justify-between space-x-1 px-4"> <!-- Adjusted space-x-1 for closer cards -->

        <!-- Card for Attendance Chart -->
        <div class="card w-full shadow-lg px-4">
            <div class="card-body">
                <h2 class="card-title">Employee Attendance</h2>
                <!-- Chart container -->
                <div id="attendanceChart" class="w-full"></div>
            </div>
        </div>

        <!-- Card for New Employees Chart -->
        <div class="card w-full shadow-lg px-4">
            <div class="card-body">
                <h2 class="card-title">New Employees</h2>
                <!-- Chart container -->
                <div id="newEmployeesChart" class="w-full"></div>
            </div>
        </div>

    </div>

    <script>
        // Chart options for Attendance
        var attendanceOptions = {
            series: [{
                name: 'Attendance',
                data: [10, 41, 35, 51, 49, 62, 69, 91, 148]
            }],
            chart: {
                height: 400,  // Adjust the height of the chart
                type: 'line',
                zoom: {
                    enabled: true
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2,  // Set line width to 2
                colors: ['#dc2626'] // Setting the line color to 'red-600'
            },
            title: {
                text: 'Employee Attendance',
                align: 'left'
            },
            grid: {
                row: {
                    colors: ['#f3f3f3', 'transparent'], // alternating background colors
                    opacity: 0.5
                },
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
            }
        };

        // Render attendance chart
        var attendanceChart = new ApexCharts(document.querySelector("#attendanceChart"), attendanceOptions);
        attendanceChart.render();

        // Chart options for New Employees
        var newEmployeesOptions = {
            series: [{
                name: 'New Employees',
                data: [5, 25, 20, 30, 40, 50, 60, 80, 100]
            }],
            chart: {
                height: 400,  // Adjust the height of the chart
                type: 'line',
                zoom: {
                    enabled: true
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2,  // Set line width to 2
                colors: ['#3b82f6'] // Example color for new employees
            },
            title: {
                text: 'New Employees',
                align: 'left'
            },
            grid: {
                row: {
                    colors: ['#f3f3f3', 'transparent'], // alternating background colors
                    opacity: 0.5
                },
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
            }
        };

        // Render new employees chart
        var newEmployeesChart = new ApexCharts(document.querySelector("#newEmployeesChart"), newEmployeesOptions);
        newEmployeesChart.render();
    </script>

    <!-- Card for the table -->
    <div class="card w-full shadow-lg mt-8 mx-auto px-4"> <!-- Added card structure -->
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table">
                    <!-- head -->
                    <thead>
                    <tr>
                        <th>
                        <label>
                            <input type="checkbox" class="checkbox" />
                        </label>
                        </th>
                        <th>Name</th>
                        <th>Job</th>
                        <th>Favorite Color</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- row 1 -->
                    <tr>
                        <th>
                        <label>
                            <input type="checkbox" class="checkbox" />
                        </label>
                        </th>
                        <td>
                        <div class="flex items-center gap-3">
                            <div class="avatar">
                            <div class="mask mask-squircle h-12 w-12">
                                <img
                                src="https://img.daisyui.com/images/profile/demo/2@94.webp"
                                alt="Avatar Tailwind CSS Component" />
                            </div>
                            </div>
                            <div>
                            <div class="font-bold">Hart Hagerty</div>
                            <div class="text-sm opacity-50">United States</div>
                            </div>
                        </div>
                        </td>
                        <td>
                        Zemlak, Daniel and Leannon
                        <br />
                        <span class="badge badge-ghost badge-sm">Desktop Support Technician</span>
                        </td>
                        <td>Purple</td>
                        <th>
                        <button class="btn btn-ghost btn-xs">details</button>
                        </th>
                    </tr>
                    <!-- row 2 -->
                    <tr>
                        <th>
                        <label>
                            <input type="checkbox" class="checkbox" />
                        </label>
                        </th>
                        <td>
                        <div class="flex items-center gap-3">
                            <div class="avatar">
                            <div class="mask mask-squircle h-12 w-12">
                                <img
                                src="https://img.daisyui.com/images/profile/demo/3@94.webp"
                                alt="Avatar Tailwind CSS Component" />
                            </div>
                            </div>
                            <div>
                            <div class="font-bold">Brice Swyre</div>
                            <div class="text-sm opacity-50">China</div>
                            </div>
                        </div>
                        </td>
                        <td>
                        Carroll Group
                        <br />
                        <span class="badge badge-ghost badge-sm">Tax Accountant</span>
                        </td>
                        <td>Red</td>
                        <th>
                        <button class="btn btn-ghost btn-xs">details</button>
                        </th>
                    </tr>
                    <!-- row 3 -->
                    <tr>
                        <th>
                        <label>
                            <input type="checkbox" class="checkbox" />
                        </label>
                        </th>
                        <td>
                        <div class="flex items-center gap-3">
                            <div class="avatar">
                            <div class="mask mask-squircle h-12 w-12">
                                <img
                                src="https://img.daisyui.com/images/profile/demo/4@94.webp"
                                alt="Avatar Tailwind CSS Component" />
                            </div>
                            </div>
                            <div>
                            <div class="font-bold">Marjy Ferencz</div>
                            <div class="text-sm opacity-50">Russia</div>
                            </div>
                        </div>
                        </td>
                        <td>
                        Rowe-Schoen
                        <br />
                        <span class="badge badge-ghost badge-sm">Office Assistant I</span>
                        </td>
                        <td>Crimson</td>
                        <th>
                        <button class="btn btn-ghost btn-xs">details</button>
                        </th>
                    </tr>
                    <!-- row 4 -->
                    <tr>
                        <th>
                        <label>
                            <input type="checkbox" class="checkbox" />
                        </label>
                        </th>
                        <td>
                        <div class="flex items-center gap-3">
                            <div class="avatar">
                            <div class="mask mask-squircle h-12 w-12">
                                <img
                                src="https://img.daisyui.com/images/profile/demo/5@94.webp"
                                alt="Avatar Tailwind CSS Component" />
                            </div>
                            </div>
                            <div>
                            <div class="font-bold">Yancy Tear</div>
                            <div class="text-sm opacity-50">Brazil</div>
                            </div>
                        </div>
                        </td>
                        <td>
                        Wyman-Ledner
                        <br />
                        <span class="badge badge-ghost badge-sm">Community Outreach Specialist</span>
                        </td>
                        <td>Indigo</td>
                        <th>
                        <button class="btn btn-ghost btn-xs">details</button>
                        </th>
                    </tr>
                    </tbody>
                    <!-- foot -->
                </table>
            </div>
        </div>
    </div>

    <br>

</body>

<?php
    include '..\admin\admin_footer.php';
?>
</html>

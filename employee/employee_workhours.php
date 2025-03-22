<?php
print_r($_SESSION);
    session_start();

    include 'employee_topnavbar.php';
    include '../authentication/db.php'; 

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
    <div class="container mx-auto p-6">
        <!-- Header -->
        <div class="text-2xl font-bold mb-6">Work Hours Overview</div>
        
        <!-- Chart Card -->
        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <h2 class="card-title">Monthly Attendance</h2>
                <div id="attendanceChart"></div>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-title">Total Hours</div>
                    <div class="stat-value">160</div>
                    <div class="stat-desc">This Month</div>
                </div>
            </div>

            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-title">Present Days</div>
                    <div class="stat-value">20</div>
                    <div class="stat-desc">Out of 22 Working Days</div>
                </div>
            </div>

            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-title">Overtime Hours</div>
                    <div class="stat-value">8</div>
                    <div class="stat-desc">This Month</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sample data - Replace with actual data from database
        var options = {
            series: [{
                name: 'Work Hours',
                data: [7.5, 8, 8, 7, 8, 8, 8.5, 7.5, 8, 8]
            }],
            chart: {
                height: 350,
                type: 'area',
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            xaxis: {
                categories: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10']
            },
            title: {
                text: 'Daily Work Hours',
                align: 'left'
            }
        };

        var chart = new ApexCharts(document.querySelector("#attendanceChart"), options);
        chart.render();
    </script>
</body>

<?php
    include 'employee_footer.php';
?>
</html>

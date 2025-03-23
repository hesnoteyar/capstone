<?php
    session_start();
    error_reporting(E_ALL);
ini_set('display_errors', 1);
    include 'employee_topnavbar.php';
    include '../authentication/db.php';

    // Get employee ID from session and current month
    $employee_id = $_SESSION['id'];
    $current_month = date('Y-m');

    // Initialize arrays for all days of the month
    $days_in_month = date('t');
    $days = range(1, $days_in_month);
    $work_hours = array_fill(0, $days_in_month, 0);
    $overtime_hours = array_fill(0, $days_in_month, 0);

    // Get attendance data for chart
    $chart_query = "SELECT SUBSTRING(date, 9, 2) as day, 
                           total_hours,
                           overtime_hours
                    FROM attendance 
                    WHERE employee_id = ? 
                    AND SUBSTRING(date, 1, 7) = ?
                    ORDER BY date ASC";
    $stmt = $conn->prepare($chart_query);
    $stmt->bind_param("is", $employee_id, $current_month);
    $stmt->execute();
    $chart_result = $stmt->get_result();

    // Fill in actual attendance data
    while($row = $chart_result->fetch_assoc()) {
        $day_index = intval($row['day']) - 1; // Convert day to 0-based index
        $work_hours[$day_index] = floatval($row['total_hours']);
        $overtime_hours[$day_index] = floatval($row['overtime_hours']);
    }

    // Get monthly summary
    $summary_query = "SELECT 
                        SUM(total_hours) as total_hours,
                        COUNT(*) as present_days,
                        SUM(overtime_hours) as total_overtime
                    FROM attendance 
                    WHERE employee_id = ? 
                    AND SUBSTRING(date, 1, 7) = ?";
    $stmt = $conn->prepare($summary_query);
    $stmt->bind_param("is", $employee_id, $current_month);
    $stmt->execute();
    $summary = $stmt->get_result()->fetch_assoc();

    // Get working days in current month
    $total_working_days = date('t'); // Gets number of days in current month
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
                    <div class="stat-value"><?php echo number_format($summary['total_hours'] ?? 0, 1); ?></div>
                    <div class="stat-desc">This Month</div>
                </div>
            </div>

            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-title">Present Days</div>
                    <div class="stat-value"><?php echo $summary['present_days'] ?? 0; ?></div>
                    <div class="stat-desc">Out of <?php echo $total_working_days; ?> Days</div>
                </div>
            </div>

            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-title">Overtime Hours</div>
                    <div class="stat-value"><?php echo number_format($summary['total_overtime'] ?? 0, 1); ?></div>
                    <div class="stat-desc">This Month</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var options = {
            series: [{
                name: 'Work Hours',
                data: <?php echo json_encode($work_hours); ?>,
                color: '#3b82f6' // blue
            }, {
                name: 'Overtime Hours',
                data: <?php echo json_encode($overtime_hours); ?>,
                color: '#ef4444' // red
            }],
            chart: {
                height: 350,
                type: 'area',
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val > 0 ? val.toFixed(1) : '';
                }
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            fill: {
                type: 'gradient',
                gradient: {
                    opacityFrom: 0.6,
                    opacityTo: 0.1
                }
            },
            xaxis: {
                categories: <?php echo json_encode($days); ?>,
                title: {
                    text: 'Day of Month'
                }
            },
            yaxis: {
                title: {
                    text: 'Hours'
                },
                min: 0,
                max: 12
            },
            title: {
                text: 'Daily Work Hours for <?php echo date("F Y"); ?>',
                align: 'center'
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function (val) {
                        return val.toFixed(1) + " hours"
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
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

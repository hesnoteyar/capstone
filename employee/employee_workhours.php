<?php
    session_start();
    error_reporting(E_ALL);
ini_set('display_errors', 1);
    include 'employee_topnavbar.php';
    include '../authentication/db.php';

    // Get employee ID from session
    $employee_id = $_SESSION['id'];
    $current_month = date('Y-m');

    // Get attendance data for chart
    $chart_query = "SELECT DATE_FORMAT(date, '%d') as day, 
                           total_hours,
                           overtime_hours,
                           TIME_FORMAT(check_in_time, '%H:%i') as check_in,
                           TIME_FORMAT(check_out_time, '%H:%i') as check_out
                    FROM attendance 
                    WHERE employee_id = ? 
                    AND DATE_FORMAT(date, '%Y-%m') COLLATE utf8mb4_general_ci = ? COLLATE utf8mb4_general_ci
                    ORDER BY date ASC";
    $stmt = $conn->prepare($chart_query);
    $stmt->bind_param("is", $employee_id, $current_month);
    $stmt->execute();
    $chart_result = $stmt->get_result();

    // Get number of days in current month
    $days_in_month = date('t');
    
    // Initialize arrays with zeros for all days
    $days = range(1, $days_in_month);
    $hours = array_fill(0, $days_in_month, 0);
    $overtime = array_fill(0, $days_in_month, 0);

    // Fill in actual attendance data
    while($row = $chart_result->fetch_assoc()) {
        $day_index = intval($row['day']) - 1;  // Convert to 0-based index
        $hours[$day_index] = floatval($row['total_hours']);
        $overtime[$day_index] = floatval($row['overtime_hours']);
    }

    // Get monthly summary
    $summary_query = "SELECT 
                        SUM(total_hours) as total_hours,
                        COUNT(*) as present_days,
                        SUM(overtime_hours) as total_overtime
                    FROM attendance 
                    WHERE employee_id = ? 
                    AND DATE_FORMAT(date, '%Y-%m') COLLATE utf8mb4_general_ci = ? COLLATE utf8mb4_general_ci";
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

    <style>    <style>
        body {y {
            font-family: 'Poppins', sans-serif;nt-family: 'Poppins', sans-serif;
        }
    </style>le>

</head></head>
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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-title">Total Hours</div>
                    <div class="stat-value"><?php echo number_format($summary['total_hours'] ?? 0, 1); ?></div>
                    <div class="stat-desc">This Month</div>
                </div>
            </div>

            <div class="stats shadow">
                <div class="stat">
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
        
        <!-- Action Buttons -->
        <div class="flex justify-end mt-4 gap-2">

            <a href="download_attendance.php" class="btn btn-error">
                Download Attendance Report
            </a>
        </div>
    </div>

    <script>
        var options = {
            series: [{
                name: 'Regular Hours',
                data: <?php echo json_encode(array_map(function($h, $o) {
                    return ['Regular Hours', $h];
                }, $hours, $overtime)); ?>
            },
            {
                name: 'Overtime Hours',
                data: <?php echo json_encode(array_map(function($h, $o) {
                    return ['Overtime Hours', $o];
                }, $hours, $overtime)); ?>
            }],
            chart: {
                height: 350,
                type: 'heatmap',
                toolbar: {
                    show: true,
                    tools: {
                        download: true
                    }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return Math.round(val) + 'h';
                },
                style: {
                    fontSize: '12px',
                    colors: ["#000000"]
                }
            },
            colors: ["#008FFB"],
            plotOptions: {
                heatmap: {
                    colorScale: {
                        ranges: [{
                            from: 0,
                            to: 0,
                            color: '#EEEEEE',
                            name: 'No Hours'
                        },
                        {
                            from: 0.1,
                            to: 4,
                            color: '#3b82f6',
                            name: 'Low Hours'
                        },
                        {
                            from: 4.1,
                            to: 8,
                            color: '#2563eb',
                            name: 'Regular Hours'
                        },
                        {
                            from: 8.1,
                            to: 12,
                            color: '#ef4444',
                            name: 'High Hours'
                        }]
                    }
                }
            },
            xaxis: {
                categories: <?php echo json_encode($days); ?>,
                title: {
                    text: 'Days of Month',
                    style: {
                        fontSize: '14px',
                        fontWeight: 600
                    }
                },
                position: 'top'
            },
            yaxis: {
                title: {
                    text: 'Work Type',
                    style: {
                        fontSize: '14px',
                        fontWeight: 600
                    }
                }
            },
            grid: {
                padding: {
                    right: 20
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return Math.round(val) + ' hours'
                    }
                }
            },
            title: {
                text: 'Daily Work Hours for <?php echo date("F Y"); ?>',
                align: 'left',
                style: {
                    fontSize: '16px',
                    fontWeight: 600
                }
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

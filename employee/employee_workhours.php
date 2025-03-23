<?php
    session_start();
    error_reporting(E_ALL);
ini_set('display_errors', 1);
    include 'employee_topnavbar.php';
    include '../authentication/db.php';

    // Get employee ID from session
    $employee_id = $_SESSION['id'];
    $current_month = date('Y-m');

    // Initialize arrays for all days
    $days_in_month = date('t');
    $days = range(1, $days_in_month);
    $hours = array_fill(0, $days_in_month, 0);

    // Get attendance data for chart
    $chart_query = "SELECT DAY(date) as day, 
                           total_hours
                    FROM attendance 
                    WHERE employee_id = ? 
                    AND DATE_FORMAT(date, '%Y-%m') COLLATE utf8mb4_general_ci = ? COLLATE utf8mb4_general_ci
                    ORDER BY date ASC";
    $stmt = $conn->prepare($chart_query);
    $stmt->bind_param("is", $employee_id, $current_month);
    $stmt->execute();
    $chart_result = $stmt->get_result();

    // Fill in actual attendance data
    while($row = $chart_result->fetch_assoc()) {
        $day_index = intval($row['day']) - 1;
        $hours[$day_index] = floatval($row['total_hours']);
    }

    // Get detailed attendance records for table
    $attendance_query = "SELECT 
        DAY(calendar.date) as day,
        DATE_FORMAT(calendar.date, '%W') as weekday,
        COALESCE(TIME_FORMAT(a.check_in_time, '%h:%i %p'), '-') as check_in,
        COALESCE(TIME_FORMAT(a.check_out_time, '%h:%i %p'), '-') as check_out,
        COALESCE(a.total_hours, 0) as total_hours,
        COALESCE(a.overtime_hours, 0) as overtime_hours
    FROM (
        SELECT DATE('$current_month-01') + INTERVAL (a.a) DAY as date
        FROM (
            SELECT @rownum:=@rownum+1-1 a
            FROM (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6) t1,
                 (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6) t2,
                 (SELECT @rownum:=-1) r
            LIMIT $days_in_month
        ) a
    ) calendar
    LEFT JOIN attendance a ON DATE(a.date) = calendar.date AND a.employee_id = ?
    WHERE calendar.date <= LAST_DAY('$current_month-01')
    ORDER BY calendar.date";

    $stmt = $conn->prepare($attendance_query);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $attendance_records = $stmt->get_result();

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

        <!-- Attendance Records -->
        <div class="card bg-base-100 shadow-xl mt-6">
            <div class="card-body">
                <h2 class="card-title text-xl mb-4"><?php echo date('F Y'); ?> Attendance Record</h2>
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr class="bg-base-200">
                                <th>Day</th>
                                <th>Weekday</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Total Hours</th>
                                <th>Overtime</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($record = $attendance_records->fetch_assoc()) { ?>
                                <tr class="<?php echo in_array($record['weekday'], ['Saturday', 'Sunday']) ? 'text-gray-400' : ''; ?>">
                                    <td><?php echo $record['day']; ?></td>
                                    <td><?php echo $record['weekday']; ?></td>
                                    <td><?php echo $record['check_in']; ?></td>
                                    <td><?php echo $record['check_out']; ?></td>
                                    <td><?php echo $record['total_hours'] > 0 ? number_format($record['total_hours'], 1) : '-'; ?></td>
                                    <td><?php echo $record['overtime_hours'] > 0 ? number_format($record['overtime_hours'], 1) : '-'; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        var options = {
            series: [{
                name: 'Work Hours',
                data: <?php echo json_encode($hours); ?>
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
                align: 'left'
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val.toFixed(1) + " hours"
                    }
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

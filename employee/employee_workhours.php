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
    <div class="container mx-auto p-6">iv class="container mx-auto p-6">
        <!-- Header -->
        <div class="text-2xl font-bold mb-6">Work Hours Overview</div>t-2xl font-bold mb-6">Work Hours Overview</div>
        
        <!-- Table Card --><!-- Chart Card -->
        <div class="card bg-base-100 shadow-xl mb-6 overflow-x-auto">-base-100 shadow-xl mb-6">
            <div class="card-body">
                <h2 class="card-title mb-4">Monthly Attendance - <?php echo date("F Y"); ?></h2>le">Monthly Attendance</h2>
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Regular Hours</th>        <!-- Attendance Summary -->
                            <th>Overtime Hours</th>1 md:grid-cols-3 gap-4 mb-4">
                            <th>Total Hours</th>
                        </tr>
                    </thead>at-title">Total Hours</div>
                    <tbody>_format($summary['total_hours'] ?? 0, 1); ?></div>
                        <?php for($i = 0; $i < count($days); $i++): ?>
                            <tr class="hover">
                                <td class="font-medium"><?php echo $days[$i]; ?></td>
                                <td><?php echo $hours[$i] ? round($hours[$i]) : '-'; ?></td>
                                <td><?php echo $overtime[$i] ? round($overtime[$i]) : '-'; ?></td>            <div class="stats shadow">
                                <td class="font-medium">
                                    <div class="stat-value"><?php echo $summary['present_days'] ?? 0; ?></div>
                                        $total = $hours[$i] + $overtime[$i];>
                                        echo $total ? round($total) : '-';
                                    ?>
                                </td>
                            </tr>            <div class="stats shadow">
                        <?php endfor; ?>
                    </tbody>at-title">Overtime Hours</div>
                    <tfoot>rmat($summary['total_overtime'] ?? 0, 1); ?></div>
                        <tr class="font-bold">
                            <td>Monthly Total</td>
                            <td><?php echo round($summary['total_hours'] - $summary['total_overtime']); ?></td>
                            <td><?php echo round($summary['total_overtime']); ?></td>
                            <td><?php echo round($summary['total_hours']); ?></td>
                        </tr><!-- Action Buttons -->
                    </tfoot>y-end mt-4 gap-2">
                </table>
            </div>            <a href="download_attendance.php" class="btn btn-error">
        </div>

        <!-- Attendance Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="stats shadow">
                <div class="stat">    <script>
                    <div class="stat-title">Total Hours</div>options = {
                    <div class="stat-value"><?php echo number_format($summary['total_hours'] ?? 0, 1); ?></div>
                    <div class="stat-desc">This Month</div>'Regular Hours',
                </div>encode(array_map(function($h, $o) {
            </div>

            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-value"><?php echo $summary['present_days'] ?? 0; ?></div>   name: 'Overtime Hours',
                    <div class="stat-desc">Out of <?php echo $total_working_days; ?> Days</div>ncode(array_map(function($h, $o) {
                </div>
            </div>

            <div class="stats shadow">rt: {
                <div class="stat">ht: 350,
                    <div class="stat-title">Overtime Hours</div>ap',
                    <div class="stat-value"><?php echo number_format($summary['total_overtime'] ?? 0, 1); ?></div>
                    <div class="stat-desc">This Month</div>true,
                </div>
            </div>load: true
        </div>
        
        <!-- Action Buttons -->
        <div class="flex justify-end mt-4 gap-2">taLabels: {
true,
            <a href="download_attendance.php" class="btn btn-error">ction(val) {
                Download Attendance Report + 'h';
            </a>
        </div>yle: {
    </div>Size: '12px',
</body>"]

<?php
    include 'employee_footer.php';
    // JavaScript code should not be here. Move it to a proper <script> block if needed.
?>
</html>

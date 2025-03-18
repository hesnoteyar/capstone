<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'employee_topnavbar.php';
include '../authentication/db.php'; 
$employee_id = $_SESSION['id']; 

// Fetch employee details
$employee_query = "SELECT firstName, middleName, lastName, role, address, city, profile_picture FROM employee WHERE employee_id = ?";
$employee_stmt = $conn->prepare($employee_query);
$employee_stmt->bind_param("i", $employee_id);
$employee_stmt->execute();
$employee_stmt->bind_result($firstName, $middleName, $lastName, $role, $address, $city, $profileImageBlob);
$employee_stmt->fetch();
$employee_stmt->close();

// Encode the binary data as a base64 string
if ($profileImageBlob) {
    $profileImage = 'data:image/jpeg;base64,' . base64_encode($profileImageBlob);
} else {
    $profileImage = "../media/default_profile.png";
}

$hourly_rate = 100;
$overtime_rate = 150;

// Fetch total hours worked and overtime hours in the current month from the attendance table
$current_month = date("Y-m");
$total_hours_query = "SELECT SUM(total_hours) AS total_hours, SUM(overtime_hours) AS overtime_hours 
                      FROM attendance 
                      WHERE employee_id = ? 
                      AND DATE_FORMAT(date, '%Y-%m') COLLATE utf8mb4_general_ci = ? COLLATE utf8mb4_general_ci";

$total_hours_stmt = $conn->prepare($total_hours_query);
$total_hours_stmt->bind_param("is", $employee_id, $current_month);
$total_hours_stmt->execute();
$total_hours_stmt->bind_result($total_hours, $overtime_hours);
$total_hours_stmt->fetch();
$total_hours_stmt->close();

// Ensure total_hours and overtime_hours are not null
$total_hours = $total_hours ?? 0;
$overtime_hours = $overtime_hours ?? 0;

// Calculate daily wage, total salary, and overtime pay for the month
$daily_wage = $hourly_rate * 8;
$monthly_salary = $hourly_rate * $total_hours;
$overtime_pay = $overtime_rate * $overtime_hours;

// Calculate SSS deduction
$sss_deduction = min($monthly_salary * 0.045, 1125);

// Calculate PhilHealth deduction
$philhealth_contribution = $monthly_salary * 0.05;
$philhealth_deduction = min(max($philhealth_contribution / 2, 400), 2500);

// Calculate Pag-IBIG deduction
$pagibig_rate = ($monthly_salary <= 1500) ? 0.01 : 0.02;
$pagibig_deduction = min($monthly_salary * $pagibig_rate, 200);

// Calculate total deductions
$total_deductions = $sss_deduction + $philhealth_deduction + $pagibig_deduction;

// Check if payroll record already exists for the current month
$payroll_check_query = "SELECT COUNT(*) FROM payroll 
                        WHERE employee_id = ? 
                        AND DATE_FORMAT(payroll_date, '%Y-%m') COLLATE utf8mb4_general_ci = ? COLLATE utf8mb4_general_ci";
$payroll_check_stmt = $conn->prepare($payroll_check_query);
$payroll_check_stmt->bind_param("is", $employee_id, $current_month);
$payroll_check_stmt->execute();
$payroll_check_stmt->bind_result($payroll_count);
$payroll_check_stmt->fetch();
$payroll_check_stmt->close();

if ($payroll_count == 0) {
    // Update net salary calculation with deductions
    $net_salary = $monthly_salary + $overtime_pay - $total_deductions;
    
    $payroll_date = date('Y-m-d');
    $insert_payroll_query = "INSERT INTO payroll (employee_id, payroll_date, salary, overtime_pay, deductions, net_salary, sss_deduction, philhealth_deduction, pagibig_deduction) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $insert_payroll_stmt = $conn->prepare($insert_payroll_query);
    // Changed bind_param types to match the actual parameter types
    $insert_payroll_stmt->bind_param("isddddddd", $employee_id, $payroll_date, $monthly_salary, $overtime_pay, $total_deductions, $net_salary, $sss_deduction, $philhealth_deduction, $pagibig_deduction);
    $insert_payroll_stmt->execute();
    $insert_payroll_stmt->close();
} else {
    // Update net salary calculation with deductions
    $net_salary = $monthly_salary + $overtime_pay - $total_deductions;
    
    $update_payroll_query = "UPDATE payroll 
    SET salary = ?, overtime_pay = ?, deductions = ?, net_salary = ?, 
        sss_deduction = ?, philhealth_deduction = ?, pagibig_deduction = ? 
    WHERE employee_id = ? 
    AND DATE_FORMAT(payroll_date, '%Y-%m') COLLATE utf8mb4_general_ci = ? COLLATE utf8mb4_general_ci";

    $update_payroll_stmt = $conn->prepare($update_payroll_query);
    // Changed bind_param types to match the actual parameter types
    $update_payroll_stmt->bind_param("dddddddis", $monthly_salary, $overtime_pay, $total_deductions, $net_salary, $sss_deduction, $philhealth_deduction, $pagibig_deduction, $employee_id, $current_month);
    $update_payroll_stmt->execute();
    $update_payroll_stmt->close();

    
}

// Fetch payroll record for the current month
$query = "SELECT * FROM payroll 
          WHERE employee_id = ? 
          AND DATE_FORMAT(payroll_date, '%Y-%m') COLLATE utf8mb4_general_ci = ? COLLATE utf8mb4_general_ci";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $employee_id, $current_month);
$stmt->execute();
$result = $stmt->get_result();
$payroll = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.13/dist/full.min.css" rel="stylesheet" type="text/css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <title>Employee Payroll</title>
  <style>
    body { font-family: 'Poppins', sans-serif; }
  </style>
</head>
<body class="bg-base-200 min-h-screen">
  <div class="container mx-auto p-6">
    <!-- Employee Information Card -->
    <div class="card lg:card-side bg-base-100 shadow-xl mb-8 hover:shadow-2xl transition-shadow duration-300">
      <figure class="p-6">
        <img src="<?php echo $profileImage; ?>" alt="Profile Picture" class="rounded-full w-32 h-32 object-cover shadow-lg border-4 border-white ml-auto mr-4"/>
      </figure>
      <div class="card-body">
      <h2 class="card-title text-2xl text-error"><?= htmlspecialchars("$firstName $middleName $lastName") ?></h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="stats bg-error text-secondary-content">
        <div class="stat">
          <div class="stat-title">Role</div>
          <div class="stat-value text-lg"><?= htmlspecialchars($role) ?></div>
        </div>
        </div>
        <div class="stats bg-error text-secondary-content">
        <div class="stat">
          <div class="stat-title">Location</div>
          <div class="stat-value text-lg"><?= htmlspecialchars("$address, $city") ?></div>
        </div>
        </div>
      </div>
      </div>
    </div>

    <!-- Payroll Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="stats shadow">
        <div class="stat">
          <div class="stat-title">Total Hours</div>
          <div class="stat-value"><?= number_format($total_hours, 1) ?></div>
          <div class="stat-desc">Hours Worked This Month</div>
        </div>
      </div>
      <div class="stats shadow">
        <div class="stat">
          <div class="stat-title">Overtime Hours</div>
          <div class="stat-value"><?= number_format($overtime_hours, 1) ?></div>
          <div class="stat-desc">Extra Hours This Month</div>
        </div>
      </div>
      <div class="stats shadow">
        <div class="stat">
          <div class="stat-title">Net Salary</div>
          <div class="stat-value text-primary">₱<?= number_format($net_salary, 2) ?></div>
          <div class="stat-desc">Current Month</div>
        </div>
      </div>
    </div>

    <!-- Payroll Information Card -->
    <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow duration-300">
      <div class="card-body">
        <h2 class="card-title text-2xl mb-6">Payroll Details - <?= date('F Y', strtotime($current_month)) ?></h2>
        <?php if ($payroll): ?>
          <div class="overflow-x-auto">
            <div class="collapse collapse-plus bg-base-200 mb-4">
              <input type="checkbox" /> 
              <div class="collapse-title text-xl font-medium">
                Earnings Breakdown
              </div>
              <div class="collapse-content"> 
                <table class="table table-zebra w-full">
                  <tbody>
                    <tr>
                      <td class="font-bold">Hourly Rate:</td>
                      <td>₱<?= number_format($hourly_rate, 2) ?></td>
                    </tr>
                    <tr>
                      <td class="font-bold">Overtime Rate:</td>
                      <td>₱<?= number_format($overtime_rate, 2) ?></td>
                    </tr>
                    <tr>
                      <td class="font-bold">Base Salary:</td>
                      <td>₱<?= number_format($payroll['salary'], 2) ?></td>
                    </tr>
                    <tr>
                      <td class="font-bold">Overtime Pay:</td>
                      <td>₱<?= number_format($payroll['overtime_pay'], 2) ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="collapse collapse-plus bg-base-200 mb-4">
              <input type="checkbox" /> 
              <div class="collapse-title text-xl font-medium">
                Deductions
              </div>
              <div class="collapse-content"> 
                <table class="table table-zebra w-full">
                  <tbody>
                    <tr>
                      <td class="font-bold">SSS:</td>
                      <td class="text-error">-₱<?= number_format($payroll['sss_deduction'], 2) ?></td>
                    </tr>
                    <tr>
                      <td class="font-bold">PhilHealth:</td>
                      <td class="text-error">-₱<?= number_format($payroll['philhealth_deduction'], 2) ?></td>
                    </tr>
                    <tr>
                      <td class="font-bold">Pag-IBIG:</td>
                      <td class="text-error">-₱<?= number_format($payroll['pagibig_deduction'], 2) ?></td>
                    </tr>
                    <tr>
                      <td class="font-bold">Total Deductions:</td>
                      <td class="text-error">-₱<?= number_format($payroll['deductions'], 2) ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="divider"></div>
            
            <div class="stats stats-vertical lg:stats-horizontal shadow w-full">
              <div class="stat">
                <div class="stat-title">Gross Salary</div>
                <div class="stat-value text-success">₱<?= number_format($payroll['salary'] + $payroll['overtime_pay'], 2) ?></div>
                <div class="stat-desc">Before Deductions</div>
              </div>
              <div class="stat">
                <div class="stat-title">Deductions</div>
                <div class="stat-value text-error">₱<?= number_format($payroll['deductions'], 2) ?></div>
                <div class="stat-desc">Total Deductions</div>
              </div>
              <div class="stat">
                <div class="stat-title">Net Salary</div>
                <div class="stat-value text-primary">₱<?= number_format($payroll['net_salary'], 2) ?></div>
                <div class="stat-desc">Final Take-home Pay</div>
              </div>
            </div>
            
            <!-- Add this button after the stats div -->
            <div class="flex justify-center mt-6">
              <a href="generate_payslip.php" class="btn btn-error">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Download Payslip
              </a>
            </div>
          </div>
        <?php else: ?>
          <div class="alert alert-info">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>No payroll records found for the current month.</span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php include 'employee_footer.php'; ?>
</body>
</html>
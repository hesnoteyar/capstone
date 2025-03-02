<?php
session_start();

include '../employee/employee_topnavbar.php';
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

$hourly_rate = 78;
$overtime_rate = 97;

// Fetch total hours worked and overtime hours in the current month from the attendance table
$current_month = date('Y-m');
$total_hours_query = "SELECT SUM(total_hours) AS total_hours, SUM(overtime_hours) AS overtime_hours FROM attendance WHERE employee_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?";
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

// Check if payroll record already exists for the current month
$payroll_check_query = "SELECT COUNT(*) FROM payroll WHERE employee_id = ? AND DATE_FORMAT(payroll_date, '%Y-%m') = ?";
$payroll_check_stmt = $conn->prepare($payroll_check_query);
$payroll_check_stmt->bind_param("is", $employee_id, $current_month);
$payroll_check_stmt->execute();
$payroll_check_stmt->bind_result($payroll_count);
$payroll_check_stmt->fetch();
$payroll_check_stmt->close();

if ($payroll_count == 0) {
    // Insert payroll record since none exists
    $payroll_date = date('Y-m-d');
    $net_salary = $monthly_salary + $overtime_pay; // Calculate net salary
    $insert_payroll_query = "INSERT INTO payroll (employee_id, payroll_date, salary, overtime_pay, deductions, net_salary, status) VALUES (?, ?, ?, ?, 0, ?, 'Pending')";
    $insert_payroll_stmt = $conn->prepare($insert_payroll_query);
    $insert_payroll_stmt->bind_param("isddd", $employee_id, $payroll_date, $monthly_salary, $overtime_pay, $net_salary);
    $insert_payroll_stmt->execute();
    $insert_payroll_stmt->close();
} else {
    // Update existing payroll record
    $net_salary = $monthly_salary + $overtime_pay; // Recalculate net salary
    $update_payroll_query = "UPDATE payroll SET salary = ?, overtime_pay = ?, net_salary = ? WHERE employee_id = ? AND DATE_FORMAT(payroll_date, '%Y-%m') = ?";
    $update_payroll_stmt = $conn->prepare($update_payroll_query);
    $update_payroll_stmt->bind_param("dddis", $monthly_salary, $overtime_pay, $net_salary, $employee_id, $current_month);
    $update_payroll_stmt->execute();
    $update_payroll_stmt->close();
}

// Fetch payroll record for the current month
$query = "SELECT * FROM payroll WHERE employee_id = ? AND DATE_FORMAT(payroll_date, '%Y-%m') = ?";
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
<body class="bg-base-200">
  <div class="container mx-auto p-6">
    <!-- Employee Information Card -->
    <div class="card lg:card-side bg-base-100 shadow-xl mb-8">
      <figure><img src="<?php echo $profileImage; ?>" alt="Profile Picture" class="rounded-lg w-48"/></figure>
      <div class="card-body">
        <h2 class="card-title"><?= htmlspecialchars("$firstName $middleName $lastName") ?></h2>
        <p><span class="font-bold">Role:</span> <?= htmlspecialchars($role) ?></p>
        <p><span class="font-bold">Address:</span> <?= htmlspecialchars($address) ?></p>
        <p><span class="font-bold">City:</span> <?= htmlspecialchars($city) ?></p>
      </div>
    </div>
    <!-- Payroll Information Card -->
    <div class="card bg-base-100 shadow-xl">
      <div class="card-body">
        <h2 class="card-title mb-4">Payroll Information</h2>
        <?php if ($payroll): ?>
          <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
              <tbody>
                <tr>
                  <td class="font-bold">Payroll Date:</td>
                  <td><?= date('F j, Y', strtotime($payroll['payroll_date'])) ?></td>
                </tr>
                <tr>
                  <td class="font-bold">Status:</td>
                  <td><?= htmlspecialchars($payroll['status']) ?></td>
                </tr>
                <tr>
                  <td class="font-bold">Total Hours Worked:</td>
                  <td><?= number_format($total_hours, 1) ?> hours</td>
                </tr>
                <tr>
                  <td class="font-bold">Overtime Hours:</td>
                  <td><?= number_format($overtime_hours, 1) ?> hours</td>
                </tr>
                <tr>
                  <td class="font-bold">Hourly Rate:</td>
                  <td>₱<?= number_format($hourly_rate, 2) ?></td>
                </tr>
                <tr>
                  <td class="font-bold">Overtime Rate:</td>
                  <td>₱<?= number_format($overtime_rate, 2) ?></td>
                </tr>
                <tr>
                  <td class="font-bold">Salary:</td>
                  <td>₱<?= number_format($payroll['salary'], 2) ?></td>
                </tr>
                <tr>
                  <td class="font-bold">Overtime Pay:</td>
                  <td>₱<?= number_format($payroll['overtime_pay'], 2) ?></td>
                </tr>
                <tr>
                  <td class="font-bold">Deductions:</td>
                  <td>₱<?= number_format($payroll['deductions'], 2) ?></td>
                </tr>
                <tr>
                  <td class="font-bold text-2xl">Net Salary:</td>
                  <td class="text-2xl">₱<?= number_format($payroll['net_salary'], 2) ?></td>
                </tr>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="text-center">No payroll records found.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php include '../employee/employee_footer.php'; ?>
</body>
</html>
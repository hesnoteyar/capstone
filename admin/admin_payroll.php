<?php
// filepath: /d:/XAMPP/htdocs/capstone/admin/admin_payroll.php
session_start();
include '../admin/adminnavbar.php';
include '../authentication/db.php';

// Fetch payroll data from the database
$sql = "SELECT p.*, e.firstName, e.middleName, e.lastName, e.profile_picture FROM payroll p JOIN employee e ON p.employee_id = e.employee_id";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Payroll Approvals - DaisyUI</title>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.13/dist/full.min.css" rel="stylesheet" type="text/css" />
</head>
<body class="min-h-screen bg-base-200 flex flex-col">
  <div class="container mx-auto p-6 flex-grow">
    <h1 class="text-4xl font-bold mb-8 text-center">Payslip Approvals</h1>
    <div class="mb-4 flex justify-center gap-4">
      <button class="btn btn-primary" onclick="window.location.href='?compute=1'">Compute Payroll</button>
      <button class="btn btn-secondary" onclick="window.location.href='admin_payroll.php'">Refresh</button>
    </div>
    <!-- DaisyUI table component with widened container -->
    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 w-full">
      <table class="table w-full">
        <thead>
          <tr>
            <th>Payroll ID</th>
            <th>Employee ID</th>
            <th>Payroll Date</th>
            <th>Salary</th>
            <th>Overtime Pay</th>
            <th>Net Salary</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['payroll_id']); ?></td>
                <td><?php echo htmlspecialchars($row['employee_id']); ?></td>
                <td><?php echo htmlspecialchars($row['payroll_date']); ?></td>
                <td><?php echo htmlspecialchars($row['salary']); ?></td>
                <td><?php echo htmlspecialchars($row['overtime_pay']); ?></td>
                <td><?php echo htmlspecialchars($row['net_salary']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td class="flex gap-2">
                  <button class="btn btn-sm btn-success" onclick="approvePayroll(<?php echo $row['payroll_id']; ?>)">Approve</button>
                  <button class="btn btn-sm btn-info" onclick="viewPayrollDetails('<?php echo htmlspecialchars(json_encode($row)); ?>')">View</button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center">No payroll records found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal -->
  <input type="checkbox" id="payrollModal" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <h3 class="font-bold text-lg" id="modalPayrollID"></h3>
      <div class="flex items-center mb-4">
        <div class="avatar">
          <div class="mask mask-squircle w-12 h-12">
            <img id="modalProfilePicture" src="" alt="Profile Picture" />
          </div>
        </div>
        <div class="ml-4">
          <p><strong>Employee Name:</strong> <span id="modalEmployeeName"></span></p>
        </div>
      </div>
      <p><strong>Employee ID:</strong> <span id="modalEmployeeID"></span></p>
      <p><strong>Payroll Date:</strong> <span id="modalPayrollDate"></span></p>
      <p><strong>Salary:</strong> <span id="modalSalary"></span></p>
      <p><strong>Overtime Pay:</strong> <span id="modalOvertimePay"></span></p>
      <p><strong>Net Salary:</strong> <span id="modalNetSalary"></span></p>
      <p><strong>Status:</strong> <span id="modalStatus"></span></p>
      <div class="modal-action">
        <label for="payrollModal" class="btn">Close</label>
      </div>
    </div>
  </div>

  <footer class="mt-auto">
    <?php include '../admin/admin_footer.php'; ?>
  </footer>

  <script>
    function approvePayroll(payrollID) {
      // Implement the logic to approve the payroll
      alert('Approve Payroll ID: ' + payrollID);
    }

    function viewPayrollDetails(payroll) {
      payroll = JSON.parse(payroll);
      console.log(payroll); // Debugging statement
      document.getElementById('modalPayrollID').innerText = 'Payroll ID: ' + payroll.payroll_id;
      document.getElementById('modalEmployeeID').innerText = payroll.employee_id;
      document.getElementById('modalPayrollDate').innerText = payroll.payroll_date;
      document.getElementById('modalSalary').innerText = payroll.salary;
      document.getElementById('modalOvertimePay').innerText = payroll.overtime_pay;
      document.getElementById('modalNetSalary').innerText = payroll.net_salary;
      document.getElementById('modalStatus').innerText = payroll.status;
      document.getElementById('modalEmployeeName').innerText = payroll.firstName + ' ' + payroll.middleName + ' ' + payroll.lastName;
      document.getElementById('modalProfilePicture').src = payroll.profile_picture ? 'data:image/jpeg;base64,' + btoa(String.fromCharCode.apply(null, new Uint8Array(atob(payroll.profile_picture).split("").map(function(c) { return c.charCodeAt(0); })))) : 'media/defaultpfp.jpg';
      document.getElementById('payrollModal').checked = true;
    }
  </script>
</body>
</html>
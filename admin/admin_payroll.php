<?php
session_start();
include '../admin/adminnavbar.php';
include '../authentication/db.php';

// Fetch payroll data from the database
$sql = "SELECT p.*, e.firstName, e.middleName, e.lastName, e.profile_picture FROM payroll p JOIN employee e ON p.employee_id = e.employee_id";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Payroll Approvals - DaisyUI</title>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.13/dist/full.min.css" rel="stylesheet" type="text/css" />
  <style>
    .notification-banner {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: #4caf50;
      color: white;
      padding: 16px;
      border-radius: 8px;
      z-index: 1000;
    }
    
    .modal {
      overflow-y: auto !important;
    }
    
    .modal::backdrop {
      background-color: rgba(0, 0, 0, 0.7);
    }
    
    body:has(dialog[open]) {
      overflow: hidden;
    }
    
    dialog[open] {
      max-height: 90vh;
      overflow-y: auto;
    }
  </style>
</head>
<body class="min-h-screen bg-base-200 flex flex-col">
  <div class="container mx-auto p-6 flex-grow">
    <h1 class="text-4xl font-bold mb-8 text-center">Payroll Records</h1>
    <div class="mb-4 flex justify-center gap-4">
      <button class="btn btn-primary" onclick="window.location.href='?compute=1'">Compute Payroll</button>
      <button class="btn btn-secondary" onclick="window.location.href='admin_payroll.php'">Refresh</button>
    </div>

    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 w-full mt-16">
      <table class="table w-full">  
        <thead>
          <tr>
            <th>Payroll ID</th>
            <th>Employee ID</th>
            <th>Employee Name</th>
            <th>Payroll Date</th>
            <th>Basic Salary</th>
            <th>Overtime Pay</th>
            <th>Total Deductions</th>
            <th>Net Salary</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr id="row-<?php echo $row['payroll_id']; ?>">
                <td><?php echo htmlspecialchars($row['payroll_id']); ?></td>
                <td><?php echo htmlspecialchars($row['employee_id']); ?></td>
                <td><?php echo htmlspecialchars($row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName']); ?></td>
                <td><?php echo htmlspecialchars($row['payroll_date']); ?></td>
                <td>₱<?php echo number_format($row['salary'], 2); ?></td>
                <td>₱<?php echo number_format($row['overtime_pay'], 2); ?></td>
                <td>₱<?php echo number_format($row['deductions'], 2); ?></td>
                <td>₱<?php echo number_format($row['net_salary'], 2); ?></td>
                <td>
                  <button class="btn btn-sm btn-info" onclick="viewPayrollDetails(<?php echo $row['payroll_id']; ?>)">View</button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="text-center">No payroll records found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal -->
  <dialog id="payrollModal" class="modal">
    <div class="modal-box w-11/12 max-w-2xl">
      <div class="flex justify-between items-center border-b border-gray-200 pb-4">
        <h3 class="font-bold text-2xl">Payroll Details</h3>
        <form method="dialog">
          <button class="btn btn-sm btn-circle btn-ghost">✕</button>
        </form>
      </div>

      <div class="py-6 space-y-6">
        <!-- Employee Info Section -->
        <div class="bg-base-200 p-4 rounded-lg">
          <h4 class="font-semibold text-lg mb-3">Employee Information</h4>
          <div class="grid grid-cols-3 gap-4">
            <div class="col-span-1">
              <img id="modal-profile-picture" class="w-32 h-32 object-cover rounded-full mx-auto" 
                   src="data:image/jpeg;base64,/9j/4AAQSkZJRg==" alt="Profile Picture">
            </div>
            <div class="col-span-2">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-gray-500">Payroll ID</p>
                  <p class="font-medium" id="modal-payroll-id"></p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Employee Name</p>
                  <p class="font-medium" id="modal-employee-name"></p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Salary Details Section -->
        <div class="bg-base-200 p-4 rounded-lg">
          <h4 class="font-semibold text-lg mb-3">Salary Details</h4>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500">Basic Salary</p>
              <p class="font-medium">₱<span id="modal-salary"></span></p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Overtime Pay</p>
              <p class="font-medium">₱<span id="modal-overtime"></span></p>
            </div>
          </div>
        </div>

        <!-- Deductions Section -->
        <div class="bg-base-200 p-4 rounded-lg">
          <h4 class="font-semibold text-lg mb-3">Mandatory Deductions</h4>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500">SSS Contribution (4.5%)</p>
              <p class="font-medium">₱<span id="modal-sss"></span></p>
            </div>
            <div>
              <p class="text-sm text-gray-500">PhilHealth (2.5%)</p>
              <p class="font-medium">₱<span id="modal-philhealth"></span></p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Pag-IBIG (2%)</p>
              <p class="font-medium">₱<span id="modal-pagibig"></span></p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Total Deductions</p>
              <p class="font-medium text-error">₱<span id="modal-deductions"></span></p>
            </div>
          </div>
        </div>

        <!-- Net Salary Section -->
        <div class="bg-base-200 p-4 rounded-lg">
          <h4 class="font-semibold text-lg mb-3">Net Salary</h4>
          <p class="font-medium text-xl text-success">₱<span id="modal-net-salary"></span></p>
        </div>

      </div>

      <div class="modal-action">
        <form method="dialog">
          <button class="btn btn-primary">Close</button>
        </form>
      </div>
    </div>
  </dialog>

  <!-- Notification Banner -->
  <div id="notificationBanner" class="notification-banner"></div>

  <footer class="mt-auto">
    <?php include '../admin/admin_footer.php'; ?>
  </footer>

  <script>
    function viewPayrollDetails(payrollID) {
      fetch('fetch_payroll_details.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ payroll_id: payrollID })
      })
      .then(response => response.json())
      .then(response => {
        if (response.success) {
          const data = response.data;
          document.getElementById('modal-payroll-id').textContent = data.payroll_id;
          document.getElementById('modal-employee-name').textContent = 
            `${data.emp_firstName} ${data.emp_middleName} ${data.emp_lastName}`;
          document.getElementById('modal-salary').textContent = Number(data.salary).toLocaleString('en-PH', {minimumFractionDigits: 2});
          document.getElementById('modal-overtime').textContent = Number(data.overtime_pay).toLocaleString('en-PH', {minimumFractionDigits: 2});
          document.getElementById('modal-sss').textContent = Number(data.sss_deduction).toLocaleString('en-PH', {minimumFractionDigits: 2});
          document.getElementById('modal-philhealth').textContent = Number(data.philhealth_deduction).toLocaleString('en-PH', {minimumFractionDigits: 2});
          document.getElementById('modal-pagibig').textContent = Number(data.pagibig_deduction).toLocaleString('en-PH', {minimumFractionDigits: 2});
          document.getElementById('modal-deductions').textContent = Number(data.deductions).toLocaleString('en-PH', {minimumFractionDigits: 2});
          document.getElementById('modal-net-salary').textContent = Number(data.net_salary).toLocaleString('en-PH', {minimumFractionDigits: 2});
          
          const profilePic = document.getElementById('modal-profile-picture');
          if (data.profile_picture) {
            profilePic.src = 'data:image/jpeg;base64,' + data.profile_picture;
          } else {
            profilePic.src = '../img/default-profile.jpg';
          }

          document.getElementById('payrollModal').showModal();
        }
      });
    }
  </script>
</body>
</html>

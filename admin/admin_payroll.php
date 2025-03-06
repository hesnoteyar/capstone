<?php
session_start();
include '../admin/adminnavbar.php';
include '../authentication/db.php';

// Assuming admin_id is stored in session
$admin_id = $_SESSION['id'];

// Fetch payroll data from the database
$sql = "SELECT p.*, e.firstName, e.middleName, e.lastName, e.profile_picture FROM payroll p JOIN employee e ON p.employee_id = e.employee_id";
$result = $conn->query($sql);

// Function to map status to badge class
function getStatusBadgeClass($status) {
  switch ($status) {
    case 'Approved':
      return 'success';
    case 'Pending':
      return 'warning';
    case 'Rejected':
      return 'error';
    default:
      return 'secondary';
  }
}
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
  </style>
</head>
<body class="min-h-screen bg-base-200 flex flex-col">
  <div class="container mx-auto p-6 flex-grow">
    <h1 class="text-4xl font-bold mb-8 text-center">Payslip Approvals</h1>
    <div class="mb-4 flex justify-center gap-4">
      <button class="btn btn-primary" onclick="window.location.href='?compute=1'">Compute Payroll</button>
      <button class="btn btn-secondary" onclick="window.location.href='admin_payroll.php'">Refresh</button>
    </div>
    
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
              <tr id="row-<?php echo $row['payroll_id']; ?>">
                <td><?php echo htmlspecialchars($row['payroll_id']); ?></td>
                <td><?php echo htmlspecialchars($row['employee_id']); ?></td>
                <td><?php echo htmlspecialchars($row['payroll_date']); ?></td>
                <td><?php echo htmlspecialchars($row['salary']); ?></td>
                <td><?php echo htmlspecialchars($row['overtime_pay']); ?></td>
                <td><?php echo htmlspecialchars($row['net_salary']); ?></td>
                <td id="status-<?php echo $row['payroll_id']; ?>">
                  <span class="badge badge-<?php echo getStatusBadgeClass($row['status']); ?>">
                    <?php echo htmlspecialchars($row['status']); ?>
                  </span>
                </td>
                <td class="flex gap-2">
                  <button class="btn btn-sm btn-success" onclick="approvePayroll(<?php echo $row['payroll_id']; ?>, <?php echo $admin_id; ?>)">Approve</button>
                  <button class="btn btn-sm btn-error" onclick="denyPayroll(<?php echo $row['payroll_id']; ?>, <?php echo $admin_id; ?>)">Deny</button>
                  <button class="btn btn-sm btn-info" onclick="viewPayrollDetails('<?php echo addslashes(json_encode($row)); ?>')">View</button>
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
  <input type="checkbox" id="payrollModal" class="hidden" />
  <div class="modal" id="payrollModalContainer">
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

  <!-- Hidden Label for Modal Trigger -->
  <label for="payrollModal" id="modalTrigger" class="hidden"></label>

  <!-- Notification Banner -->
  <div id="notificationBanner" class="notification-banner"></div>

  <footer class="mt-auto">
    <?php include '../admin/admin_footer.php'; ?>
  </footer>

  <script>
    function approvePayroll(payrollID, adminID) {
      fetch('approve_payroll.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ payroll_id: payrollID, admin_id: adminID })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const statusElement = document.getElementById('status-' + payrollID);
          statusElement.innerHTML = '<span class="badge badge-success">Approved</span>';
          showNotification('Payroll approved successfully', 'success');
        } else {
          showNotification('Failed to approve payroll', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('Error occurred while approving payroll', 'error');
      });
    }

    function denyPayroll(payrollID, adminID) {
      fetch('deny_payroll.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ payroll_id: payrollID, admin_id: adminID })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const statusElement = document.getElementById('status-' + payrollID);
          statusElement.innerHTML = '<span class="badge badge-error">Rejected</span>';
          showNotification('Payroll rejected successfully', 'error');
        } else {
          showNotification('Failed to deny payroll', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('Error occurred while denying payroll', 'error');
      });
    }

    function viewPayrollDetails(payroll) {
      console.log("viewPayrollDetails fired, raw payroll data:", payroll);
      try {
        payroll = JSON.parse(payroll);
        console.log("Parsed payroll data:", payroll);

        document.getElementById('modalPayrollID').innerText = 'Payroll ID: ' + payroll.payroll_id;
        document.getElementById('modalEmployeeID').innerText = payroll.employee_id;
        document.getElementById('modalPayrollDate').innerText = payroll.payroll_date;
        document.getElementById('modalSalary').innerText = payroll.salary;
        document.getElementById('modalOvertimePay').innerText = payroll.overtime_pay;
        document.getElementById('modalNetSalary').innerText = payroll.net_salary;
        document.getElementById('modalStatus').innerText = payroll.status;
        document.getElementById('modalEmployeeName').innerText = payroll.firstName + ' ' + payroll.middleName + ' ' + payroll.lastName;

        if (payroll.profile_picture) {
          console.log("Profile Picture Found");
          document.getElementById('modalProfilePicture').src = 'data:image/jpeg;base64,' + payroll.profile_picture;
        } else {
          document.getElementById('modalProfilePicture').src = 'media/defaultpfp.jpg';
        }

        // Click the hidden label to trigger modal
        document.getElementById('modalTrigger').click();
      } catch (error) {
        console.error("Error in viewPayrollDetails:", error);
      }
    }

    function showNotification(message, type) {
      const banner = document.getElementById('notificationBanner');
      banner.innerText = message;
      banner.style.backgroundColor = type === 'success' ? '#4caf50' : '#f44336';
      banner.style.display = 'block';
      setTimeout(() => {
        banner.style.display = 'none';
      }, 5000);
    }
  </script>
</body>
</html>

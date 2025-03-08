<?php
session_start();
include '../admin/adminnavbar.php';
include '../authentication/db.php';

// Assuming admin_id is stored in session
$admin_id = $_SESSION['id'];

// Get the filter status from the query string, default to 'All'
$status = isset($_GET['status']) ? $_GET['status'] : 'All';

// Fetch payroll data from the database
$sql = "SELECT p.*, e.firstName, e.middleName, e.lastName, e.profile_picture FROM payroll p JOIN employee e ON p.employee_id = e.employee_id";
if ($status != 'All') {
    $sql .= " WHERE p.status = ?";
}
$stmt = $conn->prepare($sql);
if ($status != 'All') {
    $stmt->bind_param("s", $status);
}
$stmt->execute();
$result = $stmt->get_result();

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
    <h1 class="text-4xl font-bold mb-8 text-center">Payslip Approvals</h1>
    <div class="mb-4 flex justify-center gap-4">
      <button class="btn btn-primary" onclick="window.location.href='?compute=1'">Compute Payroll</button>
      <button class="btn btn-secondary" onclick="window.location.href='admin_payroll.php'">Refresh</button>
    </div>

    <!-- New filter design -->
    <div class="mb-4">
      <a href="?status=All" class="btn <?= $status == 'All' ? 'btn-primary' : 'btn-outline' ?>">All</a>
      <a href="?status=Pending" class="btn <?= $status == 'Pending' ? 'btn-warning' : 'btn-outline' ?>">Pending</a>
      <a href="?status=Approved" class="btn <?= $status == 'Approved' ? 'btn-success' : 'btn-outline' ?>">Approved</a>
      <a href="?status=Rejected" class="btn <?= $status == 'Rejected' ? 'btn-error' : 'btn-outline' ?>">Rejected</a>
    </div>

    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 w-full mt-16">
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
                  <?php if ($row['status'] === 'Pending'): ?>
                    <button class="btn btn-sm btn-success" onclick="approvePayroll(<?php echo $row['payroll_id']; ?>)">Approve</button>
                    <button class="btn btn-sm btn-error" onclick="denyPayroll(<?php echo $row['payroll_id']; ?>)">Deny</button>
                  <?php endif; ?>
                  <button class="btn btn-sm btn-info" onclick="viewPayrollDetails(<?php echo $row['payroll_id']; ?>)">View</button>
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
              <p class="font-medium" id="modal-salary"></p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Overtime Pay</p>
              <p class="font-medium" id="modal-overtime"></p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Deductions</p>
              <p class="font-medium" id="modal-deductions"></p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Net Salary</p>
              <p class="font-medium text-lg text-success" id="modal-net-salary"></p>
            </div>
          </div>
        </div>

        <!-- Status Section -->
        <div class="bg-base-200 p-4 rounded-lg">
          <h4 class="font-semibold text-lg mb-3">Payment Status</h4>
          <div class="grid grid-cols-1 gap-4">
            <div>
              <p class="text-sm text-gray-500">Status</p>
              <p class="font-medium" id="modal-status"></p>
            </div>
          </div>
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
    // Add this new function at the top of your script section
    function filterPayroll(status) {
      const rows = document.querySelectorAll('tbody tr');
      rows.forEach(row => {
        const statusCell = row.querySelector('[id^="status-"]');
        if (!statusCell) return;
        
        const statusText = statusCell.textContent.trim();
        if (status === 'all') {
          row.style.display = '';
        } else {
          row.style.display = statusText === status ? '' : 'none';
        }
      });
    }

    function approvePayroll(payrollID) {
      fetch('approve_payroll.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ payroll_id: payrollID })
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

    function denyPayroll(payrollID) {
      fetch('deny_payroll.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ payroll_id: payrollID })
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
          document.getElementById('modal-salary').textContent = data.salary;
          document.getElementById('modal-overtime').textContent = data.overtime_pay;
          document.getElementById('modal-deductions').textContent = data.deductions;
          document.getElementById('modal-net-salary').textContent = data.net_salary;
          document.getElementById('modal-status').textContent = data.status;
          
          // Set profile picture
          const profilePic = document.getElementById('modal-profile-picture');
          if (data.profile_picture) {
            profilePic.src = 'data:image/jpeg;base64,' + data.profile_picture;
          } else {
            profilePic.src = '../img/default-profile.jpg'; // Set a default image path
          }

          document.getElementById('payrollModal').showModal();
        }
      });
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

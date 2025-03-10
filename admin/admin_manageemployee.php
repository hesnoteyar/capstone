<?php
// filepath: /d:/XAMPP/htdocs/capstone/admin/admin_manageemployee.php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../admin/admin_login.php");
    exit();
}
include '../admin/adminnavbar.php';
include '../authentication/db.php';

// Fetch employee data from the database
$search = isset($_GET['search']) ? $_GET['search'] : '';
$role = isset($_GET['role']) ? $_GET['role'] : '';
$sql = "SELECT employee_id, firstName, middleName, lastName, role, email, profile_picture FROM employee WHERE 1=1";
$params = [];
$types = '';

if ($search) {
    $sql .= " AND (firstName LIKE ? OR middleName LIKE ? OR lastName LIKE ? OR role LIKE ? OR email LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
    $types .= 'sssss';
}

if ($role && $role != 'All Roles') {
    $sql .= " AND role = ?";
    $params[] = $role;
    $types .= 's';
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
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

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
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
<body class="bg-base-200 min-h-screen">
    <div class="p-8 flex flex-col h-[calc(100vh-4rem)]">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Manage Employees</h1>
            <a href="admin_addemployee.php" class="btn btn-primary">Add New Employee</a>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-base-100 p-4 rounded-lg shadow-lg mb-6">
            <div class="flex gap-4">
                <div class="form-control flex-1">
                    <div class="input-group">
                        <input type="text" id="searchInput" placeholder="Search employees..." class="input input-bordered w-full" value="<?php echo htmlspecialchars($search); ?>" onkeypress="handleSearch(event)" />
                    </div>
                </div>
                <select class="select select-bordered w-48" id="roleFilter" onchange="filterByRole()">
                    <option disabled selected>Filter by Role</option>
                    <option value="All Roles">All Roles</option>
                    <option value="Cashier" <?php echo $role == 'Cashier' ? 'selected' : ''; ?>>Cashier</option>
                    <option value="Mechanic" <?php echo $role == 'Mechanic' ? 'selected' : ''; ?>>Mechanic</option>
                    <option value="Head Mechanic" <?php echo $role == 'Head Mechanic' ? 'selected' : ''; ?>>Head Mechanic</option>
                    <option value="Cleaner" <?php echo $role == 'Cleaner' ? 'selected' : ''; ?>>Cleaner</option>
                    <option value="Maintenance" <?php echo $role == 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                </select>
            </div>
        </div>

        <!-- Employee Table -->
        <div class="flex-grow bg-base-100 rounded-lg shadow-lg overflow-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Profile</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr id="row-<?php echo $row['employee_id']; ?>">
                                <td><?php echo htmlspecialchars($row['employee_id']); ?></td>
                                <td>
                                    <div class="avatar">
                                        <div class="w-12 rounded-full">
                                            <img src="<?php echo $row['profile_picture'] ? 'data:image/jpeg;base64,' . base64_encode($row['profile_picture']) : 'media/defaultpfp.jpg'; ?>" />
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName']); ?></td>
                                <td><?php echo htmlspecialchars($row['role']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <div class="flex gap-2">
                                        <?php
                                            $employeeJson = json_encode($row);
                                            // Escape quotes and make it safe for HTML attribute
                                            $employeeJsonAttr = htmlspecialchars($employeeJson, ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <button class="btn btn-sm btn-info" 
                                            data-employee="<?php echo $employeeJsonAttr; ?>"
                                            onclick="editEmployee(this)">
                                            Edit
                                        </button>
                                        <button class="btn btn-sm btn-error" onclick="confirmDelete(<?php echo $row['employee_id']; ?>)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No employees found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center mt-4 pb-2">
            <div class="join">
                <button class="join-item btn">«</button>
                <button class="join-item btn btn-active">1</button>
                <button class="join-item btn">2</button>
                <button class="join-item btn">3</button>
                <button class="join-item btn">»</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <input type="checkbox" id="deleteModal" class="modal-toggle" />
    <div class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Confirm Delete</h3>
            <p class="py-4">Are you sure you want to delete this employee?</p>
            <div class="modal-action">
                <label for="deleteModal" class="btn">Cancel</label>
                <button class="btn btn-error" onclick="deleteEmployee()">Delete</button>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <input type="checkbox" id="editModal" class="modal-toggle" />
    <div class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Edit Employee</h3>
            <form id="editEmployeeForm" onsubmit="updateEmployee(event)">
                <input type="hidden" id="editEmployeeID" name="employee_id" />
                <div class="form-control mb-4">
                    <label class="label">First Name</label>
                    <input type="text" id="editFirstName" name="firstName" class="input input-bordered" required />
                </div>
                <div class="form-control mb-4">
                    <label class="label">Middle Name</label>
                    <input type="text" id="editMiddleName" name="middleName" class="input input-bordered" required />
                </div>
                <div class="form-control mb-4">
                    <label class="label">Last Name</label>
                    <input type="text" id="editLastName" name="lastName" class="input input-bordered" required />
                </div>
                <div class="form-control mb-4">
                    <label class="label">Role</label>
                    <input type="text" id="editRole" name="role" class="input input-bordered" required />
                </div>
                <div class="form-control mb-4">
                    <label class="label">Email</label>
                    <input type="email" id="editEmail" name="email" class="input input-bordered" required />
                </div>
                <div class="form-control mb-4">
                    <label class="label">Profile Picture</label>
                    <input type="file" id="editProfilePicture" name="profile_picture" class="input input-bordered" />
                </div>
                <div class="modal-action">
                    <label for="editModal" class="btn">Cancel</label>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification Banner -->
    <div id="notificationBanner" class="notification-banner"></div>

    <footer class="mt-auto">
        <?php include '../admin/admin_footer.php'; ?>
    </footer>

    <script>
        let employeeToDelete = null;

        function confirmDelete(employeeID) {
            employeeToDelete = employeeID;
            document.getElementById('deleteModal').checked = true;
        }

        function deleteEmployee() {
            if (employeeToDelete !== null) {
                fetch('delete_employee.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ employee_id: employeeToDelete })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('row-' + employeeToDelete).remove();
                        showNotification('Employee deleted successfully', 'success');
                    } else {
                        showNotification('Failed to delete employee', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error occurred while deleting employee', 'error');
                })
                .finally(() => {
                    document.getElementById('deleteModal').checked = false;
                    employeeToDelete = null;
                });
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

        function handleSearch(event) {
            if (event.key === 'Enter') {
                const searchInput = document.getElementById('searchInput').value;
                window.location.href = '?search=' + encodeURIComponent(searchInput) + '&role=' + encodeURIComponent(document.getElementById('roleFilter').value);
            }
        }

        function filterByRole() {
            const role = document.getElementById('roleFilter').value;
            const searchInput = document.getElementById('searchInput').value;
            window.location.href = '?search=' + encodeURIComponent(searchInput) + '&role=' + encodeURIComponent(role);
        }

        function editEmployee(button) {
            try {
                const employeeDataStr = button.getAttribute('data-employee');
                if (!employeeDataStr) {
                    throw new Error('No employee data attribute found');
                }
                
                console.log('Raw employee data:', employeeDataStr); // Debug log
                const employeeData = JSON.parse(employeeDataStr);
                
                if (!employeeData || typeof employeeData !== 'object') {
                    throw new Error('Invalid employee data format');
                }

                document.getElementById('editEmployeeID').value = employeeData.employee_id || '';
                document.getElementById('editFirstName').value = employeeData.firstName || '';
                document.getElementById('editMiddleName').value = employeeData.middleName || '';
                document.getElementById('editLastName').value = employeeData.lastName || '';
                document.getElementById('editRole').value = employeeData.role || '';
                document.getElementById('editEmail').value = employeeData.email || '';
                document.getElementById('editModal').checked = true;
            } catch (error) {
                console.error('Error parsing employee data:', error);
                console.error('Attempted to parse:', button.getAttribute('data-employee'));
                showNotification('Error loading employee data', 'error');
            }
        }
        
        function updateEmployee(event) {
            event.preventDefault();
            const formData = new FormData(document.getElementById('editEmployeeForm'));

            fetch('update_employee.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Employee updated successfully', 'success');
                    document.getElementById('editModal').checked = false;
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification('Failed to update employee', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error occurred while updating employee', 'error');
            });
        }
    </script>
</body>
</html>
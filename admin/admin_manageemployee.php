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
$sql = "SELECT employee_id, firstName, middleName, lastName, role, email, profile_picture, address, city, postalCode, leaves FROM employee WHERE 1=1";
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
        /* Add custom modal styles */
        .modal-box {
            max-width: 80vw !important;
            width: 900px !important;
            max-height: 90vh !important;
            padding: 2rem !important;
        }
        .modal-box::-webkit-scrollbar {
            display: none;
        }
        .modal-box {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
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
                                            // Remove profile_picture from JSON to avoid large data
                                            $employeeData = array_diff_key($row, ['profile_picture' => '']);
                                            $employeeJson = json_encode($employeeData, JSON_HEX_APOS | JSON_HEX_QUOT);
                                            // Double encode to prevent JSON breaking
                                            $employeeJsonAttr = htmlspecialchars($employeeJson, ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <button class="btn btn-sm btn-info" 
                                            data-employee='<?php echo $employeeJsonAttr; ?>'
                                            onclick="editEmployee(this)">
                                            Edit
                                        </button>
                                        <button class="btn btn-sm btn-error" onclick="confirmDelete(<?php echo $row['employee_id']; ?>)">Delete</button>
                                    </div>
                                </td>
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
        <div class="modal-box relative bg-base-100 shadow-lg">
            <div class="flex flex-col gap-4">
                <div class="flex items-center gap-3 border-b border-base-200 pb-4">
                    <div class="text-error">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg">Confirm Delete</h3>
                </div>
                <p class="py-2 text-neutral-content">This action cannot be undone. Are you sure you want to delete this employee?</p>
                <div class="modal-action gap-2">
                    <label for="deleteModal" class="btn btn-ghost">Cancel</label>
                    <button class="btn btn-error" onclick="deleteEmployee()">Delete</button>
                </div>
            </div>
        </div>
        <label class="modal-backdrop" for="deleteModal">Close</label>
    </div>

    <!-- Edit Employee Modal -->
    <input type="checkbox" id="editModal" class="modal-toggle" />
    <div class="modal">
        <div class="modal-box relative bg-base-100 shadow-xl">
            <div class="flex items-center gap-3 border-b border-base-200 pb-4 mb-6">
                <div class="text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="red">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold">Edit Employee Information</h3>
            </div>
            <form id="editEmployeeForm" onsubmit="updateEmployee(event)">
                <input type="hidden" id="editEmployeeID" name="employee_id" />
                
                <div class="form-grid">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">First Name</span>
                        </label>
                        <input type="text" id="editFirstName" name="firstName" class="input input-bordered focus:input-primary" required />
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Middle Name</span>
                        </label>
                        <input type="text" id="editMiddleName" name="middleName" class="input input-bordered focus:input-primary" required />
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Last Name</span>
                        </label>
                        <input type="text" id="editLastName" name="lastName" class="input input-bordered focus:input-primary" required />
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Role</span>
                        </label>
                        <select id="editRole" name="role" class="select select-bordered focus:select-primary" required>
                            <option value="Cashier">Cashier</option>
                            <option value="Mechanic">Mechanic</option>
                            <option value="Head Mechanic">Head Mechanic</option>
                            <option value="Cleaner">Cleaner</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Email</span>
                        </label>
                        <input type="email" id="editEmail" name="email" class="input input-bordered focus:input-primary" required />
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Leaves</span>
                        </label>
                        <input type="number" id="editLeaves" name="leaves" class="input input-bordered focus:input-primary" required min="0" />
                    </div>

                    <div class="form-control col-span-3">
                        <label class="label">
                            <span class="label-text font-medium">Address</span>
                        </label>
                        <input type="text" id="editAddress" name="address" class="input input-bordered focus:input-primary" required />
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">City</span>
                        </label>
                        <input type="text" id="editCity" name="city" class="input input-bordered focus:input-primary" required />
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Postal Code</span>
                        </label>
                        <input type="text" id="editPostalCode" name="postalCode" class="input input-bordered focus:input-primary" required />
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Profile Picture</span>
                        </label>
                        <input type="file" id="editProfilePicture" name="profile_picture" 
                               class="file-input file-input-bordered file-input-error w-full" 
                               accept="image/*" />
                    </div>
                </div>

                <div class="modal-action gap-2 mt-8 pt-4 border-t border-base-200">
                    <label for="editModal" class="btn btn-ghost">Cancel</label>
                    <button type="submit" class="btn btn-error">Save Changes</button>
                </div>
            </form>
        </div>
        <label class="modal-backdrop" for="editModal">Close</label>
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
                console.log('Button element:', button);
                console.log('Raw employee data string:', employeeDataStr);

                if (!employeeDataStr) {
                    throw new Error('No employee data attribute found');
                }

                const employeeData = JSON.parse(employeeDataStr);
                console.log('Parsed employee data:', employeeData);

                if (!employeeData || typeof employeeData !== 'object') {
                    throw new Error('Invalid employee data format');
                }

                // Fill in form fields
                Object.entries({
                    'editEmployeeID': 'employee_id',
                    'editFirstName': 'firstName',
                    'editMiddleName': 'middleName',
                    'editLastName': 'lastName',
                    'editRole': 'role',
                    'editEmail': 'email',
                    'editAddress': 'address',
                    'editCity': 'city',
                    'editPostalCode': 'postalCode',
                    'editLeaves': 'leaves'
                }).forEach(([elementId, dataKey]) => {
                    const element = document.getElementById(elementId);
                    if (element && employeeData[dataKey] !== undefined) {
                        element.value = employeeData[dataKey];
                    }
                });

                document.getElementById('editModal').checked = true;
            } catch (error) {
                console.error('Error in editEmployee:', error);
                console.error('Button attributes:', Array.from(button.attributes));
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
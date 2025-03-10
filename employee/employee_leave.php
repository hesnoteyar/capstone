<?php
// filepath: /d:/XAMPP/htdocs/capstone/employee/employee_leave.php
session_start();
include '..\employee\employee_topnavbar.php';
include '../authentication/db.php'; // Include your database connection

$employee_id = $_SESSION['id']; // Assuming the employee ID is stored in the session

// Fetch the total number of leaves taken by the employee from the employee table
$leave_count_query = "SELECT leaves FROM employee WHERE employee_id = ?";
$leave_count_stmt = $conn->prepare($leave_count_query);
$leave_count_stmt->bind_param("i", $employee_id);
$leave_count_stmt->execute();
$leave_count_stmt->bind_result($total_leaves);
$leave_count_stmt->fetch();
$leave_count_stmt->close();
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
        .calendar-container {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
        }
        .calendar {
            flex: 1;
        }
    </style>

    <title>Employee Leave</title>
</head>
<body class="bg-base-200">
    <div class="container mx-auto p-6">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success shadow-lg mb-4">
                <div>
                    <span><?php echo $_SESSION['success_message']; ?></span>
                </div>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error shadow-lg mb-4">
                <div>
                    <span><?php echo $_SESSION['error_message']; ?></span>
                </div>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="card bg-base-100 shadow-xl mb-4">
            <div class="card-body">
                <h2 class="card-title text-2xl font-bold mb-4">Available Leaves:</h2>
                <p class="text-lg">You have     <strong><?php echo $total_leaves; ?></strong> leaves.</p>
            </div>
        </div>

        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title text-2xl font-bold mb-4">Leave Application</h2>
                <form method="POST" action="submit_leave.php">
                    <div class="form-control mb-4">
                        <label class="label text-sm text-error">Name</label>
                        <input type="text" name="name" class="input input-bordered w-full" required>
                    </div>
                    <div class="form-control mb-4">
                        <label class="label text-sm text-error">Type of Leave</label>
                        <select name="leave_type" class="select select-bordered w-full" required>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Casual Leave">Casual Leave</option>
                            <option value="Maternity Leave">Maternity Leave</option>
                            <option value="Paternity Leave">Paternity Leave</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-control mb-4">
                        <label class="label text-sm text-error">Reason for Leave</label>
                        <textarea name="reason" class="textarea textarea-bordered w-full" required></textarea>
                    </div>
                    <div class="calendar-container mb-4">
                        <div class="calendar">
                            <label class="label text-sm text-error">Leave Start Date</label>
                            <input type="date" name="leave_start_date" class="input input-bordered w-full" required>
                        </div>
                        <div class="calendar">
                            <label class="label text-sm text-error">Leave End Date</label>
                            <input type="date" name="leave_end_date" class="input input-bordered w-full" required>
                        </div>
                    </div>
                    <div class="form-control mt-6">
                        <button type="submit" class="btn btn-primary w-full">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

<?php
include '..\employee\employee_footer.php';
?>
</html>
<?php
ob_start();
session_start();

// Include required files
include '../employee/employee_topnavbar.php';
include '../authentication/db.php';

// Redirect to login if the user is not logged in
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header("Location: ../employee/employee_login.php");
    exit;
}

// Retrieve session variables
if (isset($_SESSION['id'])) {
    $employee_id = $_SESSION['id'];
    $first_name = htmlspecialchars($_SESSION['firstName']);
    $last_name = htmlspecialchars($_SESSION['lastName']);

    // Fetch role and profile picture from the database
    $stmt = $conn->prepare("SELECT role, profile_picture FROM employee WHERE employee_id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->bind_result($role, $profileImageBlob);
    $stmt->fetch();
    $stmt->close();

    // Encode the binary data as a base64 string
    if ($profileImageBlob) {
        $profileImage = 'data:image/jpeg;base64,' . base64_encode($profileImageBlob);
    } else {
        $profileImage = "../media/defaultpfp.jpg";
    }
} else {
    header("Location: ../employee/employee_login.php");
    exit;
}

// Escape the role to prevent XSS attacks
$role = htmlspecialchars($role ?: "No Role Assigned");

// Fetch leave request details
$leaveRequestQuery = "SELECT leave_type, leave_reason, leave_start_date, leave_end_date, approval_status FROM leave_request WHERE employee_id = ? ORDER BY id DESC LIMIT 1";
$leaveRequestStmt = $conn->prepare($leaveRequestQuery);
$leaveRequestStmt->bind_param("i", $employee_id);
$leaveRequestStmt->execute();
$leaveRequestStmt->bind_result($leave_type, $leave_reason, $leave_start_date, $leave_end_date, $approval_status);
$leaveRequestExists = $leaveRequestStmt->fetch();
$leaveRequestStmt->close();

// Fetch schedule request details
$scheduleRequestQuery = "SELECT requested_date, start_time, notes, status FROM schedule_requests WHERE employee_id = ? ORDER BY request_id DESC LIMIT 1";
$scheduleRequestStmt = $conn->prepare($scheduleRequestQuery);
$scheduleRequestStmt->bind_param("i", $employee_id);
$scheduleRequestStmt->execute();
$scheduleRequestStmt->bind_result($schedule_date, $schedule_time, $schedule_reason, $schedule_approval_status);
$scheduleRequestExists = $scheduleRequestStmt->fetch();
$scheduleRequestStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.13/dist/full.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Employee Dashboard</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .card {
            margin-bottom: 1.5rem;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .card-body {
            padding: 1.5rem;
        }
        .card-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .btn {
            border-radius: 0.5rem;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .btn:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <!-- Success and Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success shadow-lg mb-4" id="successBanner">
            <div>
                <span><?php echo $_SESSION['success_message']; ?></span>
            </div>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php elseif (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error shadow-lg mb-4" id="errorBanner">
            <div>
                <span><?php echo $_SESSION['error_message']; ?></span>
            </div>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <script>
        // Auto-hide banners after 5 seconds
        setTimeout(() => {
            const successBanner = document.getElementById('successBanner');
            const errorBanner = document.getElementById('errorBanner');
            if (successBanner) successBanner.style.display = 'none';
            if (errorBanner) errorBanner.style.display = 'none';
        }, 5000);
    </script>

    <!-- Dashboard Section -->
    <div class="container mx-auto p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            <!-- Profile Card -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body text-center flex flex-col items-center">
                    <h2 class="text-2xl font-bold mb-4">Hi, <?php echo $first_name; ?>! Welcome to your Dashboard</h2>
                    <div class="relative mb-4">
                        <div class="avatar w-32 h-32">
                            <img id="profileImage" 
                                src="<?php echo $profileImage; ?>" 
                                class="w-full h-full rounded-full ring ring-error ring-offset-base-100 ring-offset-2 shadow-lg" 
                                alt="Profile Picture">
                        </div>
                    </div>
                    <div class="mb-4">
                        <h3 class="text-lg font-medium">Name: <?php echo $first_name . ' ' . $last_name; ?></h3>
                        <h4 class="text-md font-medium">Designation: <?php echo $role; ?></h4>
                    </div>
                </div>
            </div>

            <!-- Timer Card -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body text-center flex flex-col items-center justify-center">
                    <h2 class="text-2xl font-bold mb-4">Work Timer</h2>
                    <div id="timer" class="mt-4 text-4xl font-bold">00:00:00</div>
                    <div class="flex justify-between mt-4 w-full">
                        <button class="btn btn-success w-1/2 mr-2" onclick="clockIn()">Clock-In</button>
                        <button class="btn btn-error w-1/2 ml-2" onclick="clockOut()">Clock-Out</button>
                    </div>
                </div>
            </div>

            <!-- Leave Request Card -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="text-2xl font-bold mb-4">Leave Request</h2>
                    <?php if ($leaveRequestExists): ?>
                        <p><strong>Type of Leave:</strong> <?= htmlspecialchars($leave_type) ?></p>
                        <p><strong>Reason:</strong> <?= htmlspecialchars($leave_reason) ?></p>
                        <p><strong>Start:</strong> <?= htmlspecialchars($leave_start_date) ?></p>
                        <p><strong>End:</strong> <?= htmlspecialchars($leave_end_date) ?></p>
                        <div class="badge <?= $approval_status === 'Approved' ? 'badge-success' : ($approval_status === 'Not Approved' ? 'badge-error' : 'badge-warning') ?>">
                            <?= htmlspecialchars($approval_status) ?>
                        </div>
                    <?php else: ?>
                        <p>You have no pending leave requests.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Schedule Request Card -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="text-2xl font-bold mb-4">Schedule Request</h2>
                    <?php if ($scheduleRequestExists): ?>
                        <p><strong>Schedule Date:</strong> <?= htmlspecialchars($schedule_date) ?></p>
                        <p><strong>Schedule Time:</strong> <?= date("g:i A", strtotime($schedule_time)) ?></p>
                        <p><strong>Reason:</strong> <?= htmlspecialchars($schedule_reason) ?></p>
                        <div class="badge <?= $schedule_approval_status === 'Approved' ? 'badge-success' : ($schedule_approval_status === 'Not Approved' ? 'badge-error' : 'badge-warning') ?>">
                            <?= htmlspecialchars($schedule_approval_status) ?>
                        </div>
                    <?php else: ?>
                        <p>You have no pending schedule requests.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Additional Card -->
            <div class="card bg-base-100 shadow-xl md:col-span-2">
                <div class="card-body">
                    <h2 class="text-2xl font-bold mb-4">Select Date</h2>
                    <div class="calendar-container">
                        <div id="calendar" class="grid grid-cols-7 gap-2 text-center text-sm">
                            <div class="col-span-7 flex justify-between items-center mb-4">
                                <button id="prevMonth" class="btn btn-sm btn-outline">◀</button>
                                <h3 id="currentMonth" class="text-lg font-semibold"></h3>
                                <button id="nextMonth" class="btn btn-sm btn-outline">▶</button>
                            </div>
                            <div class="font-bold">Sun</div>
                            <div class="font-bold">Mon</div>
                            <div class="font-bold">Tue</div>
                            <div class="font-bold">Wed</div>
                            <div class="font-bold">Thu</div>
                            <div class="font-bold">Fri</div>
                            <div class="font-bold">Sat</div>
                            <div id="dates" class="col-span-7 grid grid-cols-7 gap-2"></div>
                        </div>

                        <script>
                            const calendar = document.getElementById("calendar");
                            const currentMonthEl = document.getElementById("currentMonth");
                            const datesEl = document.getElementById("dates");
                            const prevMonthBtn = document.getElementById("prevMonth");
                            const nextMonthBtn = document.getElementById("nextMonth");

                            let currentDate = new Date();

                            function renderCalendar() {
                                const month = currentDate.getMonth();
                                const year = currentDate.getFullYear();

                                // Set current month and year in header
                                currentMonthEl.textContent = `${currentDate.toLocaleString("default", { month: "long" })} ${year}`;

                                // Clear existing dates
                                datesEl.innerHTML = "";

                                // Get first day and total days in the current month
                                const firstDay = new Date(year, month, 1).getDay();
                                const daysInMonth = new Date(year, month + 1, 0).getDate();

                                // Add blank days for the first week
                                for (let i = 0; i < firstDay; i++) {
                                    const blankCell = document.createElement("div");
                                    blankCell.className = "text-gray-400";
                                    datesEl.appendChild(blankCell);
                                }

                                // Add days of the month
                                for (let day = 1; day <= daysInMonth; day++) {
                                    const dayCell = document.createElement("div");
                                    dayCell.textContent = day;
                                    dayCell.className = "p-2 rounded cursor-pointer hover:bg-error hover:text-white";
                                    dayCell.onclick = () => alert(`You selected ${day}/${month + 1}/${year}`);
                                    datesEl.appendChild(dayCell);
                                }
                            }

                            // Update calendar when navigating months
                            prevMonthBtn.onclick = () => {
                                currentDate.setMonth(currentDate.getMonth() - 1);
                                renderCalendar();
                            };

                            nextMonthBtn.onclick = () => {
                                currentDate.setMonth(currentDate.getMonth() + 1);
                                renderCalendar();
                            };

                            // Initial render
                            renderCalendar();
                        </script>
                    </div>
                    <div class="time-selector mt-4">
                        <label for="time" class="text-sm">Time:</label>
                        <input type="time" id="time" class="input input-bordered w-full max-w-xs mt-2">
                    </div>

                    <!-- Additional Actions -->
                    <div class="additional-actions mt-6 space-y-2">
                        <button class="btn btn-info w-full text-lg py-2" onclick="window.location.href='../employee/employee_payroll.php'">Payroll</button>
                        <button class="btn btn-primary w-full text-lg py-2" onclick="window.location.href='../employee/employee_leave.php'">Request a Leave</button>
                        <button class="btn btn-warning w-full text-lg py-2" onclick="window.location.href='../employee/employee_request.php'">Request a Schedule</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../employee/employee_footer.php'; ?>
    <?php ob_end_flush(); ?>

    <script>
        let timerInterval;
        let startTime;

        // Clock-In function
        function clockIn() {
            const employee_id = <?php echo $employee_id; ?>;
            const date = new Date().toISOString().split('T')[0]; // Get current date in YYYY-MM-DD format
            const now = new Date();
            const check_in_time = now.toLocaleTimeString('en-US', { hour12: false }); // Format: HH:MM:SS

            // Send AJAX request to clock-in
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "clock_in_out.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        showBanner('success', response.message);
                    } else {
                        showBanner('error', response.message);
                    }
                    startTimer();
                }
            };
            xhr.send(`action=clock_in&employee_id=${employee_id}&check_in_time=${check_in_time}&date=${date}`);
        }

        // Clock-Out function
        function clockOut() {
            const employee_id = <?php echo $employee_id; ?>;
            const date = new Date().toISOString().split('T')[0]; // Get current date in YYYY-MM-DD format
            const now = new Date();
            const check_out_time = now.toLocaleTimeString('en-US', { hour12: false }); // Format: HH:MM:SS

            // Send AJAX request to clock-out
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "clock_in_out.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        showBanner('success', response.message);
                    } else {
                        showBanner('error', response.message);
                    }
                    stopTimer();
                }
            };
            xhr.send(`action=clock_out&employee_id=${employee_id}&check_out_time=${check_out_time}&date=${date}`);
        }

        // Start timer
        function startTimer() {
            startTime = new Date();
            timerInterval = setInterval(updateTimer, 1000);
        }

        // Stop timer
        function stopTimer() {
            clearInterval(timerInterval);
        }

        // Update timer display
        function updateTimer() {
            const now = new Date();
            const elapsedTime = new Date(now - startTime);
            const hours = String(elapsedTime.getUTCHours()).padStart(2, '0');
            const minutes = String(elapsedTime.getUTCMinutes()).padStart(2, '0');
            const seconds = String(elapsedTime.getUTCSeconds()).padStart(2, '0');
            document.getElementById('timer').textContent = `${hours}:${minutes}:${seconds}`;
        }

        // Show banner
        function showBanner(type, message) {
            const banner = document.createElement('div');
            banner.className = `alert alert-${type} shadow-lg mb-4`;
            banner.innerHTML = `<div><span>${message}</span></div>`;
            document.body.prepend(banner);
            setTimeout(() => {
                banner.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>

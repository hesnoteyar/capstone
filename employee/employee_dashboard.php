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
    $stmt->bind_result($role, $profileImage);
    $stmt->fetch();
    $stmt->close();
} else {
    header("Location: ../employee/employee_login.php");
    exit;
}

// Set a default profile image if none exists
$profileImage = $profileImage ?: "../media/defaultpfp.jpg";

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profileImage'])) {
    $targetDirectory = "../media/";
    $targetFile = $targetDirectory . basename($_FILES["profileImage"]["name"]);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Validate uploaded file
    if ($_FILES["profileImage"]["size"] > 500000) {
        $_SESSION['error_message'] = "File is too large. Maximum allowed size is 500KB.";
    } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        $_SESSION['error_message'] = "Only JPG, JPEG, PNG, and GIF files are allowed.";
    } elseif (getimagesize($_FILES["profileImage"]["tmp_name"]) !== false) {
        // Move uploaded file
        if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $targetFile)) {
            // Update profile picture path in the database
            $updateQuery = "UPDATE employee SET profile_picture = ? WHERE employee_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("si", $targetFile, $employee_id);
            $updateStmt->execute();
            $updateStmt->close();

            $_SESSION['success_message'] = "Profile picture updated successfully!";
        } else {
            $_SESSION['error_message'] = "Error uploading your file.";
        }
    } else {
        $_SESSION['error_message'] = "Uploaded file is not a valid image.";
    }

    header("Location: ../employee/employee_dashboard.php");
    exit;

}

// Escape the role to prevent XSS attacks
$role = htmlspecialchars($role ?: "No Role Assigned");
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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-4">

        <!-- Profile Card -->
        <div class="card bg-base-100 shadow-xl w-full max-w-m mx-auto max-h-fit">
        <div class="card-body text-center flex flex-col items-center justify-between">
            <h2 class="text-xl font-bold">Hi, <?php echo $first_name; ?>! Welcome to your Dashboard</h2>

            <div class="relative mb-4"> <div class="avatar w-32 h-32">
                <img id="profileImage" 
                    src="<?php echo $profileImage; ?>" 
                    class="w-full h-full rounded-full ring ring-error ring-offset-base-100 ring-offset-2 shadow-lg" 
                    alt="Profile Picture">
            </div>
            <button onclick="document.getElementById('imageInput').click()" 
                    class="absolute bottom-0 right-0 btn btn-circle btn-sm bg-error text-white shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M15.232 5.232l3.536 3.536m-2.036-1.5A2.5 2.5 0 1112.5 7.5M16.5 12H7.5M9 15h6" />
                </svg>
            </button>
            </div>
            <div>
            <h3 class="text-lg font-medium">Name: <?php echo $first_name . ' ' . $last_name; ?></h3>
            <h4 class="text-md font-medium">Designation: <?php echo $role; ?></h4>
            </div>

            <form method="POST" enctype="multipart/form-data" class="w-full">
            <input type="file" name="profileImage" accept="image/*" id="imageInput" class="hidden" onchange="previewImage(event)">
            <button type="button" onclick="document.getElementById('imageInput').click()" class="btn btn-outline mt-2 w-full">Select Photo</button>
            <button type="submit" name="saveImage" class="btn btn-error mt-2 w-full">Save Photo</button>
            </form>
        </div>
    </div>


    <!-- Additional Card -->
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="text-xl font-bold">Select Date</h2>
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
            
            <!-- Clock-In and Clock-Out Buttons -->
            <div class="flex justify-between mt-4">
                <button class="btn btn-success w-1/2 mr-2" onclick="clockIn()">Clock-In</button>
                <button class="btn btn-error w-1/2 mr-2" onclick="clockOut()">Clock-Out</button>
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
    // Profile Picture Preview
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('profileImage').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }

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
            alert(xhr.responseText); // Show alert with response message
        }
    };
    xhr.send("action=clock_in&employee_id=" + employee_id + "&check_in_time=" + check_in_time + "&date=" + date);
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
            alert(xhr.responseText); // Show alert with response message
        }
    };
    xhr.send("action=clock_out&employee_id=" + employee_id + "&check_out_time=" + check_out_time + "&date=" + date);
}

</script>

</body>
</html>

<?php
session_start();
include 'employee_topnavbar.php';

// Assuming the employee ID is stored in the session
$employee_id = $_SESSION['id'];
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
    </style>

    <title>Schedule Request</title>
</head>
<body class="bg-base-200">
    <div class="container mx-auto p-6">
        <?php if (isset($_SESSION['success_message'])): ?>
             <div class="alert alert-success shadow-lg mb-4 fixed top-5 left-5" style="width: 40%;">
                <div>
                    <span><?php echo $_SESSION['success_message']; ?></span>
                </div>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error shadow-lg mb-4 fixed top-5 left-5" style="width: 40%;">
                <div>
                    <span><?php echo $_SESSION['error_message']; ?></span>
                </div>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title text-2xl font-bold mb-4">Schedule Request</h2>
                <form method="POST" action="submit_schedule_request.php">
                    <div class="form-control mb-4">
                        <label class="label text-sm text-error">Employee Name</label>
                        <input type="text" name="employee_name" class="input input-bordered w-full" required>
                    </div>
                    <div class="form-control mb-4">
                        <label class="label text-sm text-error">Requested Date</label>
                        <input type="date" name="requested_date" class="input input-bordered w-full" required>
                    </div>
                    <div class="form-control mb-4">
                        <label class="label text-sm text-error">Start Time</label>
                        <input type="time" name="start_time" class="input input-bordered w-full" required>
                    </div>
                    <div class="form-control mb-4">
                        <label class="label text-sm text-error">End Time</label>
                        <input type="time" name="end_time" class="input input-bordered w-full" required>
                    </div>
                    <div class="form-control mb-4">
                        <label class="label text-sm text-error">Notes</label>
                        <textarea name="notes" class="textarea textarea-bordered w-full"></textarea>
                    </div>
                    <div class="form-control mt-6">
                        <button type="submit" class="btn btn-primary w-full">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide banners after 5 seconds
        setTimeout(() => {
            const successBanner = document.querySelector('.alert-success');
            const errorBanner = document.querySelector('.alert-error');
            if (successBanner) successBanner.style.display = 'none';
            if (errorBanner) errorBanner.style.display = 'none';
        }, 5000);
    </script>
</body>

<?php
include 'employee_footer.php';
?>
</html>
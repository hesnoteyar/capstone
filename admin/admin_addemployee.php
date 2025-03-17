<?php
    session_start(); // Start the session to access session variables
    include 'adminnavbar.php';
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
</head>
<body>
    <!-- Alert Section -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div id="success-alert" class="alert alert-success shadow-lg mb-4 fixed top-2 left-5 w-auto max-w-xs">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m0 0l2 2m-2-2l-2-2M6 6h12m0 6H6m0 6h12" /></svg>
                <span><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div id="error-alert" class="alert alert-error shadow-lg mb-4 fixed top-2 left-5 w-auto max-w-xs">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                <span><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const successAlert = document.getElementById('success-alert');
            const errorAlert = document.getElementById('error-alert');
            if (successAlert) successAlert.style.display = 'none';
            if (errorAlert) errorAlert.style.display = 'none';
        }, 5000); // 5 seconds
    </script>

    <!-- Form Section -->
<div class="bg-base-200 min-h-screen flex items-center justify-center py-6">
    <div class="card bg-base-100 w-full max-w-3xl shrink-0 shadow-2xl">
        <div class="text-center lg:text-above">
            <h1 class="py-6 text-3xl font-bold">Add a New Employee</h1>
        </div>

        <form action="..\authentication\add_employee.php" method="post" class="card-body" enctype="multipart/form-data">
            
            <!-- Name Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">First Name</span>
                    </label>
                    <input type="text" name="firstName" placeholder="First Name" class="input input-bordered w-full" required />
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Middle Name</span>
                    </label>
                    <input type="text" name="middleName" placeholder="Middle Name" class="input input-bordered w-full" />
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Last Name</span>
                    </label>
                    <input type="text" name="lastName" placeholder="Last Name" class="input input-bordered w-full" required />
                </div>
            </div>

            <!-- Address Section -->
            <div class="form-control mt-4">
                <label class="label">
                    <span class="label-text">Address</span>
                </label>
                <input type="text" name="address" placeholder="Street Address" class="input input-bordered w-full" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">City</span>
                    </label>
                    <input type="text" name="city" placeholder="City" class="input input-bordered w-full" required />
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Postal Code</span>
                    </label>
                    <input type="text" name="postalCode" placeholder="Postal Code" class="input input-bordered w-full" required />
                </div>
            </div>

            <!-- Email Field -->
            <div class="form-control mt-4">
                <label class="label">
                    <span class="label-text">Email</span>
                </label>
                <input type="email" name="email" placeholder="Email" class="input input-bordered w-full" required />
            </div>

            <!-- Password and Repeat Password Fields -->
            <div class="form-control mt-4">
                <label class="label">
                    <span class="label-text">Password</span>
                </label>
                <input type="password" name="password" placeholder="Password" class="input input-bordered w-full" required />
            </div>

            <div class="form-control mt-4">
                <label class="label">
                    <span class="label-text">Repeat Password</span>
                </label>
                <input type="password" name="repeat_password" placeholder="Repeat Password" class="input input-bordered w-full" required />
            </div>

            <!-- Role Field -->
            <div class="form-control mt-4">
                <label class="label">
                    <span class="label-text">Role</span>
                </label>
                <select name="role" class="select select-bordered w-full" required>
                    <option value="Cashier">Cashier</option>
                    <option value="Mechanic">Mechanic</option>
                    <option value="Head Mechanic">Head Mechanic</option>
                    <option value="Cleaner">Cleaner</option>
                    <option value="Maintenance">Maintenance</option>
                </select>
            </div>

            <!-- Profile Picture Upload -->
            <div class="form-control mt-4">
                <label class="label">
                    <span class="label-text">Profile Picture</span>
                </label>
                <input type="file" name="profileImage" accept="image/*" class="file-input file-input-bordered w-full" />
                <label class="text-xs text-gray-500 mt-1">Max size 2MB</label>
            </div>

            <!-- Create Account Button -->
            <div class="form-control mt-6">
                <button class="btn bg-red-500 hover:bg-red-700 text-white">Create Account</button>
            </div>
        </form>
    </div>
</div>

</body>
<?php
    include 'admin_footer.php';
?>
</html>

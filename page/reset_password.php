<?php
session_start();
if (!isset($_SESSION['otp_verified']) || !isset($_SESSION['reset_email'])) {
    header("Location: /index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.13/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Reset Password</title>
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center">
    <div class="card bg-base-100 w-full max-w-md shadow-xl">
        <div class="card-body">
            <h2 class="card-title justify-center">Reset Password</h2>
            <form action="/authentication/update_password.php" method="post">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">New Password</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" class="input input-bordered w-full pr-10" required />
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                        </span>
                    </div>
                </div>
                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text">Confirm Password</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="confirm_password" id="confirm_password" class="input input-bordered w-full pr-10" required />
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas fa-eye toggle-password" id="toggleConfirmPassword"></i>
                        </span>
                    </div>
                </div>
                <div class="form-control mt-6">
                    <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white">Update Password</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('#togglePassword');
            const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
            const password = document.querySelector('#password');
            const confirmPassword = document.querySelector('#confirm_password');

            [togglePassword, toggleConfirmPassword].forEach((toggle, index) => {
                toggle.addEventListener('click', function() {
                    const input = index === 0 ? password : confirmPassword;
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            });
        });
    </script>
</body>
</html>

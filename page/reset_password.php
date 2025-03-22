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
            <form action="../authentication/update_password.php" method="post">
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
                    <!-- Add password requirements indicators -->
                    <div class="mt-2 text-sm">
                        <p id="length" class="text-gray-500">❌ At least 8 characters</p>
                        <p id="capital" class="text-gray-500">❌ At least one capital letter</p>
                        <p id="number" class="text-gray-500">❌ At least one number</p>
                        <p id="special" class="text-gray-500">❌ At least one special character</p>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-red-600 h-2.5 rounded-full" id="password-strength" style="width: 0%"></div>
                        </div>
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
            const form = document.querySelector('form');

            // Password visibility toggle
            [togglePassword, toggleConfirmPassword].forEach((toggle, index) => {
                toggle.addEventListener('click', function() {
                    const input = index === 0 ? password : confirmPassword;
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            });

            // Password validation
            const length = document.getElementById('length');
            const capital = document.getElementById('capital');
            const number = document.getElementById('number');
            const special = document.getElementById('special');
            const strengthBar = document.getElementById('password-strength');

            password.addEventListener('input', function() {
                const pass = this.value;
                let strength = 0;

                // Check length
                if(pass.length >= 8) {
                    length.innerHTML = '✅ At least 8 characters';
                    length.classList.remove('text-gray-500');
                    length.classList.add('text-green-500');
                    strength += 25;
                } else {
                    length.innerHTML = '❌ At least 8 characters';
                    length.classList.remove('text-green-500');
                    length.classList.add('text-gray-500');
                }

                // Check capital letter
                if(pass.match(/[A-Z]/)) {
                    capital.innerHTML = '✅ At least one capital letter';
                    capital.classList.remove('text-gray-500');
                    capital.classList.add('text-green-500');
                    strength += 25;
                } else {
                    capital.innerHTML = '❌ At least one capital letter';
                    capital.classList.remove('text-green-500');
                    capital.classList.add('text-gray-500');
                }

                // Check number
                if(pass.match(/[0-9]/)) {
                    number.innerHTML = '✅ At least one number';
                    number.classList.remove('text-gray-500');
                    number.classList.add('text-green-500');
                    strength += 25;
                } else {
                    number.innerHTML = '❌ At least one number';
                    number.classList.remove('text-green-500');
                    number.classList.add('text-gray-500');
                }

                // Check special character
                if(pass.match(/[!@#$%^&*]/)) {
                    special.innerHTML = '✅ At least one special character';
                    special.classList.remove('text-gray-500');
                    special.classList.add('text-green-500');
                    strength += 25;
                } else {
                    special.innerHTML = '❌ At least one special character';
                    special.classList.remove('text-green-500');
                    special.classList.add('text-gray-500');
                }

                strengthBar.style.width = strength + '%';
            });

            // Form submission validation
            form.addEventListener('submit', function(e) {
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    return;
                }

                if (password.value.length < 8 || 
                    !password.value.match(/[A-Z]/) || 
                    !password.value.match(/[0-9]/) || 
                    !password.value.match(/[!@#$%^&*]/)) {
                    e.preventDefault();
                    alert('Password does not meet requirements!');
                    return;
                }
            });
        });
    </script>
</body>
</html>

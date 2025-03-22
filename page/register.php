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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .alert-top-left {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <?php
    session_start(); // Start the session to access session variables
    
    if (isset($_SESSION['error_message']) && !empty($_SESSION['error_message'])) {
        echo '<div class="alert alert-error shadow-lg absolute top-4 left-4 max-w-sm">';
        echo '<div>';
        echo '<span>' . $_SESSION['error_message'] . '</span>';
        echo '</div>';
        echo '</div>';
        unset($_SESSION['error_message']); // Clear the message after displaying it
    }

    if (isset($_SESSION['success_message']) && !empty($_SESSION['success_message'])) {
        echo '<div class="alert alert-success shadow-lg absolute top-4 left-4 max-w-sm">';
        echo '<div>';
        echo '<span>' . $_SESSION['success_message'] . '</span>';
        echo '</div>';
        echo '</div>';
        unset($_SESSION['success_message']); // Clear the message after displaying it
    }
    ?>

    <div class="hero bg-base-200 min-h-screen flex items-center justify-center py-6">
        <div class="card bg-base-100 w-full max-w-2xl shrink-0 shadow-2xl">
            <div class="text-center lg:text-above">
                <div class="flex justify-center py-6">
                    <img src="..\media\small_logo.png" alt="">
                </div>
                <h1 class="py-6 text-3xl font-bold">
                    Create an Account
                </h1>
            </div>

            <form action="\authentication\register_method.php" method="post" class="card-body">
                <!-- Name Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">First Name</span>
                        </label>
                        <input type="text" name="firstName" placeholder="First Name" class="input input-bordered w-full" required />
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
                        <input type="text" name="postalCode" placeholder="Postal Code" class="input input-bordered w-full" 
                               pattern="[0-9]*" title="Postal code must contain only numbers" required />
                    </div>
                </div>

                <!-- Email Field -->
                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text">Email</span>
                    </label>
                    <input type="email" name="email" placeholder="Email" class="input input-bordered w-full" required />
                </div>

                <!-- Password Field -->
                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text">Password</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" placeholder="Password" 
                               class="input input-bordered w-full pr-10" 
                               pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$"
                               title="Password must be at least 8 characters long and include at least one letter, one number, and one special character"
                               required />
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                            <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                        </span>
                    </div>
                    <!-- Add password strength indicator -->
                    <div class="mt-2">
                        <progress class="progress w-full" id="password-strength-meter" value="0" max="100"></progress>
                        <p class="text-xs mt-1" id="password-strength-text">Password strength: Too weak</p>
                    </div>
                </div>

                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text">Repeat Password</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="repeat_password" id="repeat_password" placeholder="Repeat Password" 
                               class="input input-bordered w-full pr-10" required />
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                            <i class="fas fa-eye toggle-password" id="toggleRepeatPassword"></i>
                        </span>
                    </div>
                </div>

                <!-- Create Account Button and Back to Login Link -->
                <div class="form-control mt-6">
                    <button class="btn bg-red-600 hover:bg-red-700 text-white">Create Account</button>
                    <div class="text-xs text-center">
                        <h1 class="text-base py-3 ">or</h1>
                        <a href="..\index.php" class="text-red-600 hover:text-red-700 text-base py-3 link-hover">Back to Login</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            gsap.from('.card', { duration: 0.5, y: -50, opacity: 0, ease: 'power1.out' });
            gsap.from('.hero img', { duration: 0.5, scale: 0.5, opacity: 0, ease: 'back.out(1.7)', delay: 0.25 });
            gsap.from('.hero h1', { duration: 0.5, y: 50, opacity: 0, ease: 'power1.out', delay: 0.5 });
            gsap.from('.form-control', { duration: 0.5, y: 50, opacity: 0, ease: 'power1.out', delay: 0.75, stagger: 0.1 });
            gsap.from('.form-control input', { duration: 0.5, x: -50, opacity: 0, ease: 'power1.out', delay: 1, stagger: 0.1 });
            gsap.from('.btn', { duration: 0.5, scale: 0.5, opacity: 0, ease: 'back.out(1.7)', delay: 1.5 });
            gsap.from('.text-xs', { duration: 0.5, y: 50, opacity: 0, ease: 'power1.out', delay: 1.75 });

            // Button hover effect
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('mouseenter', () => {
                    gsap.to(button, { scale: 1.1, duration: 0.2, ease: 'power1.out' });
                });
                button.addEventListener('mouseleave', () => {
                    gsap.to(button, { scale: 1, duration: 0.2, ease: 'power1.out' });
                });
            });

            // Toggle password visibility
            const togglePassword = document.querySelector('#togglePassword');
            const toggleRepeatPassword = document.querySelector('#toggleRepeatPassword');
            const password = document.querySelector('#password');
            const repeatPassword = document.querySelector('#repeat_password');
            
            togglePassword.addEventListener('click', function (e) {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            toggleRepeatPassword.addEventListener('click', function (e) {
                const type = repeatPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                repeatPassword.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            // Password strength checker
            const strengthMeter = document.getElementById('password-strength-meter');
            const strengthText = document.getElementById('password-strength-text');

            password.addEventListener('input', function() {
                const val = password.value;
                let score = 0;
                
                // Length check
                if (val.length >= 8) score += 25;
                
                // Uppercase check
                if (/[A-Z]/.test(val)) score += 25;
                
                // Lowercase check
                if (/[a-z]/.test(val)) score += 25;
                
                // Special character check
                if (/[^A-Za-z0-9]/.test(val)) score += 25;

                // Update progress bar
                strengthMeter.value = score;

                // Update progress bar color using daisyUI classes
                strengthMeter.className = 'progress w-full ' + 
                    (score <= 25 ? 'progress-error' : 
                     score <= 50 ? 'progress-warning' : 
                     score <= 75 ? 'progress-info' : 
                     'progress-success');

                // Update strength text
                strengthText.textContent = 'Password strength: ' + 
                    (score <= 25 ? 'Too weak' : 
                     score <= 50 ? 'Weak' : 
                     score <= 75 ? 'Medium' : 
                     'Strong');

                strengthText.className = 'text-xs mt-1 ' + 
                    (score <= 25 ? 'text-error' : 
                     score <= 50 ? 'text-warning' : 
                     score <= 75 ? 'text-info' : 
                     'text-success');
            });
        });

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>

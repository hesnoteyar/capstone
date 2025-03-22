<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.13/dist/full.min.css" rel="stylesheet" type="text/css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
    <title>Customer Login</title>
</head>
<body>
    <div class="hero bg-base-200 min-h-screen flex items-center justify-center relative">

        <!-- Alert Section -->
        <?php
        session_start();
        if (isset($_SESSION['success_message']) && !empty($_SESSION['success_message'])) {
            echo '<div class="alert alert-success shadow-lg fixed bottom-4 right-4 max-w-sm" id="success-alert">';
            echo '<div>';
            echo '<i class="fas fa-check-circle text-2xl"></i>';
            echo '<span>' . $_SESSION['success_message'] . '</span>';
            echo '</div>';
            echo '</div>';
            unset($_SESSION['success_message']); // Clear the message after displaying it
        }
        
        if (isset($_SESSION['error_message']) && !empty($_SESSION['error_message'])) {
            echo '<div class="alert alert-error shadow-lg fixed bottom-4 right-4 max-w-sm" id="error-alert">';
            echo '<div>';
            echo '<i class="fas fa-exclamation-circle text-2xl"></i>';
            echo '<span>' . $_SESSION['error_message'] . '</span>';
            echo '</div>';
            echo '</div>';
            // Clear the error message after displaying it
            unset($_SESSION['error_message']);
        }
        ?>
        <!-- End Alert Section -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('success-alert')) {
            gsap.fromTo('#success-alert', { x: -100, opacity: 0 }, { x: 0, opacity: 1, duration: 1 });
            setTimeout(() => {
            gsap.to('#success-alert', { x: 100, opacity: 0, duration: 1, onComplete: () => document.getElementById('success-alert').remove() });
            }, 5000);
            }
            if (document.getElementById('error-alert')) {
            gsap.fromTo('#error-alert', { x: -100, opacity: 0 }, { x: 0, opacity: 1, duration: 1 });
            setTimeout(() => {
            gsap.to('#error-alert', { x: 100, opacity: 0, duration: 1, onComplete: () => document.getElementById('error-alert').remove() });
            }, 5000);
            }
            });

            function closeAlert(alertId) {
            gsap.to(`#${alertId}`, { x: 100, opacity: 0, duration: 1, onComplete: () => document.getElementById(alertId).remove() });
            }
        </script>

        <div class="card bg-base-100 w-full max-w-2xl shrink-0 shadow-2xl">
            <div class="text-center lg:text-above">
                <div class="flex justify-center py-6">
                    <img src="media\small_logo.png" alt="">
                </div>
                <h1 class="py-6 text-3xl font-bold">Hi Customer!</h1>
                <p>Enter your email and password to access your account</p>
            </div>

            <form class="card-body" action="/authentication/login.php" method="post">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Email</span>
                    </label>
                    <input type="email" name="email" placeholder="Email" class="input input-bordered" required />
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Password</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" placeholder="Password" class="input input-bordered w-full pr-10" required />
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                            <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                        </span>
                    </div>
                    <label class="label">
                        <a href="#" onclick="document.getElementById('reset-modal').showModal()" class="label-text-alt link link-hover">Forgot password?</a>
                    </label>
                    <div id="password-strength" class="password-strength"></div>
                </div>
                <div class="form-control mt-6">
                    <button class="btn bg-red-600 hover:bg-red-700 text-white">Login</button>
                    <div class="text-xs text-center">
                        <h1 class="text-base py-3">or</h1>
                        <a href="page/register.php" class="text-red-600 hover:text-red-700 text-base py-3 link-hover">Create an Account</a>
                    </div>
                </div>
                <div class="text-center pb-4">

            </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <dialog id="reset-modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Reset Password</h3>
            <p class="py-4">Enter your email address to receive a password reset code.</p>
            <form action="/authentication/send_reset_otp.php" method="post">
                <div class="form-control">
                    <input type="email" name="email" placeholder="Enter your email" class="input input-bordered" required />
                </div>
                <div class="modal-action">
                    <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white">Send Reset Code</button>
                    <button type="button" class="btn" onclick="document.getElementById('reset-modal').close()">Close</button>
                </div>
            </form>
        </div>
    </dialog>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');
            
            togglePassword.addEventListener('click', function (e) {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            // Existing GSAP animations
            gsap.from('.card', { duration: 0.5, y: -50, opacity: 0, ease: 'power1.out' });
            gsap.from('.hero img', { duration: 0.5, scale: 0.5, opacity: 0, ease: 'back.out(1.7)', delay: 0.25 });
            gsap.from('.hero h1', { duration: 0.5, y: 50, opacity: 0, ease: 'power1.out', delay: 0.5 });
            gsap.from('.hero p', { duration: 0.5, y: 50, opacity: 0, ease: 'power1.out', delay: 0.75 });
            gsap.from('.form-control', { duration: 0.5, y: 50, opacity: 0, ease: 'power1.out', delay: 1, stagger: 0.1 });
            gsap.from('.form-control input', { duration: 0.5, x: -50, opacity: 0, ease: 'power1.out', delay: 1.5, stagger: 0.1 });
            gsap.from('.btn', { duration: 0.5, scale: 0.5, opacity: 0, ease: 'back.out(1.7)', delay: 2 });
            gsap.from('.text-xs', { duration: 0.5, y: 50, opacity: 0, ease: 'power1.out', delay: 2.25 });

            // Button hover effect
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('mouseenter', () => {
                    gsap.to(button, { scale: 1.1, duration: 0.2, ease: 'power1.out' });
                });
                button.addEventListener('mouseleave', () => {
                    gsap.to(button, { scale: 1, duration: 0.2, ease: 'power1.out' });
                });
            });
        });

    </script>
</body>
</html>

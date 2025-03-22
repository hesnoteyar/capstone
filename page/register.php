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

                <!-- Password Field with Toggle -->
                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text">Password</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" placeholder="Password" 
                               class="input input-bordered w-full" 
                               pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$"
                               title="Password must be at least 8 characters long and include at least one letter, one number, and one special character"
                               required />
                        <button type="button" class="btn btn-ghost btn-sm absolute top-1/2 right-2 transform -translate-y-1/2" onclick="togglePassword('password')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text">Repeat Password</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="repeat_password" id="repeat_password" placeholder="Repeat Password" 
                               class="input input-bordered w-full" required />
                        <button type="button" class="btn btn-ghost btn-sm absolute top-1/2 right-2 transform -translate-y-1/2" onclick="togglePassword('repeat_password')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
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
        });

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>

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

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
    <title>Employee Login</title>
</head>
<body>
    <div class="hero bg-base-200 min-h-screen flex items-center justify-center relative">

        <!-- Alert Section -->
        <?php
        session_start();
        if (isset($_SESSION['error_message']) && !empty($_SESSION['error_message'])) {
            echo '<div class="alert alert-error shadow-lg absolute top-4 left-4 max-w-sm">';
            echo '<div>';
            echo '<span>' . $_SESSION['error_message'] . '</span>';
            echo '</div>';
            echo '</div>';
            // Clear the error message after displaying it
            unset($_SESSION['error_message']);
        }
        ?>
        <!-- End Alert Section -->

        <div class="card bg-base-100 w-full max-w-2xl shrink-0 shadow-2xl">
            <div class="text-center lg:text-above">
                <div class="flex justify-center py-6">
                    <img src="..\media\small_logo.png" alt="">
                </div>
                <h1 class="py-6 text-3xl font-bold">Hi Employee!</h1>
                <p>Enter your email and password to access your account</p>
            </div>

            <form class="card-body" action="..\authentication\employee_login_func.php" method="post">
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
                    <input type="password" name="password" placeholder="Password" class="input input-bordered" required />
                    <label class="label">
                        <a href="#" class="label-text-alt link link-hover">Forgot password?</a>
                    </label>
                </div>
                <div class="form-control mt-6">
                    <button class="btn bg-red-600 hover:bg-red-700 text-white">Login</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

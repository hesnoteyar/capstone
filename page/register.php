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
        /* Apply the Poppins font family globally */
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>

</head>
<body>
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

            <form action="..\authentication\register_method.php" method="post" class="card-body">
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
</body>
</html>

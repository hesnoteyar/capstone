<?php
session_start();
if (!isset($_SESSION['reset_email'])) {
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
    <title>Verify OTP</title>
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center">
    <div class="card bg-base-100 w-full max-w-md shadow-xl">
        <div class="card-body">
            <h2 class="card-title justify-center">Verify OTP</h2>
            <p class="text-center">Enter the OTP sent to your email</p>
            <form action="/authentication/verify_otp.php" method="post">
                <div class="form-control">
                    <input type="text" name="otp" placeholder="Enter OTP" class="input input-bordered" required maxlength="6" />
                </div>
                <div class="form-control mt-6">
                    <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white">Verify OTP</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <!-- DaisyUI (via TailwindCSS CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.19/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-gray-100">
    <div class="flex items-center justify-center min-h-screen">
        <div class="p-8 bg-white shadow-md rounded-lg w-full max-w-sm">
            <h2 class="text-2xl font-bold text-center mb-6">OTP Verification</h2>
            <form action="otpverification_page.php" method="POST" class="space-y-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Enter OTP</span>
                    </label>
                    <input 
                        type="text" 
                        name="otp" 
                        maxlength="6" 
                        class="input input-bordered w-full" 
                        placeholder="Enter the 6-digit OTP"
                        required>
                </div>
                <button type="submit" class="btn btn-error w-full">Verify</button>
                <p class="text-sm text-center mt-4">
                    Didn't receive the OTP? 
                    <a href="resend_otp.php" class="text-blue-500 hover:underline">Resend OTP</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>

<?php
ob_start();  // Start output buffering
session_start();
include '..\authentication\db.php'; // Include your database connection
include '..\page\topnavbar.php'; // Include the navbar

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to login page if not logged in
    header("Location: ..\index.php");
    exit;
}

// Retrieve user data from the database
$id = $_SESSION['id'];
$query = "SELECT firstName, lastName, address, city, postalCode, email, profile_image FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($firstName, $lastName, $address, $city, $postalCode, $email, $profileImage);
$stmt->fetch();
$stmt->close();

// Handle form submission to save edited data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_changes'])) {
    $newFirstName = $_POST['firstName'];
    $newLastName = $_POST['lastName'];
    $newEmail = $_POST['email'];
    $newAddress = $_POST['address'];
    $newCity = $_POST['city'];
    $newPostalCode = $_POST['postalCode'];

    // Update the user data in the database
    $updateQuery = "UPDATE users SET firstName = ?, lastName = ?, email = ?, address = ?, city = ?, postalCode = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssssssi", $newFirstName, $newLastName, $newEmail, $newAddress, $newCity, $newPostalCode, $id);
    $updateStmt->execute();
    $updateStmt->close();

    $_SESSION['success_message'] = "Profile Edited Successfully!";

    // Reload the page to reflect changes
    header("Location: ..\page\profile.php");
    exit;
}

// Handle profile image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profileImage'])) {
    $targetDirectory = "..\media/";
    $targetFile = $targetDirectory . basename($_FILES["profileImage"]["name"]);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if file is an image
    if (getimagesize($_FILES["profileImage"]["tmp_name"]) !== false) {
        // Move uploaded image to the target directory
        if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $targetFile)) {
            // Update image path in the database
            $updateImageQuery = "UPDATE users SET profile_image = ? WHERE id = ?";
            $updateImageStmt = $conn->prepare($updateImageQuery);
            $updateImageStmt->bind_param("si", $targetFile, $id);
            $updateImageStmt->execute();
            $updateImageStmt->close();

            $_SESSION['success_message'] = "Profile picture updated successfully!";
        } else {
            $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
        }
    } else {
        $_SESSION['error_message'] = "File is not an image.";
    }

    header("Location: ..\page\profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Edit</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.14/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
    <script>
        // JavaScript function to toggle between editable and non-editable modes
        function toggleEdit() {
            // Toggle visibility of edit fields
            const fields = document.querySelectorAll('.editable');
            const saveButton = document.getElementById('saveButton');
            const editButton = document.getElementById('editButton');

            fields.forEach(field => {
                field.toggleAttribute('readonly');
            });
            
            // Toggle the visibility of the buttons
            saveButton.classList.toggle('hidden');
            editButton.classList.toggle('hidden');
        }
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-800 dark:text-gray-200 flex flex-col min-h-screen">



<!--Success Banner-->
<div class="flex-grow">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div id="successBanner" class="bg-green-500 text-white text-center p-4 mb-4 rounded-md">
            <?php echo $_SESSION['success_message']; ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <script>
        setTimeout(function() {
            const banner = document.getElementById('successBanner');
            if (banner) {
                banner.style.display = 'none';
            }
        }, 5000);
    </script>

    <div class="flex-grow">
        <div class="max-w-4xl mx-auto mt-10 bg-white dark:bg-gray-900 shadow-md rounded-lg p-6">
            <!-- Profile Picture -->
            <div class="text-center">
                <div class="relative w-32 h-32 mx-auto">
                    <!-- Profile picture with preview -->
                    <img id="profileImage" src="<?php echo htmlspecialchars($profileImage ?: '..\media\defaultpfp.jpg'); ?>" alt="Profile Picture" class="w-full h-full rounded-full object-cover border-4 border-gray-300 dark:border-gray-700 shadow-md">
                    <button class="absolute bottom-0 right-0 bg-gray-200 dark:bg-gray-700 p-1 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-1.5A2.5 2.5 0 1112.5 7.5M16.5 12H7.5M9 15h6" />
                        </svg>
                    </button>
                </div>
                <h2 class="mt-4 text-xl font-semibold"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>

                <!-- Photo selection and save buttons -->
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="profileImage" accept="image/*" id="imageInput" class="hidden" onchange="previewImage(event)">
                    <button type="button" onclick="document.getElementById('imageInput').click()" class="mt-4 px-6 py-2 bg-gray-100 text-black font-medium rounded-lg hover:bg-gray-200 shadow">Select Photo</button>
                    <button type="submit" name="saveImage" class="mt-4 px-6 py-2 bg-red-500 text-white font-medium rounded-lg hover:bg-red-600 shadow">Save Photo</button>
                </form>
            </div>

            <script>
                // JavaScript function to preview the image before upload
                function previewImage(event) {
                    const file = event.target.files[0];
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        document.getElementById('profileImage').src = e.target.result;
                    };

                    if (file) {
                        reader.readAsDataURL(file);
                    }
                }
            </script>

            <!-- Personal Info -->
            <div class="mt-6">
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">Personal Info</h3>
                    </div>
                    <form method="POST" action="">
                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-red-600 dark:text-gray-400 mb-2">Full Name</p>
                                <input type="text" name="firstName" value="<?php echo htmlspecialchars($firstName); ?>" class="editable text-gray-800 dark:text-gray-200 mb-4 px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg w-full" readonly>
                                <input type="text" name="lastName" value="<?php echo htmlspecialchars($lastName); ?>" class="editable text-gray-800 dark:text-gray-200 mb-4 px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg w-full" readonly>
                            </div>
                            <div>
                                <p class="text-sm text-red-600 dark:text-gray-400 mb-2">Email Address</p>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="editable text-gray-800 dark:text-gray-200 mb-4 px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg w-full" readonly>
                            </div>
                            <div>
                                <p class="text-sm text-red-600 dark:text-gray-400 mb-2">Age</p>
                                <p class="text-gray-800 dark:text-gray-200 mb-4">21</p>
                            </div>
                            <div>
                                <p class="text-sm text-red-600 dark:text-gray-400 mb-2">Phone</p>
                                <p class="text-gray-800 dark:text-gray-200">09123456789</p>
                            </div>
                        </div>

                        <!-- Location Info -->
                        <div class="mt-6">
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">Location</h3>
                                <div class="mt-4">
                                    <p class="text-sm text-red-600 dark:text-gray-400 mb-2">Address</p>
                                    <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" class="editable text-gray-800 dark:text-gray-200 mb-4 px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg w-full" readonly>

                                    <p class="text-sm text-red-600 dark:text-gray-400 mb-2">City</p>
                                    <input type="text" name="city" value="<?php echo htmlspecialchars($city); ?>" class="editable text-gray-800 dark:text-gray-200 mb-4 px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg w-full" readonly>

                                    <p class="text-sm text-red-600 dark:text-gray-400 mb-2">Postal Code</p>
                                    <input type="text" name="postalCode" value="<?php echo htmlspecialchars($postalCode); ?>" class="editable text-gray-800 dark:text-gray-200 mb-4 px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg w-full" readonly>
                                </div>
                            </div>
                        </div>

                            <!-- Buttons -->
                            <div class="text-center mt-6 flex justify-center space-x-4">
                                <button type="button" id="editButton" onclick="toggleEdit()" class="px-6 py-2 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-medium rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 shadow">
                                    Edit Details
                                </button>

                                <!-- Contact Customer Button -->
                                <button type="button" id="contactCustomerButton" onclick="contactCustomer()" class="px-6 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 shadow">
                                     Customer Support
                                </button>

                                <!-- Save Button, hidden initially -->
                                <button type="submit" name="save_changes" id="saveButton" class="px-6 py-2 bg-red-700 text-white font-medium rounded-lg hover:bg-red-800 shadow hidden">
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
<br>
<br>
    <!-- Footer -->
    <?php include '..\page\footer.php'; ?>

    <?php
    ob_end_flush(); // End output buffering and flush the output
    ?>
</body>
</html>

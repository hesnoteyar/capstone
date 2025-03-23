<?php
ob_start();  // Start output buffering
session_start();
include '../authentication/db.php'; // Include your database connection
include 'topnavbar.php'; // Include the navbar

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to login page if not logged in
    header("Location: ../index.php");
    exit;
}

// Retrieve user data from the database
$id = $_SESSION['id'];
$query = "SELECT firstName, lastName, address, city, postalCode, email, profile_image, is_active FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($firstName, $lastName, $address, $city, $postalCode, $email, $profileImage, $isActive);
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
    header("Location: ../page/profile.php");
    exit;
}

// Handle profile image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profileImage'])) {
    $targetDirectory = "../media/";
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

    header("Location: ../page/profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Edit</title>

    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.14/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom, #f3f4f6, #e5e7eb);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .profile-card {
            transition: transform 0.3s ease;
        }
        .profile-card:hover {
            transform: translateY(-5px);
        }
        .avatar img {
            transition: transform 0.3s ease;
        }
        .avatar:hover img {
            transform: scale(1.05);
        }
    </style>
    
<script>
    // JavaScript function to toggle between editable and non-editable modes
    function toggleEdit() {
        // Toggle visibility of edit fields
        const fields = document.querySelectorAll('input');
        const saveButton = document.getElementById('saveButton');
        const editButton = document.getElementById('editButton');

        // Toggle the readonly attribute of input fields
        fields.forEach(field => {
            if (field.hasAttribute('readonly')) {
                field.removeAttribute('readonly');
                field.classList.add('border', 'border-error'); // Add border for clarity
            } else {
                field.setAttribute('readonly', true);
                field.classList.remove('border', 'border-primary');
            }
        });

        // Toggle visibility of buttons
        saveButton.classList.toggle('hidden');
        editButton.classList.toggle('hidden');
    }

    // JavaScript function to open the favorites modal
    function openFavoritesModal() {
        document.getElementById('favorites-modal').classList.remove('hidden');
        document.body.classList.add('no-scroll'); // Disable background scrolling

        // GSAP animations for favorites modal elements
        gsap.from('.modal-header', { duration: 0.5, y: -50, opacity: 0, ease: 'power1.out' });
        gsap.from('.modal-body', { duration: 0.5, y: 50, opacity: 0, ease: 'power1.out', delay: 0.25 });
        gsap.from('.card', { duration: 0.5, y: 50, opacity: 0, ease: 'power1.out', delay: 0.5, stagger: 0.1 });
    }

    // JavaScript function to close the favorites modal
    function closeFavoritesModal() {
        document.getElementById('favorites-modal').classList.add('hidden');
        document.body.classList.remove('no-scroll'); // Enable background scrolling
    }

    function deleteFavorite(productId) {
        fetch('delete_favorite.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showBanner('success', 'Favorite item deleted successfully!');
                // Remove the deleted item from the DOM
                document.querySelector(`button[onclick="deleteFavorite(${productId})"]`).closest('.card').remove();
            } else {
                showBanner('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showBanner('error', 'An error occurred while deleting the favorite item.');
        });
    }

    function showBanner(type, message) {
        const banner = document.createElement('div');
        banner.classList.add('alert', 'w-full', type === 'error' ? 'alert-error' : 'alert-success', 'fixed', 'top-5', 'left-0', 'z-50', 'p-2', 'text-m');

        // Set a specific width for the banner, e.g., 80% of the screen width or a fixed pixel width
        banner.style.width = '40%';  // Adjust this value to change the banner width
        banner.style.margin = '0 auto';  // Centers the banner

        const bannerContent = document.createElement('div');
        bannerContent.classList.add('flex', 'items-center');
        
        const icon = document.createElement('span');
        icon.classList.add('material-icons', 'mr-2');
        icon.textContent = type === '' ? '' : '';

        const text = document.createElement('span');
        text.textContent = message;
        
        bannerContent.appendChild(icon);
        bannerContent.appendChild(text);
        banner.appendChild(bannerContent);

        // Append to body
        document.body.appendChild(banner);

        // Optionally remove the banner after a few seconds
        setTimeout(() => {
            banner.remove();
        }, 5000); // Remove after 5 seconds
    }

    // Check if the modal should be open after page reload
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('favoritesModalOpen')) {
            openFavoritesModal();
        }

        // GSAP animations
        gsap.from('.avatar', { duration: 0.5, scale: 0.5, opacity: 0, ease: 'back.out(1.7)' });
        gsap.from('.card', { duration: 0.5, y: 50, opacity: 0, ease: 'power1.out', stagger: 0.1 });
        gsap.from('.btn', { duration: 0.5, scale: 0.5, opacity: 0, ease: 'back.out(1.7)', delay: 0.5 });
    });

    // Add this JavaScript function where the other functions are
    function openFaqModal() {
        document.getElementById('faq-modal').classList.remove('hidden');
        document.body.classList.add('no-scroll');
    }

    function closeFaqModal() {
        document.getElementById('faq-modal').classList.add('hidden');
        document.body.classList.remove('no-scroll');
    }

    // Update the previewImage function
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const profileImage = document.getElementById('profileImage');
                profileImage.src = e.target.result;
                
                // Add a smooth transition effect
                profileImage.style.transition = 'opacity 0.3s ease';
                profileImage.style.opacity = '0';
                setTimeout(() => {
                    profileImage.style.opacity = '1';
                }, 50);
            }
            
            reader.readAsDataURL(file);
        }
    }
</script>

</head>
<body class="min-h-screen">
    <div class="content px-4 py-8">
        <!-- Success Banner with improved styling -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div id="successBanner" class="alert alert-success shadow-lg max-w-2xl mx-auto mb-6 animate-slideDown">
                <div class="flex items-center">
                    <svg class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium"><?php echo $_SESSION['success_message']; ?></span>
                </div>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <div class="max-w-5xl mx-auto">
            <!-- Profile Card -->
            <div class="profile-card glass-effect rounded-box p-8 shadow-xl">
                <!-- Profile Header -->
                <div class="text-center space-y-4">
                    <div class="avatar online">
                        <div class="w-40 h-40 rounded-full ring ring-error ring-offset-base-100 ring-offset-2">
                            <img id="profileImage" src="<?php echo htmlspecialchars($profileImage ?: '../media/defaultpfp.jpg'); ?>" 
                                 class="mask mask-circle" alt="Profile Picture">
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h2 class="text-2xl font-bold text-base-content"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>
                        <div class="flex items-center justify-center gap-2 mt-2">
                            <div class="badge <?php echo $isActive ? 'badge-success' : 'badge-error'; ?> gap-2">
                                <?php echo $isActive ? 'Verified Account' : 'Unverified Account'; ?>
                            </div>
                            <?php if (!$isActive): ?>
                                <a href="../authentication/otp_input.php" class="btn btn-xs btn-error">Verify Now</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Photo Upload Form with improved styling and preview -->
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div class="flex justify-center gap-4">
                            <label for="imageInput" class="btn btn-outline btn-error">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Select Photo
                            </label>
                            <input type="file" id="imageInput" name="profileImage" accept="image/*" class="hidden" onchange="previewImage(event)">
                            <button type="submit" name="saveImage" class="btn btn-error">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                Save Photo
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Personal Information -->
                <div class="mt-8">
                    <form method="POST" action="" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Personal Details -->
                            <div class="card bg-base-200 shadow-lg">
                                <div class="card-body">
                                    <h3 class="card-title text-error">Personal Details</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="label text-sm text-error">Full Name</label>
                                            <input type="text" name="firstName" value="<?php echo htmlspecialchars($firstName); ?>" class="input input-bordered w-full mb-4" readonly>
                                            <input type="text" name="lastName" value="<?php echo htmlspecialchars($lastName); ?>" class="input input-bordered w-full mb-4" readonly>
                                        </div>
                                        <div>
                                            <label class="label text-sm text-error">Email Address</label>
                                            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="input input-bordered w-full mb-4" readonly>
                                        </div>
                                        <div>
                                            <label class="label text-sm text-error">Age</label>
                                            <p class="text-base-content mb-4">21</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Location Info -->
                            <div class="card bg-base-200 shadow-lg">
                                <div class="card-body">
                                    <h3 class="card-title text-error">Location</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="label text-sm text-error">Address</label>
                                            <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" class="input input-bordered w-full mb-4" readonly>
                                        </div>
                                        <div>
                                            <label class="label text-sm text-error">City</label>
                                            <input type="text" name="city" value="<?php echo htmlspecialchars($city); ?>" class="input input-bordered w-full mb-4" readonly>
                                        </div>
                                        <div>
                                            <label class="label text-sm text-error">Postal Code</label>
                                            <input type="text" name="postalCode" value="<?php echo htmlspecialchars($postalCode); ?>" class="input input-bordered w-full mb-4" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-8 flex justify-center gap-4">
                            <button type="button" id="editButton" onclick="toggleEdit()" 
                                    class="btn btn-outline btn-error gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Details
                            </button>
                            
                            <button type="button" id="contactCustomerButton" onclick="openFaqModal()" class="btn btn-error bg-gray-400 hover:bg-red-500 text-white">FAQs</button>

                            <button type="submit" name="save_changes" id="saveButton" class="btn btn-success bg-gray-400 hover:bg-red-500 text-white hidden">Save Changes</button>

                            <button type="button" onclick="openFavoritesModal()" class="btn btn-error bg-gray-400 hover:bg-red-500 text-white">Favorites</button> <!-- New Favorites Button -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Favorites Modal -->
    <div id="favorites-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-6xl p-8 modal-content" style="max-height: 80vh; overflow-y: scroll;"> <!-- Changed overflow-y to scroll -->
            <div class="modal-header flex justify-between items-center">
                <h2 class="text-2xl font-bold">Favorites</h2>
                <button class="btn btn-sm btn-circle btn-error" onclick="closeFavoritesModal()">✕</button>
            </div>
            <div class="modal-body mt-4">
                <!-- Fetch and display favorite items here -->
                <?php
                $favoritesQuery = "SELECT product.product_id, product.name, product.description, product.price, product.image FROM favorites JOIN product ON favorites.productid = product.product_id WHERE favorites.userid = ?";
                $favoritesStmt = $conn->prepare($favoritesQuery);
                $favoritesStmt->bind_param("i", $id);
                $favoritesStmt->execute();
                $favoritesResult = $favoritesStmt->get_result();

                if ($favoritesResult->num_rows > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php while ($favorite = $favoritesResult->fetch_assoc()): 
                            $imageData = base64_encode($favorite['image']);
                            $imageSrc = 'data:image/jpeg;base64,' . $imageData;
                        ?>
                            <div class="card bg-base-100 shadow-lg">
                                <figure>
                                    <img src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($favorite['name']) ?>" class="card-image object-cover w-full h-48"> <!-- Fixed image size -->
                                </figure>
                                <div class="card-body">
                                    <h2 class="card-title"><?= htmlspecialchars($favorite['name']) ?></h2>
                                    <p><?= htmlspecialchars($favorite['description']) ?></p>
                                    <p>Price: ₱<?= number_format($favorite['price'], 2) ?></p>
                                    <button class="btn btn-error mt-4" onclick="deleteFavorite(<?= $favorite['product_id'] ?>)">Delete</button> <!-- Delete button -->
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>There is no favorite item.</p> <!-- Updated message -->
                <?php endif; ?>
                <?php $favoritesStmt->close(); ?>
            </div>
        </div>
    </div>

    <!-- FAQ Modal -->
    <div id="faq-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-base-100 rounded-lg shadow-lg w-full max-w-4xl p-8 modal-content" style="max-height: 85vh; overflow-y: auto;">
            <div class="modal-header flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Frequently Asked Questions</h2>
                <button class="btn btn-sm btn-circle btn-error" onclick="closeFaqModal()">✕</button>
            </div>

            <!-- General Information -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold mb-4 text-error">About ABA Racing</h3>
                <div class="join join-vertical w-full">
                    <div class="collapse collapse-arrow join-item border border-base-300">
                        <input type="radio" name="faq-about" checked="checked" /> 
                        <div class="collapse-title text-lg font-medium">
                            What is ABA Racing?
                        </div>
                        <div class="collapse-content"> 
                            <p>ABA Racing is a motorcycle parts and repair shop that provides high-quality parts, accessories, and repair services to motorcycle enthusiasts and riders.</p>
                        </div>
                    </div>
                    <div class="collapse collapse-arrow join-item border border-base-300">
                        <input type="radio" name="faq-about" /> 
                        <div class="collapse-title text-lg font-medium">
                            Where is ABA Racing located?
                        </div>
                        <div class="collapse-content"> 
                            <p>Our shop is located at Blk 23 - Suha St, Taytay, 1920 Rizal. You can visit us during our operating hours for purchases and repair services.</p>
                        </div>
                    </div>
                    <div class="collapse collapse-arrow join-item border border-base-300">
                        <input type="radio" name="faq-about" /> 
                        <div class="collapse-title text-lg font-medium">
                            What are your operating hours?
                        </div>
                        <div class="collapse-content"> 
                            <p>We are open from 8:00 Am - 6:00 Pm, Monday to Saturday.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products & Services -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold mb-4 text-error">Products & Services</h3>
                <div class="join join-vertical w-full">
                    <div class="collapse collapse-arrow join-item border border-base-300">
                        <input type="radio" name="faq-products" /> 
                        <div class="collapse-title text-lg font-medium">
                            What motorcycle parts do you sell?
                        </div>
                        <div class="collapse-content"> 
                            <ul class="list-disc pl-4">
                                <li>Engine parts</li>
                                <li>Tires and wheels</li>
                                <li>Brakes and suspension</li>
                                <li>Batteries and electrical components</li>
                                <li>Accessories and riding gear</li>
                            </ul>
                        </div>
                    </div>
                    <div class="collapse collapse-arrow join-item border border-base-300">
                        <input type="radio" name="faq-products" /> 
                        <div class="collapse-title text-lg font-medium">
                            Do you offer repair and maintenance services?
                        </div>
                        <div class="collapse-content"> 
                            <p>Yes! We provide expert repair and maintenance services, including:</p>
                            <ul class="list-disc pl-4 mt-2">
                                <li>Engine repairs</li>
                                <li>Brake servicing</li>
                                <li>Oil changes</li>
                                <li>Electrical diagnostics</li>
                                <li>Custom modifications</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders & Payments -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold mb-4 text-error">Orders & Payments</h3>
                <div class="join join-vertical w-full">
                    <div class="collapse collapse-arrow join-item border border-base-300">
                        <input type="radio" name="faq-orders" /> 
                        <div class="collapse-title text-lg font-medium">
                            What payment methods do you accept?
                        </div>
                        <div class="collapse-content"> 
                            <p>We accept:</p>
                            <ul class="list-disc pl-4 mt-2">
                                <li>Cash</li>
                                <li>Bank transfers</li>
                                <li>Mayapay</li>
                                <li>Gcash</li>
                                <li>Credit/Debit Card</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping & Pickup -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold mb-4 text-error">Shipping & Pickup</h3>
                <div class="join join-vertical w-full">
                    <div class="collapse collapse-arrow join-item border border-base-300">
                        <input type="radio" name="faq-shipping" /> 
                        <div class="collapse-title text-lg font-medium">
                            Do you offer delivery?
                        </div>
                        <div class="collapse-content"> 
                            <p>At the moment, we do not offer delivery services. However, you can visit our shop for in-store pickup.</p>
                        </div>
                    </div>
                    <div class="collapse collapse-arrow join-item border border-base-300">
                        <input type="radio" name="faq-shipping" /> 
                        <div class="collapse-title text-lg font-medium">
                            Can I pick up my order at the shop?
                        </div>
                        <div class="collapse-content"> 
                            <p>Yes! Once your order is confirmed, you can pick it up at our store during business hours.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Support -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold mb-4 text-error">Customer Support</h3>
                <div class="join join-vertical w-full">
                    <div class="collapse collapse-arrow join-item border border-base-300">
                        <input type="radio" name="faq-support" /> 
                        <div class="collapse-title text-lg font-medium">
                            How can I contact ABA Racing?
                        </div>
                        <div class="collapse-content"> 
                            <p>You can reach us via:</p>
                            <ul class="list-disc pl-4 mt-2">
                                <li>Email: abaracing@gmail.com</li>
                                <li>Social Media: Aba Racing on Facebook</li>
                            </ul>
                        </div>
                    </div>
                    <div class="collapse collapse-arrow join-item border border-base-300">
                        <input type="radio" name="faq-support" /> 
                        <div class="collapse-title text-lg font-medium">
                            What is your return policy?
                        </div>
                        <div class="collapse-content"> 
                            <p>We accept returns or exchanges within 5 days, provided the item is unused and in its original packaging. Some exclusions apply, such as electrical components.</p>
                        </div>
                    </div>
                    <div class="collapse collapse-arrow join-item border border-base-300">
                        <input type="radio" name="faq-support" /> 
                        <div class="collapse-title text-lg font-medium">
                            Do you offer warranties on products?
                        </div>
                        <div class="collapse-content"> 
                            <p>Yes, selected products come with warranties. Please check with our staff for warranty details before purchasing.</p>
                        </div>
                    </div>
                    <div class="collapse collapse-arrow join-item border border-base-300">
                        <input type="radio" name="faq-support" /> 
                        <div class="collapse-title text-lg font-medium">
                            What if I receive a defective product?
                        </div>
                        <div class="collapse-content"> 
                            <p>If you receive a defective item, please contact us within 7 days for assistance. We will assess the issue and provide a solution, such as a replacement or repair.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (isset($_SESSION['success_message'])) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', () => {
                showBanner('success', '" . $_SESSION['success_message'] . "');
            });
        </script>";
        unset($_SESSION['success_message']);
    }
    ?>

    <br>
    <br>

    <!-- Footer -->
    <?php include '../page/footer.php'; ?>

    <?php ob_end_flush(); ?>
</body>
</html>

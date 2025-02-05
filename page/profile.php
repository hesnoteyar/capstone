<?php
ob_start();  // Start output buffering
session_start();
include '../authentication/db.php'; // Include your database connection
include '../page/topnavbar.php'; // Include the navbar

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
<html lang="en" class="dark">
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
            overflow: hidden; /* Hide scrollbar for the entire page */
        }
        html, body {
            height: 100%;
            overflow: hidden;
        }
        .content {
            height: 100%;
            overflow-y: scroll; /* Allow scrolling within the content */
        }
        /* Hide scrollbar for the modal but keep it scrollable */
        .modal-content::-webkit-scrollbar {
            display: none;
        }
        .no-scroll {
            overflow: hidden;
        }
        .modal-content {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
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
</script>

</head>
<body class="bg-base-200 flex flex-col min-h-screen">
<div class="content">
  <!-- Success Banner -->
  <div class="flex-grow">
    <?php if (isset($_SESSION['success_message'])): ?>
      <div id="successBanner" class="alert alert-success shadow-lg mb-4">
        <div>
          <span><?php echo $_SESSION['success_message']; ?></span>
        </div>
      </div>
      <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <script>
      setTimeout(function () {
        const banner = document.getElementById('successBanner');
        if (banner) {
          banner.style.display = 'none';
        }
      }, 5000);
    </script>

    <div class="flex-grow">
      <div class="max-w-4xl mx-auto mt-10 bg-base-100 shadow-md rounded-lg p-6">
        <!-- Profile Picture -->
        <div class="text-center">
          <div class="avatar relative w-32 h-32 mx-auto">
            <!-- Profile picture with preview -->
            <img id="profileImage" src="<?php echo htmlspecialchars($profileImage ?: '../media/defaultpfp.jpg'); ?>" class="w-full h-full rounded-full ring ring-error ring-offset-base-100 ring-offset-2 shadow-lg">
            <button class="absolute bottom-0 right-0 btn btn-circle btn-sm bg-base-300 text-base-content">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-1.5A2.5 2.5 0 1112.5 7.5M16.5 12H7.5M9 15h6" />
              </svg>
            </button>
          </div>
          <h2 class="mt-4 text-xl font-bold"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>

          <!-- Photo selection and save buttons -->
          <form method="POST" enctype="multipart/form-data">
            <input type="file" name="profileImage" accept="image/*" id="imageInput" class="hidden" onchange="previewImage(event)">
            <button type="button" onclick="document.getElementById('imageInput').click()" class="btn btn-outline mt-4">Select Photo</button>
            <button type="submit" name="saveImage" class="btn btn-error mt-4">Save Photo</button>
          </form>

          <div class="mt-4 flex flex-col items-center justify-center">
    <h2 class="text-lg font-semibold">
        Verification Status:
        <span class="<?php echo $isActive ? 'text-green-600' : 'text-red-600'; ?>">
            <?php echo $isActive ? 'Verified' : 'Not Verified'; ?>
        </span>
    </h2>
    <?php if (!$isActive): ?>
        <form method="POST" action="../authentication/generate_otp.php" class="mt-2">
            <button type="submit" class="btn btn-error">
                Verify Now
            </button>
        </form>
    <?php endif; ?>
</div>

        </div>

        <script>
          function previewImage(event) {
            const file = event.target.files[0];
            const reader = new FileReader();

            reader.onload = function (e) {
              document.getElementById('profileImage').src = e.target.result;
            };

            if (file) {
              reader.readAsDataURL(file);
            }
          }
        </script>

        <!-- Personal Info -->
        <div class="mt-6">
          <div class="card bg-base-200 shadow-lg">
            <div class="card-body">
              <h3 class="card-title">Personal Info</h3>
              <form method="POST" action="">
                <div class="grid grid-cols-2 gap-4">
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
                  <div>
                    <label class="label text-sm text-error">Phone</label>
                    <p class="text-base-content">09123456789</p>
                  </div>
                </div>

                <!-- Location Info -->
                <div class="mt-6">
                  <div class="card bg-base-200 shadow-lg">
                    <div class="card-body">
                      <h3 class="card-title">Location</h3>
                      <div>
                        <label class="label text-sm text-error">Address</label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" class="input input-bordered w-full mb-4" readonly>

                        <label class="label text-sm text-error">City</label>
                        <input type="text" name="city" value="<?php echo htmlspecialchars($city); ?>" class="input input-bordered w-full mb-4" readonly>

                        <label class="label text-sm text-error">Postal Code</label>
                        <input type="text" name="postalCode" value="<?php echo htmlspecialchars($postalCode); ?>" class="input input-bordered w-full mb-4" readonly>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Buttons -->
                <div class="text-center mt-6 flex justify-center space-x-4">
                <button type="button" id="editButton" onclick="toggleEdit()" class="btn btn-outline bg-gray-400 hover:bg-red-500 text-white">Edit Details</button>

                <button type="button" id="contactCustomerButton" onclick="contactCustomer()" class="btn btn-error bg-gray-400 hover:bg-red-500 text-white">Customer Support</button>

                <button type="submit" name="save_changes" id="saveButton" class="btn btn-success bg-gray-400 hover:bg-red-500 text-white hidden">Save Changes</button>

                <button type="button" onclick="openFavoritesModal()" class="btn btn-error bg-gray-400 hover:bg-red-500 text-white">Favorites</button> <!-- New Favorites Button -->

                </div>
              </form>
            </div>
          </div>
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
</div>
</body>

</html>

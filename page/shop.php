<?php
    include '..\page\topnavbar.php';
    include '..\authentication\db.php';

    $category = isset($_GET['category']) ? $_GET['category'] : 'All';
    $priceRange = isset($_GET['price_range']) ? $_GET['price_range'] : 10000;

    $sql = "SELECT p.name AS product_name, p.description, p.image_url, c.name AS category_name 
            FROM Product p 
            JOIN Category c ON p.category_id = c.category_id 
            WHERE 1=1";

    if ($category !== 'All') {
        $sql .= " AND LOWER(c.name) = LOWER('" . $conn->real_escape_string($category) . "')";
    }

    $sql .= " AND p.price <= " . (int)$priceRange;


    $result = $conn->query($sql);

    if ($result === false) {
        echo "Error: " . $conn->error; 
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="min-h-screen flex flex-col bg-base-100 text-base-content" style="--primary: #E53935;">
    <div class="container mx-auto mt-10 flex-grow">
        
        <!-- Search Input centered above the cards -->
        <div class="w-full text-center mb-6">
            <input type="text" placeholder="Search..." class="input input-bordered w-1/2 p-2 rounded-md" />
            <button class="bg-red-700 text-white px-4 py-2 ml-2 rounded-md hover:bg-red-800 transition duration-300 ease-in-out">
                Search
            </button>
        </div>

        <div class="flex">
            <!-- Left Sidebar (Filters) with fixed height and scroll -->
            <form action="" method="GET" class="w-1/4 p-4 bg-base-200 text-base-content mr-6 h-96 overflow-y-auto rounded-md shadow-lg">
                <h3 class="font-bold text-xl mb-4">Filters</h3>
                <div class="mb-4">
                    <label class="block">Category</label>
                    <select name="category" class="mt-2 p-2 w-full border rounded-md">
                        <option value="All" <?= $category === 'All' ? 'selected' : ''; ?>>All</option>
                        <option value="Motorcycle" <?= $category === 'Motorcycle' ? 'selected' : ''; ?>>Motorcycle</option>
                        <option value="Car" <?= $category === 'Car' ? 'selected' : ''; ?>>Car</option>
                        <option value="Accessories" <?= $category === 'Accessories' ? 'selected' : ''; ?>>Accessories</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block">Price Range</label>
                    <input type="range" name="price_range" min="500" max="10000" value="<?= $priceRange; ?>" class="w-full mt-2 accent-red-600" />
                </div>
                <div class="mb-4">
                    <!--OTHER FILTER-->
                </div>
                <button type="submit" class="bg-red-700 text-white px-4 py-2 rounded-md hover:bg-red-800 transition duration-300 ease-in-out">
                    Apply Filters
                </button>
            </form>

            <!-- Separation Line -->
            <div class="border-l-2 border-gray-300 mx-4"></div>

            <!-- Cards Section -->
            <div class="w-3/4 flex flex-wrap justify-around gap-4 p-4">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $productName = htmlspecialchars($row['product_name']);
                        $description = htmlspecialchars($row['description']);
                        $categoryName = htmlspecialchars($row['category_name']);
                        $imageUrl = htmlspecialchars($row['image_url']);  // Fetch the image URL
                        
                        echo '<div class="card bg-base-100 w-80 shadow-lg">';
                        echo '    <figure>';
                        echo '        <img src="' . $imageUrl . '" alt="' . $productName . '" class="w-full h-48 object-cover" />'; 
                        echo '    </figure>';
                        echo '    <div class="card-body">';
                        echo '        <h2 class="card-title">';
                        echo '            ' . $productName . ' ';
                        echo '            <div class="badge badge-error text-white">NEW</div>'; // Red badge
                        echo '        </h2>';
                        echo '        <p>' . $description . '</p>';
                        echo '        <div class="card-actions justify-end">';
                        echo '            <div class="badge badge-outline">' . $categoryName . '</div>';
                        echo '        </div>';
                        echo '    </div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No products found.</p>';
                }

                $conn->close();
                ?>
            </div>

        </div>
    </div>

    <!-- Footer Section -->
    <?php
        include '..\page\footer.php';
    ?>
</body>
</html>

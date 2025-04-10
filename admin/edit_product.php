<?php
session_start();
include '../includes/db.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch product details
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header("Location: products.php");
        exit();
    }
} else {
    header("Location: products.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $main_image = $_POST['main_image']; // Get the selected main image

    // Handle image uploads
    $upload_dir = '../assets/images/products/';
    $uploaded_images = json_decode($product['images'], true); // Existing images

    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file_name = basename($_FILES['images']['name'][$key]);
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($tmp_name, $file_path)) {
                $uploaded_images[] = $file_path; // Add new images to the array
            }
        }
    }

    // If no main image is selected, default to the first image
    if (empty($main_image) && !empty($uploaded_images)) {
        $main_image = $uploaded_images[0];
    }

    // Update the database
    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, images = ?, main_image = ? WHERE product_id = ?");
    $stmt->execute([$name, $description, $price, $stock, json_encode($uploaded_images), $main_image, $product_id]);

    header("Location: products.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Shazada.com</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Shazada.com</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transactions.php">Transactions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="concerns.php">Customer Concerns</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logistics.php">Logistics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="account.php">Account</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Edit Product</h1>
                <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo $product['price']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="stock" class="form-label">Stock</label>
                <input type="number" class="form-control" id="stock" name="stock" value="<?php echo $product['stock']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="images" class="form-label">Upload Images</label>
                <input type="file" class="form-control" id="images" name="images[]" multiple>
            </div>
            <!-- Display existing images and allow selection of main image -->
            <div class="mb-3">
                <label class="form-label">Existing Images</label>
                <div id="existing-images">
                    <?php
                    $existing_images = json_decode($product['images'], true);
                    if (!empty($existing_images)) {
                        foreach ($existing_images as $image) {
                            $is_main_image = ($image === $product['main_image']);
                            echo '<div class="mb-2">
                                    <img src="' . htmlspecialchars($image) . '" alt="Product Image" style="max-width: 100px; margin-right: 10px;">
                                    <input type="radio" name="main_image" value="' . htmlspecialchars($image) . '" ' . ($is_main_image ? 'checked' : '') . '> Set as Main Image
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeImage(this, \'' . htmlspecialchars($image) . '\')">Remove</button>
                                </div>';
                        }
                    } else {
                        echo '<p>No images uploaded.</p>';
                    }
                    ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Product</button>
        </form>
    </div>
    
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>

    <script>
function removeImage(button, imagePath) {
    if (confirm('Are you sure you want to remove this image?')) {
        // Send an AJAX request to remove the image
        fetch('remove_image.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: <?php echo $product['product_id']; ?>,
                image_path: imagePath
            })
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  button.parentElement.remove(); // Remove the image from the DOM
              } else {
                  alert('Failed to remove image.');
              }
          });
    }
}
</script>

</body>
</html>
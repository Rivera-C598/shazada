<?php
session_start();
include '../includes/db.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'];
$image_path = $data['image_path'];

// Fetch the current images and main image
$stmt = $conn->prepare("SELECT images, main_image FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if ($product) {
    $images = json_decode($product['images'], true);

    // Remove the image from the array
    $images = array_filter($images, function($image) use ($image_path) {
        return $image !== $image_path;
    });

    // If the removed image was the main image, set a new main image
    $main_image = $product['main_image'];
    if ($main_image === $image_path) {
        $main_image = !empty($images) ? $images[0] : ''; // Set to the first image or empty
    }

    // Update the database
    $stmt = $conn->prepare("UPDATE products SET images = ?, main_image = ? WHERE product_id = ?");
    $stmt->execute([json_encode($images), $main_image, $product_id]);

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
<?php
session_start();

// Product creation is admin-only because it changes the live catalogue.
if (!isset($_SESSION['admin_id'])) {
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/product_catalogue_options.php';

// Only accept real form submissions from the create-product page.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/product_create.php');
    exit();
}

$name = trim($_POST['ProductName'] ?? '');
$description = trim($_POST['Description'] ?? '');
$category = trim($_POST['Category'] ?? '');
$price = trim($_POST['Price'] ?? '');
$stock = trim($_POST['Stock'] ?? '');

// Basic required-field validation first so the admin gets quick feedback.
if ($name === '' || $description === '' || $category === '' || $price === '' || $stock === '') {
    $_SESSION['error'] = 'Please fill in every product field.';
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/product_create.php');
    exit();
}

if (!is_numeric($price) || (float) $price < 0 || !is_numeric($stock) || (int) $stock < 0) {
    $_SESSION['error'] = 'Price and stock must be valid positive values.';
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/product_create.php');
    exit();
}

if (!is_valid_product_category($category)) {
    $_SESSION['error'] = 'Please choose a valid product category.';
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/product_create.php');
    exit();
}

// The upload helper does the heavier validation around file type and destination path.
[$uploadOk, $uploadError, $imagePath] = store_uploaded_product_image($_FILES['ImageFile'] ?? []);
if (!$uploadOk) {
    $_SESSION['error'] = $uploadError ?? 'Please choose a valid product image.';
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/product_create.php');
    exit();
}

try {
    // Write the product once all text fields and the image upload have already been validated.
    $stmt = $db->prepare(
        'INSERT INTO product (ProductName, Description, Category, ImagePath, Price, Stock)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$name, $description, $category, $imagePath, (float) $price, (int) $stock]);
    $_SESSION['success'] = 'Product created successfully.';
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/products.php');
    exit();
} catch (PDOException $e) {
    // Clean up the uploaded file if the database insert fails after the upload succeeded.
    remove_uploaded_product_image($imagePath);
    $_SESSION['error'] = 'Could not create this product right now.';
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/product_create.php');
    exit();
}

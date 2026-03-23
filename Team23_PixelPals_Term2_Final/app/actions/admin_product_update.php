<?php
session_start();

// Product edits stay admin-only because they change the live catalogue.
if (!isset($_SESSION['admin_id'])) {
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/product_catalogue_options.php';

// Only accept real form submissions from the edit-product page.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/products.php');
    exit();
}

$id = (int) ($_POST['id'] ?? 0);
$name = trim($_POST['ProductName'] ?? '');
$description = trim($_POST['Description'] ?? '');
$category = trim($_POST['Category'] ?? '');
$price = trim($_POST['Price'] ?? '');
$stock = trim($_POST['Stock'] ?? '');
$currentImagePath = trim($_POST['CurrentImagePath'] ?? '');

// Validate the main fields first before touching uploads or the database.
if ($id <= 0 || $name === '' || $description === '' || $category === '' || $price === '' || $stock === '') {
    $_SESSION['error'] = 'Please fill in every product field.';
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/product_edit.php?id=' . $id);
    exit();
}

if (!is_numeric($price) || (float) $price < 0 || !is_numeric($stock) || (int) $stock < 0) {
    $_SESSION['error'] = 'Price and stock must be valid positive values.';
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/product_edit.php?id=' . $id);
    exit();
}

if (!is_valid_product_category($category)) {
    $_SESSION['error'] = 'Please choose a valid product category.';
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/product_edit.php?id=' . $id);
    exit();
}

$imagePath = $currentImagePath;
$newUploadPath = null;

// If a new file was chosen, replace the current image path with the uploaded one.
if (($_FILES['ImageFile']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    [$uploadOk, $uploadError, $uploadedPath] = store_uploaded_product_image($_FILES['ImageFile'] ?? []);
    if (!$uploadOk) {
        $_SESSION['error'] = $uploadError ?? 'Please choose a valid product image.';
        header('Location: /Team23_PixelPals_Term2_Final/public/admin/product_edit.php?id=' . $id);
        exit();
    }

    $imagePath = (string) $uploadedPath;
    $newUploadPath = $imagePath;
}

try {
    // Update the product record first, then clean up any superseded upload if needed.
    $stmt = $db->prepare(
        'UPDATE product
         SET ProductName = ?, Description = ?, Category = ?, ImagePath = ?, Price = ?, Stock = ?
         WHERE ProductID = ?'
    );
    $stmt->execute([$name, $description, $category, $imagePath, (float) $price, (int) $stock, $id]);
    if ($newUploadPath !== null && $currentImagePath !== '' && $currentImagePath !== $newUploadPath) {
        remove_uploaded_product_image($currentImagePath);
    }
    $_SESSION['success'] = 'Product updated successfully.';
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/products.php');
    exit();
} catch (PDOException $e) {
    // If the database update fails, remove the brand-new upload so we do not leave orphan files behind.
    if ($newUploadPath !== null) {
        remove_uploaded_product_image($newUploadPath);
    }
    $_SESSION['error'] = 'Could not update this product right now.';
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/product_edit.php?id=' . $id);
    exit();
}

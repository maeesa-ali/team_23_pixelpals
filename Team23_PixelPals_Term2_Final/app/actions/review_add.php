<?php
session_start();

require_once __DIR__ . '/../config/db.php';

// Product reviews only come from the product page form.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/products.php');
    exit();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please log in to leave a review.';
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit();
}

$productId = (int) ($_POST['product_id'] ?? 0);
$rating = (int) ($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

// Keep the review validation simple before inserting into the database.
if ($productId <= 0 || $rating < 1 || $rating > 5 || $comment === '') {
    $_SESSION['error'] = 'Please complete the review form properly.';
    header('Location: /Team23_PixelPals_Term2_Final/public/product.php?id=' . $productId);
    exit();
}

try {
    // Product reviews are a straight insert once the user and product input has been validated.
    $stmt = $db->prepare(
        'INSERT INTO reviews (ProductID, UserID, Rating, Comment)
         VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$productId, $_SESSION['user_id'], $rating, $comment]);
    $_SESSION['success'] = 'Review added successfully.';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Could not save your review right now.';
}

header('Location: /Team23_PixelPals_Term2_Final/public/product.php?id=' . $productId);
exit();

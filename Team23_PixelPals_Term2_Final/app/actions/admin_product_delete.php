<?php
session_start();

// Product deletion is admin-only because it removes a live catalogue record.
if (!isset($_SESSION['admin_id'])) {
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit();
}

require_once __DIR__ . '/../config/db.php';

// Only accept the delete form post, not direct hits to the file.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/products.php');
    exit();
}

$id = (int) ($_POST['id'] ?? 0);

// Stop before querying if no usable product id was sent.
if ($id <= 0) {
    $_SESSION['error'] = 'No product ID was provided.';
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/products.php');
    exit();
}

try {
    $stmt = $db->prepare('DELETE FROM product WHERE ProductID = ?');
    $stmt->execute([$id]);
    $_SESSION['success'] = 'Product deleted successfully.';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Could not delete this product right now.';
}

header('Location: /Team23_PixelPals_Term2_Final/public/admin/products.php');
exit();

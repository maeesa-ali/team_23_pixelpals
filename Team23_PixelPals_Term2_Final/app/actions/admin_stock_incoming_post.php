<?php
session_start();

// Stock adjustments are an admin job, so block normal customer sessions here.
if (!isset($_SESSION['admin_id'])) {
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit();
}

require_once __DIR__ . '/../config/db.php';

// This action only exists to handle the stock-incoming form.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/stock_incoming.php');
    exit();
}

$productId = (int) ($_POST['product_id'] ?? 0);
$incomingQuantity = (int) ($_POST['incoming_quantity'] ?? 0);

// Validate the selected product and the incoming quantity before touching stock.
if ($productId <= 0) {
    $_SESSION['error'] = 'Please choose a product to restock.';
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/stock_incoming.php');
    exit();
}

if ($incomingQuantity <= 0) {
    $_SESSION['error'] = 'Incoming quantity must be greater than zero.';
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/stock_incoming.php');
    exit();
}

try {
    // Confirm the product still exists before increasing its stock count.
    $stockStmt = $db->prepare('SELECT ProductName FROM product WHERE ProductID = ?');
    $stockStmt->execute([$productId]);
    $product = $stockStmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $_SESSION['error'] = 'That product could not be found.';
        header('Location: /Team23_PixelPals_Term2_Final/public/admin/stock_incoming.php');
        exit();
    }

    // The actual restock is just a stock increment on the chosen product.
    $updateStmt = $db->prepare('UPDATE product SET Stock = Stock + ? WHERE ProductID = ?');
    $updateStmt->execute([$incomingQuantity, $productId]);

    $_SESSION['success'] = $incomingQuantity . ' units added to ' . $product['ProductName'] . '.';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Could not update stock right now.';
}

header('Location: /Team23_PixelPals_Term2_Final/public/admin/stock_incoming.php');
exit();

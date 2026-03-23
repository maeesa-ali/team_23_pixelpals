<?php
session_start();

require_once __DIR__ . '/../config/db.php';

// Reuse one helper for every "set a flash and redirect" exit in this action.
function redirect_with_message(string $location, string $type, string $message): void
{
    $_SESSION[$type] = $message;
    header('Location: ' . $location);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    redirect_with_message('/Team23_PixelPals_Term2_Final/public/login.php', 'error', 'Please log in to add items to your basket.');
}

// Add-to-basket should only ever come from a form post.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/products.php');
    exit();
}

$productId = (int) ($_POST['product_id'] ?? 0);
$quantity = max(1, (int) ($_POST['quantity'] ?? 1));

// Stop before querying if the product id is obviously bad.
if ($productId <= 0) {
    redirect_with_message('/Team23_PixelPals_Term2_Final/public/products.php', 'error', 'Please choose a valid product.');
}

try {
    // First confirm that the product exists and still has stock.
    $productStmt = $db->prepare('SELECT ProductID, ProductName, Stock FROM product WHERE ProductID = ?');
    $productStmt->execute([$productId]);
    $product = $productStmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        redirect_with_message('/Team23_PixelPals_Term2_Final/public/products.php', 'error', 'That product could not be found.');
    }

    if ((int) $product['Stock'] <= 0) {
        redirect_with_message('/Team23_PixelPals_Term2_Final/public/products.php', 'error', 'That product is currently out of stock.');
    }

    // Every customer needs one basket record before basket items can be added to it.
    $basketStmt = $db->prepare('SELECT BasketID FROM basket WHERE UserID = ? ORDER BY BasketID DESC LIMIT 1');
    $basketStmt->execute([$_SESSION['user_id']]);
    $basketId = $basketStmt->fetchColumn();

    if (!$basketId) {
        $createBasketStmt = $db->prepare('INSERT INTO basket (UserID) VALUES (?)');
        $createBasketStmt->execute([$_SESSION['user_id']]);
        $basketId = (int) $db->lastInsertId();
    }

    // If the item is already in the basket, increase it instead of inserting a duplicate row.
    $itemStmt = $db->prepare('SELECT BasketItemID, Quantity FROM basketitem WHERE BasketID = ? AND ProductID = ?');
    $itemStmt->execute([$basketId, $productId]);
    $existingItem = $itemStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingItem) {
        $newQuantity = min((int) $product['Stock'], (int) $existingItem['Quantity'] + $quantity);
        $updateStmt = $db->prepare('UPDATE basketitem SET Quantity = ? WHERE BasketItemID = ?');
        $updateStmt->execute([$newQuantity, $existingItem['BasketItemID']]);
    } else {
        $insertStmt = $db->prepare('INSERT INTO basketitem (BasketID, ProductID, Quantity) VALUES (?, ?, ?)');
        $insertStmt->execute([$basketId, $productId, min((int) $product['Stock'], $quantity)]);
    }

    redirect_with_message('/Team23_PixelPals_Term2_Final/public/basket.php', 'success', $product['ProductName'] . ' was added to your basket.');
} catch (PDOException $e) {
    // Database errors stay friendly here because the page already gives the user enough context.
    redirect_with_message('/Team23_PixelPals_Term2_Final/public/products.php', 'error', 'Could not add that item to your basket. Please try again.');
}

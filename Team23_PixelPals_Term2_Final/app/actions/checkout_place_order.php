<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';

// This helper keeps all checkout exits consistent and lets us attach one flash message at the same time.
function checkout_redirect(string $location, string $type, string $message): void
{
    $_SESSION[$type] = $message;
    header('Location: ' . $location);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    checkout_redirect('/Team23_PixelPals_Term2_Final/public/login.php', 'error', 'Please log in to continue to checkout.');
}

// The action should only ever receive a completed checkout form submission.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/checkout.php');
    exit();
}

$requiredFields = [
    'email' => trim($_POST['email'] ?? ''),
    'phone' => trim($_POST['phone'] ?? ''),
    'first_name' => trim($_POST['first_name'] ?? ''),
    'last_name' => trim($_POST['last_name'] ?? ''),
    'address' => trim($_POST['address'] ?? ''),
    'card_number' => preg_replace('/\s+/', '', $_POST['card_number'] ?? ''),
    'expiry_date' => trim($_POST['expiry_date'] ?? ''),
    'cvc' => trim($_POST['cvc'] ?? ''),
];

// Payment/contact validation is still done even though this is a demo checkout flow.
foreach ($requiredFields as $value) {
    if ($value === '') {
        checkout_redirect('/Team23_PixelPals_Term2_Final/public/checkout.php', 'error', 'Please complete all checkout fields.');
    }
}

if (!filter_var($requiredFields['email'], FILTER_VALIDATE_EMAIL)) {
    checkout_redirect('/Team23_PixelPals_Term2_Final/public/checkout.php', 'error', 'Please enter a valid email address.');
}

// Engraving is optional, so only validate the name and fee when the box was ticked.
$engravingEnabled = isset($_POST['engraving_enabled']) && (string) $_POST['engraving_enabled'] === '1';
$engravingName = trim((string) ($_POST['engraving_name'] ?? ''));
$engravingFee = 0.0;

if ($engravingEnabled) {
    if ($engravingName === '' || strlen($engravingName) > 32) {
        checkout_redirect('/Team23_PixelPals_Term2_Final/public/checkout.php', 'error', 'Please enter a valid engraving name up to 32 characters.');
    }

    $engravingFee = defined('ORDER_ENGRAVING_FEE') ? (float) ORDER_ENGRAVING_FEE : 4.99;
}

try {
    // Reload the live basket here so checkout always works from the current database state.
    $basketStmt = $db->prepare(
        'SELECT
            b.BasketID,
            bi.BasketItemID,
            bi.ProductID,
            bi.Quantity,
            p.ProductName,
            p.Price,
            p.Stock
         FROM basket b
         JOIN basketitem bi ON bi.BasketID = b.BasketID
         JOIN product p ON p.ProductID = bi.ProductID
         WHERE b.UserID = ?
         ORDER BY bi.BasketItemID ASC'
    );
    $basketStmt->execute([$_SESSION['user_id']]);
    $basketItems = $basketStmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$basketItems) {
        checkout_redirect('/Team23_PixelPals_Term2_Final/public/basket.php', 'error', 'Your basket is empty.');
    }

    // Make sure none of the basket quantities now exceed stock before we create the order.
    foreach ($basketItems as $item) {
        if ((int) $item['Quantity'] > (int) $item['Stock']) {
            checkout_redirect('/Team23_PixelPals_Term2_Final/public/basket.php', 'error', 'One of your items no longer has enough stock.');
        }
    }

    // From this point onward, order creation, stock updates and basket clearing need to succeed together.
    $db->beginTransaction();

    $orderStmt = $db->prepare('INSERT INTO orders (UserID, Status, EngravingName, EngravingFee) VALUES (?, ?, ?, ?)');
    $orderStmt->execute([
        $_SESSION['user_id'],
        'pending',
        $engravingEnabled ? $engravingName : null,
        $engravingFee,
    ]);
    $orderId = (int) $db->lastInsertId();

    $orderItemStmt = $db->prepare(
        'INSERT INTO orderitem (OrderID, ProductID, Quantity, totalProductPrice, Subtotal)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stockStmt = $db->prepare('UPDATE product SET Stock = Stock - ? WHERE ProductID = ?');

    // Create each order item row and reduce stock product by product.
    foreach ($basketItems as $item) {
        $price = (float) $item['Price'];
        $quantity = (int) $item['Quantity'];
        $subtotal = $price * $quantity;

        $orderItemStmt->execute([$orderId, $item['ProductID'], $quantity, $price, $subtotal]);
        $stockStmt->execute([$quantity, $item['ProductID']]);
    }

    // Once the order is safely written, clear the basket that it came from.
    $basketId = (int) $basketItems[0]['BasketID'];
    $clearStmt = $db->prepare('DELETE FROM basketitem WHERE BasketID = ?');
    $clearStmt->execute([$basketId]);

    $db->commit();

    $_SESSION['success'] = $engravingEnabled
        ? 'Order placed successfully with engraving added.'
        : 'Order placed successfully.';
    header('Location: /Team23_PixelPals_Term2_Final/public/order_success.php?order_id=' . $orderId);
    exit();
} catch (PDOException $e) {
    // Roll back the whole checkout if any one database step failed.
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    checkout_redirect('/Team23_PixelPals_Term2_Final/public/checkout.php', 'error', 'Could not place your order. Please try again.');
}

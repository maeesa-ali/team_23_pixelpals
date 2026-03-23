<?php
session_start();

require_once __DIR__ . '/../config/db.php';

// Service reviews are tied to completed orders, so the customer needs to be signed in first.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/orders.php');
    exit();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please log in to leave a review.';
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit();
}

$orderId = (int) ($_POST['order_id'] ?? 0);
$rating = (int) ($_POST['rating'] ?? 0);
$comment = trim((string) ($_POST['comment'] ?? ''));

// Validate the simple review fields before checking order ownership.
if ($orderId <= 0 || $rating < 1 || $rating > 5 || $comment === '') {
    $_SESSION['error'] = 'Please complete the service review form properly.';
    header('Location: /Team23_PixelPals_Term2_Final/public/order_success.php?order_id=' . max(0, $orderId));
    exit();
}

try {
    // Confirm the order belongs to this account before accepting a review for it.
    $orderStmt = $db->prepare('SELECT OrderID FROM orders WHERE OrderID = ? AND UserID = ?');
    $orderStmt->execute([$orderId, $_SESSION['user_id']]);

    if (!$orderStmt->fetchColumn()) {
        $_SESSION['error'] = 'That order could not be reviewed from this account.';
        header('Location: /Team23_PixelPals_Term2_Final/public/orders.php');
        exit();
    }

    // Only one service review is allowed per order.
    $existingStmt = $db->prepare('SELECT ServiceReviewID FROM service_reviews WHERE OrderID = ? AND UserID = ?');
    $existingStmt->execute([$orderId, $_SESSION['user_id']]);

    if ($existingStmt->fetchColumn()) {
        $_SESSION['error'] = 'You have already reviewed this order.';
        header('Location: /Team23_PixelPals_Term2_Final/public/order_success.php?order_id=' . $orderId);
        exit();
    }

    // Save the review once ownership and duplicate checks are done.
    $insertStmt = $db->prepare(
        'INSERT INTO service_reviews (OrderID, UserID, Rating, Comment)
         VALUES (?, ?, ?, ?)'
    );
    $insertStmt->execute([$orderId, $_SESSION['user_id'], $rating, $comment]);

    $_SESSION['success'] = 'Thanks for reviewing PixelPals.';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Your review could not be saved right now.';
}

header('Location: /Team23_PixelPals_Term2_Final/public/order_success.php?order_id=' . $orderId);
exit();

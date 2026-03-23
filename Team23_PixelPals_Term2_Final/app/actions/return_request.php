<?php
session_start();

require_once __DIR__ . '/../config/db.php';

// Only signed-in customers can submit returns.
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please log in to request a return.';
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit();
}

// This action is only for the return request form on the order detail page.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/orders.php');
    exit();
}

$orderId = (int) ($_POST['order_id'] ?? 0);
$orderItemId = (int) ($_POST['order_item_id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');

// Quick validation first so obviously bad requests do not hit the database.
if ($orderId <= 0 || $orderItemId <= 0 || $reason === '') {
    $_SESSION['error'] = 'Invalid return request.';
    header('Location: /Team23_PixelPals_Term2_Final/public/order_view.php?id=' . $orderId);
    exit();
}

try {
    // Confirm that the chosen order item really belongs to this user and this order.
    $itemStmt = $db->prepare(
        'SELECT
            o.OrderID,
            o.Status,
            oi.OrderItemID,
            p.ProductName
         FROM orders o
         INNER JOIN orderitem oi ON oi.OrderID = o.OrderID
         INNER JOIN product p ON p.ProductID = oi.ProductID
         WHERE o.OrderID = ? AND o.UserID = ? AND oi.OrderItemID = ?
         LIMIT 1'
    );
    $itemStmt->execute([$orderId, $_SESSION['user_id'], $orderItemId]);
    $item = $itemStmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        $_SESSION['error'] = 'That item could not be found for this order.';
        header('Location: /Team23_PixelPals_Term2_Final/public/order_view.php?id=' . $orderId);
        exit();
    }

    // Returns are only available after the order has reached a finished state.
    if (!in_array((string) $item['Status'], ['completed', 'delivered'], true)) {
        $_SESSION['error'] = 'Returns are available only for completed or delivered orders.';
        header('Location: /Team23_PixelPals_Term2_Final/public/order_view.php?id=' . $orderId);
        exit();
    }

    // Stop duplicate requests for the same item before inserting anything new.
    $duplicateStmt = $db->prepare(
        'SELECT ReturnRequestID
         FROM return_requests
         WHERE OrderID = ? AND OrderItemID = ? AND UserID = ?
         LIMIT 1'
    );
    $duplicateStmt->execute([$orderId, $orderItemId, $_SESSION['user_id']]);

    if ($duplicateStmt->fetch()) {
        $_SESSION['error'] = 'A return request has already been submitted for that item.';
        header('Location: /Team23_PixelPals_Term2_Final/public/order_view.php?id=' . $orderId);
        exit();
    }

    // Once everything checks out, create the new return request in the default requested state.
    $insertStmt = $db->prepare(
        'INSERT INTO return_requests (OrderID, OrderItemID, UserID, Reason, Status)
         VALUES (?, ?, ?, ?, ?)'
    );
    $insertStmt->execute([$orderId, $orderItemId, $_SESSION['user_id'], $reason, 'requested']);

    $_SESSION['success'] = 'Return request received for ' . $item['ProductName'] . '.';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Could not submit your return request right now.';
}

header('Location: /Team23_PixelPals_Term2_Final/public/order_view.php?id=' . $orderId);
exit();

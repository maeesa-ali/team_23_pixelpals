<?php
session_start();

// Both order processing and return processing come through this one admin-only action.
if (!isset($_SESSION['admin_id'])) {
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit();
}

require_once __DIR__ . '/../config/db.php';

// Only accept actual form submissions from the admin screens.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/orders.php');
    exit();
}

$orderId = (int) ($_POST['order_id'] ?? 0);
$returnRequestId = (int) ($_POST['return_request_id'] ?? 0);
$status = trim((string) ($_POST['status'] ?? ''));
$returnTo = trim((string) ($_POST['return_to'] ?? ''));
$orderStatuses = ['pending', 'processing', 'completed', 'delivered', 'cancelled'];
$returnStatuses = ['requested', 'approved', 'rejected', 'processed'];

// If a return request id is present, treat this as a return update instead of an order update.
if ($returnRequestId > 0) {
    $returnRedirect = $returnTo === 'returns'
        ? '/Team23_PixelPals_Term2_Final/public/admin/returns.php'
        : '/Team23_PixelPals_Term2_Final/public/admin/orders.php?id=' . max(0, $orderId);

    if ($orderId <= 0 || !in_array($status, $returnStatuses, true)) {
        $_SESSION['error'] = 'Invalid return update request.';
        header('Location: ' . $returnRedirect);
        exit();
    }

    try {
        // Keep the return update tightly scoped to both the request id and the linked order id.
        $stmt = $db->prepare(
            'UPDATE return_requests
             SET Status = ?
             WHERE ReturnRequestID = ? AND OrderID = ?'
        );
        $stmt->execute([$status, $returnRequestId, $orderId]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = 'Return request updated successfully.';
        } else {
            $_SESSION['error'] = 'That return request could not be updated.';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Could not update this return request right now.';
    }

    header('Location: ' . $returnRedirect);
    exit();
}

// Otherwise, this is a standard order-status update from the admin orders page.
if ($orderId <= 0 || !in_array($status, $orderStatuses, true)) {
    $_SESSION['error'] = 'Invalid order update request.';
    header('Location: /Team23_PixelPals_Term2_Final/public/admin/orders.php');
    exit();
}

try {
    $stmt = $db->prepare('UPDATE orders SET Status = ? WHERE OrderID = ?');
    $stmt->execute([$status, $orderId]);
    $_SESSION['success'] = 'Order status updated successfully.';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Could not update this order right now.';
}

header('Location: /Team23_PixelPals_Term2_Final/public/admin/orders.php?id=' . $orderId);
exit();

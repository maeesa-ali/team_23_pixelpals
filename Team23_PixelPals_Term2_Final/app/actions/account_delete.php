<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/user_account_service.php';

// Self-delete only applies to signed-in customers.
if (!isset($_SESSION['user_id'])) {
    header("Location: /Team23_PixelPals_Term2_Final/public/login.php");
    exit();
}

// The delete button posts here so we can clean up related account data before removing the user row.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit();
}

$uid = $_SESSION['user_id'];

try {
    // The shared service handles the related basket/order/review cleanup work first.
    delete_user_with_relations($db, (int) $uid);

    session_unset();
    session_destroy();

    session_start();
    $_SESSION['success'] = 'Your account has been permanently deleted.';
    header('Location: /Team23_PixelPals_Term2_Final/public/index.php');
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = 'Could not delete account.';
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit();
}

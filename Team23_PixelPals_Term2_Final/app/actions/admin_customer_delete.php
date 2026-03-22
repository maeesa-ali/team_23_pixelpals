<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/user_account_service.php';

// Only admins should be able to delete customer accounts from the admin list.
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . APP_BASE_PATH . '/public/login.php');
    exit();
}

// The customer id comes from the delete link/button on the admin customers page.
$user_id = $_GET['id'] ?? null;

if ($user_id) {
    try {
        // Use the shared helper so related basket/order/review records are cleaned up too.
        delete_user_with_relations($db, (int) $user_id);
        
        $_SESSION['success'] = 'Customer deleted successfully.';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Could not delete this customer because related account data is still linked.';
    }
} else {
    $_SESSION['error'] = 'No user ID provided.';
}
header('Location: ' . APP_BASE_PATH . '/public/admin/customers.php');

exit();

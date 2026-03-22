<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/user_account_service.php';


// Customer account edits from the admin area should only be available to admins.
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . APP_BASE_PATH . '/public/login.php');
    exit();
}

// This action is only for the admin customer edit form, so bounce direct visits away.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE_PATH . '/public/admin/customers.php');
    exit();
}

// Pull the posted profile values once so the validation below stays easy to follow.
$user_id  = (int) ($_POST['user_id'] ?? 0);
$username = trim($_POST['username'] ?? '');
$fname    = trim($_POST['first_name'] ?? '');
$lname    = trim($_POST['last_name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$dob      = $_POST['dob'] ?? null;

if ($user_id <= 0 || $username === '' || $fname === '' || $lname === '' || $email === '') {
    $_SESSION['error'] = 'Please complete all required fields.';
    header('Location: ' . APP_BASE_PATH . '/public/admin/customers.php');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    header('Location: ' . APP_BASE_PATH . '/public/admin/customer_edit.php?id=' . $user_id);
    exit();
}

try {
    // Reuse the shared profile helper so admin edits and self-service edits stay in sync.
    update_user_profile($db, $user_id, $username, $fname, $lname, $email, $dob);
    $_SESSION['success'] = 'Customer profile updated successfully.';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Update failed. Please try again.';
}

header('Location: ' . APP_BASE_PATH . '/public/admin/customers.php');
exit();

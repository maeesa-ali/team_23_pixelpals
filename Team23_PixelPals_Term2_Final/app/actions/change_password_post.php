<?php

// Start session because we need current logged-in user
session_start();

// Connect to database
require_once __DIR__ . '/../config/db.php');

// User must be logged in to change password
if (!isset($_SESSION['user_id'])) {
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit;
}

// Only allow POST requests from the form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/change_password.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Read values from change password form
|--------------------------------------------------------------------------
| These names must match Jamaal's change_password.php form
|
| Need to confirm with:
| - Jamaal
| Related page:
| - public/change_password.php
*/
$oldPassword = $_POST['old_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($oldPassword === '' || $newPassword === '' || $confirmPassword === '') {
    header('Location: /Team23_PixelPals_Term2_Final/public/change_password.php?error=missing_fields');
    exit;
}

// Check that new password and confirm password match
if ($newPassword !== $confirmPassword) {
    header('Location: /Team23_PixelPals_Term2_Final/public/change_password.php?error=password_mismatch');
    exit;
}

try {
    /*
    ----------------------------------------------------------------------
    | Get current password hash from database
    ----------------------------------------------------------------------
    | Assumed columns:
    | - UserID
    | - Password
    |
    | Need final confirmation from:
    | - Russell
    | Related files:
    | - database/schema.sql
    | - app/actions/account_update.php
    */
    $stmt = $db->prepare("SELECT Password FROM users WHERE UserID = ?");
    $stmt->execute([$_SESSION['user_id']]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check old password is correct
    if (!$user || !password_verify($oldPassword, $user['Password'])) {
        header('Location: /Team23_PixelPals_Term2_Final/public/change_password.php?error=wrong_old_password');
        exit;
    }

    // Hash the new password
    $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password in database
    $updateStmt = $db->prepare("UPDATE users SET Password = ? WHERE UserID = ?");
    $updateStmt->execute([$newHashedPassword, $_SESSION['user_id']]);

    // Redirect after success
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php?success=password_updated');
    exit;

} catch (PDOException $e) {
    header('Location: /Team23_PixelPals_Term2_Final/public/change_password.php?error=server_error');
    exit;
}

<?php

session_start();
require_once __DIR__ . '/../config/db.php';

// Password changes can come from either a customer or admin account page session.
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = 'Please log in to change your password.';
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit;
}



// This action should only run from the submitted password form.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit;
}



$oldPassword = $_POST['old_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';



// Make sure all three fields were supplied before we compare anything.
if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
    $_SESSION['error'] = 'Please fill in all password fields.';
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit;
}


// The confirmation field is checked before we look up the current stored password.
if ($newPassword !== $confirmPassword) {
    $_SESSION['error'] = 'New passwords do not match.';
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit;
}




try {
    $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    if (isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
        // Admin password changes check and update the admin table.
        $stmt = $db->prepare("SELECT Password FROM admin WHERE AdminID = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        $storedPassword = (string) ($account['Password'] ?? '');
        $passwordMatches = $account && (password_verify($oldPassword, $storedPassword) || hash_equals($storedPassword, $oldPassword));

        if (!$passwordMatches) {
            $_SESSION['error'] = 'Current password is incorrect.';
            header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
            exit;
        }

        $updateStmt = $db->prepare("UPDATE admin SET Password = ? WHERE AdminID = ?");
        $updateStmt->execute([$newHashedPassword, $_SESSION['admin_id']]);
    } else {
        // Customer password changes do the same check against the users table.
        $stmt = $db->prepare("SELECT Password FROM users WHERE UserID = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$account || !password_verify($oldPassword, $account['Password'])) {
            $_SESSION['error'] = 'Current password is incorrect.';
            header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
            exit;
        }

        $updateStmt = $db->prepare("UPDATE users SET Password = ? WHERE UserID = ?");
        $updateStmt->execute([$newHashedPassword, $_SESSION['user_id']]);
    }

    $_SESSION['success'] = 'Password updated successfully.';
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit;

} catch (PDOException $e) {
    // Keep database failures generic here as well.
    $_SESSION['error'] = 'Something went wrong. Please try again.';
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit;
}

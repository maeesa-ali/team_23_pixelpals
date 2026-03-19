<?php

session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please log in to change your password.';
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit;
}



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit;
}



$oldPassword = $_POST['old_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';



if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
    $_SESSION['error'] = 'Please fill in all password fields.';
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit;
}


if ($newPassword !== $confirmPassword) {
    $_SESSION['error'] = 'New passwords do not match.';
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit;
}




try {
    $stmt = $db->prepare("SELECT Password FROM users WHERE UserID = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($oldPassword, $user['Password'])) {
        $_SESSION['error'] = 'Current password is incorrect.';
        header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
        exit;
    }


    $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $updateStmt = $db->prepare("UPDATE users SET Password = ? WHERE UserID = ?");
    $updateStmt->execute([$newHashedPassword, $_SESSION['user_id']]);

    $_SESSION['success'] = 'Password updated successfully.';
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['error'] = 'Something went wrong. Please try again.';
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit;
}

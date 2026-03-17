<?php

// Start the session so we can check which user is logged in
session_start();



// Connect to the database
require_once __DIR__ . '/../config/db.php';

// If no normal user is logged in, send them to the login page
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please log in to change your password.';
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit;
}

// Only allow this file to run when the form is submitted with POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit;
}

// Get the password values from the form
$oldPassword = $_POST['old_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Check that all fields were filled in
if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
    $_SESSION['error'] = 'Please fill in all password fields.';
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit;
}

// Check that new password and confirm password match
if ($newPassword !== $confirmPassword) {
    $_SESSION['error'] = 'New passwords do not match.';
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit;
}

try {
    // Get the current logged-in user's existing password hash from the database
    $stmt = $db->prepare("SELECT Password FROM users WHERE UserID = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // If user not found or current password is wrong, stop
    if (!$user || !password_verify($oldPassword, $user['Password'])) {
        $_SESSION['error'] = 'Current password is incorrect.';
        header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
        exit;
    }

    
    // Hash the new password before saving it
    $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update the user's password in the database
    $updateStmt = $db->prepare("UPDATE users SET Password = ? WHERE UserID = ?");
    $updateStmt->execute([$newHashedPassword, $_SESSION['user_id']]);

    
    // Set success message and send user back to account page
    $_SESSION['success'] = 'Password updated successfully.';
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit;

} catch (PDOException $e) {
    // If something goes wrong with the database, show error message
    $_SESSION['error'] = 'Something went wrong. Please try again.';
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit;
}

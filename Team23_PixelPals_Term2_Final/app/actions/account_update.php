<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/user_account_service.php';

// This shared action handles both customer and admin account edits from the account page.
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: /Team23_PixelPals_Term2_Final/public/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit();
}

// Pull the form values once here so the validation below stays straightforward.
$username = trim($_POST['username'] ?? '');
$fname    = trim($_POST['first_name'] ?? '');
$lname    = trim($_POST['last_name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$dob      = $_POST['dob'] ?? null;

if ($username === '' || $fname === '' || $lname === '' || $email === '') {
    $_SESSION['error'] = 'Please complete all required fields.';
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit();
}

try {
    if (isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
        // Admin account edits update the admin table directly.
        $stmt = $db->prepare(
            'UPDATE admin
             SET Username = ?, FirstName = ?, LastName = ?, Email = ?
             WHERE AdminID = ?'
        );
        $stmt->execute([$username, $fname, $lname, $email, (int) $_SESSION['admin_id']]);
        $_SESSION['admin_name'] = $username !== '' ? $username : ($fname !== '' ? $fname : $_SESSION['admin_name']);
    } else {
        // Customer profile updates go through the shared service helper.
        $uid = $_SESSION['user_id'];
        update_user_profile($db, (int) $uid, $username, $fname, $lname, $email, $dob);
    }

    $_SESSION['success'] = 'Account details updated successfully!';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Update failed. Please try again.';
}

header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
exit();

<?php

// Start session so user data can be stored after login
session_start();

// Connect to database
require_once __DIR__ . '/../config/db.php';

// Only allow POST requests from login form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Read values from login form
|--------------------------------------------------------------------------
| These names must match Jamaal's login.php form
|
| Need to confirm with:
| - Jamaal
| Related page:
| - public/login.php
*/
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php?error=missing_fields');
    exit;
}

try {
    /*
    ----------------------------------------------------------------------
    | Find user by username
    ----------------------------------------------------------------------
    | Assumed columns based on Russell's message:
    | - UserID
    | - Username
    | - FirstName
    | - Password
    */
    $stmt = $db->prepare("
        SELECT UserID, Username, FirstName, Password
        FROM users
        WHERE Username = ?
    ");
    $stmt->execute([$username]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists and password is correct
    if (!$user || !password_verify($password, $user['Password'])) {
        header('Location: /Team23_PixelPals_Term2_Final/public/login.php?error=invalid_login');
        exit;
    }

    // Store logged-in user details in session
    $_SESSION['user_id'] = $user['UserID'];
    $_SESSION['username'] = $user['Username'];
    $_SESSION['first_name'] = $user['FirstName'];

    /*
    ----------------------------------------------------------------------
    | ADMIN LOGIC NOT CONFIRMED YET
    ----------------------------------------------------------------------
    | We still need to know:
    | - Is admin stored in users table?
    | - Is there a Role column?
    | - Should admins redirect to admin/dashboard.php?
    |
    | Need from:
    | - Russell
    | Related files/pages:
    | - public/admin/dashboard.php
    | - database/schema.sql
    | - database/seed.sql
    |
    | So for now, send everyone to homepage.
    */
    header('Location: /Team23_PixelPals_Term2_Final/public/index.php');
    exit;

} catch (PDOException $e) {
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php?error=server_error');
    exit;
}

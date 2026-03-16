<?php

// Start session in case feedback is later stored in session
session_start();

// Connect to the database
require_once __DIR__ . '/../config/db.php';

// Only allow POST requests from the signup form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/signup.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Read values from signup form
|--------------------------------------------------------------------------
| These input names must match Jamaal's signup.php form
|
| Need to confirm with:
| - Jamaal
| Related page:
| - public/signup.php
*/
$username = trim($_POST['username'] ?? '');
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$age = trim($_POST['age'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Check that no required fields are empty
if (
    $username === '' ||
    $firstName === '' ||
    $lastName === '' ||
    $age === '' ||
    $password === '' ||
    $confirmPassword === ''
) {
    header('Location: /Team23_PixelPals_Term2_Final/public/signup.php?error=missing_fields');
    exit;
}

// Check that age is a number
if (!is_numeric($age) || (int)$age < 0) {
    header('Location: /Team23_PixelPals_Term2_Final/public/signup.php?error=invalid_age');
    exit;
}

// Check that password and confirm password match
if ($password !== $confirmPassword) {
    header('Location: /Team23_PixelPals_Term2_Final/public/signup.php?error=password_mismatch');
    exit;
}

try {
    /*
    ----------------------------------------------------------------------
    | Check if username already exists
    ----------------------------------------------------------------------
    | Based on Russell's message:
    | - users table likely has UserID and Username
    |
    | Need final confirmation from:
    | - Russell
    | Related files:
    | - database/schema.sql
    | - app/actions/account_update.php
    */
    $checkStmt = $db->prepare("SELECT UserID FROM users WHERE Username = ?");
    $checkStmt->execute([$username]);

    if ($checkStmt->fetch()) {
        header('Location: /Team23_PixelPals_Term2_Final/public/signup.php?error=username_exists');
        exit;
    }

    // Hash the password before storing it
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    /*
    ----------------------------------------------------------------------
    | Insert new user into database
    ----------------------------------------------------------------------
    | Assumed columns from Russell's message:
    | - Username
    | - FirstName
    | - LastName
    | - Age
    | - Password
    |
    | Need final confirmation from:
    | - Russell
    | Related files:
    | - database/schema.sql
    | - app/actions/account_update.php
    */
    $insertStmt = $db->prepare("
        INSERT INTO users (Username, FirstName, LastName, Age, Password)
        VALUES (?, ?, ?, ?, ?)
    ");

    $insertStmt->execute([
        $username,
        $firstName,
        $lastName,
        (int)$age,
        $hashedPassword
    ]);

    // After successful signup, send user to login page
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php?success=account_created');
    exit;

} catch (PDOException $e) {
    /*
    ----------------------------------------------------------------------
    | Temporary error handling
    ----------------------------------------------------------------------
    | Later this may be replaced with Russell's flash.php system
    |
    | Need from:
    | - Russell
    | Related file:
    | - app/includes/flash.php
    */
    header('Location: /Team23_PixelPals_Term2_Final/public/signup.php?error=server_error');
    exit;
}

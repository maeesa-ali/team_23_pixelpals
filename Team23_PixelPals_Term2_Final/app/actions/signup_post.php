<?php

// Start the session so we can store success or error messages
session_start();

// Connect to the database
require_once __DIR__ . '/../config/db.php';



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/signup.php');
    exit;
}



// Get the values from the signup form
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$dateOfBirth = $_POST['dob'] ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Check that all required fields are filled in
if (
    empty($email) ||
    empty($username) ||
    empty($firstName) ||
    empty($lastName) ||
    empty($dateOfBirth) ||
    empty($password) ||
    empty($confirmPassword)
) {
    $_SESSION['error'] = 'Please fill in all fields.';
    header('Location: /Team23_PixelPals_Term2_Final/public/signup.php');
    exit;
}

// Check that password and confirm password match
if ($password !== $confirmPassword) {
    $_SESSION['error'] = 'Passwords do not match.';
    header('Location: /Team23_PixelPals_Term2_Final/public/signup.php');
    exit;
}

try {
    // Check if username already exists
    $checkUsername = $db->prepare("SELECT UserID FROM users WHERE Username = ?");
    $checkUsername->execute([$username]);

    if ($checkUsername->fetch()) {
        $_SESSION['error'] = 'Username already exists.';
        header('Location: /Team23_PixelPals_Term2_Final/public/signup.php');
        exit;
    }

    // Check if email already exists
    $checkEmail = $db->prepare("SELECT UserID FROM users WHERE Email = ?");
    $checkEmail->execute([$email]);

    if ($checkEmail->fetch()) {
        $_SESSION['error'] = 'Email already exists.';
        header('Location: /Team23_PixelPals_Term2_Final/public/signup.php');
        exit;
    }

    // Hash the password before saving it to the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user into the users table
    $insertStmt = $db->prepare("
        INSERT INTO users (Username, FirstName, LastName, DateOfBirth, Password, Email)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $insertStmt->execute([
        $username,
        $firstName,
        $lastName,
        $dateOfBirth,
        $hashedPassword,
        $email
    ]);

    // Set success message and redirect to login page
    $_SESSION['success'] = 'Account created successfully. Please log in.';
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit;

} catch (PDOException $e) {
    // If something goes wrong, redirect back with an error message
    $_SESSION['error'] = 'Something went wrong. Please try again.';
    header('Location: /Team23_PixelPals_Term2_Final/public/signup.php');
    exit;
}

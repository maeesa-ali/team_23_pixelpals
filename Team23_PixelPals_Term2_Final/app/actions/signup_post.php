<?php

// Start the session so we can store success or error messages
session_start();

require_once __DIR__ . '/../config/config.php';

// Connect to the database
require_once __DIR__ . '/../config/db.php';


// This action only exists to process the signup form, so anything else gets bounced back.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/signup.php');
    exit;
}



// Pull the raw form values once so the validation below stays readable.
// Get the values from the signup form
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$dateOfBirth = $_POST['dob'] ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$isAdmin = isset($_POST['is_admin']) && $_POST['is_admin'] === '1';
$adminPassword = $_POST['admin_password'] ?? '';

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
    // Keep the first validation simple: make sure the core fields were all filled in.
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

// Admin signups need the extra access code before we even check duplicates.
if ($isAdmin && $adminPassword !== ADMIN_SIGNUP_CODE) {
    $_SESSION['error'] = 'Invalid admin access code.';
    header('Location: /Team23_PixelPals_Term2_Final/public/signup.php');
    exit;
}

try {
    if ($isAdmin) {
        // Admin usernames and emails are checked against the admin table separately.
        $checkAdminUsername = $db->prepare("SELECT AdminID FROM admin WHERE Username = ?");
        $checkAdminUsername->execute([$username]);

        if ($checkAdminUsername->fetch()) {
            $_SESSION['error'] = 'Admin username already exists.';
            header('Location: /Team23_PixelPals_Term2_Final/public/signup.php');
            exit;
        }

        $checkAdminEmail = $db->prepare("SELECT AdminID FROM admin WHERE Email = ?");
        $checkAdminEmail->execute([$email]);

        if ($checkAdminEmail->fetch()) {
            $_SESSION['error'] = 'Admin email already exists.';
            header('Location: /Team23_PixelPals_Term2_Final/public/signup.php');
            exit;
        }
    }

    // Customer usernames and emails still need to stay unique in the users table as well.
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

    if ($isAdmin) {
        // Admin accounts are stored in the dedicated admin table with a default role value.
        $insertStmt = $db->prepare("
            INSERT INTO admin (Username, FirstName, LastName, Password, Email, Role)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $insertStmt->execute([
            $username,
            $firstName,
            $lastName,
            $hashedPassword,
            $email,
            'Admin'
        ]);

        $_SESSION['success'] = 'Admin account created successfully. Log in with your username or email and password.';
    } else {
        // Standard customer accounts are inserted into the users table instead.
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

        $_SESSION['success'] = 'Account created successfully. Please log in.';
    }

    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit;

} catch (PDOException $e) {
    // Keep the error message friendly and generic rather than exposing raw database details.
    // If something goes wrong, redirect back with an error message
    $_SESSION['error'] = 'Something went wrong. Please try again.';
    header('Location: /Team23_PixelPals_Term2_Final/public/signup.php');
    exit;
}

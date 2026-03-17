<?php

// Start the session so we can store login data
session_start();

// Connect to database
require_once __DIR__ . '/../config/db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit;
}

// Get form inputs
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Check if fields were filled
if (empty($username) || empty($password)) {
    $_SESSION['error'] = 'Please enter your username and password.';
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit;
}

try {

    // Look up the user in the database
    $stmt = $db->prepare("
        SELECT UserID, Username, Password 
        FROM users 
        WHERE Username = ?
    ");

    $stmt->execute([$username]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists and password is correct
    if (!$user || !password_verify($password, $user['Password'])) {

        $_SESSION['error'] = 'Invalid username or password.';
        header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
        exit;
    }

    // Store user session
    $_SESSION['user_id'] = $user['UserID'];
    $_SESSION['username'] = $user['Username'];

    // Redirect to account page
    $_SESSION['success'] = 'Login successful!';
    header('Location: /Team23_PixelPals_Term2_Final/public/account.php');
    exit;

} catch (PDOException $e) {

    $_SESSION['error'] = 'Something went wrong. Please try again.';
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit;
}

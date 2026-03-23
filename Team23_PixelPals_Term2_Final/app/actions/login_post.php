<?php

session_start();

require_once __DIR__ . '/../config/db.php';

// Keep the redirect logic in one helper so every early exit lands on the same login page.
function redirect_to_login(string $message = null): void
{
    if ($message !== null) {
        $_SESSION['error'] = $message;
    }

    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to_login();
}

// Stop early if the shared database connection did not come through properly.
if (!isset($db) || !($db instanceof PDO)) {
    redirect_to_login('Database connection failed. Please check XAMPP and MySQL.');
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Basic empty-field check before touching the database.
if ($username === '' || $password === '') {
    redirect_to_login('Please enter your username and password.');
}

try {
    // Check the admin table first because admins are sent to a different part of the site after login.
    $adminStmt = $db->prepare(
        'SELECT AdminID, Username, FirstName, Email, Password
         FROM admin
         WHERE LOWER(Username) = LOWER(?) OR LOWER(Email) = LOWER(?)
         LIMIT 1'
    );
    $adminStmt->execute([$username, $username]);
    $admin = $adminStmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        // Support both hashed passwords and any older plain-text seed values still hanging around.
        $adminStoredPassword = $admin['Password'] ?? '';
        $adminPasswordMatches = password_verify($password, $adminStoredPassword) || hash_equals($adminStoredPassword, $password);

        if (!$adminPasswordMatches) {
            redirect_to_login('Invalid username or password.');
        }

        // Clear any customer session values before switching into the admin session.
        session_regenerate_id(true);
        unset($_SESSION['user_id'], $_SESSION['username']);
        $_SESSION['admin_id'] = (int) $admin['AdminID'];
        $_SESSION['admin_name'] = $admin['Username'] ?: ($admin['FirstName'] ?: $admin['Email']);
        $_SESSION['success'] = 'Admin login successful!';

        header('Location: /Team23_PixelPals_Term2_Final/public/admin/dashboard.php');
        exit;
    }

    // If no admin matched, fall back to the normal customer lookup.
    $stmt = $db->prepare(
        'SELECT UserID, Username, Password
         FROM users
         WHERE LOWER(Username) = LOWER(?) OR LOWER(Email) = LOWER(?)
         LIMIT 1'
    );

    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        redirect_to_login('Invalid username or password.');
    }

    $storedPassword = $user['Password'] ?? '';
    $passwordMatches = password_verify($password, $storedPassword) || hash_equals($storedPassword, $password);

    if (!$passwordMatches) {
        redirect_to_login('Invalid username or password.');
    }

    // Clear any admin session values before storing the customer session.
    session_regenerate_id(true);
    unset($_SESSION['admin_id'], $_SESSION['admin_name']);

    $_SESSION['user_id'] = (int) $user['UserID'];
    $_SESSION['username'] = $user['Username'];
    $_SESSION['success'] = 'Login successful!';

    header('Location: /Team23_PixelPals_Term2_Final/public/index.php');
    exit;
} catch (PDOException $e) {
    // Keep database errors vague on purpose so the login flow does not leak internals.
    redirect_to_login('Something went wrong. Please try again.');
}

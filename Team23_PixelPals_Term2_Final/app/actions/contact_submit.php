<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';

// This action only handles the contact form, so ignore direct GET requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE_PATH . '/public/contact.php');
    exit();
}

// Pull the form values once up front so the validation and insert stay readable.
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$order   = trim($_POST['order'] ?? '');
$message = trim($_POST['message'] ?? '');

// The form can survive without a name or order number, but email and message are required.
if (empty($email) || empty($message)) {
    $_SESSION['error'] = 'Email and Message are required.';
    header('Location: ' . APP_BASE_PATH . '/public/contact.php');
    exit();
}

try {
    // Turn the optional order number into a simple subject line for the admin inbox.
    $sql = 'INSERT INTO contact_messages (Name, Email, Subject, Message) VALUES (?, ?, ?, ?)';
    $stmt = $db->prepare($sql);
    
    $subject = !empty($order) ? "Order Inquiry #$order" : 'General Inquiry';
    
    $stmt->execute([$name, $email, $subject, $message]);
    
    $_SESSION['success'] = 'Thank you! Your message has been received.';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error. Please try again later.';
}

header('Location: ' . APP_BASE_PATH . '/public/contact.php');
exit();

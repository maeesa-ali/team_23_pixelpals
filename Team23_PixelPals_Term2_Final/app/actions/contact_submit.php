<?php
session_start();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $order   = trim($_POST['order'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($email) || empty($message)) {
        $_SESSION['error'] = "Email and Message are required.";
        header("Location: ../../public/contact.php");
        exit();
    }

    try {
        $sql = "INSERT INTO contact_messages (Name, Email, Subject, Message) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        
        $subject = !empty($order) ? "Order Inquiry #$order" : "General Inquiry";
        
        $stmt->execute([$name, $email, $subject, $message]);
        
        $_SESSION['success'] = "Thank you! Your message has been received.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error. Please try again later.";
    }

    header("Location: ../../public/contact.php");
    exit();
}
<?php
session_start();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    try {
        $stmt = $db->prepare("INSERT INTO contact_messages (Name, Email, Subject, Message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        
        $_SESSION['success'] = "Message sent successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Failed to send message.";
    }

    header("Location: ../../public/contact.php");
    exit();
}
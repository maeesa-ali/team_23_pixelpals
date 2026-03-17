<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../public/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $uid = $_SESSION['user_id'];

    try {
        $stmt = $db->prepare("DELETE FROM users WHERE UserID = ?");
        $stmt->execute([$uid]);

        session_unset();
        session_destroy();

        session_start();
        $_SESSION['success'] = "Your account has been permanently deleted.";
        header("Location: ../../public/index.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Could not delete account.";
        header("Location: ../../public/account.php");
        exit();
    }
}

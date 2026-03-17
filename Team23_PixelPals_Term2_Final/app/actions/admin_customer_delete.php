<?php
session_start();
require_once '../config/db.php';



if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../public/admin_login.php");

    exit();
}

$user_id = $_GET['id'] ?? null;

if ($user_id) {
    try {
        $stmt = $db->prepare("DELETE FROM users WHERE UserID = ?");
        $stmt->execute([$user_id]);
        
        $_SESSION['success'] = "Customer deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Deletion failed: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "No user ID provided.";
}
header("Location: ../../public/admin/customers.php");

exit();
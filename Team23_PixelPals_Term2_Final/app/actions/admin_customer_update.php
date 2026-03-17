<?php
session_start();
require_once '../config/db.php'; 


if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../public/admin_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id  = $_POST['user_id'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $fname    = trim($_POST['first_name'] ?? '');
    $lname    = trim($_POST['last_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $dob      = $_POST['dob'] ?? null; 

    try {

        $sql = "UPDATE users SET 
                Username = ?, 
                FirstName = ?, 
                LastName = ?, 
                Email = ?, 
                DateOfBirth = ? 
                WHERE UserID = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$username, $fname, $lname, $email, $dob, $user_id]);
        
        $_SESSION['success'] = "Profile updated and age recalculated!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Update failed: " . $e->getMessage();
    }

    header("Location: ../../public/admin/customers.php");
    exit();
}
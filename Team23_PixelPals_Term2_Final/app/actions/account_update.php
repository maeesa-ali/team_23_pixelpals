<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../public/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $uid      = $_SESSION['user_id'];
    $username = trim($_POST['username'] ?? '');
    $fname    = trim($_POST['first_name'] ?? '');
    $lname    = trim($_POST['last_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $dob      = $_POST['dob'] ?? null;

    try {
        $sql = "UPDATE users SET Username = ?, FirstName = ?, LastName = ?, Email = ?, DateOfBirth = ? WHERE UserID = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$username, $fname, $lname, $email, $dob, $uid]);

        $_SESSION['success'] = "Account details updated successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Update failed: " . $e->getMessage();
    }

    header("Location: ../../public/account.php");
    exit();
}
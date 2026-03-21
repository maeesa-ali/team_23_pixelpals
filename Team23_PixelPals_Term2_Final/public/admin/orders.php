<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Admin') {
    header('Location: ../../public/login.php');
    exit;
}

$sql = "SELECT o.OrderID, o.Status, u.Username, u.Email FROM orders o JOIN users u ON o.UserID = u.UserID ORDER BY CASE WHEN o.Status = 'Processing' THEN 1 ELSE 2 END, o.OrderID DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Orders</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
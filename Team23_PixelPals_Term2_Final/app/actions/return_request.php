<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['UserID'])){
    header('Location: ../../public/login.php');
    exit;
}

$user_id = $_SESSION['UserID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $OrderItemID = $_POST['OrderItemID'];
    $Reason = $_POST['Reason'];

    $verify_sql = "SELECT oi.OrderID FROM orderitem oi JOIN orders o ON oi.OrderID = o.OrderID WHERE oi.OrderItemID = ? AND o.UserID = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $OrderItemID, $user_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    $verified_item = $verify_result->fetch_assoc();

    if (!$verified_item) {
        header('Location: ../../public/orders.php');
        exit;
    }

    $OrderID = $verified_item['OrderID'];
    $insert_sql = "INSERT INTO return_requests (OrderItemID, UserID, Reason, Status, CreatedAt) VALUES (?, ?, ?, 'Pending', NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iis", $OrderItemID, $UserID, $Reason, $Details);
    $insert_stmt->execute();

    header('Location: ../../public/orders.php?id=' . $OrderID);
    exit;
} else {
    header('Location: ../../public/orders.php');
    exit;
}

?>
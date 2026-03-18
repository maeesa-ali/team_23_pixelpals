<?php
session_start();
if (!isset($_SESSION['UserID'])){
    header('Location: login.php');
    exit;
}

$UserID = $_SESSION['UserID'];

$stmt = $db->prepare('SELECT BasketID FROM basket WHERE UserID = ?');
$stmt->execute([$UserID]);
$basket = $stmt->fetch();

if(!$basket){
    header('Location: basket.php');
    exit;
}

$BasketID = $basket['BasketID'];

$sql = "SELECT b.ProductID, b.Quantity, p.Price, p.Stock, p.ProductName FROM basketitem b JOIN product p ON b.ProductID = p.ProductID WHERE BasketID = ?";
$items_stmt = $db->prepare($sql);
$items_stmt->execute([$BasketID]);
$checkout_items = $items_stmt->fetchAll();

if (count($checkout_items) === 0){
    $_SESSION["error"] = "Your basket is empty. Please add items to your basket before checking out.";
    header('Location: basket.php');
    exit;
}
?>

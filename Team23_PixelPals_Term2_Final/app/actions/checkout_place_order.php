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

$order_sql = "INSERT INTO orders (UserID, Status) VALUES (?, 'Processing')";
$order_stmt = $db->prepare($order_sql);
$order_stmt->execute([$UserID]);
$OrderID = $db->lastInsertId();

$item_sql = "INSERT INTO orderitem (OrderID, ProductID, Quantity, totalProductPrice, Subtotal) VALUES (?, ?, ?, ?, ?)";
$item_stmt = $db->prepare($item_sql);
 
$stock_sql = "UPDATE product SET Stock = Stock - ? WHERE ProductID = ?";
$stock_stmt = $db->prepare($stock_sql);

foreach ($checkout_items as $item){
    $Subtotal = $item['totalProductPrice'] * $item['Quantity'];
    $item_stmt->execute([$OrderID, $item['ProductID'], $item['Quantity'], $item['totalProductPrice'], $Subtotal]);
    $stock_stmt->execute([$item['Quantity'], $item['ProductID']]);
}

$clear_sql = "DELETE FROM basketitem WHERE BasketID = ?";
$clear_stmt = $db->prepare($clear_sql);
$clear_stmt->execute([$BasketID]);

header("Location: ../../public/order_success.php?order_id=" . $OrderID);
exit;
?>
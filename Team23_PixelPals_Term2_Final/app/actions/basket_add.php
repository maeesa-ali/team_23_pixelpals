<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['UserID'])){
    header('Location: ../../public/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $ProductID = filter_input(INPUT_POST,'ProductID', FILTER_VALIDATE_INT);
    $Quantity = filter_input(INPUT_POST,'Quantity', FILTER_VALIDATE_INT);

    if ($ProductID && $Quantity && $Quantity>0){
        
        $user_id = $_SESSION['UserID'];

        $stmt = $db->prepare("SELECT BasketID FROM basket WHERE UserID = ?");
        $stmt->execute([$UserID]);
        $existing_basket = $stmt->fetch();

        if ($existing_basket){
            $basket_id = $existing_basket["BasketID"];
        } else {
            $insert_basket = $db->prepare("INSERT INTO basket (UserID) VALUES (?)");
            $insert_basket->execute([$UserID]);
            $basket_id = $db->lastInsertId();
        }


        $stmt_item = $db->prepare("SELECT Quantity FROM basketitem WHERE BasketID = ? AND ProductID = ?");
        $stmt_item->execute([$BasketID, $ProductID]);
        $existing_item = $stmt_item->fetch();

        if ($existing_item){
            $newQuantity = $existing_item["Quantity"] + $Quantity;

            $basket_update = $db->prepare("INSERT INTO basketitem (BasketID, ProductID, Quantity) VALUES (?, ?, ?)");
            $basket_update->execute([$BasketID, $ProductID, $Quantity]);
        } else {
            $basket_update = $db->prepare("INSERT INTO basketitem (BasketID, ProductID, Quantity) VALUES (?, ?, ?)");
            $basket_update->execute([$BasketID, $ProductID, $Quantity]);
        }
    }  

    header('Location: ../../public/basket.php');
    exit;
}
?>
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

    if ($Quantity !== false && $Quantity !== null && $ProductID){
        if ($Quantity <1){
            $Quantity = 1;
        }

        $basket_update =$db -> prepare("UPDATE basketitem SET Quantity = ? WHERE BasketID = ? AND ProductID = ?")->execute([$Quantity, $BasketID, $ProductID]);
        $basket_update = $db -> execute([$Quantity, $BasketID, $ProductID]);
    } else{
        $_SESSION["error"] = "Failed to update basket item: Quantity must be one or more";
    }
}
header('Location: ../../public/basket.php');
exit;
?>
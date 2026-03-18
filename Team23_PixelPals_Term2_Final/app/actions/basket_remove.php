<?php
session_start();
if (!isset($_SESSION['UserID'])){
    header('Location: login.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $ProductID = filter_input(INPUT_POST,'ProductID', FILTER_VALIDATE_INT);

    if ($ProductID){
        $UserID = $_SESSION['UserID'];
        $stmt = $db->prepare("SELECT BasketID FROM basket WHERE UserID = ?");
        $stmt-> execute([$UserID]);
        $basket = $stmt->fetch();
    

        if($basket){
            $BasketID = $basket['BasketID'];
            $delete_stmt = $db->prepare("DELETE FROM basketitem WHERE BasketID = ? AND ProductID = ?");
            $delete_stmt->execute([$BasketID, $ProductID]);
        } else{
            $_SESSION["error"] = "No basket found for user.";
        }
    } else {
        $_SESSION["error"] = "Invalid product ID.";
    }
}

header('Location: basket.php');
exit;
?>

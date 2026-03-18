<?php
session_start();

require_once "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // user must be logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../public/login.php");
        exit();
    }

    $product_id = $_POST['product_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO reviews (ProductID, UserID, Rating, Comment)
            VALUES (?, ?, ?, ?)";

    $stmt = $db->prepare($sql);
    $stmt->execute([$product_id, $user_id, $rating, $comment]);

    header("Location: ../../public/product.php?id=" . $product_id);
    exit();
}
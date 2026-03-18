<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = $_POST["id"];

    $stmt = $db->prepare("DELETE FROM product WHERE ProductID = ?");
    $stmt->execute([$id]);

}

header("Location: admin_products.php");
exit();
?>
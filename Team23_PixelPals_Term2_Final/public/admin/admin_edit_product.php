<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "db.php";

if (!isset($_GET["id"])) {
    header("Location: admin_products.php");
    exit();
}

$id = $_GET["id"];

// Fetch product
$stmt = $db->prepare("SELECT * FROM product WHERE ProductID = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: admin_products.php");
    exit();
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST["ProductName"];
    $description = $_POST["Description"];
    $category = $_POST["Category"];
    $price = $_POST["Price"];
    $stock = $_POST["Stock"];

    $sql = "UPDATE product 
            SET ProductName=?, Description=?, Category=?, Price=?, Stock=?
            WHERE ProductID=?";

    $stmt = $db->prepare($sql);
    $stmt->execute([$name, $description, $category, $price, $stock, $id]);

    header("Location: admin_products.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
</head>
<body>

<h1>Edit Product</h1>

<form method="POST">

    <label>Product Name:</label><br>
    <input type="text" name="ProductName" value="<?= htmlspecialchars($product['ProductName']) ?>" required><br><br>

    <label>Description:</label><br>
    <textarea name="Description" required><?= htmlspecialchars($product['Description']) ?></textarea><br><br>

    <label>Category:</label><br>
    <input type="text" name="Category" value="<?= htmlspecialchars($product['Category']) ?>" required><br><br>

    <label>Price:</label><br>
    <input type="number" step="0.01" name="Price" value="<?= $product['Price'] ?>" required><br><br>

    <label>Stock:</label><br>
    <input type="number" name="Stock" value="<?= $product['Stock'] ?>" required><br><br>

    <button type="submit">Update Product</button>

</form>

</body>
</html>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {//POST submits data to server, recieves response
	//POST is submitting what is stored as name by the user as ProductName in the db
    $name = $_POST["ProductName"];
    $description = $_POST["Description"];
    $category = $_POST["Category"];
    $price = $_POST["Price"];
    $stock = $_POST["Stock"];

    $sql = "INSERT INTO product 
            (ProductName, Description, Category, Price, Stock)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $db->prepare($sql);
    $stmt->execute([$name, $description, $category, $price, $stock]);

    header("Location: admin_products.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>
</head>
<body>

<h1>Add New Product</h1>

<form method="POST">

    <label>Product Name:</label><br>
    <input type="text" name="ProductName" required><br><br>

    <label>Description:</label><br>
    <textarea name="Description" required></textarea><br><br>

    <label>Category:</label><br>
    <input type="text" name="Category" required><br><br>

    <label>Price:</label><br>
    <input type="number" step="0.01" name="Price" required><br><br>

    <label>Stock:</label><br>
    <input type="number" name="Stock" required><br><br>

    <button type="submit">Create Product</button>

</form>

</body>
</html>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1); // make all errors show up instead of being hidden

session_start();
require_once "database.php";

// Fetch all products
$stmt = $db->query("SELECT * FROM product ORDER BY ProductID DESC");//stmt means statement: stores results of query
$products = $stmt->fetchAll(); // take all from the db and put it in a array called products
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Products</title>
</head>
<body>

<h1>Manage Products</h1>

//td is table data, th is table header
<a href="add_product.php">Add New Product</a>
<br><br>
<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Description</th>
        <th>Category</th>
        <th>Price</th>
        <th>Stock</th>
		<th>Status</th>
    </tr>

    <?php foreach ($products as $product): ?>
    	<?php
			if ($product['Stock'] <= 0) {
			    $status = "Out of Stock";
		} elseif ($product['Stock'] <= 10) { //change this number if necessary
    			$status = "Low Stock";
		} else {
    			$status = "In Stock";
				}
		?>
        <tr>
            <td><?= $product['ProductID'] ?></td>
            <td><?= htmlspecialchars($product['ProductName']) ?></td>
            <td><?= htmlspecialchars($product['Description']) ?></td>
            <td><?= htmlspecialchars($product['Category']) ?></td>
            <td>£<?= $product['Price'] ?></td>
            <td><?= $product['Stock'] ?></td>
        	<td><?= $status ?></td>
        </tr>
    <?php endforeach; ?>

</table>

</body>
</html>

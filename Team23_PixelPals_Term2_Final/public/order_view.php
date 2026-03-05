<?php
$conn = new mysqli("localhost", "root", "", "cs2team23_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    die("No order selected.");
}

$orderId = intval($_GET['id']);

/* Get Order Details */
$orderSql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($orderSql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$orderResult = $stmt->get_result();

if ($orderResult->num_rows === 0) {
    die("Order not found.");
}

$order = $orderResult->fetch_assoc();

/* Get Order Items */
$itemsSql = "
    SELECT 
        order_items.quantity,
        order_items.price,
        product.name
    FROM order_items
    JOIN product ON order_items.product_id = product.id
    WHERE order_items.order_id = ?
";

$stmt2 = $conn->prepare($itemsSql);
$stmt2->bind_param("i", $orderId);
$stmt2->execute();
$itemsResult = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #<?php echo $order['id']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>

<!-- Header -->
<header class="topBar">
    <img src="pixelPals.png" class="logo" alt="Logo">

    <div class="searchContainer">
        <form id="searchForm" action="products.php">
            <input type="text" id="searchInput" placeholder="Search">
        </form>
        <button class="clear-btn">×</button>
    </div>

    <div class="topLinks">
        <a href="basket.html">Basket</a>
    </div>
</header>

<!-- Navigation -->
<nav class="bottomNav">
    <a href="login.php">Log in</a>
    <a href="index.php">Home</a>
    <a href="products.php">Products</a>
    <a href="about.php">About Us</a>
    <a href="contact.php">Contact Us</a>
     <a href="orders.php">Orders</a>
</nav>

<!-- Page Content -->
<main style="padding:40px;">

    <h1>Order #<?php echo $order['id']; ?></h1>

    <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
    <p><strong>Date:</strong> <?php echo $order['created_at']; ?></p>
    <p><strong>Total:</strong> £<?php echo number_format($order['total'], 2); ?></p>

    <br>

    <h2>Products in this Order</h2>

    <?php if ($itemsResult->num_rows > 0): ?>

        <table border="1" cellpadding="10" cellspacing="0" width="100%">
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price (each)</th>
                <th>Subtotal</th>
            </tr>

            <?php while ($item = $itemsResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>£<?php echo number_format($item['price'], 2); ?></td>
                    <td>
                        £<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                    </td>
                </tr>
            <?php endwhile; ?>

        </table>

    <?php else: ?>
        <p>No items found for this order.</p>
    <?php endif; ?>

    <br>
    <a href="orders.php">← Back to Orders</a>

</main>

</body>
</html>

<?php
$conn->close();
?>

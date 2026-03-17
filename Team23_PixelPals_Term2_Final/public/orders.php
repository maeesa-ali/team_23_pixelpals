<?php
session_start();
require 'db_connect.php';

// check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// get orders for current user
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Past Orders</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
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
<main>
    <h1>Past Orders</h1>

    <?php if ($result->num_rows > 0): ?>

        <table border="1" cellpadding="10" cellspacing="0" width="100%">
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Total</th>
                <th>Date</th>
                <th>View</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['customer_email']); ?></td>
                    <td>£<?php echo number_format($row['total'], 2); ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td>
                        <a href="order_view.php?id=<?php echo $row['id']; ?>">
                            View
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>

        </table>

    <?php else: ?>
        <p>No orders found.</p>
    <?php endif; ?>

</main>

</body>
</html>

<?php
$conn->close();
?>

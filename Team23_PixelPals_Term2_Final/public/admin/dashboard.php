<?php
session_start();

// Security check
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../public/login.php"); // Updated path to general login or admin login
    exit();
}

require_once '../../app/config/db.php'; 

$totalCustomers = 0;
$totalMessages = 0;
$totalProducts = 0;

try {
    $totalCustomers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalMessages = $db->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
    $totalProducts = $db->query("SELECT COUNT(*) FROM product")->fetchColumn();
} catch (Exception $e) { 
    // Silently fail or log
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PixelPals Admin Dashboard</title>
</head>
<body>
    <h1>PixelPals Admin Dashboard</h1>
    <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong> | <a href="admin_login.php">Logout</a></p>

    <hr>

    <h3>User Management</h3>
    <ul>
        <li>Total Customers: <?php echo $totalCustomers; ?> - <a href="customers.php">Manage</a></li>
        <li>Inquiry Messages: <?php echo $totalMessages; ?> - <a href="messages.php">View</a></li>
    </ul>

    <h3>Inventory Management</h3>
    <ul>
        <li>Total Products: <?php echo $totalProducts; ?></li>
        <li><a href="admin_products.php">All Products</a></li>
        <li><a href="admin_create_product.php">Add New Product</a></li>
        <li><a href="stock_incoming.php">Stock Shipments</a></li>
    </ul>
</body>
</html>
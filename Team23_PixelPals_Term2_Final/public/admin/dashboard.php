<?php
session_start();


if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php"); 
    exit();
}


$host = 'localhost';
$db_name = 'cs2team23_db'; 
$username = 'cs2team23'; 
$password = '5JWJ5aZvA1TzknSYRW8I1niW1'; 

try {
    $db = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}


$totalCustomers = 0;
$totalMessages = 0;
$totalProducts = 0;

try {
    $totalCustomers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalMessages = $db->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
    $totalProducts = $db->query("SELECT COUNT(*) FROM product")->fetchColumn();
} catch (Exception $e) {

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PixelPals Admin Dashboard</title>
</head>
<body>
    <header style="display: flex; justify-content: space-between; align-items: center;">
        <h1>PixelPals Admin Panel</h1>
        <a href="../admin_login.php" class="btn-logout">Logout</a>
    </header>

    <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>.</p>

    <h2 class="section-title">User & Inquiry Management</h2>
    <div class="grid">
        <div class="card">
            <h3>Customers</h3>
            <p class="stat"><?php echo $totalCustomers; ?></p>
            <div class="btn-group">
                <a href="customers.php" class="btn">Manage Customers</a>
            </div>
        </div>

        <div class="card">
            <h3>Messages</h3>
            <p class="stat"><?php echo $totalMessages; ?></p>
            <div class="btn-group">
                <a href="messages.php" class="btn">View Inbox</a>
            </div>
        </div>
    </div>

    <h2 class="section-title">Inventory & Store Management</h2>
    <div class="grid">
        <div class="card" style="min-width: 400px;">
            <h3>Product Overview</h3>
            <p>Currently managing <strong><?php echo $totalProducts; ?></strong> products in the catalog.</p>
            <div class="btn-group" style="flex-direction: row; justify-content: center;">
                <a href="products.php" class="btn">View All Products</a>
                <a href="product_create.php" class="btn btn-secondary">+ Add New Product</a>
                <a href="stock_incoming.php" class="btn btn-secondary">Update Stock</a>
            </div>
        </div>
    </div>

</body>
</html>
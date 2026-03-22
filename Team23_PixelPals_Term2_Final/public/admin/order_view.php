<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Admin') {
    header('Location: ../../public/login.php');
    exit;
}

if (!isset($_GET['OrderId'])) {
    header("Location: orders.php");
    exit();
}
$orderId = intval($_GET['OrderId']);

$order_sql = "SELECT o.OrderID, o.Status FROM orders o JOIN users u ON o.UserID = u.UserID WHERE o.OrderID = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order = $order_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard -Manage Order #<?php echo $order['OrderID']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="admin-panel">
        <h1>Order #<?php echo $order['OrderID']; ?></h1>
        <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['Name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['Email']); ?></p>
        <p><strong>Current Status:</strong> <span style="font-weight:bold; color:#e67e22;"><?php echo htmlspecialchars($order['Status']); ?></span></p>

        <hr style="margin: 20px 0;">

        <h3>Update Order Status</h3>
        <form action="../../app/actions/admin_order_status_update.php" method="POST" class="status-form">
            
            <input type="hidden" name="order_id" value="<?php echo $order['OrderID']; ?>">
            
            <select name="status" class="status-select" required>
                <option value="Processing" <?php if($order['Status'] == 'Processing') echo 'selected'; ?>>Processing</option>
                <option value="Shipped" <?php if($order['Status'] == 'Shipped') echo 'selected'; ?>>Shipped</option>
                <option value="Delivered" <?php if($order['Status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                <option value="Returned" <?php if($order['Status'] == 'Returned') echo 'selected'; ?>>Returned</option>
                <option value="Cancelled" <?php if($order['Status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
            </select>
            
            <button type="submit" class="btn-update">Update Status</button>
        </form>
    </div>

    <h2>Items in this Order</h2>
    <?php if ($items_result->num_rows > 0): ?>
        <table border="1" cellpadding="10" cellspacing="0" width="100%">
            <tr>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price (each)</th>
                <th>Subtotal</th>
            </tr>

            <?php while ($item = $items_result->fetch_assoc()): ?>
                <?php $grand_total += $item['Subtotal']; ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['ProductName']); ?></td>
                    <td><?php echo $item['Quantity']; ?></td>
                    <td>£<?php echo number_format($item['totalProductPrice'], 2); ?></td>
                    <td>£<?php echo number_format($item['Subtotal'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
            
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Grand Total:</strong></td>
                <td><strong>£<?php echo number_format($grand_total, 2); ?></strong></td>
            </tr>
        </table>
    <?php else: ?>
        <p>No items found for this order. (This shouldn't happen!)</p>
    <?php endif; ?>
</body>
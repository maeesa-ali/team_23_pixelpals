<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Admin') {
    header('Location: ../../public/login.php');
    exit;
}

$sql = "SELECT o.OrderID, o.Status, u.Username, u.Email FROM orders o JOIN users u ON o.UserID = u.UserID ORDER BY CASE WHEN o.Status = 'Processing' THEN 1 ELSE 2 END, o.OrderID DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Orders</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php if ($result->num_rows > 0): ?>

        <table border="1" cellpadding="10" cellspacing="0" width="100%">
            <tr>
                <th>Order ID</th>
                <th>Customer Name/Email</th>
                <th>Total</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $row['OrderID']; ?></td>
                    
                    <td>
                        <strong><?php echo htmlspecialchars($row['Name']); ?></strong><br>
                        <small style="color: #666;"><?php echo htmlspecialchars($row['Email']); ?></small>
                    </td>
                    
                    <td>£<?php echo number_format($row['OrderTotal'] ?? 0, 2); ?></td>
                    
                    <td>
                        <span class="badge badge-<?php echo strtolower($row['Status']); ?>">
                            <?php echo htmlspecialchars($row['Status']); ?>
                        </span>
                    </td>
                    
                    <td>
                        <a href="order_view.php?id=<?php echo $row['OrderID']; ?>" 
                           style="padding: 5px 10px; background: #3498db; color: white; text-decoration: none; border-radius: 4px;">
                            View
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>

        </table>

    <?php else: ?>
        <p>No orders currently exist in the database.</p>
    <?php endif; ?>
</body>
</html>

<?php
$conn->close();
?>
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../public/admin/admin_login"); 
    exit();
}

require_once '../../app/config/db.php'; 
require_once '../../app/includes/flash.php'; 

try {
    $sql = "SELECT UserID, Username, FirstName, LastName, Email, DateOfBirth, 
            TIMESTAMPDIFF(YEAR, DateOfBirth, CURDATE()) AS CalculatedAge 
            FROM users ORDER BY UserID ASC";
    $stmt = $db->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customers List</title>
</head>
<body>

    <h1>Customers List</h1>
    <p><a href="../../puiblic/admin/dashboard.php">Back to Dashboard</a></p>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>UserID</th>
                <th>Username</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>DOB (YYYY-MM-DD)</th>
                <th>Age</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $row): ?>
            <tr>
                <td><?php echo $row['UserID']; ?></td>
                <td><?php echo htmlspecialchars($row['Username']); ?></td>
                <td><?php echo htmlspecialchars($row['FirstName']); ?></td>
                <td><?php echo htmlspecialchars($row['LastName']); ?></td>
                <td><?php echo htmlspecialchars($row['Email']); ?></td>
                <td><?php echo htmlspecialchars($row['DateOfBirth']); ?></td>
                <td><?php echo $row['CalculatedAge']; ?></td>
                <td>
                    <a href="../../public/admin/customer_edit.php?id=<?php echo $row['UserID']; ?>">Edit</a> | 
                    <a href="../../app/actions/admin_customer_delete.php?id=<?php echo $row['UserID']; ?>" 
                       onclick="return confirm('Are you sure?');" 
                       style="color:red;">Delete</a> 
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($customers)): ?>
                <tr><td colspan="8">No customers found in the database.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
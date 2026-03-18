<?php
session_start();


if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php"); 
    exit();
}


require_once '../../app/config/db.php'; 


try {
    $stmt = $db->query("SELECT * FROM contact_messages ORDER BY CreatedAt DESC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $messages = [];
    $error = "The table 'contact_messages' does not exist yet. Please create it in phpMyAdmin.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inquiry Messages</title>
</head>
<body>
    <h1>Customer Inquiry Messages</h1>
    <p><a href="../../public/admin/dashboard.php">Back to Dashboard</a></p>

    <?php if (isset($error)): ?>
        <p style="color: red; border: 1px solid red; padding: 10px;"><?php echo $error; ?></p>
    <?php endif; ?>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Date Received</th>
                <th>Customer Name</th>
                <th>Email Address</th>
                <th>Subject</th>
                <th>Message Content</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($messages)): ?>
                <tr>
                    <td colspan="5">No inquiry messages found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                <tr>
                    <td><?php echo date('d M Y, H:i', strtotime($msg['CreatedAt'])); ?></td>
                    <td><?php echo htmlspecialchars($msg['Name']); ?></td>
                    <td><?php echo htmlspecialchars($msg['Email']); ?></td>
                    <td><?php echo htmlspecialchars($msg['Subject']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($msg['Message'])); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
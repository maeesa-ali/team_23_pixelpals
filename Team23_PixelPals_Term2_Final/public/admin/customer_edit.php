<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php"); 
    exit();
}
require_once '../../app/config/db.php'; 



$user_id = $_GET['id'] ?? null;
$user = null;

if ($user_id) {
    $stmt = $db->prepare("SELECT * FROM users WHERE UserID = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$user) {
    die("User not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Customer</title>
</head>
<body>
    <h1>Edit User: <?php echo htmlspecialchars($user['Username']); ?></h1>
    <p><a href="../../public/admin/customers.php">Back to List</a></p>

    <form action="admin_customer_update.php" method="POST">
        <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">

        <label>Username:</label><br>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['Username']); ?>" required><br><br>

        <label>First Name:</label><br>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['FirstName']); ?>" required><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['LastName']); ?>" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required><br><br>
        
        <label>Date Of Birth:</label><br>
        <input type="date" name="dob" value="<?php echo $user['DateOfBirth']; ?>" required><br><br>

        <button type="submit">Update Customer</button>
    </form>
</body>
</html>
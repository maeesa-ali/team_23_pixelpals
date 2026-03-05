<?php

session_start();
require __DIR__ . '/../../app/config/db.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    $stmt = $db->prepare("SELECT AdminID, Password, FirstName FROM admin WHERE TRIM(LOWER(Email)) = ?");
    $stmt->execute([$email]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        if ($password === $admin['Password']) {
            $_SESSION['admin_id'] = $admin['AdminID'];
            $_SESSION['admin_name'] = $admin['FirstName'];
            header("Location: dashboard.php");
            exit();
        } else {
            $errors[] = "Wrong password. Please try again.";
        }
    } else {
        $errors[] = "Admin email not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Admin Login</title></head>
<body>

<h2>Admin Login</h2>

<?php if (!empty($errors)): ?>
    <?php foreach ($errors as $error): ?>
        <p style='color:red;'><?php echo htmlspecialchars($error); ?></p>
    <?php endforeach; ?>
<?php endif; ?>

<form method="POST" action="admin_login.php">
    <label>Email</label><br>
    <input type="email" name="email" required><br><br>
    <label>Password</label><br>
    <input type="password" name="password" required><br><br>
    <button type="submit">Login</button>
</form>

</body>
</html>
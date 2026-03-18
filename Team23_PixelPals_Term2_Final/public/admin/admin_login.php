<?php
session_start(); 

$host = 'localhost';
$db_name = 'cs2team23_db'; 
$username = 'cs2team23'; 
$password = '5JWJ5aZvA1TzknSYRW8I1niW1'; 

try {
    $db = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim(strtolower($_POST['email'] ?? ''));
    $input_password = $_POST['password'] ?? '';

    $stmt = $db->prepare("SELECT AdminID, Password, FirstName FROM admin WHERE LOWER(Email) = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        if ($input_password === $admin['Password']) {
            $_SESSION['admin_id'] = $admin['AdminID'];
            $_SESSION['admin_name'] = $admin['FirstName'];
            header("Location: ..admin/dashboard.php");
            exit();
        } else {
            $errors[] = "Wrong password.";
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
            <p style='color:red;'><?php echo $error; ?></p>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="POST">
        <label>Email</label><br>
        <input type="email" name="email" required><br><br>
        <label>Password</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
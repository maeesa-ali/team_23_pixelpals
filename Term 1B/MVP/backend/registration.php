<?php
session_start();
require_once __DIR__ . "/database.php";

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {

    $username  = trim($_POST["username"] ?? "");
    $firstname = trim($_POST["firstname"] ?? "");
    $lastname  = trim($_POST["lastname"] ?? "");
    $age       = (int)($_POST["age"] ?? 0);
    $email     = trim($_POST["email"] ?? "");
    $password  = $_POST["password"] ?? "";

    if ($username === "" || $firstname === "" || $lastname === "" || $email === "" || $password === "") {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email is not valid.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    if ($age < 1 || $age > 120) {
        $errors[] = "Age must be between 1 and 120.";
    }

    if (empty($errors)) {
        $check = $db->prepare("SELECT UserID FROM users WHERE Email = ? LIMIT 1");
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = "Email already exists.";
        }
    }

    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $insert = $db->prepare("
            INSERT INTO users (Username, FirstName, LastName, Age, Email, Password)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        if ($insert->execute([$username, $firstname, $lastname, $age, $email, $passwordHash])) {
           
        } else {
            $errors[] = "Registration failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PixelPals – Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="pageWrapper">

    <?php if (!empty($errors)): ?>
        <div style="color:red; text-align:center;">
            <?php foreach ($errors as $e): ?>
                <div><?php echo htmlspecialchars($e); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div style="color:green; text-align:center;">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="registration.php" novalidate>

        <label>Username</label>
        <input type="text" name="username" required>

        <label>First Name</label>
        <input type="text" name="firstname" required>

        <label>Last Name</label>
        <input type="text" name="lastname" required>

        <label>Age</label>
        <input type="number" name="age" min="1" max="120" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" name="submit">Register</button>

        <a href="login.php">Already have an account?</a>

    </form>
</div>

</body>
</html>
<style>
    * 
    {
        box-sizing: border-box;
    }

   body /*overall page style*/
    {
        margin: 0;
        font-family: Arial, sans-serif;
        background: linear-gradient(#DE4FFF, #77ADFF, #D5A4Ff);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .pageWrapper /* Main container*/
    {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    form /*form container styles*/
    {
        margin: 0 auto;
        background: #C9DAFF;
        padding: 20px;
        width: 400px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    label /*input field labels*/
    {
        display: block;
        margin-top: 10px;
        font-weight: bold;
        font-size: 14px;
    }

    input /*input field styles*/
    {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
    }

    input:focus /*input focus styles*/
    {
        outline: none;
        border-color: #4A90E2;
        box-shadow: 0 0 5px rgba(74,144,226,0.5);
    }

    button /*login button styles*/
    {
        width: 100%;
        padding: 10px;
        margin-top: 15px;
        background: #C0ED45;
        color: black;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
    }

    button:hover /*login button hover styles*/
    {
        background: #3a7ac8;
    }

    a /*registration link styles*/
    {
        text-align: center;
        display: block;
        margin-top: 12px;
        color: #4A90E2;
        text-decoration: none;
    }

    a:hover /*registration link hover styles*/
    {
        text-decoration: underline;
    }

    #error, #success /*error and success message styles*/
    {
        text-align: center;
        margin-top: 10px;
        font-size: 14px;
    }

    .topBar /* Top blue bar*/
    {
        background: #3F8BE0;
        display: flex;
        align-items: center;
        padding: 10px 20px;
        gap: 20px;
        width: 100%;
        box-sizing: border-box;
    }

    .topBar .logo /* Logo*/
    {
        height: 60px;
    }

    .searchContainer /* Search bar*/
    {
        flex: 1;
        position: relative;
    }

    .searchContainer input /* Search bar input field*/
    {
        width: 100%;
        padding: 12px 40px 12px 15px;
        border-radius: 20px;
        border: none;
        font-size: 16px;
    }

    .topLinks a /* Basket Link*/
    {
        color: white;
        font-size: 18px;
        margin-left: 20px;
        text-decoration: none;
    }

    .bottomNav /* Purple nav bar*/
    {
        background: #8962C6;
        display: flex;
        justify-content: space-evenly;
        padding: 10px 0;
        width: 100%;
        box-sizing: border-box;
    }

    .bottomNav a /* Navigation links*/
    {
        color: white;
        font-size: 18px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 5px;
    }
</style>
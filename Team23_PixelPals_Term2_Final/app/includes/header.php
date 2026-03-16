<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>PixelPals</title>

<link rel="stylesheet" href="/assets/css/styles.css">
</head>

<body>

<nav class="navbar">

<div class="nav-left">
<a href="/index.php">
<img src="/assets/img/logo.png" class="logo" alt="PixelPals Logo">
</a>

<a href="/index.php">PixelPals</a>
</div>

<div class="nav-links">
<a href="/index.php">Home</a>
<a href="/products.php">Products</a>
<a href="/about.php">About</a>
<a href="/contact.php">Contact</a>
</div>

<div class="nav-right">
<a href="/basket.php">Basket</a>
<a href="/account.php">Account</a>

<?php if(isset($_SESSION['user_id'])): ?>
<a href="/logout.php">Logout</a>
<?php else: ?>
<a href="/login.php">Login</a>
<a href="/signup.php">Signup</a>
<?php endif; ?>

<?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
<a href="/admin/dashboard.php">Admin</a>
<?php endif; ?>

</div>

</nav>

<?php if(isset($_SESSION['success'])): ?>
<div class="flash-success">
<?php echo $_SESSION['success']; ?>
</div>
<?php unset($_SESSION['success']); endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<div class="flash-error">
<?php echo $_SESSION['error']; ?>
</div>
<?php unset($_SESSION['error']); endif; ?>
  
<main class="container">

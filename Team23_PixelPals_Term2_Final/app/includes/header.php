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
<img src="/assets/img/logo.png" class="logo">
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

</div>

</nav>

<main class="container">

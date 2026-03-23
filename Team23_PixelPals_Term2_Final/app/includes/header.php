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
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>




  <!-- Header -->
  <header class="header">
    <img src="assets/img/logo.png" class="logo" alt="PixelPals logo">

    <div class="searchbar">
      <form  action="products.php"  method="GET">
        <input type="text" id="searchinput" name="q" placeholder="Search for anything!">
        <button type="button" class="clearbutton">×</button>
      </form>
    </div>

    <div class="topLinks">
      <a href="basket.php">Basket</a>

      <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="login.php">Login</a>
        <a href="signup.php">Signup</a>

      <?php else: ?>
        <a href="logout.php">Logout</a>
      <?php endif; ?>
    </div>
  </header>

  <!-- Navigation bar -->
  <nav class="navbar">
    <a href="index.php">Home</a>
    <a href="products.php">Products</a>
    <a href="account.php">Account</a>
    <a href="about.php">About Us</a>
    <a href="contact.php">Contact Us</a>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
      <a href="admin/dashboard.php">Admin</a>
    <?php endif; ?>
  </nav>

  <!-- flash messages area -->
  <div class="flash-container">

    <?php if (isset($_SESSION['success'])): ?>
      <div class="flash success">
        <?php 
          echo $_SESSION['success']; 
          unset($_SESSION['success']);
        ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="flash error">
        <?php 
          echo $_SESSION['error']; 
          unset($_SESSION['error']);
        ?>
      </div>
    <?php endif; ?>

  </div>

  <!-- opens main container -->
  <main class="container">

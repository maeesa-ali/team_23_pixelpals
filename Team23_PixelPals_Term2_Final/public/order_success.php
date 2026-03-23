<?php
session_start();

require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/config/config.php';

if (!isset($_GET['order_id']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$orderId = (int) $_GET['order_id'];
$successMessage = $_SESSION['success'] ?? "Your order has been placed successfully!";
unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success!</title>
    <link rel="stylesheet" type="text/css" href="assets/css/styles.css" />

    <style>
      .success-card {
        background: white;
        border-radius: 15px;
        padding: 40px;
        text-align: center;
        box-shadow: 0px 6px 20px rgba(0,0,0,0.1);
      }

      .success-icon {
        font-size: 70px;
        color: #4CAF50;
        margin-bottom: 20px;
      }

      .success-message {
        font-size: 22px;
        margin-bottom: 15px;
      }

      .order-number {
        font-size: 18px;
        color: #023475;
        margin-bottom: 25px;
      }

      .button-group {
        display: flex;
        justify-content: center;
        gap: 20px;
      }

      .primary-btn {
        background: #3F8BE0;
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
      }

      .primary-btn:hover {
        background: #2f6db4;
      }

      .secondary-btn {
        background: #8962C6;
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
      }

      .secondary-btn:hover {
        background: #6c4ea5;
      }
    </style>
  </head>

  <body>
    <div class="container">
      <header>
        <nav class="navbar">

          <div class="nav-left">
            <a href="index.php">
              <img src="/assets/img/logo.png" class="logo" alt="PixelPals Logo">
            </a>

            <a href="index.php">PixelPals</a>
          </div>

          <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="products.php">Products</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
          </div>

          <div class="nav-right">
            <a href="basket.php">Basket</a>
            <a href="account.php">Account</a>

            <?php if(isset($_SESSION['user_id'])): ?>
            <a href="logout.php">Logout</a>
            <?php else: ?>
            <a href="login.php">Login</a>
            <a href="signup.php">Signup</a>
            <?php endif; ?>

            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="/admin/dashboard.php">Admin</a>
            <?php endif; ?>

            <!-- Search Bar 
            <div class="searchContainer">
              <input type="text" placeholder="Search">
            </div>
            -->
          </div>
        </nav>
      </header>

      <?php if(isset($_SESSION['error'])): ?>
      <div class="flash-error">
      <?php echo $_SESSION['error']; ?>
      </div>
      <?php unset($_SESSION['error']); endif; ?>

      <h1 class="page-title">Order Confirmed</h1>
      <div class="success-card">
        <div class="success-icon">
          <p>&#10004;</p>
        </div>

        <div class="success-message">
          <?php echo htmlspecialchars($successMessage); ?>
        </div>

        <div class="order-number">
          Order ID: <strong>#<?php echo $orderId; ?></strong>
        </div>

        <p>
        Thank you for shopping with PixelPals!  
        A confirmation email will be sent shortly.
        </p>

        <br>

        <div class="button-group">
          <a href="index.php" class="primary-btn">
          Continue Shopping
          </a>

          <a href="account.php" class="secondary-btn">
          View My Orders
          </a>
        </div>
      </div>

      <footer class="footer">
        <p><strong>PixelPals</strong></p>
        <p>Ergonomic gaming accessories for children</p>
        <p>© 2026 PixelPals</p>
      </footer>
    </div>
  </body>
</html>


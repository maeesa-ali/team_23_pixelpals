<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Details</title>
  <link rel="stylesheet" href="css/styles.css">
</head>



<body>

  <?php
$conn = new mysqli("localhost", "root", "", "cs2team23_db");

$id = $_GET['id'] ?? 0;

$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "<p>Product not found.</p>";
    exit();
}

// Create base image name (remove spaces)
$baseName = str_replace(' ', '', $product['name']);
?>

  <!-- Header with logo and search -->
<header class="topBar">
  <img src="pixelPals.png" class="logo"> <!-- Logo Image -->

  <!-- Search Bar -->
  <div class="searchContainer">
    <form  action="products.html"  method="GET">
      <input type="text" id="searchInput" name="q" placeholder="Search">
      </form>
      <button class="clear-btn">×</button>
  </div>



  <!-- Wishlist & Basket Links -->
  <div class="topLinks">
      <a href="basket.html"> Basket</a>
  </div>
</header>



<!-- Bottom purple nav bar -->
<nav class="bottomNav">
  <a href="index.html"> Home</a>
  <a href="login.html">Log in</a>
  <a href="products.html"> Products</a>
  <a href="contact.html"> Contact Us</a>
  <a href="about.html">About Us</a>
</nav>

<main class="product-page">

    <!-- Main image -->
  <div class="main-image">
    <img id="mainImage" src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
   
</div>

<!-- Side thumbnails -->
<div class="side-images" id="thumbnailContainer">
   
    <?php for ($i = 1; $i <= 4; $i++): ?>
  <?php $imgPath = "product-images/" . $baseName . $i . ".png"; ?>

  <img class="thumb" src="<?= $imgPath ?>" alt="Thumbnail <?= $i ?>">
  <img class="thumb" src="<?= $imgPath ?>" alt="Thumbnail2 <?= $i ?>">
  <img class="thumb" src="<?= $imgPath ?>" alt="Thumbnail3 <?= $i ?>">
  <img class="thumb" src="<?= $imgPath ?>" alt="Thumbnail4 <?= $i ?>">

<?php endfor; ?>
</div>
    
  <!-- PRODUCT INFO -->
  <div class="product-info">

    <h1><?= htmlspecialchars($product['name']) ?></h1>

    <p><?= htmlspecialchars($product['description']) ?></p>

    <p><strong>Category:</strong> <?= $product['category'] ?></p>

    <p><strong>Price:</strong> £<?= number_format($product['price'], 2) ?></p>

    <p><strong>Stock:</strong>
      <?= $product['stock'] > 0 ? $product['stock'] . " available" : "Out of stock" ?>
    </p>

    <p><strong>Age:</strong> <?= $product['min_age'] ?>–<?= $product['max_age'] ?></p>

    <button class="view-product">Add to basket</button>

  </div>

  <!-- REVIEW FORM -->
  <h3>Leave a Review</h3>

  <form action="app/actions/review_add.php" method="POST">

    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">

    <label>Rating:</label>
    <select name="rating" required>
      <option value="5">5 ⭐</option>
      <option value="4">4 ⭐</option>
      <option value="3">3 ⭐</option>
      <option value="2">2 ⭐</option>
      <option value="1">1 ⭐</option>
    </select>

    <br><br>

    <label>Comment:</label>
    <textarea name="comment" required></textarea>

    <br><br>

    <button type="submit">Submit Review</button>

  </form>

  <!-- REVIEWS DISPLAY -->
  <h3>Reviews</h3>

  <?php
  $sql = "SELECT * FROM reviews WHERE product_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0):
      while ($review = $result->fetch_assoc()):
  ?>

      <div class="review">
          <p>Rating: <?= $review['rating'] ?> ⭐</p>
          <p><?= htmlspecialchars($review['comment']) ?></p>
      </div>

  <?php
      endwhile;
  else:
  ?>
      <p>No reviews yet.</p>
  <?php endif; ?>

</main>

</body>
</html>

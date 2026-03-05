
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome page</title>
  <link rel="stylesheet" type="text/css" href="css/styles.css" />
</head>

<body>

  <!-- Header -->
  <header class="topBar">
    <img src="pixelPals.png" class="logo">

    <div class="searchContainer">
      <form  action="products.html"  method="GET">
        <input type="text" id="searchInput" name="q" placeholder="Search">
      </form>
      <button class="clear-btn">×</button>
    </div>

    <div class="topLinks">
      <a href="basket.html">Basket</a>
    </div>
  </header>

  <!-- Bottom Navigation -->
  <nav class="bottomNav">
    <a href="login.php">Log in</a>
    <a href="index.php">Home</a>
    <a href="products.php">Products</a>
    <a href="about.php">About Us</a>
    <a href="contact.php">Contact Us</a>
    <a href="orders.php">Orders</a>   
 
  </nav>

  <!-- Page Content -->
  <main>

    <section id="welcome-message">
      <h2>Welcome to Pixel Pals!</h2>
      <p>Pixel Pals is great for finding gaming accessories for young children!</p>

      <form action="products.html" method="GET">
        <button type="submit" class="shop-now-btn">Shop Now!</button>
      </form>
    </section>

    <section class="card">
      <h2>Shop by Category</h2>

      <div class="category-buttons">

        <a href="products.html?q=chair">
          <button class="cat-btn cat-headphones">Ergonomic Chairs </button>
        </a>

        <a href="products.html?q=gameing desks">
          <button class="cat-btn cat-headsets">Gameing Desks</button>
        </a>

        <a href="products.html?q=moniter stands">
          <button class="cat-btn cat-controllers">Moniter Stands</button>
        </a>

        <a href="products.html?q=support">
          <button class="cat-btn cat-keyboards">Wrist And Arm Support</button>
        </a>

        <a href="products.html?q=gameing accessory">
          <button class="cat-btn cat-gameingAcsessorry">Gameing Acsessories</button>
        </a>

      </div>
    </section>

    <section id="recommended">
      <h2>Recommended Products</h2>
      <div class="recommended-grid" id="recommendedGrid"></div>
    </section>



  </main>

  <script src="js/script.js"></script>

</body>
</html>

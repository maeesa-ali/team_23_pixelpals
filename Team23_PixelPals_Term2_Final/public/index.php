 <!-- Header + navbar -->
<?php include '../app/includes/header.php'; ?>

  
    <!-- HERO SECTION -->
     <section class="herobanner">
        <div class="section-content">
            <div class ="hero-details">
                <h2 class="title">PixelPals</h2>
                <h3 class="subtitle">Welcome to PixelPals!</h3>
                <p class="description">Designing healthier gaming experiences for young players</p>

                <div class="hero-buttons">
                  <a href="products.php" class="shopnow-button">Shop All Products!</a>
                  <a href="#categories" class="categories-button">Browse Categories!</a>
                </div>
                
            </div>
            <div class="hero-image-wrapper">
                <img src="assets/img/hero/herobanner.png" alt="Hero" class="herobanner-image">
            </div>
        </div>
     </section>


     <!-- CATEGORIES. -->
    <section class="categories" id="categories">
      <h2>Shop by Category</h2>

      <div class="categorycard">

        <a href="products.php?q=chair">
          <button class="cat-btn cat-chair">Ergonomic Chairs </button>
        </a>

        <a href="products.php?q=gaming desks">
          <button class="cat-btn cat-desk">Gaming Desks</button>
        </a>

        <a href="products.php?q=monitor stands">
          <button class="cat-btn cat-monitor">Monitor Stands</button>
        </a>

        <a href="products.php?q=support">
          <button class="cat-btn cat-support">Wrist And Arm Support</button>
        </a>

        <a href="products.php?q=gaming accessory">
          <button class="cat-btn cat-accessory">Gaming Acsessories</button>
        </a>

      </div>
    </section>

    <!-- FEATURED PRODUCTS. -->
    <section class="featured">
      <h2>Featured Products</h2>

      <div class="featured-grid">
        <div class="product-card">
            <a href="product.php?id=1">
                <img src="assets/img/products/dc_ergochair_pink.png" alt ="Ergonomic chair">
                <h3>Ergonomic Chair</h3>
                <p class="price">£199.99 </p>
            </a>
            
            <form action="../app/actions/basket_add.php" method="POST">
              <input type="hidden" name="product_id" value="1">
              <button type="submit" class="add-to-cart">Add to Basket</button>
            </form>
            
        </div>

        <div class="product-card">
            <a href="product.php?id=2">
                <img src="assets/img/products/dc_adjustabletable.png" alt="Gaming desk">
                <h3>Gaming Desk</h3>
                <p class="price">£149.99</p>
            </a>

            <form action="../app/actions/basket_add.php" method="POST">
              <input type="hidden" name="product_id" value="2">
              <button type="submit" class="add-to-cart">Add to Basket</button>
            </form>

        </div>

        <div class="product-card">
            <a href="product.php?id=3">
                <img src="assets/img/products/dc_monitorstand.png" alt="Monitor Stand">
                <h3>Monitor Stand</h3>
                <p class="price">£39.99</p>
            </a>

            <form action="../app/actions/basket_add.php" method="POST">
              <input type="hidden" name="product_id" value="3">
              <button type="submit" class="add-to-cart">Add to Basket</button>
            </form>  

        </div>

        <div class="product-card">
            <a href="product.php?id=4">
                <img src="assets/img/products/km_wristrest.png" alt="Wrist Support">
                <h3>Wrist & Arm Support</h3>
                <p class="price">£24.99</p>
            </a>

            <form action="../app/actions/basket_add.php" method="POST">
              <input type="hidden" name="product_id" value="4">
              <button type="submit" class="add-to-cart">Add to Basket</button>
            </form>
            
        </div>

      </div>
    </section>
  
  
  <!-- FOOTER -->
   <?php include '../app/includes/footer.php'; ?>

</body>
</html>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Page</title>
    <link rel = "stylesheet"  type="text/css" href="css/styles.css" />
   
</head>
<body>
  <!-- Header with logo and search -->
   <header class="topBar">
    <img src="pixelPals.png" class="logo"> <!-- Logo Image -->

    <!-- Search Bar -->
    <div class="searchContainer">
        <form id="searchForm" action="products.html">
            <input type="text" id="searchInput" placeholder="Search">
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
    <a href="login.php">Log in</a>
    <a href="index.php"> Home</a>
    <a href="products.php"> Products</a>
    <a href="about.php">About Us</a>
    <a href="contact.php"> Contact Us</a>

</nav>


    <div class="page-container">

        <!-- Product Grid -->
        <main class="product-grid" id="productGrid"></main>

        <!-- Filters -->
        <aside class="filters">
            <h3>Filters</h3>

            <!-- Price Filter -->
            <div class="filter-group">
                <h4>Price</h4>
                <label><input type="radio" name="pricePreset" value="0-10"> £0 – £10</label><br>
                <label><input type="radio" name="pricePreset" value="10-20"> £10 – £20</label><br>
                <label><input type="radio" name="pricePreset" value="20-50"> £20 – £50</label><br>
                <label>
                    <input type="radio" name="pricePreset" value="custom" id="customPriceToggle"> Custom price range
                </label>
                <div id="customPriceFields" style="opacity:0.4; pointer-events:none; margin-top:5px;">
                    From <input type="number" class="priceInput" id="priceFromCustom" placeholder="£0.00"><br>
                    To <input type="number" class="priceInput" id="priceToCustom" placeholder="£0.00">
                </div>
            </div>

            <!-- Age Filter -->
            <div class="filter-group">
                <h4>Recommended Age</h4>
                <label><input type="radio" name="agePreset" value="3"> 3+</label><br>
                <label><input type="radio" name="agePreset" value="5"> 5+</label><br>
                <label><input type="radio" name="agePreset" value="7"> 7+</label><br>
                <label>
                    <input type="radio" name="agePreset" value="custom" id="customAgeToggle"> Custom age range
                </label>
                <div id="customAgeFields" style="opacity:0.4; pointer-events:none; margin-top:5px;">
                    From <input type="number" id="minAge" class="ageInput" placeholder="0"><br>
                    To <input type="number" id="maxAge" class="ageInput" placeholder="0">
                </div>
            </div>

            <button id="applyFilters">Apply Filters</button>
        </aside>

    </div>

    

    <script src="js/script.js"></script>

</body>
</html>
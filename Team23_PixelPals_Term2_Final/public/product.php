<?php
// This is the main storefront page, so the category links and first actions live here.
// We only need session state here for auth checks and the optional admin preview banner.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Shared auth helpers keep access rules and admin preview mode consistent with the rest of the site.
require_once '../app/includes/auth.php';
require_once '../app/includes/admin_preview.php';
requireAuthenticatedSession();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PixelPals | Gaming Setups for Growing Players</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    /* Smooth scrolling makes the category jump link feel a little nicer on the homepage. */
    html {
      scroll-behavior: smooth;
    }
    /* These small hover effects keep the homepage cards from feeling too static. */
    .cta-row a {
      transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .cta-row a:hover,
    .category-card:hover {
      transform: translateY(-2px);
    }

    /* The top section is split into intro copy on the left and quick stats on the right. */
    .hero {
      display: grid;
      grid-template-columns: 1.15fr 0.85fr;
      gap: 24px;
      align-items: stretch;
      margin-bottom: 28px;
    }

    /* Shared card styling keeps the homepage sections visually tied together. */
    .section-card {
      background: var(--card);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.6);
      border-radius: 30px;
      box-shadow: var(--shadow);
    }

    .hero-copy::before {
      content: "";
      position: absolute;
      width: 140px;
      height: 140px;
      left: -30px;
      top: -30px;
      background:
        linear-gradient(90deg, rgba(255,255,255,0.45) 50%, transparent 50%),
        linear-gradient(rgba(255,255,255,0.45) 50%, transparent 50%);
      background-size: 18px 18px;
      opacity: 0.5;
      transform: rotate(10deg);
    }

    .hero-copy p {
      max-width: 58ch;
      font-size: 1.05rem;
      line-height: 1.7;
      opacity: 0.92;
    }

    .cta-row {
      display: flex;
      flex-wrap: wrap;
      gap: 14px;
      margin-top: 26px;
    }

    .cta-row a {
      padding: 14px 20px;
      border-radius: 18px;
      font-weight: 700;
    }

    .cta-main {
      background: linear-gradient(135deg, var(--mint), #f5ff9a);
    }

    .cta-secondary {
      background: rgba(17, 37, 77, 0.08);
      border: 1px solid var(--outline);
    }

    .content-grid {
      display: grid;
      grid-template-columns: 1.3fr 0.7fr;
      gap: 24px;
      margin-bottom: 40px;
    }

    .section-card {
      padding: 28px;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(247, 250, 255, 0.82));
    }

    .section-card h2 {
      margin: 0 0 10px;
      font-size: 1.9rem;
    }

    .section-card p.lead {
      margin: 0 0 24px;
      line-height: 1.7;
      opacity: 0.86;
    }

    /* Categories are shown in a compact grid so the homepage doubles as a quick route into the catalogue. */
    .category-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px;
    }

    .category-card {
      padding: 20px;
      border-radius: 24px;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(201, 218, 255, 0.92));
      border: 1px solid rgba(17, 37, 77, 0.08);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      box-shadow: 0 14px 28px rgba(17, 37, 77, 0.08);
      position: relative;
      overflow: hidden;
    }

    .category-card::after {
      content: "";
      position: absolute;
      inset: auto -30px -40px auto;
      width: 110px;
      height: 110px;
      border-radius: 50%;
      background: rgba(116, 70, 200, 0.08);
    }

    .category-card span {
      display: inline-block;
      margin-bottom: 10px;
      padding: 8px 10px;
      border-radius: 12px;
      background: linear-gradient(90deg, rgba(116, 70, 200, 0.12), rgba(255, 109, 178, 0.12));
      font-size: 0.9rem;
      font-weight: 700;
    }

    .category-card h3 {
      margin: 0 0 8px;
      font-size: 1.2rem;
      position: relative;
      z-index: 1;
    }

    .category-card p {
      margin: 0;
      line-height: 1.55;
      opacity: 0.82;
      position: relative;
      z-index: 1;
    }

    /* These smaller highlight cards explain the value of the store without taking over the page. */
    .highlight-list {
      display: grid;
      gap: 14px;
    }

    .highlight-item {
      padding: 18px;
      border-radius: 22px;
      background: linear-gradient(135deg, rgba(87, 166, 255, 0.14), rgba(116, 70, 200, 0.14));
      border: 1px solid var(--outline);
    }

    .highlight-item strong {
      display: block;
      margin-bottom: 8px;
    }

    @media (max-width: 900px) {
      .hero,
      .content-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 640px) {
      .category-grid {
        grid-template-columns: 1fr;
      }

      .hero-copy,
      .hero-panel,
      .section-card {
        padding: 22px;
      }

    }
  </style>
</head>
<body>
  <?php renderAdminPreviewBanner(); ?>
  <div class="site-shell">
    <!-- Shared customer header and nav stay the same across the public-facing pages. -->
    <header class="topBar">
      <a class="brand" href="index.php">
        <img src="assets/img/logo.png" alt="PixelPals logo" class="logo">
        <div class="brand-copy">
          <strong>PixelPals</strong>
          <span>Comfort-first gaming gear for growing players</span>
        </div>
      </a>

      <div class="searchContainer">
        <form action="products.php" method="GET">
          <input type="text" id="searchInput" name="q" placeholder="Search chairs, desks, stands and more">
        </form>
      </div>

            <div class="topLinks">
                <a class="chip-link" href="basket.php">Basket</a>
                <?php if (isAdminPreviewMode()): ?>
                  <a class="primary-link" href="admin/dashboard.php">Admin Dashboard</a>
                <?php elseif (isset($_SESSION['user_id'])): ?>
                  <a class="primary-link" href="account.php">My Account</a>
                <?php else: ?>
                  <a class="primary-link" href="login.php">Log In</a>
                <?php endif; ?>
      </div>
    </header>

    <nav class="bottomNav">
        <div class="nav-links">
          <a class="active" href="index.php">Home</a>
          <a href="products.php">Products</a>
          <a href="about.php">About Us</a>
          <a href="contact.php">Contact Us</a>
          <a href="orders.php">Orders</a>
          <?php if (isAdminPreviewMode()): ?>
            <a href="admin/dashboard.php">Admin Dashboard</a>
          <?php elseif (isset($_SESSION['user_id'])): ?>
            <a href="logout.php">Log Out</a>
          <?php else: ?>
            <a href="signup.php">Create Account</a>
        <?php endif; ?>
      </div>
    </nav>

    <!-- The homepage content starts here. -->
    <main>
      <!-- Hero section: quick intro to the shop and a couple of easy first actions. -->
      <section class="hero">
        <div class="hero-copy">
          <span class="eyebrow">PixelPals</span>
          <h1>Gaming gear for younger players.</h1>
          <p>
            Browse desks, chairs, headsets, controllers, keyboards and accessories chosen to be easier and
            more comfortable for younger players to use.
          </p>

          <div class="cta-row">
            <a class="cta-main" href="products.php">Shop All Products</a>
            <a class="cta-secondary" href="#categories">Browse Categories</a>
          </div>
        </div>

        <aside class="hero-panel">
          <div class="hero-stat">
            <strong>5</strong>
            product categories in the shop
          </div>
          <div class="hero-stat">
            <strong>25</strong>
            products currently available
          </div>
          <div class="hero-stat">
            <strong>Simple filters</strong>
            search by category and narrow products quickly
          </div>
        </aside>
      </section>

      <!-- Main homepage content: category shortcuts on the left and store highlights on the right. -->
      <section class="content-grid">
        <div class="section-card" id="categories">
          <h2>Shop by Category</h2>
          <p class="lead">Choose a category to go straight to the products that fit it.</p>

          <!-- Each category card links straight into the filtered products page. -->
          <div class="category-grid">
            <a class="category-card" href="products.php?category=Desks+%26+Chairs">
              <span>Main Setup</span>
              <h3>Desks &amp; Chairs</h3>
              <p>Chairs and desks for the main setup.</p>
            </a>

            <a class="category-card" href="products.php?category=Audio+%26+Headsets">
              <span>Audio</span>
              <h3>Audio &amp; Headsets</h3>
              <p>Headsets and audio gear for gaming, calls and desk use.</p>
            </a>

            <a class="category-card" href="products.php?category=Controllers+%26+Grips">
              <span>Controllers</span>
              <h3>Controllers &amp; Grips</h3>
              <p>Controllers and grip add-ons for smaller hands.</p>
            </a>

            <a class="category-card" href="products.php?category=Keyboards+%26+Mice">
              <span>Input</span>
              <h3>Keyboards &amp; Mice</h3>
              <p>Keyboards and mice for everyday use.</p>
            </a>

            <a class="category-card" href="products.php?category=Accessories+%26+Focus">
              <span>Extras</span>
              <h3>Accessories &amp; Focus</h3>
              <p>Accessories and support items for the rest of the setup.</p>
            </a>

            <a class="category-card" href="products.php">
              <span>Everything</span>
              <h3>Full Collection</h3>
              <p>See the full range in one place.</p>
            </a>
          </div>
        </div>

        <aside class="section-card">
          <!-- This side column just gives a quick explanation of what the store is aiming for. -->
          <h2>Why Choose PixelPals</h2>
          <p class="lead">The store is built around comfort, simpler setups and practical upgrades.</p>

          <div class="highlight-list">
            <div class="highlight-item">
              <strong>Everyday use</strong>
              Products are suitable for both gaming and desk time.
            </div>
            <div class="highlight-item">
              <strong>Easy to build up</strong>
              Start with one item and add more later.
            </div>
            <div class="highlight-item">
              <strong>Clear product range</strong>
              The categories are kept simple so products are easier to find.
            </div>
          </div>
        </aside>
      </section>
    </main>

    <!-- Shared footer note keeps the public pages feeling connected at the bottom. -->
    <div class="footer-note">
      <div class="footer-brand">
        <img src="assets/img/logo.png" alt="PixelPals logo">
        <div>PixelPals sells gaming gear for younger players and family setups.</div>
      </div>

      <div class="footer-socials">
        <a href="#" aria-label="Instagram">
          <img src="assets/img/instagram_logo.png" alt="Instagram">
        </a>
        <a href="#" aria-label="Twitter">
          <img src="assets/img/twitter_logo.png" alt="Twitter">
        </a>
        <a href="#" aria-label="YouTube">
          <img src="assets/img/youtube_logo.png" alt="YouTube">
        </a>
      </div>
    </div>
  </div>
</body>
</html>

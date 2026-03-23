<?php
// This page gives the short version of what PixelPals is about and also shows recent service reviews.
// Session state is only needed here for access control and preview behaviour.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// We need the DB here because the about page now also surfaces customer service reviews.
require_once '../app/includes/auth.php';
require_once '../app/includes/admin_preview.php';
require_once '../app/config/db.php';
requireAuthenticatedSession();

// Recent service reviews are loaded separately so the page still works even if that query fails.
$serviceReviews = [];

try {
    $reviewStmt = $db->query(
        'SELECT
            sr.Rating,
            sr.Comment,
            sr.CreatedAt,
            u.Username,
            u.FirstName,
            u.LastName
         FROM service_reviews sr
         INNER JOIN users u ON u.UserID = sr.UserID
         ORDER BY sr.CreatedAt DESC, sr.ServiceReviewID DESC
         LIMIT 3'
    );
    $serviceReviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $serviceReviews = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="stylesheet" type="text/css" href="assets/css/styles.css" />

    <title>PixelPals | About Us</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* Light hover movement stops the page cards from feeling too flat. */
        .cta-row a,
        .value-card,
        .story-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .cta-row a:hover,
        .value-card:hover,
        .story-card:hover {
            transform: translateY(-2px);
        }

        /* The top section is split between the summary copy and a simple stats panel. */
        .hero {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 24px;
            margin-bottom: 28px;
        }

        .story-card,
        .value-card,
        .cta-banner {
            background: var(--card);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 30px;
            box-shadow: var(--shadow);
        }

        h1 {
            margin: 18px 0 14px;
            font-size: clamp(2.4rem, 5vw, 4.4rem);
            line-height: 0.95;
            letter-spacing: -0.04em;
        }

        .story-card p,
        .cta-banner p {
            line-height: 1.7;
            margin: 0;
            opacity: 0.9;
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

        /* The middle of the page is broken into story/value sections so the copy is easier to scan. */
        .story-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 24px;
            margin-bottom: 28px;
        }

        .story-card {
            padding: 30px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(247, 250, 255, 0.82));
        }

        .story-card h2,
        .values-section h2 {
            margin: 0 0 12px;
            font-size: 2rem;
        }

        .story-copy {
            display: grid;
            gap: 14px;
        }

        .callout {
            padding: 18px 20px;
            border-radius: 22px;
            background: linear-gradient(135deg, rgba(87, 166, 255, 0.14), rgba(116, 70, 200, 0.14));
            border: 1px solid var(--outline);
            font-weight: 700;
        }

        .values-section {
            margin-bottom: 28px;
        }

        .values-section > p {
            margin: 0 0 18px;
            line-height: 1.7;
            opacity: 0.86;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .value-card {
            padding: 22px;
            border-radius: 24px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(201, 218, 255, 0.92));
            border: 1px solid rgba(17, 37, 77, 0.08);
            box-shadow: 0 14px 28px rgba(17, 37, 77, 0.08);
        }

        .value-card span {
            display: inline-block;
            margin-bottom: 12px;
            padding: 8px 10px;
            border-radius: 12px;
            background: linear-gradient(90deg, rgba(116, 70, 200, 0.12), rgba(255, 109, 178, 0.12));
            font-size: 0.85rem;
            font-weight: 800;
        }

        .value-card h3 {
            margin: 0 0 8px;
            font-size: 1.18rem;
        }

        .value-card p,
        .value-card ul {
            margin: 0;
            line-height: 1.6;
            opacity: 0.84;
        }

        .value-card ul {
            padding-left: 18px;
        }

        .cta-banner {
            padding: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 36px;
            background: linear-gradient(120deg, rgba(17, 37, 77, 0.94), rgba(116, 70, 200, 0.88));
            color: #fff;
        }

        .cta-banner h2 {
            margin: 0 0 10px;
            font-size: 2rem;
        }

        .cta-banner a {
            padding: 14px 20px;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--mint), #f5ff9a);
            color: var(--navy);
            font-weight: 800;
            white-space: nowrap;
        }

        .reviews-section {
            margin-bottom: 28px;
        }

        .reviews-section > p {
            margin: 0 0 18px;
            line-height: 1.7;
            opacity: 0.86;
        }

        /* Customer service reviews are shown in a simple grid underneath the main about content. */
        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .review-card {
            padding: 22px;
            border-radius: 24px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(201, 218, 255, 0.92));
            border: 1px solid rgba(17, 37, 77, 0.08);
            box-shadow: 0 14px 28px rgba(17, 37, 77, 0.08);
        }

        .review-rating {
            display: inline-block;
            margin-bottom: 12px;
            padding: 8px 10px;
            border-radius: 12px;
            background: linear-gradient(90deg, rgba(116, 70, 200, 0.12), rgba(255, 109, 178, 0.12));
            font-size: 0.85rem;
            font-weight: 800;
        }

        .review-card p {
            margin: 0 0 14px;
            line-height: 1.65;
            opacity: 0.88;
        }

        .review-card strong {
            display: block;
            margin-bottom: 4px;
        }

        .review-card span {
            font-size: 0.92rem;
            opacity: 0.72;
        }

        @media (max-width: 920px) {
            .hero,
            .story-grid,
            .values-grid,
            .reviews-grid {
                grid-template-columns: 1fr;
            }

            .cta-banner {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 640px) {
            .hero-copy,
            .hero-panel,
            .story-card,
            .cta-banner {
                padding: 22px;
            }

        }
    </style>
  </head>

  <body>
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
                    <input type="text" name="q" placeholder="Search chairs, desks, stands and more">
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
                <a href="index.php">Home</a>
                <a href="products.php">Products</a>
                <a class="active" href="about.php">About Us</a>
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

        <?php if (isset($_SESSION['success'])): ?>
            <div class="flash success"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="flash error"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- About page content starts here. -->
        <main>
            <!-- Hero section: a short overview of what the business is about. -->
            <section class="hero">
                <div class="hero-copy">
                    <span class="eyebrow">About Us</span>
                    <h1>PixelPals is built around gaming gear for younger players.</h1>
                    <p>
                        We wanted a shop that focuses on comfort, simpler setups and products that are easier for
                        younger players to use.
                    </p>

                    <div class="cta-row">
                        <a class="cta-main" href="products.php">Browse Products</a>
                        <a class="cta-secondary" href="contact.php">Get in Touch</a>
                    </div>
                </div>

                <aside class="hero-panel">
                    <div class="hero-stat">
                        <strong>Kid-first</strong>
                        products chosen with younger players in mind
                    </div>
                    <div class="hero-stat">
                        <strong>Practical</strong>
                        a range that works for home setups and everyday use
                    </div>
                    <div class="hero-stat">
                        <strong>Simple</strong>
                        easier categories and products that are straightforward to browse
                    </div>
                </aside>
            </section>

            <!-- Story section: why the store exists and what it is trying to focus on. -->
            <section class="story-grid">
                <article class="story-card">
                    <h2>Why We Started</h2>
                    <div class="story-copy">
                        <p>
                            A lot of gaming gear is designed for adults first. We wanted a store that pays more attention
                            to comfort, size and everyday use for younger players.
                        </p>
                        <p>
                            The aim was to make it easier for parents and customers to find products that work for both
                            gaming and desk time.
                        </p>
                        <p>
                            That includes chairs, desks, audio gear and accessories that help build a setup step by step.
                        </p>
                    </div>
                </article>

                <article class="story-card">
                    <h2>What We Focus On</h2>
                    <div class="callout">
                        A more comfortable setup makes it easier to play, study and use a desk for longer.
                    </div>
                    <div class="story-copy" style="margin-top: 14px;">
                        <p>
                            We focus on seating, desk layout, screen height and accessories that make setups easier to use.
                        </p>
                        <p>
                            The goal is not to overcomplicate things, just to make better choices easier to find.
                        </p>
                    </div>
                </article>
            </section>

            <!-- Values section: the practical things the product range is meant to support. -->
            <section class="values-section">
                <h2>What Our Products Support</h2>
                <p>
                    The range is aimed at everyday setups, including gaming, homework and shared spaces at home.
                </p>

                <div class="values-grid">
                    <article class="value-card">
                        <span>Comfort</span>
                        <h3>Posture and desk comfort</h3>
                        <p>
                            Seating, desk position and support items can help make longer sessions more comfortable.
                        </p>
                    </article>

                    <article class="value-card">
                        <span>Use</span>
                        <h3>Gaming and desk time</h3>
                        <ul>
                            <li>Gaming</li>
                            <li>Homework</li>
                            <li>General computer use</li>
                        </ul>
                    </article>

                    <article class="value-card">
                        <span>Practicality</span>
                        <h3>More manageable setups</h3>
                        <p>
                            The shop is designed to help customers build a setup without having to sort through unrelated products.
                        </p>
                    </article>
                </div>
            </section>

            <!-- Review section: customer feedback from the post-order service review flow. -->
            <section class="reviews-section">
                <h2>Customer Reviews</h2>
                <p>
                    These reviews are left by customers after placing an order.
                </p>

                <div class="reviews-grid">
                    <?php if ($serviceReviews): ?>
                        <?php foreach ($serviceReviews as $serviceReview): ?>
                            <?php
                            $reviewName = trim(($serviceReview['FirstName'] ?? '') . ' ' . ($serviceReview['LastName'] ?? ''));
                            if ($reviewName === '') {
                                $reviewName = (string) ($serviceReview['Username'] ?? 'PixelPals customer');
                            }
                            ?>
                            <article class="review-card">
                                <div class="review-rating"><?php echo (int) $serviceReview['Rating']; ?>/5</div>
                                <p><?php echo nl2br(htmlspecialchars((string) $serviceReview['Comment'])); ?></p>
                                <strong><?php echo htmlspecialchars($reviewName); ?></strong>
                                <span><?php echo htmlspecialchars(date('j M Y', strtotime((string) $serviceReview['CreatedAt']))); ?></span>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <article class="review-card">
                            <div class="review-rating">No reviews yet</div>
                            <p>Customer feedback will appear here after the first reviews are submitted.</p>
                            <strong>PixelPals</strong>
                            <span>Waiting for feedback</span>
                        </article>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Final call to action sends the reader back into the catalogue. -->
            <section class="cta-banner">
                <div>
                    <h2>Ready to browse the products?</h2>
                    <p>
                        View the full range and start putting together a setup that fits what you need.
                    </p>
                </div>
                <a href="products.php">Browse Products</a>
            </section>
        </main>

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
    <?php unset($_SESSION['error']); endif; ?>

    <!-- Main content -->
    <main class="container">
      <section id="about-us-info">
        <h2 class="about-us-header">About Us | PixelPals</h2>

        <p>
          Hello there, Player One! 
        </p>

        <p>
          Welcome to PixelPals, a colourful corner of the internet where gaming, fun, and healthy habits level up together.
        </p>

        <p>
          We noticed something important in the world of gaming: kids love to play, explore, and learn through games, 
          but not all gaming gear is designed with young players’ bodies in mind; that’s where PixelPals comes in!
        </p>

        <p>
          Our mission is simple: keep gaming comfortable, safe, and supportive.
        </p>

        <p>
          Young gamers are still growing, learning, and developing their skills. That’s why our accessories are designed 
          to support motor development, coordination, and healthy movement.
        </p>

         <p> 
          From <b><a href="/products.php">easy-grip controllers</a></b> to <b><a href="/products.php">adaptive keyboards</a></b>, 
          every PixelPals product is created to help kids:
        </p>

        <ul>
          <li>Improve hand-eye coordination</li>
          <li>Build fine motor skills</li>
          <li>Maintain good posture while gaming</li>
          <li>Stay comfortable during play</li>
        </ul>

        <p>
          Because when kids feel comfortable, they can focus on learning, exploring, and having fun.
        </p>

        <p>
          We understand that children connect through gaming and technology, it is the modern-day community, and it is important
          to protect that space as well as the consumer. Our products are here to make sure kids are healthy and secure whilst 
          interacting with technology and help support them as much as possible without having to sacrifice their joy.
        </p>

        <p>
          Many PixelPals products are designed with ergonomics and accessibility in mind, for ease on joints, eyesight, hearing, 
          and posture. Its focus on ergonomic handling makes each product helpful for children with different abilities, learning 
          needs, or motor challenges.
        </p>

        <p>
          And PixelPals is a great place to shop for your kids; parents, guardians, and teachers who want to promote positive habits 
          when using technology can find a product with us that supports their views!
        </p>

        <p>
          So, whether you're a young adventurer, a curious learner, or a parent guiding the next generation of gamers, PixelPals 
          is here to help every player game smarter, safer, and happier.
        </p>

        <br>

        <h3 class="outro">
          <a href="products.php">Game On!</a>
        </h3>
      </section>
    </main>

    <footer class="footer">
      <p><strong>PixelPals</strong></p>
      <p>Ergonomic gaming accessories for children</p>
      <p>© 2026 PixelPals</p>
    </footer>
  </body>
</body>
</html>

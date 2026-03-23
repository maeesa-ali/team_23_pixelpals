<?php
// This page pulls the live basket from the database so the items and totals stay in sync.
// Session state matters here because baskets are tied to the signed-in user.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auth keeps the page protected, flash handles feedback, and admin preview lets admins inspect the customer view.
require_once '../app/includes/auth.php';
require_once '../app/config/db.php';
require_once '../app/includes/flash.php';
require_once '../app/includes/admin_preview.php';
requireAuthenticatedSession();

// These values drive the summary card on the right-hand side of the page.
$basketItems = [];
$subtotal = 0.0;
$delivery = 0.0;

if (isset($_SESSION['user_id'])) {
    try {
        // Pull the basket contents with joined product data so each line can render without extra queries.
        $stmt = $db->prepare(
            'SELECT
                bi.BasketItemID,
                bi.Quantity,
                p.ProductID,
                p.ProductName,
                p.Description,
                p.Price,
                p.Stock,
                (bi.Quantity * p.Price) AS LineTotal
             FROM basket b
             JOIN basketitem bi ON bi.BasketID = b.BasketID
             JOIN product p ON p.ProductID = bi.ProductID
             WHERE b.UserID = ?
             ORDER BY bi.BasketItemID DESC'
        );
        $stmt->execute([$_SESSION['user_id']]);
        $basketItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add up the line totals so the summary card can be built from the same data set.
        foreach ($basketItems as $item) {
            $subtotal += (float) $item['LineTotal'];
        }

        // Delivery stays free over the threshold, otherwise we use the flat demo fee.
        $delivery = $subtotal >= 100 || $subtotal === 0.0 ? 0.0 : 4.99;
    } catch (PDOException $e) {
        $basketItems = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PixelPals | Basket</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* Hover feedback is limited to the main cards and calls to action on this page. */
        .basket-card, .summary-card, .cta-link {
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }
        .basket-card:hover, .summary-card:hover, .cta-link:hover { transform: translateY(-2px); }
        .basket-card, .summary-card {
            background: var(--card); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.6);
            border-radius: 30px; box-shadow: var(--shadow);
        }
        .basket-card p, .summary-card p { line-height: 1.7; margin: 0; opacity: 0.9; }
        .flash-wrap { margin-bottom: 18px; }
        .inline-status {
            margin-bottom: 18px;
            padding: 12px 16px;
            border-radius: 16px;
            font-weight: 700;
            display: none;
        }
        .inline-status.success { display: block; background: rgba(204, 255, 111, 0.7); }
        .inline-status.error { display: block; background: rgba(255, 109, 178, 0.2); }
        /* The basket uses a two-column layout: item list on the left, summary on the right. */
        .basket-layout { display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 24px; margin-bottom: 36px; }
        .basket-card, .summary-card { padding: 28px; }
        .basket-card h2, .summary-card h2 { margin: 0 0 12px; font-size: 1.95rem; }
        .item-list { display: grid; gap: 16px; margin-top: 18px; }
        .item-card {
            display: grid; grid-template-columns: 1.2fr auto; gap: 18px; padding: 18px; border-radius: 24px;
            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(201,218,255,0.92));
            border: 1px solid rgba(17,37,77,0.08);
        }
        .item-card h3 { margin: 0 0 8px; font-size: 1.18rem; }
        .item-card p { margin: 0 0 10px; }
        .item-meta { display: flex; flex-wrap: wrap; gap: 10px; font-size: 0.92rem; opacity: 0.78; }
        .item-side { display: grid; justify-items: end; align-content: space-between; gap: 12px; }
        .line-total { font-size: 1.2rem; font-weight: 900; }
        .qty-form { display: flex; gap: 8px; align-items: center; }
        .qty-form input[type="number"] {
            width: 84px; padding: 10px 12px; border-radius: 12px; border: 1px solid rgba(17,37,77,0.12);
            background: rgba(255,255,255,0.92); font: inherit;
        }
        .qty-form button, .remove-btn {
            padding: 10px 12px; border-radius: 12px; border: none; background: rgba(17,37,77,0.08);
            font: inherit; font-weight: 800; cursor: pointer;
        }
        .remove-btn { background: rgba(255, 61, 87, 0.14); color: #b43030; }
        .empty-visual {
            display: grid; place-items: center; min-height: 220px; margin: 18px 0 20px; border-radius: 26px;
            background: radial-gradient(circle at 30% 30%, rgba(204,255,111,0.85), transparent 16%),
                        radial-gradient(circle at 70% 65%, rgba(255,109,178,0.72), transparent 18%),
                        linear-gradient(135deg, rgba(87,166,255,0.9), rgba(43,111,214,0.92));
            color: #fff; text-align: center; padding: 24px; box-shadow: inset 0 0 0 2px rgba(255,255,255,0.18);
        }
        .empty-visual strong { display: block; font-size: clamp(1.8rem, 4vw, 2.8rem); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.08em; }
        .hint-list { display: grid; gap: 12px; margin-top: 18px; }
        .hint-item { padding: 16px 18px; border-radius: 20px; background: linear-gradient(135deg, rgba(87,166,255,0.14), rgba(116,70,200,0.14)); border: 1px solid var(--outline); }
        .summary-block { display: grid; gap: 14px; margin-top: 14px; }
        .summary-line { display: flex; justify-content: space-between; gap: 16px; padding: 14px 16px; border-radius: 18px; background: rgba(17,37,77,0.06); }
        .summary-line.total { background: linear-gradient(135deg, rgba(255,109,178,0.14), rgba(87,166,255,0.14)); font-weight: 800; font-size: 1.05rem; }
        .delivery-stack { display: grid; gap: 12px; margin: 20px 0; }
        .delivery-option { padding: 16px 18px; border-radius: 20px; background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(201,218,255,0.92)); border: 1px solid rgba(17,37,77,0.08); }
        .delivery-option strong { display: block; margin-bottom: 6px; }
        .cta-stack { display: grid; gap: 12px; margin-top: 22px; }
        .cta-link { display: block; text-align: center; padding: 14px 18px; border-radius: 18px; font-weight: 800; }
        .cta-link.primary { background: linear-gradient(135deg, var(--mint), #f5ff9a); }
        .cta-link.secondary { background: rgba(17,37,77,0.08); }
        @media (max-width: 920px) { .basket-layout { grid-template-columns: 1fr; } }
        @media (max-width: 640px) {
            .basket-card, .summary-card { padding: 22px; }
            .item-card { grid-template-columns: 1fr; }
            .item-side { justify-items: start; }
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

        <div class="flash-wrap">
            <?php display_flash_messages(); ?>
        </div>
        <!-- This small status box is updated by JS when quantity changes are saved in the background. -->
        <div id="basketStatus" class="inline-status"></div>

        <!-- Basket items and the matching summary sit side by side here. -->
        <section class="basket-layout">
            <article class="basket-card">
                <?php if ($basketItems): ?>
                    <!-- Normal basket view: each item row can update quantity or remove the item. -->
                    <h2>Your Basket</h2>
                    <p>Review your items, update quantities and head to checkout when everything looks right.</p>

                    <div class="item-list">
                        <?php foreach ($basketItems as $item): ?>
                            <article class="item-card" data-price="<?php echo htmlspecialchars((string) $item['Price']); ?>">
                                <div>
                                    <h3><?php echo htmlspecialchars($item['ProductName']); ?></h3>
                                    <p><?php echo htmlspecialchars($item['Description']); ?></p>
                                    <div class="item-meta">
                                        <span>&pound;<?php echo number_format((float) $item['Price'], 2); ?> each</span>
                                        <span><?php echo (int) $item['Stock']; ?> in stock</span>
                                    </div>
                                </div>

                                <div class="item-side">
                                    <div class="line-total">&pound;<span class="line-total-value"><?php echo number_format((float) $item['LineTotal'], 2); ?></span></div>
                                    <form class="qty-form" method="POST" action="basket_update.php">
                                        <input type="hidden" name="basket_item_id" value="<?php echo (int) $item['BasketItemID']; ?>">
                                        <input class="qty-input" type="number" name="quantity" min="1" max="<?php echo (int) $item['Stock']; ?>" value="<?php echo (int) $item['Quantity']; ?>">
                                    </form>
                                    <form method="POST" action="basket_update.php" style="margin: 0;">
                                        <input type="hidden" name="basket_item_id" value="<?php echo (int) $item['BasketItemID']; ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <button class="remove-btn" type="submit">Remove</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Empty-state version of the page when there is nothing in the basket yet. -->
                    <h2>Your Basket Is Empty</h2>
                    <p>Add something from the catalogue and it will appear here straight away.</p>

                    <div class="empty-visual">
                        <div>
                            <strong>Ready To Fill</strong>
                            Chairs, desks, support gear and accessories you add will show up here.
                        </div>
                    </div>

                    <div class="hint-list">
                        <div class="hint-item">Start with a chair or desk if you are building a full setup from scratch.</div>
                        <div class="hint-item">Add monitor stands or wrist support if you want a smaller comfort upgrade first.</div>
                        <div class="hint-item">Keep browsing products and come back here when you are ready to check out.</div>
                    </div>
                <?php endif; ?>
            </article>

            <aside class="summary-card">
                <!-- Summary card mirrors the totals the customer will see again at checkout. -->
                <h2>Order Summary</h2>

                <div class="delivery-stack">
                    <div class="delivery-option">
                        <strong>Home delivery</strong>
                        Spend more than &pound;100 to unlock free delivery.
                    </div>
                    <div class="delivery-option">
                        <strong>Click &amp; Collect</strong>
                        Free collection within 2 hours, held for 4 days.
                    </div>
                </div>

                <div class="summary-block">
                    <div class="summary-line">
                        <span>Subtotal</span>
                        <span id="basketSubtotal">&pound;<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-line">
                        <span>Delivery</span>
                        <span id="basketDelivery">&pound;<?php echo number_format($delivery, 2); ?></span>
                    </div>
                    <div class="summary-line total">
                        <span>Total</span>
                        <span id="basketTotal">&pound;<?php echo number_format($subtotal + $delivery, 2); ?></span>
                    </div>
                </div>

                <div class="cta-stack">
                    <a class="cta-link primary" href="products.php">Continue Shopping</a>
                    <a class="cta-link secondary" href="checkout.php">Go to Checkout</a>
                </div>
            </aside>
        </section>
    </div>
    <script src="js/basket.js?v=1" defer></script>
</body>
</html>




<?php
// This page turns the basket into an order and adds the optional engraving extra to the flow.
// Session state is required here because checkout belongs to the signed-in user and their live basket.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Shared config gives us the engraving fee, and the other includes handle auth, DB access and flash messages.
require_once '../app/includes/auth.php';
require_once '../app/config/db.php';
require_once '../app/config/config.php';
require_once '../app/includes/flash.php';
require_once '../app/includes/admin_preview.php';

// Admin preview can browse most customer pages, but checkout is blocked because it needs a real basket and user.
if (isAdminPreviewMode()) {
    renderAdminPreviewUnavailablePage(
        'Checkout Preview Not Available',
        'Checkout needs a live customer basket and account details, so admin preview stops here. You can return to the dashboard or keep browsing the customer-facing pages without signing in again.'
    );
}

requireLogin();

// These defaults keep the template stable even if the user lookup fails.
$user = [
    'Email' => '',
    'FirstName' => '',
    'LastName' => '',
];
$basketItems = [];
$subtotal = 0.0;
$delivery = 0.0;
$engravingFee = defined('ORDER_ENGRAVING_FEE') ? (float) ORDER_ENGRAVING_FEE : 4.99;

try {
    // Load the account details that are used to prefill the checkout form.
    $userStmt = $db->prepare('SELECT Email, FirstName, LastName FROM users WHERE UserID = ?');
    $userStmt->execute([$_SESSION['user_id']]);
    $loadedUser = $userStmt->fetch(PDO::FETCH_ASSOC);
    if ($loadedUser) {
        $user = $loadedUser;
    }

    // Pull the basket items once so both the checkout form and summary panel use the same data.
    $basketStmt = $db->prepare(
        'SELECT
            bi.BasketItemID,
            bi.Quantity,
            p.ProductName,
            p.Price,
            p.Description,
            (bi.Quantity * p.Price) AS LineTotal
         FROM basket b
         JOIN basketitem bi ON bi.BasketID = b.BasketID
         JOIN product p ON p.ProductID = bi.ProductID
         WHERE b.UserID = ?
         ORDER BY bi.BasketItemID DESC'
    );
    $basketStmt->execute([$_SESSION['user_id']]);
    $basketItems = $basketStmt->fetchAll(PDO::FETCH_ASSOC);

    // Build the subtotal directly from the basket query results.
    foreach ($basketItems as $item) {
        $subtotal += (float) $item['LineTotal'];
    }

    // Delivery uses the same rule as the basket page so the totals stay consistent.
    $delivery = $subtotal >= 100 || $subtotal === 0.0 ? 0.0 : 4.99;
} catch (PDOException $e) {
    $basketItems = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PixelPals | Checkout</title>
    <!-- Checkout JS handles the live engraving toggle and total update before submit. -->
    <script src="js/checkout.js?v=1" defer></script>
    <style>
        :root {
            --bubblegum: #ff6db2;
            --sky: #57a6ff;
            --deep-sky: #2b6fd6;
            --navy: #11254d;
            --plum: #7446c8;
            --mint: #ccff6f;
            --card: rgba(255, 255, 255, 0.88);
            --outline: rgba(17, 37, 77, 0.08);
            --shadow: 0 20px 60px rgba(17, 37, 77, 0.18);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Verdana", "Trebuchet MS", "Segoe UI", sans-serif;
            color: var(--navy);
            background:
                radial-gradient(circle at 12% 10%, rgba(255, 109, 178, 0.28), transparent 18%),
                radial-gradient(circle at 88% 10%, rgba(255, 213, 77, 0.35), transparent 16%),
                radial-gradient(circle at 20% 80%, rgba(204, 255, 111, 0.32), transparent 18%),
                linear-gradient(180deg, #81d4ff 0%, #b6d4ff 36%, #efe7ff 100%);
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: 0.3;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.12) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.12) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        a { color: inherit; text-decoration: none; }
        .site-shell { width: min(1180px, calc(100% - 32px)); margin: 0 auto; position: relative; z-index: 1; }
        .topBar { display: flex; align-items: center; gap: 18px; padding: 20px 0 14px; }
        .brand { display: flex; align-items: center; gap: 14px; min-width: 220px; }
        .logo {
            width: 68px;
            height: 68px;
            object-fit: contain;
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(255,255,255,0.62));
            padding: 8px;
            box-shadow: var(--shadow);
            border: 3px solid rgba(255,255,255,0.7);
        }
        .brand-copy strong { display: block; font-size: 1.55rem; letter-spacing: 0.02em; text-transform: uppercase; }
        .brand-copy span { display: block; max-width: 26ch; font-size: 0.92rem; opacity: 0.82; }
        .searchContainer { flex: 1; }
        .searchContainer form { margin: 0; }
        .searchContainer input {
            width: 100%;
            border: 2px solid rgba(255,255,255,0.7);
            border-radius: 999px;
            padding: 16px 22px;
            font-size: 1rem;
            box-shadow: var(--shadow);
            background: rgba(255,255,255,0.9);
        }
        .topLinks { display: flex; align-items: center; gap: 12px; }
        .chip-link, .primary-link, .nav-links a, .panel, .place-order {
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }
        .chip-link, .primary-link { border-radius: 999px; padding: 12px 18px; font-weight: 700; }
        .chip-link { background: rgba(255,255,255,0.78); box-shadow: var(--shadow); }
        .primary-link { background: linear-gradient(135deg, var(--mint), #f5ff9a); box-shadow: var(--shadow); }
        .chip-link:hover, .primary-link:hover, .nav-links a:hover, .panel:hover, .place-order:hover { transform: translateY(-2px); }
        .bottomNav { margin-bottom: 24px; }
        .nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 12px;
            border-radius: 24px;
            background: linear-gradient(90deg, rgba(17, 37, 77, 0.95), rgba(116, 70, 200, 0.9));
            box-shadow: var(--shadow);
        }
        .nav-links a { color: #fff; padding: 10px 16px; border-radius: 999px; font-weight: 600; }
        .nav-links a:hover { background: rgba(255,255,255,0.14); }
        /* The top section is just a quick checkout summary before the actual form begins. */
        .hero {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 24px;
            margin-bottom: 22px;
        }
        .hero-copy, .hero-panel, .panel {
            background: var(--card);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.6);
            border-radius: 30px;
            box-shadow: var(--shadow);
        }
        .hero-copy {
            padding: 38px;
            background: radial-gradient(circle at top right, rgba(255, 213, 77, 0.3), transparent 24%),
                        linear-gradient(180deg, rgba(255,255,255,0.92), rgba(255,255,255,0.8));
        }
        .eyebrow {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 999px;
            background: linear-gradient(90deg, rgba(87,166,255,0.18), rgba(255,109,178,0.18));
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-size: 0.76rem;
        }
        h1 { margin: 18px 0 14px; font-size: clamp(2.4rem, 5vw, 4rem); line-height: 0.95; letter-spacing: -0.04em; }
        .hero-copy p, .hero-panel p, .panel p { line-height: 1.7; margin: 0; opacity: 0.9; }
        .hero-panel {
            padding: 28px;
            display: grid;
            gap: 16px;
            align-content: center;
            background: radial-gradient(circle at top right, rgba(255, 213, 77, 0.28), transparent 24%),
                        linear-gradient(160deg, rgba(116,70,200,0.95), rgba(43,111,214,0.95));
            color: #fff;
        }
        .hero-stat { padding: 16px 18px; border-radius: 20px; background: rgba(255,255,255,0.12); }
        .hero-stat strong { display: block; font-size: 1.8rem; margin-bottom: 4px; }
        .flash-wrap { margin-bottom: 18px; }
        /* Main layout: form on the left, order summary on the right. */
        .checkout-layout { display: grid; grid-template-columns: 1.05fr 0.95fr; gap: 24px; margin-bottom: 36px; }
        .panel { padding: 28px; background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(247,250,255,0.82)); }
        .panel h2 { margin: 0 0 12px; font-size: 1.95rem; }
        .checkout-form { display: grid; gap: 18px; }
        .section-title { font-size: 1rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; opacity: 0.72; margin-top: 6px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .field { display: grid; gap: 8px; }
        .field.full { grid-column: 1 / -1; }
        .field label { font-weight: 800; font-size: 0.95rem; }
        .field input { width: 100%; padding: 13px 14px; border-radius: 16px; border: 1px solid rgba(17,37,77,0.12); background: rgba(255,255,255,0.92); font: inherit; }
        .engraving-box {
            padding: 20px;
            border-radius: 22px;
            background: linear-gradient(135deg, rgba(255,109,178,0.1), rgba(87,166,255,0.1));
            border: 1px solid rgba(17,37,77,0.08);
        }
        .engraving-toggle {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }
        .engraving-toggle input {
            width: 20px;
            height: 20px;
            margin-top: 2px;
        }
        .engraving-copy strong {
            display: block;
            margin-bottom: 6px;
            font-size: 1.04rem;
        }
        .engraving-copy p {
            margin: 0;
            line-height: 1.6;
            opacity: 0.86;
        }
        .engraving-field {
            margin-top: 16px;
            display: grid;
            gap: 8px;
        }
        .engraving-field.hidden {
            display: none;
        }
        .engraving-field label {
            font-weight: 800;
            font-size: 0.95rem;
        }
        .engraving-field input {
            width: 100%;
            padding: 13px 14px;
            border-radius: 16px;
            border: 1px solid rgba(17,37,77,0.12);
            background: rgba(255,255,255,0.92);
            font: inherit;
        }
        .engraving-note {
            font-size: 0.88rem;
            opacity: 0.72;
        }
        .summary-items { display: grid; gap: 14px; margin: 18px 0 20px; }
        .summary-item { display: grid; grid-template-columns: 1fr auto; gap: 14px; padding: 14px 0; border-bottom: 1px solid rgba(17,37,77,0.08); }
        .summary-item:last-child { border-bottom: none; }
        .summary-item strong { display: block; margin-bottom: 6px; }
        .summary-meta { font-size: 0.9rem; opacity: 0.72; }
        .summary-line { display: flex; justify-content: space-between; gap: 16px; padding: 14px 16px; border-radius: 18px; background: rgba(17,37,77,0.06); margin-top: 10px; }
        .summary-line.total { background: linear-gradient(135deg, rgba(255,109,178,0.14), rgba(87,166,255,0.14)); font-weight: 800; font-size: 1.05rem; }
        .summary-line.is-hidden { display: none; }
        .place-order {
            width: 100%;
            border: none;
            border-radius: 18px;
            padding: 15px 18px;
            background: linear-gradient(135deg, var(--mint), #f5ff9a);
            font: inherit;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 14px 28px rgba(17,37,77,0.12);
            margin-top: 10px;
        }
        .empty-checkout { text-align: center; }
        .empty-checkout a { display: inline-block; margin-top: 14px; padding: 14px 18px; border-radius: 18px; background: linear-gradient(135deg, var(--mint), #f5ff9a); font-weight: 800; }
        @media (max-width: 920px) { .hero, .checkout-layout { grid-template-columns: 1fr; } }
        @media (max-width: 640px) {
            .site-shell { width: min(100% - 20px, 1180px); }
            .topBar { flex-direction: column; align-items: stretch; }
            .topLinks { flex-wrap: wrap; }
            .topLinks a { flex: 1; text-align: center; }
            .form-grid { grid-template-columns: 1fr; }
            .panel, .hero-copy, .hero-panel { padding: 22px; }
        }
    </style>
</head>
<body>
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
                <a class="primary-link" href="account.php">My Account</a>
            </div>
        </header>

        <nav class="bottomNav">
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="products.php">Products</a>
                <a href="about.php">About Us</a>
                <a href="contact.php">Contact Us</a>
                <a href="orders.php">Orders</a>
                <a href="logout.php">Log Out</a>
            </div>
        </nav>

        <!-- Quick checkout context sits up top before the actual form. -->
        <section class="hero">
            <div class="hero-copy">
                <span class="eyebrow">Checkout</span>
                <h1>Complete your order.</h1>
                <p>Check your details below and submit the order when you are ready.</p>
            </div>

            <aside class="hero-panel">
                <div class="hero-stat">
                    <strong><?php echo count($basketItems); ?></strong>
                    item<?php echo count($basketItems) === 1 ? '' : 's'; ?> in this order
                </div>
                <div class="hero-stat">
                    <strong>Order total</strong>
                    totals update here before you place the order
                </div>
                <div class="hero-stat">
                    <strong>One step left</strong>
                    submit the form to save this as an order
                </div>
            </aside>
        </section>

        <!-- Checkout validation and order placement feedback appear here after redirects. -->
        <div class="flash-wrap">
            <?php display_flash_messages(); ?>
        </div>

        <?php if ($basketItems): ?>
            <!-- The checkout form and order summary are the main job of this page. -->
            <section class="checkout-layout">
                <section class="panel">
                    <!-- Left side: customer details, payment details and the engraving extra. -->
                    <h2>Delivery and Payment Details</h2>
                    <p>Fill in the form below to place your order.</p>

                    <form class="checkout-form" method="POST" action="checkout_place_order.php">
                        <!-- Contact details are saved with the order for the demo checkout flow. -->
                        <div class="section-title">Contact details</div>
                        <div class="form-grid">
                            <div class="field">
                                <label for="email">Email</label>
                                <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars($user['Email'] ?? ''); ?>">
                            </div>
                            <div class="field">
                                <label for="phone">Phone Number</label>
                                <input id="phone" name="phone" type="text" required placeholder="07123 456789">
                            </div>
                            <div class="field">
                                <label for="first_name">First Name</label>
                                <input id="first_name" name="first_name" type="text" required value="<?php echo htmlspecialchars($user['FirstName'] ?? ''); ?>">
                            </div>
                            <div class="field">
                                <label for="last_name">Last Name</label>
                                <input id="last_name" name="last_name" type="text" required value="<?php echo htmlspecialchars($user['LastName'] ?? ''); ?>">
                            </div>
                            <div class="field full">
                                <label for="address">Delivery Address</label>
                                <input id="address" name="address" type="text" required placeholder="123 Pixel Street, Birmingham">
                            </div>
                        </div>

                        <!-- Payment fields are validated for the flow, even though this is not a real gateway. -->
                        <div class="section-title">Payment details</div>
                        <div class="form-grid">
                            <div class="field full">
                                <label for="card_number">Card Number</label>
                                <input id="card_number" name="card_number" type="text" required placeholder="1234 5678 9012 3456">
                            </div>
                            <div class="field">
                                <label for="expiry_date">Expiry Date</label>
                                <input id="expiry_date" name="expiry_date" type="text" required placeholder="MM/YY">
                            </div>
                            <div class="field">
                                <label for="cvc">CVC</label>
                                <input id="cvc" name="cvc" type="text" required placeholder="123">
                            </div>
                        </div>

                        <!-- The engraving block is the custom extra feature on this checkout page. -->
                        <div class="section-title">Unique extras</div>
                        <div class="engraving-box" data-engraving-box data-engraving-fee="<?php echo htmlspecialchars(number_format($engravingFee, 2, '.', '')); ?>">
                            <label class="engraving-toggle" for="engraving_enabled">
                                <input id="engraving_enabled" name="engraving_enabled" type="checkbox" value="1" data-engraving-toggle>
                                <div class="engraving-copy">
                                    <strong>Add name engraving for an extra &pound;<?php echo number_format($engravingFee, 2); ?></strong>
                                    <p>Add one custom name to this order.</p>
                                </div>
                            </label>

                            <div class="engraving-field hidden" data-engraving-field>
                                <label for="engraving_name">Name to engrave</label>
                                <input id="engraving_name" name="engraving_name" type="text" maxlength="32" placeholder="e.g. Olivia" data-engraving-input>
                                <div class="engraving-note">Up to 32 characters. The fee is added once per order.</div>
                            </div>
                        </div>

                        <button class="place-order" type="submit">Place Order</button>
                    </form>
                </section>

                <aside class="panel">
                    <!-- Right side: a live summary of exactly what the customer is about to place. -->
                    <h2>Order Summary</h2>
                    <p>This is everything included in the order.</p>

                    <div class="summary-items">
                        <?php foreach ($basketItems as $item): ?>
                            <div class="summary-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($item['ProductName']); ?></strong>
                                    <div class="summary-meta"><?php echo (int) $item['Quantity']; ?> x &pound;<?php echo number_format((float) $item['Price'], 2); ?></div>
                                </div>
                                <div>&pound;<?php echo number_format((float) $item['LineTotal'], 2); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-line">
                        <span>Subtotal</span>
                        <span>&pound;<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-line">
                        <span>Delivery</span>
                        <span>&pound;<?php echo number_format($delivery, 2); ?></span>
                    </div>
                    <div class="summary-line is-hidden" data-engraving-line>
                        <span>Name engraving</span>
                        <span data-engraving-amount>&pound;<?php echo number_format($engravingFee, 2); ?></span>
                    </div>
                    <div class="summary-line total">
                        <span>Total</span>
                        <span data-checkout-total>&pound;<?php echo number_format($subtotal + $delivery, 2); ?></span>
                    </div>
                </aside>
            </section>
        <?php else: ?>
            <!-- Fallback shown if someone reaches checkout without any basket items. -->
            <section class="panel empty-checkout" style="margin-bottom: 36px;">
                <h2>Your basket is empty</h2>
                <p>Add something to your basket before heading to checkout.</p>
                <a href="products.php">Browse Products</a>
            </section>
        <?php endif; ?>
    </div>
</body>
</html>

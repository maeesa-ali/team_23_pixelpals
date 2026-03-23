<?php
// This page loads one product in full and keeps the add-to-basket and review actions close by.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../app/includes/auth.php';
require_once '../app/config/db.php';
require_once '../app/includes/admin_preview.php';

requireAuthenticatedSession();

// The whole page is driven by one product id coming from the catalogue links.
$productId = (int) ($_GET['id'] ?? 0);
$product = null;
$reviews = [];
$relatedProducts = [];
$ratingSummary = [
    'average_rating' => null,
    'review_count' => 0,
];

function productStockLabel(int $stock): string
{
    if ($stock <= 0) {
        return 'Out of stock';
    }

    if ($stock <= 10) {
        return 'Low stock';
    }

    return 'In stock';
}

function productStockClass(int $stock): string
{
    if ($stock <= 0) {
        return 'out';
    }

    if ($stock <= 10) {
        return 'low';
    }

    return 'in';
}

if ($productId > 0) {
    try {
        // Load the main product record first because everything else depends on it existing.
        $productStmt = $db->prepare(
            'SELECT ProductID, ProductName, Description, Category, ImagePath, Price, Stock
             FROM product
             WHERE ProductID = ?'
        );
        $productStmt->execute([$productId]);
        $product = $productStmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            // Reviews and related products only make sense once the product itself has been confirmed.
            $ratingStmt = $db->prepare(
                'SELECT AVG(Rating) AS average_rating, COUNT(*) AS review_count
                 FROM reviews
                 WHERE ProductID = ?'
            );
            $ratingStmt->execute([$productId]);
            $ratingSummary = $ratingStmt->fetch(PDO::FETCH_ASSOC) ?: $ratingSummary;

            $reviewsStmt = $db->prepare(
                'SELECT r.Rating, r.Comment, r.CreatedAt, u.Username
                 FROM reviews r
                 INNER JOIN users u ON u.UserID = r.UserID
                 WHERE r.ProductID = ?
                 ORDER BY r.CreatedAt DESC, r.ReviewID DESC'
            );
            $reviewsStmt->execute([$productId]);
            $reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);

            $relatedStmt = $db->prepare(
                'SELECT ProductID, ProductName, ImagePath, Price, Stock
                 FROM product
                 WHERE Category = ? AND ProductID <> ?
                 ORDER BY Stock > 0 DESC, ProductName ASC
                 LIMIT 3'
            );
            $relatedStmt->execute([$product['Category'], $productId]);
            $relatedProducts = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // A failed lookup just falls through to the normal "not found" state below.
        $product = null;
    }
}

if (!$product) {
    http_response_code(404);
}

$flashSuccess = $_SESSION['success'] ?? null;
$flashError = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? 'PixelPals | ' . htmlspecialchars($product['ProductName']) : 'PixelPals | Product'; ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .product-detail-shell {
            display: grid;
            gap: 24px;
            margin-bottom: 36px;
        }

        .product-hero {
            display: grid;
            grid-template-columns: minmax(280px, 420px) 1fr;
            gap: 24px;
        }

        .detail-card,
        .review-card,
        .related-card,
        .not-found-card {
            background: var(--card);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 30px;
            box-shadow: var(--shadow);
        }

        .product-summary {
            padding: 30px;
        }

        .product-visual {
            padding: 24px;
            display: grid;
            align-content: start;
            gap: 16px;
            background: linear-gradient(160deg, rgba(116, 70, 200, 0.95), rgba(43, 111, 214, 0.95));
            color: #fff;
        }

        .product-visual img {
            width: 100%;
            aspect-ratio: 4 / 4;
            object-fit: cover;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .product-visual strong {
            display: block;
            font-size: 1.4rem;
        }

        .product-visual p {
            margin: 0;
            line-height: 1.6;
            opacity: 0.92;
        }

        .product-summary {
            background:
                radial-gradient(circle at top right, rgba(255, 213, 77, 0.28), transparent 24%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(247, 250, 255, 0.82));
        }

        .product-summary h1 {
            margin: 16px 0 12px;
            font-size: clamp(2.3rem, 5vw, 4.2rem);
            line-height: 0.95;
            letter-spacing: -0.04em;
        }

        .product-summary p {
            margin: 0;
            line-height: 1.7;
            opacity: 0.88;
        }

        .summary-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 22px 0 18px;
        }

        .meta-chip,
        .stock-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 999px;
            font-weight: 800;
        }

        .meta-chip {
            background: rgba(17, 37, 77, 0.08);
        }

        .stock-pill.in {
            background: rgba(72, 187, 120, 0.14);
            color: #1b7a44;
        }

        .stock-pill.low {
            background: rgba(237, 137, 54, 0.16);
            color: #a95511;
        }

        .stock-pill.out {
            background: rgba(229, 62, 62, 0.16);
            color: #b43030;
        }

        .price-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: end;
            gap: 16px;
            margin: 20px 0 18px;
            padding: 18px 0;
            border-top: 1px solid rgba(17, 37, 77, 0.08);
            border-bottom: 1px solid rgba(17, 37, 77, 0.08);
        }

        .price-block strong {
            display: block;
            font-size: 0.82rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            opacity: 0.7;
        }

        .price-block span {
            display: block;
            margin-top: 6px;
            font-size: 2rem;
            font-weight: 900;
        }

        .rating-block {
            text-align: right;
        }

        .rating-value {
            font-size: 1.4rem;
            font-weight: 900;
        }

        .action-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 22px;
        }

        .action-row form {
            margin: 0;
            display: flex;
        }

        .detail-button,
        .detail-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            min-width: 220px;
            padding: 13px 18px;
            border-radius: 18px;
            font: inherit;
            font-weight: 800;
            text-decoration: none;
            box-sizing: border-box;
        }

        .detail-button {
            border: none;
            cursor: pointer;
            background: linear-gradient(135deg, var(--mint), #f5ff9a);
            color: var(--navy);
        }

        .detail-button[disabled] {
            cursor: not-allowed;
            opacity: 0.55;
        }

        .detail-link {
            background: rgba(17, 37, 77, 0.08);
            border: 1px solid var(--outline);
            color: var(--navy);
        }

        .detail-button:hover,
        .detail-link:hover,
        .related-link:hover,
        .review-submit:hover {
            transform: translateY(-2px);
        }

        .detail-button,
        .detail-link,
        .related-link,
        .review-submit {
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .review-layout {
            display: grid;
            grid-template-columns: 1fr 0.95fr;
            gap: 24px;
        }

        .review-card,
        .related-card,
        .not-found-card {
            padding: 28px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(247, 250, 255, 0.82));
        }

        .review-card h2,
        .related-card h2,
        .not-found-card h2 {
            margin: 0 0 10px;
            font-size: 1.85rem;
        }

        .review-card p,
        .related-card p,
        .not-found-card p {
            margin: 0;
            line-height: 1.7;
            opacity: 0.86;
        }

        .review-form {
            display: grid;
            gap: 14px;
            margin-top: 22px;
        }

        .review-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .review-form select,
        .review-form textarea {
            width: 100%;
            padding: 12px 14px;
            border-radius: 16px;
            border: 1px solid rgba(17, 37, 77, 0.12);
            background: rgba(255, 255, 255, 0.92);
            font: inherit;
        }

        .review-form textarea {
            min-height: 140px;
            resize: vertical;
        }

        .review-submit {
            border: none;
            cursor: pointer;
            padding: 14px 18px;
            border-radius: 18px;
            font: inherit;
            font-weight: 800;
            background: linear-gradient(135deg, var(--mint), #f5ff9a);
            color: var(--navy);
        }

        .review-list {
            display: grid;
            gap: 14px;
            margin-top: 22px;
        }

        .review-item {
            padding: 18px;
            border-radius: 22px;
            background: linear-gradient(135deg, rgba(87, 166, 255, 0.12), rgba(116, 70, 200, 0.12));
            border: 1px solid rgba(17, 37, 77, 0.08);
        }

        .review-head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
            font-weight: 800;
        }

        .review-date {
            opacity: 0.68;
            font-size: 0.92rem;
        }

        .review-empty {
            margin-top: 22px;
            padding: 18px;
            border-radius: 22px;
            background: rgba(17, 37, 77, 0.06);
        }

        .related-grid {
            display: grid;
            gap: 14px;
            margin-top: 22px;
        }

        .related-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            padding: 18px;
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(201, 218, 255, 0.9));
            border: 1px solid rgba(17, 37, 77, 0.08);
        }

        .related-item strong {
            display: block;
            margin-bottom: 4px;
        }

        .related-price {
            font-weight: 900;
        }

        .related-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 14px;
            border-radius: 14px;
            background: rgba(17, 37, 77, 0.08);
            font-weight: 800;
        }

        .not-found-card {
            text-align: center;
        }

        .not-found-card .detail-link {
            margin-top: 18px;
        }

        @media (max-width: 920px) {
            .product-hero,
            .review-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .product-summary,
            .review-card,
            .related-card,
            .not-found-card {
                padding: 22px;
            }

            .detail-button,
            .detail-link {
                width: 100%;
                min-width: 0;
            }

            .price-row,
            .review-head,
            .related-item {
                align-items: start;
                flex-direction: column;
            }

            .rating-block {
                text-align: left;
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
                    <input type="text" name="q" placeholder="Search chairs, desks, stands and more">
                </form>
            </div>

            <div class="topLinks">
                <a class="chip-link" href="basket.php">Basket</a>
                <?php if (isAdminPreviewMode()): ?>
                    <a class="primary-link" href="admin/dashboard.php">Admin Dashboard</a>
                <?php else: ?>
                    <a class="primary-link" href="account.php">My Account</a>
                <?php endif; ?>
            </div>
        </header>

        <nav class="bottomNav">
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a class="active" href="products.php">Products</a>
                <a href="about.php">About Us</a>
                <a href="contact.php">Contact Us</a>
                <a href="orders.php">Orders</a>
                <?php if (isAdminPreviewMode()): ?>
                    <a href="admin/dashboard.php">Admin Dashboard</a>
                <?php else: ?>
                    <a href="logout.php">Log Out</a>
                <?php endif; ?>
            </div>
        </nav>

        <?php if ($flashSuccess): ?>
            <div class="flash success"><?php echo htmlspecialchars($flashSuccess); ?></div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="flash error"><?php echo htmlspecialchars($flashError); ?></div>
        <?php endif; ?>

        <!-- The single product view starts here. -->
        <main class="product-detail-shell">
            <?php if (!$product): ?>
                <!-- Fallback for an invalid or outdated product link. -->
                <section class="not-found-card">
                    <span class="eyebrow">Product not found</span>
                    <h2>That product could not be found.</h2>
                    <p>It may have been removed, or the link may be out of date.</p>
                    <a class="detail-link" href="products.php">Back to Products</a>
                </section>
            <?php else: ?>
                <!-- The hero row keeps the visual summary and the main buying info together. -->
                <section class="product-hero">
                    <aside class="detail-card product-visual">
                        <img src="<?php echo htmlspecialchars($product['ImagePath'] ?: 'assets/img/logo.png'); ?>" alt="<?php echo htmlspecialchars($product['ProductName']); ?>">
                        <div>
                            <strong><?php echo htmlspecialchars($product['Category']); ?></strong>
                            <p>This product is listed in the <?php echo htmlspecialchars($product['Category']); ?> category.</p>
                        </div>
                    </aside>

                    <div class="detail-card product-summary">
                        <span class="eyebrow">Product Details</span>
                        <h1><?php echo htmlspecialchars($product['ProductName']); ?></h1>
                        <p><?php echo htmlspecialchars($product['Description']); ?></p>

                        <div class="summary-meta">
                            <span class="meta-chip"><?php echo htmlspecialchars($product['Category']); ?></span>
                            <span class="stock-pill <?php echo productStockClass((int) $product['Stock']); ?>">
                                <?php echo htmlspecialchars(productStockLabel((int) $product['Stock'])); ?>
                            </span>
                            <span class="meta-chip"><?php echo (int) $product['Stock']; ?> available</span>
                        </div>

                        <div class="price-row">
                            <div class="price-block">
                                <strong>Price</strong>
                                <span>&pound;<?php echo number_format((float) $product['Price'], 2); ?></span>
                            </div>

                            <div class="rating-block">
                                <div class="rating-value">
                                    <?php if ($ratingSummary['review_count'] > 0): ?>
                                        <?php echo number_format((float) $ratingSummary['average_rating'], 1); ?>/5
                                    <?php else: ?>
                                        No ratings yet
                                    <?php endif; ?>
                                </div>
                                <div><?php echo (int) $ratingSummary['review_count']; ?> review<?php echo (int) $ratingSummary['review_count'] === 1 ? '' : 's'; ?></div>
                            </div>
                        </div>

                        <!-- These are the main next steps: buy now, browse the category, or go back to the full catalogue. -->
                        <div class="action-row">
                            <?php if (!isAdminPreviewMode()): ?>
                                <form method="POST" action="basket_add.php" style="margin:0;">
                                    <input type="hidden" name="product_id" value="<?php echo (int) $product['ProductID']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button class="detail-button" type="submit" <?php echo (int) $product['Stock'] <= 0 ? 'disabled' : ''; ?>>
                                        <?php echo (int) $product['Stock'] <= 0 ? 'Out of Stock' : 'Add to Basket'; ?>
                                    </button>
                                </form>
                            <?php endif; ?>

                            <a class="detail-link" href="products.php?category=<?php echo urlencode($product['Category']); ?>">
                                More in <?php echo htmlspecialchars($product['Category']); ?>
                            </a>
                            <a class="detail-link" href="products.php">Back to Catalogue</a>
                        </div>
                    </div>
                </section>

                <!-- Reviews and related products sit lower down once the main product details are out of the way. -->
                <section class="review-layout">
                    <article class="review-card">
                        <!-- The review form stays on the same page so feedback is tied closely to the product. -->
                        <h2>Customer Reviews</h2>
                        <p>See what other shoppers thought and add your own review if you have tried this item.</p>

                        <?php if (!isAdminPreviewMode()): ?>
                            <form class="review-form" action="review_add.php" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo (int) $product['ProductID']; ?>">

                                <div>
                                    <label for="rating">Rating</label>
                                    <select id="rating" name="rating" required>
                                        <option value="">Choose a rating</option>
                                        <option value="5">5 stars</option>
                                        <option value="4">4 stars</option>
                                        <option value="3">3 stars</option>
                                        <option value="2">2 stars</option>
                                        <option value="1">1 star</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="comment">Your review</label>
                                    <textarea id="comment" name="comment" required placeholder="What did you think of this product?"></textarea>
                                </div>

                                <button class="review-submit" type="submit">Submit Review</button>
                            </form>
                        <?php endif; ?>

                        <?php if ($reviews): ?>
                            <div class="review-list">
                                <?php foreach ($reviews as $review): ?>
                                    <div class="review-item">
                                        <div class="review-head">
                                            <div><?php echo htmlspecialchars($review['Username']); ?> · <?php echo (int) $review['Rating']; ?>/5</div>
                                            <div class="review-date"><?php echo htmlspecialchars(date('j M Y', strtotime((string) $review['CreatedAt']))); ?></div>
                                        </div>
                                        <p><?php echo nl2br(htmlspecialchars($review['Comment'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="review-empty">
                                <p>No reviews yet. This would be a good one to review first.</p>
                            </div>
                        <?php endif; ?>
                    </article>

                    <aside class="related-card">
                        <!-- Related items keep people moving through the same category without a full search reset. -->
                        <h2>Related Products</h2>
                        <p>More options from the same category if you want to compare before adding to your basket.</p>

                        <?php if ($relatedProducts): ?>
                            <div class="related-grid">
                                <?php foreach ($relatedProducts as $related): ?>
                                    <div class="related-item">
                                        <div>
                                            <strong><?php echo htmlspecialchars($related['ProductName']); ?></strong>
                                            <div class="related-price">&pound;<?php echo number_format((float) $related['Price'], 2); ?></div>
                                            <div><?php echo htmlspecialchars(productStockLabel((int) $related['Stock'])); ?></div>
                                        </div>
                                        <a class="related-link" href="product.php?id=<?php echo (int) $related['ProductID']; ?>">View</a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="review-empty">
                                <p>This is the only product in this category right now.</p>
                            </div>
                        <?php endif; ?>
                    </aside>
                </section>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>

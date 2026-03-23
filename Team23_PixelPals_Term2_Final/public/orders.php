<?php
// This page shows the order history for the signed-in user, or an admin preview of that history.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../app/includes/auth.php';
require_once '../app/config/db.php';
require_once '../app/includes/admin_preview.php';

$isAdminPreview = isset($_SESSION['admin_id']) && !isset($_SESSION['user_id']);

// Customers see only their own orders, while admins can preview the customer-facing history screen.
if (!$isAdminPreview) {
    requireLogin();
}

$orders = [];

try {
    if ($isAdminPreview) {
        // Admin preview mode loads the whole order list so the page still has something meaningful to show.
        $stmt = $db->query(
            'SELECT
                o.OrderID,
                o.Status,
                COALESCE(SUM(oi.Quantity), 0) AS ItemCount,
                COALESCE(SUM(oi.Subtotal), 0) + COALESCE(o.EngravingFee, 0) AS OrderTotal
             FROM orders o
             LEFT JOIN orderitem oi ON oi.OrderID = o.OrderID
             GROUP BY o.OrderID, o.Status
             ORDER BY o.OrderID DESC'
        );
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Normal customers are locked to orders tied to their own user id.
        $stmt = $db->prepare(
            'SELECT
                o.OrderID,
                o.Status,
                COALESCE(SUM(oi.Quantity), 0) AS ItemCount,
                COALESCE(SUM(oi.Subtotal), 0) + COALESCE(o.EngravingFee, 0) AS OrderTotal
             FROM orders o
             LEFT JOIN orderitem oi ON oi.OrderID = o.OrderID
             WHERE o.UserID = ?
             GROUP BY o.OrderID, o.Status
             ORDER BY o.OrderID DESC'
        );
        $stmt->execute([$_SESSION['user_id']]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Keep the page alive even if the history query has a problem.
    $orders = [];
}

// This helper keeps the status pill classes readable in the template.
function order_status_class(string $status): string
{
    return match ($status) {
        'completed', 'delivered' => 'good',
        'processing' => 'active',
        'cancelled' => 'bad',
        default => 'pending',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PixelPals | Orders</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .order-card, .empty-state a {
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .order-card:hover, .empty-state a:hover {
            transform: translateY(-2px);
        }

        .admin-preview {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
            padding: 14px 18px;
            border-radius: 22px;
            background: rgba(17, 37, 77, 0.9);
            color: #fff;
            box-shadow: var(--shadow);
        }

        .admin-preview strong {
            display: block;
            margin-bottom: 4px;
        }

        .admin-preview a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 16px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--mint), #f5ff9a);
            color: var(--navy);
            font-weight: 800;
            white-space: nowrap;
        }

        .orders-board, .empty-state {
            background: var(--card);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.6);
            border-radius: 30px;
            box-shadow: var(--shadow);
        }

        .orders-board {
            padding: 24px;
            margin-bottom: 36px;
        }

        .board-head {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 16px;
            margin-bottom: 18px;
        }

        .board-head h2 {
            margin: 0 0 6px;
            font-size: 2rem;
        }

        .board-head p {
            margin: 0;
            opacity: 0.76;
        }

        .orders-grid {
            display: grid;
            gap: 16px;
        }

        .order-card {
            display: grid;
            grid-template-columns: 1.2fr 0.9fr 0.9fr auto;
            gap: 16px;
            align-items: center;
            padding: 20px;
            border-radius: 24px;
            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(201,218,255,0.92));
            border: 1px solid rgba(17,37,77,0.08);
            box-shadow: 0 14px 28px rgba(17,37,77,0.08);
        }

        .order-card h3 {
            margin: 0 0 8px;
            font-size: 1.2rem;
        }

        .order-card p {
            margin: 0;
            opacity: 0.8;
            line-height: 1.5;
        }

        .metric strong {
            display: block;
            font-size: 1.2rem;
            margin-bottom: 6px;
        }

        .status-pill {
            display: inline-flex;
            padding: 9px 12px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 800;
            text-transform: capitalize;
        }

        .status-pill.good { background: rgba(72, 187, 120, 0.14); color: #1b7a44; }
        .status-pill.active { background: rgba(87, 166, 255, 0.16); color: #1d64c9; }
        .status-pill.pending { background: rgba(255, 213, 77, 0.22); color: #8f6500; }
        .status-pill.bad { background: rgba(229, 62, 62, 0.16); color: #b43030; }

        .view-link {
            padding: 12px 14px;
            border-radius: 14px;
            background: rgba(17, 37, 77, 0.08);
            font-weight: 800;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .order-actions {
            display: grid;
            justify-items: start;
            gap: 14px;
        }

        .empty-state {
            padding: 28px;
            text-align: center;
            margin-bottom: 36px;
        }

        .empty-state a {
            display: inline-block;
            margin-top: 14px;
            padding: 14px 18px;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--mint), #f5ff9a);
            font-weight: 800;
        }

        @media (max-width: 920px) {
            .order-card { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 640px) {
            .order-card { grid-template-columns: 1fr; }
            .board-head { flex-direction: column; align-items: start; }
            .admin-preview { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="site-shell">
        <?php if ($isAdminPreview): ?>
            <?php renderAdminPreviewBanner('/Team23_PixelPals_Term2_Final/public/admin/dashboard.php'); ?>
        <?php endif; ?>

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
                <?php if ($isAdminPreview): ?>
                    <a class="primary-link" href="admin/dashboard.php">Admin Dashboard</a>
                <?php else: ?>
                    <a class="primary-link" href="account.php">My Account</a>
                <?php endif; ?>
            </div>
        </header>

        <nav class="bottomNav">
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="products.php">Products</a>
                <a href="about.php">About Us</a>
                <a href="contact.php">Contact Us</a>
                <a class="active" href="orders.php">Orders</a>
                <?php if ($isAdminPreview): ?>
                    <a href="admin/dashboard.php">Admin Dashboard</a>
                <?php else: ?>
                    <a href="logout.php">Log Out</a>
                <?php endif; ?>
            </div>
        </nav>

        <!-- The order list or empty state starts here. -->
        <?php if ($orders): ?>
            <section class="orders-board">
                <div class="board-head">
                    <div>
                        <h2>Your Orders</h2>
                        <p>Most recent orders appear first.</p>
                    </div>
                    <div><?php echo count($orders); ?> total order<?php echo count($orders) === 1 ? '' : 's'; ?></div>
                </div>

                <div class="orders-grid">
                    <?php foreach ($orders as $order): ?>
                        <!-- Each card gives the key order facts, with a separate link to the full breakdown. -->
                        <article class="order-card">
                            <div>
                                <h3>Order #<?php echo (int) $order['OrderID']; ?></h3>
                                <p>Open this order to see the full details.</p>
                            </div>

                            <div class="metric">
                                <strong><?php echo (int) $order['ItemCount']; ?> item<?php echo (int) $order['ItemCount'] === 1 ? '' : 's'; ?></strong>
                                <p>Items in this order</p>
                            </div>

                            <div class="metric">
                                <strong>&pound;<?php echo number_format((float) $order['OrderTotal'], 2); ?></strong>
                                <p>Order total</p>
                            </div>

                            <div class="order-actions">
                                <div class="status-pill <?php echo order_status_class($order['Status']); ?>">
                                    <?php echo htmlspecialchars($order['Status']); ?>
                                </div>
                                <a class="view-link" href="order_view.php?id=<?php echo (int) $order['OrderID']; ?>">View Order</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php else: ?>
            <!-- Brand new accounts land here before they have placed anything. -->
            <section class="empty-state">
                <h2>No orders yet</h2>
                <p>Your order history will appear here once you complete a purchase.</p>
                <a href="products.php">Start Shopping</a>
            </section>
        <?php endif; ?>
    </div>
</body>
</html>

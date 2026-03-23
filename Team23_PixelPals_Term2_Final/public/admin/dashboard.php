<?php
// The dashboard is the admin starting point, so it pulls together the headline numbers and key routes.
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../public/login.php");
    exit();
}

require_once '../../app/config/db.php';
require_once '../../app/includes/admin_panel_page.php';

$totalCustomers = 0;
$totalMessages = 0;
$totalProducts = 0;
$totalOrders = 0;

try {
    $totalCustomers = (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalMessages = (int) $db->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
    $totalProducts = (int) $db->query("SELECT COUNT(*) FROM product")->fetchColumn();
    $totalOrders = (int) $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
} catch (Exception $e) {
    // Keep dashboard visible even if one query fails
}

// These extra styles are only for the dashboard cards layered on top of the shared admin shell.
$extraStyles = <<<'CSS'
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }
        .stat-card,
        .section-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 30px;
            box-shadow: var(--shadow);
        }
        .stat-card {
            padding: 22px;
            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(201,218,255,0.92));
        }
        .stat-card span {
            display: inline-block;
            margin-bottom: 10px;
            padding: 8px 10px;
            border-radius: 12px;
            background: linear-gradient(90deg, rgba(116,70,200,0.12), rgba(255,109,178,0.12));
            font-size: 0.82rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .stat-card strong {
            display: block;
            font-size: 2rem;
            margin-bottom: 8px;
        }
        .stat-card p,
        .section-card p {
            margin: 0;
            line-height: 1.7;
            opacity: 0.88;
        }
        .sections-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        .section-card {
            padding: 28px;
            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(247,250,255,0.82));
        }
        .section-card h2 {
            margin: 0 0 10px;
            font-size: 1.8rem;
        }
        .action-list {
            display: grid;
            gap: 14px;
            margin-top: 18px;
        }
        .action-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            padding: 16px 18px;
            border-radius: 20px;
            background: rgba(17, 37, 77, 0.05);
            border: 1px solid var(--outline);
        }
        .action-copy strong {
            display: block;
            margin-bottom: 4px;
            font-size: 1rem;
        }
        .action-link {
            padding: 11px 14px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--mint), #f5ff9a);
            font-weight: 800;
            white-space: nowrap;
        }
        .welcome-pill {
            font-weight: 800;
            opacity: 0.92;
        }
        @media (max-width: 980px) {
            .sections-grid {
                grid-template-columns: 1fr;
            }
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .action-item {
                flex-direction: column;
                align-items: flex-start;
            }
        }
CSS;

render_admin_panel_start([
    'title' => 'PixelPals Admin Dashboard',
    'brand_subtitle' => 'Control panel for customers, products, orders and support',
    'shell_width' => '1180px',
    'extra_styles' => $extraStyles,
]);
?>
            <!-- The top block keeps the page title and quick links together. -->
            <div class="section-header">
                <div>
                    <h1>Dashboard</h1>
                    <p>Jump into the key admin areas and keep track of store activity at a glance.</p>
                </div>

                <div class="section-actions">
                    <span class="welcome-pill">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                    <a class="ghost" href="../account.php">My Account</a>
                    <a class="ghost" href="../index.php">View Site</a>
                </div>
            </div>

            <!-- These headline cards are the quickest read on store activity. -->
            <section class="stats-grid">
                <article class="stat-card">
                    <span>Customers</span>
                    <strong><?php echo $totalCustomers; ?></strong>
                    <p>Track customer growth and jump straight into account management.</p>
                </article>

                <article class="stat-card">
                    <span>Messages</span>
                    <strong><?php echo $totalMessages; ?></strong>
                    <p>Stay on top of contact enquiries and customer follow-ups.</p>
                </article>

                <article class="stat-card">
                    <span>Products</span>
                    <strong><?php echo $totalProducts; ?></strong>
                    <p>Monitor the size of the live catalogue and update stock faster.</p>
                </article>

                <article class="stat-card">
                    <span>Orders</span>
                    <strong><?php echo $totalOrders; ?></strong>
                    <p>See how many orders are now moving through the system.</p>
                </article>
            </section>

            <!-- The lower half groups the actual admin routes by the kind of work they support. -->
            <section class="sections-grid">
                <section class="section-card">
                    <!-- Customer-facing admin work lives together here for support and account tasks. -->
                    <h2>Customer & Support</h2>
                    <p>Manage accounts, review messages and keep customer-facing operations moving.</p>

                    <div class="action-list">
                        <div class="action-item">
                            <div class="action-copy">
                                <strong>Manage Customers</strong>
                                Edit or review customer accounts from the admin area.
                            </div>
                            <a class="action-link" href="customers.php">Open</a>
                        </div>

                        <div class="action-item">
                            <div class="action-copy">
                                <strong>View Messages</strong>
                                Check new enquiries and reply priorities.
                            </div>
                            <a class="action-link" href="messages.php">Open</a>
                        </div>

                        <div class="action-item">
                            <div class="action-copy">
                                <strong>Review Orders</strong>
                                Keep an eye on order activity from the admin side.
                            </div>
                            <a class="action-link" href="orders.php">Open</a>
                        </div>

                        <div class="action-item">
                            <div class="action-copy">
                                <strong>Manage Returns</strong>
                                Review return requests and update their progress.
                            </div>
                            <a class="action-link" href="returns.php">Open</a>
                        </div>
                    </div>
                </section>

                <section class="section-card">
                    <!-- Inventory tasks are kept together so stock and product work are easy to find. -->
                    <h2>Inventory & Catalogue</h2>
                    <p>Maintain product information, update stock and keep the catalogue ready for customers.</p>

                    <div class="action-list">
                        <div class="action-item">
                            <div class="action-copy">
                                <strong>All Products</strong>
                                Review the full product list and current stock levels.
                            </div>
                            <a class="action-link" href="products.php">Open</a>
                        </div>

                        <div class="action-item">
                            <div class="action-copy">
                                <strong>Stock Incoming</strong>
                                Record stock shipments and keep inventory accurate.
                            </div>
                            <a class="action-link" href="stock_incoming.php">Open</a>
                        </div>
                    </div>
                </section>
            </section>

<?php render_admin_panel_end(); ?>

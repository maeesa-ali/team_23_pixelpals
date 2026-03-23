<?php
// This page is the quick restock screen, so admins can bump stock levels without editing full products.
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../public/login.php');
    exit();
}

require_once '../../app/config/db.php';
require_once '../../app/includes/flash.php';
require_once '../../app/includes/admin_panel_page.php';

// The stock screen only needs a lean product list because it is built for quick restocking.
$products = [];

try {
    // Lowest-stock items are loaded first so the more urgent ones naturally rise to the top.
    $stmt = $db->query(
        'SELECT ProductID, ProductName, Category, Stock
         FROM product
         ORDER BY Stock ASC, ProductName ASC'
    );
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = 'Could not load stock records right now.';
}

// These helpers keep the stock health wording and badge class logic in one place.
function incoming_status_label(int $stock): string
{
    if ($stock <= 0) {
        return 'Restock now';
    }

    if ($stock <= 10) {
        return 'Running low';
    }

    return 'Healthy';
}

function incoming_status_class(int $stock): string
{
    if ($stock <= 0) {
        return 'bad';
    }

    if ($stock <= 10) {
        return 'pending';
    }

    return 'good';
}

$extraStyles = <<<'CSS'
        .content-grid {
            display: grid;
            grid-template-columns: minmax(320px, 0.9fr) minmax(0, 1.1fr);
            gap: 20px;
            align-items: start;
        }
        .form-card,
        .list-card {
            padding: 24px;
            border-radius: 24px;
            border: 1px solid var(--outline);
            background: rgba(255, 255, 255, 0.72);
        }
        .form-card h2,
        .list-card h2 {
            margin: 0 0 12px;
            font-size: 1.4rem;
        }
        .form-card p,
        .list-card p {
            margin: 0;
            line-height: 1.7;
            opacity: 0.84;
        }
        .form-grid {
            display: grid;
            gap: 16px;
            margin-top: 18px;
        }
        .field {
            display: grid;
            gap: 8px;
        }
        label {
            font-weight: 800;
        }
        select,
        input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(17, 37, 77, 0.14);
            font: inherit;
            background: rgba(255, 255, 255, 0.92);
            color: var(--navy);
        }
        button {
            padding: 14px 18px;
            border: none;
            border-radius: 16px;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
            background: linear-gradient(135deg, var(--mint), #f5ff9a);
            color: var(--navy);
        }
        .product-list {
            display: grid;
            gap: 12px;
            margin-top: 18px;
        }
        .product-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 16px 18px;
            border-radius: 18px;
            background: rgba(17, 37, 77, 0.05);
        }
        .product-copy strong {
            display: block;
            margin-bottom: 4px;
        }
        .product-copy span {
            opacity: 0.74;
            font-size: 0.92rem;
        }
        .product-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .stock-pill,
        .status-pill {
            display: inline-flex;
            padding: 9px 12px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 800;
        }
        .stock-pill {
            background: rgba(17, 37, 77, 0.08);
        }
        .status-pill.good { background: rgba(72, 187, 120, 0.14); color: #1b7a44; }
        .status-pill.pending { background: rgba(255, 213, 77, 0.22); color: #8f6500; }
        .status-pill.bad { background: rgba(229, 62, 62, 0.16); color: #b43030; }
        .empty-state {
            padding: 24px;
            text-align: center;
            opacity: 0.82;
        }
        @media (max-width: 920px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 720px) {
            .product-row {
                flex-direction: column;
                align-items: flex-start;
            }
            .product-meta {
                justify-content: flex-start;
            }
        }
CSS;

render_admin_panel_start([
    'title' => 'Stock Incoming | PixelPals Admin',
    'brand_subtitle' => 'Receive new stock and keep the catalogue accurate',
    'shell_width' => '1180px',
    'extra_styles' => $extraStyles,
]);
?>
            <!-- The heading explains the task, while the action links keep the main admin routes nearby. -->
            <div class="section-header">
                <div>
                    <h1>Stock Incoming</h1>
                    <p>Use this page to record incoming stock deliveries and immediately increase the live stock value shown across the admin area and storefront.</p>
                </div>

                <div class="section-actions">
                    <a class="ghost" href="dashboard.php">Back to Dashboard</a>
                    <a class="primary" href="../logout.php">Log Out</a>
                </div>
            </div>

            <div class="flash-wrap">
                <?php display_flash_messages(); ?>
            </div>

            <!-- The page is split between the quick delivery form and the live stock snapshot. -->
            <div class="content-grid">
                <section class="form-card">
                    <!-- This form is the only action the admin needs to record an incoming stock delivery. -->
                    <h2>Record Delivery</h2>
                    <p>Select a product and enter how many new units have arrived.</p>

                    <form method="POST" action="admin_stock_incoming_post.php" class="form-grid">
                        <div class="field">
                            <label for="product_id">Product</label>
                            <select id="product_id" name="product_id" required>
                                <option value="">Choose a product</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo (int) $product['ProductID']; ?>">
                                        <?php echo htmlspecialchars((string) $product['ProductName']); ?> (Current stock: <?php echo (int) $product['Stock']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label for="incoming_quantity">Incoming Quantity</label>
                            <input id="incoming_quantity" type="number" name="incoming_quantity" min="1" required>
                        </div>

                        <button type="submit">Add Incoming Stock</button>
                    </form>
                </section>

                <aside class="list-card">
                    <!-- The stock list acts like a quick checklist so low items stand out immediately. -->
                    <h2>Current Stock Snapshot</h2>
                    <p>The lowest-stock items appear first so urgent restocks are easier to spot.</p>

                    <div class="product-list">
                        <?php if (empty($products)): ?>
                            <div class="empty-state">No products are available to restock yet.</div>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <?php $stock = (int) $product['Stock']; ?>
                                <div class="product-row">
                                    <div class="product-copy">
                                        <strong><?php echo htmlspecialchars((string) $product['ProductName']); ?></strong>
                                        <span><?php echo htmlspecialchars((string) $product['Category']); ?></span>
                                    </div>

                                    <div class="product-meta">
                                        <span class="stock-pill"><?php echo $stock; ?> in stock</span>
                                        <span class="status-pill <?php echo incoming_status_class($stock); ?>">
                                            <?php echo htmlspecialchars(incoming_status_label($stock)); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </aside>
            </div>
<?php render_admin_panel_end(); ?>

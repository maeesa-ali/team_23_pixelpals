<?php
// This page is the admin product list, so stock, pricing and quick actions all sit in one table.
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../public/login.php');
    exit();
}

require_once '../../app/config/db.php';
require_once '../../app/includes/flash.php';
require_once '../../app/includes/admin_panel_page.php';

// The product list page rolls summary counts, quick actions and the full table into one admin screen.
$products = [];
$categoryCounts = [];
$totals = [
    'products' => 0,
    'in_stock' => 0,
    'low_stock' => 0,
    'out_of_stock' => 0,
];

try {
    // Pull the full product list first, then derive the stock and category summaries from that same data.
    $stmt = $db->query('SELECT ProductID, ProductName, Description, Category, ImagePath, Price, Stock FROM product ORDER BY ProductID DESC');
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $product) {
        $totals['products']++;

        if ((int) $product['Stock'] <= 0) {
            $totals['out_of_stock']++;
        } elseif ((int) $product['Stock'] <= 10) {
            $totals['low_stock']++;
            $totals['in_stock']++;
        } else {
            $totals['in_stock']++;
        }

        $category = trim((string) $product['Category']);
        if ($category !== '') {
            $categoryCounts[$category] = ($categoryCounts[$category] ?? 0) + 1;
        }
    }

    arsort($categoryCounts);
} catch (PDOException $e) {
    $_SESSION['error'] = 'Could not load product records right now.';
}

// These helpers keep the stock wording and badge class logic out of the table markup.
function stock_status_label(int $stock): string
{
    if ($stock <= 0) {
        return 'Out of stock';
    }

    if ($stock <= 10) {
        return 'Low stock';
    }

    return 'In stock';
}

function stock_status_class(int $stock): string
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }
        .stat-card {
            padding: 18px 20px;
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(214,226,255,0.88));
            border: 1px solid var(--outline);
        }
        .stat-card strong {
            display: block;
            font-size: 1.55rem;
            margin-bottom: 4px;
        }
        .stat-card span {
            opacity: 0.76;
            font-weight: 700;
        }
        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 280px;
            gap: 20px;
            align-items: start;
        }
        .table-wrap {
            overflow-x: auto;
            border-radius: 24px;
            border: 1px solid var(--outline);
            background: rgba(255, 255, 255, 0.62);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 980px;
        }
        th, td {
            padding: 16px 18px;
            text-align: left;
            border-bottom: 1px solid rgba(17, 37, 77, 0.08);
            vertical-align: top;
        }
        th {
            font-size: 0.82rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            background: rgba(17, 37, 77, 0.06);
        }
        tbody tr:hover {
            background: rgba(87, 166, 255, 0.08);
        }
        .product-name {
            display: grid;
            gap: 4px;
        }
        .product-cell {
            display: grid;
            grid-template-columns: 72px 1fr;
            gap: 14px;
            align-items: start;
        }
        .product-thumb {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 16px;
            background: rgba(255,255,255,0.78);
            border: 1px solid rgba(17, 37, 77, 0.08);
        }
        .product-name strong {
            font-size: 1rem;
        }
        .product-name span {
            opacity: 0.74;
            font-size: 0.92rem;
        }
        .price {
            font-weight: 800;
            white-space: nowrap;
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
        .status-pill.pending { background: rgba(255, 213, 77, 0.22); color: #8f6500; }
        .status-pill.bad { background: rgba(229, 62, 62, 0.16); color: #b43030; }
        .row-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .row-actions a,
        .row-actions button {
            padding: 10px 14px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.92rem;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }
        .row-actions .edit {
            background: rgba(17, 37, 77, 0.08);
        }
        .row-actions .delete {
            background: rgba(255, 109, 178, 0.16);
            color: #8f1f53;
        }
        .side-card {
            padding: 22px;
            border-radius: 24px;
            border: 1px solid var(--outline);
            background: rgba(255, 255, 255, 0.72);
        }
        .side-card h2 {
            margin: 0 0 12px;
            font-size: 1.2rem;
        }
        .side-card p {
            margin: 0;
            line-height: 1.7;
            opacity: 0.84;
        }
        .tag-list {
            display: grid;
            gap: 10px;
            margin-top: 16px;
        }
        .tag-item {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 16px;
            background: rgba(17, 37, 77, 0.06);
            font-weight: 700;
        }
        .empty-state {
            padding: 26px;
            text-align: center;
            opacity: 0.82;
        }
        @media (max-width: 1020px) {
            .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .content-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 720px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
CSS;

render_admin_panel_start([
    'title' => 'Product Management | PixelPals Admin',
    'brand_subtitle' => 'Catalogue control, stock visibility and product maintenance',
    'shell_width' => '1260px',
    'extra_styles' => $extraStyles,
]);
?>
            <!-- The admin product table is the main working area for stock and catalogue changes. -->
            <div class="section-header">
                <div>
                    <h1>Product Management</h1>
                    <p>Review the full catalogue, spot low stock quickly and jump into creating or editing products without dealing with the old broken admin flow.</p>
                </div>

                <div class="section-actions">
                    <span class="meta"><?php echo $totals['products']; ?> products</span>
                    <a class="primary" href="product_create.php">Add Product</a>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <strong><?php echo $totals['products']; ?></strong>
                    <span>Total products</span>
                </div>
                <div class="stat-card">
                    <strong><?php echo $totals['in_stock']; ?></strong>
                    <span>In stock</span>
                </div>
                <div class="stat-card">
                    <strong><?php echo $totals['low_stock']; ?></strong>
                    <span>Low stock</span>
                </div>
                <div class="stat-card">
                    <strong><?php echo $totals['out_of_stock']; ?></strong>
                    <span>Out of stock</span>
                </div>
            </div>

            <div class="flash-wrap">
                <?php display_flash_messages(); ?>
            </div>

            <div class="content-grid">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="7" class="empty-state">No products are in the catalogue yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <?php $stock = (int) $product['Stock']; ?>
                                    <tr>
                                        <td>#<?php echo (int) $product['ProductID']; ?></td>
                                        <td>
                                            <div class="product-cell">
                                                <img class="product-thumb" src="../<?php echo htmlspecialchars((string) ($product['ImagePath'] ?: 'assets/img/logo.png')); ?>" alt="<?php echo htmlspecialchars((string) $product['ProductName']); ?>">
                                                <div class="product-name">
                                                    <strong><?php echo htmlspecialchars((string) $product['ProductName']); ?></strong>
                                                    <span><?php echo htmlspecialchars((string) $product['Description']); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars((string) $product['Category']); ?></td>
                                        <td class="price">&pound;<?php echo number_format((float) $product['Price'], 2); ?></td>
                                        <td><?php echo $stock; ?></td>
                                        <td>
                                            <span class="status-pill <?php echo stock_status_class($stock); ?>">
                                                <?php echo htmlspecialchars(stock_status_label($stock)); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="row-actions">
                                                <a class="edit" href="product_edit.php?id=<?php echo (int) $product['ProductID']; ?>">Edit</a>
                                                <form method="POST" action="admin_product_delete.php" data-confirm="Delete this product from the catalogue?" style="margin:0;">
                                                    <input type="hidden" name="id" value="<?php echo (int) $product['ProductID']; ?>">
                                                    <button class="delete" type="submit">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <aside class="side-card">
                    <h2>Categories</h2>
                    <p>Quick snapshot of how the current catalogue is split across categories.</p>

                    <div class="tag-list">
                        <?php if (empty($categoryCounts)): ?>
                            <div class="tag-item">
                                <span>No categories yet</span>
                                <strong>0</strong>
                            </div>
                        <?php else: ?>
                            <?php foreach ($categoryCounts as $category => $count): ?>
                                <div class="tag-item">
                                    <span><?php echo htmlspecialchars($category); ?></span>
                                    <strong><?php echo (int) $count; ?></strong>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </aside>
            </div>
<?php render_admin_panel_end('<script src="../js/confirm_actions.js?v=1" defer></script>'); ?>



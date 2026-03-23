<?php
// This page is the main admin order queue, so status updates and order details live side by side here.
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../public/login.php');
    exit();
}

require_once '../../app/config/db.php';
require_once '../../app/includes/admin_panel_page.php';

// Keep the full queue and the selected order detail on one page so processing feels quicker.
$orders = [];
$orderItemsByOrder = [];
$error = null;
$selectedOrder = null;
$selectedOrderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$statusCounts = [
    'pending' => 0,
    'processing' => 0,
    'completed' => 0,
    'delivered' => 0,
    'cancelled' => 0,
];
$flashSuccess = $_SESSION['success'] ?? null;
$flashError = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

try {
    // Load the order headers first because both the left-hand list and right-hand detail panel depend on them.
    $orderStmt = $db->query(
        'SELECT
            o.OrderID,
            o.UserID,
            o.Status,
            o.EngravingName,
            o.EngravingFee,
            u.Username,
            u.FirstName,
            u.LastName,
            u.Email,
            COALESCE(SUM(oi.Quantity), 0) AS ItemCount,
            COALESCE(SUM(oi.Subtotal), 0) + COALESCE(o.EngravingFee, 0) AS OrderTotal
         FROM orders o
         JOIN users u ON u.UserID = o.UserID
         LEFT JOIN orderitem oi ON oi.OrderID = o.OrderID
         GROUP BY
            o.OrderID,
            o.UserID,
            o.Status,
            o.EngravingName,
            o.EngravingFee,
            u.Username,
            u.FirstName,
            u.LastName,
            u.Email
         ORDER BY o.OrderID DESC'
    );
    $orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as $order) {
        $status = strtolower((string) $order['Status']);
        if (isset($statusCounts[$status])) {
            $statusCounts[$status]++;
        }
    }

    // Pull all line items in one pass, then group them by order id for the detail pane.
    $itemStmt = $db->query(
        'SELECT
            oi.OrderID,
            oi.OrderItemID,
            p.ProductName,
            oi.Quantity,
            oi.totalProductPrice,
            oi.Subtotal
         FROM orderitem oi
         JOIN product p ON p.ProductID = oi.ProductID
         ORDER BY oi.OrderID DESC, p.ProductName ASC'
    );
    $allItems = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($allItems as $item) {
        $orderId = (int) $item['OrderID'];
        if (!isset($orderItemsByOrder[$orderId])) {
            $orderItemsByOrder[$orderId] = [];
        }
        $orderItemsByOrder[$orderId][] = $item;
    }

    if ($selectedOrderId > 0) {
        // Respect an explicit order id so reloads return to the same selected order.
        foreach ($orders as $order) {
            if ((int) $order['OrderID'] === $selectedOrderId) {
                $selectedOrder = $order;
                break;
            }
        }
    }

    if (!$selectedOrder && !empty($orders)) {
        // Fall back to the newest order so the detail area is not blank by default.
        $selectedOrder = $orders[0];
    }
} catch (PDOException $e) {
    $error = 'Orders could not be loaded right now.';
}

// Small helpers keep status styling and customer display names tidy inside the template.
function admin_order_status_class(string $status): string
{
    return match ($status) {
        'completed', 'delivered' => 'good',
        'processing' => 'active',
        'cancelled' => 'bad',
        default => 'pending',
    };
}

function admin_order_customer_name(array $order): string
{
    $name = trim(($order['FirstName'] ?? '') . ' ' . ($order['LastName'] ?? ''));
    return $name !== '' ? $name : (string) $order['Username'];
}

$extraStyles = <<<'CSS'
        .section-actions {
            justify-content: flex-end;
        }
        .summary-meta {
            display: flex;
            align-items: center;
        }
        .order-count {
            font-size: 1rem;
            font-weight: 800;
            color: rgba(17, 37, 77, 0.72);
            white-space: nowrap;
        }
        .action-buttons {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .status-strip {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }
        .status-chip {
            padding: 16px 18px;
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(214,226,255,0.88));
            border: 1px solid var(--outline);
        }
        .status-chip strong {
            display: block;
            font-size: 1.45rem;
            margin-bottom: 4px;
        }
        .status-chip span {
            opacity: 0.76;
            font-weight: 700;
        }
        .notice {
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(255, 109, 178, 0.12);
            color: #8f1f53;
            border: 1px solid rgba(255, 109, 178, 0.2);
        }
        .notice.success {
            background: rgba(204, 255, 111, 0.36);
            color: #375100;
            border-color: rgba(150, 190, 45, 0.24);
        }
        .orders-layout {
            display: grid;
            grid-template-columns: minmax(0, 0.9fr) minmax(360px, 0.95fr);
            gap: 20px;
            align-items: start;
        }
        .order-list {
            display: grid;
            gap: 14px;
        }
        .order-card {
            width: 100%;
            padding: 18px;
            border: 1px solid var(--outline);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.72);
            cursor: pointer;
            text-align: left;
            font: inherit;
            color: inherit;
            box-shadow: 0 12px 24px rgba(17, 37, 77, 0.07);
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background 0.18s ease;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 28px rgba(17, 37, 77, 0.1);
        }
        .order-card.selected {
            border-color: rgba(43, 111, 214, 0.28);
            background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(226,241,255,0.95));
        }
        .order-top,
        .order-bottom {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
        }
        .order-bottom {
            margin-top: 16px;
            align-items: end;
        }
        .order-main strong {
            display: block;
            margin-bottom: 6px;
            font-size: 1.08rem;
        }
        .order-main span,
        .order-submeta,
        .metric-label {
            opacity: 0.74;
            font-size: 0.92rem;
        }
        .order-submeta {
            margin-top: 8px;
        }
        .metrics {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
        }
        .metric strong {
            display: block;
            font-size: 1.02rem;
            margin-bottom: 4px;
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
        .detail-card {
            padding: 24px;
            border-radius: 26px;
            border: 1px solid var(--outline);
            background:
                radial-gradient(circle at top right, rgba(255, 213, 77, 0.16), transparent 22%),
                rgba(255, 255, 255, 0.78);
        }
        .detail-card h2 {
            margin: 0 0 14px;
            font-size: 1.5rem;
        }
        .detail-pill {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(17, 37, 77, 0.08);
            font-weight: 800;
            font-size: 0.85rem;
            margin-bottom: 18px;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }
        .detail-item {
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(17, 37, 77, 0.05);
        }
        .detail-item.span-2 {
            grid-column: 1 / -1;
        }
        .detail-item span {
            display: block;
            font-size: 0.76rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            opacity: 0.64;
            font-weight: 800;
            margin-bottom: 6px;
        }
        .detail-item strong {
            display: block;
            line-height: 1.6;
        }
        .detail-note {
            margin-bottom: 20px;
            padding: 16px 18px;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(255,109,178,0.1), rgba(87,166,255,0.1));
        }
        .detail-note strong {
            display: block;
            margin-bottom: 6px;
        }
        .items-block h3 {
            margin: 0 0 12px;
            font-size: 1.08rem;
        }
        .process-block {
            margin-bottom: 22px;
            padding: 18px;
            border-radius: 22px;
            background: rgba(17, 37, 77, 0.05);
        }
        .process-block h3 {
            margin: 0 0 8px;
            font-size: 1.08rem;
        }
        .process-block p {
            margin: 0 0 14px;
            line-height: 1.6;
            opacity: 0.8;
        }
        .process-form {
            display: grid;
            gap: 12px;
        }
        .process-form label {
            display: block;
            font-size: 0.78rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            opacity: 0.64;
            font-weight: 800;
        }
        .process-controls {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }
        .process-form select {
            flex: 1 1 220px;
            min-height: 46px;
            padding: 11px 12px;
            border-radius: 14px;
            border: 1px solid var(--outline);
            background: rgba(255, 255, 255, 0.92);
            font: inherit;
        }
        .process-form button {
            min-height: 46px;
            padding: 11px 16px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--mint), #f5ff9a);
            color: var(--navy);
            font: inherit;
            font-weight: 800;
            cursor: pointer;
        }
        .item-list {
            display: grid;
            gap: 10px;
        }
        .item-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 14px;
            align-items: center;
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(17, 37, 77, 0.05);
        }
        .item-row strong {
            display: block;
            margin-bottom: 4px;
        }
        .item-row span {
            opacity: 0.76;
            font-size: 0.92rem;
        }
        .item-price {
            font-weight: 800;
            white-space: nowrap;
        }
        .empty-state,
        .detail-empty {
            padding: 24px;
            border-radius: 20px;
            background: rgba(17, 37, 77, 0.05);
            text-align: center;
            opacity: 0.82;
        }
        @media (max-width: 980px) {
            .orders-layout {
                grid-template-columns: 1fr;
            }
            .detail-grid {
                grid-template-columns: 1fr;
            }
            .detail-item.span-2 {
                grid-column: auto;
            }
        }
        @media (max-width: 760px) {
            .section-actions {
                align-items: flex-start;
            }
            .action-buttons {
                width: 100%;
            }
            .status-strip {
                grid-template-columns: 1fr 1fr;
            }
            .order-top,
            .order-bottom {
                flex-direction: column;
            }
        }
CSS;

$extraScripts = <<<'HTML'
    <script src="../js/admin_orders.js?v=1" defer></script>
HTML;

render_admin_panel_start([
    'title' => 'Order Management | PixelPals Admin',
    'brand_subtitle' => 'Order oversight across every customer account',
    'shell_width' => '1240px',
    'extra_styles' => $extraStyles,
]);
?>
            <!-- The order list stays on the left, and the selected order detail sits alongside it. -->
            <div class="section-header">
                <div>
                    <h1>Order Management</h1>
                    <p>Check the live order queue, switch between customer purchases and inspect the full basket breakdown without leaving the page.</p>
                </div>

                <div class="section-actions">
                    <div class="summary-meta">
                        <span class="order-count"><?php echo count($orders); ?> orders</span>
                    </div>
                    <div class="action-buttons">
                        <a class="ghost" href="returns.php">Manage Returns</a>
                        <a class="primary" href="dashboard.php">Back to Dashboard</a>
                    </div>
                </div>
            </div>

            <!-- The status strip gives a fast read on how the whole queue is currently distributed. -->
            <div class="status-strip">
                <div class="status-chip">
                    <strong><?php echo $statusCounts['pending']; ?></strong>
                    <span>Pending</span>
                </div>
                <div class="status-chip">
                    <strong><?php echo $statusCounts['processing']; ?></strong>
                    <span>Processing</span>
                </div>
                <div class="status-chip">
                    <strong><?php echo $statusCounts['completed'] + $statusCounts['delivered']; ?></strong>
                    <span>Fulfilled</span>
                </div>
                <div class="status-chip">
                    <strong><?php echo $statusCounts['cancelled']; ?></strong>
                    <span>Cancelled</span>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="notice"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($flashSuccess): ?>
                <div class="notice success"><?php echo htmlspecialchars($flashSuccess); ?></div>
            <?php endif; ?>
            <?php if ($flashError): ?>
                <div class="notice"><?php echo htmlspecialchars($flashError); ?></div>
            <?php endif; ?>

            <div class="orders-layout">
                <section class="order-list">
                    <!-- The left column is the selectable queue of orders. -->
                    <?php if (empty($orders)): ?>
                        <div class="empty-state">No orders have been placed yet.</div>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <?php
                            $orderId = (int) $order['OrderID'];
                            $isSelected = $selectedOrder && (int) $selectedOrder['OrderID'] === $orderId;
                            $displayName = admin_order_customer_name($order);
                            $itemsJson = htmlspecialchars(json_encode($orderItemsByOrder[$orderId] ?? [], JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES);
                            ?>
                            <button
                                type="button"
                                class="order-card <?php echo $isSelected ? 'selected' : ''; ?>"
                                data-order-id="<?php echo $orderId; ?>"
                                data-order-user="<?php echo htmlspecialchars($displayName, ENT_QUOTES); ?>"
                                data-order-username="<?php echo htmlspecialchars((string) $order['Username'], ENT_QUOTES); ?>"
                                data-order-email="<?php echo htmlspecialchars((string) $order['Email'], ENT_QUOTES); ?>"
                                data-order-status="<?php echo htmlspecialchars((string) $order['Status'], ENT_QUOTES); ?>"
                                data-order-items="<?php echo (int) $order['ItemCount']; ?>"
                                data-order-total="<?php echo number_format((float) $order['OrderTotal'], 2, '.', ''); ?>"
                                data-order-userid="<?php echo (int) $order['UserID']; ?>"
                                data-order-engraving-name="<?php echo htmlspecialchars((string) ($order['EngravingName'] ?? ''), ENT_QUOTES); ?>"
                                data-order-engraving-fee="<?php echo number_format((float) ($order['EngravingFee'] ?? 0), 2, '.', ''); ?>"
                                data-order-items-json="<?php echo $itemsJson; ?>"
                            >
                                <div class="order-top">
                                    <div class="order-main">
                                        <strong>Order #<?php echo $orderId; ?></strong>
                                        <span><?php echo htmlspecialchars($displayName); ?></span>
                                        <div class="order-submeta">@<?php echo htmlspecialchars((string) $order['Username']); ?> | User ID #<?php echo (int) $order['UserID']; ?></div>
                                    </div>

                                    <span class="status-pill <?php echo admin_order_status_class((string) $order['Status']); ?>">
                                        <?php echo htmlspecialchars((string) $order['Status']); ?>
                                    </span>
                                </div>

                                <div class="order-bottom">
                                    <div class="metrics">
                                        <div class="metric">
                                            <strong><?php echo (int) $order['ItemCount']; ?></strong>
                                            <div class="metric-label">items</div>
                                        </div>
                                        <div class="metric">
                                            <strong>&pound;<?php echo number_format((float) $order['OrderTotal'], 2); ?></strong>
                                            <div class="metric-label">order total</div>
                                        </div>
                                    </div>

                                    <div class="metric-label">Click to inspect</div>
                                </div>
                            </button>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>

                <aside class="detail-card">
                    <!-- The right column is the fuller breakdown for whichever order is selected. -->
                    <h2>Order Detail</h2>

                    <?php if ($selectedOrder): ?>
                        <?php
                        $selectedDisplayName = admin_order_customer_name($selectedOrder);
                        $selectedItems = $orderItemsByOrder[(int) $selectedOrder['OrderID']] ?? [];
                        ?>
                        <div class="detail-pill" id="detailOrderId">Order #<?php echo (int) $selectedOrder['OrderID']; ?></div>

                        <div class="detail-grid">
                            <div class="detail-item">
                                <span>Customer</span>
                                <strong id="detailCustomer"><?php echo htmlspecialchars($selectedDisplayName); ?></strong>
                            </div>
                            <div class="detail-item">
                                <span>Status</span>
                                <strong id="detailStatus"><?php echo htmlspecialchars((string) $selectedOrder['Status']); ?></strong>
                            </div>
                            <div class="detail-item">
                                <span>Username</span>
                                <strong id="detailUsername"><?php echo htmlspecialchars((string) $selectedOrder['Username']); ?></strong>
                            </div>
                            <div class="detail-item">
                                <span>Email</span>
                                <strong id="detailEmail"><?php echo htmlspecialchars((string) $selectedOrder['Email']); ?></strong>
                            </div>
                            <div class="detail-item span-2">
                                <span>Summary</span>
                                <strong id="detailSummary"><?php echo (int) $selectedOrder['ItemCount']; ?> items | &pound;<?php echo number_format((float) $selectedOrder['OrderTotal'], 2); ?> | User ID #<?php echo (int) $selectedOrder['UserID']; ?></strong>
                            </div>
                        </div>

                        <div class="detail-note" id="detailEngraving" <?php echo empty($selectedOrder['EngravingName']) || (float) ($selectedOrder['EngravingFee'] ?? 0) <= 0 ? 'hidden' : ''; ?>>
                            <strong>Personal engraving</strong>
                            <span id="detailEngravingText">
                                <?php if (!empty($selectedOrder['EngravingName']) && (float) ($selectedOrder['EngravingFee'] ?? 0) > 0): ?>
                                    "<?php echo htmlspecialchars((string) $selectedOrder['EngravingName']); ?>" added for &pound;<?php echo number_format((float) $selectedOrder['EngravingFee'], 2); ?>.
                                <?php endif; ?>
                            </span>
                        </div>

                        <div class="process-block">
                            <!-- Admin processing tools live here because they act on the selected order only. -->
                            <h3>Process This Order</h3>
                            <p>Update the workflow stage as the order moves from review to shipment and delivery.</p>
                            <form class="process-form" action="admin_order_status_update.php" method="POST">
                                <input id="processOrderId" type="hidden" name="order_id" value="<?php echo (int) $selectedOrder['OrderID']; ?>">
                                <label for="processStatus">Order status</label>
                                <div class="process-controls">
                                    <select id="processStatus" name="status">
                                        <option value="pending" <?php echo $selectedOrder['Status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="delivered" <?php echo $selectedOrder['Status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $selectedOrder['Status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit">Update Status</button>
                                </div>
                            </form>
                        </div>

                        <div class="items-block">
                            <!-- The item list is the verification step before updating status or responding to queries. -->
                            <h3>Order Items</h3>
                            <div class="item-list" id="detailItemList">
                                <?php if ($selectedItems): ?>
                                    <?php foreach ($selectedItems as $item): ?>
                                        <div class="item-row">
                                            <div>
                                                <strong><?php echo htmlspecialchars((string) $item['ProductName']); ?></strong>
                                                <span><?php echo (int) $item['Quantity']; ?> x &pound;<?php echo number_format((float) $item['totalProductPrice'], 2); ?></span>
                                            </div>
                                            <div class="item-price">&pound;<?php echo number_format((float) $item['Subtotal'], 2); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="detail-empty">No item rows were found for this order.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="detail-empty">Select an order to view its customer and item breakdown here.</div>
                    <?php endif; ?>
                </aside>
            </div>
<?php render_admin_panel_end($extraScripts); ?>

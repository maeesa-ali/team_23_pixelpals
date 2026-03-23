<?php
// This page zooms in on one order so the customer can check items, totals and request a return.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../app/includes/auth.php';
require_once '../app/config/db.php';
require_once '../app/includes/admin_preview.php';

$isAdminPreview = isset($_SESSION['admin_id']) && !isset($_SESSION['user_id']);

// Customers need to be signed in for their own orders, but admins can still preview the same page.
if (!$isAdminPreview) {
    requireLogin();
}

$order = null;
$items = [];
$returnRequests = [];
$returnRequestByItem = [];
$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Stop early if the page was opened without a usable order id.
if ($orderId <= 0) {
    $_SESSION['error'] = 'Order not found.';
    header('Location: /Team23_PixelPals_Term2_Final/public/orders.php');
    exit();
}

try {
    if ($isAdminPreview) {
        // Admin preview loads by order id only because it is not tied to one customer session.
        $stmt = $db->prepare(
            'SELECT
                o.OrderID,
                o.Status,
                o.EngravingName,
                o.EngravingFee,
                COALESCE(SUM(oi.Quantity), 0) AS ItemCount,
                COALESCE(SUM(oi.Subtotal), 0) + COALESCE(o.EngravingFee, 0) AS OrderTotal
             FROM orders o
             LEFT JOIN orderitem oi ON oi.OrderID = o.OrderID
             WHERE o.OrderID = ?
             GROUP BY o.OrderID, o.Status, o.EngravingName, o.EngravingFee'
        );
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Customers can only open orders that belong to their own account.
        $stmt = $db->prepare(
            'SELECT
                o.OrderID,
                o.Status,
                o.EngravingName,
                o.EngravingFee,
                COALESCE(SUM(oi.Quantity), 0) AS ItemCount,
                COALESCE(SUM(oi.Subtotal), 0) + COALESCE(o.EngravingFee, 0) AS OrderTotal
             FROM orders o
             LEFT JOIN orderitem oi ON oi.OrderID = o.OrderID
             WHERE o.OrderID = ? AND o.UserID = ?
             GROUP BY o.OrderID, o.Status, o.EngravingName, o.EngravingFee'
        );
        $stmt->execute([$orderId, $_SESSION['user_id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$order) {
        // Treat missing and unauthorised orders the same from the customer's point of view.
        $_SESSION['error'] = 'Order not found or access denied.';
        header('Location: /Team23_PixelPals_Term2_Final/public/orders.php');
        exit();
    }

    // Load the line items once the order header has been confirmed.
    $itemStmt = $db->prepare(
        'SELECT
            oi.OrderItemID,
            oi.ProductID,
            p.ProductName,
            oi.Quantity,
            oi.totalProductPrice,
            oi.Subtotal
         FROM orderitem oi
         JOIN product p ON p.ProductID = oi.ProductID
         WHERE oi.OrderID = ?
         ORDER BY p.ProductName ASC'
    );
    $itemStmt->execute([$orderId]);
    $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

    try {
        // Return requests are loaded separately because not every order will have them.
        if ($isAdminPreview) {
            $returnStmt = $db->prepare(
                'SELECT
                    rr.ReturnRequestID,
                    rr.OrderItemID,
                    rr.Status,
                    rr.Reason,
                    rr.CreatedAt,
                    p.ProductName
                 FROM return_requests rr
                 INNER JOIN orderitem oi ON oi.OrderItemID = rr.OrderItemID
                 INNER JOIN product p ON p.ProductID = oi.ProductID
                 WHERE rr.OrderID = ?
                 ORDER BY rr.CreatedAt DESC, rr.ReturnRequestID DESC'
            );
            $returnStmt->execute([$orderId]);
        } else {
            $returnStmt = $db->prepare(
                'SELECT
                    rr.ReturnRequestID,
                    rr.OrderItemID,
                    rr.Status,
                    rr.Reason,
                    rr.CreatedAt,
                    p.ProductName
                 FROM return_requests rr
                 INNER JOIN orderitem oi ON oi.OrderItemID = rr.OrderItemID
                 INNER JOIN product p ON p.ProductID = oi.ProductID
                 WHERE rr.OrderID = ? AND rr.UserID = ?
                 ORDER BY rr.CreatedAt DESC, rr.ReturnRequestID DESC'
            );
            $returnStmt->execute([$orderId, $_SESSION['user_id']]);
        }

        $returnRequests = $returnStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($returnRequests as $returnRequest) {
            $returnRequestByItem[(int) $returnRequest['OrderItemID']] = $returnRequest;
        }
    } catch (PDOException $e) {
        // A return lookup failure should not block the rest of the order page.
        $returnRequests = [];
        $returnRequestByItem = [];
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Could not load order details.';
    header('Location: /Team23_PixelPals_Term2_Final/public/orders.php');
    exit();
}

function detail_status_class(string $status): string
{
    return match ($status) {
        'completed', 'delivered' => 'good',
        'processing' => 'active',
        'cancelled' => 'bad',
        default => 'pending',
    };
}

function return_status_class(string $status): string
{
    return match ($status) {
        'approved', 'processed' => 'good',
        'rejected' => 'bad',
        default => 'pending',
    };
}

$canRequestReturns = !$isAdminPreview && in_array((string) $order['Status'], ['completed', 'delivered'], true);
// Only offer items that do not already have a return request attached.
$availableReturnItems = array_values(array_filter(
    $items,
    static fn(array $item): bool => !isset($returnRequestByItem[(int) $item['OrderItemID']])
));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PixelPals | Order #<?php echo (int) $order['OrderID']; ?></title>
    <style>
        :root {
            --bubblegum: #ff6db2;
            --sky: #57a6ff;
            --deep-sky: #2b6fd6;
            --navy: #11254d;
            --plum: #7446c8;
            --mint: #ccff6f;
            --card: rgba(255, 255, 255, 0.88);
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
        .shell { width: min(1080px, calc(100% - 32px)); margin: 32px auto; }
        .panel {
            background: var(--card);
            backdrop-filter: blur(10px);
            border-radius: 28px;
            padding: 28px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255,255,255,0.6);
        }
        .top {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: start;
            margin-bottom: 24px;
        }
        h1, h2 { margin: 0 0 10px; }
        p { line-height: 1.6; }
        .meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin: 24px 0;
        }
        .meta-card {
            background: rgba(255,255,255,0.7);
            border-radius: 22px;
            padding: 18px;
        }
        .meta-card strong {
            display: block;
            font-size: 1.25rem;
            margin-bottom: 6px;
        }
        .status-pill {
            display: inline-flex;
            padding: 10px 12px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 800;
            text-transform: capitalize;
        }
        .status-pill.good { background: rgba(72, 187, 120, 0.14); color: #1b7a44; }
        .status-pill.active { background: rgba(87, 166, 255, 0.16); color: #1d64c9; }
        .status-pill.pending { background: rgba(255, 213, 77, 0.22); color: #8f6500; }
        .status-pill.bad { background: rgba(229, 62, 62, 0.16); color: #b43030; }
        table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 18px;
            background: rgba(255,255,255,0.7);
        }
        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid rgba(17,37,77,0.08);
        }
        th { background: rgba(17,37,77,0.08); }
        .back {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 16px;
            border-radius: 14px;
            background: rgba(17,37,77,0.08);
            font-weight: 800;
            color: inherit;
            text-decoration: none;
        }
        .returns-panel {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid rgba(17,37,77,0.08);
        }
        .engraving-card {
            margin: 20px 0 24px;
            padding: 18px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255,109,178,0.1), rgba(87,166,255,0.1));
        }
        .engraving-card strong {
            display: block;
            margin-bottom: 6px;
            font-size: 1.12rem;
        }
        .returns-panel h2,
        .returns-panel h3 {
            margin: 0 0 10px;
        }
        .returns-panel p {
            margin: 0;
        }
        .return-form {
            display: grid;
            gap: 14px;
            margin-top: 18px;
            padding: 18px;
            border-radius: 22px;
            background: rgba(255,255,255,0.7);
        }
        .return-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
        }
        .return-form select,
        .return-form textarea {
            width: 100%;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid rgba(17,37,77,0.12);
            background: rgba(255,255,255,0.92);
            font: inherit;
        }
        .return-form textarea {
            min-height: 120px;
            resize: vertical;
        }
        .return-form button {
            justify-self: start;
            padding: 12px 16px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--mint), #f5ff9a);
            color: var(--navy);
            font: inherit;
            font-weight: 800;
            cursor: pointer;
        }
        .returns-list {
            display: grid;
            gap: 14px;
            margin-top: 18px;
        }
        .return-card {
            padding: 18px;
            border-radius: 20px;
            background: rgba(255,255,255,0.7);
        }
        .return-card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 8px;
        }
        .return-card strong {
            display: block;
            margin-bottom: 4px;
        }
        .return-meta {
            opacity: 0.72;
            font-size: 0.92rem;
        }
        @media (max-width: 700px) {
            .top, .meta { grid-template-columns: 1fr; display: grid; }
            table, thead, tbody, th, td, tr { display: block; }
            thead { display: none; }
            td { padding: 10px 0; border-bottom: none; }
            tr { padding: 14px 16px; border-bottom: 1px solid rgba(17,37,77,0.08); }
            .return-card-top {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="shell">
        <?php if ($isAdminPreview): ?>
            <?php renderAdminPreviewBanner('/Team23_PixelPals_Term2_Final/public/admin/dashboard.php'); ?>
        <?php endif; ?>

        <!-- This page is more focused, so it jumps straight into the selected order details. -->
        <section class="panel">
            <!-- The top row keeps the order identity and current status together. -->
            <div class="top">
                <div>
                    <h1>Order #<?php echo (int) $order['OrderID']; ?></h1>
                    <p>Here is the item-by-item breakdown for this PixelPals order.</p>
                </div>
                <div class="status-pill <?php echo detail_status_class($order['Status']); ?>">
                    <?php echo htmlspecialchars($order['Status']); ?>
                </div>
            </div>

            <!-- These meta cards summarise the order before the detailed item table starts. -->
            <div class="meta">
                <div class="meta-card">
                    <strong><?php echo (int) $order['ItemCount']; ?> item<?php echo (int) $order['ItemCount'] === 1 ? '' : 's'; ?></strong>
                    Products in this order
                </div>
                <div class="meta-card">
                    <strong>&pound;<?php echo number_format((float) $order['OrderTotal'], 2); ?></strong>
                    Total order value
                </div>
                <div class="meta-card">
                    <strong><?php echo htmlspecialchars(ucfirst($order['Status'])); ?></strong>
                    Current order status
                </div>
            </div>

            <?php if (!empty($order['EngravingName']) && (float) ($order['EngravingFee'] ?? 0) > 0): ?>
                <div class="engraving-card">
                    <strong>Name engraving added</strong>
                    "<?php echo htmlspecialchars((string) $order['EngravingName']); ?>" was added to this order for &pound;<?php echo number_format((float) $order['EngravingFee'], 2); ?>.
                </div>
            <?php endif; ?>

            <!-- The item table is the factual line-by-line record of what was purchased. -->
            <h2>Items</h2>
            <?php if ($items): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price each</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['ProductName']); ?></td>
                                <td><?php echo (int) $item['Quantity']; ?></td>
                                <td>&pound;<?php echo number_format((float) $item['totalProductPrice'], 2); ?></td>
                                <td>&pound;<?php echo number_format((float) $item['Subtotal'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No order items were found for this order.</p>
            <?php endif; ?>

            <section class="returns-panel">
                <!-- Returns sit directly under the order because they only make sense in the context of these items. -->
                <h2>Returns</h2>
                <p>
                    <?php if ($isAdminPreview): ?>
                        Return requests submitted for this order appear below.
                    <?php elseif ($canRequestReturns): ?>
                        Need to return something? Choose an item from this order and tell us why.
                    <?php else: ?>
                        Returns can be requested once an order has been completed or delivered.
                    <?php endif; ?>
                </p>

                <?php if (!$isAdminPreview && $canRequestReturns && $availableReturnItems): ?>
                    <!-- Customers can raise a new return here once the order has reached a finished state. -->
                    <form class="return-form" action="return_request.php" method="POST">
                        <input type="hidden" name="order_id" value="<?php echo (int) $order['OrderID']; ?>">

                        <div>
                            <label for="order_item_id">Item to return</label>
                            <select id="order_item_id" name="order_item_id" required>
                                <option value="">Choose an item</option>
                                <?php foreach ($availableReturnItems as $item): ?>
                                    <option value="<?php echo (int) $item['OrderItemID']; ?>">
                                        <?php echo htmlspecialchars($item['ProductName']); ?> x <?php echo (int) $item['Quantity']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="reason">Reason for return</label>
                            <textarea id="reason" name="reason" required placeholder="Tell us why you would like to return this item."></textarea>
                        </div>

                        <button type="submit">Request Return</button>
                    </form>
                <?php elseif (!$isAdminPreview && $canRequestReturns): ?>
                    <div class="return-card" style="margin-top: 18px;">
                        <strong>All items from this order already have return requests.</strong>
                    </div>
                <?php endif; ?>

                <?php if ($returnRequests): ?>
                    <!-- Existing requests stay visible so the customer can see what has already been raised. -->
                    <div class="returns-list">
                        <?php foreach ($returnRequests as $returnRequest): ?>
                            <div class="return-card">
                                <div class="return-card-top">
                                    <div>
                                        <strong><?php echo htmlspecialchars($returnRequest['ProductName']); ?></strong>
                                        <div class="return-meta">Requested on <?php echo htmlspecialchars(date('j M Y', strtotime((string) $returnRequest['CreatedAt']))); ?></div>
                                    </div>
                                    <div class="status-pill <?php echo return_status_class((string) $returnRequest['Status']); ?>">
                                        <?php echo htmlspecialchars($returnRequest['Status']); ?>
                                    </div>
                                </div>
                                <p><?php echo nl2br(htmlspecialchars($returnRequest['Reason'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <a class="back" href="<?php echo $isAdminPreview ? 'admin/dashboard.php' : 'orders.php'; ?>">
                <?php echo $isAdminPreview ? 'Back to Admin Dashboard' : 'Back to Orders'; ?>
            </a>
        </section>
    </div>
</body>
</html>

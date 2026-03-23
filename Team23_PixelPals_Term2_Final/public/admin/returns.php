<?php
// This page is the admin returns queue, built to keep outstanding requests easy to process.
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../public/login.php');
    exit();
}

require_once '../../app/config/db.php';
require_once '../../app/includes/admin_panel_page.php';

// This page is just a returns queue, so all of the data here is shaped around one list of requests.
$returnRequests = [];
$error = null;
$flashSuccess = $_SESSION['success'] ?? null;
$flashError = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

$statusCounts = [
    'requested' => 0,
    'approved' => 0,
    'rejected' => 0,
    'processed' => 0,
];

try {
    // Load each return with the linked order item, product and customer details the admin needs.
    $stmt = $db->query(
        'SELECT
            rr.ReturnRequestID,
            rr.OrderID,
            rr.OrderItemID,
            rr.UserID,
            rr.Status,
            rr.Reason,
            rr.CreatedAt,
            oi.Quantity,
            oi.Subtotal,
            p.ProductName,
            u.Username,
            u.FirstName,
            u.LastName,
            u.Email
         FROM return_requests rr
         INNER JOIN orderitem oi ON oi.OrderItemID = rr.OrderItemID
         INNER JOIN product p ON p.ProductID = oi.ProductID
         INNER JOIN users u ON u.UserID = rr.UserID
         ORDER BY
            CASE rr.Status
                WHEN "requested" THEN 0
                WHEN "approved" THEN 1
                WHEN "rejected" THEN 2
                WHEN "processed" THEN 3
                ELSE 4
            END,
            rr.CreatedAt DESC,
            rr.ReturnRequestID DESC'
    );
    $returnRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($returnRequests as $returnRequest) {
        $status = strtolower((string) $returnRequest['Status']);
        if (isset($statusCounts[$status])) {
            $statusCounts[$status]++;
        }
    }
} catch (PDOException $e) {
    $error = 'Return requests could not be loaded right now.';
}

// Small helpers keep the display name and badge styling logic out of the main template.
function admin_return_queue_name(array $returnRequest): string
{
    $name = trim(($returnRequest['FirstName'] ?? '') . ' ' . ($returnRequest['LastName'] ?? ''));
    return $name !== '' ? $name : (string) ($returnRequest['Username'] ?? 'Customer');
}

function admin_return_queue_status_class(string $status): string
{
    return match (strtolower($status)) {
        'approved', 'processed' => 'good',
        'rejected' => 'bad',
        default => 'pending',
    };
}

$extraStyles = <<<'CSS'
        .section-actions {
            justify-content: flex-end;
        }
        .summary-meta {
            display: flex;
            align-items: center;
        }
        .return-count {
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
            margin-bottom: 18px;
        }
        .notice.success {
            background: rgba(204, 255, 111, 0.36);
            color: #375100;
            border-color: rgba(150, 190, 45, 0.24);
        }
        .return-list {
            display: grid;
            gap: 16px;
        }
        .return-card {
            padding: 22px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.74);
            border: 1px solid var(--outline);
            box-shadow: 0 12px 24px rgba(17, 37, 77, 0.07);
        }
        .return-top {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        .return-main strong {
            display: block;
            margin-bottom: 6px;
            font-size: 1.16rem;
        }
        .return-main p {
            margin: 0;
            line-height: 1.65;
            opacity: 0.84;
        }
        .return-meta {
            display: flex;
            gap: 10px 18px;
            flex-wrap: wrap;
            margin-top: 12px;
            font-size: 0.92rem;
            opacity: 0.78;
        }
        .return-side {
            display: grid;
            gap: 10px;
            justify-items: end;
            min-width: 260px;
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
        .return-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
        }
        .return-form select {
            flex: 1 1 160px;
            min-height: 42px;
            padding: 10px 12px;
            border-radius: 14px;
            border: 1px solid var(--outline);
            background: rgba(255,255,255,0.92);
            font: inherit;
        }
        .return-form button {
            min-height: 42px;
            padding: 10px 14px;
            border: none;
            border-radius: 14px;
            background: rgba(17, 37, 77, 0.08);
            color: var(--navy);
            font: inherit;
            font-weight: 800;
            cursor: pointer;
        }
        .return-reason {
            padding: 16px 18px;
            border-radius: 18px;
            background: rgba(17, 37, 77, 0.05);
            line-height: 1.7;
        }
        .empty-state {
            padding: 24px;
            border-radius: 20px;
            background: rgba(17, 37, 77, 0.05);
            text-align: center;
            opacity: 0.82;
        }
        @media (max-width: 900px) {
            .status-strip {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .return-top {
                flex-direction: column;
            }
            .return-side {
                width: 100%;
                justify-items: stretch;
                min-width: 0;
            }
            .return-form {
                justify-content: stretch;
            }
            .return-form select,
            .return-form button {
                width: 100%;
            }
        }
CSS;

render_admin_panel_start([
    'title' => 'Returns | PixelPals Admin',
    'brand_subtitle' => 'Review and process customer return requests',
    'shell_width' => '1180px',
    'extra_styles' => $extraStyles,
]);
?>
            <!-- Return requests are grouped here so admins can work through them in one queue. -->
            <div class="section-header">
                <div>
                    <h1>Returns</h1>
                    <p>Review customer return reasons, check the linked order details and move each request through the right status.</p>
                </div>

                <div class="section-actions">
                    <div class="summary-meta">
                        <span class="return-count"><?php echo count($returnRequests); ?> return requests</span>
                    </div>
                    <div class="action-buttons">
                        <a class="ghost" href="orders.php">View Orders</a>
                        <a class="primary" href="dashboard.php">Back to Dashboard</a>
                    </div>
                </div>
            </div>

            <!-- This strip gives a quick read on how many requests are sitting in each stage. -->
            <div class="status-strip">
                <div class="status-chip">
                    <strong><?php echo $statusCounts['requested']; ?></strong>
                    <span>Requested</span>
                </div>
                <div class="status-chip">
                    <strong><?php echo $statusCounts['approved']; ?></strong>
                    <span>Approved</span>
                </div>
                <div class="status-chip">
                    <strong><?php echo $statusCounts['rejected']; ?></strong>
                    <span>Rejected</span>
                </div>
                <div class="status-chip">
                    <strong><?php echo $statusCounts['processed']; ?></strong>
                    <span>Processed</span>
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

            <div class="return-list">
                <?php if (empty($returnRequests)): ?>
                    <div class="empty-state">No return requests have been submitted yet.</div>
                <?php else: ?>
                    <?php foreach ($returnRequests as $returnRequest): ?>
                        <?php $displayName = admin_return_queue_name($returnRequest); ?>
                        <!-- Each card holds the context, reason and status controls for one return request. -->
                        <article class="return-card">
                            <div class="return-top">
                                <div class="return-main">
                                    <strong><?php echo htmlspecialchars((string) $returnRequest['ProductName']); ?></strong>
                                    <p><?php echo htmlspecialchars($displayName); ?> requested a return for order #<?php echo (int) $returnRequest['OrderID']; ?>.</p>
                                    <div class="return-meta">
                                        <span>@<?php echo htmlspecialchars((string) $returnRequest['Username']); ?></span>
                                        <span><?php echo htmlspecialchars((string) $returnRequest['Email']); ?></span>
                                        <span><?php echo (int) $returnRequest['Quantity']; ?> item(s)</span>
                                        <span>&pound;<?php echo number_format((float) $returnRequest['Subtotal'], 2); ?></span>
                                        <span><?php echo htmlspecialchars(date('j M Y', strtotime((string) $returnRequest['CreatedAt']))); ?></span>
                                    </div>
                                </div>

                                <div class="return-side">
                                    <!-- Status updates happen inline here so admins can work through the queue quickly. -->
                                    <span class="status-pill <?php echo admin_return_queue_status_class((string) $returnRequest['Status']); ?>">
                                        <?php echo htmlspecialchars((string) $returnRequest['Status']); ?>
                                    </span>

                                    <form class="return-form" action="admin_order_status_update.php" method="POST">
                                        <input type="hidden" name="return_request_id" value="<?php echo (int) $returnRequest['ReturnRequestID']; ?>">
                                        <input type="hidden" name="order_id" value="<?php echo (int) $returnRequest['OrderID']; ?>">
                                        <input type="hidden" name="return_to" value="returns">
                                        <select name="status">
                                            <option value="requested" <?php echo $returnRequest['Status'] === 'requested' ? 'selected' : ''; ?>>Requested</option>
                                            <option value="approved" <?php echo $returnRequest['Status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                            <option value="rejected" <?php echo $returnRequest['Status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            <option value="processed" <?php echo $returnRequest['Status'] === 'processed' ? 'selected' : ''; ?>>Processed</option>
                                        </select>
                                        <button type="submit">Update Return</button>
                                    </form>
                                </div>
                            </div>

                            <div class="return-reason">
                                <?php echo nl2br(htmlspecialchars((string) $returnRequest['Reason'])); ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
<?php render_admin_panel_end(); ?>

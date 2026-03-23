<?php
// This page lists contact form messages and keeps one message selected for a closer read.
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../public/login.php');
    exit();
}

require_once '../../app/config/db.php';
require_once '../../app/includes/admin_panel_page.php';

$messages = [];
$error = null;
$totalMessages = 0;
$latestReceived = null;
$selectedMessage = null;
$selectedMessageId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

try {
    // Load the full message list once so both the table and the selected detail panel can reuse it.
    $stmt = $db->query('SELECT * FROM contact_messages ORDER BY CreatedAt DESC');
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalMessages = count($messages);

    if (!empty($messages) && !empty($messages[0]['CreatedAt'])) {
        $latestReceived = $messages[0]['CreatedAt'];
    }

    if ($selectedMessageId > 0) {
        foreach ($messages as $message) {
            if ((int) $message['MessageID'] === $selectedMessageId) {
                $selectedMessage = $message;
                break;
            }
        }
    }

    if (!$selectedMessage && !empty($messages)) {
        $selectedMessage = $messages[0];
    }
} catch (PDOException $e) {
    $error = 'Messages could not be loaded right now.';
}

$extraStyles = <<<'CSS'
        .notice {
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(255, 109, 178, 0.12);
            color: #8f1f53;
            border: 1px solid rgba(255, 109, 178, 0.2);
        }
        .messages-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(320px, 0.9fr);
            gap: 20px;
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
        th,
        td {
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
        tbody tr.selected-row {
            background: rgba(204, 255, 111, 0.26);
        }
        .name-cell {
            display: grid;
            gap: 4px;
        }
        .name-cell strong {
            font-size: 1rem;
        }
        .name-cell span {
            font-size: 0.92rem;
            opacity: 0.74;
        }
        .subject-pill {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(17, 37, 77, 0.08);
            font-weight: 800;
            font-size: 0.88rem;
        }
        .date-text {
            white-space: nowrap;
            font-weight: 700;
        }
        .empty-state {
            padding: 28px;
            text-align: center;
            opacity: 0.82;
        }
        .detail-card {
            padding: 24px;
            border-radius: 24px;
            border: 1px solid var(--outline);
            background: rgba(255, 255, 255, 0.72);
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
            margin-bottom: 16px;
        }
        .detail-grid {
            display: grid;
            gap: 16px;
            margin-bottom: 18px;
        }
        .detail-item {
            display: grid;
            gap: 6px;
        }
        .detail-item span {
            font-size: 0.8rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            opacity: 0.66;
            font-weight: 800;
        }
        .detail-item strong,
        .detail-item p {
            margin: 0;
            line-height: 1.7;
        }
        .detail-item p {
            white-space: pre-line;
        }
        .detail-empty {
            padding: 18px 0;
            opacity: 0.8;
        }
        .view-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 12px;
            background: rgba(17, 37, 77, 0.08);
            font-weight: 800;
            white-space: nowrap;
            border: none;
            color: var(--navy);
            cursor: pointer;
            font: inherit;
        }
        .view-link.active {
            background: linear-gradient(135deg, var(--mint), #f5ff9a);
        }
        @media (max-width: 760px) {
            .messages-layout {
                grid-template-columns: 1fr;
            }
        }
CSS;

$extraScripts = <<<'HTML'
    <script src="../js/admin_messages.js?v=1" defer></script>
HTML;

render_admin_panel_start([
    'title' => 'Customer Messages | PixelPals Admin',
    'brand_subtitle' => 'Customer enquiries and support messages',
    'shell_width' => '1240px',
    'extra_styles' => $extraStyles,
]);
?>
            <!-- The section header keeps the page summary and admin shortcuts in one place. -->
            <div class="section-header">
                <div>
                    <h1>Customer Messages</h1>
                    <p>Review the latest contact form enquiries from customers and keep support conversations easy to scan.</p>
                </div>

                <div class="section-actions">
                    <span class="meta"><?php echo $totalMessages; ?> messages</span>
                    <?php if ($latestReceived): ?>
                        <span class="meta">Latest: <?php echo htmlspecialchars(date('d M Y, H:i', strtotime($latestReceived))); ?></span>
                    <?php endif; ?>
                    <a class="ghost" href="../contact.php">Open Contact Page</a>
                    <a class="primary" href="dashboard.php">Back to Dashboard</a>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="notice"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- The layout is split between the message table and the selected detail panel. -->
            <div class="messages-layout">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Date Received</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Open</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($messages)): ?>
                                <tr>
                                    <td colspan="5" class="empty-state">No enquiry messages have come through yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($messages as $msg): ?>
                                    <!-- Each row carries its own message data so the JS can swap the detail panel quickly. -->
                                    <?php $isSelected = $selectedMessage && (int) $selectedMessage['MessageID'] === (int) $msg['MessageID']; ?>
                                    <tr
                                        class="<?php echo $isSelected ? 'selected-row' : ''; ?>"
                                        data-message-id="<?php echo (int) $msg['MessageID']; ?>"
                                        data-message-name="<?php echo htmlspecialchars($msg['Name'], ENT_QUOTES); ?>"
                                        data-message-email="<?php echo htmlspecialchars($msg['Email'], ENT_QUOTES); ?>"
                                        data-message-subject="<?php echo htmlspecialchars($msg['Subject'] ?: 'General enquiry', ENT_QUOTES); ?>"
                                        data-message-created="<?php echo htmlspecialchars(date('d M Y, H:i', strtotime($msg['CreatedAt'])), ENT_QUOTES); ?>"
                                        data-message-body="<?php echo htmlspecialchars($msg['Message'], ENT_QUOTES); ?>"
                                    >
                                        <td class="date-text"><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($msg['CreatedAt']))); ?></td>
                                        <td>
                                            <div class="name-cell">
                                                <strong><?php echo htmlspecialchars($msg['Name']); ?></strong>
                                                <span>Contact enquiry</span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($msg['Email']); ?></td>
                                        <td>
                                            <span class="subject-pill"><?php echo htmlspecialchars($msg['Subject'] ?: 'General enquiry'); ?></span>
                                        </td>
                                        <td>
                                            <button type="button" class="view-link <?php echo $isSelected ? 'active' : ''; ?>">View</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- The right-hand panel shows whichever message is currently selected. -->
                <aside class="detail-card" id="messageDetailPanel">
                    <h2>Message Detail</h2>

                    <?php if ($selectedMessage): ?>
                        <div class="detail-pill" id="detailMessageId">Message #<?php echo (int) $selectedMessage['MessageID']; ?></div>

                        <div class="detail-grid">
                            <div class="detail-item">
                                <span>Received</span>
                                <strong id="detailCreated"><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($selectedMessage['CreatedAt']))); ?></strong>
                            </div>

                            <div class="detail-item">
                                <span>From</span>
                                <strong id="detailName"><?php echo htmlspecialchars($selectedMessage['Name']); ?></strong>
                            </div>

                            <div class="detail-item">
                                <span>Email</span>
                                <strong id="detailEmail"><?php echo htmlspecialchars($selectedMessage['Email']); ?></strong>
                            </div>

                            <div class="detail-item">
                                <span>Subject</span>
                                <strong id="detailSubject"><?php echo htmlspecialchars($selectedMessage['Subject'] ?: 'General enquiry'); ?></strong>
                            </div>

                            <div class="detail-item">
                                <span>Message</span>
                                <p id="detailBody"><?php echo htmlspecialchars($selectedMessage['Message']); ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="detail-empty">Select a message to view the full enquiry here.</p>
                    <?php endif; ?>
                </aside>
            </div>
<?php render_admin_panel_end($extraScripts); ?>


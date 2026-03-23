<?php
// This page is the admin customer list, with enough detail to review, edit or remove accounts.
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../public/login.php');
    exit();
}

require_once '../../app/config/db.php';
require_once '../../app/includes/flash.php';
require_once '../../app/includes/admin_panel_page.php';

$customers = [];
$totalCustomers = 0;

try {
    // Pull the customer list once here so the table can be rendered without extra queries in the loop.
    $sql = "
        SELECT
            UserID,
            Username,
            FirstName,
            LastName,
            Email,
            DateOfBirth,
            TIMESTAMPDIFF(YEAR, DateOfBirth, CURDATE()) AS CalculatedAge
        FROM users
        ORDER BY UserID ASC
    ";

    $stmt = $db->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalCustomers = count($customers);
} catch (PDOException $e) {
    $_SESSION['error'] = 'Unable to load customers right now.';
}

$extraStyles = <<<'CSS'
        .list-card {
            overflow: hidden;
        }
        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
        }
        .list-header h2 {
            margin: 0;
            font-size: 1.7rem;
        }
        .list-header p {
            margin: 6px 0 0;
            line-height: 1.7;
            opacity: 0.84;
        }
        .count-pill {
            padding: 0;
            border-radius: 0;
            background: transparent;
            color: rgba(17, 37, 77, 0.72);
            font-weight: 700;
        }
        .table-wrap {
            overflow-x: auto;
            border-radius: 24px;
            border: 1px solid var(--outline);
            background: rgba(255, 255, 255, 0.6);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 860px;
        }
        th,
        td {
            padding: 16px 18px;
            text-align: left;
            border-bottom: 1px solid rgba(17, 37, 77, 0.08);
            vertical-align: middle;
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
        .customer-name {
            display: grid;
            gap: 4px;
        }
        .customer-name strong {
            font-size: 1rem;
        }
        .customer-name span {
            opacity: 0.76;
            font-size: 0.92rem;
        }
        .age-pill,
        .id-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 0.84rem;
            font-weight: 800;
        }
        .age-pill {
            background: rgba(87, 166, 255, 0.14);
        }
        .id-pill {
            background: rgba(17, 37, 77, 0.08);
        }
        .row-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .row-actions a {
            padding: 10px 14px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.92rem;
        }
        .row-actions .edit {
            background: rgba(17, 37, 77, 0.08);
        }
        .row-actions .delete {
            background: rgba(255, 109, 178, 0.16);
            color: #8f1f53;
        }
        .empty-state {
            padding: 24px;
            text-align: center;
            opacity: 0.82;
        }
        @media (max-width: 700px) {
            .list-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
CSS;

render_admin_panel_start([
    'title' => 'Customer Accounts | PixelPals Admin',
    'brand_subtitle' => 'Customer account management and admin controls',
    'shell_width' => '1240px',
    'extra_styles' => $extraStyles,
]);
?>
            <div class="list-header">
                <div>
                    <h2>Customer Directory</h2>
                    <p>Every customer account currently available for admin review.</p>
                </div>

                <div class="section-actions">
                    <span class="count-pill"><?php echo $totalCustomers; ?> accounts</span>
                    <a class="ghost" href="../index.php">View Storefront</a>
                    <a class="primary" href="dashboard.php">Back to Dashboard</a>
                </div>
            </div>

            <div class="flash-wrap">
                <?php display_flash_messages(); ?>
            </div>

            <!-- The customer table is the main working area on this page. -->
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Date of Birth</th>
                            <th>Age</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($customers)): ?>
                            <?php foreach ($customers as $row): ?>
                                <!-- Each row shows the account details plus the edit/delete actions. -->
                                <tr>
                                    <td><span class="id-pill">#<?php echo (int) $row['UserID']; ?></span></td>
                                    <td>
                                        <div class="customer-name">
                                            <strong><?php echo htmlspecialchars(trim(($row['FirstName'] ?? '') . ' ' . ($row['LastName'] ?? '')) ?: $row['Username']); ?></strong>
                                            <span>@<?php echo htmlspecialchars($row['Username']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                    <td><?php echo !empty($row['DateOfBirth']) ? htmlspecialchars($row['DateOfBirth']) : 'Not set'; ?></td>
                                    <td>
                                        <?php if ($row['CalculatedAge'] !== null): ?>
                                            <span class="age-pill"><?php echo (int) $row['CalculatedAge']; ?> years</span>
                                        <?php else: ?>
                                            <span class="age-pill">Unknown</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            <a class="edit" href="customer_edit.php?id=<?php echo (int) $row['UserID']; ?>">Edit</a>
                                            <a class="delete" href="admin_customer_delete.php?id=<?php echo (int) $row['UserID']; ?>" data-confirm="Delete this customer account?">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="empty-state">No customers were found in the database yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
<?php render_admin_panel_end('<script src="../js/confirm_actions.js?v=1" defer></script>'); ?>



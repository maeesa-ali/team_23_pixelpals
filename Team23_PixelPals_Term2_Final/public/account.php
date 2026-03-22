<?php
// This page loads the current account details and switches between customer/admin data when needed.
require_once '../app/includes/auth.php';
require_once '../app/config/db.php';
require_once '../app/includes/flash.php';
require_once '../app/includes/admin_preview.php';
requireAuthenticatedSession();

$user = null;
$isAdminAccount = isset($_SESSION['admin_id']) && !isset($_SESSION['user_id']);
$accountLabel = $isAdminAccount ? 'Admin Account' : 'My Account';

try 
{
    // Pull the right table depending on who is signed in so the form can stay shared.
    if ($isAdminAccount) 
        {
        $stmt = $db->prepare('SELECT Username, Email, FirstName, LastName, NULL AS DateOfBirth FROM admin WHERE AdminID = ?');
        $stmt->execute([$_SESSION['admin_id']]);
    } 
    else 
    {
        $stmt = $db->prepare('SELECT Username, Email, FirstName, LastName, DateOfBirth FROM users WHERE UserID = ?');
        $stmt->execute([$_SESSION['user_id']]);
    }
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} 
catch (PDOException $e) 
{
    $_SESSION['error'] = 'Could not load your account details.';
}

if (!$user) 
{
    $_SESSION['error'] = 'Your account could not be found.';
    header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="./js/account.js?v=2" defer></script>
    <title>PixelPals | <?php echo htmlspecialchars($accountLabel); ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        :root 
        {
            --bubblegum: #ff6db2;
            --sky: #57a6ff;
            --deep-sky: #2b6fd6;
            --navy: #11254d;
            --plum: #7446c8;
            --mint: #ccff6f;
            --sun: #ffd54d;
            --card: rgba(255, 255, 255, 0.88);
            --outline: rgba(17, 37, 77, 0.08);
            --shadow: 0 20px 60px rgba(17, 37, 77, 0.18);
        }

        * { box-sizing: border-box; }

        body 
        {
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

        body::before 
        {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: 0.3;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.12) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.12) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        button, .panel 
        {
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        button:hover, .panel:hover 
        {
            transform: translateY(-2px);
        }

        .flash-wrap 
        {
            margin-bottom: 16px;
        }

        .panel, .danger-zone 
        {
            background: var(--card);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.6);
            border-radius: 30px;
            box-shadow: var(--shadow);
        }

        .panel p, .danger-zone p 
        {
            line-height: 1.7;
            margin: 0;
            opacity: 0.9;
        }

        .account-layout 
        {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .panel 
        {
            padding: 28px;
            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(247,250,255,0.82));
        }

        .panel-header 
        {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .panel h2 
        {
            margin: 0 0 10px;
            font-size: 1.9rem;
        }

        .actions 
        {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        button 
        {
            padding: 12px 16px;
            border: none;
            border-radius: 16px;
            cursor: pointer;
            font: inherit;
            font-weight: 800;
            background: linear-gradient(135deg, var(--mint), #f5ff9a);
            box-shadow: 0 14px 28px rgba(17,37,77,0.12);
        }

        button.ghost 
        {
            background: rgba(17,37,77,0.08);
            box-shadow: none;
        }

        .btn-delete 
        {
            background: linear-gradient(135deg, #ff6b7d, #ff3d57);
            color: #fff;
        }

        .message 
        {
            min-height: 24px;
            margin: 12px 0 0;
            font-weight: 700;
        }

        .grid 
        {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-top: 18px;
        }

        .field 
        {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .field.fullWidth 
        {
            grid-column: 1 / -1;
        }

        label 
        {
            font-weight: 800;
            font-size: 0.95rem;
        }

        input 
        {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid rgba(17,37,77,0.12);
            border-radius: 16px;
            font: inherit;
            background: rgba(255,255,255,0.92);
            color: #334;
        }

        input:disabled 
        {
            opacity: 0.8;
            cursor: not-allowed;
        }

        .danger-zone 
        {
            padding: 26px 28px;
            border: 2px solid rgba(255, 61, 87, 0.25);
            margin-bottom: 36px;
            background: linear-gradient(180deg, rgba(255,255,255,0.94), rgba(255,235,239,0.82));
        }

        .danger-zone h3 
        {
            margin: 0 0 10px;
            font-size: 1.6rem;
        }

        @media (max-width: 920px) 
        {
            .account-layout 
            {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) 
        {
            .grid { grid-template-columns: 1fr; }
            .panel, .danger-zone { padding: 22px; }
        }
    </style>
</head>
<body>
    <div class="site-shell">
        <!-- The top bar changes slightly for admins so they can jump back to the dashboard easily. -->
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
                <?php if ($isAdminAccount): ?>
                    <a class="chip-link" href="admin/dashboard.php">Dashboard</a>
                    <a class="primary-link" href="logout.php">Log Out</a>
                <?php else: ?>
                    <a class="chip-link" href="basket.php">Basket</a>
                    <a class="primary-link" href="account.php">My Account</a>
                <?php endif; ?>
            </div>
        </header>

        <?php if (!$isAdminAccount): ?>
            <nav class="bottomNav">
                <div class="nav-links">
                    <a href="index.php">Home</a>
                    <a href="products.php">Products</a>
                    <a href="about.php">About Us</a>
                    <a href="contact.php">Contact Us</a>
                    <a href="orders.php">Orders</a>
                    <a href="logout.php">Log Out</a>
                </div>
            </nav>
        <?php endif; ?>

        <!-- Flash messages sit near the forms so save and password feedback is easy to spot. -->
        <div class="flash-wrap">
            <?php display_flash_messages(); ?>
        </div>

        <!-- Personal details and password controls are split into two separate panels. -->
        <section class="account-layout">
            <section class="panel">
                <!-- This form handles the editable profile fields. -->
                <div class="panel-header">
                    <div>
                        <h2>Personal Details</h2>
                        <p id="accountMessage" class="message">Your personal details</p>
                    </div>
                    <div class="actions">
                        <button type="button" id="editAccountBtn">Edit</button>
                        <button type="submit" id="saveAccountBtn" form="accountForm" hidden>Save Changes</button>
                        <button type="button" id="cancelAccountBtn" class="ghost" hidden>Cancel</button>
                    </div>
                </div>

                <form id="accountForm" method="POST" action="account_update.php">
                    <!-- These are the profile fields that can be unlocked by the edit button. -->
                    <div class="grid">
                        <div class="field">
                            <label for="username">Username</label>
                            <input id="username" name="username" type="text" value="<?php echo htmlspecialchars($user['Username'] ?? ''); ?>" required disabled>
                        </div>

                        <div class="field">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($user['Email'] ?? ''); ?>" required disabled>
                        </div>

                        <div class="field">
                            <label for="first_Name">First Name</label>
                            <input id="first_Name" name="first_name" type="text" value="<?php echo htmlspecialchars($user['FirstName'] ?? ''); ?>" required disabled>
                        </div>

                        <div class="field">
                            <label for="last_Name">Last Name</label>
                            <input id="last_Name" name="last_name" type="text" value="<?php echo htmlspecialchars($user['LastName'] ?? ''); ?>" required disabled>
                        </div>

                        <?php if (!$isAdminAccount): ?>
                            <div class="field fullWidth">
                                <label for="dob">Date of Birth</label>
                                <input id="dob" name="dob" type="date" value="<?php echo htmlspecialchars($user['DateOfBirth'] ?? ''); ?>" disabled>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </section>

            <section class="panel">
                <!-- Password changes stay separate from profile edits so the two actions do not get mixed together. -->
                <div class="panel-header">
                    <div>
                        <h2>Security</h2>
                        <p class="message">Change your password whenever you need to.</p>
                    </div>
                </div>

                <form method="POST" action="change_password_post.php" id="passwordForm">
                    <div class="grid">
                        <div class="field">
                            <label for="old_Password">Current Password</label>
                            <input id="old_Password" name="old_password" type="password">
                        </div>

                        <div class="field">
                            <label for="new_Password">New Password</label>
                            <input id="new_Password" name="new_password" type="password">
                        </div>

                        <div class="field fullWidth">
                            <label for="confirm_Password">Confirm New Password</label>
                            <input id="confirm_Password" name="confirm_password" type="password">
                        </div>
                    </div>

                    <div class="actions" style="margin-top: 18px;">
                        <button type="submit">Update Password</button>
                    </div>
                </form>
            </section>
        </section>

        <?php if (!$isAdminAccount): ?>
            <!-- Account deletion is isolated here because it is a much riskier action than a normal edit. -->
            <section class="danger-zone">
                <h3>Danger Zone</h3>
                <p>
                    Deleting your account permanently removes your access and cannot be undone. Please make sure you really want to continue.
                </p>
                <form action="account_delete.php" method="POST" data-confirm="WARNING: This will permanently delete your account and all your data. Are you absolutely sure?" style="margin-top: 18px;">
                    <button type="submit" class="btn-delete">Delete My Account</button>
                </form>
            </section>
        <?php endif; ?>
    </div>
    <script src="./js/confirm_actions.js?v=1" defer></script>
</body>
</html>



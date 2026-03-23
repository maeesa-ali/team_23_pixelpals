<?php

function render_admin_panel_start(array $config): void
{
    // This helper provides the shared admin shell so the dashboard/list pages all feel like one system.
    $title = $config['title'] ?? 'Admin Panel';
    $brandSubtitle = $config['brand_subtitle'] ?? '';
    $shellWidth = $config['shell_width'] ?? '1240px';
    $extraStyles = $config['extra_styles'] ?? '';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <style>
        :root {
            --navy: #11254d;
            --mint: #ccff6f;
            --card: rgba(255, 255, 255, 0.92);
            --outline: rgba(17, 37, 77, 0.1);
            --shadow: 0 20px 60px rgba(17, 37, 77, 0.18);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Verdana", "Trebuchet MS", "Segoe UI", sans-serif;
            color: var(--navy);
            background:
                radial-gradient(circle at 10% 10%, rgba(255, 109, 178, 0.2), transparent 18%),
                radial-gradient(circle at 88% 12%, rgba(255, 213, 77, 0.24), transparent 16%),
                linear-gradient(180deg, #81d4ff 0%, #b6d4ff 36%, #efe7ff 100%);
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: 0.26;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.12) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.12) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .shell {
            width: min(<?php echo htmlspecialchars($shellWidth); ?>, calc(100% - 32px));
            margin: 24px auto 36px;
            position: relative;
            z-index: 1;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 22px;
            padding: 18px 22px;
            border-radius: 28px;
            background: rgba(17, 37, 77, 0.9);
            color: #fff;
            box-shadow: var(--shadow);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand img {
            width: 58px;
            height: 58px;
            object-fit: contain;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.12);
            padding: 6px;
        }

        .brand strong {
            display: block;
            font-size: 1.2rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .brand span {
            opacity: 0.8;
            font-size: 0.92rem;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .admin-name {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 800;
            opacity: 0.82;
        }

        .admin-name::before {
            content: "";
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--mint);
            box-shadow: 0 0 0 4px rgba(204, 255, 111, 0.16);
        }

        .top-actions a {
            padding: 12px 16px;
            border-radius: 999px;
            font-weight: 800;
        }

        .top-actions .ghost {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
        }

        .top-actions .primary {
            background: linear-gradient(135deg, var(--mint), #f5ff9a);
            color: var(--navy);
        }

        .card {
            background: var(--card);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 30px;
            box-shadow: var(--shadow);
        }

        .page-card {
            padding: 28px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            margin-bottom: 18px;
        }

        .section-header h1 {
            margin: 0;
            font-size: 2rem;
        }

        .section-header p {
            margin: 8px 0 0;
            line-height: 1.7;
            opacity: 0.84;
        }

        .section-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .section-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 16px;
            border-radius: 999px;
            font-weight: 800;
        }

        .section-actions .ghost {
            background: rgba(17, 37, 77, 0.08);
        }

        .section-actions .primary {
            background: linear-gradient(135deg, var(--mint), #f5ff9a);
        }

        .meta {
            color: rgba(17, 37, 77, 0.72);
            font-weight: 700;
        }

        .flash-wrap,
        .notice {
            margin-bottom: 18px;
        }

        @media (max-width: 760px) {
            .shell {
                width: min(100% - 20px, <?php echo htmlspecialchars($shellWidth); ?>);
            }

            .topbar,
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-card {
                padding: 22px;
            }
        }

<?php
// Page-specific admin CSS gets injected here so each screen only adds what is unique to that page.
echo $extraStyles;
?>
    </style>
</head>
<body>
    <div class="shell">
        <!-- Shared admin top bar: brand on the left, quick actions on the right. -->
        <header class="topbar">
            <div class="brand">
                <img src="../assets/img/logo.png" alt="PixelPals logo">
                <div>
                    <strong>PixelPals Admin</strong>
                    <span><?php echo htmlspecialchars($brandSubtitle); ?></span>
                </div>
            </div>

            <div class="top-actions">
                <span class="admin-name"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                <a class="ghost" href="dashboard.php">Dashboard</a>
                <a class="primary" href="../logout.php">Log Out</a>
            </div>
        </header>

        <!-- The page body gets opened here and is closed in the matching render_admin_panel_end helper below. -->
        <section class="card page-card">
    <?php
}

function render_admin_panel_end(string $extraScripts = ''): void
{
    // Close the shared shell and then optionally append any page-specific scripts.
    ?>
        </section>
    </div>
<?php echo $extraScripts; ?>
</body>
</html>
    <?php
}

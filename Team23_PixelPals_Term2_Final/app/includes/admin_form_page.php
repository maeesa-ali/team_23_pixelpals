<?php

function render_admin_form_page(array $config, string $contentHtml): void
{
    // Form-style admin pages use a slightly different shared shell from the bigger dashboard/list screens.
    $title = $config['title'] ?? 'Admin Form';
    $brandSubtitle = $config['brand_subtitle'] ?? '';
    $backHref = $config['back_href'] ?? '#';
    $backLabel = $config['back_label'] ?? 'Back';
    $extraScripts = $config['extra_scripts'] ?? [];
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
        .shell {
            width: min(960px, calc(100% - 32px));
            margin: 24px auto 36px;
        }
        .topbar,
        .card {
            border-radius: 28px;
            box-shadow: var(--shadow);
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 22px;
            padding: 18px 22px;
            background: rgba(17, 37, 77, 0.9);
            color: #fff;
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
            gap: 12px;
            flex-wrap: wrap;
        }
        .top-actions a {
            padding: 12px 16px;
            border-radius: 999px;
            font-weight: 800;
            color: inherit;
            text-decoration: none;
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
            padding: 30px;
            background: var(--card);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.6);
        }
        .eyebrow {
            display: inline-flex;
            padding: 8px 14px;
            border-radius: 999px;
            background: linear-gradient(90deg, rgba(87, 166, 255, 0.16), rgba(255, 109, 178, 0.18));
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        h1 {
            margin: 18px 0 10px;
            font-size: clamp(2rem, 5vw, 3.8rem);
            line-height: 0.96;
        }
        .intro {
            margin: 0 0 24px;
            line-height: 1.7;
            opacity: 0.86;
        }
        .flash-wrap {
            margin-bottom: 16px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }
        .field {
            display: grid;
            gap: 8px;
        }
        .field.full {
            grid-column: 1 / -1;
        }
        label {
            font-weight: 800;
        }
        input,
        select,
        textarea {
            width: 100%;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(17, 37, 77, 0.14);
            font: inherit;
            background: rgba(255, 255, 255, 0.92);
            color: var(--navy);
        }
        textarea {
            min-height: 140px;
            resize: vertical;
        }
        .media-preview {
            display: grid;
            gap: 10px;
            margin-top: 24px;
            padding: 18px;
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(214,226,255,0.88));
            border: 1px solid rgba(17, 37, 77, 0.08);
        }
        .media-preview strong {
            font-size: 1rem;
        }
        .media-preview img {
            width: min(100%, 280px);
            aspect-ratio: 4 / 3;
            object-fit: cover;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(17, 37, 77, 0.08);
        }
        .media-preview span {
            opacity: 0.78;
            font-size: 0.95rem;
        }
        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 26px;
        }
        button,
        .button-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 13px 18px;
            border: none;
            border-radius: 16px;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
        }
        button {
            background: linear-gradient(135deg, var(--mint), #f5ff9a);
            color: var(--navy);
        }
        .button-link {
            background: rgba(17, 37, 77, 0.08);
            color: inherit;
        }
        .meta {
            margin-top: 16px;
            font-size: 0.95rem;
            opacity: 0.76;
        }
        @media (max-width: 700px) {
            .shell {
                width: min(100% - 20px, 960px);
            }
            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }
            .card {
                padding: 22px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="shell">
        <!-- Shared admin form header with the page's chosen back link and the logout shortcut. -->
        <header class="topbar">
            <div class="brand">
                <img src="../assets/img/logo.png" alt="PixelPals logo">
                <div>
                    <strong>PixelPals Admin</strong>
                    <span><?php echo htmlspecialchars($brandSubtitle); ?></span>
                </div>
            </div>

            <div class="top-actions">
                <a class="ghost" href="<?php echo htmlspecialchars($backHref); ?>"><?php echo htmlspecialchars($backLabel); ?></a>
                <a class="primary" href="../logout.php">Log Out</a>
            </div>
        </header>

        <!-- The individual form page injects its own form/body markup into this shared card wrapper. -->
        <section class="card">
            <?php echo $contentHtml; ?>
        </section>
    </div>
    <!-- Allow a form page to attach a small JS file without duplicating the whole shell. -->
    <?php foreach ($extraScripts as $scriptPath): ?>
        <script src="<?php echo htmlspecialchars($scriptPath); ?>" defer></script>
    <?php endforeach; ?>
</body>
</html>
    <?php
}

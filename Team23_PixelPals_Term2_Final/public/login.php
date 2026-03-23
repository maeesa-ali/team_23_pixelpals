<?php
// Customers and admins both come through this page, so the sign-in form stays shared.
if (session_status() === PHP_SESSION_NONE) 
    {
    session_start();
}

require_once __DIR__ . '/../app/includes/flash.php';
require_once __DIR__ . '/../app/includes/admin_preview.php';
require_once __DIR__ . '/../app/includes/header.php';
require_once __DIR__ . '/../app/includes/footer.php';

// This page stays small on purpose because the real login validation happens in the POST action.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PixelPals | Log In</title>
    <script src="js/validation.js?v=2" defer></script>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .login-card, .visual-panel, button 
        {
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }
        .login-card:hover, .visual-panel:hover, button:hover 
        {
            transform: translateY(-2px);
        }

        .login-layout 
        {
            display: grid;
            grid-template-columns: 0.95fr 1.05fr;
            gap: 24px;
            margin-bottom: 36px;
            align-items: stretch;
        }

        .login-card, .visual-panel 
        {
            background: var(--card);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.6);
            border-radius: 30px;
            box-shadow: var(--shadow);
        }

        .visual-panel 
        {
            padding: 12px;
            background: transparent;
            border: none;
            box-shadow: none;
            backdrop-filter: none;
        }

        .visual-figure 
        {
            position: relative;
            min-height: 520px;
            border-radius: 26px;
            overflow: hidden;
            padding: 30px;
            display: flex;
            flex-direction: column;
            background:
                radial-gradient(circle at 18% 16%, rgba(255, 255, 255, 0.2), transparent 18%),
                radial-gradient(circle at 82% 18%, rgba(255, 213, 77, 0.22), transparent 18%),
                linear-gradient(165deg, #7446c8 0%, #4d61dd 52%, #2b6fd6 100%);
            color: #fff;
        }

        .visual-figure::before 
        {
            content: "";
            position: absolute;
            inset: 18px;
            border-radius: 22px;
            border: 1px solid rgba(255,255,255,0.16);
        }

        .visual-figure > * 
        {
            position: relative;
            z-index: 1;
        }

        .visual-badge 
        {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            width: fit-content;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,0.14);
            font-weight: 700;
        }

        .visual-badge img 
        {
            width: 24px;
            height: 24px;
            object-fit: contain;
        }

        .visual-art 
        {
            position: absolute;
            right: 28px;
            top: 76px;
            width: 220px;
            height: 220px;
            border-radius: 36px;
            background:
                radial-gradient(circle at 30% 30%, rgba(255,255,255,0.34), transparent 30%),
                linear-gradient(145deg, rgba(255,255,255,0.3), rgba(255,255,255,0.08));
            border: 1px solid rgba(255,255,255,0.24);
            transform: rotate(12deg);
            display: grid;
            place-items: center;
            box-shadow: 0 24px 60px rgba(8, 18, 54, 0.28);
        }

        .visual-art img 
        {
            width: 118px;
            height: 118px;
            object-fit: contain;
            filter: drop-shadow(0 10px 25px rgba(8, 18, 54, 0.28));
        }

        .visual-copy 
        {
            display: grid;
            gap: 16px;
            max-width: 20rem;
            margin-top: auto;
        }

        .visual-copy strong 
        {
            font-size: clamp(2.4rem, 4.8vw, 3.9rem);
            line-height: 0.92;
            letter-spacing: -0.04em;
        }

        .visual-points 
        {
            display: grid;
            gap: 12px;
            margin-top: 6px;
        }

        .visual-point 
        {
            padding: 13px 15px;
            border-radius: 18px;
            background: rgba(255,255,255,0.14);
            font-weight: 700;
            font-size: 0.95rem;
        }

        .login-card 
        {
            padding: 28px;
            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(247,250,255,0.82));
        }

        .login-card h2 
        {
            margin: 0 0 12px;
            font-size: 1.95rem;
        }

        .login-card p 
        {
            line-height: 1.7;
            margin: 0 0 6px;
            opacity: 0.9;
        }

        .flash-wrap 
        {
            margin-bottom: 16px;
        }

        .login-form 
        {
            display: grid;
            gap: 16px;
        }

        .field 
        {
            display: flex;
            flex-direction: column;
            gap: 8px;
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

        button 
        {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 18px;
            cursor: pointer;
            font: inherit;
            font-weight: 800;
            background: linear-gradient(135deg, var(--mint), #f5ff9a);
            box-shadow: 0 14px 28px rgba(17,37,77,0.12);
        }

        .alt-link 
        {
            text-align: center;
            font-weight: 700;
            color: #2b6fd6;
        }

        #error 
        {
            text-align: center;
            min-height: 20px;
            font-size: 0.95rem;
            font-weight: 700;
            color: #b43030;
        }

        @media (max-width: 920px) 
        {
            .login-layout 
            {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) 
        {
            .login-card { padding: 22px; }
            .visual-panel { padding: 0; }
            .visual-figure { min-height: 360px; padding: 20px; }
            .visual-art 
            {
                width: 130px;
                height: 130px;
                top: 62px;
                right: 18px;
            }
            .visual-art img 
            {
                width: 78px;
                height: 78px;
            }
        }
    </style>
</head>
<body>
    <?php renderAdminPreviewBanner(); ?>
    <div class="site-shell">
        <!-- Reuse the shared auth header so login and signup stay visually in sync. -->
        <?php render_public_header('auth', 'signup.php', 'Create Account'); ?>

        <!-- The login split layout starts here: visual panel on one side, form on the other. -->
        <section class="login-layout">
            <aside class="visual-panel">
                <!-- The left side is just a branded visual block so the form side can stay simple. -->
                <div class="visual-figure">
                    <div class="visual-art">
                        <img src="assets/img/logo.png" alt="PixelPals logo">
                    </div>

                    <div class="visual-badge">
                        <img src="assets/img/logo.png" alt="PixelPals">
                        PixelPals Access
                    </div>

                    <div class="visual-copy">
                        <strong>Log in to continue.</strong>
                        <div class="visual-points">
                            <div class="visual-point">View your orders and account details.</div>
                            <div class="visual-point">Return to your basket and checkout more quickly.</div>
                            <div class="visual-point">Admins can also sign in here.</div>
                        </div>
                    </div>
                </div>
            </aside>

            <section class="login-card">
                <h2>Sign In</h2>
                <p>Enter your details below.</p>

                <!-- Flash messages sit right above the form because failed login feedback usually comes back here. -->
                <div class="flash-wrap">
                    <?php display_flash_messages(); ?>
                </div>

                <!-- The form itself stays lean: credentials first, then one route to the signup page. -->
                <form id="loginForm" class="login-form" method="POST" action="login_post.php" novalidate>
                    <div class="field">
                        <label for="username">Username or Email</label>
                        <input type="text" id="username" name="username">
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password">
                    </div>

                    <button type="submit">Log In</button>
                    <a class="alt-link" href="signup.php">Need an account? Create one here</a>
                    <p id="error"></p>
                </form>
            </section>

        </section>

        <!-- The shared footer avoids repeating another copy of the auth-page footer markup here. -->
        <?php render_public_footer(); ?>
    </div>
</body>
</html>

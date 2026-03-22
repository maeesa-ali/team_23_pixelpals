<?php
// This page is the shared account creation form for normal customers and first-time admins.
// Start a session if one is not already running so flash messages and form flow work properly.
if (session_status() === PHP_SESSION_NONE) 
{
    session_start();
}

// These shared includes cover flash messages, admin preview mode and the common auth page chrome.
require_once __DIR__ . '/../app/includes/flash.php';
require_once __DIR__ . '/../app/includes/admin_preview.php';
require_once __DIR__ . '/../app/includes/header.php';
require_once __DIR__ . '/../app/includes/footer.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="js/validation.js?v=2" defer></script>
    <title>PixelPals | Create Account</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* Small hover feedback keeps the page feeling a bit more alive without changing the layout. */
        .signup-card, .visual-panel, button 
        {
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }
        .signup-card:hover, .visual-panel:hover, button:hover 
        {
            transform: translateY(-2px);
        }

        /* The page is split into two main sides: the branded panel and the actual signup form. */
        .signup-layout 
        {
            display: grid;
            grid-template-columns: 0.95fr 1.05fr;
            gap: 24px;
            align-items: stretch;
            margin-bottom: 36px;
        }

        /* Both sides use the same rounded card language so the page still feels connected. */
        .signup-card, .visual-panel 
        {
            background: var(--card);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.6);
            border-radius: 30px;
            box-shadow: var(--shadow);
        }

        /* The left side is treated more like a visual frame, so the outer wrapper stays minimal. */
        .visual-panel 
        {
            padding: 12px;
            background: transparent;
            border: none;
            box-shadow: none;
            backdrop-filter: none;
        }

        .signup-card p 
        {
            line-height: 1.7;
            margin: 0;
            opacity: 0.9;
        }

        /* This is the purple branded panel that gives the page some personality beside the form. */
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

        /* A faint inner border helps the panel feel framed instead of like one flat block of colour. */
        .visual-figure::before 
        {
            content: "";
            position: absolute;
            inset: 18px;
            border-radius: 22px;
            border: 1px solid rgba(255,255,255,0.18);
        }

        /* Keep every child above the decorative layers. */
        .visual-figure > * 
        {
            position: relative;
            z-index: 1;
        }

        /* This badge acts like a small label for the left-hand visual area. */
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

        /* The logo is kept small here so it supports the text rather than stealing focus from it. */
        .visual-badge img 
        {
            width: 24px;
            height: 24px;
            object-fit: contain;
        }

        /* The main copy stack stays near the bottom so the visual panel feels balanced. */
        .visual-copy 
        {
            display: grid;
            gap: 16px;
            max-width: 20rem;
            margin-top: auto;
        }

        /* The headline is intentionally bold because it is the main anchor on the left side. */
        .visual-copy strong 
        {
            font-size: clamp(2.4rem, 4.8vw, 3.9rem);
            line-height: 0.92;
            letter-spacing: -0.04em;
        }

        /* These smaller point cards quickly explain why signing up is useful. */
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

        /* The floating logo tile is just there to break up the panel visually. */
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

        /* The actual form card stays clean and lighter so it is easier to scan and fill out. */
        .signup-card 
        {
            padding: 28px;
            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(247,250,255,0.82));
            display: grid;
            align-content: start;
        }

        /* A tiny gap under the intro copy helps keep the card from feeling cramped. */
        .signup-card p 
        {
            margin-bottom: 6px;
        }

        .signup-card h2 
        {
            margin: 0 0 12px;
            font-size: 1.95rem;
        }

        /* Flash messages sit here so signup errors and success feedback stay close to the form. */
        .flash-wrap 
        {
            margin-bottom: 16px;
        }

        /* The form itself is a simple vertical stack, with the inner grid handling paired fields. */
        .signup-form 
        {
            display: grid;
            gap: 16px;
        }

        /* Most fields sit in two columns to keep the form shorter on larger screens. */
        .form-grid 
        {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        /* Each field is wrapped so the label and input always stay visually tied together. */
        .field 
        {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        /* Full-width rows are used for fields that need more space, like email and admin controls. */
        .field.full 
        {
            grid-column: 1 / -1;
        }

        /* Labels are slightly heavier so the form is easier to scan quickly. */
        label 
        {
            font-weight: 800;
            font-size: 0.95rem;
        }

        /* Inputs keep a consistent shape and size so the mixed field types still feel unified. */
        input 
        {
            width: 100%;
            box-sizing: border-box;
            padding: 13px 14px;
            border: 1px solid rgba(17,37,77,0.12);
            border-radius: 16px;
            font: inherit;
            background: rgba(255,255,255,0.92);
            color: #334;
        }

        /* The submit button is kept obvious because it is the main action on the page. */
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

        /* This secondary link gives people an easy route back to login if they already have an account. */
        .alt-link 
        {
            text-align: center;
            font-weight: 700;
            color: #2b6fd6;
        }

        /* Client-side validation messages land here without shifting the rest of the form around too much. */
        #error 
        {
            text-align: center;
            min-height: 20px;
            font-size: 0.95rem;
            font-weight: 700;
            color: #b43030;
        }

        /* On tablets and smaller laptops, stack the visual panel above the form. */
        @media (max-width: 920px) 
        {
            .signup-layout 
            {
                grid-template-columns: 1fr;
            }
        }

        /* On phones, simplify spacing and let every field take the full width. */
        @media (max-width: 640px) 
        {
            .form-grid { grid-template-columns: 1fr; }
            .signup-card { padding: 22px; }
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
        <!-- Shared auth header keeps the login/signup pages visually tied together. -->
        <?php render_public_header('auth', 'login.php', 'Log In'); ?>

        <!-- The signup split layout starts here: visual panel on one side, form on the other. -->
        <section class="signup-layout">
            <!-- Left side: simple branded content so the page does not feel like a plain form. -->
            <aside class="visual-panel">
                <div class="visual-figure">
                    <!-- Decorative floating logo tile. -->
                    <div class="visual-art">
                        <img src="assets/img/logo.png" alt="PixelPals logo">
                    </div>

                    <!-- Small branded label at the top of the visual area. -->
                    <div class="visual-badge">
                        <img src="assets/img/logo.png" alt="PixelPals">
                        PixelPals Access
                    </div>

                    <!-- Short reasons to create an account. -->
                    <div class="visual-copy">
                        <strong>Create an account.</strong>
                        <div class="visual-points">
                            <div class="visual-point">Save your details for future orders.</div>
                            <div class="visual-point">View past orders in one place.</div>
                            <div class="visual-point">Update your profile later from your account page.</div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Right side: the actual signup form and any feedback messages. -->
            <section class="signup-card">
                <h2>Create Your Account</h2>
                <p>Fill in the form below to get started.</p>

                <!-- Flash messages from the backend appear here after redirects. -->
                <div class="flash-wrap">
                    <?php display_flash_messages(); ?>
                </div>

                <!-- The form posts to the signup action, while the JS file handles client-side checks. -->
                <form id="regForm" class="signup-form" action="signup_post.php" method="POST" novalidate>
                    <!-- Main signup fields are grouped in a responsive grid to keep the form compact. -->
                    <div class="form-grid">
                        <!-- Email gets a full row because it is longer and more important than most fields. -->
                        <div class="field full">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email">
                        </div>

                        <!-- Username is used later for customer logins and account display. -->
                        <div class="field">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username">
                        </div>

                        <!-- Date of birth is stored as part of the customer profile. -->
                        <div class="field">
                            <label for="dob">Date of Birth</label>
                            <input type="date" id="dob" name="dob">
                        </div>

                        <!-- First and last name are split into separate fields for easier account editing later. -->
                        <div class="field">
                            <label for="first_Name">First Name</label>
                            <input type="text" id="first_Name" name="first_name">
                        </div>

                        <div class="field">
                            <label for="last_Name">Last Name</label>
                            <input type="text" id="last_Name" name="last_name">
                        </div>

                        <!-- Password pair keeps the normal create-and-confirm flow in one place. -->
                        <div class="field">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password">
                        </div>

                        <div class="field">
                            <label for="confirm_Password">Confirm Password</label>
                            <input type="password" id="confirm_Password" name="confirm_password">
                        </div>

                        <!-- Ticking this box reveals the extra admin access-code field below. -->
                        <div class="field full">
                            <label style="display:flex; align-items:center; gap:10px;">
                                <input type="checkbox" id="adminCheckbox" name="is_admin" value="1" style="width:auto; padding:0;">
                                Create as admin account
                            </label>
                        </div>

                        <!-- This row stays hidden unless the user is trying to create an admin account. -->
                        <div class="field full" id="adminPasswordRow" style="display:none;">
                            <label for="admin_Password">Admin Access Code</label>
                            <input type="password" id="admin_Password" name="admin_password" placeholder="Enter admin access code">
                        </div>
                    </div>

                    <!-- Primary action and fallback link for people who already have an account. -->
                    <button type="submit">Create Account</button>
                    <a class="alt-link" href="login.php">Already have an account? Log in</a>

                    <!-- Client-side validation writes inline messages here. -->
                    <p id="error"></p>
                </form>
            </section>

        </section>

        <!-- Shared auth footer keeps the signup page consistent with the login page. -->
        <?php render_public_footer(); ?>
    </div>
</body>
</html>

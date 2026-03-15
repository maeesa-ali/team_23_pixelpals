<?php

// Start session if it has not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require the user to be logged in.
 * Use on pages like account.php, orders.php, checkout.php
 */
function requireLogin()
{
    // If no logged-in user exists in session, send them to login page
    if (!isset($_SESSION['user_id'])) {
        header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
        exit;
    }
}

/**
 * Require the user to be an admin.
 * Use on admin pages.
 *
 * NOT FULLY CONFIRMED YET:
 * We still need Russell to confirm how admin is stored.
 * For now, this assumes $_SESSION['role'] should equal 'admin'.
 *
 * Need from:
 * - Russell
 * Related page/file:
 * - public/admin/dashboard.php
 * - database/schema.sql
 * - database/seed.sql
 */
function requireAdmin()
{
    // User must at least be logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
        exit;
    }

    // Temporary admin check
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: /Team23_PixelPals_Term2_Final/public/index.php');
        exit;
    }
}

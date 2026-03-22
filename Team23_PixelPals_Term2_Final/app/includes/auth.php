<?php

if (session_status() === PHP_SESSION_NONE) {
    // These helpers are used all over the site, so make sure the session is available before checking it.
    session_start();
}

function requireLogin()
{
    // Customer-only pages call this to block guests and push them back to the shared login page.
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Please log in to access this page.";
        header("Location: /Team23_PixelPals_Term2_Final/public/login.php");
        exit();
    }
}

function requireAuthenticatedSession()
{
    // Some pages can be used by either a customer or an admin preview session, so allow either one here.
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
        $_SESSION['error'] = "Please log in to access this page.";
        header("Location: /Team23_PixelPals_Term2_Final/public/login.php");
        exit();
    }
}

function requireAdmin()
{
    // Admin pages use this stricter guard to block both guests and normal customer sessions.
    if (!isset($_SESSION['admin_id'])) {
        $_SESSION['error'] = "Admin access required.";
        header("Location: /Team23_PixelPals_Term2_Final/public/login.php");
        exit();
    }
}

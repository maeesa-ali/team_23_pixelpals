<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {

        $_SESSION['error'] = "Please log in to access this page.";

        header("Location: /login.php");
        exit();
    }
}


function requireAdmin()
{
    if (!isset($_SESSION['admin_id'])) {

        $_SESSION['error'] = "Admin access required.";

        header("Location: /login.php");
        exit();
    }
}

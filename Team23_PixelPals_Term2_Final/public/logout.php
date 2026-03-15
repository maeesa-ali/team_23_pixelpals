<?php

// Start session so we can clear it
session_start();

// Remove all session data
session_unset();

// Destroy session completely
session_destroy();

// Redirect user back to login page
header('Location: /Team23_PixelPals_Term2_Final/public/login.php');
exit;

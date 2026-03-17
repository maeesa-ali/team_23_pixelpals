<?php

// Start the session so we can access and clear session data
session_start();

// Remove all session variables 
session_unset();

// Destroy the entire session so the user is fully logged out
session_destroy();

// Redirect the user to the login page after logging out
// The path includes the project folder because the site is running
// inside /htdocs/Team23_PixelPals_Term2_Final in XAMPP
header('Location: /Team23_PixelPals_Term2_Final/public/login.php');

// Stop further script execution after redirect
exit;

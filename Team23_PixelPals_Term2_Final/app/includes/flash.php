<?php

function display_flash_messages() {
    if (isset($_SESSION['success'])) {
        echo '<div style="color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 12px; margin: 15px 0; border-radius: 4px;">' 
             . htmlspecialchars($_SESSION['success']) . 
             '</div>';
        unset($_SESSION['success']);
    }

    if (isset($_SESSION['error'])) {
        echo '<div style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 12px; margin: 15px 0; border-radius: 4px;">' 
             . htmlspecialchars($_SESSION['error']) . 
             '</div>';
        unset($_SESSION['error']);
    }
}
?>
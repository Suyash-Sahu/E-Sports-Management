<?php
session_start();
include 'db_connection.php';

// Function to redirect with a message
function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION[$type] = $message;
    }
    header("Location: $url");
    exit();
}

// Function to display flash messages
function display_flash_message($type) {
    if (isset($_SESSION[$type])) {
        $message = $_SESSION[$type];
        unset($_SESSION[$type]);
        return $message;
    }
    return null;
}
?> 
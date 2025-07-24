<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is an organizer
function isOrganizer() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'organizer';
}

// Function to check if user is a player
function isPlayer() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'player';
}

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);

// List of pages that don't require authentication
$public_pages = ['index.php', 'login.php', 'register.php', 'logout.php'];

// Only redirect if not on a public page and not logged in
if (!in_array($current_page, $public_pages) && !isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Special check for create_tournament.php
if ($current_page === 'create_tournament.php' && (!isLoggedIn() || !isOrganizer())) {
    header("Location: login.php");
    exit();
}
?> 
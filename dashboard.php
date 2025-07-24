<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Redirect based on user type
if ($_SESSION['user_type'] == 'player') {
    header("Location: player_dashboard.php");
} elseif ($_SESSION['user_type'] == 'organizer') {
    header("Location: organizer_dashboard.php");
} else {
    // If user type is not set or invalid, redirect to login
    header("Location: login.php");
}
exit();
?> 
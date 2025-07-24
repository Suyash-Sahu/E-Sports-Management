<?php
session_start();
include 'includes/db_connection.php';

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'organizer') {
    header("Location: login.php");
    exit();
}

// Check if tournament ID is provided
if (!isset($_POST['tournament_id'])) {
    header("Location: organizer_dashboard.php");
    exit();
}

$tournament_id = $_POST['tournament_id'];
$organizer_id = $_SESSION['user_id'];

// Delete tournament players
$sql = "DELETE FROM tournament_players WHERE tournament_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();

// Finally delete the tournament
$sql = "DELETE FROM tournaments WHERE tournament_id = ? AND organizer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $tournament_id, $organizer_id);

if ($stmt->execute()) {
    $_SESSION['message'] = "Tournament has been successfully deleted.";
} else {
    $_SESSION['error'] = "Error deleting tournament. Please try again.";
}

header("Location: organizer_dashboard.php");
exit();
?> 
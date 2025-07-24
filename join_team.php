<?php
require_once 'includes/init.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirect('login.php', 'Please login to join a team', 'error');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['team_id'])) {
    $team_id = $_POST['team_id'];
    $user_id = $_SESSION['user_id'];
    
    // Check if team exists
    $check_sql = "SELECT * FROM teams WHERE team_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $team_id);
    $check_stmt->execute();
    $team = $check_stmt->get_result()->fetch_assoc();
    
    if (!$team) {
        redirect('teams.php', 'Team not found', 'error');
    }
    
    // Check if user is already a member
    $member_sql = "SELECT * FROM team_members WHERE team_id = ? AND user_id = ?";
    $member_stmt = $conn->prepare($member_sql);
    $member_stmt->bind_param("ii", $team_id, $user_id);
    $member_stmt->execute();
    
    if ($member_stmt->get_result()->num_rows > 0) {
        redirect('teams.php', 'You are already a member of this team', 'error');
    }
    
    // Add user to team
    $join_sql = "INSERT INTO team_members (team_id, user_id) VALUES (?, ?)";
    $join_stmt = $conn->prepare($join_sql);
    $join_stmt->bind_param("ii", $team_id, $user_id);
    
    if ($join_stmt->execute()) {
        redirect('teams.php', 'Successfully joined the team');
    } else {
        redirect('teams.php', 'Failed to join the team', 'error');
    }
} else {
    redirect('teams.php', 'Invalid request', 'error');
} 
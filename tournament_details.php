<?php
session_start();
include 'includes/db_connection.php';
include 'includes/header.php';

// Check if tournament ID is provided
if (!isset($_GET['id'])) {
    header("Location: tournaments.php");
    exit();
}

$tournament_id = $_GET['id'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;

// Get tournament details
$sql = "SELECT t.*, o.organization_name, o.contact_email, o.contact_phone,
        (SELECT COUNT(*) FROM tournament_players tp WHERE tp.tournament_id = t.tournament_id) as current_players
        FROM tournaments t 
        JOIN organizer_profiles o ON t.organizer_id = o.user_id 
        WHERE t.tournament_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Tournament not found.";
    header("Location: tournaments.php");
    exit();
}

$tournament = $result->fetch_assoc();

// Check if user is already registered
$is_registered = false;
if ($user_type == 'player') {
    $sql = "SELECT 1 FROM tournament_players WHERE tournament_id = ? AND player_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $tournament_id, $user_id);
    $stmt->execute();
    $is_registered = $stmt->get_result()->num_rows > 0;
}

// Get registered players with error handling
try {
    $sql = "SELECT u.username, u.email, tp.team_name 
            FROM tournament_players tp 
            JOIN users u ON tp.player_id = u.user_id 
            WHERE tp.tournament_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $registered_players = $stmt->get_result();
} catch (Exception $e) {
    // Log the error but don't show it to users
    error_log("Error fetching registered players: " . $e->getMessage());
    $registered_players = false;
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0"><?php echo htmlspecialchars($tournament['tournament_name']); ?></h2>
                </div>
                <div class="card-body">
                    <p class="lead"><?php echo htmlspecialchars($tournament['description']); ?></p>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Tournament Details</h5>
                            <ul class="list-unstyled">
                                <li><strong>Game:</strong> <?php echo htmlspecialchars($tournament['game_name']); ?></li>
                                <li><strong>Level:</strong> <span class="badge bg-info"><?php echo htmlspecialchars($tournament['level']); ?></span></li>
                                <li><strong>Format:</strong> <?php echo htmlspecialchars($tournament['format']); ?></li>
                                <li><strong>Team Requirement:</strong> 
                                    <?php 
                                    $team_requirement = $tournament['team_required'];
                                    $badge_class = $team_requirement == 'solo' ? 'bg-primary' : 
                                                 ($team_requirement == 'team' ? 'bg-success' : 'bg-warning');
                                    $display_text = $team_requirement == 'solo' ? 'Solo Players Only' : 
                                                  ($team_requirement == 'team' ? 'Teams Only' : 'Both Allowed');
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo $display_text; ?></span>
                                </li>
                                <li><strong>Start Date:</strong> <?php echo date('M d, Y H:i', strtotime($tournament['start_date'])); ?></li>
                                <li><strong>End Date:</strong> <?php echo date('M d, Y H:i', strtotime($tournament['end_date'])); ?></li>
                                <li><strong>Registration Deadline:</strong> <?php echo date('M d, Y H:i', strtotime($tournament['registration_deadline'])); ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Prize Information</h5>
                            <ul class="list-unstyled">
                                <li><strong>Entry Fee:</strong> ₹<?php echo number_format($tournament['entry_fee'], 2); ?></li>
                                <li><strong>Prize Pool:</strong> ₹<?php echo number_format($tournament['prize_pool'], 2); ?></li>
                                <li><strong>Maximum Players:</strong> <?php echo $tournament['max_players']; ?></li>
                                <li><strong>Current Players:</strong> <?php echo $tournament['current_players']; ?></li>
                            </ul>
                            <div class="progress mb-3">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: <?php echo ($tournament['current_players'] / $tournament['max_players']) * 100; ?>%">
                                    <?php echo $tournament['current_players']; ?>/<?php echo $tournament['max_players']; ?> players
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Organizer Information</h5>
                        <ul class="list-unstyled">
                            <li><strong>Organization:</strong> <?php echo htmlspecialchars($tournament['organization_name']); ?></li>
                            <li><strong>Contact Email:</strong> <?php echo htmlspecialchars($tournament['contact_email']); ?></li>
                            <li><strong>Contact Phone:</strong> <?php echo htmlspecialchars($tournament['contact_phone']); ?></li>
                        </ul>
                    </div>

                    <?php if ($user_type == 'player'): ?>
                        <div class="d-grid gap-2">
                            <?php if (!$is_registered && $tournament['current_players'] < $tournament['max_players'] && strtotime($tournament['registration_deadline']) > time()): ?>
                                <a href="join_tournament.php?id=<?php echo $tournament_id; ?>" class="btn btn-success btn-lg">Join Tournament</a>
                            <?php elseif ($is_registered): ?>
                                <button class="btn btn-secondary btn-lg" disabled>Already Registered</button>
                            <?php elseif ($tournament['current_players'] >= $tournament['max_players']): ?>
                                <button class="btn btn-danger btn-lg" disabled>Tournament Full</button>
                            <?php elseif (strtotime($tournament['registration_deadline']) <= time()): ?>
                                <button class="btn btn-danger btn-lg" disabled>Registration Closed</button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Registered Players</h5>
                </div>
                <div class="card-body">
                    <?php if ($registered_players && $registered_players->num_rows > 0): ?>
                        <div class="list-group">
                            <?php while ($player = $registered_players->fetch_assoc()): ?>
                                <div class="list-group-item">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($player['username']); ?></h6>
                                    <?php if (!empty($player['team_name'])): ?>
                                        <small class="text-muted">Team: <?php echo htmlspecialchars($player['team_name']); ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No players registered yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 
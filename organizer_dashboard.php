<?php
session_start();
include 'includes/db_connection.php';
include 'includes/header.php';

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'organizer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get organizer profile
$sql = "SELECT * FROM organizer_profiles WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

// Get organizer's tournaments
$sql = "SELECT t.*, g.game_name,
        (SELECT COUNT(*) FROM tournament_players tp WHERE tp.tournament_id = t.tournament_id) as current_players
        FROM tournaments t
        JOIN games g ON t.game_id = g.game_id
        WHERE t.organizer_id = ?
        ORDER BY t.start_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tournaments = $stmt->get_result();

$message = '';
$error = '';

// Handle winner selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tournament_id'], $_POST['winner_id'])) {
    $tournament_id = $_POST['tournament_id'];
    $winner_id = $_POST['winner_id'];
    
    if (selectTournamentWinner($tournament_id, $winner_id)) {
        $message = "Winner has been successfully selected and points have been awarded!";
    } else {
        $error = "There was an error selecting the winner. Please try again.";
    }
}
?>

<div class="container my-5">
    <div class="row">
        <!-- Profile Section -->
        <div class="col-md-4">
            <div class="card profile-section">
                <div class="card-body text-center">
                    <h3><?php echo htmlspecialchars($profile['organization_name']); ?></h3>
                    <p class="text-muted">Organizer</p>
                    <div class="d-grid gap-2">
                        <a href="create_tournament.php" class="btn btn-primary">Create New Tournament</a>
                        <a href="edit_organizer_profile.php" class="btn btn-outline-primary">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Tournaments Section -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">My Tournaments</h4>
                    <a href="create_tournament.php" class="btn btn-light btn-sm">Create New</a>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($tournaments->num_rows === 0): ?>
                        <div class="alert alert-info">You haven't created any tournaments yet.</div>
                    <?php else: ?>
                        <div class="row">
                            <?php while ($tournament = $tournaments->fetch_assoc()): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h4 class="mb-0"><?php echo htmlspecialchars($tournament['tournament_name']); ?></h4>
                                            <span class="badge bg-<?php 
                                                echo $tournament['status'] === 'upcoming' ? 'info' : 
                                                    ($tournament['status'] === 'ongoing' ? 'success' : 'secondary'); 
                                            ?>">
                                                <?php echo ucfirst($tournament['status']); ?>
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Start Date:</strong> <?php echo date('F j, Y', strtotime($tournament['start_date'])); ?></p>
                                            <p><strong>End Date:</strong> <?php echo date('F j, Y', strtotime($tournament['end_date'])); ?></p>
                                            <p><strong>Participants:</strong> <?php echo $tournament['current_players']; ?></p>
                                            
                                            <?php if ($tournament['status'] === 'ended' && (!isset($tournament['winner_id']) || !$tournament['winner_id'])): ?>
                                                <form method="POST" action="" class="mt-3">
                                                    <input type="hidden" name="tournament_id" value="<?php echo $tournament['tournament_id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label for="winner_<?php echo $tournament['tournament_id']; ?>" class="form-label">Select Winner:</label>
                                                        <select class="form-select" id="winner_<?php echo $tournament['tournament_id']; ?>" name="winner_id" required>
                                                            <option value="">Select a winner...</option>
                                                            <?php
                                                            $participants = getTournamentParticipants($tournament['tournament_id']);
                                                            while ($participant = $participants->fetch_assoc()):
                                                            ?>
                                                                <option value="<?php echo $participant['user_id']; ?>">
                                                                    <?php echo htmlspecialchars($participant['username']); ?> 
                                                                    (Current Points: <?php echo $participant['points']; ?>)
                                                                </option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>
                                                    
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-trophy"></i> Select Winner
                                                    </button>
                                                </form>
                                            <?php elseif (isset($tournament['winner_id']) && $tournament['winner_id']): ?>
                                                <?php
                                                $sql = "SELECT username FROM users WHERE user_id = ?";
                                                $stmt = $conn->prepare($sql);
                                                $stmt->bind_param("i", $tournament['winner_id']);
                                                $stmt->execute();
                                                $winner = $stmt->get_result()->fetch_assoc();
                                                ?>
                                                <div class="alert alert-success mt-3">
                                                    <i class="fas fa-trophy"></i> Winner: <?php echo htmlspecialchars($winner['username']); ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="mt-3">
                                                <a href="tournament_details.php?id=<?php echo $tournament['tournament_id']; ?>" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a>
                                                <a href="edit_tournament.php?id=<?php echo $tournament['tournament_id']; ?>" class="btn btn-outline-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="manage_participants.php?id=<?php echo $tournament['tournament_id']; ?>" class="btn btn-outline-success">
                                                    <i class="fas fa-users"></i> Manage Participants
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Stats Section -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h5>Total Tournaments</h5>
                            <h3><?php echo $tournaments->num_rows; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h5>Active Tournaments</h5>
                            <h3>
                                <?php
                                $sql = "SELECT COUNT(*) as count FROM tournaments WHERE organizer_id = ? AND status = 'ongoing'";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                echo $stmt->get_result()->fetch_assoc()['count'];
                                ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h5>Total Participants</h5>
                            <h3>
                                <?php
                                $sql = "SELECT COUNT(DISTINCT player_id) as count FROM tournament_players tp 
                                        JOIN tournaments t ON tp.tournament_id = t.tournament_id 
                                        WHERE t.organizer_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                echo $stmt->get_result()->fetch_assoc()['count'];
                                ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 
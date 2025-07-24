<?php
session_start();
require_once 'includes/db_connection.php';
require_once 'includes/tournament_functions.php';

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organizer') {
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['user_id'];
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

// Get tournaments that need winner selection
$sql = "SELECT t.*, g.game_name, 
        (SELECT COUNT(*) FROM matches m WHERE m.tournament_id = t.tournament_id) as match_count
        FROM tournaments t 
        JOIN games g ON t.game_id = g.game_id
        WHERE t.organizer_id = ? 
        AND t.status = 'ended'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$tournaments = $stmt->get_result();

include 'includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4">Select Tournament Winners</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($tournaments->num_rows === 0): ?>
        <div class="alert alert-info">No tournaments need winner selection at this time.</div>
    <?php else: ?>
        <?php while ($tournament = $tournaments->fetch_assoc()): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h4><?php echo htmlspecialchars($tournament['tournament_name']); ?></h4>
                </div>
                <div class="card-body">
                    <p><strong>Game:</strong> <?php echo htmlspecialchars($tournament['game_name']); ?></p>
                    <p><strong>End Date:</strong> <?php echo date('F j, Y', strtotime($tournament['end_date'])); ?></p>
                    <p><strong>Total Matches:</strong> <?php echo $tournament['match_count']; ?></p>
                    
                    <form method="POST" action="" class="mt-3">
                        <input type="hidden" name="tournament_id" value="<?php echo $tournament['tournament_id']; ?>">
                        
                        <div class="mb-3">
                            <label for="winner_<?php echo $tournament['tournament_id']; ?>" class="form-label">Select Winner:</label>
                            <select class="form-select" id="winner_<?php echo $tournament['tournament_id']; ?>" name="winner_id" required>
                                <option value="">Select a winner...</option>
                                <?php
                                // Get players from matches
                                $sql = "SELECT DISTINCT u.user_id, u.username, u.points
                                       FROM matches m
                                       JOIN users u ON (m.player1_id = u.user_id OR m.player2_id = u.user_id)
                                       WHERE m.tournament_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $tournament['tournament_id']);
                                $stmt->execute();
                                $players = $stmt->get_result();
                                
                                while ($player = $players->fetch_assoc()):
                                ?>
                                    <option value="<?php echo $player['user_id']; ?>">
                                        <?php echo htmlspecialchars($player['username']); ?> 
                                        (Current Points: <?php echo $player['points']; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Select Winner</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?> 
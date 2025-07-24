<?php
session_start();
require_once 'includes/db_connection.php';

// Check if user is logged in and is a player
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'player') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle tournament joining
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tournament_id'])) {
    $tournament_id = $_POST['tournament_id'];
    
    // Check if tournament exists and is accepting participants
    $sql = "SELECT * FROM tournaments WHERE tournament_id = ? AND status IN ('upcoming', 'active')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $tournament = $stmt->get_result()->fetch_assoc();
    
    if ($tournament) {
        // Check if player has already joined
        $sql = "SELECT * FROM tournament_players WHERE tournament_id = ? AND player_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $tournament_id, $user_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = "You have already joined this tournament.";
        } else {
            // Check if tournament is full
            $sql = "SELECT COUNT(*) as count FROM tournament_players WHERE tournament_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $tournament_id);
            $stmt->execute();
            $count = $stmt->get_result()->fetch_assoc()['count'];
            
            if ($count >= $tournament['max_players']) {
                $error = "This tournament is full.";
            } else {
                // Add player to tournament
                $sql = "INSERT INTO tournament_players (tournament_id, player_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $tournament_id, $user_id);
                
                if ($stmt->execute()) {
                    $message = "Successfully joined the tournament!";
                } else {
                    $error = "Error joining tournament. Please try again.";
                }
            }
        }
    } else {
        $error = "Tournament not found or not accepting participants.";
    }
}

// Get tournament details
if (isset($_GET['id'])) {
    $tournament_id = $_GET['id'];
    $sql = "SELECT t.*, g.game_name, u.username as organizer_name,
            (SELECT COUNT(*) FROM tournament_players WHERE tournament_id = t.tournament_id) as current_players
            FROM tournaments t 
            JOIN games g ON t.game_id = g.game_id 
            JOIN users u ON t.organizer_id = u.user_id 
            WHERE t.tournament_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $tournament = $stmt->get_result()->fetch_assoc();
    
    if (!$tournament) {
        header("Location: tournaments.php");
        exit();
    }
} else {
    header("Location: tournaments.php");
    exit();
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Join Tournament</h3>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <h4><?php echo htmlspecialchars($tournament['tournament_name']); ?></h4>
                    <p><strong>Game:</strong> <?php echo htmlspecialchars($tournament['game_name']); ?></p>
                    <p><strong>Organizer:</strong> <?php echo htmlspecialchars($tournament['organizer_name']); ?></p>
                    <p><strong>Start Date:</strong> <?php echo date('F j, Y', strtotime($tournament['start_date'])); ?></p>
                    <p><strong>End Date:</strong> <?php echo date('F j, Y', strtotime($tournament['end_date'])); ?></p>
                    <p><strong>Prize Pool:</strong> <?php echo $tournament['prize_pool']; ?> points</p>
                    <p><strong>Participants:</strong> <?php echo $tournament['current_players']; ?>/<?php echo $tournament['max_players']; ?></p>
                    
                    <?php if (in_array($tournament['status'], ['upcoming', 'active'])): ?>
                        <?php if ($tournament['current_players'] < $tournament['max_players']): ?>
                            <form method="POST" action="">
                                <input type="hidden" name="tournament_id" value="<?php echo $tournament['tournament_id']; ?>">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Join Tournament</button>
                                    <a href="tournaments.php" class="btn btn-outline-secondary">Back to Tournaments</a>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                This tournament is full.
                            </div>
                            <a href="tournaments.php" class="btn btn-outline-secondary">Back to Tournaments</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <?php
                            switch($tournament['status']) {
                                case 'ended':
                                    echo "This tournament has ended.";
                                    break;
                                case 'completed':
                                    echo "This tournament has been completed.";
                                    break;
                                default:
                                    echo "This tournament is not currently accepting participants.";
                            }
                            ?>
                        </div>
                        <a href="tournaments.php" class="btn btn-outline-secondary">Back to Tournaments</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 
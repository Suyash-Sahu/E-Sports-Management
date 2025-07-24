<?php
session_start();
include 'includes/db_connection.php';
include 'includes/header.php';

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'organizer') {
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['user_id'];
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $match_id = $_POST['match_id'];
    $winner_id = $_POST['winner_id'];
    $score = $_POST['score'];
    $is_team_match = isset($_POST['is_team_match']) ? 1 : 0;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update match status and winner
        $sql = "UPDATE matches SET status = 'completed', winner_id = ? WHERE match_id = ? AND tournament_id IN (SELECT tournament_id FROM tournaments WHERE organizer_id = ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $winner_id, $match_id, $organizer_id);
        $stmt->execute();
        
        if ($is_team_match) {
            // Get team members
            $sql = "SELECT player_id FROM team_members WHERE team_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $winner_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Update each team member's stats
            while ($row = $result->fetch_assoc()) {
                $player_id = $row['player_id'];
                
                // Update player rankings
                $sql = "UPDATE player_rankings 
                        SET matches_played = matches_played + 1,
                            matches_won = matches_won + 1,
                            rank_points = rank_points + 50
                        WHERE player_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $player_id);
                $stmt->execute();
                
                // Award badge based on tournament level
                $sql = "INSERT INTO player_badges (player_id, badge_id) 
                        SELECT ?, badge_id FROM badges 
                        WHERE name = (SELECT level FROM tournaments t JOIN matches m ON t.tournament_id = m.tournament_id WHERE m.match_id = ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $player_id, $match_id);
                $stmt->execute();
            }
        } else {
            // Update individual player stats
            $sql = "UPDATE player_rankings 
                    SET matches_played = matches_played + 1,
                        matches_won = matches_won + 1,
                        rank_points = rank_points + 50
                    WHERE player_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $winner_id);
            $stmt->execute();
            
            // Award badge
            $sql = "INSERT INTO player_badges (player_id, badge_id) 
                    SELECT ?, badge_id FROM badges 
                    WHERE name = (SELECT level FROM tournaments t JOIN matches m ON t.tournament_id = m.tournament_id WHERE m.match_id = ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $winner_id, $match_id);
            $stmt->execute();
        }
        
        // Insert match history
        $sql = "INSERT INTO match_history (match_id, player_id, result, score) VALUES (?, ?, 'win', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $match_id, $winner_id, $score);
        $stmt->execute();
        
        $conn->commit();
        $message = "Match result updated successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error updating match result: " . $e->getMessage();
    }
}

// Get organizer's ongoing matches
$sql = "SELECT m.match_id, m.tournament_id, t.tournament_name, 
        p1.in_game_name as player1_name,
        p2.in_game_name as player2_name
        FROM matches m
        JOIN tournaments t ON m.tournament_id = t.tournament_id
        LEFT JOIN players p1 ON m.player1_id = p1.player_id
        LEFT JOIN players p2 ON m.player2_id = p2.player_id
        WHERE t.organizer_id = ? AND m.status = 'ongoing'
        ORDER BY m.scheduled_time ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$matches = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Results Management</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .match-card {
            margin-bottom: 20px;
        }
        .score-input {
            width: 100px;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h2>Match Results Management</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <?php while ($match = $matches->fetch_assoc()): ?>
                <div class="col-md-6">
                    <div class="card match-card">
                        <div class="card-header">
                            <h5 class="mb-0"><?php echo htmlspecialchars($match['tournament_name']); ?></h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="match_id" value="<?php echo $match['match_id']; ?>">
                                <input type="hidden" name="is_team_match" value="<?php echo $match['is_team_match']; ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Match</label>
                                    <p class="form-control-static">
                                        <?php echo htmlspecialchars($match['player1_name']); ?> vs 
                                        <?php echo htmlspecialchars($match['player2_name']); ?>
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Winner</label>
                                    <select name="winner_id" class="form-select" required>
                                        <option value="">Select Winner</option>
                                        <option value="<?php echo $match['player1_id']; ?>">
                                            <?php echo htmlspecialchars($match['player1_name']); ?>
                                        </option>
                                        <option value="<?php echo $match['player2_id']; ?>">
                                            <?php echo htmlspecialchars($match['player2_name']); ?>
                                        </option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Score</label>
                                    <input type="text" name="score" class="form-control score-input" required 
                                           placeholder="e.g., 2-1, 16-14">
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Submit Result</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            
            <?php if ($matches->num_rows == 0): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No ongoing matches found.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 
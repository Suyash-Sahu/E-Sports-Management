<?php
session_start();
include 'includes/db_connection.php';
include 'includes/header.php';

// Check if user is logged in and is a player
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'player') {
    header("Location: login.php");
    exit();
}

$player_id = $_SESSION['user_id'];

// Get player's basic info
$sql = "SELECT pp.*, p.in_game_name, p.game_domain, pr.*, c.college_name, c.state, c.city
        FROM player_profiles pp
        JOIN players p ON pp.user_id = p.user_id
        LEFT JOIN player_rankings pr ON p.user_id = pr.player_id
        LEFT JOIN colleges c ON pp.college_id = c.college_id
        WHERE pp.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $player_id);
$stmt->execute();
$player = $stmt->get_result()->fetch_assoc();

// If player not found, redirect to login
if (!$player) {
    $_SESSION['error'] = "Player profile not found. Please try logging in again.";
    header("Location: login.php");
    exit();
}

// Debug information
error_log("Player data: " . print_r($player, true));

// Get player's match history
$sql = "SELECT mh.*, t.tournament_name, 
        CASE 
            WHEN m.player1_id = ? THEN p2.in_game_name
            ELSE p1.in_game_name
        END as opponent_name
        FROM match_history mh
        JOIN matches m ON mh.match_id = m.match_id
        JOIN tournaments t ON m.tournament_id = t.tournament_id
        LEFT JOIN players p1 ON m.player1_id = p1.player_id
        LEFT JOIN players p2 ON m.player2_id = p2.player_id
        WHERE mh.player_id = ?
        ORDER BY mh.match_id DESC
        LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $player_id, $player_id);
$stmt->execute();
$match_history = $stmt->get_result();

// Get player's badges
$sql = "SELECT b.* FROM badges b
        JOIN player_badges pb ON b.badge_id = pb.badge_id
        WHERE pb.player_id = ?
        ORDER BY FIELD(b.name, 'All India', 'State', 'College', 'Inter-college')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $player_id);
$stmt->execute();
$badges = $stmt->get_result();

// Get player's tournaments
$sql = "SELECT t.*, g.game_name, o.organization_name,
        (SELECT COUNT(*) FROM tournament_players tp WHERE tp.tournament_id = t.tournament_id) as current_players
        FROM tournaments t
        JOIN games g ON t.game_id = g.game_id
        JOIN organizer_profiles o ON t.organizer_id = o.user_id
        JOIN tournament_players tp ON t.tournament_id = tp.tournament_id
        WHERE tp.player_id = ?
        ORDER BY t.start_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $player_id);
$stmt->execute();
$player_tournaments = $stmt->get_result();

// Get upcoming matches for the player
$sql = "SELECT m.*, t.tournament_name, g.game_name,
        CASE 
            WHEN m.player1_id = ? THEN p2.in_game_name
            ELSE p1.in_game_name
        END as opponent_name
        FROM matches m
        JOIN tournaments t ON m.tournament_id = t.tournament_id
        JOIN games g ON t.game_id = g.game_id
        LEFT JOIN players p1 ON m.player1_id = p1.user_id
        LEFT JOIN players p2 ON m.player2_id = p2.user_id
        WHERE (m.player1_id = ? OR m.player2_id = ?)
        AND m.status = 'scheduled'
        AND (t.status = 'ongoing' OR t.status = 'upcoming')
        ORDER BY m.scheduled_time ASC";

error_log("Player ID for upcoming matches: " . $player_id);
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $player_id, $player_id, $player_id);
$stmt->execute();
$upcoming_matches = $stmt->get_result();

// Debug upcoming matches
error_log("Number of upcoming matches: " . $upcoming_matches->num_rows);
while ($match = $upcoming_matches->fetch_assoc()) {
    error_log("Upcoming match: " . print_r($match, true));
}
$upcoming_matches->data_seek(0); // Reset the result pointer
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .stats-card {
            margin-bottom: 20px;
        }
        .badge-icon {
            width: 50px;
            height: 50px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-header">
                        <h5 class="mb-0">Player Profile</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($player['full_name'] ?? 'N/A'); ?></p>
                        <p><strong>In-Game Name:</strong> <?php echo htmlspecialchars($player['in_game_name'] ?? 'N/A'); ?></p>
                        <p><strong>Game Domain:</strong> <?php echo htmlspecialchars($player['game_domain'] ?? 'N/A'); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars(($player['city'] ?? 'N/A') . ', ' . ($player['state'] ?? 'N/A')); ?></p>
                        <p><strong>College:</strong> <?php echo htmlspecialchars($player['college_name'] ?? 'N/A'); ?></p>
                    </div>
                </div>
                
                <div class="card stats-card">
                    <div class="card-header">
                        <h5 class="mb-0">Statistics</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Rank Points:</strong> <?php echo $player['rank_points'] ?? 0; ?></p>
                        <p><strong>Matches Played:</strong> <?php echo $player['matches_played'] ?? 0; ?></p>
                        <p><strong>Matches Won:</strong> <?php echo $player['matches_won'] ?? 0; ?></p>
                        <p><strong>Win Rate:</strong> <?php 
                            $matches_played = $player['matches_played'] ?? 0;
                            $matches_won = $player['matches_won'] ?? 0;
                            echo $matches_played > 0 ? round(($matches_won / $matches_played) * 100, 2) : 0;
                        ?>%</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card stats-card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Matches</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($match_history->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Tournament</th>
                                            <th>Opponent</th>
                                            <th>Result</th>
                                            <th>Score</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($match = $match_history->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($match['tournament_name']); ?></td>
                                                <td><?php echo htmlspecialchars($match['opponent_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $match['result'] == 'win' ? 'success' : 'danger'; ?>">
                                                        <?php echo ucfirst($match['result']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($match['score']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($match['match_date'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No match history available.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card stats-card">
                    <div class="card-header">
                        <h5 class="mb-0">Achievements</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($badges->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($badge = $badges->fetch_assoc()): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo htmlspecialchars($badge['image_url'] ?? 'images/default-badge.png'); ?>" 
                                                 alt="<?php echo htmlspecialchars($badge['name']); ?>" 
                                                 class="badge-icon">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($badge['name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($badge['description']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p>No achievements yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0">My Tournaments</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($player_tournaments->num_rows > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Tournament</th>
                                                    <th>Game</th>
                                                    <th>Status</th>
                                                    <th>Start Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($tournament = $player_tournaments->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($tournament['tournament_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($tournament['game_name']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php 
                                                                echo $tournament['status'] == 'upcoming' ? 'info' : 
                                                                    ($tournament['status'] == 'ongoing' ? 'success' : 'secondary'); 
                                                            ?>">
                                                                <?php echo ucfirst($tournament['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo date('M d, Y', strtotime($tournament['start_date'])); ?></td>
                                                        <td>
                                                            <a href="tournament_details.php?id=<?php echo $tournament['tournament_id']; ?>" 
                                                               class="btn btn-sm btn-primary">View Details</a>
                                                            <?php if ($tournament['status'] == 'ongoing'): ?>
                                                                <a href="join_match.php?tournament_id=<?php echo $tournament['tournament_id']; ?>" 
                                                                   class="btn btn-sm btn-success">Join Match</a>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p>You haven't joined any tournaments yet.</p>
                                    <a href="tournaments.php" class="btn btn-primary">Browse Tournaments</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0">Upcoming Matches</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($upcoming_matches->num_rows > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Tournament</th>
                                                    <th>Game</th>
                                                    <th>Opponent</th>
                                                    <th>Scheduled Time</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($match = $upcoming_matches->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($match['tournament_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($match['game_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($match['opponent_name'] ?? 'TBD'); ?></td>
                                                        <td><?php echo date('M d, Y H:i', strtotime($match['scheduled_time'])); ?></td>
                                                        <td>
                                                            <a href="join_match.php?match_id=<?php echo $match['match_id']; ?>" 
                                                               class="btn btn-sm btn-success">Join Match</a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p>No upcoming matches scheduled.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 
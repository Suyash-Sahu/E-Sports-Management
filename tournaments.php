<?php
session_start();
include 'includes/db_connection.php';
include 'includes/header.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;

// Get player's highest level badge if logged in as player
$highest_level = 'Inter-college'; // Default level
if ($user_type == 'player') {
    // First, check if player has any badges
    $sql = "SELECT 1 FROM player_badges WHERE player_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $has_badges = $stmt->get_result()->num_rows > 0;

    if (!$has_badges) {
        // If no badges, assign default Inter-college badge
        $sql = "INSERT INTO player_badges (player_id, badge_id) 
                SELECT ?, badge_id FROM badges WHERE name = 'Inter-college'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }

    // Now get the highest level badge
    $sql = "SELECT b.name as level FROM badges b 
            JOIN player_badges pb ON b.badge_id = pb.badge_id 
            WHERE pb.player_id = ? 
            ORDER BY FIELD(b.name, 'All India', 'State', 'College', 'Inter-college') 
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $highest_level = $result->fetch_assoc()['level'];
    }
}

// Update tournament statuses based on dates
$sql = "UPDATE tournaments 
        SET status = CASE 
            WHEN end_date < NOW() THEN 'ended'
            WHEN start_date <= NOW() AND end_date >= NOW() THEN 'active'
            ELSE status 
        END
        WHERE status IN ('upcoming', 'active')";
$conn->query($sql);

// Check if level column exists in tournaments table
$column_exists = false;
$result = $conn->query("SHOW COLUMNS FROM tournaments LIKE 'level'");
if ($result->num_rows > 0) {
    $column_exists = true;
}

// Get tournaments based on user type and eligibility
$sql = "SELECT t.*, o.organization_name, g.game_name,
        (SELECT COUNT(*) FROM tournament_players tp WHERE tp.tournament_id = t.tournament_id) as current_players
        FROM tournaments t 
        JOIN organizer_profiles o ON t.organizer_id = o.user_id 
        JOIN games g ON t.game_id = g.game_id
        WHERE t.status IN ('upcoming', 'active')"; // Only show upcoming and active tournaments

// Initialize variables
$params = [];
$types = "";
$debug_info = [];

if ($user_type == 'player' && $column_exists) {
    // For players, show tournaments they're eligible for based on their highest level badge
    $sql .= " AND (
        t.level = 'Custom/Open' 
        OR t.level = ? 
        OR (
            t.level = 'College' AND ? IN ('College', 'State', 'All India')
        )
        OR (
            t.level = 'State' AND ? IN ('State', 'All India')
        )
        OR (
            t.level = 'All India' AND ? = 'All India'
        )
    )";
    $params[] = $highest_level;
    $params[] = $highest_level;
    $params[] = $highest_level;
    $params[] = $highest_level;
    $types .= "ssss";
    $debug_info[] = "Player level filter applied: " . $highest_level;
} elseif ($user_type == 'organizer') {
    // For organizers, show all tournaments except their own
    $sql .= " AND t.organizer_id != ?";
    $params[] = $user_id;
    $types .= "i";
    $debug_info[] = "Organizer filter applied: excluding user_id " . $user_id;
}

$sql .= " ORDER BY t.start_date ASC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$tournaments = $stmt->get_result();

// Add debugging information
if ($tournaments->num_rows == 0) {
    echo '<div class="alert alert-warning">';
    echo '<h4>Debug Information:</h4>';
    echo '<p>No tournaments found. Here\'s why:</p>';
    echo '<ul>';
    foreach ($debug_info as $info) {
        echo '<li>' . htmlspecialchars($info) . '</li>';
    }
    echo '<li>SQL Query: ' . htmlspecialchars($sql) . '</li>';
    echo '</ul>';
    echo '</div>';
}
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Available Tournaments</h2>
            <?php if ($user_type == 'player'): ?>
                <p class="text-muted">Your current level: <span class="badge bg-primary"><?php echo $highest_level; ?></span></p>
            <?php endif; ?>
        </div>
        <div class="col-md-4 text-end">
            <?php if ($user_type == 'organizer'): ?>
                <a href="create_tournament.php" class="btn btn-primary">Create Tournament</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <?php if ($tournaments->num_rows > 0): ?>
            <?php while ($tournament = $tournaments->fetch_assoc()): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 tournament-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><?php echo htmlspecialchars($tournament['tournament_name']); ?></h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?php echo htmlspecialchars($tournament['description']); ?></p>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Game:</small>
                                    <p><?php echo htmlspecialchars($tournament['game_name']); ?></p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Level:</small>
                                    <p><span class="badge bg-info"><?php echo htmlspecialchars($tournament['level'] ?? 'Inter-college'); ?></span></p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Format:</small>
                                    <p><?php echo htmlspecialchars($tournament['format'] ?? 'Single Elimination'); ?></p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Organizer:</small>
                                    <p><?php echo htmlspecialchars($tournament['organization_name']); ?></p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Start Date:</small>
                                    <p><?php echo date('M d, Y H:i', strtotime($tournament['start_date'])); ?></p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">End Date:</small>
                                    <p><?php echo date('M d, Y H:i', strtotime($tournament['end_date'])); ?></p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Entry Fee:</small>
                                    <p>₹<?php echo number_format($tournament['entry_fee'], 2); ?></p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Prize Pool:</small>
                                    <p>₹<?php echo number_format($tournament['prize_pool'], 2); ?></p>
                                </div>
                            </div>
                            <div class="progress mb-3">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: <?php echo ($tournament['current_players'] / $tournament['max_players']) * 100; ?>%">
                                    <?php echo $tournament['current_players']; ?>/<?php echo $tournament['max_players']; ?> players
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="tournament_details.php?id=<?php echo $tournament['tournament_id']; ?>" 
                                   class="btn btn-primary">View Details</a>
                                <?php if ($user_type == 'player'): ?>
                                    <a href="join_tournament.php?id=<?php echo $tournament['tournament_id']; ?>" 
                                       class="btn btn-success">Join Tournament</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    No active tournaments available at the moment.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 
<?php
session_start();
include 'includes/db_connection.php';
include 'includes/header.php';

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'organizer') {
    header("Location: login.php");
    exit();
}

// Check if tournament ID is provided
if (!isset($_GET['id'])) {
    header("Location: organizer_dashboard.php");
    exit();
}

$tournament_id = $_GET['id'];
$organizer_id = $_SESSION['user_id'];

// Verify tournament belongs to organizer
$sql = "SELECT * FROM tournaments WHERE tournament_id = ? AND organizer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $tournament_id, $organizer_id);
$stmt->execute();
$tournament = $stmt->get_result()->fetch_assoc();

if (!$tournament) {
    header("Location: organizer_dashboard.php");
    exit();
}

// Get tournament participants
$sql = "SELECT tp.*, pp.full_name, u.email, p.in_game_name, p.game_domain
        FROM tournament_players tp
        JOIN player_profiles pp ON tp.player_id = pp.user_id
        JOIN users u ON pp.user_id = u.user_id
        JOIN players p ON pp.user_id = p.user_id
        WHERE tp.tournament_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$participants = $stmt->get_result();

// Get tournament matches
$sql = "SELECT m.*, 
        p1.in_game_name as player1_name,
        p2.in_game_name as player2_name
        FROM matches m
        LEFT JOIN players p1 ON m.player1_id = p1.player_id
        LEFT JOIN players p2 ON m.player2_id = p2.player_id
        WHERE m.tournament_id = ?
        ORDER BY m.round_number, m.match_id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$matches = $stmt->get_result();
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Tournament: <?php echo htmlspecialchars($tournament['tournament_name']); ?></h2>
        <div>
            <a href="match_results.php?tournament_id=<?php echo $tournament_id; ?>" class="btn btn-primary">Manage Match Results</a>
            <a href="edit_tournament.php?id=<?php echo $tournament_id; ?>" class="btn btn-outline-primary">Edit Tournament</a>
        </div>
    </div>

    <!-- Participants Section -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Participants</h4>
        </div>
        <div class="card-body">
            <?php if ($participants->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>In-Game Name</th>
                                <th>Game Domain</th>
                                <th>Email</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($participant = $participants->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($participant['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['in_game_name']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['game_domain']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $participant['status'] == 'active' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($participant['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">No participants yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Matches Section -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Matches</h4>
        </div>
        <div class="card-body">
            <?php if ($matches->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Round</th>
                                <th>Match</th>
                                <th>Player 1</th>
                                <th>Player 2</th>
                                <th>Status</th>
                                <th>Winner</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($match = $matches->fetch_assoc()): ?>
                                <tr>
                                    <td>Round <?php echo $match['round_number']; ?></td>
                                    <td>Match <?php echo $match['match_id']; ?></td>
                                    <td><?php echo htmlspecialchars($match['player1_name']); ?></td>
                                    <td><?php echo htmlspecialchars($match['player2_name']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $match['status'] == 'scheduled' ? 'info' : 
                                                ($match['status'] == 'ongoing' ? 'warning' : 'success'); 
                                        ?>">
                                            <?php echo ucfirst($match['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($match['status'] == 'completed'): ?>
                                            <?php echo htmlspecialchars($match['winner_id'] == $match['player1_id'] ? $match['player1_name'] : $match['player2_name']); ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="match_results.php?match_id=<?php echo $match['match_id']; ?>" 
                                           class="btn btn-sm btn-primary">Update Result</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">No matches scheduled yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 
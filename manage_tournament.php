<?php
session_start();
require_once 'includes/db_connection.php';

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organizer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$tournament_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$tournament_id) {
    header("Location: my_tournaments.php");
    exit();
}

// Get tournament details
$sql = "SELECT t.*, g.game_name, u.username as organizer_name
        FROM tournaments t 
        JOIN games g ON t.game_id = g.game_id 
        JOIN users u ON t.organizer_id = u.user_id 
        WHERE t.tournament_id = ? AND t.organizer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $tournament_id, $user_id);
$stmt->execute();
$tournament = $stmt->get_result()->fetch_assoc();

if (!$tournament) {
    header("Location: my_tournaments.php");
    exit();
}

// Get tournament matches
$sql = "SELECT m.*, 
        p1.username as player1_name, 
        p2.username as player2_name,
        w.username as winner_name
        FROM matches m
        LEFT JOIN users p1 ON m.player1_id = p1.user_id
        LEFT JOIN users p2 ON m.player2_id = p2.user_id
        LEFT JOIN users w ON m.winner_id = w.user_id
        WHERE m.tournament_id = ?
        ORDER BY m.match_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$matches = $stmt->get_result();

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Manage Tournament: <?php echo htmlspecialchars($tournament['tournament_name']); ?></h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Tournament Details</h5>
                            <p><strong>Game:</strong> <?php echo htmlspecialchars($tournament['game_name']); ?></p>
                            <p><strong>Start Date:</strong> <?php echo date('F j, Y', strtotime($tournament['start_date'])); ?></p>
                            <p><strong>End Date:</strong> <?php echo date('F j, Y', strtotime($tournament['end_date'])); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-<?php echo $tournament['status'] === 'active' ? 'success' : 'info'; ?>">
                                    <?php echo ucfirst($tournament['status']); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5>Actions</h5>
                            <div class="d-grid gap-2">
                                <?php if ($tournament['status'] === 'upcoming'): ?>
                                    <a href="schedule_matches.php?tournament_id=<?php echo $tournament_id; ?>" 
                                       class="btn btn-primary">Schedule Matches</a>
                                <?php endif; ?>
                                <a href="edit_tournament.php?id=<?php echo $tournament_id; ?>" 
                                   class="btn btn-secondary">Edit Tournament</a>
                            </div>
                        </div>
                    </div>

                    <!-- Matches Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Matches</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($matches->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Match Date</th>
                                                <th>Player 1</th>
                                                <th>Player 2</th>
                                                <th>Winner</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($match = $matches->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo date('M d, Y H:i', strtotime($match['match_date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($match['player1_name'] ?? 'TBD'); ?></td>
                                                    <td><?php echo htmlspecialchars($match['player2_name'] ?? 'TBD'); ?></td>
                                                    <td>
                                                        <?php if ($match['winner_id']): ?>
                                                            <span class="badge bg-success">
                                                                <?php echo htmlspecialchars($match['winner_name']); ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Pending</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $status_class = '';
                                                        $status_text = '';
                                                        switch($match['status']) {
                                                            case 'scheduled':
                                                                $status_class = 'bg-info';
                                                                $status_text = 'Scheduled';
                                                                break;
                                                            case 'in_progress':
                                                                $status_class = 'bg-warning';
                                                                $status_text = 'In Progress';
                                                                break;
                                                            case 'completed':
                                                                $status_class = 'bg-success';
                                                                $status_text = 'Completed';
                                                                break;
                                                            default:
                                                                $status_class = 'bg-secondary';
                                                                $status_text = 'Pending';
                                                        }
                                                        ?>
                                                        <span class="badge <?php echo $status_class; ?>">
                                                            <?php echo $status_text; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($match['status'] !== 'completed'): ?>
                                                            <a href="update_match.php?id=<?php echo $match['match_id']; ?>" 
                                                               class="btn btn-sm btn-primary">Update</a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    No matches scheduled yet.
                                </div>
                                <?php if ($tournament['status'] === 'upcoming'): ?>
                                    <a href="schedule_matches.php?tournament_id=<?php echo $tournament_id; ?>" 
                                       class="btn btn-primary">Schedule Matches</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 
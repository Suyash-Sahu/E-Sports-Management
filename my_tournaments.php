<?php
session_start();
require_once 'includes/db_connection.php';

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organizer') {
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['user_id'];
$message = isset($_GET['message']) ? $_GET['message'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Get tournaments for this organizer
$sql = "SELECT t.*, g.game_name, o.organization_name,
        (SELECT COUNT(*) FROM matches m WHERE m.tournament_id = t.tournament_id) as match_count
        FROM tournaments t 
        JOIN games g ON t.game_id = g.game_id
        LEFT JOIN organizer_profiles o ON t.organizer_id = o.user_id
        WHERE t.organizer_id = ?
        ORDER BY t.start_date DESC";

// Debug information
echo "<div class='container mt-3'>";
echo "<div class='alert alert-info'>";
echo "<h4>Debug Information:</h4>";
echo "<p>Organizer ID: " . htmlspecialchars($organizer_id) . "</p>";
echo "<p>SQL Query: " . htmlspecialchars($sql) . "</p>";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$tournaments = $stmt->get_result();

echo "<p>Number of tournaments found: " . $tournaments->num_rows . "</p>";

// Show first tournament data for debugging
if ($tournaments->num_rows > 0) {
    $first_tournament = $tournaments->fetch_assoc();
    echo "<h5>First Tournament Data:</h5>";
    echo "<pre>";
    print_r($first_tournament);
    echo "</pre>";
    // Reset the pointer
    $tournaments->data_seek(0);
}
echo "</div></div>";

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Tournaments</h2>
        <a href="create_tournament.php" class="btn btn-primary">Create New Tournament</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($tournaments->num_rows === 0): ?>
        <div class="alert alert-info">
            You haven't created any tournaments yet. 
            <a href="create_tournament.php" class="alert-link">Create your first tournament</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php while ($tournament = $tournaments->fetch_assoc()): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h4 class="mb-0"><?php echo htmlspecialchars($tournament['tournament_name']); ?></h4>
                        </div>
                        <div class="card-body">
                            <p><strong>Game:</strong> <?php echo htmlspecialchars($tournament['game_name']); ?></p>
                            <p><strong>Organization:</strong> <?php echo htmlspecialchars($tournament['organization_name'] ?? 'Not set'); ?></p>
                            <p><strong>Start Date:</strong> <?php echo date('F j, Y', strtotime($tournament['start_date'])); ?></p>
                            <p><strong>End Date:</strong> <?php echo date('F j, Y', strtotime($tournament['end_date'])); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-<?php 
                                    echo $tournament['status'] === 'active' ? 'success' : 
                                        ($tournament['status'] === 'upcoming' ? 'primary' : 'secondary'); 
                                ?>">
                                    <?php echo ucfirst($tournament['status']); ?>
                                </span>
                            </p>
                            <p><strong>Total Matches:</strong> <?php echo $tournament['match_count']; ?></p>
                            <p><strong>Format:</strong> <?php echo htmlspecialchars($tournament['format'] ?? 'Not set'); ?></p>
                            <p><strong>Level:</strong> <?php echo htmlspecialchars($tournament['level'] ?? 'Not set'); ?></p>
                        </div>
                        <div class="card-footer">
                            <div class="btn-group">
                                <a href="manage_tournament.php?id=<?php echo $tournament['tournament_id']; ?>" 
                                   class="btn btn-primary">Manage</a>
                                <a href="edit_tournament.php?id=<?php echo $tournament['tournament_id']; ?>" 
                                   class="btn btn-secondary">Edit</a>
                                <?php if ($tournament['status'] !== 'ended'): ?>
                                    <form method="POST" action="update_tournament.php" class="d-inline">
                                        <input type="hidden" name="tournament_id" value="<?php echo $tournament['tournament_id']; ?>">
                                        <input type="hidden" name="status" value="ended">
                                        <button type="submit" class="btn btn-warning" 
                                                onclick="return confirm('Are you sure you want to end this tournament?')">
                                            End Tournament
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?> 
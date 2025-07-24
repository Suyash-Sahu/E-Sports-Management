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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tournament_name = $_POST['tournament_name'];
    $game_id = $_POST['game_id'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $registration_deadline = $_POST['registration_deadline'];
    $max_players = $_POST['max_players'];
    $entry_fee = $_POST['entry_fee'];
    $prize_pool = $_POST['prize_pool'];
    $status = $_POST['status'];
    
    // Add error logging
    error_log("Updating tournament: ID=$tournament_id, Name=$tournament_name, Status=$status");
    
    $sql = "UPDATE tournaments SET 
            tournament_name = ?,
            game_id = ?,
            description = ?,
            start_date = ?,
            end_date = ?,
            registration_deadline = ?,
            max_players = ?,
            entry_fee = ?,
            prize_pool = ?,
            status = ?
            WHERE tournament_id = ? AND organizer_id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error = "Error preparing statement: " . $conn->error;
        error_log("Prepare failed: " . $conn->error);
    } else {
        $stmt->bind_param("sisssssiisii", 
            $tournament_name, $game_id, $description, $start_date, $end_date,
            $registration_deadline, $max_players, $entry_fee, $prize_pool,
            $status, $tournament_id, $organizer_id
        );
        
        if ($stmt->execute()) {
            $message = "Tournament updated successfully!";
            // Refresh tournament data
            $sql = "SELECT t.*, g.game_name FROM tournaments t 
                    JOIN games g ON t.game_id = g.game_id 
                    WHERE t.tournament_id = ? AND t.organizer_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $tournament_id, $organizer_id);
            $stmt->execute();
            $tournament = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "Error updating tournament: " . $stmt->error;
            error_log("Execute failed: " . $stmt->error);
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Edit Tournament</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($message)): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $tournament_id; ?>">
                        <div class="mb-3">
                            <label for="tournament_name" class="form-label">Tournament Name</label>
                            <input type="text" class="form-control" id="tournament_name" name="tournament_name" 
                                   value="<?php echo htmlspecialchars($tournament['tournament_name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="game_id" class="form-label">Game</label>
                            <select class="form-select" id="game_id" name="game_id" required>
                                <?php
                                $sql = "SELECT game_id, game_name FROM games ORDER BY game_name";
                                $result = $conn->query($sql);
                                while ($game = $result->fetch_assoc()) {
                                    $selected = ($game['game_id'] == $tournament['game_id']) ? 'selected' : '';
                                    echo "<option value='" . $game['game_id'] . "' " . $selected . ">" . htmlspecialchars($game['game_name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($tournament['description']); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?php echo date('Y-m-d', strtotime($tournament['start_date'])); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="<?php echo date('Y-m-d', strtotime($tournament['end_date'])); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="registration_deadline" class="form-label">Registration Deadline</label>
                            <input type="date" class="form-control" id="registration_deadline" name="registration_deadline" 
                                   value="<?php echo date('Y-m-d', strtotime($tournament['registration_deadline'])); ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="max_players" class="form-label">Maximum Players</label>
                                <input type="number" class="form-control" id="max_players" name="max_players" 
                                       value="<?php echo $tournament['max_players']; ?>" required min="2">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="entry_fee" class="form-label">Entry Fee (₹)</label>
                                <input type="number" class="form-control" id="entry_fee" name="entry_fee" 
                                       value="<?php echo $tournament['entry_fee']; ?>" required min="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="prize_pool" class="form-label">Prize Pool (₹)</label>
                            <input type="number" class="form-control" id="prize_pool" name="prize_pool" 
                                   value="<?php echo $tournament['prize_pool']; ?>" required min="0">
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="upcoming" <?php echo $tournament['status'] == 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                <option value="ongoing" <?php echo $tournament['status'] == 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                <option value="ended" <?php echo $tournament['status'] == 'ended' ? 'selected' : ''; ?>>Ended</option>
                                <option value="cancelled" <?php echo $tournament['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Tournament</button>
                            <a href="manage_participants.php?id=<?php echo $tournament_id; ?>" class="btn btn-outline-primary">Manage Participants</a>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteTournamentModal">
                                <i class="fas fa-trash"></i> Delete Tournament
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Tournament Modal -->
<div class="modal fade" id="deleteTournamentModal" tabindex="-1" aria-labelledby="deleteTournamentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTournamentModalLabel">Confirm Tournament Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this tournament? This action cannot be undone.</p>
                <p><strong>Tournament:</strong> <?php echo htmlspecialchars($tournament['tournament_name']); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="delete_tournament.php" method="POST" style="display: inline;">
                    <input type="hidden" name="tournament_id" value="<?php echo $tournament_id; ?>">
                    <button type="submit" class="btn btn-danger">Delete Tournament</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 
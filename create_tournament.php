<?php
session_start();
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Check organizer's experience
$sql = "SELECT COUNT(*) as hosted_count FROM tournaments WHERE organizer_id = ? AND status = 'completed'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$hosted_count = $result->fetch_assoc()['hosted_count'];

// Determine allowed tournament levels
$allowed_levels = ['Inter-college', 'Custom/Open'];
if ($hosted_count >= 1) {
    $allowed_levels[] = 'State';
}
if ($hosted_count >= 3) {
    $allowed_levels[] = 'All India';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tournament_name = $_POST['tournament_name'];
    $game_id = $_POST['game_id'];
    $description = $_POST['description'];
    $level = $_POST['level'];
    $format = $_POST['format'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $registration_deadline = $_POST['registration_deadline'];
    $max_players = $_POST['max_players'];
    $entry_fee = $_POST['entry_fee'];
    $prize_pool = $_POST['prize_pool'];
    $team_required = $_POST['team_required'];
    
    // Validate tournament level
    if (!in_array($level, $allowed_levels)) {
        $error = "You are not authorized to create tournaments at this level.";
    } else {
        $sql = "INSERT INTO tournaments (organizer_id, game_id, tournament_name, description, start_date, end_date, 
                registration_deadline, max_players, entry_fee, prize_pool, status, level, format, team_required) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'upcoming', ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssssiidsss", 
            $user_id, $game_id, $tournament_name, $description, $start_date, $end_date, 
            $registration_deadline, $max_players, $entry_fee, $prize_pool, $level, $format, $team_required
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Tournament created successfully!";
            header("Location: my_tournaments.php");
            exit();
        } else {
            $error = "Error creating tournament. Please try again.";
        }
    }
}

// Include header after all potential redirects
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Create New Tournament</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="tournament_name" class="form-label">Tournament Name</label>
                            <input type="text" class="form-control" id="tournament_name" name="tournament_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="game_id" class="form-label">Game</label>
                            <select class="form-select" id="game_id" name="game_id" required>
                                <?php
                                $sql = "SELECT game_id, game_name FROM games ORDER BY game_name";
                                $result = $conn->query($sql);
                                while ($game = $result->fetch_assoc()) {
                                    echo "<option value='" . $game['game_id'] . "'>" . htmlspecialchars($game['game_name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="level" class="form-label">Tournament Level</label>
                            <select class="form-select" id="level" name="level" required>
                                <?php foreach ($allowed_levels as $level): ?>
                                    <option value="<?php echo htmlspecialchars($level); ?>"><?php echo htmlspecialchars($level); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">
                                <?php if ($hosted_count < 1): ?>
                                    Host at least 1 tournament to unlock State-level tournaments.
                                <?php elseif ($hosted_count < 3): ?>
                                    Host at least 3 tournaments to unlock All India-level tournaments.
                                <?php endif; ?>
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="format" class="form-label">Tournament Format</label>
                            <select class="form-select" id="format" name="format" required>
                                <option value="Single Elimination">Single Elimination</option>
                                <option value="Double Elimination">Double Elimination</option>
                                <option value="Round Robin">Round Robin</option>
                                <option value="Swiss">Swiss</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="team_required" class="form-label">Team Requirement</label>
                            <select class="form-select" id="team_required" name="team_required" required>
                                <option value="solo">Solo Players Only</option>
                                <option value="team">Teams Only</option>
                                <option value="both">Both Solo and Teams Allowed</option>
                            </select>
                            <small class="text-muted">
                                Choose whether players must register as teams, solo players, or both.
                            </small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="registration_deadline" class="form-label">Registration Deadline</label>
                                <input type="datetime-local" class="form-control" id="registration_deadline" name="registration_deadline" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="max_players" class="form-label">Maximum Players</label>
                                <input type="number" class="form-control" id="max_players" name="max_players" min="2" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="entry_fee" class="form-label">Entry Fee (₹)</label>
                                <input type="number" class="form-control" id="entry_fee" name="entry_fee" min="0" step="0.01" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="prize_pool" class="form-label">Prize Pool (₹)</label>
                                <input type="number" class="form-control" id="prize_pool" name="prize_pool" min="0" step="0.01" required>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Create Tournament</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 
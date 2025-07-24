<?php
require_once 'includes/init.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirect('login.php', 'Please login to create a team', 'error');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $team_name = trim($_POST['team_name']);
    $description = trim($_POST['description']);
    $game = trim($_POST['game']);
    $leader_id = $_SESSION['user_id'];
    
    $errors = [];
    
    // Validate input
    if (empty($team_name)) {
        $errors[] = "Team name is required";
    } elseif (strlen($team_name) > 100) {
        $errors[] = "Team name must be less than 100 characters";
    }
    
    if (empty($game)) {
        $errors[] = "Game is required";
    }
    
    // Check if team name already exists
    $check_sql = "SELECT team_id FROM teams WHERE team_name = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $team_name);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $errors[] = "Team name already exists";
    }
    
    if (empty($errors)) {
        // Create team
        $sql = "INSERT INTO teams (team_name, description, game, leader_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $team_name, $description, $game, $leader_id);
        
        if ($stmt->execute()) {
            $team_id = $conn->insert_id;
            
            // Add leader as first team member
            $member_sql = "INSERT INTO team_members (team_id, user_id, role) VALUES (?, ?, 'leader')";
            $member_stmt = $conn->prepare($member_sql);
            $member_stmt->bind_param("ii", $team_id, $leader_id);
            $member_stmt->execute();
            
            redirect('teams.php', 'Team created successfully');
        } else {
            $errors[] = "Failed to create team";
        }
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Create New Team</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="team_name" class="form-label">Team Name</label>
                            <input type="text" class="form-control" id="team_name" name="team_name" 
                                   value="<?php echo isset($_POST['team_name']) ? htmlspecialchars($_POST['team_name']) : ''; ?>" 
                                   required maxlength="100">
                        </div>
                        
                        <div class="mb-3">
                            <label for="game" class="form-label">Game</label>
                            <select class="form-select" id="game" name="game" required>
                                <option value="">Select a game</option>
                                <option value="League of Legends">League of Legends</option>
                                <option value="Dota 2">Dota 2</option>
                                <option value="Counter-Strike 2">Counter-Strike 2</option>
                                <option value="Valorant">Valorant</option>
                                <option value="Overwatch 2">Overwatch 2</option>
                                <option value="Rocket League">Rocket League</option>
                                <option value="PUBG">PUBG</option>
                                <option value="Fortnite">Fortnite</option>
                                <option value="Apex Legends">Apex Legends</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Team Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            <small class="text-muted">Describe your team's goals, requirements, and any other relevant information.</small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Create Team</button>
                            <a href="teams.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 
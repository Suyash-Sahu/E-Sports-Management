<?php
require_once 'includes/init.php';

// Get all teams with their member count
$sql = "SELECT t.*, 
        COUNT(tm.team_member_id) as member_count,
        u.username as leader_name
        FROM teams t 
        LEFT JOIN team_members tm ON t.team_id = tm.team_id
        LEFT JOIN users u ON t.leader_id = u.user_id
        GROUP BY t.team_id
        ORDER BY t.created_at DESC";
$result = $conn->query($sql);
$teams = $result->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Teams</h2>
            <p class="text-muted">Browse and join esports teams</p>
        </div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="col-md-4 text-end">
                <a href="create_team.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Team
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($message = display_flash_message('success')): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($message = display_flash_message('error')): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php if (empty($teams)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    No teams have been created yet. Be the first to create one!
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($teams as $team): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($team['team_name']); ?></h5>
                            <p class="card-text text-muted">
                                <i class="fas fa-gamepad"></i> <?php echo htmlspecialchars($team['game']); ?>
                            </p>
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-users"></i> <?php echo $team['member_count']; ?> members
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-crown"></i> Leader: <?php echo htmlspecialchars($team['leader_name']); ?>
                                </small>
                            </div>
                            <p class="card-text">
                                <?php echo htmlspecialchars(substr($team['description'], 0, 100)) . '...'; ?>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="team_details.php?id=<?php echo $team['team_id']; ?>" class="btn btn-outline-primary btn-sm">
                                    View Details
                                </a>
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $team['leader_id']): ?>
                                    <?php
                                    // Check if user is already a member
                                    $check_sql = "SELECT * FROM team_members WHERE team_id = ? AND user_id = ?";
                                    $check_stmt = $conn->prepare($check_sql);
                                    $check_stmt->bind_param("ii", $team['team_id'], $_SESSION['user_id']);
                                    $check_stmt->execute();
                                    $is_member = $check_stmt->get_result()->num_rows > 0;
                                    ?>
                                    <?php if (!$is_member): ?>
                                        <form method="POST" action="join_team.php" class="d-inline">
                                            <input type="hidden" name="team_id" value="<?php echo $team['team_id']; ?>">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-user-plus"></i> Join Team
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge bg-success">Member</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 
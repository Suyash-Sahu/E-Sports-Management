<?php
session_start();
require_once 'includes/db_connection.php';

// Get top players with their rankings and statistics
$query = "SELECT 
    u.username,
    pp.full_name,
    c.state,
    c.city,
    p.game_domain,
    pr.rank_points,
    pr.matches_played,
    pr.matches_won,
    pr.matches_lost,
    ROUND((pr.matches_won / NULLIF(pr.matches_played, 0)) * 100, 2) as win_percentage
FROM users u
JOIN player_profiles pp ON u.user_id = pp.user_id
JOIN players p ON u.user_id = p.user_id
JOIN player_rankings pr ON u.user_id = pr.player_id
LEFT JOIN colleges c ON pp.college_id = c.college_id
ORDER BY pr.rank_points DESC
LIMIT 50";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esports Leaderboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .leaderboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .leaderboard-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .leaderboard-table th, .leaderboard-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .leaderboard-table th {
            background-color:rgb(55, 62, 70);
            font-weight: bold;
        }
        .leaderboard-table tr:hover {
            background-color:#1D0B32;
        }
        .rank-1 { color: #FFD700; font-weight: bold; }
        .rank-2 { color: #C0C0C0; font-weight: bold; }
        .rank-3 { color: #CD7F32; font-weight: bold; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="leaderboard-container">
        <h1>Esports Leaderboard</h1>
        <table class="leaderboard-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Location</th>
                    <th>Game</th>
                    <th>Rank Points</th>
                    <th>Matches</th>
                    <th>Win Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $rank = 1;
                while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td class="rank-<?php echo $rank <= 3 ? $rank : ''; ?>"><?php echo $rank; ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['city'] . ', ' . $row['state']); ?></td>
                        <td><?php echo htmlspecialchars($row['game_domain']); ?></td>
                        <td><?php echo $row['rank_points']; ?></td>
                        <td><?php echo $row['matches_played']; ?></td>
                        <td><?php echo $row['win_percentage']; ?>%</td>
                    </tr>
                <?php 
                $rank++;
                endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 
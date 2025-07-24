<?php
require_once 'includes/db_connection.php';

// Get all tournaments
$sql = "SELECT t.*, g.game_name, u.username as organizer_name 
        FROM tournaments t 
        JOIN games g ON t.game_id = g.game_id 
        JOIN users u ON t.organizer_id = u.user_id";
$result = $conn->query($sql);

echo "<h2>All Tournaments</h2>";
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Tournament Name</th><th>Game</th><th>Organizer</th><th>Status</th><th>Start Date</th><th>End Date</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['tournament_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['game_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['organizer_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . date('Y-m-d', strtotime($row['start_date'])) . "</td>";
        echo "<td>" . date('Y-m-d', strtotime($row['end_date'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No tournaments found in the database.";
}
?> 
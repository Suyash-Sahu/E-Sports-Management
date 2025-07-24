<?php
require_once '../includes/db_connection.php';

// Create tournament_participants table
$sql = "CREATE TABLE IF NOT EXISTS tournament_participants (
    participant_id INT PRIMARY KEY AUTO_INCREMENT,
    tournament_id INT NOT NULL,
    user_id INT NOT NULL,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'confirmed', 'eliminated') DEFAULT 'registered',
    FOREIGN KEY (tournament_id) REFERENCES tournaments(tournament_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_participant (tournament_id, user_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table tournament_participants created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?> 
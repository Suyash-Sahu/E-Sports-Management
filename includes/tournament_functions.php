<?php
require_once 'db_connection.php';

function updateTournamentStatus() {
    global $conn;
    
    // Update tournaments that have passed their end date
    $sql = "UPDATE tournaments 
            SET status = 'ended' 
            WHERE end_date < NOW() 
            AND status = 'active'";
    
    $conn->query($sql);
    
    // Get tournaments that need winner selection
    $sql = "SELECT t.*, u.email as organizer_email, u.username as organizer_name 
            FROM tournaments t 
            JOIN users u ON t.organizer_id = u.user_id 
            WHERE t.status = 'ended' 
            AND t.winner_id IS NULL";
    
    $result = $conn->query($sql);
    return $result;
}

function selectTournamentWinner($tournament_id, $winner_id) {
    global $conn;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update tournament with winner
        $sql = "UPDATE tournaments 
                SET winner_id = ?, 
                    status = 'completed' 
                WHERE tournament_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $winner_id, $tournament_id);
        $stmt->execute();
        
        // Get tournament details for points calculation
        $sql = "SELECT * FROM tournaments WHERE tournament_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $tournament_id);
        $stmt->execute();
        $tournament = $stmt->get_result()->fetch_assoc();
        
        // Add points to winner's profile
        $sql = "UPDATE user_profiles 
                SET points = points + ? 
                WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $tournament['prize_pool'], $winner_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        return false;
    }
}

function getTournamentParticipants($tournament_id) {
    global $conn;
    
    $sql = "SELECT u.user_id, u.username, up.points 
            FROM tournament_participants tp 
            JOIN users u ON tp.user_id = u.user_id 
            LEFT JOIN user_profiles up ON u.user_id = up.user_id 
            WHERE tp.tournament_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    return $stmt->get_result();
}
?> 
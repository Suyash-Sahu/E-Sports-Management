<?php
require_once '../includes/db_connection.php';
require_once '../includes/tournament_functions.php';

// Update tournament statuses
updateTournamentStatus();

// Get tournaments that need winner selection
$ended_tournaments = updateTournamentStatus();

// If there are tournaments that need winner selection, send email notifications to organizers
if ($ended_tournaments && $ended_tournaments->num_rows > 0) {
    while ($tournament = $ended_tournaments->fetch_assoc()) {
        $to = $tournament['organizer_email'];
        $subject = "Tournament Winner Selection Required";
        $message = "Dear " . $tournament['organizer_name'] . ",\n\n";
        $message .= "Your tournament '" . $tournament['title'] . "' has ended. ";
        $message .= "Please log in to select the winner and award points.\n\n";
        $message .= "Best regards,\nE-Sports Hub Team";
        
        $headers = "From: noreply@esportshub.com\r\n";
        $headers .= "Reply-To: noreply@esportshub.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        mail($to, $subject, $message, $headers);
    }
}
?> 
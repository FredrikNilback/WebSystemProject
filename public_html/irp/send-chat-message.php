<?php
session_start();
require_once "../../app/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mysqli = getDataBase();

    $case_id = intval($_POST['case_id']);
    $chat_message = trim($_POST['chat_message']);
    $user_id = $_SESSION['user_id'];

    if ($case_id > 0 && !empty($chat_message)) {
        
        // 1. hämta den nuvarande statusen på ärendet så det inte ändras
        $status_query = "SELECT status FROM incident_update WHERE incident_id = ? ORDER BY incident_update_id DESC LIMIT 1";
        $stmt_s = $mysqli->prepare($status_query);
        $stmt_s->bind_param("i", $case_id);
        $stmt_s->execute();
        $current_status = $stmt_s->get_result()->fetch_assoc()['status'] ?? 'pending';
        $stmt_s->close();

        // 2. skapar en ny incident_update 
        $stmt = $mysqli->prepare("INSERT INTO incident_update (incident_id, status, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $case_id, $current_status, $user_id);

        if ($stmt->execute()) {
            $new_update_id = $mysqli->insert_id;

            // 3. spara själva meddelandet i comment i databasen
            $stmt_comment = $mysqli->prepare("INSERT INTO comment (incident_update_id, comment_text) VALUES (?, ?)");
            $stmt_comment->bind_param("is", $new_update_id, $chat_message);
            $stmt_comment->execute();
            $stmt_comment->close();
        }
        $stmt->close();
    }

    $mysqli->close();

    // skickar sen tillbaka användaren till ärendet direkt
    header("Location: cases.php?view=" . $case_id);
    exit();
}
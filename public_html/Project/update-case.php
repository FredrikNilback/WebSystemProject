<?php
session_start();
require_once "../../app/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mysqli = getDataBase();

    $case_id = intval($_POST['case_id']);
    $status = $_POST['status']; // 'pending', 'in progress' eller 'resolved'
    $comment_text = trim($_POST['admin_comment']); // texten från textarea
    $admin_id = $_SESSION['user_id'] ?? 1; // id på den som är inloggad

    if ($case_id > 0) {
        // första är skapa en ny statusuppdatering 
        $stmt = $mysqli->prepare("INSERT INTO incident_update (incident_id, status, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $case_id, $status, $admin_id);

        if ($stmt->execute()) {
            // fånga upp det nya id för just denna uppdatering
            $new_update_id = $mysqli->insert_id;

            // spara kommentaren om det finns någon text 
            if (!empty($comment_text)) {
                $stmt_comment = $mysqli->prepare("INSERT INTO comment (incident_update_id, comment_text) VALUES (?, ?)");
                $stmt_comment->bind_param("is", $new_update_id, $comment_text);
                $stmt_comment->execute();
                $stmt_comment->close();
            }

            // här lägger jag in filer sen

            $stmt->close();
            $mysqli->close();

            // Skicka tillbaka till cases.php
            header("Location: cases.php?view=" . $case_id . "&success=updated");
            exit();
        } else {
            echo "Error: " . $mysqli->error;
        }
    }
}
?>
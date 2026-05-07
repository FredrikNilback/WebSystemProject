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
            $final_comment = !empty($comment_text) ? $comment_text : "System: Status changed to " . ucfirst($status);

            $stmt_comment = $mysqli->prepare("INSERT INTO comment (incident_update_id, comment_text) VALUES (?, ?)");
            $stmt_comment->bind_param("is", $new_update_id, $final_comment);
            $stmt_comment->execute();
            $stmt_comment->close();

                // här läggs filerna in 
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
                $fileName = $_FILES['attachment']['name'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $uniqueFileName = "update_" . $new_update_id . "_" . time() . "." . $fileExt;
                
                // använder den absoluta sökvägen som fungerade 
                $uploadFolder = "/space1/home/viktha25/uploads/";
                $destination = $uploadFolder . $uniqueFileName;

                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $destination)) {
                    $stmt_file = $mysqli->prepare("INSERT INTO attachment (incident_update_id, attachment_file_path) VALUES (?, ?)");
                    $stmt_file->bind_param("is", $new_update_id, $uniqueFileName);
                    $stmt_file->execute();
                    $stmt_file->close();
                }
            }
            // slut på filhantering

            $stmt->close();
            $mysqli->close();

            // skicka tillbaka till cases.php
            header("Location: cases.php?view=" . $case_id . "&success=updated");
            exit();
        } else {
            echo "Error: " . $mysqli->error;
        }
    }
}
?>
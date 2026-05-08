<?php
    session_start();
    require_once "../../app/db.php";
    $userId = $_SESSION['user_id'];
    if(!isset($userId)) {
        header('Location: unauthorized.php');
        exit();
    }
    updateLastSeen($userId);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $mysqli = getDataBase();

        $caseId = intval($_POST['case_id']) ?? NULL;
        $message = trim($_POST['chat_message']) ?? NULL;

        if ($caseId != NULL && $message != NULL && isset($userId)) {
            // Skickar tillbaka användaren till ärendet direkt
            if (sendMessage($caseId, $message, $userId)) {
                header("Location: cases.php?view=" . $caseId);
                exit(); 
            }
        }
    }
?>

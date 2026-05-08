<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: unauthorized.php');
        exit();
    }
    require_once "../../app/db.php";
    updateLastSeen($_SESSION['user_id']);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        require_once "../../app/incident-service.php";

        // 1. KONTROLLERA FILER (Samma logik som förut)
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
            $fileName = $_FILES['attachment']['name'];
            $fileSize = $_FILES['attachment']['size'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed = array('pdf', 'jpg', 'jpeg', 'png');

            if (!in_array($fileExt, $allowed)) {
                header("Location: new-case.php?error=filetype");
                exit();
            }
            if ($fileSize > 5000000) { 
                header("Location: new-case.php?error=filesize");
                exit();
            }
        }
        
        $result = submitIncident($_POST, $_FILES['attachment'] ?? NULL, $_SESSION['user_id']);
        if ($result) {
            // 7. SKICKA TILLBAKA ANVÄNDAREN 
            header("Location: new-case.php?success=true&id=" . $result);
            exit();
        }

        // Om något går fel
        header("Location: new-case.php?error=" . "true");
        exit();
    }
?>

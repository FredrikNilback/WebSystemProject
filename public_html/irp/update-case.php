<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: unauthorized.php');
        exit();
    }
    require_once "../../app/db.php";
    updateLastSeen($_SESSION['user_id']);
    $page = basename($_SERVER['PHP_SELF']);
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $browserName = "Unknown";

    if (strpos($userAgent, 'Edg') !== false) {
        $browserName = "Edge";
    }   elseif (strpos($userAgent, 'OPR') !== false) {
        $browserName = "Opera";
    }   elseif (strpos($userAgent, 'Firefox') !== false) {
            $browserName = "Firefox";
    }   elseif (strpos($userAgent, 'Chrome') !== false) {
            $browserName = "Chrome";
    }   elseif (strpos($userAgent, 'Safari') !== false) {
            $browserName = "Safari";
    }

    $browserId = getBrowserId($browserName);
    $trackingId = trackVisit($page, $browserId, $ip);
    trackUserVisit($trackingId, $_SESSION['user_id']);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        require_once "../../app/incident-service.php";

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
        $result = updateIncident($_POST, $_FILES['attachment'] ?? NULL, $_SESSION['user_id']);
        if ($result) {
            // skicka tillbaka till cases.php 
            header("Location: cases.php?view=" . $result . "&success=updated");
            exit();
        }

        // Om något går fel
        header("Location: cases.php?error=true");
        exit();
    }
?>

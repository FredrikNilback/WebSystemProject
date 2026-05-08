<?php
    session_start();
    require_once "../../app/db.php";
    $userId = $_SESSION['user_id'];
    if(!isset($userId)) {
        header('Location: unauthorized.php');
        exit();
    }
    updateLastSeen($userId);
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

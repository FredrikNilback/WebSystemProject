<?php
    require_once '../../app/logout.php';
    require_once '../../app/db.php';
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
    if (isset($_SESSION['user_id'])) {
        trackUserVisit($trackingId, $_SESSION['user_id']);
    }
    
    logout();
    header('index.php');
    exit;
?>

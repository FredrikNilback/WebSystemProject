<?php
session_start();
// kom ihåg att lägga till kontroll: if(!isset($_SESSION['user_id'])) die('Access denied');

if (isset($_GET['name'])) {
    $fileName = basename($_GET['name']); // basename för säkerhet så man inte kan "backa" ut ur mappen
    $filePath = "../../uploads/" . $fileName;

    if (file_exists($filePath)) {
        // talar om för webbläsaren vilken typ av fil det är
        $type = mime_content_type($filePath);
        header("Content-Type: " . $type);
        header("Content-Length: " . filesize($filePath));
        
        // läs filen och skicka till webbläsaren
        readfile($filePath);
        exit;
    }
}
echo "No file was found.";
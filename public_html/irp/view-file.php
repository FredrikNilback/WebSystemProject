<?php
session_start();
// kommer inte in utan inlogg
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die("Access denied: You must be logged in to view attachments.");
}

if (isset($_GET['name'])) {
    $fileName = basename($_GET['name']); // basename för säkerhet så man inte kan "backa" ut ur mappen
    $filePath = "../../uploads/" . $fileName;

    if (file_exists($filePath)) {
        // talar om för webbläsaren vilken typ av fil det är
        $type = mime_content_type($filePath);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($type, $allowedTypes)) {
            die("Security error: Illegal file type.");
        }
        header("Content-Type: " . $type);
        header("Content-Length: " . filesize($filePath));
        header("X-Content-Type-Options: nosniff");
        // läs filen och skicka till webbläsaren
        readfile($filePath);
        exit;
    }
}
echo "No file was found.";
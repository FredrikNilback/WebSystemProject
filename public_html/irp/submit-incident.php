<?php
session_start();
require_once "../../app/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mysqli = getDataBase();

    // 1. HÄMTA DATA
    $occurance = $_POST['incident_time'];
    $description = $_POST['description'];
    $incident_type_id = $_POST['threats'];
    $incident_severity = $_POST['urgency'];
    $selected_assets = $_POST['asset_id'] ?? []; 
    
    // kollar både fältet och sessionen
    $userId = $_SESSION['user_id'] ?? NULL; // Fredrik säger: stor säkerhetsrisk att lita på POST för user_id. vem som helst kan skicka en post med vilket id som helst.
    if (!$userId) {
        header('Location: unauthorized.php');
        exit();
    }

    // 2. KONTROLLERA FILER (Samma logik som förut)
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

    // 3. SPARA INCIDENTEN
    $stmt = $mysqli->prepare("INSERT INTO incident (incident_type_id, description, incident_severity, occurance) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $incident_type_id, $description, $incident_severity, $occurance);

    if ($stmt->execute()) {
        $new_incident_id = $mysqli->insert_id;

        // 4. SKAPA STATUSUPPDATERING (Viktigt för cases.php!)
        $stmt_status = $mysqli->prepare("INSERT INTO incident_update (incident_id, status, user_id) VALUES (?, 'pending', ?)");
        $stmt_status->bind_param("ii", $new_incident_id, $userId);
        $stmt_status->execute();
        
        $new_update_id = $mysqli->insert_id; 

        // 5. HANTERA FILUPPLADDNING
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
            $fileName = $_FILES['attachment']['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $uniqueFileName = "update_" . $new_update_id . "_" . time() . "." . $fileExt;
            
            $uploadFolder = __DIR__ . "/../../uploads/";
            $destination = $uploadFolder . $uniqueFileName;

            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $destination)) {
                $stmt_file = $mysqli->prepare("INSERT INTO attachment (incident_update_id, attachment_file_path) VALUES (?, ?)");
                $stmt_file->bind_param("is", $new_update_id, $uniqueFileName);
                $stmt_file->execute();
                $stmt_file->close();
            }
        }
        $stmt_status->close();

        // 6. SPARA AFFECTED ASSETS (Many-to-Many loopen)
        if (!empty($selected_assets) && is_array($selected_assets)) {
            foreach ($selected_assets as $asset_id) {
                $stmt_asset = $mysqli->prepare("INSERT INTO affected_asset (asset_id, incident_id) VALUES (?, ?)");
                $stmt_asset->bind_param("ii", $asset_id, $new_incident_id);
                $stmt_asset->execute();
                $stmt_asset->close();
            }
        }

        $stmt->close();
        $mysqli->close();

        // 7. SKICKA TILLBAKA ANVÄNDAREN 
        header("Location: new-case.php?success=true&id=" . $new_incident_id);
        exit();

    } else {
        // Om något går helt fel vid första steget
        header("Location: new-case.php?error=database");
        exit();
    }
}
?>
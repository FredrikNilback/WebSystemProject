<?php
session_start();
require_once "../../app/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mysqli = getDataBase();

    // hämta data från formet
    $occurance = $_POST['incident_time'];
    $description = $_POST['description'];
    $incident_type_id = $_POST['threats'];
    $incident_severity = $_POST['urgency'];
    $user_id = $_POST['id_nr']; // hämtas inte just nu, vet ej hur den ska läggas till
    $selected_asset_id = $_POST['asset_id']; // rullis asset

    // kontrollerar filerna
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
        // glöm ej att här ska jag npg lägga till move_uploaded_file om jag vill spara den på servern!
    }

    // sparar incidenten
    // 4 parametrar int, string string string
    $stmt = $mysqli->prepare("INSERT INTO incident (incident_type_id, description, incident_severity, occurance) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $incident_type_id, $description, $incident_severity, $occurance);

    if ($stmt->execute()) {
        // id som skapades sparas här 
        $new_incident_id = $mysqli->insert_id;
        // Vi använder kolumnnamnet 'incident_id' som matchar din tabell i bilden
        $stmt_status = $mysqli->prepare("INSERT INTO incident_update (incident_id, status, user_id) VALUES (?, 'pending', ?)");

        // Vi skickar in värdet från $new_incident_id (det som i din kod sparades från $mysqli->insert_id)
        $stmt_status->bind_param("ii", $new_incident_id, $user_id);
        $stmt_status->execute();
        $stmt_status->close();

        // här sparas 'affected_assets'
        if (!empty($selected_asset_id)) {
            $stmt_asset = $mysqli->prepare("INSERT INTO affected_assets (asset_id, incident_id) VALUES (?, ?)");
            $stmt_asset->bind_param("ii", $selected_asset_id, $new_incident_id);
            $stmt_asset->execute();
            $stmt_asset->close();
        }

        $stmt->close();
        $mysqli->close();

        // skickar till success med det nya id
        header("Location: new-case.php?success=true&id=" . $new_incident_id);
        exit();
    } else {
        echo "Database Error: " . $stmt->error;
    }
}
?>
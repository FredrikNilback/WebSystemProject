<?php
    function submitIncident($post, $files, $userId) {
        require_once __DIR__ . "/db.php";

        $mysqli = getDataBase();
        $mysqli->begin_transaction();

        try {
            // 2. HÄMTA DATA
            $userId = (int)$userId;

            $occurrence = $post['incident_time']; 
            $description = $post['description'];
            $affectedAssets = $post['asset_id'] ?? []; 
            $incidentTypeId = (int)($post['threats']);

            $incidentSeverity = $post['urgency'];
            $allowedSeverities = ['critical', 'high', 'medium', 'low'];

            if (!in_array($incidentSeverity, $allowedSeverities)) {
                throw new Exception("Unexpected severity", 1);
            }

            // 3. SPARA INCIDENTEN
            $incidentId = insertIncident($mysqli, $incidentTypeId, $description, $incidentSeverity, $occurrence);
            // 4. SKAPA STATUSUPPDATERING (Viktigt för cases.php!)
            $updateId = insertUpdate($mysqli, $incidentId, $userId);


            // 5. HANTERA FILUPPLADDNING
            if (isset($files) && $files['error'] === 0) {
                $fileName = $files['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $uniqueFileName = "update_" . $updateId . "_" . time() . "." . $fileExtension;

                $uploadFolder = __DIR__ . "/../uploads/";
                $destination = $uploadFolder . $uniqueFileName;

                if (move_uploaded_file($files['tmp_name'], $destination)) {
                    insertAttachment($mysqli, $updateId, $uniqueFileName);
                } else {
                    throw new Exception("Attachment upload unsuccessful!", 1);
                }
            }

            if (!empty($affectedAssets) && is_array($affectedAssets)) {
                insertAffectedAssets($mysqli, $affectedAssets, $incidentId);
            }

            $mysqli->commit();
            return $incidentId;
        } catch (Exception $e) {
            $mysqli->rollback();
            if (isset($destination) && file_exists($destination)) {
                unlink($destination);
            }
            return FALSE;
        } finally {
            $mysqli->close();
        } 
    }
?>

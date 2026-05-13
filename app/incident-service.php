<?php
    require_once __DIR__ . "/db.php";
    date_default_timezone_set('Europe/Stockholm');

    function submitIncident($post, $files, $userId) {
        $mysqli = getDataBase();
        $mysqli->begin_transaction();

        try {
            // 2. HÄMTA DATA
            $userId = (int)$userId;

            $occurrence = $post['incident_time'];
            $occurrenceDate = new DateTime($occurrence);
            $now = new DateTime();

            if ($occurrenceDate > $now) {
                throw new Exception("Incident time cannot be in the future", 1);
            }

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
            $uploadedFiles = [];

            if (
                isset($files['name']) &&
                is_array($files['name']) &&
                count($files['name']) > 0
            ) {
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                $allowedMimeTypes = [
                    'image/jpeg',
                    'image/png',
                    'application/pdf'
                ];

                $uploadFolder = __DIR__ . "/../uploads/";

                foreach ($files['name'] as $index => $fileName) {
                    if ($files['error'][$index] === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    if ($files['error'][$index] !== UPLOAD_ERR_OK) {
                        throw new Exception("Attachment upload unsuccessful!");
                    }

                    $tmpName = $files['tmp_name'][$index];

                    $fileExtension = strtolower(
                        pathinfo($fileName, PATHINFO_EXTENSION)
                    );

                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $tmpName);
                    finfo_close($finfo);

                    if (
                        !in_array($fileExtension, $allowedExtensions) ||
                        !in_array($mimeType, $allowedMimeTypes)
                    ) {
                        throw new Exception("Invalid file type");
                    }

                    $uniqueFileName =
                        "update_" .
                        $updateId .
                        "_" .
                        time() .
                        "_" .
                        $index .
                        "." .
                        $fileExtension;

                    $destination = $uploadFolder . $uniqueFileName;

                    if (move_uploaded_file($tmpName, $destination)) {
                        insertAttachment($mysqli, $updateId, $uniqueFileName);
                        $uploadedFiles[] = $destination;
                    } else {
                        throw new Exception("Attachment upload unsuccessful!");
                    }
                }
            }

            if (!empty($affectedAssets) && is_array($affectedAssets)) {
                insertAffectedAssets($mysqli, $affectedAssets, $incidentId);
            }

            $mysqli->commit();
            return $incidentId;
        } catch (Exception $e) {
            $mysqli->rollback();
            if (!empty($uploadedFiles)) {
                foreach ($uploadedFiles as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
            }
            return FALSE;
        } finally {
            $mysqli->close();
        } 
    }

    function updateIncident($post, $files, $userId) {
        $mysqli = getDataBase();
        $mysqli->begin_transaction();
        $uploadedFiles = []; 

        try {
            $userId = (int)$userId;
            $caseId = (int)($post['case_id']);
            $comment = trim($post['admin_comment'] ?? ''); 
            $status = $post['status'];

            $allowedStatuses = ['pending', 'in progress', 'resolved'];
            if (!in_array($status, $allowedStatuses)) {
                throw new Exception("Unexpected status");
            }

            $updateId = insertUpdate($mysqli, $caseId, $userId, $status);
            
            $finalComment = !empty($comment) ? $comment : "System: Status changed to " . ucfirst($status);
            insertComment($mysqli, $updateId, $finalComment);

            if (isset($files['name']) && is_array($files['name'])) {
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                $uploadFolder = __DIR__ . "/../uploads/";

                foreach ($files['name'] as $index => $fileName) {
                    // Hoppa över om ingen fil valts på denna index-plats
                    if ($files['error'][$index] === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    if ($files['error'][$index] !== UPLOAD_ERR_OK) {
                        throw new Exception("Attachment upload unsuccessful!");
                    }

                    $tmpName = $files['tmp_name'][$index];
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $tmpName);
                    finfo_close($finfo);

                    if (!in_array($fileExtension, $allowedExtensions) || !in_array($mimeType, $allowedMimeTypes)) {
                        throw new Exception("Invalid file type: " . $fileName);
                    }

                    
                    $uniqueFileName = "update_" . $updateId . "_" . time() . "_" . $index . "." . $fileExtension;
                    $destination = $uploadFolder . $uniqueFileName;

                    if (move_uploaded_file($tmpName, $destination)) {
                        insertAttachment($mysqli, $updateId, $uniqueFileName);
                        $uploadedFiles[] = $destination; 
                    } else {
                        throw new Exception("Could not move uploaded file!");
                    }
                }
            }

            $mysqli->commit();
            return $caseId;
        } catch (Exception $e) {
            $mysqli->rollback();
            
            foreach ($uploadedFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            return FALSE;
        } finally {
            $mysqli->close();
        }
    }
?>

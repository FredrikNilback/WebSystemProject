<?php
    require_once __DIR__ . "/db.php";

    function submitIncident($post, $files, $userId) {
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
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                $allowedMimeTypes = [
                    'image/jpeg',
                    'image/png',
                    'application/pdf'
                ];
                
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $files['tmp_name']);
                finfo_close($finfo);
                
                if (!in_array($fileExtension, $allowedExtensions) ||
                    !in_array($mimeType, $allowedMimeTypes)) {
                    throw new Exception("Invalid file type");
                }
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

    function updateIncident($post, $files, $userId) {
        $mysqli = getDataBase();
        $mysqli->begin_transaction();

        try {
            $userId = (int)$userId; // id på den som är inloggad
            $caseId = (int)($post['case_id']);
            $comment = trim($post['admin_comment']); // texten från textarea
            $status = $post['status']; // 'pending', 'in progress' eller 'resolved'
            $allowedStatuses = ['pending', 'in progress', 'resolved'];
            if (!in_array($status, $allowedStatuses)) {
                throw new Exception("Unexpected status", 1);
            }
            
            // första är skapa en ny statusuppdatering
            // fånga upp det nya id för just denna uppdatering
            $updateId = insertUpdate($mysqli, $caseId, $userId, $status);

            // spara kommentaren om det finns någon text 
            $finalComment = !empty($comment) ? $comment : "System: Status changed to " . ucfirst($status);
            insertComment($mysqli, $updateId, $finalComment);

            // här läggs filerna in 
            if (isset($files) && $files['error'] === 0) {
                $fileName = $files['name'];
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                $allowedMimeTypes = [
                    'image/jpeg',
                    'image/png',
                    'application/pdf'
                ];

                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $files['tmp_name']);
                finfo_close($finfo);

                if (!in_array($fileExtension, $allowedExtensions) ||
                    !in_array($mimeType, $allowedMimeTypes)) {
                    throw new Exception("Invalid file type");
                }
                $uniqueFileName = "update_" . $updateId . "_" . time() . "." . $fileExtension;
                
                $uploadFolder = __DIR__ . "/../uploads/";
                $destination = $uploadFolder . $uniqueFileName;

                if (move_uploaded_file($files['tmp_name'], $destination)) {
                    insertAttachment($mysqli, $updateId, $uniqueFileName);
                } else {
                    throw new Exception("Attachment upload unsuccessful!", 1);
                }
            }
            // slut på filhantering

            $mysqli->commit();
            return $caseId;
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

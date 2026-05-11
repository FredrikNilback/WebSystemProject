<?php 
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: unauthorized.php');
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
    $activePage="new-case";  

    $incidentTypes = getIncidentTypes();
    $assets = getAssets();
?>

<?php require_once 'includes/header.php' ?>
    <div class="content">
        <main>
            <div id="success-message" class="success-banner"></div>

            <form action="submit-incident.php" method="post" enctype="multipart/form-data">
                <h2>Incident Report</h2>
                <div>
                    <label>Date and time of the incident</label>
                    <input type="datetime-local" name="incident_time" required>
                </div>

                <input type="hidden" name="reporter_email" value="<?= $_SESSION['user_email'] ?>">

                <div>
                    <label>Describe incident</label>
                    <textarea name="description" class="text-box" required></textarea>
                </div>

                <div>
                    <label>File attachments (pdf or image*)</label>
                    <input type="file" name="attachment" accept=".pdf, image/*">
                </div>

                <div class="form-row">
                    <div class="input-group">
                        <label>Incident type</label>
                        <select name="threats" required>
                            <option value="">-Select-</option>
                            <?php foreach($incidentTypes as $incidentType):?>
                                <option value="<?= $incidentType['incident_type_id'] ?>"><?= $incidentType['incident_type_name'] ?></option>;
                            <?php endforeach;?>
                        </select>
                    </div>
                
                    <div class="input-group">
                        <label>Affected Assets</label>
                        <div class="checkbox-container">
                            <?php foreach($assets as $asset): ?>
                                    <label class="custom-checkbox">
                                        <input type="checkbox" name="asset_id[]" value="<?= $asset['asset_id'] ?>">
                                        <?= $asset['asset_name'] ?>
                                    </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <h2>Severity</h2>

                <div class="urgency-selection">
                    <label class="custom-radio"> Critical
                        <input type="radio" name="urgency" value="critical" required>
                        <span class="checkmark"></span>
                    </label>
                    <label class="custom-radio"> High
                        <input type="radio" name="urgency" value="high" required>
                        <span class="checkmark"></span>
                    </label>
                    <label class="custom-radio"> Medium
                        <input type="radio" name="urgency" value="medium" required>
                        <span class="checkmark"></span>
                    </label>
                    <label class="custom-radio"> Low
                        <input type="radio" name="urgency" value="low" required>
                        <span class="checkmark"></span>
                    </label>
                </div>

                <button type="submit">Submit Report</button>
                
            </form>
        </main>
    </div>
<?php require_once 'includes/footer.php' ?>

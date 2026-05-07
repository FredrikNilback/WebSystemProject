<?php 
session_start();  
$activePage="new-case";  
require_once "../../app/db.php"; 

$mysqli = getDataBase();
$type_result = $mysqli->query("SELECT * FROM incident_type");

$asset_result = $mysqli->query("SELECT * FROM asset");
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

                <input type="hidden" name="id_nr" value="<?= $_SESSION['user_id'] ?>">
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
                            <?php
                            if ($type_result) {
                                while ($row = $type_result->fetch_assoc()) {
                                    echo '<option value="' . $row['incident_type_id'] . '">' . $row['incident_type_name'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                
                    <div class="input-group">
                        <label>Affected Assets</label>
                        <div class="checkbox-container">
                            <?php 
                            if ($asset_result) {
                                $asset_result->data_seek(0);
                                while ($row = $asset_result->fetch_assoc()) {
                                    ?>
                                    <label class="custom-checkbox">
                                        <input type="checkbox" name="asset_id[]" value="<?php echo $row['asset_id']; ?>">
                                        <?php echo $row['asset_name']; ?>
                                    </label>
                                    <?php
                                }
                            }
                            ?>
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

<script>
    // kollar ifall adressen innehåller success=true
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('success')) {
        const incidentId = urlParams.get('id');
        const message = "Your form has been sent. Incident nr: " + incidentId;
        
        // alerten som kommer upp
        alert(message);
        
        // visar ett meddelande
        const successDiv = document.getElementById('success-message');
        if (successDiv) {
            successDiv.style.display = 'block';
            successDiv.innerHTML = message; // lägger in texten
        }
    }
</script>
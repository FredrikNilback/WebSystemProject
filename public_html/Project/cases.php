<?php 
session_start();  
$activePage="cases";  
require_once "../../app/db.php"; 

$mysqli = getDataBase();

$query = "SELECT i.*, 
                 t.incident_type_name, 
                 u.status, 
                 a.asset_name
          FROM incident i 
          JOIN incident_type t ON i.incident_type_id = t.incident_type_id 
          -- hämtar senaste statusen 
          LEFT JOIN incident_update u ON u.incident_id = i.incident_id 
          AND u.incident_update_id = (
              SELECT MAX(incident_update_id) 
              FROM incident_update 
              WHERE incident_id = i.incident_id
          )
          -- hämtar asset namnet via kopplingstabell
          LEFT JOIN affected_assets aa ON i.incident_id = aa.incident_id
          LEFT JOIN asset a ON aa.asset_id = a.asset_id
          ORDER BY i.incident_id DESC";

$result = $mysqli->query($query);
$incidents = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $incidents[] = $row;
    }
}

// kolla om ett specifikt ärende är valt (via URL:en ?view=ID)
$selectedCase = null;
if (isset($_GET['view'])) {
    $viewId = intval($_GET['view']);
    foreach ($incidents as $i) {
        if ($i['incident_id'] == $viewId) {
            $selectedCase = $i;
            break;
        }
    }
}

// räkna statusar för boxarna 
$counts = ['pending' => 0, 'in progress' => 0, 'resolved' => 0]; 
foreach ($incidents as $i) { 
    if (isset($counts[$i['status']])) { 
        $counts[$i['status']]++; 
    } 
} 
?>
<?php require_once 'includes/header.php' ?>
    <div class="content"> 
        <aside>
            <h2>Incident Overview</h2>
            <h5>Click on the statuses you want to filter</h5>

            <div onclick="filterTable('all')" class="status-card">
                <h3>Total Cases</h3>
                <div><?php echo count($incidents); ?></div>
            </div>
            <div onclick="filterTable('pending')" class="status-card">
                <h3>Pending</h3>
                <div><?php echo $counts['pending']; ?></div>
            </div>
            <div onclick="filterTable('in progress')" class="status-card">
                <h3>In Progress</h3>
                <div><?php echo $counts['in progress']; ?></div>
            </div>
            <div onclick="filterTable('resolved')" class="status-card">
                <h3>Resolved</h3>
                <div><?php echo $counts['resolved']; ?></div>
            </div>
            

            
            <div>
                <table>
                    <thead>
                        <tr>
                            <th>Incident.nr</th>
                            <th>Affected Asset</th>
                            <th>Status</th>
                            <th>Assign Case</th>
                            <th>Severity</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <?php if (empty($incidents)): ?>
                            <tr><td colspan="5">No incidents found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($incidents as $incident): ?>
                                <tr class="incident-row <?php echo $incident['status']; ?>">
                                    <!-- Incident.nr -->
                                    <td>
                                        <a href="cases.php?view=<?php echo $incident['incident_id']; ?>">
                                            #<?php echo $incident['incident_id']; ?>
                                        </a>
                                    </td>

                                    <!-- Affected Asset -->
                                    <td><?php echo $incident['asset_name'] ?? 'N/A'; ?></td>

                                    <!-- Status -->
                                    <td><?php echo $incident['status']; ?></td>

                                    <!-- Assign Case -->
                                    <td>
                                        <input type="text" placeholder="Admin ID" style="width: 60px;">
                                    </td>

                                    <!-- Severity -->
                                    <td><?php echo $incident['incident_severity']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </aside>
        
        <main>
            <article>
                <div>
                    <h2>Incident Detail: <?php echo $selectedCase ? "#".$selectedCase['incident_id'] : "Select a case"; ?></h2>
                </div>
        
                <div class="chat-log">
                    <p>No messages.</p>
                </div>
        
                <div class="chat-input">
                    <input type="text" placeholder="Write...">
                    <button type="button">Send</button>
                </div>
            </article>
            <section class="action-panel">

        
                <form action="update-case.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="case_id" value="<?php echo $selectedCase ? $selectedCase['incident_id'] : ''; ?>">
            
                    <div class="case-details-box">
                        <?php if ($selectedCase): ?>
                            <p><strong>Incident Description:</strong> <?php echo $selectedCase['description']; ?></p>
                        <?php else: ?>
                            <p>Select a case in the list to view the report details here.</p>
                        <?php endif; ?>
                    </div>

                    <div class="input-group">
                        <span>Change Status:</span>
                        <select name="status">
                            <option value="pending" <?php if($selectedCase && $selectedCase['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                            <option value="in progress" <?php if($selectedCase && $selectedCase['status'] == 'in progress') echo 'selected'; ?>>In Progress</option>
                            <option value="resolved" <?php if($selectedCase && $selectedCase['status'] == 'resolved') echo 'selected'; ?>>Resolved</option>
                        </select>
                    </div>
            
                     <div class="input-group">
                        <span>Add Comment:</span>
                        <textarea name="admin_comment" placeholder="Write comment..."></textarea>
                    </div>
            
                    <div class="input-group">
                        <span>Attach Document:</span>
                        <input type="file" name="admin_file">
                    </div>
            
                    <button type="submit" class="update-btn">Update Incident</button>
                </form>
            </section>
        </main>
    </div>
<?php require_once 'includes/footer.php' ?>
    
    <script>
        let activeFilters = []; 

        function filterTable(status) {
            const cards = document.querySelectorAll('.status-card');
            const rows = document.querySelectorAll('.incident-row');

            if (status === 'all') {
                activeFilters = [];
                cards.forEach(c => c.classList.remove('active'));
            } else {
                // hittar boxen och ändrar färg på den 
                // letar efter den box som har exakt det status-ordet i sin onclick grej
                cards.forEach(card => {
                    if (card.getAttribute('onclick').includes("'" + status + "'")) {
                        card.classList.toggle('active');
                    }
                });

                    // listan uppdateras med valda filter
                if (activeFilters.includes(status)) {
                    activeFilters = activeFilters.filter(s => s !== status);
                } else {
                    activeFilters.push(status);
                }
            }

                // visa/dölj rader
            rows.forEach(row => {
                if (activeFilters.length === 0) {
                    row.style.display = ''; 
                } else {
                    // om raden har klassen t.ex. 'Pending' eller 'In-Progress'
                    const matches = activeFilters.some(s => row.classList.contains(s));
                    row.style.display = matches ? '' : 'none';
                }
            });
        }
    </script>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: unauthorized.php');
}
require_once '../../app/db.php';
updateLastSeen($_SESSION['user_id']);
$activePage="cases";

$mysqli = getDataBase();

// hämta alla användare som kan tilldelas ärenden
$user_query = "SELECT user_id, username FROM user WHERE user_role IN ('administrator', 'responder') ORDER BY username ASC";
$user_result = $mysqli->query($user_query);
$all_users = [];
if ($user_result) {
    while($u = $user_result->fetch_assoc()) {
        $all_users[] = $u;
    }
}

// detta är för att assigna ett case   
if (isset($_POST['assign_submit'])) {
    $inc_id = 0;
    
    // Vi kollar både URL och POST efter ID:t
    if (isset($_GET['assign_id'])) {
        $inc_id = intval($_GET['assign_id']);
    } elseif (isset($_POST['incident_id'])) {
        $inc_id = intval($_POST['incident_id']);
    }
    
    // om inget id, ett felmed.
    if ($inc_id === 0) {
        die("No ID-number found.");
    }

    $new_owner = !empty($_POST['new_owner_id']) ? intval($_POST['new_owner_id']) : $_SESSION['user_id'];
    $status = "in progress"; 

    $stmt_upd = $mysqli->prepare("INSERT INTO incident_update (incident_id, status, user_id) VALUES (?, ?, ?)");
    $stmt_upd->bind_param("isi", $inc_id, $status, $new_owner);
    
    if ($stmt_upd->execute()) {
        $new_update_id = $mysqli->insert_id;
        
        $system_msg = "System: Case assigned to user #" . $new_owner;
        $stmt_com = $mysqli->prepare("INSERT INTO comment (incident_update_id, comment_text) VALUES (?, ?)");
        $stmt_com->bind_param("is", $new_update_id, $system_msg);
        $stmt_com->execute();
        
        // skicka användaren tillbaka till vyn för det aktuella ärendet
        header("Location: cases.php?view=" . $inc_id);
        exit();
    } else {
        die("Database Error: " . $stmt_upd->error);
    }
}



// 1. hämta info från sessionen
$user_id = $_SESSION['user_id']; 
$user_role = $_SESSION['user_role'];

// 2. listar kolumnerna istället för att använda *
$query = "SELECT i.incident_id, i.description, i.incident_severity, i.occurrence,
                 t.incident_type_name, 
                 u.status, 
                 GROUP_CONCAT(DISTINCT a.asset_name SEPARATOR ', ') AS asset_name
          FROM incident i 
          JOIN incident_type t ON i.incident_type_id = t.incident_type_id 
          LEFT JOIN incident_update u ON u.incident_id = i.incident_id 
          AND u.incident_update_id = (
              SELECT MAX(incident_update_id) 
              FROM incident_update 
              WHERE incident_id = i.incident_id
          )
          LEFT JOIN affected_asset aa ON i.incident_id = aa.incident_id
          LEFT JOIN asset a ON aa.asset_id = a.asset_id";

// 3. filtret 
if ($user_role === 'reporter') {
    $query .= " WHERE u.user_id = " . intval($user_id);
}

// 4. grupperingen och sorteringen
$query .= " GROUP BY i.incident_id, i.description, i.incident_severity, i.occurrence, t.incident_type_name, u.status
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
// hämta attachments
$attachments = [];
if ($selectedCase) {
    $case_id = $selectedCase['incident_id'];
    $query_files = "SELECT a.attachment_file_path 
                    FROM attachment a
                    JOIN incident_update u ON a.incident_update_id = u.incident_update_id
                    WHERE u.incident_id = ?";
    
    $stmt_f = $mysqli->prepare($query_files);
    $stmt_f->bind_param("i", $case_id);
    $stmt_f->execute();
    $res_f = $stmt_f->get_result();
    while ($f = $res_f->fetch_assoc()) {
        $attachments[] = $f;
    }
    // HÄR HÄMTAS KOMMENTARERNA (Chatten)  
    // Fredrik säger: Vi hade kunnat joina in user här och på så sätt få användarnamnet så det kan displayas i chatten istället för user #X
    $query_comments = "SELECT c.comment_text, u.status, u.user_id, DATE_FORMAT(u.incident_update_id, '%H:%i') as time 
                       FROM comment c
                       JOIN incident_update u ON c.incident_update_id = u.incident_update_id
                       WHERE u.incident_id = ?
                       ORDER BY u.incident_update_id ASC";
    
    $stmt_c = $mysqli->prepare($query_comments);
    $stmt_c->bind_param("i", $case_id);
    $stmt_c->execute();
    $res_c = $stmt_c->get_result();
    while ($c = $res_c->fetch_assoc()) {
        $comments[] = $c;
    }
    $stmt_f->close();
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
                            <th>Affected Asset(s)</th>
                            <th>Status</th>
                            <?php if ($user_role !== 'reporter'): ?>
                                <th>Assign/Accpept Case</th>
                            <?php endif; ?>
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
                                    <td><?php echo htmlspecialchars($incident['asset_name'] ?? 'N/A'); ?></td>

                                    <!-- Status -->
                                    <td><?php echo $incident['status']; ?></td>

                                    <!-- Assign Case -->
                                    <?php if ($user_role !== 'reporter'): ?>
                                        <td>
                                            <form action="cases.php?assign_id=<?php echo $incident['incident_id']; ?>" method="POST" class="assign-form">
            
                                                <?php if ($_SESSION['user_role'] === 'administrator'): ?>
                                                    <select name="new_owner_id" class="assign-select">
                                                        <option value="">Select...</option>
                                                        <?php foreach ($all_users as $user_option): ?>
                                                            <option value="<?php echo $user_option['user_id']; ?>">
                                                                <?php echo htmlspecialchars($user_option['username']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                <?php endif; ?>
            
                                                <button type="submit" name="assign_submit" class="assign-btn">
                                                    <?php echo ($_SESSION['user_role'] === 'administrator') ? 'Assign' : 'Accept'; ?>
                                                </button>
                                            </form>
                                        </td>
                                    <?php endif; ?>

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
                    <?php if (empty($comments)): ?>
                        <p>No messages yet. Start the conversation!</p>
                    <?php else: ?>
                        <?php foreach ($comments as $msg): ?>
                            <?php 
                                // Kolla om det är mitt eget meddelande
                                $isMine = ($msg['user_id'] == $_SESSION['user_id']); 
                            ?>
                            <div class="message <?php echo $isMine ? 'my-message' : 'other-message'; ?>">
                                <small>User #<?php echo $msg['user_id']; ?></small>
                                <span><?php echo htmlspecialchars($msg['comment_text']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="chat-input">
                    <form action="send-chat-message.php" method="POST">
                        <input type="hidden" name="case_id" value="<?php echo $selectedCase['incident_id']; ?>">
                        <input type="text" name="chat_message" placeholder="Write..." required>
                        <button type="submit" class="send-btn">Send</button>
                    </form>
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
                        <span>Attach Document:</span>
                        <input type="file" name="attachment">
                    </div>

                    <div class="existing-attachments">
                        <?php if (!empty($attachments)): ?>
                            <span class="attachments-label">ATTACHED FILES:</span>
                            <?php foreach ($attachments as $file): ?>
                                <div class="attachment-wrapper">
                                    <a href="view-file.php?name=<?php echo urlencode($file['attachment_file_path']); ?>" 
                                        target="_blank" 
                                        class="attachment-link">
                                        📄 <?php echo htmlspecialchars($file['attachment_file_path']); ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
                    // om raden har klassen t.ex. pending eller in progress
                    const matches = activeFilters.some(s => row.classList.contains(s));
                    row.style.display = matches ? '' : 'none';
                }
            });
        }
    </script>
<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: unauthorized.php');
        exit();
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

    $activePage="cases";

    $mysqli = getDataBase();
    $user_id = $_SESSION['user_id']; 
    $user_role = $_SESSION['user_role'];


    // --- HANTERA ASSIGN ---
    if (isset($_POST['assign_submit'])) {
        $inc_id = isset($_POST['incident_id']) ? (int)$_POST['incident_id'] : (int)$_GET['assign_id'];
        $new_owner = !empty($_POST['new_owner_id']) ? (int)$_POST['new_owner_id'] : $user_id;

        if ($inc_id > 0) {
            $mysqli->begin_transaction();
            try {
                // anropar vi Fredriks insert-funktioner från db.php
                $updId = insertUpdate($mysqli, $inc_id, $new_owner, 'in progress');
                insertComment($mysqli, $updId, "System: Case assigned to user #" . $new_owner);

                $mysqli->commit();
                header("Location: cases.php?view=" . $inc_id);
                exit();
            } catch (Exception $e) {
                $mysqli->rollback();
                die("Fel vid tilldelning: " . $e->getMessage());
            }
        }
    }

    // --- HÄMTA ALL DATA ---
    $all_users = getAllUsers($mysqli);
    $incidents = getAllIncidents($mysqli, $user_role, $user_id);

    $selectedCase = null;
    $attachments = [];
    $comments = [];

    if (isset($_GET['view'])) {
        $viewId = intval($_GET['view']);
        foreach ($incidents as $i) {
            if ($i['incident_id'] == $viewId) {
                $selectedCase = $i;
                break;
            }
        }

        if ($selectedCase) {
            // hämtar chatt och filer 
            $attachments = getAttachmentsByIncident($mysqli, $viewId);
            $comments = getCommentsByIncident($mysqli, $viewId);
        }
    }

    // --- RÄKNA STATUSAR ---
    $counts = ['pending' => 0, 'in progress' => 0, 'resolved' => 0]; 
    foreach ($incidents as $i) { 
        if (isset($counts[$i['status']])) $counts[$i['status']]++; 
    } 
?> 


<?php require_once 'includes/header.php' ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <div class="content"> 
        <aside>
            <h2>Incident Overview</h2>
            <div class="chart-container" style="position: relative; height:200px; width:100%; margin-bottom: 20px;">
                <canvas id="statusChart"></canvas>
            </div> 
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
                            <th>Occured</th> 
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
                                <tr class="incident-row <?php echo str_replace(' ', '-', htmlspecialchars($incident['status'])); ?>"> 
                                    <!-- Incident.nr -->
                                    <td>
                                        <a href="cases.php?view=<?php echo $incident['incident_id']; ?>">
                                            #<?php echo $incident['incident_id']; ?>
                                        </a>
                                    </td>
                                    <!--Occurrence--> 
                                    <td><?php echo $incident['occurrence']; ?></td> 

                                    <!-- Affected Asset -->
                                    <td><?php echo htmlspecialchars($incident['asset_name'] ?? 'N/A'); ?></td>

                                    <!-- Status -->
                                    <td><?php echo htmlspecialchars($incident['status']); ?></td>

                                    <!-- Assign Case -->
                                    <?php if ($user_role !== 'reporter'): ?>
                                        <td>
                                            <form action="cases.php?assign_id=<?php echo $incident['incident_id']; ?>" method="POST" class="assign-form">
            
                                                <?php if ($_SESSION['user_role'] === 'administrator'): ?>
                                                    <select name="new_owner_id" class="assign-select">
                                                        <option value="">Select</option>
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
                                    <td><?php echo htmlspecialchars($incident['incident_severity']); ?></td>
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
                    <h2>Incident Detail: <?php echo $selectedCase ? "#".$selectedCase['incident_id'] : "Select a case"; ?>
                            <!--tiden-->
                        <?php if ($selectedCase): ?> 
                            - <?php echo date("Y-m-d H:i", strtotime($selectedCase['occurrence'])); ?> 
                            <!--incident type--> 
                            - <?php echo htmlspecialchars($selectedCase['incident_type_name'] ?? 'N/A');?> 
                        <?php endif; ?> 
                    </h2>
                </div>
        
                <div class="chat-log">
                    <?php if (!isset($viewId)): ?>
                        <p>No incident selected!</p>
                    <?php elseif (empty($comments)): ?>
                        <p>No messages yet. Start the conversation!</p>
                    <?php else: ?>
                        <?php foreach ($comments as $msg): ?>
                            <?php 
                                // Kolla om det är mitt eget meddelande
                                $isMine = ($msg['user_id'] == $_SESSION['user_id']); 
                            ?>
                            <div class="message <?php echo $isMine ? 'my-message' : 'other-message'; ?>">
                                <small><?php echo htmlspecialchars($msg['username']); ?>:</small>
                                <span><?php echo htmlspecialchars($msg['comment_text']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="chat-input">
                    <form action="send-chat-message.php" method="POST">
                        <input type="hidden" name="case_id" value="<?php echo htmlspecialchars($selectedCase['incident_id']); ?>">
                        <input type="text" name="chat_message" placeholder="Write..." required>
                        <button type="submit" class="send-btn" <?= !isset($viewId) ? 'disabled' : '' ?>>Send</button>
                    </form>
                </div>
            </article>

            <section class="action-panel">
                <form action="update-case.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="case_id" value="<?php echo $selectedCase ? $selectedCase['incident_id'] : ''; ?>">
            
                    <div class="case-details-box">
                        <?php if ($selectedCase): ?>
                            <p><strong>Incident Description:</strong> <?php echo htmlspecialchars($selectedCase['description']); ?></p>
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
                        <input type="file" name="attachment[]" multiple>
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
            
                    <button type="submit" class="update-btn" <?= !isset($viewId) ? 'disabled' : '' ?>>Update Incident</button>
                </form>
            </section>
        </main>
    </div>
<?php require_once 'includes/footer.php' ?>
       
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('statusChart').getContext('2d'); //munkdiiagrammet
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'In Progress', 'Resolved'],
            datasets: [{
                data: [
                    <?php echo $counts['pending']; ?>, 
                    <?php echo $counts['in progress']; ?>, 
                    <?php echo $counts['resolved']; ?>
                ],
                backgroundColor: ['#ff933a','#4a0ab9', '#0b026c'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 10 }
                }
            }
        }
    });
});
</script>

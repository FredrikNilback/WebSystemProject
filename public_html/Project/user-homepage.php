<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: unauthorized.php');
    }
    require_once '../../app/db.php';
    updateLastSeen($_SESSION['user_id']);
    $activePage = 'user-homepage';

    $currentEvents = getCurrentEvents();
?>

<?php require_once 'includes/header.php'?>
    <div class='content'>
        <main>
            <h2>Welcome, <?= htmlspecialchars($_SESSION['first_name']) ?>!</h2>
            <div class='main-column'>
                <div id='navigation-buttons'>
                    <button onclick='location.href="new-case.html"' id='create-btn' class='navigation-button'>
                        <span>New Case</span>
                        <img src='images/homepage/create-btn.png' alt='New'>
                    </button>

                    <button onclick='location.href="cases.html"' id='cases-btn' class='navigation-button'>
                        <div class='notification-div'>
                            <img src='images/homepage/notification.png' alt='notification'>
                            <span id='cases-update-count' class='update-count'>5</span>
                        </div>
                        <span>Incidents</span>
                        <img src='images/homepage/cases-btn.png' alt='Cases'>
                    </button>

                    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'administrator'): ?>
                        <button onclick='location.href="manage-users.php"' id='user-btn' class='navigation-button'>
                            <span>Manage Users</span>
                            <img src='images/homepage/user-btn.png' alt='user'>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class='main-column'>
                <h3 id='incident-title'>Incident Report Overview</h3>
                <a href='analytics.html'>
                    <img src='images/placeholder_graph.png' alt='Statistical Overview' id='statistic-graph'>
                </a>
            </div>
            <button onclick='location.href="logout.php"' id='logout-btn'>LOGOUT</button>
        </main>
        <aside>
            <h3>Current Events</h3>
            <div id='current-events'>
                <?php foreach ($currentEvents as $event): ?>
                    <details class='current-event'>
                        <summary>
                            <span class='event-title'>
                                <u><em><?= htmlspecialchars($event['event_date']) ?></em></u><br>
                                <strong><?= htmlspecialchars($event['event_title']) ?></strong><br>
                            </span>
                            <span class='read-more'><em>read more...</em></span>
                        </summary>
                        <p>
                            <?= nl2br(htmlspecialchars($event['event_text'])) ?>
                        </p>
                    </details>
                <?php endforeach; ?>
            </div>
        </aside>
    </div>
<?php require_once 'includes/footer.php'?>
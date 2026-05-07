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
        <main class=''>
            <div id='welcome-div'>
                <h2>Welcome, <?= htmlspecialchars($_SESSION['first_name']) ?>!</h2>
            </div>
            <div class='main-column'>
                <div id='navigation-buttons'>
                    <button onclick='location.href="new-case.php"' id='create-btn' class='navigation-button'>
                        <img class='button-icon' src='images/homepage/create-btn.png' alt='Cases'>
                        <span>New Case</span>
                        <img class='button-arrow' src='images/homepage/arrow-right.png' alt='arrow right'>
                    </button>

                    <button onclick='location.href="cases.php"' id='cases-btn' class='navigation-button'>
                        <img class='button-icon' src='images/homepage/cases-btn.png' alt='Cases'>
                        <span>Incidents</span>
                        <img class='button-arrow' src='images/homepage/arrow-right.png' alt='arrow right'>
                    </button>

                    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'administrator'): ?>
                        <button onclick='location.href="manage-users.php"' id='user-btn' class='navigation-button'>
                            <img class='button-icon' src='images/homepage/user-btn.png' alt='user'>
                            <span>Manage Users</span>
                            <img class='button-arrow' src='images/homepage/arrow-right.png' alt='arrow right'>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class='main-column'>
                <h3 id='incident-title'>Incident Report Overview</h3>
                <a href='analytics.php'>
                    <img src='images/placeholder_graph.png' alt='Statistical Overview' id='statistic-graph'>
                </a>
            </div>
            <button onclick='location.href="logout.php"' id='logout-btn'>
                <img src="images/homepage/exit_sign.png" alt="exit">
                LOGOUT
            </button>
            <button id='open-aside-btn' class='hidden'>current events</button>
        </main>
        <aside class=''>
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
            <div id='minimize-btn-div'>
                <button id='minimize-btn'>minimize</button>
            </div>
        </aside>
    </div>
<?php require_once 'includes/footer.php'?>
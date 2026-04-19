<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: unauthorized.php');
    }
    require_once '../../app/db.php';
    updateLastSeen($_SESSION['user_id']);
    $activePage = 'user-homepage';
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
            <!-- these events will be fetched from the DB -->
            <div id='current-events'>
                <details class='current-event'>
                    <summary>
                        <span class='event-title'>
                            <u><em>10/4-26</em></u><br>
                            <strong>Our CEO and founder Viktoria Thålin comes to visit!<br></strong>
                        </span>
                        <span class='read-more'><em>read more...</em></span>
                    </summary>
                    <p>On April 10th our CEO and founder, Viktoria Thålin, will be visiting. <br>
                        During her visit, she will meet with teams across the company, share her insights and
                        experiences, and discuss strategies to foster innovation, collaboration, and growth.<br>
                        This is a unique opportunity for everyone to engage directly with Viktoria, gain inspiration
                        from her vision, and participate in meaningful conversations about the future of our
                        organization.<br><br>
                        We're excited to welcome her and look forward to an inspiring and productive day together!
                    </p>
                </details>
                <details class='current-event'>
                    <summary>
                        <span class='event-title'>
                            <u><em>12/1-26</em></u><br>
                            <strong>Nike Borgström: employee of the year for the 10th consecutive year!<br></strong>
                        </span>
                        <span class='read-more'><em>read more...</em></span>
                    </summary>
                    <p>We are thrilled to announce that Nike Borgström has been named Employee of the Year for the
                        10th consecutive year!<br>
                        Her dedication, tireless commitment, and ability to make every meeting feel like a
                        masterclass in excellence have truly set her apart.<br>
                        To commemorate this remarkable milestone, we will be hosting a pizza party in her
                        honor.<br><br>
                        Join us in celebrating Nike's continued brilliance, unmatched consistency, and her inspiring
                        ability to keep showing up year after year, without asking for pay raises. Now that's some
                        serious dedication!<br>
                        Her work is invaluable to the company and without her we would not have made it this far!
                    </p>
                </details>
                <details class='current-event'>
                    <summary>
                        <span class='event-title'>
                            <u><em>12/12-25</em></u><br>
                            <strong>Fredrik Nilback retires at the ripe age of 29<br></strong>
                        </span>
                        <span class='read-more'><em>read more...</em></span>
                    </summary>
                    <p>After 2 years at NFV, Fredrik Nilback is finally retiring. After solving our entire technical
                        debt and backlog he has now made the choice to retire from the IT profession to pursue
                        farming.<br>
                        "Farming seems like... a peaceful life" - Fredrik said to the HR department after announcing
                        his decision to retire.<br><br>
                        We will miss you Fredrik, and we hope that your future endeavours bring you the happiness
                        that you are seeking!
                    </p>
                </details>
            </div>
        </aside>
    </div>
<?php require_once 'includes/footer.php'?>
<?php




session_start();

$activePage = "visits";

require_once '../../app/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'administrator') {
    header('Location: unauthorized.php');
    exit;
}

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
$dateFilter = $_GET['date'] ?? 'Today';
$browserFilter = $_GET['browser'] ?? 'AllBrowsers';
$userFilter = $_GET['user'] ?? 'all';

echo $dateFilter;
echo $browserFilter;
echo $userFilter;





$visits = getVisits($browserFilter, $dateFilter, $userFilter);
$todayVisits = getTodayVisits($browserFilter, $dateFilter, $userFilter);
$avgDaily = getAvgDailyVisits();
$avgWeekly = getAvgWeeklyVisits();
$avgMonthly = getAvgMonthlyVisits();
$visitsPerDate = getVisitsPerDate();
$visitsPerBrowser = getVisitsPerBrowser();
$visitsPerWeek = getVisitsPerWeek();
$visitsPerDay = getVisitsPerDay();
?>


<?php require_once "includes/header.php"?>
    
<div class="content">
    <main>

        <h3>Analytics Dashboard</h3>
        <nav class="analytics-dashboard">
            <a href="analytics.php">Incidents</a>
            <a href="visits.php">Page Visits</a>
        </nav>
    
        <div class="top-part">
            <form class="filters" method="GET">
                <div class="filter-group">
                    <label for="dateRange">Date</label>

                    <select id="dateRange" name="date">
                        <option value="Today">Today</option>
                        <option value="Yesterday">Yesterday</option>
                        <option value="Current Week">Current Week</option>
                        <option value="Current Month">Current Month</option>
                        <option value="Custom">Custom</option>
                    </select>
                    <input type="date" id="Custom" style="display:none;">
                </div>

                <div class="filter-group">
                    <label for="Browser">Browser</label>

                    <select id="BrowserUsed" name="browser">
                        <option value="AllBrowsers">All</option>
                        <option value="Chrome">Chrome</option>
                        <option value="Edge">Microsoft Edge</option>
                        <option value="Opera">Opera</option>
                        <option value="Safari">Safari</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="UserVisit">User</label>

                    <select id="User" name="user">
                        <option value="all">All</option>
                        
                        <?php
                            $users = getUsers(100,0);
                            foreach ($users as $user):
                        ?>
                        
                            <option value="<?= $user['username'] ?>">
                                <?= $user['username'] ?>
                            </option>

                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" id="ApplyFilters">Apply</button>
            </form>

            <div class="cards">
                <div class="card">
                    <p>Total visits</p>
                    <h2><?= $todayVisits ?></h2>
                </div>
                <div class="card">
                    <p>Avg Daily Visits</p>
                    <h2><?= $avgDaily ?></h2>
                </div>
                <div class="card">
                    <p>Avg Weekly Visits</p>
                    <h2><?= $avgWeekly ?></h2>
                </div>
                <div class="card">
                    <p>Avg Monthly Visits</p>
                    <h2><?= $avgMonthly ?></h2>
                </div>
            </div>
        </div>

        <div class="dashboard-structure">
            <div class="chart">
                <h3>History</h3>
                <canvas id="Visits_History_lineChart"></canvas>
            </div>
            <div class="chart">
                <h3>Weekly</h3>
                <canvas id="Visits_Weekly_barChart"></canvas>
            </div>
            <div class="chart">
                <h3>Daily</h3>
                <canvas id="Visits_Daily_barChart"></canvas>
            </div>
            <div class="chart">
                <h3>Browser</h3>
                <canvas id="Browser_Daily_barChart"></canvas>
            </div>
        </div>

        <div class="table-container">
            <table>
                <tr>
                    <th>Page</th>
                    <th>Tracking-ID</th>
                    <th>Browser Type ID</th>
                    <th>Host IP</th>
                    <th>Time-stamp</th>
                </tr>

                <?php foreach ($visits as $visit): ?>
                <tr>
                    <td><?= $visit['page_name'] ?></td>
                    <td><?= $visit['tracking_id']?></td>
                    <td><?= $visit['browser_name']?></td>
                    <td><?= $visit['host_ip']?></td>
                    <td><?= $visit['visited_at']?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const visitsData = <?= json_encode($visitsPerDate); ?>;
const browserData = <?= json_encode($visitsPerBrowser); ?>;
const weeklyData = <?= json_encode($visitsPerWeek); ?>;
const dayData = <?= json_encode($visitsPerDay); ?>;
</script>



<?php require_once "includes/footer.php"?>




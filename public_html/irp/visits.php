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

$dateFilter = $_GET['date'] ?? 'All';
$browserFilter = $_GET['browser'] ?? 'AllBrowsers';
$userFilter = $_GET['user'] ?? 'All';
$currentPage = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($currentPage - 1) * $limit;



$visits = getVisits($browserFilter, $dateFilter, $userFilter, $limit, $offset);
$todayVisits = getTodayVisits($browserFilter, $dateFilter, $userFilter);
$avgDaily = getAvgDailyVisits($browserFilter, $dateFilter, $userFilter);
$avgWeekly = getAvgWeeklyVisits($browserFilter, $dateFilter, $userFilter);
$avgMonthly = getAvgMonthlyVisits($browserFilter, $dateFilter,$userFilter);
$visitsPerDate = getVisitsPerDate($browserFilter, $dateFilter, $userFilter);
$visitsPerBrowser = getVisitsPerBrowser($browserFilter, $dateFilter, $userFilter);
$visitsPerWeek = getVisitsPerWeek($browserFilter, $dateFilter, $userFilter);
$visitsPerDay = getVisitsPerDay($browserFilter, $dateFilter, $userFilter);
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

                        <option value="All"
                            <?= $dateFilter == 'All' ? 'selected' : '' ?>>
                            All
                        </option>
                        <option value="Today"
                            <?= $dateFilter == 'Today' ? 'selected' : '' ?>>
                            Today
                        </option>
                        <option value="Yesterday"
                        <?= $dateFilter == 'Yesterday' ? 'selected' : '' ?>>
                        Yesterday
                        </option>
                        <option value="Current Week"
                        <?= $dateFilter == 'Current Week' ? 'selected' : '' ?>>
                        Current Week
                        </option>
                        <option value="Current Month"
                            <?= $dateFilter == 'Current Month' ? 'selected' : '' ?>>
                        Current Month
                        </option>
                        
                    </select>
                    <input type="date" id="All" style="display:none;">
                </div>

                <div class="filter-group">
                    <label for="Browser">Browser</label>

                    <select id="BrowserUsed" name="browser">
                        <option value="AllBrowsers"
                            <?= $browserFilter == 'AllBrowsers' ? 'selected' : '' ?>>All</option>
                        
                        <option value="Chrome"
                            <?= $browserFilter == 'Chrome' ? 'selected' : '' ?>>Chrome</option>
                        
                        <option value="Edge"
                            <?= $browserFilter == 'Edge' ? 'selected' : '' ?>>Microsoft Edge</option>
                        
                        <option value="Opera"
                            <?= $browserFilter == 'Opera' ? 'selected' : '' ?>>Opera</option>
                        
                        <option value="Safari"
                            <?= $browserFilter == 'Safari' ? 'selected' : '' ?>>Safari</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="UserVisit">User</label>

                    <select id="User" name="user">
                        <option value="All"
                            <?= $userFilter == 'All' ? 'selected' : '' ?>>All</option>
                        
                        <?php
                            $users = getUsers(100,0);
                            foreach ($users as $user):
                        ?>
                        
                            <option value="<?= $user['username'] ?>"
                                <?= $userFilter == $user['username'] ? 'selected' : '' ?>>
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
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?= $currentPage - 1 ?>&date=<?= $dateFilter ?>&browser=<?= $browserFilter ?>&user=<?= $userFilter ?>">
                        Previous page</a>
                <?php endif; ?>

                <a href="?page=<?= $currentPage + 1 ?>&date=<?= $dateFilter ?>&browser=<?= $browserFilter ?>&user=<?= $userFilter ?>">
                        Next Page</a>
                </div>
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




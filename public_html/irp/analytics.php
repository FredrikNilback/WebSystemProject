
<?php 
    session_start();
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'administrator') {
        header('Location: unauthorized.php');
        exit;
    }

    $activePage = 'analytics';
    require_once '../../app/db.php';
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
    trackVisit($page, $browserId, $ip);


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
            <div class="filters">

                <div class="filter-group">
                    <label for="dateRange">Date</label>
                    <select id="dateRange">
                        <option value="Today">Today</option>
                        <option value="Yesterday">Yesterday</option>
                        <option value="Current Week">Current Week</option>
                        <option value="Current Month">Current Month</option>
                        <option value="Custom">Custom</option>
                    </select>
                    <input type="date" id="Custom" style="display:none;">
                </div>

                <div class="filter-group">
                    <label for="SeverityLevel">Severity</label>
                    <select id="SeverityLevel">
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>

                <div class="filter-group">

                <label for="Category">Category</label>
                    <select id="Category">
                        <option value="PhishingAttacks">Phishing Attacks</option>
                        <option value="Malware">Malware</option>
                        <option value="Ransomware">Ransomware</option>
                        <option value="UnauthorizedAccess">Unautharized Access</option>
                    </select>
                </div>

                <button id="ApplyFilters">Apply</button>

            </div>

            <div class="cards">
                <div class="card">Incidents Pending</div>
                <div class="card">Resolved Incidents</div>
                <div class="card">Avg Resolution Time</div>
                <div class="card">Avg Visits</div>
            </div>
        </div>

        <div class="dashboard-structure">
             <div class="chart">
                <h3>Incident History</h3>
                <canvas id="History_lineChart"></canvas>
            </div>

            <div class="chart">
                <h3>Top Incident Category</h3>
                <canvas id="Top_Incidents_pieChart"></canvas>
            </div>

            <div class="chart">
                <h3>Incident Severity</h3>
                <canvas id="Incident_Overview_barChart"></canvas>
            </div>

            <div class="chart">
                <h3>Resolution Time</h3>
                <canvas id="Resolution_Time_barChart"></canvas>
            </div>
        </div>

    </main>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/analytics.js"></script>


</body>

</html>



 
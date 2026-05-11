<?php
    session_start();
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
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
    $trackingId = trackVisit($page, $browserId, $ip);
    trackUserVisit($trackingId, $_SESSION['user_id']);

    $allowedDates = ['All', 'Today', 'Yesterday', 'Current Week', 'Current Month', 'Custom'];
    $allowedSeverities = ['All', 'Low', 'Medium', 'High', 'Critical'];
    $allowedCategories = ['All', 'Denial of Service', 'Insider Threat', 'Man in the Middle', 'Password Attack', 'Phishing Attack', 'Privilege Escalation', 'Ransomware', 'Theft', 'Unauthorized Access Attack'];
    $dateFilter = $_GET['date'] ?? 'All';
    $severityFilter = $_GET['severity'] ?? 'All';
    $categoryFilter = $_GET['category'] ?? 'All';

    if (!in_array($dateFilter, $allowedDates, true)) {
        $dateFilter = 'All';
    }
    if (!in_array($severityFilter, $allowedSeverities, true)) {
        $severityFilter = 'All';
    }
    if (!in_array($categoryFilter, $allowedCategories, true)) {
        $categoryFilter = 'All';
    }

    $totalIncidents = getIncidentCount ($dateFilter, $severityFilter, $categoryFilter);
    $resolvedIncidents = getResolvedIncidents($dateFilter, $severityFilter, $categoryFilter);
    $avgResolutionTime = getAvgResolutionTime($dateFilter, $severityFilter, $categoryFilter);
    $avgVisits = getAvgDailyVisits();
    $incidentHistory = getIncidentHistory($dateFilter, $severityFilter, $categoryFilter);
    $topIncidentCategories = getTopIncidentCategories($dateFilter, $severityFilter, $categoryFilter);
    $incidentSeverityData = getIncidentSeverityData($dateFilter, $severityFilter, $categoryFilter);
    $resolutionTimeData = getResolutionTimeData($dateFilter, $severityFilter, $categoryFilter);
?>

<?php require_once "includes/header.php"?>

<div class="content">
    <main>
        <h3>Analytics Dashboard</h3>
        <nav class="analytics-dashboard">
            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'administrator'): ?>
                <a href="analytics.php">Incidents</a>
                <a href="visits.php">Page Visits</a>
            <?php endif; ?>
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
                        <option value="Custom"
                        <?= $dateFilter == 'Custom' ? 'selected' : '' ?>>
                        Custom
                        </option>
                    </select>
                    <input type="date" id="Custom" style="display:none;">
                </div>

                <div class="filter-group">
                    <label for="SeverityLevel">Severity</label>
                    <select id="SeverityLevel" name="severity">
                        <option value="All"
                            <?= $severityFilter == 'All' ? 'selected' : '' ?>>
                            All
                        </option>
                        <option value="Low"
                            <?= $severityFilter == 'Low' ? 'selected' : '' ?>>
                            Low
                        </option>
                        <option value="Medium"
                            <?= $severityFilter == 'Medium' ? 'selected' : '' ?>>
                            Medium
                        </option>
                        <option value="High"
                            <?= $severityFilter == 'High' ? 'selected' : '' ?>>
                            High
                        </option>
                        <option value="Critical"
                            <?= $severityFilter == 'Critical' ? 'selected' : '' ?>>
                            Critical
                        </option>
                    </select>
                </div>

                <div class="filter-group">

                <label for="Category">Category</label>
                    <select id="Category" name="category">
                        <option value="All"
                            <?= $categoryFilter == 'All' ? 'selected' : '' ?>>
                            All
                        </option>
                        <option value="Denial of Service"
                        <?= $categoryFilter == 'Denial of Service' ? 'selected' : '' ?>>
                        Denial of Service
                        </option>
                        <option value="Insider Threat"
                        <?= $categoryFilter == 'Insider Threat' ? 'selected' : '' ?>>
                        Insider Threat
                        </option>
                        <option value="Man in the Middle"
                        <?= $categoryFilter == 'Man in the Middle' ? 'selected' : '' ?>>
                        Man in the Middle
                        </option>
                        <option value="Password Attack"
                        <?= $categoryFilter == 'Password Attack' ? 'selected' : '' ?>>
                        Password Attack
                        </option>
                        <option value="Phishing Attack"
                        <?= $categoryFilter == 'Phishing Attack' ? 'selected' : '' ?>>
                        Phishing Attack
                        </option>
                        <option value="Privilege Escalation"
                        <?= $categoryFilter == 'Privilege Escalation' ? 'selected' : '' ?>>
                        Privilege Escalation
                        </option>
                        <option value="Ransomware"
                        <?= $categoryFilter == 'Ransomware' ? 'selected' : '' ?>>
                        Ransomware
                        </option>
                        <option value="Theft"
                        <?= $categoryFilter == 'Theft' ? 'selected' : '' ?>>
                        Theft
                        </option>
                        <option value="Unauthorized Access Attack"
                        <?= $categoryFilter == 'Unauthorized Access Attack' ? 'selected' : '' ?>>
                        Unauthorized Access Attack
                        </option>
                    </select>
                </div>

                <button id="ApplyFilters">Apply</button>

            </form>

            <div class="cards">
                <div class="card">
                    <p>Total Incidents</p>
                    <h2><?= $totalIncidents ?></h2>
                </div>
                <div class="card">
                    <p>Resolved Incidents</p>
                    <h2><?= $resolvedIncidents ?></h2>
                </div>
                <div class="card">
                    <p>Avg Resolution Time</p>
                    <h2><?= $avgResolutionTime ?>h</h2>
                    </div>
                <div class="card">
                    <p>Avg Daily Page Visits</p>
                    <h2><?= $avgVisits ?></h2>
                </div>
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

<script>
    const incidentHistoryData = <?= json_encode($incidentHistory); ?>;
    const topIncidentCategoriesData = <?= json_encode($topIncidentCategories); ?>;
    const incidentSeverityChartData = <?= json_encode($incidentSeverityData); ?>;
    const resolutionTimeChartData = <?= json_encode($resolutionTimeData); ?>;
</script>

<?php require_once 'includes/footer.php'?>

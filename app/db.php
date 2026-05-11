<?php
    /*===========================================================
                        FREDRIK'S FUNCTIONS
                                |
                                V
    =============================================================*/

    function getDataBase() {
        $secretsPath = __DIR__ . '/../secrets.json';
        $secrets = json_decode(file_get_contents($secretsPath));

        $host = 'localhost';
        $username = $secrets->db_usr;
        $dbName = $secrets->db_name;
        $password = $secrets->db_pwd;

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $mysqli = new mysqli($host, $username, $password, $dbName);

        if ($mysqli->connect_error) {
            die('Could not establish database connection');
        }

        $mysqli->query("SET time_zone = '+00:00'");
        return $mysqli;
    }

    function createUser($username, $firstname, $lastname, $email, $password, $role) {
        $mysqli = getDataBase();
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $mysqli->prepare(
            'INSERT INTO user (username, first_name, last_name, user_email, password_hash, user_role)
             VALUES (?, ?, ?, ?, ?, ?);'
        );

        $stmt->bind_param(
            'ssssss',
            $username,
            $firstname,
            $lastname,
            $email,
            $passwordHash,
            $role
        );

        $stmt->execute();
        $stmt->close();
        $mysqli->close();
    }

    function login($username, $password) {
        $mysqli = getDataBase();

        $stmt = $mysqli->prepare(
            'SELECT * 
             FROM user
             WHERE username = ?;'
        );
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_object();

        $stmt->close();

        if (!$user || !password_verify($password, $user->password_hash)) {
            $mysqli->close();
            return false;
        }
        
        unset($user->password_hash);
        $mysqli->close();
        return $user; 
    }

    function getUsers($limit, $offset, $order='uaz', $direction='ASC', $roles=NULL, $search=NULL) {
        $allowedDirection = ['ASC', 'DESC'];
        if (!in_array($direction, $allowedDirection, TRUE)) {
            $direction = 'ASC';
        }
        $limit = (int)$limit;
        $offset = (int)$offset;

        if (empty($roles) || !is_array($roles)) {
            $roles = NULL;
        }

        if ($roles) {
            $allowedRoles = ['reporter', 'responder', 'administrator'];
            for ($i = 0; $i < count($roles); $i++) {
                if (!in_array($roles[$i], $allowedRoles, TRUE)) {
                    $roles = NULL;
                    break;
                }
            }
        }

        $mysqli = getDataBase();

        $sql =
            "SELECT 
                 user_id, 
                 CONCAT(first_name,' ', last_name) AS full_name,
                 username,
                 user_email,
                 user_role,
                 last_seen
             FROM user
             ";
        
        if ($roles || $search) {
            $sql .= "WHERE ";
        }

        if ($roles) {
            $sql .= "(";
            $iterations = count($roles);
            for ($i = 0; $i < $iterations - 1; $i++) {
                $sql .= "user_role = '" . $roles[$i]. "' OR ";
            }
            $sql .= "user_role = '" . $roles[$iterations - 1] . "'";
            $sql .= ") ";
        }

        if ($roles && $search) {
            $sql .= "AND ";
        }

        if ($search) {
            $sql .= "(username LIKE ? OR CONCAT(first_name,' ', last_name) LIKE ?) ";
        }

        $sql .= "ORDER BY ";
        switch ($order) {
            case "uaz":
                $sql .= "username $direction ";
                break;
            case "naz":
                $sql .= "last_name COLLATE utf8mb4_swedish_ci $direction ";
                break;
            case "id":
                $sql .= "user_id $direction ";
                break;
            default:
                $sql .= "username $direction ";
                break;
        }
        $sql .= "LIMIT $limit OFFSET $offset;";

        $users = NULL;
        if ($search) {
            $search = "%" . $search . "%";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ss', $search, $search);
            $stmt->execute();

            $result = $stmt->get_result();
            $users = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            $query = $mysqli->query($sql);
            $users = $query->fetch_all(MYSQLI_ASSOC);
        }

        $mysqli->close();

        return $users;
    }

    function getUserCount($roles=NULL, $search=NULL) {
        if (empty($roles) || !is_array($roles)) {
            $roles = NULL;
        }
        if ($roles) {
            $allowedRoles = ['reporter', 'responder', 'administrator'];
            for ($i = 0; $i < count($roles); $i++) {
                if (!in_array($roles[$i], $allowedRoles, TRUE)) {
                    $roles = NULL;
                    break;
                }
            }
        }

        $mysqli = getDataBase();
        $count = 0;
        $sql = 
            "SELECT COUNT(*) AS count
             FROM user
             ";

        if ($roles || $search) {
            $sql .= "WHERE ";
        }

        if ($roles) {
            $sql .= "(";
            $iterations = count($roles);
            for ($i = 0; $i < $iterations - 1; $i++) {
                $sql .= "user_role = '" . $roles[$i]. "' OR ";
            }
            $sql .= "user_role = '" . $roles[$iterations - 1] . "'";
            $sql .= ") ";
        }

        if ($roles && $search) {
            $sql .= "AND ";
        }

        $count = 0;
        if ($search) {
            $sql .= "(username LIKE ? OR CONCAT(first_name,' ', last_name) LIKE ?) ";
            $search = "%" . $search . "%";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ss', $search, $search);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();
        } else {
            $count = $mysqli->query($sql)->fetch_assoc()['count'];
        }
        $mysqli->close();

        return (int)$count;
    }

    function updateUserRole($userId, $role) {
        $allowedRoles = ['reporter', 'responder', 'administrator'];
        if (!in_array($role, $allowedRoles, true)) {
            return false;
        }

        $userId = (int)$userId;
        $mysqli = getDataBase();

        $mysqli->query(
            "UPDATE user
             SET user_role = '$role'
             WHERE user_id = $userId;"
        );

        $mysqli->close();
    }

    function deleteUser($userId) {
        $mysqli = getDataBase();

        $userId = (int)$userId;

        $mysqli->query(
            "DELETE FROM user
             WHERE user_id=$userId;"
        );
        $mysqli->close();
    }

    function updateLastSeen($userId) {
        $mysqli = getDataBase();
        $userId = (int)$userId;
    
        $mysqli->query(
            "UPDATE user
             SET last_seen = NOW()
             WHERE user_id = $userId;"
        );
        $mysqli->close();
    }

    function getCurrentEvents() {
        $mysqli = getDataBase();

        $query = $mysqli->query(
            "SELECT event_date, event_title, event_text
             FROM current_event
             ORDER BY event_date DESC
             LIMIT 5;"
        );
        $events = $query->fetch_all(MYSQLI_ASSOC);
        $mysqli->close();

        return $events;
    }

    /*===========================================================
                        NIKE'S FUNCTIONS
                                |
                                V
    =============================================================*/

    function trackVisit($page, $browser_id, $ip) {
       
        $mysqli = getDataBase();

        $stmt = $mysqli->prepare("SELECT page_id FROM page WHERE page_name = ?;");
        $stmt->bind_param("s", $page);
        $stmt->execute();
        $stmt->bind_result($pageId);
        $stmt->fetch();
        $stmt->close();
        if (!$pageId) {
            return;
        }

        $stmt = $mysqli->prepare(
            "INSERT INTO visit_tracking (page_id, browser_type_id, host_ip)
            VALUES (?, ?, INET_ATON(?));"
        );

        $stmt->bind_param("iis", $pageId, $browser_id, $ip);
        $stmt->execute();
        $trackingId = $mysqli->insert_id;
   
        $stmt->close();
        $mysqli->close();

        return $trackingId;
    }

    function getBrowserId($browserName) {
        $mysqli = getDataBase();

        $stmt = $mysqli->prepare("
            SELECT browser_type_id
            FROM browser_type
            WHERE browser_name = ?;
        ");

        $stmt->bind_param("s", $browserName);
        $stmt->execute();
        $stmt->bind_result($browserId);

        if ($stmt->fetch()) {
            $stmt->close();
            $mysqli->close();
            return $browserId;
        }

        $stmt->close();

        $stmt = $mysqli->prepare("
            INSERT INTO browser_type (browser_name)
            VALUES (?);
        ");

        $stmt->bind_param("s", $browserName);
        $stmt->execute();

        $browserId = $mysqli->insert_id;

        $stmt->close();
        $mysqli->close();

        return $browserId;
    }


    function trackUserVisit($trackingId, $userId) {

        $mysqli = getDataBase();

        $stmt = $mysqli->prepare("
            INSERT INTO user_tracking (tracking_id, user_id)
            VALUES (?, ?)
        ");

        $stmt->bind_param("ii", $trackingId, $userId);
        $stmt->execute();

        $stmt->close();
        $mysqli->close();
    }

    function buildVisitFilters(
        &$params,
        &$types,
        $browserFilter,
        $dateFilter,
        $userFilter) {
       
        $where = [];

        if ($browserFilter != 'AllBrowsers') {
            $where[] = "browser_type.browser_name = ?";
            $params[] = $browserFilter;
            $types .= "s";
        }

        if ($userFilter != 'All') {
            $where[] = "user.username = ?";
            $params[] = $userFilter;
            $types .= "s";
        }

        if ($dateFilter == 'Today') {
            $where[] = "DATE(visited_at) = CURDATE()";
        }

        elseif ($dateFilter == 'Yesterday') {
            $where[] = "DATE(visited_at) = CURDATE() - INTERVAL 1 DAY";
        }

        elseif ($dateFilter == 'Current Week') {
            $where[] = "
                YEARWEEK(visited_at, 1)
                = YEARWEEK(CURDATE(),1)
            ";
        }

        elseif ($dateFilter == 'Current Month') {
            $where[] = "
                MONTH(visited_at) = MONTH(CURDATE())
                AND YEAR(visited_at) = YEAR(CURDATE())
            ";
        }

        return $where;
        
    }
            

    function getVisits(
        $browserFilter = 'AllBrowsers', 
        $dateFilter = 'All',
        $userFilter = 'All',
        $limit = 10,
        $offset = 0) {

        $mysqli = getDataBase();

        $sql = "
            SELECT visit_tracking.tracking_id,
                page.page_name,
                browser_type.browser_name,
                INET_NTOA(visit_tracking.host_ip) AS host_ip,
                visit_tracking.visited_at
            FROM visit_tracking
            JOIN page ON visit_tracking.page_id = page.page_id
            LEFT JOIN browser_type ON visit_tracking.browser_type_id = browser_type.browser_type_id
            LEFT JOIN user_tracking ON visit_tracking.tracking_id = user_tracking.tracking_id
            LEFT JOIN user ON user_tracking.user_id = user.user_id
        ";

        $params = [];
        $types = "";

        $where = buildVisitFilters(
            $params,
            $types,
            $browserFilter,
            $dateFilter,
            $userFilter
        );

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= "
            ORDER BY visit_tracking.visited_at DESC
            LIMIT $limit OFFSET $offset
        ";

        if (!empty($params)) {
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $visits = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }

        else {
            $result = $mysqli->query($sql);
            $visits = $result->fetch_all(MYSQLI_ASSOC);
        }

        $mysqli->close();
        return $visits;
    }

    function getTodayVisits(
        $browserFilter = 'AllBrowsers',
        $dateFilter = 'All',
        $userFilter = 'All') 
    {

        $mysqli = getDataBase();

        $sql = "
            SELECT COUNT(*) AS count
            FROM visit_tracking

            LEFT JOIN browser_type
            ON visit_tracking.browser_type_id = browser_type.browser_type_id
            
            LEFT JOIN user_tracking
            ON visit_tracking.tracking_id = user_tracking.tracking_id

            LEFT JOIN user
            ON user_tracking.user_id = user.user_id
        ";

        $params = [];
        $types = "";

        $where = buildVisitFilters(
            $params,
            $types,
            $browserFilter,
            $dateFilter,
            $userFilter
        );

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        if (!empty($params)) {
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $stmt->close();

        } else {

            $result = $mysqli->query($sql);
            $row = $result->fetch_assoc();
        }

        $mysqli->close();

        return $row['count'];
    }



    function getAvgDailyVisits(
        $browserFilter = 'AllBrowsers',
        $dateFilter = 'All',
        $userFilter = 'All'
    ) {
        
        $mysqli = getDataBase();


        $sql = "
            SELECT COUNT(*) / COUNT(DISTINCT DATE(visited_at)) AS avg_daily
            FROM visit_tracking

            LEFT JOIN browser_type
            ON visit_tracking.browser_type_id = browser_type.browser_type_id
            
            LEFT JOIN user_tracking
            ON visit_tracking.tracking_id = user_tracking.tracking_id
        
            LEFT JOIN user
            ON user_tracking.user_id = user.user_id
        
        ";

        $params = [];
        $types = "";

        $where = buildVisitFilters(
            $params,
            $types,
            $browserFilter,
            $dateFilter,
            $userFilter
        );

            if (!empty($where)) {

            $sql .= " WHERE " . implode(" AND ", $where);

            $stmt = $mysqli->prepare($sql);

            if (!empty($types)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $stmt->close();

        } else {

        $result = $mysqli->query($sql);
        $row = $result->fetch_assoc();
        }

        $mysqli->close();
        return round($row['avg_daily']);

    }

 

    function getAvgWeeklyVisits(
        $browserFilter = 'AllBrowsers',
        $dateFilter = 'All',
        $userFilter = 'All'
    ) {

        $mysqli = getDataBase();

        $sql = "
            SELECT COUNT(*) / COUNT(DISTINCT YEARWEEK(visited_at, 1)) AS avg_weekly
            FROM visit_tracking

            LEFT JOIN browser_type
            ON visit_tracking.browser_type_id = browser_type.browser_type_id
            
            LEFT JOIN user_tracking
            ON visit_tracking.tracking_id = user_tracking.tracking_id
            
            LEFT JOIN user
            ON user_tracking.user_id = user.user_id
            ";

        $params =[];
        $types = "";

        $where = buildVisitFilters(
            $params,
            $types,
            $browserFilter,
            $dateFilter,
            $userFilter
        );

        if (!empty($where)) {

            $sql .= " WHERE " . implode(" AND ", $where);

            $stmt = $mysqli->prepare($sql);
            if (!empty($types)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();

            $result =$stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

        } else {

        $result = $mysqli->query($sql);
        $row = $result->fetch_assoc();
        }

        $mysqli->close();

        return $row['avg_weekly'] ? round($row['avg_weekly']) : 0;
    }

        function getAvgMonthlyVisits(
            $browserFilter = 'AllBrowsers',
            $dateFilter = 'All',
            $userFilter = 'All'
        ) {

        $mysqli = getDataBase();
        $sql = "
            SELECT COUNT(*) / COUNT(DISTINCT YEAR(visited_at), MONTH(visited_at)) AS avg_monthly
            FROM visit_tracking
        
            LEFT JOIN browser_type
            ON visit_tracking.browser_type_id = browser_type.browser_type_id
            
            LEFT JOIN user_tracking
            ON visit_tracking.tracking_id = user_tracking.tracking_id
            
            LEFT JOIN user
            ON user_tracking.user_id = user.user_id
        ";

        $params = [];
        $types = "";

        $where = buildVisitFilters(
            $params,
            $types,
            $browserFilter,
            $dateFilter,
            $userFilter
        );

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);

            $stmt = $mysqli->prepare($sql);
        
            if (!empty($types)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $row = $result->fetch_assoc();

            $stmt->close();

        } else {

            $result = $mysqli->query($sql);

            $row = $result->fetch_assoc();
        }

        $mysqli->close();

        return $row['avg_monthly'] ? round($row['avg_monthly']) : 0;
    }


    function getVisitsPerDate(
        $browserFilter = 'AllBrowsers',
        $dateFilter = 'All',
        $userFilter = 'All'
        ) {

        $mysqli = getDataBase();
        $sql = "
            SELECT DATE(visited_at) as date,
            COUNT(*) as count

            FROM visit_tracking
            
            LEFT JOIN browser_type
            ON visit_tracking.browser_type_id = browser_type.browser_type_id
        
            LEFT JOIN user_tracking
            ON visit_tracking.tracking_id = user_tracking.tracking_id
        
            LEFT JOIN user
            ON user_tracking.user_id = user.user_id
        ";

        $params = [];
        $types = "";

        $where = buildVisitFilters(
            $params,
            $types,
            $browserFilter,
            $dateFilter,
            $userFilter
        );

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= "
            GROUP BY DATE(visited_at)
            ORDER BY DATE(visited_at);
        ";

        if (!empty($params)) {
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);

            $stmt->close();

        } else {
            
            $result = $mysqli->query($sql);
            $data = $result->fetch_all(MYSQLI_ASSOC);
        }

        $mysqli->close();
        return $data;
    }

    function getVisitsPerBrowser(
        $browserFilter = 'AllBrowsers',
        $dateFilter = 'All',
        $userFilter = 'All'
    ) {

        $mysqli = getDataBase();
        $sql = "
            SELECT browser_type.browser_name, 
            COUNT(*) AS count
            FROM visit_tracking

            LEFT JOIN browser_type
            ON visit_tracking.browser_type_id = browser_type.browser_type_id
            
            LEFT JOIN user_tracking
            ON visit_tracking.tracking_id = user_tracking.tracking_id
            
            LEFT JOIN user
            ON user_tracking.user_id = user.user_id
        ";

        $params = [];
        $types = "";

        $where = buildVisitFilters(
            $params,
            $types,
            $browserFilter,
            $dateFilter,
            $userFilter
        );

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= "
            GROUP BY browser_type.browser_name;
        ";

        if (!empty($params)) {
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param($types, ...$params);

            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

        } else {
            $result = $mysqli->query($sql);
            $data = $result->fetch_all(MYSQLI_ASSOC);
        }

        
        $mysqli->close();
        return $data;
    }

    function getVisitsPerWeek(
        $browserFilter = 'AllBrowsers',
        $dateFilter = 'All',
        $userFilter = 'All'
    ) {
        $mysqli = getDataBase();

        $sql = "
            SELECT YEARWEEK(visited_at, 1) AS week, COUNT(*) AS count
            FROM visit_tracking
            
            LEFT JOIN browser_type
            ON visit_tracking.browser_type_id = browser_type.browser_type_id
            
            LEFT JOIN user_tracking
            ON visit_tracking.tracking_id = user_tracking.tracking_id

            LEFT JOIN user
            ON user_tracking.user_id = user.user_id

        ";

        $params = [];
        $types = "";

        $where = buildVisitFilters(
            $params,
            $types,
            $browserFilter,
            $dateFilter,
            $userFilter
        );

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= "
            GROUP BY week
            ORDER BY week;
        ";

        if (!empty($params)) {
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);

            $stmt->close();

        } else {
            $result = $mysqli->query($sql);
            $data = $result->fetch_all(MYSQLI_ASSOC);
        }

        $mysqli->close();
        return $data;
    }

    function getVisitsPerDay(
        $browserFilter = 'AllBrowsers',
        $dateFilter = 'All',
        $userFilter = 'All'
    ) {
        $mysqli = getDataBase();
        $sql = "
            SELECT DAYOFWEEK(visited_at) AS day, COUNT(*) AS count
            FROM visit_tracking
            
            LEFT JOIN browser_type
            ON visit_tracking.browser_type_id = browser_type.browser_type_id
        
            LEFT JOIN user_tracking
            ON visit_tracking.tracking_id = user_tracking.tracking_id
        
            LEFT JOIN user
            ON user_tracking.user_id = user.user_id
        ";

        $params = [];
        $types = "";

        $where = buildVisitFilters(
            $params,
            $types,
            $browserFilter,
            $dateFilter,
            $userFilter
        );

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= "
            GROUP BY day
            ORDER BY day;
        ";

        if (!empty($params)) {
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param($types, ...$params);

            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);

            $stmt->close();

        } else {
            $result = $mysqli->query($sql);
            $data = $result->fetch_all(MYSQLI_ASSOC);
        }

        $mysqli->close();
        return $data;
    }

    
        function buildIncidentFilters(
        &$params,
        &$types,
        $dateFilter,
        $severityFilter,
        $categoryFilter
    ) {

        $where = [];

        if ($severityFilter != 'All') {
            $where[] = "incident.incident_severity = ?";
            $params[] = strtolower($severityFilter);
            $types .= "s";
        }

        if ($categoryFilter != 'All') {
            $where[] = "incident_type.incident_type_name = ?";
            $params[] = $categoryFilter;
            $types .= "s";
        }

        if ($dateFilter == 'Today') {
            $where[] = "DATE(incident.occurrence) = CURDATE()";
        }

        elseif ($dateFilter == 'Yesterday') {
            $where[] = "DATE(incident.occurrence) = CURDATE() - INTERVAL 1 DAY";
        }

        elseif ($dateFilter == 'Current Week') {
            $where[] = "
            YEARWEEK(incident.occurrence, 1) = YEARWEEK(CURDATE(), 1)";
        }

        elseif ($dateFilter == 'Current Month') {
            $where[] = "
                MONTH(incident.occurrence) = MONTH(CURDATE())
                AND YEAR(incident.occurrence) = YEAR(CURDATE())";
        }

        return $where;

    }

    function getIncidentCount(
        $dateFilter = 'All',
        $severityFilter = 'All',
        $categoryFilter = 'All'
    ) {
        $mysqli = getDataBase();

        $sql = "
            SELECT COUNT(*) AS count
            FROM incident
            
            LEFT JOIN incident_type
            ON incident.incident_type_id = incident_type.incident_type_id
        ";

        $params = [];
        $types = "";

        $where = buildIncidentFilters(
            $params,
            $types,
            $dateFilter,
            $severityFilter,
            $categoryFilter
        );

        if (!empty($where)) {
            $sql .= " WHERE " . implode( " AND ", $where);

            $stmt = $mysqli->prepare($sql);

        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt->close();
        
        } else {
            $result = $mysqli->query($sql);
            $row = $result->fetch_assoc();
        }

        $mysqli->close();
        return $row['count'];
    }

    function getResolvedIncidents(
        $dateFilter = 'All',
        $severityFilter = 'All',
        $categoryFilter = 'All'
    ) {
        
        $mysqli = getDataBase();

        $sql = "
            SELECT COUNT(DISTINCT incident.incident_id) AS count
            FROM incident
            
            LEFT JOIN incident_type
            ON incident.incident_type_id = incident_type.incident_type_id
            
            LEFT JOIN incident_update
            ON incident.incident_id = incident_update.incident_id
        ";

        $params = [];
        $types = "";

        $where = buildIncidentFilters(
            $params,
            $types,
            $dateFilter,
            $severityFilter,
            $categoryFilter
        );

        $where[] = "incident_update.status = 'resolved'";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
            $stmt = $mysqli->prepare($sql);

            if (!empty($types)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $stmt->close();

        } else {
            $result = $mysqli->query($sql);
            $row = $result->fetch_assoc();
        }

        $mysqli->close();
        return $row['count'];

    }

    function getAvgResolutionTime(
        $dateFilter = 'All',
        $severityFilter = 'All',
        $categoryFilter = 'All'
    ) {

    $mysqli = getDataBase();

    $sql = "
        SELECT AVG(
            TIMESTAMPDIFF(
                HOUR,
                incident.occurrence,
                incident_update.update_at
            )
        ) AS avg_resolution
         
        FROM incident
        
        LEFT JOIN incident_type
        ON incident.incident_type_id = incident_type.incident_type_id 
        
        LEFT JOIN incident_update
        ON incident.incident_id = incident_update.incident_id 
    ";

    $params = [];
    $types = "";
    
    $where = buildIncidentFilters(
        $params,
        $types,
        $dateFilter,
        $severityFilter,
        $categoryFilter
    );

    $where[] = "incident_update.status = 'resolved'";

    if (!empty($where)) {

        $sql .= " WHERE " . implode(" AND ", $where);
        $stmt = $mysqli->prepare($sql);

        if (!empty($types)) {

            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt->close();

    } else {

        $result = $mysqli->query($sql);
        $row = $result->fetch_assoc();
    }

    $mysqli->close();
    return round($row['avg_resolution']);

    }

    function getIncidentHistory(
        $dateFilter = 'All',
        $severityFilter = 'All',
        $categoryFilter = 'All'
    ) {

        $mysqli = getDataBase();

        $sql = "
            SELECT
                DATE(incident.occurrence) AS date,
                COUNT(*) AS count
                
            FROM incident
            
            LEFT JOIN incident_type
            ON incident.incident_type_id = incident_type.incident_type_id 
        ";

        $params = [];
        $types = "";

        $where = buildIncidentFilters(
            $params,
            $types,
            $dateFilter,
            $severityFilter,
            $categoryFilter
        );

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= "
            GROUP BY DATE(incident.occurrence)
            ORDER BY DATE(incident.occurrence)
        ";

        if (!empty($params)) {
            
            $stmt = $mysqli->prepare($sql);

            if (!empty($types))  {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            
            $stmt->close();

        } else {
            $result = $mysqli->query($sql);
            $data = $result->fetch_all(MYSQLI_ASSOC);
        }

        $mysqli->close();
        return $data;
    }

    function getTopIncidentCategories(
        $dateFilter = 'All',
        $severityFilter = 'All',
        $categoryFilter = 'All'
    ) {
        
        $mysqli = getDataBase();

        $sql = "
            SELECT
                incident_type.incident_type_name AS category,
                COUNT(*) AS count
                
                FROM incident

                LEFT JOIN incident_type
                ON incident.incident_type_id = incident_type.incident_type_id 
            ";
                
            $params = [];
            $types = "";

            $where = buildIncidentFilters(
                $params,
                $types,
                $dateFilter,
                $severityFilter,
                $categoryFilter
            );

            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }

            $sql .= "
                GROUP BY incident_type.incident_type_name
                ORDER BY count DESC
            ";

            if (!empty($params)) {

                $stmt = $mysqli->prepare($sql);

                if (!empty($types)) {
                    $stmt->bind_param($types, ...$params);
                }

                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_all(MYSQLI_ASSOC);

                $stmt->close();

            } else {

                $result = $mysqli->query($sql);
                $data = $result->fetch_all(MYSQLI_ASSOC);
            }

            $mysqli->close();
            return $data;
    }

    function getIncidentSeverityData(
        $dateFilter = 'All',
        $severityFilter = 'All',
        $categoryFilter = 'All'
    ) {

        $mysqli = getDataBase();

        $sql = "
            SELECT
                incident.incident_severity AS severity,
                COUNT(*) AS count
                
            FROM incident

            LEFT JOIN incident_type
            ON incident.incident_type_id = incident_type.incident_type_id
        ";

        $params = [];
        $types = "";

        $where = buildIncidentFilters(
            $params,
            $types,
            $dateFilter,
            $severityFilter,
            $categoryFilter
        );

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= "
            GROUP BY incident.incident_severity
        ";

        if (!empty($params)) {

            $stmt = $mysqli->prepare($sql);

            if (!empty($types)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);

            $stmt->close();

        } else {
            $result = $mysqli->query($sql);
            $data= $result->fetch_all(MYSQLI_ASSOC);
        }

        $mysqli->close();
        return $data;
    }

    function getResolutionTimeData(
        $dateFilter = 'All',
        $severityFilter = 'All',
        $categoryFilter = 'All'
    ) {

        $mysqli = getDataBase();

        $sql = "
            SELECT
                YEARWEEK(incident.occurrence, 1) AS week,
                AVG(
                    TIMESTAMPDIFF(HOUR, incident.occurrence, incident_update.update_at)) AS avg_time
         
            FROM incident

            LEFT JOIN incident_type
            ON incident.incident_type_id = incident_type.incident_type_id
        
            LEFT JOIN incident_update
            ON incident.incident_id = incident_update.incident_id
        
        ";

        $params = [];
        $types = "";

        $where = buildIncidentFilters(
            $params,
            $types,
            $dateFilter,
            $severityFilter,
            $categoryFilter
        );

        $where[] = "incident_update.status = 'resolved'";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= "
            GROUP BY YEARWEEK(incident.occurrence, 1)
            ORDER BY week
        ";

        if (!empty($params)) {

            $stmt = $mysqli->prepare($sql);

            if (!empty($types)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);

            $stmt->close();

        } else {
            $result = $mysqli->query($sql);
            $data= $result->fetch_all(MYSQLI_ASSOC);
        }

        $mysqli->close();
        return $data;
    }



    













































    /*===========================================================
                        VIKTORIA'S FUNCTIONS
                                |
                                V
    =============================================================*/

    /* new-case.php */

    function getIncidentTypes() {
        $mysqli = getDataBase();
        $result = $mysqli->query("SELECT * FROM incident_type")->fetch_all(MYSQLI_ASSOC);
        $mysqli->close();

        return $result;
    }

    function getAssets() {
        $mysqli = getDataBase();
        $result = $mysqli->query("SELECT * FROM asset")->fetch_all(MYSQLI_ASSOC);
        $mysqli->close();

        return $result;
    }

    /* cases.php */

    /* send-chat-message.php */

    function sendMessage($incidentId, $message, $userId) {
        $mysqli = getDataBase();

        $incidentId = (int)$incidentId;
        $userId = (int)$userId;

        $mysqli->begin_transaction();
        try {
            // 1. hämta den nuvarande statusen på ärendet så det inte ändras
            $query = $mysqli->query(
                "SELECT status FROM incident_update 
                 WHERE incident_id = $incidentId 
                 ORDER BY incident_update_id DESC 
                 LIMIT 1;"
            );
            $status = $query->fetch_assoc()['status'];

            // Fredrik säger: vi kollar att statusen är giltig så behöver vi inte använda ett prepared statement
            $allowedStatuses = ['pending', 'in progress', 'resolved'];
            if (!in_array($status, $allowedStatuses)) {
                throw new Exception("Unexpected status", 1);
            }

            // 2. skapar en ny incident_update
            $mysqli->query(
                "INSERT INTO incident_update (incident_id, status, user_id) 
                 VALUES ($incidentId, '$status', $userId);"
            );

            $incidentUpdateId = (int)($mysqli->insert_id);

            // 3. spara själva meddelandet i comment i databasen
            $stmt = $mysqli->prepare(
                "INSERT INTO comment (incident_update_id, comment_text)
                VALUES ($incidentUpdateId, ?);"
            );
            $stmt->bind_param('s', $message);
            $stmt->execute();
            $stmt->close();

            $mysqli->commit();
            return TRUE;
        } catch (Exception $e) {
            $mysqli->rollback();
            return FALSE;
        } finally {
            $mysqli->close();
        }
    }

    /* submit-incident.php + incident-service.php + update-case.php*/
    function insertIncident ($mysqli, $incidentTypeId, $description, $severity, $occurrence) {
        $incidentTypeId = (int)$incidentTypeId;

        $stmt = $mysqli->prepare(
            "INSERT INTO incident (incident_type_id, description, incident_severity, occurrence) 
             VALUES ($incidentTypeId, ?, ?, ?);"
        );
        $stmt->bind_param("sss", $description, $severity, $occurrence);
        $stmt->execute();

        $incidentId = $mysqli->insert_id;
        $stmt->close();

        return $incidentId;
    }

    function insertUpdate($mysqli, $incidentId, $userId, $status = 'pending') {
        $incidentId = (int)$incidentId;
        $userId = (int)$userId;

        $stmt = $mysqli->prepare(
            "INSERT INTO incident_update (incident_id, status, user_id) 
             VALUES ($incidentId, ?, $userId);"
        );
        $stmt->bind_param("s", $status);
        $stmt->execute();

        $updateId = $mysqli->insert_id;
        $stmt->close();

        return $updateId;
    }

    function insertAttachment($mysqli, $updateId, $fileName) {
        $updateId = (int)$updateId;
                
        $stmt_file = $mysqli->prepare(
            "INSERT INTO attachment (incident_update_id, attachment_file_path) 
             VALUES ($updateId, ?);"
        );
        $stmt_file->bind_param("s", $fileName);
        $stmt_file->execute();
        $stmt_file->close();
    }

    function insertAffectedAssets($mysqli, $assets, $incidentId) {
        $incidentId = (int)$incidentId;
        $sql = 
            "INSERT INTO affected_asset (asset_id, incident_id) 
             VALUES ";
        foreach ($assets as $asset_id) {
            $asset_id = (int)$asset_id;
            $sql .= "($asset_id, $incidentId), ";
        }
        $sql = substr($sql, 0, -2);

        $mysqli->query($sql);
    }

    function insertComment($mysqli, $updateId, $comment) {
        $updateId = (int)$updateId;
        $stmt_comment = $mysqli->prepare(
            "INSERT INTO comment (incident_update_id, comment_text)
             VALUES ($updateId, ?)"
        );
        $stmt_comment->bind_param("s", $comment);
        $stmt_comment->execute();
        $stmt_comment->close();
    }

?>

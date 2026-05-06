<?php
    function getDataBase() {
        $secretsPath = __DIR__ . '/../secrets.json';
        $secrets = json_decode(file_get_contents($secretsPath));

        $host = 'localhost';
        $username = $secrets->db_usr;
        $dbName = $secrets->db_name;
        $password = $secrets->db_pwd;

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
             VALUES (?, ?, ?, ?, ?, ?)'
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
             WHERE username = ?'
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

    function getUsers($limit, $offset, $abc='ASC', $roles=NULL, $search=NULL) {
        $allowedAbc = ['ASC', 'DESC'];
        if (!in_array($abc, $allowedAbc, TRUE)) {
            $abc = 'ASC';
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

        $sql .= 
            "ORDER BY username $abc
             LIMIT $limit OFFSET $offset";

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
             WHERE user_id = $userId"
        );

        $mysqli->close();
    }

    function deleteUser($userId) {
        $mysqli = getDataBase();

        $userId = (int)$userId;

        $mysqli->query(
            "DELETE FROM user
             WHERE user_id=$userId"
        );
        $mysqli->close();
    }

    function updateLastSeen($userId) {
        $mysqli = getDataBase();
        $userId = (int)$userId;
    
        $mysqli->query(
            "UPDATE user
             SET last_seen = NOW()
             WHERE user_id = $userId"
        );
        $mysqli->close();
    }

    function getCurrentEvents() {
        $mysqli = getDataBase();

        $query = $mysqli->query(
            "SELECT event_date, event_title, event_text
             FROM current_event
             ORDER BY event_date DESC
             LIMIT 5"
        );
        $events = $query->fetch_all(MYSQLI_ASSOC);
        $mysqli->close();

        return $events;
    }

    function trackVisit($page, $browser_id, $ip) {
        $mysqli = getDataBase();

        $stmt = $mysqli->prepare("SELECT page_id FROM page WHERE page_name = ?");
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
            VALUES (?, ?, INET_ATON(?))"
        );

        $stmt->bind_param("iis", $pageId, $browser_id, $ip);
        $stmt->execute();

        $stmt->close();
        $mysqli->close();
    }

    function getBrowserId($browserName) {
        $mysqli = getDataBase();

        $stmt = $mysqli->prepare("
            SELECT browser_type_id
            FROM browser_type
            WHERE browser_name = ?
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
            VALUES (?)
        ");

        $stmt->bind_param("s", $browserName);
        $stmt->execute();

        $browserId = $mysqli->insert_id;

        $stmt->close();
        $mysqli->close();

        return $browserId;
    }























    function getVisits() {
        $mysqli = getDataBase();

        $result = $mysqli->query("
        SELECT visit_tracking.tracking_id,
            page.page_name,
            browser_type.browser_name,
            INET_NTOA(visit_tracking.host_ip) AS host_ip,
            visit_tracking.visited_at
        FROM visit_tracking
        JOIN page ON visit_tracking.page_id = page.page_id
        LEFT JOIN browser_type ON visit_tracking.browser_type_id = browser_type.browser_type_id
        ORDER BY visit_tracking.visited_at DESC
        LIMIT 10

        ");

        $visits = $result->fetch_all(MYSQLI_ASSOC);
        $mysqli->close();
        return $visits;
    }

    function getTodayVisits() {
        $mysqli = getDataBase();
        $result = $mysqli->query("
            SELECT COUNT(*) AS count
            FROM visit_tracking
            WHERE DATE(visited_at) = CURDATE()
        ");

        $row = $result->fetch_assoc();
        $mysqli->close();
        return $row['count'];
    }

    function getAvgDailyVisits() {
        $mysqli = getDataBase();
        $result = $mysqli->query("
            SELECT COUNT(*) / COUNT(DISTINCT DATE(visited_at)) AS avg_daily
            FROM visit_tracking
        ");

        $row = $result->fetch_assoc();
        $mysqli->close();
        return round($row['avg_daily']);
    } 

    function getAvgWeeklyVisits() {
        $mysqli = getDataBase();
        $result = $mysqli->query("
            SELECT COUNT(*) / COUNT(DISTINCT YEARWEEK(visited_at, 1)) AS avg_weekly
            FROM visit_tracking
        ");

        $row = $result->fetch_assoc();
        $mysqli->close();
        return $row['avg_weekly'] ? round($row['avg_weekly']) : 0;
    }

        function getAvgMonthlyVisits() {
        $mysqli = getDataBase();
        $result = $mysqli->query("
            SELECT COUNT(*) / COUNT(DISTINCT YEAR(visited_at), MONTH(visited_at)) AS avg_monthly
            FROM visit_tracking
        ");

        $row = $result->fetch_assoc();
        $mysqli->close();
        return $row['avg_monthly'] ? round($row['avg_monthly']) : 0;
    }

    function getVisitsPerDate() {
        $mysqli = getDataBase();
        $result = $mysqli->query("
            SELECT DATE(visited_at) as date, COUNT(*) as count
            FROM visit_tracking
            GROUP BY DATE(visited_at)
            ORDER BY DATE(visited_at)
        ");

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $mysqli->close();
        return $data;
    }

    function getVisitsPerBrowser() {
        $mysqli = getDataBase();
        $result = $mysqli->query("
            SELECT browser_type.browser_name, COUNT(*) AS count
            FROM visit_tracking
            LEFT JOIN browser_type
            ON visit_tracking.browser_type_id = browser_type.browser_type_id
            GROUP BY browser_type.browser_name
        ");

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $mysqli->close();
        return $data;
    }

        function getVisitsPerWeek() {
        $mysqli = getDataBase();
        $result = $mysqli->query("
            SELECT YEARWEEK(visited_at, 1) AS week, COUNT(*) AS count
            FROM visit_tracking
            GROUP BY week
            ORDER BY week
        ");

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $mysqli->close();
        return $data;
    }

        function getVisitsPerDay() {
        $mysqli = getDataBase();
        $result = $mysqli->query("
            SELECT DAYOFWEEK(visited_at) AS day, COUNT(*) AS count
            FROM visit_tracking
            GROUP BY day
            ORDER BY day
        ");

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $mysqli->close();
        return $data;
    }

?>
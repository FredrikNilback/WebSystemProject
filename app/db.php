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

    function getUsers($limit, $offset, $abc='ASC', $roles=NULL) {
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
        
        if ($roles) {
            $sql .= "WHERE ";
            $iterations = count($roles);
            for ($i = 0; $i < $iterations - 1; $i++) {
                $sql .= "user_role = '" . $roles[$i]. "' OR ";
            }
            $sql .= "user_role = '" . $roles[$iterations - 1] . "' ";
        }

        $sql .= 
            "ORDER BY username $abc
             LIMIT $limit OFFSET $offset";

        $query = $mysqli->query($sql);
        $users = $query->fetch_all(MYSQLI_ASSOC);

        $mysqli->close();

        return $users;
    }

    function getUserCount($roles=NULL) {
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

        if ($roles) {
            $sql .= "WHERE ";
            $iterations = count($roles);
            for ($i = 0; $i < $iterations - 1; $i++) {
                $sql .= "user_role = '" . $roles[$i]. "' OR ";
            }
            $sql .= "user_role = '" . $roles[$iterations - 1] . "' "; 
        }

        $count = $mysqli->query($sql)->fetch_assoc()['count'];
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

?>
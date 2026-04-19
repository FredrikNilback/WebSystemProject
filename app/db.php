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
            return false;
        }

        $stmt->close();
        $mysqli->close();
        return $user; 
    }

    function getUsers($limit, $offset, $role=NULL) {
        $mysqli = getDataBase();
        
        if (!$role) {
            $stmt = $mysqli->prepare(
                'SELECT 
                    user_id, 
                    CONCAT(first_name," ", last_name) AS full_name,
                    username,
                    user_email,
                    user_role,
                    last_seen
                FROM user
                ORDER BY username
                LIMIT ? OFFSET ?'
            );

            $stmt->bind_param('ii', $limit, $offset);
        } else {
            $stmt = $mysqli->prepare(
                'SELECT 
                    user_id, 
                    CONCAT(first_name," ", last_name) AS full_name,
                    username,
                    user_email,
                    user_role,
                    last_seen
                FROM user
                WHERE user_role = ?
                ORDER BY username
                LIMIT ? OFFSET ?'
            );

            $stmt->bind_param('sii', $role, $limit, $offset);
        }

        $stmt->execute();
        $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        $mysqli->close();

        return $users;
    }

    function getUserCount($role=NULL) {
        $mysqli = getDataBase();
        $count = 0;

        if ($role) {
            $stmt = $mysqli->prepare(
                'SELECT COUNT(*) AS count 
                 FROM user 
                 WHERE user_role = ?');
            $stmt->bind_param('s', $role);
            $stmt->execute();
            $count = $stmt->get_result()->fetch_assoc()['count'];
            $stmt->close();
        } else {
            $count = $mysqli->query('SELECT COUNT(*) AS count FROM user')->fetch_assoc()['count'];
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

    function getCurrentEvents() {

    }

    function updateLastSeen($userId, $mysqli = null) {
        if (!$mysqli) {
            $mysqli = getDataBase();
        }
    
        $userId = (int)$userId;
    
        $mysqli->query(
            "UPDATE user
             SET last_seen = NOW()
             WHERE user_id = $userId"
        );
        $mysqli->close();
    }

?>
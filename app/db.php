<?php
    function getDataBase() {
        $secretsPath = __DIR__ . '/../secrets.json';
        $secrets = json_decode(file_get_contents($secretsPath));

        $host = "localhost";
        $username = $secrets->db_usr;
        $dbName = $secrets->db_name;
        $password = $secrets->db_pwd;

        $mysqli = new mysqli($host, $username, $password, $dbName);

        if ($mysqli->connect_error) {
            die("Could not establish database connection");
        }

        return $mysqli;
    }

    function createUser($username, $firstname, $lastname, $email, $password, $role) {
        $mysqli = getDataBase();
        $passwordHash =  password_hash($password, PASSWORD_DEFAULT);

        $stmt = $mysqli->prepare(
            "INSERT INTO user (username, first_name, last_name, user_email, password_hash, user_role)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssssss",
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
            "SELECT * 
             FROM user
             WHERE username = ?"
        );
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_object();

        $stmt->close();
        $mysqli->close();

        if (!$user || !password_verify($password, $user->password_hash)) {
            return false;
        }

        return $user; 
    }

?>
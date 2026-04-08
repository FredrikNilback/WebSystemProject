<?php
$host = "localhost";
$secrets = json_decode(file_get_contents('../../secrets.json'));
$dbname = $secrets->db_name;
$username = $secrets->db_usr;
$password = $secrets->db_pwd;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected successfully<br>";

    $stmt = $pdo->query("SELECT * FROM asset LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo "Row fetched:<br>";
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    } else {
        echo "No rows found in table.";
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
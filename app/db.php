<?php
$secrets = json_decode(file_get_contents('../../../secrets.json'));

$host = "localhost";
$username = $secrets->db_usr;
$dbName = $secrets->db_name;
$password = $secrets->db_pwd;

$mysqli = new mysqli($host, $username, $password, $dbName);
?>
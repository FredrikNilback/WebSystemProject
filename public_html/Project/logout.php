<?php
    require_once '../../app/logout.php';
    logout();
    header('index.php');
    exit;
?>
<?php
    $headerImg = "<img src='images/company_logo.png' alt='company logo' id='company-logo'>";
    $header = 
    <<<HTML
    <header>
        <a href='user-homepage.php'>
            $headerImg
        </a>
        <h1>NFV incident report portal</h1>
    </header>
    HTML;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <title>NFV incident report portal - <?=$activePage?></title>
    <link rel='icon' type='image/x-icon' href='images/company_logo.png'>
    <link rel='stylesheet' href='css/styles.css'>
    <link rel='stylesheet' href='css/<?=$activePage?>.css'>
    <script src='js/<?=$activePage?>.js' defer></script>
</head>

<body>
    <?php 
        if (!($activePage == 'login' || $activePage == 'unauthorized')) {
            echo $header;
        }
    ?>

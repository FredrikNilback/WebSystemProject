<?php
    $anchorHeaderImg = 
    <<<HTML
    <a href='user-homepage.php'>
                <img src='images/company_logo.png' alt='company logo' id='company-logo'>
            </a>
    HTML;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <title>NFV incident report portal - <?=$activePage?></title>
    <link rel='stylesheet' href='css/styles.css'>
    <link rel='stylesheet' href='css/<?=$activePage?>.css'>
    <script src='js/<?=$activePage?>.js' defer></script>
</head>

<body>
    <header>
        <?php 
            if ($activePage == 'login' || $activePage == 'unauthorized') {
                echo "<img src='images/company_logo.png' alt='company logo' id='company-logo'>";
            } else {
                echo $anchorHeaderImg;
            }
        ?>

        <h1>NFV incident report portal</h1>
    </header>

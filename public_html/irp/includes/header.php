<?php
    $headerImg = "<img src='images/company_logo.png' alt='company logo' id='company-logo'>";
    $titleApendage = NULL;
    switch ($activePage) {
        case 'user-homepage': 
            $titleApendage = 'Homepage';
            break;
        case 'manage-users':
            $titleApendage = 'User Management';
            break;
        default:
            $titleApendage = $activePage;
    }
    $header = 
    <<<HTML
    <header>
        <a href='user-homepage.php'>
            $headerImg
        </a>
        <h1>NFV incident report portal - $titleApendage</h1>
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
    <?php if ($activePage == "visits"):?>
        <link rel='stylesheet' href='css/analytics.css'>
    <?php else:?>
    <link rel='stylesheet' href='css/<?=$activePage?>.css'>
    <?php endif;?>
    <script src='js/<?=$activePage?>.js' defer></script>
</head>

<body>
    <?php 
        if (!($activePage == 'login' || $activePage == 'unauthorized')) {
            echo $header;
        }
    ?>

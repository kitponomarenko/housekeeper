<!DOCTYPE html>
<html lang="ru">
    <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    
        <?php include('php/construct_page.php') ?>
        
        <title><?php echo($page['title'])?></title>
        
        <meta name="description" content="<?php echo($page['description'])?>">
        <meta name="keywords" content="<?php echo($page['keywords'])?>">
        <meta name="author" content="Ponomarenko Nikita">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="theme-color" content="#1BF58B">
        
        <link rel="shortcut icon" type="images/png" href="images/favicon/logo.png">
        <link rel="apple-touch-icon" href="images/favicon/apple-touch-icon.png">
        <link rel="apple-touch-icon" sizes="72x72" href="images/favicon/apple-touch-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="114x114" href="images/favicon/apple-touch-icon-114x114.png">

        <link rel="stylesheet" href="css/core.css">
        <?php echo($page['css'])?>
    </head>
    <body>
        
    <div class="pop_up_fader"></div>
    <div id="pop_up" class="pop_up"></div>
        
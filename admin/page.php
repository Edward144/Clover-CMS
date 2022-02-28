<?php

    require_once(dirname(__DIR__) . '/includes/database.php');
    require_once(dirname(__DIR__) . '/includes/functions.php');

    $fileToCheck = dirname(__DIR__) . '/includes/plugins/' . $_GET['file'] . '.php';

    if(file_exists($fileToCheck)) {
        require_once($fileToCheck);
        exit();
    }
    else {
        http_response_code(404);
        include_once(dirname(__FILE__) . '/404.php');
        exit();
    }

?>
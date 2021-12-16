<?php
	
	session_start();
    
    define('CMS_VERSION', 'v1.1.2');

    $setupLoc = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . '/' . explode($_SERVER['DOCUMENT_ROOT'] . '/', dirname(__DIR__))[1] . '/admin/setup';

    if($_SERVER['REQUEST_URI'] != explode($_SERVER['SERVER_NAME'], $setupLoc)[1]) {
        if(!file_exists(dirname(__FILE__) . '/settings.php')) {
            header('Location: ' . $setupLoc);
            exit();
        }
        
        include_once(dirname(__FILE__) . '/settings.php');
        $mysqli = new mysqli($hostname, $username, $password, $database);

        if($mysqli->connect_error) {
            die('Failed to connect to database');
        }

        //Check if setup is complete
        $setupCheck = $mysqli->query("SELECT value FROM `settings` WHERE name = 'setup_complete' AND value = 1 LIMIT 1");

        if($setupCheck->num_rows != 1) {
            header('Location: ' . $setupLoc);
            exit();
        }

        unset($hostname);
        unset($database);
        unset($username);
        unset($password);
        unset($setupLoc);
    }

?>

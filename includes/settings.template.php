<?php

    //Database Details
	$hostname = '';
    $database = '';
    $username = '';
	$password = '';

	define('ROOT_DIR', '/');
	define('BASE_DIR', (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . ROOT_DIR);
    define('CMS_VERSION', 'v1.0.0');

?>
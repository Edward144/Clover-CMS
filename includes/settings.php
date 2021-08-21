<?php

	//Database Details
	$hostname = 'localhost';
    $database = 'edhwd_clovercms';
    $username = 'edhwd_admin';
	$password = '977foCJ7I49W';

	define('ROOT_DIR', '/clover-cms/');
	define('BASE_DIR', (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . ROOT_DIR);
    define('CMS_VERSION', 'v1.0.0');

?>
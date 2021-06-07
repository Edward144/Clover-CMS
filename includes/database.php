<?php
	
	session_start();

	require_once(dirname(__FILE__) . '/settings.php');

	$mysqli = new mysqli($hostname, $username, $password, $database);

	if($mysqli->connect_error) {
		die('Connection Error: ' . $mysqli->connect_error);
	}

	unset($hostname);
	unset($database);
	unset($username);
	unset($password);

?>
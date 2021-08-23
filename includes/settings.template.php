<?php

    //Database Details
	$hostname = '';
    $database = '';
    $username = '';
	$password = '';

	define('ROOT_DIR', '/');
	define('BASE_DIR', (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . ROOT_DIR);
    define('CMS_VERSION', 'v1.0.0');

    //Pages that must always be accessible by cms user roles
    //Manage content is included here as we will differentiate content by post type
    define('ALLOWED_PAGES', ['404.php', 'index.php', 'setup.php', 'template.php', 'manage-content.php']);

?>
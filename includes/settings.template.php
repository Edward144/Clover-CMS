<?php

    //Database Details
	$hostname = '';
    $database = '';
    $username = '';
	$password = '';

	define('ROOT_DIR', '/');
	define('BASE_DIR', (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . ROOT_DIR);

    //Pages that must always be accessible by cms user roles
    //Manage content is included here as we will differentiate content by post type
    define('ALLOWED_PAGES', ['404.php', 'index.php', 'setup.php', 'template.php', 'manage-content.php', 'my-profile.php']);

    //The given name for none admin users
    define('BASIC_USER', 'Subscriber');

    //Default admin sidebar menu items
    define('ADMIN_MENU', [
        [
            'name' => 'Dashboard',
            'link' => 'admin/',
            'icon' => 'fa-chart-line'
        ],
        [
            'name' => 'Comments',
            'link' => 'admin/manage-comments',
            'icon' => 'fa-comments',
            'filename' => 'manage-comments.php'
        ],
        [
            'name' => 'Forms',
            'link' => 'admin/manage-forms',
            'icon' => 'fa-pen-alt',
            'filename' => 'manage-forms.php'
        ],
        [
            'name' => 'Navigation',
            'link' => 'admin/manage-navigation',
            'icon' => 'fa-sitemap',
            'filename' => 'manage-navigation.php'
        ],
        [
            'name' => 'Users',
            'link' => 'admin/manage-users',
            'icon' => 'fa-users',
            'filename' => 'manage-users.php'
        ],
        [
            'name' => 'Settings',
            'link' => 'admin/settings',
            'icon' => 'fa-cogs',
            'filename' => 'settings.php'
        ],
        [
            'name' => 'Profile',
            'link' => 'admin/my-profile',
            'icon' => 'fa-user-cog'
        ]
    ]);
    $adminMenu = ADMIN_MENU;

?>
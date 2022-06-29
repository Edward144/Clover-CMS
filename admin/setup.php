<?php

    require_once(dirname(__FILE__, 2) . '/includes/database.php');
    require_once(dirname(__FILE__, 2) . '/includes/functions.php');

    if(file_exists(dirname(__FILE__, 2) . '/includes/settings.php') && defined('ROOT_DIR') && !isset($_GET['update'])) {
        header('Location: ' . ROOT_DIR);
        exit();
    }
    
    if(isset($_POST['doSetup']) || isset($_GET['update'])) {
        if(isset($_GET['update'])) {
            goto runupdate;
        }
        
        //Check write permissions for settings
        $template = dirname(__FILE__, 2) . '/includes/settings.template.php';
        $settings = fopen(dirname(__FILE__, 2) . '/includes/settings.php', 'w');
        
        if(!$settings || !file_exists($template)) {
            $status = 'danger';
            $setupmessage = 'Failed to create settings file, ensure permissions are correct for /includes directory and "settings.template.php" exists';
        }
        else {
            //Connect to the database
            $mysqli = new mysqli($_POST['hostname'], $_POST['username'], $_POST['password'], $_POST['database']);

            if($mysqli->connect_error) {
                $status = 'danger';
                $setupmessage = 'Failed to connect to the database, please check your details';
            }
            else {
                //Create the required tables
                runupdate:
                $queryErrors = '';
                $queries = [
                    //Settings
                    "CREATE TABLE IF NOT EXISTS `settings` (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(191) UNIQUE,
                        value VARCHAR(255)
                    )",
                    "INSERT IGNORE INTO `settings` (name, value) VALUES
                    ('setup_complete', 0),
                    ('website_name', ''),
                    ('address_1', ''),
                    ('address_2', ''),
                    ('city', ''),
                    ('county', ''),
                    ('postcode', ''),
                    ('phone', ''),
                    ('email', ''),
                    ('google_analytics', ''),
                    ('recaptcha_sitekey_v3', ''),
                    ('recaptcha_secretkey_v3', ''),
                    ('recaptcha_sitekey_v2', ''),
                    ('recaptcha_secretkey_v2', ''),
                    ('logo', ''),
                    ('homepage', '1'),
                    ('newspage', NULL),
                    ('comment_approval', 'unapproved'),
                    ('allow_signup', 'true'),
                    ('allow_signin', 'true'),
                    ('use_smtp', 'false'),
                    ('smtp_host', ''),
                    ('smtp_username', ''),
                    ('smtp_password', ''),
                    ('smtp_port', ''),
                    ('mail_from_address', ''),
                    ('mail_from_friendly', ''),
                    ('reply_to_address', ''),
                    ('reply_to_friendly', '')",
                    //Users
                    "CREATE TABLE IF NOT EXISTS `users` (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        first_name VARCHAR(255),
                        last_name VARCHAR(255),
                        email VARCHAR(191) UNIQUE,
                        username VARCHAR(191) UNIQUE,
                        password VARCHAR(60),
                        role INT NOT NULL DEFAULT 1,
                        last_signin DATETIME DEFAULT NULL
                    )",
                    //Users Pending
                    "CREATE TABLE IF NOT EXISTS`users_pending` LIKE `users`;
                    DROP INDEX IF EXISTS email ON `users_pending`;
                    DROP INDEX IF EXISTS username ON `users_pending`;",
                    "CREATE TABLE IF NOT EXISTS `roles` (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(191) UNIQUE,
                        access LONGTEXT
                    )",
                    //Roles
                    "INSERT IGNORE INTO `roles` (id, name, access) VALUES(1, 'Admin', 'ALL'), (2, 'Standard', NULL)",
                    "CREATE TABLE IF NOT EXISTS `password_reset` (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        email VARCHAR(191) UNIQUE,
                        token VARCHAR(191) UNIQUE,
                        date_generated DATETIME DEFAULT CURRENT_TIMESTAMP(),
                        expired INT DEFAULT 0
                    )",
                    //Post Types
                    "CREATE TABLE IF NOT EXISTS `post_types` (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(191) UNIQUE,
                        icon VARCHAR(50)
                    )",
                    "INSERT IGNORE INTO `post_types` (name, icon) VALUES ('pages', 'fa-file-alt'), ('news', 'fa-newspaper')",
                    //Posts
                    "CREATE TABLE IF NOT EXISTS `posts` (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        post_type_id INT,
                        name VARCHAR(255),
                        excerpt VARCHAR(500),
                        content TEXT,
                        url VARCHAR(191) UNIQUE,
                        author VARCHAR(255),
                        featured_image VARCHAR(500),
                        carousel LONGTEXT,
                        template VARCHAR(255),
                        date_created DATETIME DEFAULT CURRENT_TIMESTAMP(),
                        last_edited DATETIME DEFAULT CURRENT_TIMESTAMP(),
                        last_edited_by INT,
                        state  INT DEFAULT 0,
                        meta_title VARCHAR(255),
                        meta_description VARCHAR(500),
                        meta_author VARCHAR(255),
                        meta_keywords VARCHAR(255),
                        allow_comments INT DEFAULT 0
                    )",
                    "INSERT IGNORE INTO `posts` (id, post_type_id, name, content, url, author, state) VALUES(1, 1, 'Welcome', '<h1>Welcome to Clover CMS</h1><p>Set up is complete, you can now start creating content.</p>', '.', 'Admin User', 2)",
                    //Comments
                    "CREATE TABLE IF NOT EXISTS `comments` (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        post_id INT,
                        author VARCHAR(255) DEFAULT NULL,
                        registered INT DEFAULT 0,
                        content VARCHAR(1000) DEFAULT NULL,
                        reply_to INT DEFAULT 0,
                        approved INT DEFAULT 0,
                        date_posted DATETIME DEFAULT CURRENT_TIMESTAMP(),
                        ip_address VARCHAR(25) DEFAULT NULL,
                        modified INT DEFAULT 0,
                        original_content VARCHAR(1000) DEFAULT NULL,
                        edited_from VARCHAR(1000) DEFAULT NULL,
                        edited INT DEFAULT 0
                    )",
                    //Navigation
                    "CREATE TABLE IF NOT EXISTS `navigation_menus` (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(191) UNIQUE
                    )",
                    "INSERT IGNORE INTO `navigation_menus` (name) VALUES ('Breadcrumbs')",
                    "CREATE TABLE IF NOT EXISTS `navigation_structure` (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        menu_id INT DEFAULT 0,
                        name VARCHAR(255),
                        url VARCHAR(500),
                        visible INT DEFAULT 0,
                        target VARCHAR(50) DEFAULT NULL,
                        parent_id INT DEFAULT 0,
                        position INT DEFAULT 0
                    )",
                    //Social Links
                    "CREATE TABLE IF NOT EXISTS `social_links` (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(191) UNIQUE,
                        link VARCHAR(255)
                    )",
                    "INSERT IGNORE INTO `social_links` (name) VALUES('facebook'), ('twitter'), ('instagram'), ('youtube'), ('linkedin')",
                    //Forms
                    "CREATE TABLE IF NOT EXISTS `forms` (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255),
                        structure LONGTEXT
                    )",
                    //Mail Log
                    "CREATE TABLE IF NOT EXISTS `mail_log` (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        json_data LONGTEXT DEFAULT NULL
                    )"
                ];

                foreach($queries as $query) {
                    if(strpos($query, ';') !== false) {
                        $mysqli->multi_query($query);
                    }
                    else {
                        $mysqli->query($query);
                    }

                    while($mysqli->more_results()) {
                        $mysqli->next_result();
                    }

                    if($mysqli->error) {
                        $queryErrors .= $mysqli->error . '<br><br>';
                    }
                }

                //If we are updating then only update the database tables and do nothing else
                if(isset($_GET['update'])) {
                    if(!empty($queryErrors)) {
                        echo $queryErrors . '<a href="./">Return to dashboard</a>';
                    }
                    else {
                        header('Location: ./');
                    }

                    exit();
                }

                //Create the settings file
                $template = file($template);
                $templateContent = '';

                $rootdir = '/' . explode($_SERVER['DOCUMENT_ROOT'] . '/', dirname(__FILE__, 2))[1] . '/';
                $rootdir = ($rootdir == '//' ? '/' : $rootdir);
                define('ROOT_DIR', $rootdir);

                foreach($template as $i => $line) {
                    switch ($i) {
                        case 0:
                            $templateContent .= '<?php' . "\n";
                            break;
                        case 3:
                            $templateContent .= "\t" . 'define(\'DB_HOST\', \'' . $_POST['hostname'] . '\');' . "\n";
                            break;
                        case 4:
                            $templateContent .= "\t" . 'define(\'DB_DATABASE\', \'' . $_POST['database'] . '\');' . "\n";
                            break;
                        case 5:
                            $templateContent .= "\t" . 'define(\'DB_USERNAME\', \'' . $_POST['username'] . '\');' . "\n";
                            break;
                        case 6:
                            $templateContent .= "\t" . 'define(\'DB_PASSWORD\', \'' . $_POST['password'] . '\');' . "\n";
                            break;
                        case 8:
                            $templateContent .= "\t" . 'define(\'ROOT_DIR\', \'' . $rootdir . '\');' . "\n";
                            break;
                        default:
                            $templateContent .= $line;
                            break;
                        }
                }

                fwrite($settings, $templateContent);
                fclose($settings);

                //Create the admin user
                $password = password_hash($_POST['aPassword'], PASSWORD_BCRYPT);

                $createAdmin = $mysqli->prepare("INSERT IGNORE INTO `users` (first_name, last_name, username, email, password, role) VALUES('Admin', 'User', 'admin', ?, ?, 1)");
                $createAdmin->bind_param('ss', $_POST['aEmail'], $password);
                $createAdmin->execute();

                if($createAdmin->error) {
                    $status = 'danger';
                    $setupmessage = 'Failed to create admin user';
                }
                else {
                    //Email confirmation to admin
                    $to = $_POST['aEmail'];
                    $subject = 'Clover CMS has finished setup';
                    $content = 
                        '<p>Welcome ' . $_POST['aEmail'] . ',</p>
                        <p>Clover CMS has finished it\'s setup and you can now login using the link below. You can login with username <strong>admin</strong> and your chosen password.</p>

                        <div style="margin: 1rem auto;">
                            <a href="' . (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . $rootdir . 'admin-login" target="_blank" style="border-radius: 10px; box-sizing: border-box; background: #009688; color: #fff; padding: 0.5rem; border: 0; text-decoration: none;">Click here to login</a>
                        </div>';

                    sendemail($to, $subject, $content);

                    //Mark as setup completed    
                    $mysqli->query("UPDATE `settings` SET value = 1 WHERE name = 'setup_complete'");
                    $status = 'success';
                    $setupmessage = 'Setup is complete you can now login with username <strong>admin</strong> and your chosen password, by clicking <a href="../admin-login">here</a>';
                }
            }
        }
    }

?>

<!DOCTYPE html>

<html lang="en">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<title>Clover CMS Setup</title>
		
		<link rel="preconnect" href="https://fonts.gstatic.com">
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
		<link rel="stylesheet" href="../css/admin.min.css">
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
		<script src="js/bootstrap.bundle.min.js"></script>
		<script src="https://kit.fontawesome.com/a05d626b05.js" crossorigin="anonymous"></script>
    </head>
    
    <body>
        <div class="wrapper d-flex flex-column align-items-stretch" id="setupPage">
			<div class="main container-fluid flex-column flex-grow-1">				
				<div class="content">
					<form id="setup" class="shadow rounded bg-white overflow-hidden mx-auto my-5" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
						<input type="hidden" name="doSetup">
						
						<div class="formHeader text-center bg-primary text-white p-3">
							<h1 class="mb-0"><img src="../images/clover-cms-logo.png" alt="Clover CMS Logo" style="width: 50px;"> Clover CMS Setup</h1>
						</div>

						<div class="formBody border-right border-left border-right border-light p-3">
                            <?php if($status != 'success') : ?>
                                <h3>Database Connection</h3>
                                <p>These are the details for your MySQL database.</p>
                            
                                <div class="mb-3">
                                    <label>Hostname</label>

                                    <div class="input-group">
                                        <span class="input-group-text"><span class="fa fa-cloud"></span></span>
                                        <input type="text" class="form-control" name="hostname" value="<?php echo (!empty($_POST['hostname']) ? $_POST['hostname'] : 'localhost'); ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label>Database Name</label>

                                    <div class="input-group">
                                        <span class="input-group-text"><span class="fa fa-database"></span></span>
                                        <input type="text" class="form-control" name="database" value="<?php echo (!empty($_POST['database']) ? $_POST['database'] : ''); ?>" required autofocus>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label>Username</label>

                                    <div class="input-group">
                                        <span class="input-group-text"><span class="fa fa-user"></span></span>
                                        <input type="text" class="form-control" name="username" value="<?php echo (!empty($_POST['username']) ? $_POST['username'] : ''); ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label>Password</label>

                                    <div class="input-group">
                                        <span class="input-group-text"><span class="fa fa-key"></span></span>
                                        <input type="password" class="form-control" name="password" value="<?php echo (!empty($_POST['password']) ? $_POST['password'] : ''); ?>" required>
                                    </div>
                                </div>

                                <hr>

                                <h3>Your New Admin Account</h3>
                                <p>These are the details you will use to login to the CMS.</p>
                            
                                <div class="mb-3">
                                    <label>Email</label>

                                    <div class="input-group">
                                        <span class="input-group-text"><span class="fa fa-at"></span></span>
                                        <input type="email" class="form-control" name="aEmail" value="<?php echo (!empty($_POST['aEmail']) ? $_POST['aEmail'] : ''); ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label>Password</label>

                                    <div class="input-group">
                                        <span class="input-group-text"><span class="fa fa-key"></span></span>
                                        <input type="password" class="form-control" name="aPassword" value="<?php echo (!empty($_POST['aPassword']) ? $_POST['aPassword'] : ''); ?>" required>
                                    </div>
                                </div>
                            
                                <div class="mb-3">
                                    <label>Confirm Password</label>

                                    <div class="input-group">
                                        <span class="input-group-text"><span class="fa fa-key"></span></span>
                                        <input type="password" class="form-control" name="aPasswordConf" value="<?php echo (!empty($_POST['aPasswordConf']) ? $_POST['aPasswordConf'] : ''); ?>" required>
                                    </div>
                                </div>
                            
                                <div class="d-flex align-items-center">
                                    <input type="submit" class="btn btn-primary me-3" value="Submit">
                                </div>
							<?php endif; ?>
                            
							<?php if(isset($setupmessage)) : ?>
								<div class="alert alert-<?php echo $status; ?> mb-0 mt-3">
									<?php echo $setupmessage; ?>
								</div>
							<?php endif; ?>
						</div>
					</form>
				</div>
			</div>
		</div>
    </body>
    
    <script>
        //Validate form
        $("form").submit(function() {
            var valid = true;
            var passChar = 8;

            $(this).find(".invalid-feedback").remove();
            $(this).find(".is-invalid").removeClass("is-invalid");

            //Validate Passwords
            if($(this).find("input[name='aPassword']").length && $(this).find("input[name='aPasswordConf']").length) {
                var pass = $(this).find("input[name='aPassword']");
                var passConf = $(this).find("input[name='aPasswordConf']");

                if(pass.val().length || passConf.val().length) {
                    if(pass.val().length < passChar) {
                        pass.addClass("is-invalid");
                        $("<div class='invalid-feedback'>Password must be at least " + passChar + " characters</div>").insertAfter(pass);

                        valid = false;
                    }
                    else if(!/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/.test(pass.val())) {
                        pass.addClass("is-invalid");
                        $("<div class='invalid-feedback'>Password must contain at least one lowercase, one uppercase and one digit</div>").insertAfter(pass);

                        valid = false;
                    }
                    else if(pass.val() != passConf.val()) {
                        pass.addClass("is-invalid");
                        passConf.addClass("is-invalid");
                        $("<div class='invalid-feedback'>Passwords do not match</div>").insertAfter(passConf);

                        valid = false;
                    }
                }
            }

            //Validate Urls
            if($(this).find("input[name='url']").length) {
                var url = $(this).find("input[name='url']");

                if(!/^[a-zA-Z0-9\:\/\-\_\+\?\&\=\#\.]+$/.test(url.val())) {
                    url.addClass("is-invalid");
                    $("<div class='invalid-feedback'>Url contains invalid characters. Allowed characters are A-Z, 0-9, :, /, -, _, +, ?, &, =, #, .</div>").insertAfter(url);

                    valid = false;
                }
            }

            if(valid == false) {
                event.preventDefault();
                return;
            }
        });
    </script>
</html>
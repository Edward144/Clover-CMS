<?php 
	require_once(dirname(__FILE__) . '/database.php'); 
	require_once(dirname(__FILE__) . '/functions.php');

    $settings = $mysqli->query("SELECT * FROM `settings`");
    $settingsArray = [];

    if($settings->num_rows > 0) {
        while($setting = $settings->fetch_assoc()) {
            $settingsArray[$setting['name']] = $setting['value'];
        }
    }
?>

<!DOCTYPE html>

<html lang="en">
	<head>
		<base href="<?php echo BASE_DIR; ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta charset="UTF-8">
		
		<?php 
            metadata($title, $description, $keywords, $author); 
            googleanalytics();
        ?>
		
		<link rel="preconnect" href="https://fonts.gstatic.com">
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.3/css/all.css" integrity="sha384-SZXxX4whJ79/gErwcOYf+zWLeJdY/qpuqC4cAa9rOGUstPomtqpuNWT9wdPEn2fk" crossorigin="anonymous">
		<link rel="stylesheet" href="css/style.min.css">
		<link rel="stylesheet" href="css/jquery.fancybox.min.css">
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
		<script src="bootstrap-5.0.1/bootstrap.min.js"></script>
		<script src="js/docRoot.min.js"></script>
		<script src="js/tinymce/tinymce.min.js"></script>
		<script src="js/jquery.fancybox.min.js"></script>
	</head>
	
	<body>
		<div class="wrapper">
			<header id="pageHeader" class="bg-secondary text-light">
				<div class="headerInner container-xl">
					<div class="row py-3">
						<div class="col-sm-3">
                            <a href="<?php echo ROOT_DIR; ?>" class="siteTitle">
                                <?php if(!empty($settingsArray['logo'])) : ?>
                                    <img src="<?php echo $settingsArray['logo']; ?>" class="siteLogo img-fluid" alt="<?php echo(!empty($settingsArray['website_name']) ? $settingsArray['website_name'] : 'Website '); ?>Logo">    
                                <?php else : ?>
                                    <h2 class="siteName"><?php echo (!empty($settingsArray['website_name']) ? $settingsArray['website_name'] : ''); ?></h2>
                                <?php endif; ?>
                            </a>
						</div>
                        
                        <div class="col d-flex flex-column justify-content-between">
                            <?php if(!empty($settingsArray['phone']) || !empty($settingsArray['email'])) : ?>
                                <div class="contact d-flex align-items-center justify-content-end">
                                    <?php if(!empty($settingsArray['phone'])) : ?>
                                        <p class="phone">
                                            <span class="fa fa-phone me-1"></span>
                                            <a href="tel: <?php echo $settingsArray['phone']; ?>" class="link-light"><?php echo $settingsArray['phone']; ?></a>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if(!empty($settingsArray['phone'])) : ?>
                                        <p class="email ms-3">
                                            <span class="fa fa-envelope me-1"></span>
                                            <a href="mailto: <?php echo $settingsArray['email']; ?>" class="link-light"><?php echo $settingsArray['email']; ?></a>
                                        </p>
                                    <?php endif; ?>                                    
                                </div>
                            <?php endif; ?>
                            
                            <?php new navbar(); ?>
                        </div>
					</div>
				</div>
			</header>
			
			<div class="main">
                <div class="container-xl">
                    <div class="row">
                        <div class="col">
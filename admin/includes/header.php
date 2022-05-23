<?php 
	require_once(dirname(__FILE__, 3) . '/includes/database.php'); 
	require_once(dirname(__FILE__, 3) . '/includes/functions.php'); 

	isloggedin();

	$title = (!empty($title) ? $title : '');
	$description = (!empty($description) ? $description : '');
	$keywords = (!empty($keywords) ? $keywords : '');
	$author = (!empty($author) ? $author : '');
?>

<!DOCTYPE html>

<html lang="en">
	<head>
		<base href="<?php echo BASE_DIR; ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta charset="UTF-8">
		
		<?php adminmeta($title, $description, $keywords, $author); ?>
		
		<link rel="preconnect" href="https://fonts.gstatic.com">
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.3/css/all.css" integrity="sha384-SZXxX4whJ79/gErwcOYf+zWLeJdY/qpuqC4cAa9rOGUstPomtqpuNWT9wdPEn2fk" crossorigin="anonymous">
		<link rel="stylesheet" href="css/adminStyle.min.css">
		<link rel="stylesheet" href="css/jquery.fancybox.min.css">
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
		<script src="js/bootstrap.bundle.min.js"></script>
		<script src="js/docRoot.min.js"></script>
		<script src="js/tinymce/tinymce.min.js"></script>
		<script src="js/jquery.fancybox.min.js"></script>
	</head>
	
	<body>
		<div class="wrapper">
			<div class="notifications"></div>

			<header id="pageHeader">
				<div class="container-fluid">
					<div class="row bg-secondary text-light">						
						<div class="col-md-auto px-0">
							<div class="logo bg-primary py-2 px-3">
								<img src="images/clover-cms-logo.png" alt="Clover CMS Logo" class="img-fluid">
							</div>
                        </div>
                        
                        <div class="col-sm-auto d-flex align-items-center">
							<h2 class="h1 mb-0 py-2 py-sm-0">Clover CMS<?php echo (!empty($title) ? '<small> &bull; ' . $title . '</small>': ''); ?></h2>
                        </div>
                
                        <div class="col-sm-auto d-flex align-items-stretch px-0 ms-auto me-0">
							<a href="admin/includes/logout" class="logout text-white btn-dark p-3 ms-auto h-100"><span class="fa fa-sign-out-alt me-3"></span>Logout</a>
						</div>
					</div>
				</div>
			</header>
			
			<div class="main <?php echo str_replace(' ', '-', strtolower($title)); ?>">
				<div class="sidebar">
					<?php include_once(dirname(__FILE__) . '/sidebar.php'); ?>
				</div>

				<div class="content container-fluid">
					<div class="contentInner row h-100">
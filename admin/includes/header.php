<?php 
	require_once(dirname(__FILE__, 3) . '/includes/database.php'); 
	require_once(dirname(__FILE__, 3) . '/includes/functions.php'); 

	isloggedin();
?>

<!DOCTYPE html>

<html lang="en">
	<head>
		<base href="<?php echo BASE_DIR; ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta charset="UTF-8">
		
		<?php echo adminmeta($title, $description, $keywords, $author); ?>
		
		<link rel="preconnect" href="https://fonts.gstatic.com">
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.3/css/all.css" integrity="sha384-SZXxX4whJ79/gErwcOYf+zWLeJdY/qpuqC4cAa9rOGUstPomtqpuNWT9wdPEn2fk" crossorigin="anonymous">
		<link rel="stylesheet" href="css/style.min.css">
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
		<script src="bootstrap-5.0.1/bootstrap.min.js"></script>
		<script src="js/tinymce/tinymce.min.js"></script>
	</head>
	
	<body>
		<div class="wrapper">
			<header id="pageHeader">
				<div class="container-fluid">
					<div class="row">						
						<div class="col bg-secondary text-light d-flex align-items-center justify-content-start px-0">
							<div class="logo bg-primary me-2 p-2">
								<img src="images/clover-cms-logo.png" alt="Clover CMS Logo" class="img-fluid">
							</div>
							
							<h2 class="h1 mb-0">Clover CMS</h2>
							
							<a href="admin/includes/logout" class="logout text-white btn-dark p-3 ms-auto h-100"><span class="fa fa-sign-out-alt me-3"></span>Logout</a>
						</div>
					</div>
				</div>
			</header>
			
			<div class="main">
				<div class="sidebar">
					<?php include_once(dirname(__FILE__) . '/sidebar.php'); ?>
				</div>

				<div class="content container-fluid">
					<div class="contentInner">
						<div class="row">
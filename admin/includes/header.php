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
		<link rel="stylesheet" href="css/style.min.css">
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
		<script src="bootstrap-5.0.1/bootstrap.min.js"></script>
	</head>
	
	<body>
		<div class="wrapper">
			<header id="pageHeader">
				<div class="container-fluid">
					<div class="row">						
						<div class="col bg-secondary text-light d-flex align-items-center justify-content-start">
							<div class="logo bg-primary ms-n3 me-2 p-2">
								<img src="images/clover-cms-logo.png" alt="Clover CMS Logo" class="img-fluid">
							</div>
							
							<h2 class="h1 mb-0">Clover CMS</h2>
						</div>
					</div>
				</div>
			</header>
			
			<div class="main">
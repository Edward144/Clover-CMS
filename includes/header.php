<?php 
	require_once(dirname(__FILE__) . '/database.php'); 
	require_once(dirname(__FILE__) . '/functions.php');
?>

<!DOCTYPE html>

<html lang="en">
	<head>
		<base href="<?php echo BASE_DIR; ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta charset="UTF-8">
		
		<?php echo metadata($title, $description, $keywords, $author); ?>
		
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
			<header id="pageHeader">
				<div class="container-fluid">
					<div class="row">						
						<div class="col bg-secondary">
							header
						</div>
					</div>
				</div>
			</header>
			
			<div class="main">
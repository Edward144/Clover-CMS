<?php 
	require_once(dirname(__FILE__) . '/includes/database.php'); 
	
	$_GET['url'] = (empty($_GET['url']) ? '' : $_GET['url']);

	$notFound = true;
	$templatePath = dirname(__FILE__) . '/';
	$template = 'page.php';
		
	$checkUrl = $mysqli->prepare("SELECT template FROM `posts` WHERE url = ? LIMIT 1");
	$checkUrl->bind_param('s', $_GET['url']);
	$checkUrl->execute();
	$checkUrlResult = $checkUrl->get_result();

	if($checkUrlResult->num_rows > 0) {
		$page = $checkUrlResult->fetch_assoc();
		$notFound = false;
		
		if(!empty($page['template'])) {
			if(file_exists(dirname(__FILE__) . '/includes/templates/' . $page['template' . '.php'])) {
				$templatePath .= 'includes/templates/';
				$template = $page['template'] . '.php';
			}
		}
	}

	if($notFound == true) {
		http_response_code(404);
	}
	else {
		require_once($templatePath . $template);
	}
?>
<?php 
	require_once(dirname(__FILE__) . '/includes/database.php'); 
	
    $state = (!empty($_SESSION['adminid']) ? 1 : 2);
    $checkHomepage = $mysqli->query("SELECT value FROM `settings` WHERE name = 'homepage' LIMIT 1");

    if($checkHomepage->num_rows > 0) {
        $homepage = $checkHomepage->fetch_array()[0];
    }
    else {
        $getFirstPage = $mysqli->prepare("SELECT id FROM `posts` WHERE post_type_id = 1 AND state >= ? LIMIT 1");
        $getFirstPage->bind_param('i', $state);
        $getFirstPage->execute();
        $getFirstResult = $getFirstPage->get_result();
        
        if($getFirstResult->num_rows > 0) {
            $homepage = $getFirstResult->fetch_assoc()[0];
        }
    }

    $homepage = (empty($homepage) ? 0 : $homepage);

    if($homepage > 0) {
        $homeUrl = $mysqli->prepare("SELECT url FROM `posts` WHERE id = ? AND state >= ?");
        $homeUrl->bind_param('ii', $homepage, $state);
        $homeUrl->execute();
        $homeResult = $homeUrl->get_result();
        
        if($homeResult->num_rows > 0) {
            $homeUrl = $homeResult->fetch_array()[0];
            
            if(empty($_GET['url'])) {
                $_GET['url'] = $homeUrl;
            }
            elseif($_GET['url'] == $homeUrl) {
                http_response_code(301);
                header('Location: ' . ROOT_DIR);
            }
        }
    }

	$_GET['url'] = (empty($_GET['url']) ? '' : $_GET['url']);
    
	$notFound = true;
	$templatePath = dirname(__FILE__) . '/';
	$template = 'page.php';
		
	$checkUrl = $mysqli->prepare("SELECT id, template FROM `posts` WHERE url = ? AND state >= ? LIMIT 1");
	$checkUrl->bind_param('si', $_GET['url'], $state);
	$checkUrl->execute();
	$checkUrlResult = $checkUrl->get_result();

	if($checkUrlResult->num_rows > 0) {
		$page = $checkUrlResult->fetch_assoc();
		$notFound = false;
		$contentId = $page['id'];
        
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
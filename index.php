<?php 
    /* This file is used to redirect any requested url to the correct template file or if a template file does not exist then to page.php.
     * If the homepage has been set and the page exists then that page will be redirected to / which will display that page's content.
     * If the homepage doesn't exist or isn't set then / will display the content of the page with the lowest ID.
     */
     
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
    
    //Remove first part of url if post type pages is included
    $hasPostType = false;
    $postTypes = $mysqli->query("SELECT name FROM `post_types`");
    
    if($postTypes->num_rows > 0) {
        $ptA = [];
        
        while($pt = $postTypes->fetch_array()) {
            array_push($ptA, $pt[0]);
        }
        
        if(in_array(explode('/', $_GET['url'])[0], $ptA) && strpos($_GET['url'], explode('/', $_GET['url'])[0] . '/') === 0) {
            $hasPostType = true;
            
            $url = explode('/', $_GET['url']);
            array_shift($url);
            $url = implode('/', $url);
        }
    }

    if($hasPostType == false) {
        $url = $_GET['url'];
    }

	$notFound = true;
	$templatePath = dirname(__FILE__) . '/';
	$template = 'page.php';
		
	$checkUrl = $mysqli->prepare(
        "SELECT posts.id, posts.template, posts.post_type_id, posts.url, post_types.name AS post_type FROM `posts` AS posts
            LEFT OUTER JOIN `post_types` AS post_types ON post_types.id = posts.post_type_id
        WHERE posts.url = ? AND posts.state >= ? LIMIT 1");
	$checkUrl->bind_param('si', $url, $state);
	$checkUrl->execute();
	$checkUrlResult = $checkUrl->get_result();

	if($checkUrlResult->num_rows > 0) {
		$page = $checkUrlResult->fetch_assoc();
        
        if($page['post_type_id'] > 1 && strpos($_GET['url'], $page['post_type'] . '/') !== 0) {
            //Redirect post types to include the prefix
            http_response_code(301);
            header('Location: ' . ROOT_DIR . $page['post_type'] . '/' . $page['url']);
            exit();
        }
        elseif($page['post_type_id'] == 1 && strpos($_GET['url'], $page['post_type'] . '/') === 0) {
            //Redirect pages to remove the prefix
            http_response_code(301);
            header('Location: ' . ROOT_DIR . $page['url']);
            exit();
        }
        
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
        include_once(dirname(__FILE__) . '/404.php');
	}
	else {
		require_once($templatePath . $template);
	}
?>
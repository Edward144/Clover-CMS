<?php
	
	require_once(dirname(__DIR__) . '/includes/settings.php');
	
	//Check if admin user is logged in
	function isloggedin() {
		global $mysqli;
		$valid = true;
		
		if(!empty($_SESSION['adminid'])) {			
			$checkId = $mysqli->query("SELECT COUNT(*) FROM `users` WHERE id = {$_SESSION['adminid']}");
			
			if($checkId->fetch_array()[0] <= 0) {
				$valid = false;
			}
		}
		else {
			$valid = false;
		}
		
		if($valid == false) {
			http_response_code(403);
			header('Location: https://' . $_SERVER['SERVER_NAME'] . ROOT_DIR . 'admin-login');
			exit();
		}
	}

	//Create metadata
	function adminmeta($title = '', $description = '', $keywords = '', $author = '') {
		$metadata = '';
		
		if(!empty($title) && is_string($title)) {
			$metadata .= '<title>' . $title . ' | Clover CMS</title>';
		}
		else {
			$metadata .= '<title>Clover CMS</title>';
		}
		
		if(!empty($description) && is_string($description)) {
			$metadata .= '<meta name="description" content="' . $description . '">';
		}
		
		if(!empty($keywords) && is_string($keywords)) {
			$metadata .= '<meta name="keywords" content="' . $keywords . '">';
		}
		
		if(!empty($author) && is_string($author)) {
			$metadata .= '<meta name="author" content="' . $author . '">';
		}
		
		echo $metadata;
	}

	function metadata($title = '', $description = '', $keywords = '', $author = '') {
		$metadata = '';
		
        if(!empty($title) && is_string($title)) {
			$metadata .= '<title>' . $title . '</title>';
		}
		
		if(!empty($description) && is_string($description)) {
			$metadata .= '<meta name="description" content="' . $description . '">';
		}
		
		if(!empty($keywords) && is_string($keywords)) {
			$metadata .= '<meta name="keywords" content="' . $keywords . '">';
		}
		
		if(!empty($author) && is_string($author)) {
			$metadata .= '<meta name="author" content="' . $author . '">';
		}
        
		echo $metadata;
	}

    //Google analytics tracking code
    function googleanalytics() {
        global $mysqli;
        
        $checkAnalytics = $mysqli->query("SELECT value FROM `settings` WHERE name = 'google_analytics' LIMIT 1");
        
        if($checkAnalytics->num_rows > 0) {
            if(preg_match('/^([A-Z]{2}\-[0-9]{8}\-[0-9]{1})$/', $checkAnalytics->fetch_array()[0], $matches)) {
                $gaTag = $matches[0];
                
                echo 
                    '<!-- Global site tag (gtag.js) - Google Analytics -->
                    <script async src="https://www.googletagmanager.com/gtag/js?id=' . $gaTag . '"></script>
                    <script>
                      window.dataLayer = window.dataLayer || [];
                      function gtag(){dataLayer.push(arguments);}
                      gtag("js", new Date());

                      gtag("config", "' . $gaTag . '");
                    </script>';
            }
        }
    }

	//Generate random alphanumeric string, for passwords
	function randomstring($length = 12, $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
		$random = '';

		while(strlen($random) != $length || !preg_match('$(^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])).*+$', $random)) {
			$random .= substr($characters, rand(0, strlen($characters)), 1);
		}

		return $random;
	}

	//Custom MySQLI duplicate error message
	function duplicateerror($value) {
		preg_match_all('/\'(.*?)\'/', $value, $matches);
		return ucwords(str_replace('_', ' ', $matches[1][1])) . ' "' . $matches[1][0] . '" already exists';	
	}

	//Validate that two passwords match
	function validatepassword($pass = '', $passConf = '') {
		if(empty($pass) || empty($passConf)) {
			return ['status' => 'danger', 'message' => 'Failed to verify supplied passwords'];
		}
		elseif($pass != $passConf) {
			return ['status' => 'danger', 'message' => 'Passwords do not match'];
		}
		else {
			return password_hash($pass, PASSWORD_BCRYPT);
		}
    }

	//PHP mail function with HTML template
	function systememail($to, $subject, $content, $additionalHeaders = '', $from = '') {
		$from = (empty($from) ? 'noreply@' . $_SERVER['SERVER_NAME'] : $from);
		
		$headers  = 'From: ' . $from . "\r\n";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type:text/html;charset=UTF-8' . "\r\n";
		
		$messageHeader = 
			'<html>				
				<body style="font-family: sans-serif; background: #f3f3f3; padding: 5rem 2rem; max-width: 1000px;">
					<div class="padding: 0 1rem; margin: 5rem auto;">
						<div style="background: #009688; padding: 1rem; display: flex; align-items: center; justify-content: center;">
							<img src="https://' . $_SERVER['SERVER_NAME'] . ROOT_DIR . 'images/clover-cms-logo.png" alt="Clover CMS Logo" style="display: block; width: 100%; max-width: 64px; margin-right: 1rem;">
							<span style="font-size: 32px; color: #fff;">Clover CMS</span>
						</div>

						<div style="background: #fff; padding: 1rem;">';
		
		$messageFooter =
						'</div>
					</div>
				</body>
			</html>';
		
		$message = $messageHeader . $content . $messageFooter;
		
		if(mail($to, $subject, $message, $headers, '-f' . $from)) {
			return true;
		}
		else {
			return false;
		}
	}

	//Find and execute shortcode functions within page content
	function parsecontent($content) {
		$shortcodes = [];

		//Search for square brackets
		preg_match_all('$\[(.*)\]$', $content, $matches);

		foreach($matches[0] as $match) {
			//Get the shortcode function name
			preg_match('$^\[[a-zA-Z0-9\-\_]+ $', $match, $names);

			if(!empty($names)) {
				$shortcodeName = rtrim(ltrim($names[0], '['), ' ');
				$parameters = [];

				//Get the supplied parameters
				preg_match_all('$ [a-zA-Z0-9\-\_]+\=\"[^\"]+$', $match, $params);

				foreach($params[0] as $param) {
					preg_match('$[^\=\"]+$', $param, $pName);
					$pName = ltrim($pName[0], ' ');

					preg_match('$^ [a-zA-Z0-9\-\_]+\=\"(.*)$', $param, $pValue);
					$pValue = $pValue[1];

					$parameters[$pName] = $pValue; 
				}

				//Check if function exists and pass the parameters
				if(function_exists($shortcodeName)) {
					//Replace the shortcode with the function output
					$doShortcode = $shortcodeName($parameters);

					//Check if shortcode has surrounding p tags and remove them (TinyMCE likes to add these)
					preg_match('$<p[^>]*>' . str_replace(']', '\]', str_replace('[', '\[', addslashes($match))) . '<\\/p[^>]*>$', $content, $ptags);
					$match = (!empty($ptags[0]) ? $ptags[0] : $match);
					$content = str_replace($match, $doShortcode, $content);
				}
			}
		}

		echo $content;
	}

    //List posts with pagination
    function listposts($postType = 2, $items = 12, $displayDate = true, $displayAuthor = true) {
        global $mysqli;
        
        if(!is_numeric($postType) || $postType <= 0) {
            $postType = 2;
        }
        
        if(!is_numeric($items) || $items <= 0) {
            $items = 12;
        }
        
        if(isset($_SESSION['adminid'])) {
            $visiblity = 1;
        }
        else {
            $visiblity = 2;
        }
        
        $newspage = $mysqli->query("SELECT value FROM `settings` WHERE name = 'newspage' LIMIT 1");
        
        if($newspage->num_rows > 0) {
            $newspage = $newspage->fetch_array()[0];
        }
        else {
            $newspage = 0;
        }
        
        $countPosts = $mysqli->prepare("SELECT COUNT(*) FROM `posts` WHERE post_type_id = ? AND id <> ? AND state >= ?");
        $countPosts->bind_param('iii', $postType, $newspage, $visiblity);
        $countPosts->execute();
        $postCount = $countPosts->get_result()->fetch_array()[0];
        
        $pagination = new pagination($postCount);
        $pagination->itemsPerPage = $items;
        $pagination->load();
        
        $checkPosts = $mysqli->prepare(
            "SELECT posts.*, post_types.name AS post_type FROM `posts` AS posts 
                LEFT OUTER JOIN `post_types` AS post_types ON post_types.id = posts.post_type_id
            WHERE posts.post_type_id = ? AND posts.id <> ? AND posts.state >= ?
            ORDER BY posts.date_created DESC LIMIT {$pagination->itemsPerPage} OFFSET {$pagination->offset}"
        );
        $checkPosts->bind_param('iii', $postType, $newspage, $visiblity);
        $checkPosts->execute();
        $posts = $checkPosts->get_result();
        
        if($posts->num_rows > 0) {
            $output = 
                '<div class="postsList row" id="postsList' . $postType . '">';
            
            while($post = $posts->fetch_assoc()) {
                if($displayDate == true || $displayAuthor == true) {
                    $details = 
                        '<h5 class="postDetails">' .
                            ($displayDate == true && !empty($post['date_created']) ? '<span class="postDate"><span class="fa fa-calendar"></span> ' . date('d/m/Y', strtotime($post['date_created'])) . '</span>' : '') .   
                            ($displayAuthor == true && !empty($post['author']) ? '<span class="postAuthor"><span class="fa fa-user"></span> ' . $post['author'] . '</span>' : '') .   
                        '</h5>';
                }
                else {
                    $details = '';
                }
                
                $output .=
                    '<div class="col-sm-6 col-lg-3 mb-3">
                        <div class="postItem" id="postItem' . $post['id'] . '">
                            <div class="postImage">
                                ' . (!empty($post['featured_image']) ? '<img src="' . $post['featured_image'] .'" alt="' . $post['name'] . ' Featured Image">' : '') . '
                            </div>
                            
                            <div class="postBody">
                                <h3 class="postTitle">' . $post['name'] . '</h3>' . 
                                $details .
                    
                                (!empty($post['excerpt']) ? '<p class="postExcerpt">' . substr($post['excerpt'], 0, 100) . (strlen($post['excerpt']) > 100 ? '...' : '') . '</p>' : '') .
                    
                                (!empty($post['content']) ? '<div class="postLink"><a href="' . $post['post_type'] . '/' . $post['url'] . '" class="btn btn-primary text-white">Read More</a></div>' : '') .
                            '</div>
                        </div>
                    </div>';
            }
            
            $output .=
                '</div>' . $pagination->display();
        }
        else {
            $output = '<div class="alert alert-info">No posts could be found</div>';
        }
        
        return $output;
    }

    //Include class
    $classes = scandir(dirname(__FILE__) . '/classes');

    foreach($classes as $class) {
        if(strpos($class, '.class') !== false) {
            include_once(dirname(__FILE__) . '/classes/' . $class);
        }
    }
    
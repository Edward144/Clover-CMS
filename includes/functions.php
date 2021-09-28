<?php
	
	include_once(dirname(__FILE__) . '/settings.php');
	include_once(dirname(__FILE__) . '/shortcodes.php');

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

    //Check if user has access to page
    function checkaccess($pagename, $menu = false) {
        global $mysqli;
        $allowed = false;
        
        $getRole = $mysqli->prepare(
            "SELECT users.role, roles.access FROM `users` AS users
                LEFT OUTER JOIN `roles` AS roles ON roles.id = users.role
            WHERE users.id = ?"
        );
        $getRole->bind_param('i', $_SESSION['adminid']);
        $getRole->execute();
        $roleResult = $getRole->get_result();
        
        if($roleResult->num_rows > 0) {
            $user = $roleResult->fetch_assoc();
            $access = json_decode($user['access'], true);
            $access = ($access == null ? [] : $access);
            
            if($user['role'] == 0 || in_array($pagename, $access)) {
                $allowed = true;
            }
        }
        
        if($allowed == false && $menu == false) {
            http_response_code(403);
            require_once(dirname(__DIR__) . '/admin/includes/header.php');

            echo 
                '<div>
                    <div class="alert alert-danger mt-3">
                        <h3>Forbidden</h3>
                        <h5>You do not have access to view this page. Please contact your administrator if you feel this is incorrect.<h5>
                    </div>
                </div>';
            
            require_once(dirname(__DIR__) . '/admin/includes/footer.php');
            exit();
        }
        //Return true or false for menu items so that they can be hidden
        elseif($allowed == false && $menu == true) {
            return false;
        }
        elseif($allowed == true && $menu == true) {
            return true;
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

    //Carousel
    function carousel($postId, $builder = false, $json = '', $interval = 5000, $wrap = true, $controls = true) {        
        global $mysqli;

        $slides = '';
        $si = 0;

        $getSlides = $mysqli->prepare("SELECT carousel FROM `posts` WHERE id = ?");
        $getSlides->bind_param('i', $postId);
        $getSlides->execute();
        $slidesResult = $getSlides->get_result();

        if($slidesResult->num_rows > 0 || !is_numeric($postId)) {
            if(!empty($json)) {
                $slideData = $json;
            }
            else {
                $slideData = $slidesResult->fetch_array()[0];
            }
            
            $slideJson = json_decode($slideData, true);

            if(!empty($slideJson)) {
                foreach($slideJson as $slide) {
                    if($builder == true) {
                        $title = '<input type="text" name="carouselTitle" class="carouselTitle display-3" value="' . $slide['title'] . '" placeholder="Slide title"' . (!empty($slide['titlecolor']) ? 'style="color: ' . $slide['titlecolor'] . ';"' : '') . '>';
                        $tagline = '<input type="text" name="carouselTagline" class="carouselTitle display-6" value="' . $slide['tagline'] . '" placeholder="Slide tagline"' . (!empty($slide['taglinecolor']) ? 'style="color: ' . $slide['taglinecolor'] . ';"' : '') . '>';
                    }
                    else {
                        $title = (!empty($slide['title']) ? '<h3 class="carouselTitle display-3"' . (!empty($slide['titlecolor']) ? 'style="color: ' . $slide['titlecolor'] . ';"' : '') . '>' . $slide['title'] . '</h3>' : '');
                        $tagline = (!empty($slide['tagline']) ? '<h6 class="carouselTagline display-6"' . (!empty($slide['taglinecolor']) ? 'style="color: ' . $slide['taglinecolor'] . ';"' : '') . '>' . $slide['tagline'] . '</h6>' : '');
                    }


                    $slides .=
                        '<div class="carousel-item' . ($si == 0 ? ' active' : '') . '">
                            <div class="carousel-item-inner">' .
                                (!empty($slide['image']) ? '<img src="' . $slide['image'] . '" class="background" style="' . (!empty($slide['imageposition']) ? 'object-position: ' . $slide['imageposition'] : '') . '">' : '') . 
                                $title . 
                                $tagline .
                            '</div>
                        </div>';

                    $si++;
                }
            }
        }

        if($builder == true) {
            $si++;
        }

        $controlsOut = 
            '<button class="carousel-control-prev" type="button" data-bs-target="#carousel' . $postId . '" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carousel' . $postId . '" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>';


        if($si > 1) {
            $controlsOut .=
                '<div class="carousel-indicators">';

            for($i = 0; $i < $si; $i++) {
                $controlsOut .=
                    '<button type="button" data-bs-target="#carousel' . $postId . '" data-bs-slide-to="' . $i . '"' . ($i == 0 ? ' class="active" aria-current="true"' : '') . 'aria-label="slide-' . ($i+1) . '"></button>';
            }

            $controlsOut .=
                '</div>';
        }

        if(!empty($slides) || $builder == true) {
            if($builder == true) {
                $controls = true;
                $interval = false;
            }
            elseif($si == 0) {
                $controls = false;
            }

            $output =
                '<div id="carousel' . $postId . '" class="' . ($builder == true ? 'builder ': 'fullWidth mt-n3 mb-3 ') . 'carousel slide" data-bs-ride="carousel" data-bs-interval="' . ($interval === false ? 'false' : $interval) . '" data-bs-wrap="' . ($wrap == true ? 'true' : 'false') . '">
                    <div class="carousel-inner">' .
                        $slides . 
                        ($builder == true ? 
                            '<div class="carousel-item' . (empty($slides) ? ' active' : '') . ' additionalSlide bg-dark">
                                <div class="carousel-item-inner">
                                    <button type="button" name="addCarousel" class="btn btn-secondary">+ Slide</button>
                                </div>
                            </div>'
                        : '') .
                    '</div>' .
                    ($controls == true ? $controlsOut : '').
                '</div>';

            if($builder == true) {
                $output .=
                    '<input type="hidden" name="carousel"' . (!empty($slideJson) ? ' value="' . htmlspecialchars(json_encode($slideJson)) . '"' : '') . '>';
            }
        }

        return $output;
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
				//preg_match_all('$ [a-zA-Z0-9\-\_]+\=\"[^\"]+$', $match, $params);
				preg_match_all('$ [a-zA-Z0-9\-\_]+\="[^"\\\\]*(?:\\\\.[^"\\\\]*)*"$s', $match, $params);

				foreach($params[0] as $param) {
					preg_match('$[^\=\"]+$', $param, $pName);
					$pName = ltrim($pName[0], ' ');

					preg_match('$^ [a-zA-Z0-9\-\_]+\=\"(.*)\"$', $param, $pValue);
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
    
<?php

	//Generate random alphanumeric string, for passwords
	function randomstring($length = 12, $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
		$random = '';

		while(strlen($random) != $length || !preg_match('$(^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])).*+$', $random)) {
			$random .= substr($characters, rand(0, strlen($characters)), 1);
		}

		return $random;
	}

	//PHP mail function with HTML template
	function systememail() {
		
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

		return $content;
	}
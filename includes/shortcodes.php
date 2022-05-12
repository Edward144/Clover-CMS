<?php

    /*
     * Create shortcodes here
     * Shortcodes follow should consist of a function name surrounded by two square brackets,
     * parameters should consist of a key and a value, there may be some issues caused by using
     * double quotes within a value as the value must be surrounded by double quotes istelf.
     * If you do need to accept double quotes within a parameter, such as for JSON, then make sure
     * to escape all double quotes with a backslash.
     *
     * Example: [function_name param1="hello world" param2="12345" param3="{ \"json" : \"value\" }"]
     *
     */

    //Render form
    function form($params) {
        global $mysqli;
        
        if(!empty($params['id'])) {
            $output = '';
            $searchForm = $mysqli->prepare("SELECT * FROM `forms` WHERE id = ?");
            $searchForm->bind_param('i', $params['id']);
            $searchForm->execute();
            $formResult = $searchForm->get_result();
            
            if($formResult->num_rows > 0) {
                $form = $formResult->fetch_assoc();
                $structure = json_decode($form['structure'], true);
                
                if(!empty($structure)) {
                    $output .=
                        '<form id="' . $structure['formid'] . '" class="userForm form' . $form['id'] . '" action="' . $structure['action'] . '" method="' . $structure['method'] . '">';
                    
                    $groupCount = count($structure['groups']);
                    $gi = 0;
                    
                    foreach($structure['groups'] as $group) {
                        $output .=
                            '<div id="group' . $group['groupid'] . '" class="formGroup">';
                        
                        //Show group title if not blank and one group or if multiple groups blank or not
                        if(!empty($group['name']) || $groupCount > 1) {
                            $output .=    
                                '<div class="groupHeader">
                                    <h4 class="groupTitle">' . $group['name'] . '</h4>' .
                                    ($groupCount > 1 ?
                                    '<button type="button" class="groupToggle btn btn-muted" data-bs-toggle="collapse" data-bs-target="#' . $group['groupid'] . '" role="button" aria-expanded="' . ($gi == 0 ? 'true' : 'false') . '"><span class="fa fa-chevron-left"></span></button>' : '') .
                                '</div>';
                        }
                        
                        //Show group body
                        $output .=
                                '<div id="' . $group['groupid'] . '" class="groupBody collapse' . ($groupCount == 1 || $gi == 0 ? ' show' : '') . '">';
                        
                        //Loop inputs
                        foreach($group['inputs'] as $input) {
                            $output .=
                                '<div class="form-group mb-3" id="input' . $input['inputid'] . '">' .
                                    (!empty($input['label']) && !in_array($input['type'], ['button', 'submit', 'hidden', 'checkbox']) ? '<label' . ($input['required'] == true ? ' class="required"': '') . '>' . $input['label'] . '</label>' : '');
                            
                            if(!in_array($input['type'], ['button', 'submit', 'hidden'])) {
                                $output .=
                                    '<input type="hidden" name="label' . $input['inputid'] . '" value="' . $input['label'] . '">';
                            }
                            
                            switch($input['type']) {
                                case 'general': 
                                    $output .= $input['value'];
                                    break;
                                case 'textarea': 
                                    $output .=
                                        '<textarea class="form-control" name="' . $input['inputid'] . '" placeholder="' . $input['placeholder'] . '"' . ($input['required'] == true ? ' required' : '') . '>' . $input['value'] . '</textarea>';
                                    break;
                                case 'number': 
                                    $output .=
                                        '<input type="' . $input['type'] . '" class="form-control" name="' . $input['inputid'] . '" placeholder="' . $input['placeholder'] . '" min="' . $input['min'] . '" max="' . $input['max'] . '"  step="' . $input['step'] . '" value="' . $input['value'] . '"' . ($input['required'] == true ? ' required' : '') . '>';
                                    break;
                                case 'select':
                                    $output .=
                                        '<select class="form-control" name="' . $input['inputid'] . '"' . ($input['required'] == true ? ' required': '') . ($input['multiple'] == true ? ' multiple' : '') . '>';
                                    
                                    foreach($input['options'] as $option) {
                                        $output .=
                                            '<option value="' . $option['value'] . '">' . $option['value'] . '</option>';
                                    }
                                    
                                    $output .=
                                        '</select>';
                                    break;
                                case 'radio': 
                                    $ri = 0;
                                    
                                    foreach($input['options'] as $option) {
                                        $output .= 
                                            '<div class="form-check">
                                                <input type="radio" class="form-check-input" id="' . $input['inputid'] . $ri . '" name="' . ($input['inputid']) . '"' . ($input['required'] == true ? ' required' : '') . ($option['default'] === true ? ' checked' : '') . '>
                                                <label for="' . $input['inputid'] . $ri . '">' . $option['value'] . '</label>
                                            </div>';
                                        $ri++;
                                    }
                                    break;
                                case 'checkbox': 
                                    $output .=
                                        '<div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="' . $input['inputid'] . '" name="' . $input['inputid'] . '"' . ($input['required'] == true ? ' class="required"' : '') . ($input['checked'] == true ? ' checked' : '') . '>
                                            <label for="' . $input['inputid'] . '"' . ($input['required'] == true ? ' class="required"' : '') . '>' . $input['label'] . '</label>
                                        </div>';
                                    break;
                                case 'file': 
                                    $output .=
                                        '<input type="file" class="form-control" name="' . $input['inputid'] . '"' . ($input['multiple'] == true ? ' multiple' : '') . '>';
                                    break;
                                case 'hidden':
                                    $output .=
                                        '<input type="hidden" name="' . $input['inputid'] .  '" value="' . $input['value'] . '">';
                                    break;
                                case 'button': 
                                    $output .=
                                        '<input type="button" class="btn ' . (!empty($input['theme']) ? $input['theme'] : 'btn-secondary') . '" name="' . $input['inputid'] . '" value="' . (!empty($input['label']) ? $input['label'] : 'Submit') . '">';
                                    break;
                                case 'submit': 
                                    $output .=
                                        '<input type="submit" class="btn ' . (!empty($input['theme']) ? $input['theme'] : 'btn-primary') . '" name="' . $input['inputid'] . '" value="' . (!empty($input['label']) ? $input['label'] : 'Submit') . '">';
                                    break;
                                default: 
                                    switch($input['type']) {
                                        case 'date':
                                            $placeholder = 'YYYY-MM-DD';
                                            break;
                                        case 'time':
                                            $placeholder = 'HH:MM';
                                            break;
                                        case 'datetime-local':
                                            $placeholder = 'YYYY-MM-DD HH:MM';
                                            break;
                                        default: 
                                            $placeholder = $input['placeholder'];
                                            break;
                                    }
                                    
                                    $output .=
                                        '<input type="' . $input['type'] . '" class="form-control" name="' . $input['inputid'] . '" placeholder="' . $placeholder . '" value="' . $input['value'] . '"' . ($input['required'] == true ? ' required' : '') . '>';
                                    break;
                            }
                            
                            $output .=
                                '</div>';
                        }
                        
                        $output .=
                                '</div>
                            </div>';
                        
                        $gi++;
                    }
                    
                    //Add Recaptcha validation
		$captcha = $mysqli->query("SELECT name, value FROM `settings` WHERE name = 'recaptcha_sitekey_v3' OR name = 'recaptcha_secretkey_v3'");

		if($captcha->num_rows > 0) {
		    $cptch = [];

		    while($row = $captcha->fetch_assoc()) {
			$cptch[$row['name']] = $row['value'];
		    }

		    $sitekey = $cptch['recaptcha_sitekey_v3'];
		    $secretkey = $cptch['recaptcha_secretkey_v3'];

		    if(!empty($sitekey) && !empty($secretkey)) {
			$output .=
			    '<input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
			    <input type="hidden" name="action" value="validate_captcha">
			    <input type="hidden" name="formid" value="' . $form['id'] . '">
							    <input type="hidden" name="returnurl" value="' . $_SERVER['REQUEST_URI'] . '">

			    <script src="https://www.google.com/recaptcha/api.js?render=' . $sitekey . '"></script>

			    <script>
				$("form#' . $structure['formid'] . '").submit(function() {
				    event.preventDefault();

				    grecaptcha.execute("' . $sitekey . '", {
					action: \'validate_captcha\'
				    }).then(function(token) {
					$("#g-recaptcha-response").val(token);
					$("form#' . $structure['formid'] . '").unbind("submit").submit();
				    });
				});
			    </script>';
		    }
		}
                    
                    if(isset($_SESSION['message']) && isset($_SESSION['status'])) {
                        $output .=
                            '<div class="alert alert-' . $_SESSION['status'] . '">'
                                . $_SESSION['message'] . 
                            '</div>';
                        
                        unset($_SESSION['message']);
                        unset($_SESSION['status']);
                    }
                    
                    $output .=
                        '</form>';
                }
            }            
            
            return $output;
        }
    }

?>

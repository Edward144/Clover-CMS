<?php

    if(isset($_POST['formbuilder_method'])) {
        require_once(dirname(__DIR__) . '/database.php');
        
        if($_POST['formbuilder_method'] == 'addGroup') {
            $group = new formbuilder($_POST['id']);
            $g = $group->pullgroups([
                "groups" => [json_decode($_POST['data'], true)]
            ]);
            
            echo json_encode($g);
        }
        elseif($_POST['formbuilder_method'] == 'addInput') {
            $data = json_decode($_POST['data'], true);
            
            if(!empty($data) && method_exists('formbuilder', $data['type'])) {
                $data['type'] = explode('input_', $data['type'])[1];
                
                $input = new formbuilder($_POST['id']);
                $i = $input->pullinputs([$data]);
                
                echo json_encode($i);
            }
            else {
                echo json_encode($method . ' does not exist');
            }
        }
        elseif($_POST['formbuilder_method'] == 'addOptionRadio') {
            $checked = ($_POST['isDefault'] === 'true' ? true : false);
            $option = new formbuilder($_POST['id']);
            $o = $option->radio_option($_POST['inputId'], '', $checked);
            
            echo json_encode($o);
        }
        elseif($_POST['formbuilder_method'] == 'addOptionSelect') {
            $option = new formbuilder($_POST['id']);
            $o = $option->select_option('');
            
            echo json_encode($o);
        }
        
        exit();
    }

    class forminputs {
        protected function _input_default($required = false, $repeatable = false, $additional = null) {
            $requiredOption = '';
            $repeatableOption = '';
            $additionalOptions = '';
            
            if(!empty($additional)) {
                foreach($additional as $label => $value) {
                    $additionalOptions .=
                        '<div class="input-group-text">
                            <input type="checkbox" class="form-check-input mt-0" name="' . $label . '" ' . ($value === 'true' ? 'checked' : '') . '>
                        </div>
                        <span class="input-group-text me-2">' . ucwords($label) . '?</span>';
                }
            }
            
            if($required !== 0) {
                $requiredOption =
                    '<div class="input-group-text">
                        <input type="checkbox" class="form-check-input mt-0" name="required" ' . ($required == true ? 'checked' : '') . '>
                    </div>
                    <span class="input-group-text me-2">Required?</span>';
            }
            
            if($repeatable !== 0 && 1 == 2) { //Don't want to show repeatable option until it is implemented
                $repeatableOption =
                    '<div class="input-group-text">
                        <input type="checkbox" class="form-check-input mt-0" name="repeatable" ' . ($repeatable == true ? 'checked' : '') . '>
                    </div>
                    <span class="input-group-text me-2">Repeatable?</span>';
            }
            
            $output =
                '<div class="input-group mt-2">' . 
                    $requiredOption .
                    $repeatableOption .
                    $additionalOptions .
                '</div>';
            
            return $output;
        }
        
        protected function input_general($data = []) {
            //textarea to add custom text, javascript etc that will appear on the form 
            $output = 
                '<div class="input-group mt-2">
                    <span class="input-group-text align-items-start">Value</span>
                    <textarea class="form-control" name="value" placeholder="Insert your own text, html, css, javascript...">' . (!empty($data['value']) ? $data['value'] : '') . '</textarea>
                </div>';
            
            return $output;
        }
            
        protected function input_text($data = []) {
            $output =
                $this->_input_default($data['required'], $data['repeatable']) . 
                '<div class="input-group mt-2">
                    <span class="input-group-text">Label</span>
                    <input type="text" class="form-control" name="label" value="' . (!empty($data['label']) ? $data['label'] : '') . '">
                    <span class="input-group-text">Placeholder</span>
                    <input type="text" class="form-control" name="placeholder" value="' . (!empty($data['placeholder']) ? $data['placeholder'] : '') . '">
                    <span class="input-group-text">Default Value</span>
                    <input type="text" class="form-control" name="value" value="' . (!empty($data['value']) ? $data['value'] : '') . '">
                </div>';
            
            return $output;
        }
        
        protected function input_textarea($data = []) {
            $output =
                $this->_input_default($data['required'], $data['repeatable']) . 
                '<div class="input-group mt-2">
                    <span class="input-group-text">Label</span>
                    <input type="text" class="form-control" name="label" value="' . (!empty($data['label']) ? $data['label'] : '') . '">
                    <span class="input-group-text">Placeholder</span>
                    <input type="text" class="form-control" name="placeholder" value="' . (!empty($data['placeholder']) ? $data['placeholder'] : '') . '">
                </div>
                
                <div class="input-group mt-2">
                    <span class="input-group-text align-items-start">Default Value</span>
                    <textarea class="form-control" name="value">' . (!empty($data['value']) ? $data['value'] : '') . '</textarea>
                </div>';
            
            return $output;
        }
        
        protected function input_number($data = []) {
            //min, max, step
            $output =
                $this->_input_default($data['required'], $data['repeatable']) . 
                '<div class="input-group mt-2">
                    <span class="input-group-text">Min</span>
                    <input type="number" class="form-control" name="min" value="' . (!empty($data['min']) ? $data['min'] : '') . '">
                    <span class="input-group-text">Max</span>
                    <input type="number" class="form-control" name="max" value="' . (!empty($data['max']) ? $data['max'] : '') . '">
                    <span class="input-group-text">Step</span>
                    <input type="number" class="form-control" name="step" value="' . (!empty($data['step']) ? $data['step'] : '') . '">
                </div>
                
                <div class="input-group mt-2">
                    <span class="input-group-text">Label</span>
                    <input type="text" class="form-control" name="label" value="' . (!empty($data['label']) ? $data['label'] : '') . '">
                    <span class="input-group-text">Placeholder</span>
                    <input type="number" class="form-control" name="placeholder" value="' . (!empty($data['placeholder']) ? $data['placeholder'] : '') . '">
                    <span class="input-group-text">Default Value</span>
                    <input type="number" class="form-control" name="value" value="' . (!empty($data['value']) ? $data['value'] : '') . '">
                </div>';
            
            return $output;
        }
        
        protected function input_email($data = []) {
            $output =
                $this->_input_default($data['required'], $data['repeatable']) . 
                '<div class="input-group mt-2">
                    <span class="input-group-text">Label</span>
                    <input type="text" class="form-control" name="label" value="' . (!empty($data['label']) ? $data['label'] : '') . '">
                    <span class="input-group-text">Placeholder</span>
                    <input type="email" class="form-control" name="placeholder" value="' . (!empty($data['placeholder']) ? $data['placeholder'] : '') . '">
                    <span class="input-group-text">Default Value</span>
                    <input type="email" class="form-control" name="value" value="' . (!empty($data['value']) ? $data['value'] : '') . '">
                </div>';
            
            return $output;
        }
        
        protected function input_password($data = []) {
            $output =
                $this->_input_default($data['required'], $data['repeatable']) . 
                '<div class="input-group mt-2">
                    <span class="input-group-text">Label</span>
                    <input type="text" class="form-control" name="label" value="' . (!empty($data['label']) ? $data['label'] : '') . '">
                    <span class="input-group-text">Default Value</span>
                    <input type="text" class="form-control" name="value" value="' . (!empty($data['value']) ? $data['value'] : '') . '">
                </div>';
            
            return $output;
        }
        
        protected function input_date($data = []) {
            $output =
                $this->_input_default($data['required'], $data['repeatable']) . 
                '<div class="input-group mt-2">
                    <span class="input-group-text">Label</span>
                    <input type="text" class="form-control" name="label" value="' . (!empty($data['label']) ? $data['label'] : '') . '">
                    <span class="input-group-text">Default Value</span>
                    <input type="date" class="form-control" name="value" value="' . (!empty($data['value']) ? $data['value'] : '') . '">
                </div>';
            
            return $output;
        }
        
        protected function input_time($data = []) {
            $output =
                $this->_input_default($data['required'], $data['repeatable']) . 
                '<div class="input-group mt-2">
                    <span class="input-group-text">Label</span>
                    <input type="text" class="form-control" name="label" value="' . (!empty($data['label']) ? $data['label'] : '') . '">
                    <span class="input-group-text">Default Value</span>
                    <input type="time" class="form-control" name="value" value="' . (!empty($data['value']) ? $data['value'] : '') . '">
                </div>';
            
            return $output;
        }
        
        protected function input_datetime_local($data = []) {
            $output =
                $this->_input_default($data['required'], $data['repeatable']) . 
                '<div class="input-group mt-2">
                    <span class="input-group-text">Label</span>
                    <input type="text" class="form-control" name="label" value="' . (!empty($data['label']) ? $data['label'] : '') . '">
                    <span class="input-group-text">Default Value</span>
                    <input type="datetime-local" class="form-control" name="value" value="' . (!empty($data['value']) ? $data['value'] : '') . '">
                </div>';
            
            return $output;
        }
        
        protected function input_select($data = []) {
            $options = 
                '<ul class="list-group options mt-2 ms-5">';
            
            foreach($data['options'] as $opt) {
                $options .= $this->select_option($opt['value']);
            }
            
            $options .= 
                    '<li class="list-group-item actions bg-light d-flex align-items-center justify-content-end">
                        <input type="button" class="btn btn-primary" name="addOptionSelect" value="+ Option">
                    </li>
                </ul>';
            
            $output =
                $this->_input_default($data['required'], 0, ['multiple' => $data['multiple']]) . 
                '<div class="input-group mt-2">
                    <span class="input-group-text">Label</span>
                    <input type="text" class="form-control" name="label" value="' . (!empty($data['label']) ? $data['label'] : '') . '">
                </div>' . $options;
            
            return $output;
        }
        
        public function select_option($value) {
            $output = 
                '<li class="list-group-item option">
                    <div class="input-group">
                        <span class="input-group-text">Value</span>
                        <input type="text" class="form-control" name="optionvalue" value="' . $value . '" required>
                        <input type="button" class="btn btn-danger" name="deleteOption" value="× Option">
                    </div>
                </li>';
            
            return $output;
        }
        
        protected function input_radio($data = []) {
            $options = 
                '<ul class="list-group options mt-2 ms-5">';
            
            foreach($data['options'] as $opt) {
                $options .= $this->radio_option($data['inputid'], $opt['value'], $opt['default']);
            }
            
            $options .= 
                    '<li class="list-group-item actions bg-light d-flex align-items-center justify-content-end">
                        <input type="button" class="btn btn-primary" name="addOptionRadio" value="+ Option">
                    </li>
                </ul>';
            
            $output =
                $this->_input_default($data['required'], 0) . 
                '<div class="input-group mt-2">
                    <span class="input-group-text">Label</span>
                    <input type="text" class="form-control" name="label" value="' . (!empty($data['label']) ? $data['label'] : '') . '">
                </div>' . $options;
            
            return $output;
        }
        
        public function radio_option($inputId, $value, $checked = false) {
            $output = '';
            
            if(!empty($inputId)) {
                $output .= 
                    '<li class="list-group-item option">
                        <div class="input-group">
                            <span class="input-group-text">Value</span>
                            <input type="text" class="form-control" name="optionvalue" value="' . $value . '" required>
                            <div class="input-group-text">
                                <input type="radio" class="form-check-input mt-0" name="' . $inputId . 'default" ' . ($checked == true ? 'checked' : '') . '>
                            </div>
                            <span class="input-group-text">Default?</span>
                            <input type="button" class="btn btn-danger" name="deleteOption" value="× Option">
                        </div>
                    </li>';
            }
            
            return $output;
        }
        
        protected function input_checkbox($data = []) {
            $output =
                $this->_input_default($data['required'], $data['repeatable']) . 
                '<div class="input-group mt-2">
                    <span class="input-group-text">Label</span>
                    <input type="text" class="form-control" name="label" value="' . (!empty($data['label']) ? $data['label'] : '') . '">
                    <div class="input-group-text">
                        <input type="checkbox" class="form-check-input mt-0" name="checked" ' . ($data['checked'] == true ? 'checked' : '') . '>
                    </div>
                    <span class="input-group-text">Checked?</span>
                </div>';
            
            return $output;
        }
        
        protected function input_file($data = []) {
            $output =
                $this->_input_default(0, 0, ['multiple' => $data['multiple']]) . 
                '<div class="input-group mt-2">
                    <span class="input-group-text">Label</span>
                    <input type="text" class="form-control" name="label" value="' . (!empty($data['label']) ? $data['label'] : '') . '">
                </div>';
            
            return $output;
        }
        
        protected function input_hidden($data = []) {
            $output =
                $this->_input_default(0, 0) . 
                '<div class="input-group mt-2">
                    <span class="input-group-text">Value</span>
                    <input type="text" class="form-control" name="value" value="' . (!empty($data['value']) ? $data['value'] : '') . '">
                </div>';
            
            return $output;
        }
        
        protected function input_button($data = []) {
            $output =
                '<div class="input-group mt-2">
                    <span class="input-group-text">Label</span>
                    <input type="text" class="form-control" name="label" value="' . (!empty($data['label']) ? $data['label'] : '') . '">
                    <span class="input-group-text">Theme</span>
                    <input type="text" class="form-control" name="theme" placeholder="btn-{primary, secondary, success, warning, danger, info, light, dark}" value="' . (!empty($data['theme']) ? $data['theme'] : 'btn-primary') . '">
                </div>';
            
            return $output;
        }
        
        protected function input_submit($data = []) {
            $output =
                '<div class="input-group mt-2">
                    <span class="input-group-text">Label</span>
                    <input type="text" class="form-control" name="label" value="' . (!empty($data['label']) ? $data['label'] : '') . '">
                    <span class="input-group-text">Theme</span>
                    <input type="text" class="form-control" name="theme" placeholder="btn-{primary, secondary, success, warning, danger, info, light, dark}" value="' . (!empty($data['theme']) ? $data['theme'] : 'btn-primary') . '">
                </div>';
            
            return $output;
        }
    }

    class formbuilder extends forminputs {
        private $output = '';
        private $structure = [];
        private $inputs = [];
        
        public function __construct($formId = 0) {
            global $mysqli;
            
            $methods = get_class_methods($this);
            
            foreach($methods as $method) {
                if(strpos($method, 'input_') === 0) {
                    array_push($this->inputs, $method);
                }
            }
            
            //Check the form exists
            $checkForm = $mysqli->prepare("SELECT * FROM `forms` WHERE id = ?");
            $checkForm->bind_param('i', $formId);
            $checkForm->execute();
            $checkResult = $checkForm->get_result();
            
            if($checkResult->num_rows > 0) {
                $form = $checkResult->fetch_assoc();
                
                $this->structure = json_decode($form['structure'], true);
            }
            
            $this->output .= 
                '<ul class="list-group groups">' . 
                    $this->pullgroups($this->structure) .
                    '<li class="list-group-item actions bg-light d-flex align-items-center justify-content-end">
                        <div class="input-group me-2">
                            <span class="input-group-text">Form ID</span>
                            <input type="text" class="form-control bg-white" name="formid" value="' . (!empty($this->structure['formid']) ? $this->structure['formid'] : $formId) . '" readonly>
                            <span class="input-group-text">Action</span>
                            <input type="text" class="form-control" name="action" value="' . $this->structure['action'] . '" placeholder="includes/actions/script.php">
                            <span class="input-group-text">Method</span>
                            <select class="form-control" name="method">
                                <option value="post">POST</option>
                                <option value="get">GET</option>
                            </select>
                        </div>
                        <input type="button" class="btn btn-primary" name="addGroup" value="+ Group">
                    </li>
                </ul>';
        }
        
        public function display() {
            return 
                '<form class="formbuilder" id="' . $this->structure['formid'] . '">' .
                    $this->output . 
                '</form>';
        }
        
        public function pullgroups($structure) {
            $output = '';
            $types = '';
            
            foreach($this->inputs as $inputType) {
                $types .=
                    '<option value="' . $inputType . '">' . ucwords(str_replace('input_', '', $inputType)) . '</option>';
            }
            
            foreach($structure['groups'] as $group) {
                $output .=
                    '<li class="list-group-item group">
                        <div class="input-group">
                            <span class="input-group-text">Group ID</span>
                            <input type="text" class="form-control bg-white" name="groupid" value="' . (isset($group['groupid']) ? $group['groupid'] : '') .'" readonly>
                            <span class="input-group-text">Group Name</span>
                            <input type="text" class="form-control bg-white" name="groupname" value="' . (isset($group['name']) ? $group['name'] : '') .'">
                            <input type="button" class="btn btn-dark" data-bs-toggle="collapse" href="#' . $group['groupid'] . 'inputs" role="button" aria-expanded=false name="expander" value="Expand">
                            <input type="button" class="btn btn-danger" name="deleteGroup" value="× Group">
                        </div>
                        
                        <ul class="list-group inputs collapse mt-2 ms-5" id="' . $group['groupid'] . 'inputs">' .
                            $this->pullinputs($group['inputs']) .
                            '<li class="list-group-item actions bg-light d-flex align-items-center justify-content-end">
                                <div class="input-group">
                                    <select class="form-control" name="inputType">' .
                                        $types .
                                    '</select>
                                    <input type="button" class="btn btn-primary" name="addInput" value="+ Input">
                                </div>
                            </li>
                        </ul>
                    </li>';
            }
            
            return $output;
        }
        
        public function pullinputs($inputs) {
            $output = '';
            
            foreach($inputs as $input) {
                $method = 'input_' . str_replace('-', '_', $input['type']);
                $output .=
                    '<li class="list-group-item input">
                        <div class="input-group">
                            <span class="input-group-text">Input ID</span>
                            <input type="text" class="form-control bg-white" name="inputid" value="' . (isset($input['inputid']) ? $input['inputid'] : '') .'" readonly>
                            <span class="input-group-text">Input Type</span>
                            <input type="text" class="form-control bg-white" name="type" value="' . (isset($input['type']) ? str_replace('_', '-', $input['type']) : '') .'" readonly>
                            <input type="button" class="btn btn-dark" data-bs-toggle="collapse" href="#input' . $input['inputid'] . '" role="button" aria-expanded=false name="expander" value="Expand">
                            <input type="button" class="btn btn-danger" name="deleteInput" value="× Input">
                        </div>
                        
                        <div class="collapse" id="input' . $input['inputid'] . '">' .
                            (method_exists('forminputs', $method) ? $this->$method($input) : '<div class="alert alert-warning m-0 mt-2 p-2">This input appears to be invalid ' .  $method .'</div>') . 
                        '</div>
                    </li>';
            }
            
            return $output;
        }
    }

?>
<?php

    class plugin {
        private $cssFiles = [];
        private $jsFiles = [];
        private $baseDir;

        public function __construct() {
            $this->baseDir = $_SERVER['DOCUMENT_ROOT'] . ROOT_DIR;
        }

        public function storecss($path, $type = '') {
            if(file_exists($path) && pathinfo($path)['extension'] == 'css') {
                $relativePath = explode($this->baseDir, $path)[1];

                if(!in_array($relativePath, $this->cssFiles)) {
                    $this->cssFiles[] =[
                        'path' => $relativePath,
                        'type' => $type
                    ];
                }
            }
        }

        public function loadcss($type = '') {
            if(!empty($this->cssFiles) && is_array($this->cssFiles)) {
                $output = '';
                
                foreach($this->cssFiles as $cssFile) {
                    if($type == $cssFile['type']) {
                        $output .=
                            '<link href="' . $cssFile['path'] . '" rel="stylesheet">';
                    }
                }

                return $output;
            }
        }

        public function storejs($path, $type = '') {
            if(file_exists($path) && pathinfo($path)['extension'] == 'js') {
                $relativePath = explode($this->baseDir, $path)[1];

                if(!in_array($relativePath, $this->jsFiles)) {
                    $this->jsFiles[] =[
                        'path' => $relativePath,
                        'type' => $type
                    ];
                }
            }
        }

        public function loadjs($type = '') {
            if(!empty($this->jsFiles) && is_array($this->jsFiles)) {
                $output = '';
                
                foreach($this->jsFiles as $jsFile) {
                    if($type == $jsFile['type']) {
                        $output .=
                            '<script src="' . $jsFile['path'] . '"></script>';
                    }
                }

                return $output;
            }
        }
    }

    $__pluginManager = new plugin();

?>
<?php

    class plugin {
        private $cssFiles = [];
        private $baseDir;

        public function __construct() {
            $this->baseDir = explode('://', BASE_DIR)[1];
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
    }

    $__pluginManager = new plugin();

?>
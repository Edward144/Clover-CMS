<?php

    class breadcrumbs {
        private $trail;
        private $ti;
        private $output = '';
        private $delimiter;
        private $prefix;

        public function __construct($pageId, $delimiter = '>', $prefix = 'You are here:') {
            if(!is_numeric($pageId) || $pageId <= 0) {
                return;
            }
            else {
                $this->delimiter = $delimiter;
                $this->prefix = $prefix;
                $this->ti = 0;

                $this->buildtrail($pageId);
            }
        }

        private function buildtrail($pageId) {
            global $mysqli;
            
            $checkPage = $mysqli->prepare(
                "SELECT p.id, ns.id AS nav_id, ns.parent_id, ns.name, ns.url FROM `navigation_structure` AS ns
                    LEFT OUTER JOIN `posts` AS p ON p.url = ns.url
                WHERE p.id = ?"
            );
            $checkPage->bind_param('i', $pageId);
            $checkPage->execute();
            $checkResult = $checkPage->get_result();

            if($checkResult->num_rows > 0) {
                $page = $checkResult->fetch_assoc();
                
                $this->trail[$this->ti] = [
                    'id' => $page['id'],
                    'nav_id' => $page['nav_id'],
                    'parent_id' => $page['parent_id'],
                    'name' => $page['name'],
                    'url' => $page['url']                    
                ];

                $this->ti++;

                if($page['parent_id'] > 0) {
                    $checkParent = $mysqli->prepare(
                        "SELECT p.id, ns.id AS nav_id, ns.parent_id, ns.name, ns.url FROM `navigation_structure` AS ns
                            LEFT OUTER JOIN `posts` AS p ON p.url = ns.url
                        WHERE ns.id = ?"
                    );
                    $checkParent->bind_param('i', $page['parent_id']);
                    $checkParent->execute();
                    $checkResult = $checkParent->get_result();
        
                    if($checkResult->num_rows > 0) {
                        $parent = $checkResult->fetch_assoc();
                        $this->buildtrail($parent['id']);
                    }
                }
            }
        }

        public function display() {
            if(!empty($this->trail)) {
                $this->trail = array_reverse($this->trail);

                $trailCount = count($this->trail);
                $ti = 1;

                $this->output =
                    '<div class="breadcrumbs">' . $this->prefix . ' ';

                foreach($this->trail as $trail) {
                    if($ti == $trailCount) {
                        $this->output .=
                            '<span class="breadcrumb current">' . $trail['name'] . '</span>';
                    }
                    else {
                        $this->output .=
                            '<span class="breadcrumb"><a href="' . $trail['url'] . '">' . $trail['name'] . '</a> ' . $this->delimiter . ' </span>';
                    }
                    
                    $ti++;
                }

                $this->output .=
                    '</div>';
            }

            return $this->output;
        }
    }

?>
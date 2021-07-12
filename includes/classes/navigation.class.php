<?php

    class navbar {
        public $menuId;
        private $output = '';
        
        public function __construct($menuId = 0) {
            if(is_numeric($menuId) && $menuId >= 0) {
                $this->menuId = $menuId;
            }
            else {
                $this->menuId = 0;
            }
            
            $this->display();
        }
        
        private function display() {
            global $mysqli;
            
            //Check menu exists 
            if($this->menuId > 0) {
                $checkMenu = $mysqli->prepare("SELECT COUNT(*) FROM `navigation_menus` WHERE id = ?");
                $checkMenu->bind_param('i', $this->menuId);
                $checkMenu->execute();
                $checkResult = $checkMenu->get_result();
                
                if($checkResult->fetch_array()[0] <= 0) {
                    return;
                }
            }
            
            $this->output =
                '<nav class="navbar navbar-expand-xl navbar-dark py-0" id="navbar' . $this->menuId . '">
                    <button class="navbar-toggler ms-auto me-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse' . $this->menuId . '" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarCollapse' . $this->menuId . '">'
                        . $this->createlevel() .
                    '</div>
                </nav>';
            
            echo $this->output;
        }
        
        private function createlevel($parentId = 0) {
            global $mysqli;
            $output = '';
            
            $items = $mysqli->prepare("SELECT * FROM `navigation_structure` WHERE menu_id = ? AND parent_id = ? AND visible = 1 ORDER BY position ASC");
            $items->bind_param('ii', $this->menuId, $parentId);
            $items->execute();
            $itemsResult = $items->get_result();
            
            if($itemsResult->num_rows > 0) {
                $output .= 
                    '<ul class="' . ($parentId == 0 ? 'navbar-nav ms-auto mb-2 mb-xl-0' : 'navbar-nav subMenu w-100') . '">';

                while($item = $itemsResult->fetch_assoc()) {
                    $output .=
                        '<li class="nav-item' . ($this->checkchildren($item['id']) ? ' dropdown d-flex flex-wrap' : '') . '">
                            <a class="nav-link ' . ($this->checkChildren($item['id']) ? 'd-inline-block ' : '') . 'flex-grow-1 px-3 px-xl-0" href="' . $item['url'] . '" ' . (!empty($item['target']) ? 'target="' . $item['target'] . '"' : '') . '>' . $item['name'] . '</a>'
                            . ($this->checkChildren($item['id']) ? '<a class="nav-link d-inline-block dropdown-toggle float-end" href="#" id="dropdown' . $item['id'] . '" role="button" aria-expanded="false"></a>' : '')
                            . ($this->checkchildren($item['id']) ? $this->createlevel($item['id']) : '') .
                        '</li>';
                }

                $output .=    
                    '</ul>';
                
                return $output;
            }
        }
        
        protected function checkchildren($id) {
            global $mysqli;
            
            $checkChildren = $mysqli->prepare("SELECT COUNT(*) FROM `navigation_structure` WHERE parent_id = ?");
            $checkChildren->bind_param('i', $id);
            $checkChildren->execute();
            $checkResult = $checkChildren->get_result()->fetch_array()[0];
            
            return ($checkResult > 0 ? true : false);
        }
    }
    
    class verticalnav extends navbar {
        private function display() {
            
        }
        
        private function createlevel($parentId = 0) {
            
        }
    }

?>
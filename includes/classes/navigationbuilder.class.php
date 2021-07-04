<?php
    
    class navigationTree {
        private $id;
        private $output;
        private $levelInc = 0;
        private $hasLevels = false;
        
        public function __construct($id = 0) {
            global $structuremessage;
            global $status;
            
            if(isset($id) && is_numeric($id) && $id >= 0) {
                $this->id = $id;
            }
            
            $this->output =
                '<form class="structure structureItems" id="structure' . $this->id . '" method="post">'
                    . $this->createlevel($this->id) . 
                    '<div class="form-group">
                        <input type="hidden" name="json">
                        <input type="hidden" name="structureId" value="' . $this->id . '">
                        <input type="submit" class="btn btn-primary" name="saveStructure" name="saveStructure" value="Save Navigation Structure">
                    </div>'
                    . (!empty($structuremessage[$this->id]) ? 
                        '<div class="alert alert-' . $status[$this->id] . ' mt-3">' . $structuremessage[$this->id] . '</div>' : '') .
                '</form>';
        }
        
        public function display() {
            if($this->hasLevels == true) {
                echo $this->output;
            }
            else {
                echo '<div class="alert alert-info">There are currently no items in this menu</div>';
            }
        }
        
        private function createlevel($menuId, $parentId = 0) {
            global $mysqli;
            $output = '';
            
            $getLevel = $mysqli->prepare("SELECT * FROM `navigation_structure` WHERE menu_id = ? AND parent_id = ? ORDER BY position ASC");
            $getLevel->bind_param('ii', $menuId, $parentId);
            $getLevel->execute();
            $levelResult = $getLevel->get_result();
            
            if($levelResult->num_rows > 0) {
                $this->hasLevels = true;
                
                if($parentId > 0) {
                    $this->levelInc++;
                }
                else {
                    $this->levelInc = 0;
                }
                
                while($level = $levelResult->fetch_assoc()) {
                    $output .=
                        '<div class="navigationLevel" data-position="' . $level['position'] . '" data-id="' . $level['id'] . '">
                            <div class="row bg-light p-3 mx-0 rounded">
                                <div class="col-xl">
                                    <span class="me-3">' . $level['name'] . '</span>
                                    <small class="text-muted">' . $level['url'] . '</small>
                                </div>

                                <div class="col-xl-3 text-end mb-n1">
                                    <button type="button" class="btn btn-primary mb-1" name="edit">Edit</button>
                                    <button type="button" class="btn btn-danger mb-1" name="delete">Delete</button>
                                </div>
                            </div>
                            
                            <div class="modal fade" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit ' . $level['name'] . '</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true"></span>
                                            </button>
                                        </div>
                                        
                                        <div class="modal-body">
                                            <div id="editItem">
                                                <input type="hidden" name="id" value="' . $level['id'] . '">
                                                <input type="hidden" name="parent" value="' . $level['parent_id'] . '">
                                                <input type="hidden" name="position" value="' . $level['position'] . '">
                                                <input type="hidden" name="delete" value="0">
                                                
                                                <div class="form-group mb-3">
                                                    <label>Name</label>
                                                    <input type="text" class="form-control" name="name" value="' . $level['name'] . '" required>
                                                </div>
                                                
                                                <div class="form-group mb-3">
                                                    <label>Url</label>
                                                    <input type="text" class="form-control" name="url" value="' . $level['url'] . '">
                                                </div>
                                                
                                                <div class="form-group mb-3">
                                                    <label>Visible</label>
                                                    <select name="visible" class="form-control">
                                                        <option value="0" ' . ($level['visible'] == 0 ? 'selected' : '') . '>No</option>
                                                        <option value="1" ' . ($level['visible'] == 1 ? 'selected' : '') . '>Yes</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> <div class="structureItems">'
                            . $this->createlevel($menuId, $level['id']) . 
                        '</div></div>';
                }
            }
            
            return $output;
        }
    }
    
?>
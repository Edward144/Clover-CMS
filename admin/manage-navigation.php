<?php 
	$title = 'Navigation';
	require_once(dirname(__FILE__) . '/includes/header.php'); 

    //Redirect to menu 0 if the id is empty or doesn't exist
    if(!isset($_GET['id'])) {
        http_response_code(404);
        header('Location: ./manage-navigation/0');
        exit();
    }

    $checkMenu = $mysqli->prepare("SELECT * FROM `navigation_menus` WHERE id = ?");
    $checkMenu->bind_param('i', $_GET['id']);
    $checkMenu->execute();
    $checkResult = $checkMenu->get_result();

    if($checkResult->num_rows <= 0 && $_GET['id'] != 0) {
        http_response_code(404);
        header('Location: ./0');
        exit();
    }

    if($_GET['id'] == 0) {
        $currMenu = [
            'id' => 0,
            'name' => 'Main Menu'
        ];
    }
    else {
        $currMenu = $checkResult->fetch_assoc();
    }

    class navigationTree {
        private $id;
        private $output;
        private $levelInc = 0;
        private $hasLevels = false;
        
        public function __construct($id = 0) {
            if(isset($id) && is_numeric($id) && $id >= 0) {
                $this->id = $id;
            }
            
            $this->output =
                '<div class="structure" id="structure' . $this->id . '">'
                    . $this->createlevel($this->id) . 
                '</div>';
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
                        '<div class="navigationLevel bg-light p-3 rounded mb-3" ' . ($parentId > 0 ? 'style="margin-left: ' . $this->levelInc . 'rem;"' : '') . '>
                            <div class="row">
                                <div class="col-xl">
                                    <span class="me-3">' . $level['name'] . '</span>
                                    <small class="text-muted">' . $level['url'] . '</small>
                                </div>

                                <div class="col-xl-3 text-end mb-n1">
                                    <button type="button" class="btn btn-primary mb-1">Edit</button>
                                    <button type="button" class="btn btn-danger mb-1">Delete</button>
                                </div>
                            </div>
                        </div>' . $this->createlevel($menuId, $level['id']);
                }
            }
            
            return $output;
        }
    }
?>

<div class="col-lg-3 bg-light py-3">
    <h3>Manage Menu</h3>
    
	<div class="form-group mb-3">
        <select class="form-control" name="chooseMenu">
            <option value="0">Main Menu</option>
            
            <?php $menus = $mysqli->query("SELECT * FROM `navigation_menus` ORDER BY name ASC"); ?>
            
            <?php if($menus->num_rows > 0) : ?>
                <?php while($menu = $menus->fetch_assoc()) : ?>
                    <option value="<?php echo $menu['id']; ?>" <?php echo ($menu['id'] == $_GET['id'] ? 'selected' : ''); ?>><?php echo $menu['name']; ?></option>
                <?php endwhile; ?>
            <?php endif; ?>
        </select>
    </div>
    
    <hr>
</div>

<div class="col py-3">
    <h3><?php echo $currMenu['name']; ?> Structure</h3>
    
    <?php $structure = new navigationTree($_GET['id']); $structure->display(); ?>
</div>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
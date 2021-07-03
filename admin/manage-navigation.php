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

    //Insert new item
    if(isset($_POST['insertItem'])) {
        $getLastPosition = $mysqli->prepare("SELECT MAX(position) FROM `navigation_structure` WHERE parent_id = ? and menu_id = ?");
        $getLastPosition->bind_param('ii', $_POST['parent'], $_POST['menuId']);
        $getLastPosition->execute();
        $lastPositionResult = $getLastPosition->get_result();
        
        if($lastPositionResult->num_rows > 0) {
            $position = $lastPositionResult->fetch_array()[0] + 1;
        }
        else {
            $position = 0;
        }
        
        $insert = $mysqli->prepare("INSERT INTO `navigation_structure` (menu_id, name, url, parent_id, visible, position) VALUES(?, ?, ?, ?, ?, ?)");
        $insert->bind_param('issiii', $_POST['menuId'], $_POST['name'], $_POST['url'], $_POST['parent'], $_POST['visible'], $position);
        $insert->execute();
        
        if($insert->error) {
            $status = 'danger';
            $insertmessage = 'Failed to insert item into menu';
        }
        else {
            $status = 'success';
            $insertmessage = 'Item inserted successfully';
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
    
    <h3>Insert Item</h3>
    
    <form id="insertNavigation" method="post">
        <input type="hidden" name="menuId" value="<?php echo $_GET['id']; ?>">
        
        <?php 
            $existing = $mysqli->query(
                "SELECT posts.id, posts.name, posts.url, post_types.name AS post_type FROM `posts`
                    LEFT OUTER JOIN `post_types` AS post_types ON post_types.id = posts.post_type_id
                ORDER BY posts.post_type_id, posts.name"
            );
        ?>
        
        <?php if($existing->num_rows > 0) : ?>
            <div class="form-group mb-3">
                <label>Choose Existing</label>
                <select class="form-control" name="existing">
                    <option selected disabled>--Select--</option>
                    
                    <?php while($existingItem = $existing->fetch_assoc()) : ?>
                        <option value="<?php echo $existingItem['id']; ?>" data-name="<?php echo $existingItem['name']; ?>" data-url="<?php echo $existingItem['url']; ?>"><?php echo $existingItem['post_type'] . ': ' . $existingItem['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        <?php endif; ?>
        
        <div class="form-group mb-3">
            <label>Name</label>
            <input type="text" class="form-control" name="name" required>
        </div>
        
        <div class="form-group mb-3">
            <label>Url</label>
            <input type="text" class="form-control" name="url">
        </div>
        
        <div class="form-group mb-3">
            <label>Parent</label>
            <select class="form-control" name="parent">
                <option value="0">No Parent</option>
                
                <?php 
                    $items = $mysqli->prepare("SELECT id, name FROM `navigation_structure` WHERE menu_id = ? ORDER BY parent_id, position"); 
                    $items->bind_param('i', $_GET['id']);
                    $items->execute();
                    $itemsResult = $items->get_result();
                ?>
                
                <?php if($itemsResult->num_rows > 0) : ?>
                    <?php while($item = $itemsResult->fetch_assoc()) : ?>
                        <option value="<?php echo $item['id']; ?>"><?php echo $item['name']; ?></option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>
        
        <div class="form-group mb-3">
            <label>Visible</label>
            <select class="form-control" name="visible">
                <option value="0">No</option>
                <option value="1" selected>Yes</option>
            </select>
        </div>
        
        <div class="form-group">
            <input type="submit" class="btn btn-primary" name="insertItem" value="Insert">
        </div>
        
        <?php if(!empty($insertmessage)) : ?>
            <div class="mt-3 alert alert-<?php echo $status; ?>">
                <?php echo $insertmessage; ?>
            </div>
        <?php endif; ?>
    </form>
</div>

<div class="col py-3">
    <h3><?php echo $currMenu['name']; ?> Structure</h3>
    
    <?php $structure = new navigationTree($_GET['id']); $structure->display(); ?>
</div>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
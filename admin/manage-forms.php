<?php 
    require_once(dirname(__DIR__) . '/includes/database.php');
    require_once(dirname(__DIR__) . '/includes/functions.php');

    checkaccess(basename(__FILE__));

    //Create Form
    if(isset($_POST['createForm'])) {
        $create = $mysqli->query("INSERT INTO `forms` (name) VALUES('New Form')");
        $lastId = $mysqli->insert_id;

        $rename = $mysqli->query("UPDATE `forms` SET name = CONCAT(name, ' {$lastId}') WHERE id = {$lastId}");

        if($mysqli->error) {
            /*$status = 'danger';
            $createmsg = 'Failed to create form';*/
            createnotification('Failed to form', 'alert-danger');
        }
        else {
            header('Location: ./manage-forms?id=' . $lastId);
            exit();
        }
    }

    //Save Form
    if(isset($_POST['saveForm'])) {
        $save = $mysqli->prepare("UPDATE `forms` SET name = ?, structure = ? WHERE id = ?");
        $save->bind_param('ssi', $_POST['name'], $_POST['structure'], $_POST['id']);
        $save->execute();

        if($save->error) {
            /*$status = 'danger';
            $savemsg = 'Failed to save form';*/
            createnotification('Failed to save form', 'alert-danger');
        }
        else {
            /*$status = 'success';
            $savemsg = 'Saved form successfully';*/
            createnotification('Form save successfully', 'alert-success');
        }
    }

    //Delete Form
    if(isset($_POST['deleteForm'])) {
        $delete = $mysqli->prepare("DELETE FROM `forms` WHERE id = ?");
        $delete->bind_param('i', $_POST['id']);
        $delete->execute();
        
        if($delete->error) {
            $status = 'danger';
            $deletemsg = 'Failed to delete form';
        }
        else {
            $status = 'success';
            $deletemsg = 'Successfully deleted form';
        }
        
        echo json_encode(['status' => $status, 'message' => $deletemsg]);
        exit(); 
    }
?>

<?php if(isset($_GET['id'])) : ?>
    <?php
        $checkForm = $mysqli->prepare("SELECT * FROM `forms` WHERE id = ? LIMIT 1");
        $checkForm->bind_param('i', $_GET['id']);
        $checkForm->execute();
        $formResult = $checkForm->get_result();

        if($formResult->num_rows <= 0) {
            header('Location: ./manage-forms');
            exit();
        }
        
        $form = $formResult->fetch_assoc();
        $title = 'Manage Forms: ' . $form['name'];
        require_once(dirname(__FILE__) . '/includes/header.php');
    ?>

    <div class="col-lg-3 bg-light py-3">	
        <div class="form-group mb-3">
            <input type="button" class="btn btn-dark" name="returnList" value="Return to List">
        </div>
        
        <form id="manageForm" method="post">
            <input type="hidden" name="id" value="<?php echo $form['id']; ?>">
            <input type="hidden" name="structure" value="<?php echo $form['structure']; ?>">
            
            <div class="form-group mb-3">
                <label>Name</label>
                <input type="text" class="form-control" name="name" value="<?php echo $form['name']; ?>" required>
            </div>
            
            <div class="form-group mb-n1">
                <input type="submit" class="btn btn-primary mb-1" name="saveForm" value="Save Form">
                <input type="button" class="btn btn-danger mb-1" data-id="<?php echo $form['id']; ?>" name="deleteForm" value="Delete">
            </div>
            
            <?php if(!empty($savemsg)) : ?>
                <div class="alert alert-<?php echo $status; ?> mt-3 mb-0">
                    <?php echo $savemsg; ?>    
                </div>
            <?php endif; ?>
        </form>
    </div>

    <div class="col py-3">
        <?php $formbuilder = new formbuilder($form['id']); echo $formbuilder->display(); ?>
    </div>
<?php else : ?>
    <?php 
        $title = 'Manage Forms';
        require_once(dirname(__FILE__) . '/includes/header.php'); 
    ?>

    <div class="col-lg-3 bg-light py-3">	
        <form id="createForm" method="post">
            <div class="form-group mb-3">
                <input type="submit" class="btn btn-primary" name="createForm" value="Create Form">
            </div>
            
            <?php if(!empty($createmsg)) : ?>
                <div class="alert alert-<?php echo $status; ?>">
                    <?php echo $createmsg; ?>
                </div>
            <?php endif; ?>
        </form>
        
        <h3>Search Forms</h3>
		
		<form id="searchForms">
			<div class="form-group">
				<div class="input-group">
					<input type="text" class="form-control" name="search" value="<?php echo $_GET['search']; ?>" required>
					
					<input type="submit" class="btn btn-primary" value="Search">
					
					<?php if(isset($_GET['search'])) : ?>
						<input type="button" class="btn btn-dark" name="clearSearch" value="Clear">
					<?php endif; ?>
				</div>
			</div>
		</form>
        
        <?php if(!empty($deletemsg)) : ?>
            <div class="alert alert-<?php echo $status; ?>">
                <?php echo $deletemsg; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="col py-3">
        <h3>Current Forms</h3>
		
		<?php 
			$search = (!empty($_GET['search']) ? '%' . $_GET['search'] . '%' : '%');
		
            $formCount = $mysqli->prepare("SELECT * FROM `forms` WHERE name LIKE ? ORDER BY name ASC");
			$formCount->bind_param('s', $search);
			$formCount->execute();
			$formCountResult = $formCount->get_result();
        
            $pagination = new pagination($formCountResult->num_rows);
			$pagination->load();
        
			$forms = $mysqli->prepare("SELECT * FROM `forms` WHERE name LIKE ? ORDER BY name ASC LIMIT {$pagination->itemsPerPage} OFFSET {$pagination->offset}");
			$forms->bind_param('s', $search);
			$forms->execute();
			$formsResult = $forms->get_result();			
		?>
		
		<?php if($formsResult->num_rows > 0) : ?>
			<div class="table-responsive">
				<table class="table table-striped">
					<thead class="table-dark">
						<tr>
							<th class="shorten">ID</th>
							<th>Details</th>
							<th class="shorten">Actions</th>
						</tr>
					</thead>
					
					<tbody>
						<?php while($row = $formsResult->fetch_assoc()) : ?>
							<tr>
								<th class="shorten" scope="row"><?php echo $row['id']; ?></th>
								
								<td>
									<span><strong><?php echo $row['name']; ?></strong></span><br>
									<small>Insert: [form id="<?php echo $row['id']; ?>"]</small>
								</td>
								
								<td class="shorten">
									<div class="form-group mb-n1">
                                        <?php echo editbutton($row['id']); ?>
										<input type="button" class="btn btn-danger mb-1" name="deleteForm" data-id="<?php echo $row['id']; ?>" value="Delete">
									</div>
								</td>
							</tr>
						<?php endwhile; ?>
					</tbody>
				</table>
			</div>
        
            <?php echo $pagination->display(); ?>
		<?php else : ?>
			<div class="alert alert-info">No content could be found</div>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
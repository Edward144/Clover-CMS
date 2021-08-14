<?php 
    require_once(dirname(__DIR__) . '/includes/database.php');
    require_once(dirname(__DIR__) . '/includes/functions.php');
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

        //Save Form
        if(isset($_POST['saveForm'])) {
            $save = $mysqli->prepare("UPDATE `forms` SET name = ?, structure = ? WHERE id = ?");
            $save->bind_param('ssi', $_POST['name'], $_POST['structure'], $_POST['id']);
            $save->execute();
            
            if($save->error) {
                $status = 'danger';
                $savemsg = 'Failed to save form';
            }
            else {
                $status = 'success';
                $savemsg = 'Saved form successfully';
            }
        }
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
            
            <div class="form-group">
                <input type="submit" class="btn btn-primary" name="saveForm" value="Save Form">
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
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Create Form">
            </div>
        </form>
    </div>

    <div class="col py-3">
        <h3>Current Forms</h3>
		
		<?php 
			$search = (!empty($_GET['search']) ? '%' . $_GET['search'] . '%' : '%');
		
            $contentCount = $mysqli->prepare(
				"SELECT posts.* FROM `posts` AS posts
					LEFT OUTER JOIN `post_types` AS post_types ON post_types.id = posts.post_type_id
				WHERE post_types.name = ? AND (posts.name LIKE ? OR posts.id LIKE ? OR posts.author LIKE ? OR posts.excerpt LIKE ? OR posts.content LIKE ?) 
				ORDER BY date_created DESC"
			);
			$contentCount->bind_param('ssssss', $pt['name'], $search, $search, $search, $search, $search);
			$contentCount->execute();
			$contentCountResult = $contentCount->get_result();
        
            $pagination = new pagination($contentCountResult->num_rows);
			$pagination->load();
        
			$content = $mysqli->prepare(
				"SELECT posts.* FROM `posts` AS posts
					LEFT OUTER JOIN `post_types` AS post_types ON post_types.id = posts.post_type_id
				WHERE post_types.name = ? AND (posts.name LIKE ? OR posts.id LIKE ? OR posts.author LIKE ? OR posts.excerpt LIKE ? OR posts.content LIKE ?) 
				ORDER BY date_created DESC LIMIT {$pagination->itemsPerPage} OFFSET {$pagination->offset}"
			);
			$content->bind_param('ssssss', $pt['name'], $search, $search, $search, $search, $search);
			$content->execute();
			$contentResult = $content->get_result();			
		?>
		
		<?php if($contentResult->num_rows > 0) : ?>
			<div class="table-responsive">
				<table class="table table-striped">
					<thead class="table-dark">
						<tr>
							<th class="shorten">ID</th>
							<th>Details</th>
							<th class="shorten">Date Created</th>
							<th class="shorten">Actions</th>
						</tr>
					</thead>
					
					<tbody>
						<?php while($row = $contentResult->fetch_assoc()) : ?>
							<tr>
								<th class="shorten" scope="row"><?php echo $row['id']; ?></th>
								
								<td>
									<span><strong><?php echo $row['name']; ?></strong></span><br>
									<small>URL: /<?php echo $row['url']; ?></small>
								</td>
								
								<td class="shorten">
									<?php echo date('d/m/Y', strtotime($row['date_created'])); ?><br>
									<?php echo date('H:i', strtotime($row['date_created'])); ?>
								</td>
								
								<td class="shorten">
									<div class="form-group mb-n1">
										<a href="<?php echo explode('?', $_SERVER['REQUEST_URI'])[0] . '?id=' . $row['id']; ?>" class="btn btn-primary mb-1">Edit</a>
										<input type="button" class="btn btn-danger mb-1" name="deleteContent" data-id="<?php echo $row['id']; ?>" value="Delete">
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
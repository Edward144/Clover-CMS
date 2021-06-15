<?php
	require_once(dirname(__FILE__, 2) . '/includes/database.php');
	
	$checkType = $mysqli->prepare("SELECT id, name FROM `post_types` WHERE name = ?");
	$checkType->bind_param('s', $_GET['post-type']);
	$checkType->execute();
	$checkResult = $checkType->get_result();

	if($checkResult->num_rows <= 0 || !isset($_GET['post-type'])) {
		http_response_code(404);
		header('Location: ' . ROOT_DIR . 'admin');
		exit();
	}

	$pt = $checkResult->fetch_assoc();

	//Create Content
    if(isset($_POST['createContent'])) {
        $unique = rtrim(base64_encode(date('Y-m-d H:i:s')), '=');
                        
        $create = $mysqli->prepare("INSERT INTO `posts` (post_type_id, name, url) VALUES(?, ?, ?)");
        $create->bind_param('iss', $pt['id'], $pt['name'], $unique);
        $ex = $create->execute();
        
        if($ex === false) {
            $status = 'danger';
            $message = 'Failed to create content';
        }
        else {
            $lastId = $mysqli->insert_id;
            $name = ucwords(str_replace('-', ' ', $pt['name'])) . ' ' . $lastId;
            $url = str_replace(' ', '-', strtolower($name));
            
            $updateCreate = $mysqli->prepare("UPDATE `posts` SET name = ?, url = ? WHERE id = ?");
            $updateCreate->bind_param('ssi', $name, $url, $lastId);
            $updateCreate->execute();
                
            header('Location: ' . explode('?', $_SERVER['REQUEST_URI'])[0] . '?id=' . $lastId);
            exit();
        }
    }

	//Delete Content
	if(isset($_POST['method']) && $_POST['method'] == 'deleteContent') {
		$delete = $mysqli->prepare("DELETE FROM `posts` WHERE id = ?");
		$delete->bind_param('i', $_POST['id']);
		$delete->execute();
		
		if($delete->affected_rows > 0) {
			echo json_encode(['status' => 'success', 'message' => 'Successfully deleted content']);
		}
		else {
			echo json_encode(['status' => 'danger', 'message' => 'Failed to delete content']);
		}
		
		exit();
	}

	$title = 'Manage ' . ucwords(str_replace('-', ' ', $pt['name']));
?>

<?php if(isset($_GET['id'])) : ?>
    <?php 
        $contentCheck = $mysqli->prepare("SELECT * FROM `posts` WHERE id = ?");
        $contentCheck->bind_param('i', $_GET['id']);
        $contentCheck->execute();
        $contentResult = $contentCheck->get_result();
        
        if($contentResult->num_rows <= 0) {
            http_response_code(404);
            header('Location: ' . explode('?', $_SERVER['REQUEST_URI'])[0]);
            exit();
        }

        $content = $contentResult->fetch_assoc();

        require_once(dirname(__FILE__) . '/includes/header.php'); 
    ?>

    <form id="manageContent" method="post">
        <input type="hidden" name="id" value="<?php echo $content['id']; ?>">
        
        <div class="col-lg-3 bg-light py-3">
            
        </div>

        <div class="col py-3">

        </div>
    </form>
<?php else : ?>
    <?php require_once(dirname(__FILE__) . '/includes/header.php'); ?>

	<div class="col-lg-3 bg-light py-3">		
		<form id="createContent" method="post">
			<div class="form-group mb-3">
				<input type="submit" class="btn btn-primary" name="createContent" value="Create Content">
			</div>
		</form>
		
		<h3>Search Content</h3>
		
		<form id="searchContent">
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
	</div>

	<div class="col py-3">
		<h3>Current <?php echo $pt['name']; ?></h3>
		
		<?php 
			$search = (!empty($_GET['search']) ? '%' . $_GET['search'] . '%' : '%');
		
			$content = $mysqli->prepare(
				"SELECT posts.* FROM `posts` AS posts
					LEFT OUTER JOIN `post_types` AS post_types ON post_types.id = posts.post_type_id
				WHERE post_types.name = ? AND (posts.name LIKE ? OR posts.id LIKE ? OR posts.author LIKE ? OR posts.excerpt LIKE ? OR posts.content LIKE ?) 
				ORDER BY date_created DESC" //LIMIT OFFSET
			);
			$content->bind_param('ssssss', $pt['name'], $search, $search, $search, $search, $search);
			$content->execute();
			$contentResult = $content->get_result();
		
			/*$pagination = new pagination($content->num_rows);
			$pagination->load();*/
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
		<?php else : ?>
			<div class="alert alert-info">No content could be found</div>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
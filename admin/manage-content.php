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
	$title = 'Manage ' . ucwords(str_replace('-', ' ', $pt['name']));
	require_once(dirname(__FILE__) . '/includes/header.php'); 
?>

<?php if(isset($_GET['id'])) : ?>
	<div class="col-lg-3 bg-light py-3">

	</div>

	<div class="col py-3">

	</div>
<?php else : ?>
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
					<input type="text" class="form-control" name="search" required>
					
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
				"SELECT * FROM `posts` AS posts
					LEFT OUTER JOIN `post_types` AS post_types ON post_types.id = posts.post_type_id
				WHERE post_types.name = ? AND (posts.name LIKE ? OR posts.id LIKE ? OR posts.author LIKE ? OR posts.excerpt LIKE ? OR posts.content LIKE ?)" //LIMIT OFFSET
			);
			$content->bind_param('ssssss', $pt['name'], $search, $search, $search, $search, $search);
			$content->execute();
			$contentResult = $content->get_result();
		
			/*$pagination = new pagination($content->num_rows);
			$pagination->load();*/
		?>
		
		<?php if($contentResult->num_rows > 0) : ?>
		
		<?php else : ?>
		
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
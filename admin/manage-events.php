<?php
	require_once(dirname(__FILE__, 2) . '/includes/database.php');
    require_once(dirname(__FILE__, 2) . '/includes/functions.php');

    checkaccess(basename(__FILE__));
	$title = 'Manage Events';

    /*
    //Create Content
    if(isset($_POST['createContent'])) {
        $unique = rtrim(base64_encode(date('Y-m-d H:i:s')), '=');
        $defaultContent = 
            '<h1>Enter your title</h1>
            <p>Enter your content...</p>';
        
        $create = $mysqli->prepare("INSERT INTO `posts` (post_type_id, name, url, content, last_edited_by) VALUES(?, ?, ?, ?, ?)");
        $create->bind_param('isssi', $pt['id'], $pt['name'], $unique, $defaultContent, $_SESSION['adminid']);
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

    //Save Content
    if(isset($_POST['saveContent'])) {
        $allowComments = (isset($_POST['allowComments']) ? 1 : 0);
        
        $save = $mysqli->prepare("UPDATE `posts` SET name = ?, url = ?, template = ?, author = ?, date_created = ?, state = ?, featured_image = ?, carousel = ?, excerpt = ?, content = ?, meta_title = ?, meta_description = ?, meta_keywords = ?, meta_author = ?, last_edited = NOW(), last_edited_by = ?, allow_comments = ? WHERE id = ?");
        $save->bind_param('sssssissssssssiii', $_POST['name'], $_POST['url'], $_POST['template'], $_POST['author'], $_POST['dateCreated'], $_POST['state'], $_POST['featuredImage'], $_POST['carousel'], $_POST['excerpt'], $_POST['content'], $_POST['metaTitle'], $_POST['metaDescription'], $_POST['metaKeywords'], $_POST['metaAuthor'], $_SESSION['adminid'], $allowComments, $_POST['id']);
        $save->execute();
        
        if($save->error) {
            $status = 'danger';
            $message = 'Failed to save changes';
        }
        else {
            $status = 'success';
            $message = 'Saved changes successfully';
        }
    }

    //Add Carousel Slide
    if(isset($_POST['method']) && $_POST['method'] == 'carouselRegen') {
        $carousel = carousel($_POST['carouselid'], true, $_POST['carouseldata']);
        echo json_encode($carousel);
        exit();
    }*/
?>

<?php if(isset($_GET['id'])) : ?>
    <?php 
        $eventCheck = $mysqli->prepare("SELECT * FROM `posts` WHERE id = ?");
        $eventCheck->bind_param('i', $_GET['id']);
        $eventCheck->execute();
        $eventResult = $eventCheck->get_result();
        
        if($eventResult->num_rows <= 0) {
            http_response_code(404);
            header('Location: ' . explode('?', $_SERVER['REQUEST_URI'])[0]);
            exit();
        }

        $event = $eventResult->fetch_assoc();

        require_once(dirname(__FILE__) . '/includes/header.php'); 
    ?>

    <form id="manageContent" class="row" method="post">
        <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
        
        <div class="col-lg-3 bg-light py-3">
            <div class="form-group mb-3">
                <input type="button" class="btn btn-dark" name="returnList" value="Return to List">
            </div>
            
            <div class="form-group mb-3">
                <label>Name</label>
                <input type="text" class="form-control" name="name" value="<?php echo $event['name']; ?>" required>
            </div>
            
            <div class="form-group mb-3">
                <label>Url</label>
                <input type="text" class="form-control" name="url" value="<?php echo $event['url']; ?>" required>
            </div>
            
            <div class="form-group mb-3">
                <label>Template</label>
                <select class="form-control" name="template">
                    <option value="">Standard</option>
                    
                    <?php foreach(glob($_SERVER['DOCUMENT_ROOT'] . ROOT_DIR . 'includes/templates/*.php') as $template) : ?>
                        <option value="<?php echo pathinfo($template)['filename']; ?>" <?php echo ($event['template'] == pathinfo($template)['filename'] ? 'selected' : ''); ?>><?php echo pathinfo($template)['filename']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group mb-3">
                <label>Author</label>
                <input type="text" class="form-control" name="author" value="<?php echo $event['author']; ?>">
            </div>
            
            <div class="form-group mb-3">
                <label>Date Created</label>
                <input type="datetime-local" class="form-control" name="dateCreated" value="<?php echo date('Y-m-d\TH:i', strtotime($event['date_created'])); ?>" required>
            </div>
            
            <div class="form-group mb-3">
                <label>Visiblity</label>
                <select class="form-control" name="state" required>
                    <option value="0" <?php echo ($event['state'] == 0 ? 'selected' : ''); ?>>Hidden</option>
                    <option value="1" <?php echo ($event['state'] == 1 ? 'selected' : ''); ?>>Draft</option>
                    <option value="2" <?php echo ($event['state'] == 2 ? 'selected' : ''); ?>>Visible</option>
                </select>
            </div>
            
            <div class="form-group form-check mb-3">
                <input type="checkbox" class="form-check-input" name="allowComments" <?php echo ($event['allow_comments'] == 1 ? 'checked' : ''); ?> id="allowcomments">
                <label for="allowcomments" class="form-check-label">Allow Comments</label>
            </div>
            
            <hr>
            
            <div class="form-group mb-3">
                <label>Meta Title</label>
                <input type="text" class="form-control" name="metaTitle" value="<?php echo $event['meta_title']; ?>">
            </div>
            
            <div class="form-group mb-3">
                <label>Meta Description</label>
                <textarea type="textarea" class="form-control" name="metaDescription"><?php echo $event['meta_description']; ?></textarea>
            </div>
            
            <div class="form-group mb-3">
                <label>Meta Keywords</label>
                <input type="text" class="form-control" name="metaKeywords" value="<?php echo $event['meta_keywords']; ?>">
            </div>
            
            <div class="form-group mb-3">
                <label>Meta Author</label>
                <input type="text" class="form-control" name="metaAuthor" value="<?php echo $event['meta_author']; ?>">
            </div>
            
            <hr>
            
            <div class="form-group mb-3">
                <label>Featured Image</label>
                <input type="hidden" id="featuredImage" name="featuredImage" value="<?php echo $event['featured_image']; ?>">

                <?php if(!empty($event['featured_image'])) : ?>
                    <img src="<?php echo $event['featured_image']; ?>" class="d-block img-fluid">
                <?php endif; ?>
                
                <div class="buttons mt-3 mb-n1">
                    <a class="btn btn-secondary mb-1" data-fancybox="mediamanager" data-type="iframe" data-src="js/responsive_filemanager/filemanager/dialog.php?type=1&field_id=featuredImage">Select Image</a>
                    <input type="button" class="btn btn-dark mb-1" name="clearImage" value="Clear Image">
                </div>
            </div>
            
            <hr>
            
            <div class="form-group mb-n1">
                <input type="submit" class="btn btn-primary mb-1" name="saveContent" value="Save">
                <input type="button" class="btn btn-danger mb-1" name="deleteContent" data-id="<?php echo $event['id']; ?>" value="Delete">
            </div>
            
            <?php if(isset($message)) : ?>
                <div class="alert alert-<?php echo $status; ?> mb-0 mt-3">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col py-3">
            <div class="form-group mb-3">
                <label>Excerpt</label>
                <textarea class="form-control countChars" maxlength="500" name="excerpt"><?php echo $event['excerpt']; ?></textarea>
            </div>
            
            <div class="form-group mb-3">
                <label>Carousel</label>
                <?php echo carousel($_GET['id'], true); ?>
            </div>
            
            <div class="form-group mb-3">
                <label>Content</label>
                <textarea class="form-control tiny" name="content"><?php echo $event['content']; ?></textarea>
            </div>
        </div>
    </form>
<?php else : ?>
    <?php require_once(dirname(__FILE__) . '/includes/header.php'); ?>

	<div class="col-lg-3 bg-light py-3">		
		<form id="createEvent" method="post">
			<div class="form-group mb-3">
				<input type="submit" class="btn btn-primary" name="createEvent" value="Create Event">
			</div>
		</form>
		
		<h3>Search Events</h3>
		
		<form id="searchEvents">
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
		<h3>Current Events</h3>
		
		<?php 
			$search = (!empty($_GET['search']) ? '%' . $_GET['search'] . '%' : '%');
		
            $eventCount = $mysqli->prepare(
				"SELECT posts.* FROM `posts` AS posts
					LEFT OUTER JOIN `post_types` AS post_types ON post_types.id = posts.post_type_id
				WHERE post_types.name = ? AND (posts.name LIKE ? OR posts.id LIKE ? OR posts.author LIKE ? OR posts.excerpt LIKE ? OR posts.content LIKE ?) 
				ORDER BY date_created DESC"
			);
			$eventCount->bind_param('ssssss', $pt['name'], $search, $search, $search, $search, $search);
			$eventCount->execute();
			$eventCountResult = $eventCount->get_result();
        
            $pagination = new pagination($eventCountResult->num_rows);
			$pagination->load();
        
			$event = $mysqli->prepare(
				"SELECT posts.* FROM `posts` AS posts
					LEFT OUTER JOIN `post_types` AS post_types ON post_types.id = posts.post_type_id
				WHERE post_types.name = ? AND (posts.name LIKE ? OR posts.id LIKE ? OR posts.author LIKE ? OR posts.excerpt LIKE ? OR posts.content LIKE ?) 
				ORDER BY date_created DESC LIMIT {$pagination->itemsPerPage} OFFSET {$pagination->offset}"
			);
			$event->bind_param('ssssss', $pt['name'], $search, $search, $search, $search, $search);
			$event->execute();
			$eventResult = $event->get_result();			
		?>
		
		<?php if($eventResult->num_rows > 0) : ?>
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
						<?php while($row = $eventResult->fetch_assoc()) : ?>
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

<script>
	$("input[name='featuredImage']").change(function() {
		$(this).val($(this).val().split("\"")[0]);
		$(this).siblings("img").remove();
		
		if($(this).val() != "") {
			$("<img src='" + $(this).val() + "' class='d-block img-fluid'>").insertAfter($(this));
		}
	});
	
	$("input[name='clearImage']").click(function() {
		$(this).parents(".form-group").first().children("input[type='hidden']").val("").trigger("change");
	});
</script>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
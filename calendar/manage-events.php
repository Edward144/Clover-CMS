<?php
    require_once(dirname(__DIR__, 2) . '/database.php');
    require_once(dirname(__DIR__, 2) . '/functions.php');

    checkaccess(basename(__FILE__));
	$title = 'Manage Events';

    //Create Event
    if(isset($_POST['createEvent'])) {
        $unique = rtrim(base64_encode(date('Y-m-d H:i:s')), '=');
        $defaultContent = 
            '<h1>Enter your title</h1>
            <p>Enter your content...</p>';
        
        $create = $mysqli->prepare("INSERT INTO `events` (name, url, content, last_edited_by) VALUES('Event', ?, ?, ?)");
        $create->bind_param('ssi', $unique, $defaultContent, $_SESSION['adminid']);
        $ex = $create->execute();
        
        if($ex === false) {
            $status = 'danger';
            $message = 'Failed to create content';
        }
        else {
            $lastId = $mysqli->insert_id;
            $name = 'Event ' . $lastId;
            $url = str_replace(' ', '-', strtolower($name));
            
            $updateCreate = $mysqli->prepare("UPDATE `events` SET name = ?, url = ? WHERE id = ?");
            $updateCreate->bind_param('ssi', $name, $url, $lastId);
            $updateCreate->execute();
                
            header('Location: ' . explode('?', $_SERVER['REQUEST_URI'])[0] . '?id=' . $lastId);
            exit();
        }
    }

	//Delete Event
	if(isset($_POST['method']) && $_POST['method'] == 'deleteContent') {
		$delete = $mysqli->prepare("DELETE FROM `events` WHERE id = ?");
		$delete->bind_param('i', $_POST['id']);
		$delete->execute();
        
		if($delete->affected_rows > 0) {
			echo json_encode(['status' => 'success', 'message' => 'Successfully deleted event']);
		}
		else {
			echo json_encode(['status' => 'danger', 'message' => 'Failed to delete event']);
		}
        
		exit();
	}

    //Save Event
    if(isset($_POST['saveEvent'])) {
        $styles = [
            'background' => $_POST['backgroundStyle'],
            'text' => $_POST['textStyle']
        ];

        $save = $mysqli->prepare("UPDATE `events` SET name = ?, url = ?, template = ?, author = ?, start_date = ?, end_date = ?, state = ?, featured_image = ?, carousel = ?, excerpt = ?, content = ?, meta_title = ?, meta_description = ?, meta_keywords = ?, meta_author = ?, last_edited = NOW(), last_edited_by = ?, styles = ? WHERE id = ?");
        $save->bind_param('ssssssissssssssisi', $_POST['name'], $_POST['url'], $_POST['template'], $_POST['author'], $_POST['startDate'], $_POST['endDate'], $_POST['state'], $_POST['featuredImage'], $_POST['carousel'], $_POST['excerpt'], $_POST['content'], $_POST['metaTitle'], $_POST['metaDescription'], $_POST['metaKeywords'], $_POST['metaAuthor'], $_SESSION['adminid'], json_encode($styles), $_POST['id']);
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
        $carousel = carousel($_POST['carouselid'], true, $_POST['carouseldata'], 'events');
        echo json_encode($carousel);
        exit();
    }
?>

<?php if(isset($_GET['id'])) : ?>
    <?php 
        $eventCheck = $mysqli->prepare("SELECT * FROM `events` WHERE id = ?");
        $eventCheck->bind_param('i', $_GET['id']);
        $eventCheck->execute();
        $eventResult = $eventCheck->get_result();
        
        if($eventResult->num_rows <= 0) {
            http_response_code(404);
            header('Location: ' . explode('?', $_SERVER['REQUEST_URI'])[0]);
            exit();
        }

        $event = $eventResult->fetch_assoc();
        $eventBackgrounds = $mysqli->query("SELECT * FROM `event_styles` WHERE type = 'background' ORDER BY selector");
        $eventTexts = $mysqli->query("SELECT * FROM `event_styles` WHERE type = 'text' ORDER BY selector");

        $styles = json_decode($event['styles'], true);

        require_once(dirname(__DIR__, 3) . '/admin/includes/header.php'); 
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
                <label>Coordinator</label>
                <input type="text" class="form-control" name="author" value="<?php echo $event['author']; ?>">
            </div>
            
            <div class="form-group mb-3">
                <label>Start Date</label>
                <input type="datetime-local" class="form-control" name="startDate" value="<?php echo date('Y-m-d\TH:i', strtotime($event['start_date'])); ?>" required>
            </div>

            <div class="form-group mb-3">
                <label>End Date</label>
                <input type="datetime-local" class="form-control" name="endDate" value="<?php echo date('Y-m-d\TH:i', strtotime($event['end_date'])); ?>" required>
            </div>

            <?php if($eventBackgrounds->num_rows > 0) : ?>
                <div class="form-group mb-3">
                    <label>Calendar Background</label>
                    <select class="form-control" name="backgroundStyle" required>
                        <?php while($background = $eventBackgrounds->fetch_assoc()) : ?>
                            <option value="<?php echo $background['value']; ?>" <?php echo ($styles['background'] == $background['value'] ? 'selected' : ''); ?>><?php echo $background['selector']; ?>
                        <?php endwhile; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if($eventTexts->num_rows > 0) : ?>
                <div class="form-group mb-3">
                    <label>Calendar Text Colour</label>
                    <select class="form-control" name="textStyle" required>
                        <?php while($text = $eventTexts->fetch_assoc()) : ?>
                            <option value="<?php echo $text['value']; ?>" <?php echo ($styles['text'] == $text['value'] ? 'selected' : ''); ?>><?php echo $text['selector']; ?>
                        <?php endwhile; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="form-group mb-3">
                <label>Visiblity</label>
                <select class="form-control" name="state" required>
                    <option value="0" <?php echo ($event['state'] == 0 ? 'selected' : ''); ?>>Hidden</option>
                    <option value="1" <?php echo ($event['state'] == 1 ? 'selected' : ''); ?>>Draft</option>
                    <option value="2" <?php echo ($event['state'] == 2 ? 'selected' : ''); ?>>Visible</option>
                </select>
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
                    <a class="btn btn-secondary mb-1" data-fancybox="mediamanager" data-type="iframe" data-src="js/responsive_filemanager/filemanager/dialog.php?type=1&field_id=featuredImage&callback=responsive_filemanager_callback">Select Image</a>
                    <input type="button" class="btn btn-dark mb-1" name="clearImage" value="Clear Image">
                </div>
            </div>
            
            <hr>
            
            <div class="form-group mb-n1">
                <input type="submit" class="btn btn-primary mb-1" name="saveEvent" value="Save">
                <input type="button" class="btn btn-danger mb-1" name="deleteEvent" data-id="<?php echo $event['id']; ?>" value="Delete">
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
                <?php echo carousel($_GET['id'], true, '', 'events'); ?>
            </div>
            
            <div class="form-group mb-3">
                <label>Content</label>
                <textarea class="form-control tiny" name="content"><?php echo $event['content']; ?></textarea>
            </div>
        </div>
    </form>
<?php else : ?>
    <?php require_once(dirname(__DIR__, 3) . '/admin/includes/header.php'); ?>

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
		
            $eventCount = $mysqli->prepare("SELECT * FROM `events` WHERE name LIKE ? OR id LIKE ? OR author LIKE ? OR excerpt LIKE ? OR content LIKE ? ORDER BY start_date DESC");
			$eventCount->bind_param('sssss', $search, $search, $search, $search, $search);
			$eventCount->execute();
			$eventCountResult = $eventCount->get_result();
        
            $pagination = new pagination($eventCountResult->num_rows);
			$pagination->load();
        
			$event = $mysqli->prepare("SELECT * FROM `events` WHERE name LIKE ? OR id LIKE ? OR author LIKE ? OR excerpt LIKE ? OR content LIKE ? ORDER BY start_date DESC LIMIT {$pagination->itemsPerPage} OFFSET {$pagination->offset}");
			$event->bind_param('sssss', $search, $search, $search, $search, $search);
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
							<th class="shorten">Date</th>
							<th class="shorten">Status</th>
							<th class="shorten">Actions</th>
						</tr>
					</thead>
					
					<tbody>
						<?php while($row = $eventResult->fetch_assoc()) : ?>
							<tr>
								<th class="shorten" scope="row"><?php echo $row['id']; ?></th>
								
								<td>
									<span><strong><?php echo $row['name']; ?></strong></span><br>
                                    <?php echo (!empty($row['excerpt']) ? '<span>' . $row['excerpt'] . '</span><br>' : ''); ?>
									<small>URL: <?php echo (substr($row['url'], 0, 4) === 'http' ? $row['url'] : '/events/' . $row['url']); ?></small>
								</td>
								
								<td class="shorten">
									<?php echo date('d/m/Y H:i', strtotime($row['start_date'])); ?>

                                    <?php if(date('d/m/y H:i', strtotime($row['end_date'])) > date('d/m/y H:i', strtotime($row['start_date']))) : ?>
                                        to <br> <?php echo date('d/m/Y H:i', strtotime($row['end_date'])); ?>
                                    <?php endif; ?>
								</td>

                                <td class="shorten text-center">
                                    <span <?php echo ($row['state'] == 0 ? 'class="fas fa-eye-slash text-muted" title="Hidden"' : ($row['state'] == 1 ? 'class="fas fa-eye text-muted" title="Draft"' : 'class="fas fa-eye" title="Visible"')); ?>></span><br>
                                    <span class="fas fa-circle" title="Event color" style="text-shadow: 0 0 3px black; color: <?php echo json_decode($row['styles'], true)['background']; ?>"></span>
                                </td>
								
								<td class="shorten">
									<div class="form-group mb-n1">
										<?php echo editbutton($row['id']); ?>
										<input type="button" class="btn btn-danger mb-1" name="deleteEvent" data-id="<?php echo $row['id']; ?>" value="Delete">
									</div>
								</td>
							</tr>
						<?php endwhile; ?>
					</tbody>
				</table>
			</div>
        
            <?php echo $pagination->display(); ?>
		<?php else : ?>
			<div class="alert alert-info">No events could be found</div>
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

<?php require_once(dirname(__DIR__, 3) . '/admin/includes/footer.php'); ?>
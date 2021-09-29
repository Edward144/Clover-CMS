<?php
	require_once(dirname(__FILE__, 2) . '/includes/database.php');
    require_once(dirname(__FILE__, 2) . '/includes/functions.php');

    checkaccess(basename(__FILE__));
    $title = 'Manage Comments';
    
    require_once(dirname(__FILE__) . '/includes/header.php')
?>

<div class="col-lg-3 bg-light py-3">
		<h3>Search Comments</h3>
		
		<form id="searchComments">
			<div class="form-group mb-3">
				<div class="input-group">
					<input type="text" class="form-control" name="search" value="<?php echo $_GET['search']; ?>" required>
					
					<input type="submit" class="btn btn-primary" value="Search">
					
					<?php if(isset($_GET['search'])) : ?>
						<input type="button" class="btn btn-dark" name="clearSearch" value="Clear">
					<?php endif; ?>
				</div>
			</div>
		</form>
    
        <h3>Modifying Comments</h3>
    
        <p>You can edit any comment and then click the "Modify" button to change it, this is useful for censoring inappropriate comments.</p>
    
        <p>A record of the original comment will be stored and can be viewed by deleting the entire contents of a comment. Clicking "Modify" after erasing a comment will restore the original.</p>
    
        <h3>Approving Comments</h3>
        
        <p>You can change the "Approved" checkbox to change whether or not a comment is visible. After changing the checkbox click the "Modify" button.</p>
    
        <p>An unapproved comment will be highlighted in red.</p>
    
        <h3>Replies</h3>
    
        <p>If a comment is a reply then a "Reply to ID" will be displayed. You can click on this ID to view the comment being referenced.</p>
    
        <h3>IP Addresses</h3>
        
        <p>When a comment is posted the system will attempt to capture the user's IP address. This is not guarunteed to be accurate as this value can be obscured by the user.</p>
    
        <p>However if the IP address is captured accurately then it can be blocked. This is useful if you are seeing a lot of spam coming from the same user.</p>
    
        <p>You can click the IP address to view more information about it, such as county of origin.</p>
	</div>

	<div class="col py-3">
		<h3>Moderate Comments</h3>
		
		<?php 
            if(isset($_GET['reply'])) {
                $search = $_GET['id'];
            }
            else {
                $search = (!empty($_GET['search']) ? '%' . $_GET['search'] . '%' : '%');
            }
        
            $commentCount = $mysqli->prepare(
				"SELECT comments.*, CONCAT(users.first_name, ' ', users.last_name) AS registered_author, posts.name as post_name FROM `comments` AS comments
					LEFT OUTER JOIN `users` AS users ON users.id = comments.registered
                    LEFT OUTER JOIN `posts` AS posts ON posts.id = comments.post_id
				WHERE comments.id LIKE ? OR comments.content LIKE ? OR posts.name LIKE ? OR comments.author LIKE ? OR comments.ip_address = ?
				ORDER BY comments.date_posted DESC"
			);
			$commentCount->bind_param('sssss', $search, $search, $search, $search, $search);
			$commentCount->execute();
			$commentCountResult = $commentCount->get_result();
        
            $pagination = new pagination($commentCountResult->num_rows);
            $pagination->itemLimit = 5;
			$pagination->load();
        
			$comment = $mysqli->prepare(
				"SELECT comments.*, CONCAT(users.first_name, ' ', users.last_name) AS registered_author, posts.name as post_name FROM `comments` AS comments
					LEFT OUTER JOIN `users` AS users ON users.id = comments.registered
                    LEFT OUTER JOIN `posts` AS posts ON posts.id = comments.post_id
				WHERE comments.id LIKE ? OR comments.content LIKE ? OR posts.name LIKE ? OR comments.author LIKE ? OR comments.ip_address = ?
				ORDER BY comments.date_posted DESC LIMIT {$pagination->itemsPerPage} OFFSET {$pagination->offset}"
			);
			$comment->bind_param('sssss', $search, $search, $search, $search, $search);
			$comment->execute();
			$commentResult = $comment->get_result();			
		?>
		
		<?php if($commentResult->num_rows > 0) : ?>
			<div class="table-responsive">
				<table class="table table-striped">
					<thead class="table-dark">
						<tr>
							<th class="shorten">ID</th>
							<th>Comment</th>
							<th>Details</th>
							<th class="shorten">Date Posted</th>
							<th class="shorten">Actions</th>
						</tr>
					</thead>
					
					<tbody>
						<?php while($row = $commentResult->fetch_assoc()) : ?>
							<tr <?php echo ($row['approved'] == 0 ? 'class="alert-danger"' : ''); ?>>
								<th class="shorten" scope="row"><?php echo $row['id']; ?></th>
								
								<td>                                    
                                    <textarea class="form-control" name="comment" placeholder="Original comment: <?php echo (!empty($row['original_content']) ? $row['original_content'] : $row['content']); ?>"><?php echo $row['content']; ?></textarea>
                                    
									<?php if(!empty($row['post_name'])) : ?>
                                        <hr class="my-1">
                                    <small>Posted on: <strong><?php echo $row['post_name']; ?></strong></small>
                                    <?php endif; ?>
								</td>
                                
                                <td class="shorten">
                                    <span>Author: <strong><?php echo (!empty($row['registered_author']) ? $row['registered_author'] . ' <small>(Registered)</small>' : $row['author'] . ' <small>(Guest)</small>'); ?></strong></span><br>
                                    
                                    <?php echo (!empty($row['ip_address']) ? '<span>IP Address: <a href="https://api.iplocation.net/?ip=' . $row['ip_address'] . '" target="_blank"><strong>' . $row['ip_address'] . '</strong></a></span><br>' : '');?>
                                    
                                    <?php if($row['reply_to'] > 0) : ?>
                                        <span>Reply to ID: <a href="admin/manage-comments?search=<?php echo $row['reply_to']; ?>&reply"><strong><?php echo $row['reply_to']; ?></strong></a></span><br>
                                    <?php endif; ?>
                                    
                                    <span>Modified: <strong><span class="fa fa-<?php echo ($row['modified'] == 0 ? 'times-circle text-danger' : 'check-circle text-success'); ?>" title="Has this comment been modified by an administrative user?"></span></strong></span>
                                </td>
								
								<td class="shorten">
									<?php echo date('d/m/Y', strtotime($row['date_posted'])); ?><br>
									<?php echo date('H:i', strtotime($row['date_posted'])); ?>
								</td>
								
								<td class="shorten">
									<div class="form-group mb-n1">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="approved" id="approved<?php echo $row['id']; ?>" <?php echo ($row['approved'] == 1 ? 'checked' : ''); ?>>
                                            <label for="approved<?php echo $row['id']; ?>" class="form-check-label">Approved</label>
                                        </div>
                                        
										<input type="button" class="btn btn-primary mb-1" name="modifyComment" data-id="<?php echo $row['id']; ?>" value="Modify">
										<input type="button" class="btn btn-danger mb-1" name="deleteComment" data-id="<?php echo $row['id']; ?>" value="Delete">
									</div>
								</td>
							</tr>
						<?php endwhile; ?>
					</tbody>
				</table>
			</div>
        
            <?php echo $pagination->display(); ?>
		<?php else : ?>
			<div class="alert alert-info">No comments could be found</div>
		<?php endif; ?>
	</div>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
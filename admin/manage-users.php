<?php 
	$title = 'Manage Users';
	require_once(dirname(__FILE__) . '/includes/header.php'); 

	//Create User
	if(isset($_POST['createUser'])) {
		$c = $_POST;
		
		$password = validatepassword($_POST['password'], $_POST['passwordConf']);
		
		if(is_array($password)) {
			$status = $password['status'];
			$message = $password['message'];
		}
		else {
			$create = $mysqli->prepare("INSERT INTO `users` (first_name, last_name, username, email, password) VALUES(?, ?, ?, ?, ?)");
			$create->bind_param('sssss', $_POST['firstName'], $_POST['lastName'], $_POST['username'], $_POST['email'], $password);
			$create->execute();
			
			if($create->error) {
				$status = 'danger';
				
				if($create->errno == 1062) {					
					$message = duplicateerror($create->error);
				}
				else {
					$message = 'Failed to create user';
				}
			}
			else {
				$status = 'success';
				$message = 'User created successfully';
				unset($c);
			}
		}
	}

	//Edit User
	if(isset($_POST['saveUser'])) {
		$openModal = true;
		$messageId = $_POST['id'];
		
		if(!empty($_POST['password']) && !empty($_POST['passwordConf'])) {
			$password = validatepassword($_POST['password'], $_POST['passwordConf']);
			
			if(is_array($password)) {
				$status = $password['status'];
				$message = $password['message'];
			}
			else {
				$edit = $mysqli->prepare("UPDATE `users` SET first_name = ?, last_name = ?, username = ?, email = ?, password = ? WHERE id = ?");
				$edit->bind_param('sssssi', $_POST['firstName'], $_POST['lastName'], $_POST['username'], $_POST['email'], $password, $_POST['id']);
			}
		}
		else {
			$edit = $mysqli->prepare("UPDATE `users` SET first_name = ?, last_name = ?, username = ?, email = ? WHERE id = ?");
			$edit->bind_param('ssssi', $_POST['firstName'], $_POST['lastName'], $_POST['username'], $_POST['email'], $_POST['id']);
		}
		
		if(!isset($status)) {
			$edit->execute();
			
			if($edit->error) {
				$status = 'danger';
				
				if($edit->errno == 1062) {
					$editMessage = duplicateerror($edit->error);
				}
				else {
					$editMessage = 'Failed to update user';
				}
			}
			else {
				$status = 'success';
				$editMessage = 'User updated successfully';
			}
		}
	}

	//Delete User
	if(isset($_POST['deleteUser'])) {
		$delete = $mysqli->prepare("DELETE FROM `users` WHERE id = ? AND id <> ? AND (SELECT COUNT(*) FROM `users`) > 1");
		$delete->bind_param('ii', $_POST['id'], $_SESSION['adminid']);
		$delete->execute();
		
		if($delete->error) {
			$status = 'danger';
			$deleteMessage = 'Failed to delete user';
		}
	}
?>

<div class="col-lg-3 bg-light py-3">	
	<h3>Create New User</h3>
	
	<form id="createUser" method="post">		
		<div class="form-group mb-3">
			<label>First Name</label>
			<input type="text" class="form-control" name="firstName" value="<?php echo $c['firstName']; ?>" required>
		</div>
		
		<div class="form-group mb-3">
			<label>Last Name</label>
			<input type="text" class="form-control" name="lastName" value="<?php echo $c['lastName']; ?>">
		</div>
		
		<div class="form-group mb-3">
			<label>Username</label>
			<input type="text" class="form-control" name="username" value="<?php echo $c['username']; ?>" required>
		</div>
		
		<div class="form-group mb-3">
			<label>Email</label>
			<input type="email" class="form-control" name="email" value="<?php echo $c['email']; ?>" required>
		</div>
		
		<div class="form-group mb-3">
			<label>Password</label>
			<input type="password" class="form-control" name="password" value="<?php echo $c['password']; ?>" required>
		</div>
		
		<div class="form-group mb-3">
			<label>Confirm Password</label>
			<input type="password" class="form-control" name="passwordConf" value="<?php echo $c['passwordConf']; ?>" required>
		</div>
		
		<div class="form-group d-flex align-items-center mb-3">
			<input type="submit" class="btn btn-primary" name="createUser" value="Create User">
		</div>
		
		<?php if(isset($message)) : ?>
			<div class="alert alert-<?php echo $status; ?> mb-0">
				<?php echo $message; ?>
			</div>
		<?php endif; ?>
	</form>
</div>

<div class="col py-3">
	<h3>Existing Users</h3>
	
	<?php $users = $mysqli->query("SELECT * FROM `users`"); $i = 0; ?>
	
	<?php if($users->num_rows > 0) : ?>
		<div class="existingUsers row">
			<?php while($user = $users->fetch_assoc()) : ?>
				<div id="user<?php echo $user['id']; ?>" class="existingUser d-flex flex-column col-lg-6 col-xl-4 mb-3">
					<div class="existingUserHeader bg-primary text-white p-3">
						<span class="username h6"><?php echo $user['id'] . '. ' . $user['username']; ?></span>
					</div>
					
					<div class="existingUserBody d-flex flex-column flex-grow-1 border border-light shadow p-3">
						<p class="name font-weight-bold"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></p>
						<p class="email"><a href="mailto: <?php echo $user['email']; ?>"><?php echo $user['email']; ?></a></p>
						
						<div class="form-group mt-auto mb-n1">
							<input type="button" class="btn btn-primary mb-1" name="editUser" data-id="<?php echo $user['id']; ?>" data-bs-toggle="modal" data-bs-target="#modal<?php echo $user['id']; ?>" value="Edit User">

							<?php if($users->num_rows > 1 && $user['id'] != $_SESSION['adminid']) : ?>
								<form id="deleteUser" class="d-inline" method="post">
									<input type="hidden" name="id" value="<?php echo $user['id']; ?>">
									<input type="submit" class="btn btn-danger mb-1" data-confirm="Are you sure you want to delete this user?" name="deleteUser" value="Delete User">
									
									<?php if(isset($deleteMessage)) : ?>
										<div class="alert alert-<?php echo $status; ?> mb-0">
											<?php echo $deleteMessage; ?>
										</div>
									<?php endif; ?>
								</form>
							<?php endif; ?>
						</div>
					</div>
				</div>
			
				<div id="modal<?php echo $user['id']; ?>" class="modal fade" tabindex="-1">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title"><?php echo $user['id'] . '. ' . $user['username']; ?></h5>
        						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							
							<div class="modal-body">
								<form id="editUser" method="post">
									<input type="hidden" name="id" value="<?php echo $user['id']; ?>">
									
									<div class="form-group mb-3">
										<label>First Name</label>
										<input type="text" class="form-control" name="firstName" value="<?php echo $user['first_name']; ?>" required>
									</div>

									<div class="form-group mb-3">
										<label>Last Name</label>
										<input type="text" class="form-control" name="lastName" value="<?php echo $user['last_name']; ?>">
									</div>

									<div class="form-group mb-3">
										<label>Username</label>
										<input type="text" class="form-control" name="username" value="<?php echo $user['username']; ?>" required>
									</div>

									<div class="form-group mb-3">
										<label>Email</label>
										<input type="email" class="form-control" name="email" value="<?php echo $user['email']; ?>" required>
									</div>

									<div class="form-group mb-3">
										<label>Password</label>
										<input type="password" class="form-control" name="password" value="<?php echo $_POST['password']; ?>">
									</div>

									<div class="form-group mb-3">
										<label>Confirm Password</label>
										<input type="password" class="form-control" name="passwordConf" value="<?php echo $_POST['passwordConf']; ?>">
										<small class="text-muted">Leave both password fields if you do not wish to change the password</small>
									</div>

									<div class="form-group d-flex align-items-center mb-3">
										<input type="submit" class="btn btn-primary" name="saveUser" value="Save Changes">
									</div>
									
									<?php if(isset($editMessage) && isset($messageId) && $messageId == $user['id']) : ?>
										<div class="alert alert-<?php echo $status; ?>">
											<?php echo $editMessage; ?>
										</div>
									<?php endif; ?>
								</form>
							</div>
						</div>
					</div>
				</div>
			
				<?php $i++; ?>
			<?php endwhile; ?>
		</div>
	<?php else : ?>
		<div class="alert alert-danger">
			Unable to locate any users, this should not be possible. A new user may need to be created directly within the database in order to continue using the system.
		</div>
	<?php endif; ?>
</div>

<?php if($openModal == true) : ?>
	<script>
		$(document).ready(function() {
			$("#modal" + <?php echo $messageId; ?>).removeClass("fade").modal("show").addClass("fade");
		});
	</script>
<?php endif; ?>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
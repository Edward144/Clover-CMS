<?php 
	$title = 'Manage Users';
	require_once(dirname(__FILE__) . '/includes/header.php'); 
?>

<div class="col-lg-3 bg-light py-3">	
	<h3>Create New User</h3>
	
	<form id="createUser" action="" method="post">		
		<div class="form-group mb-3">
			<label>First Name</label>
			<input type="text" class="form-control" name="firstName" required>
		</div>
		
		<div class="form-group mb-3">
			<label>Last Name</label>
			<input type="text" class="form-control" name="lastName">
		</div>
		
		<div class="form-group mb-3">
			<label>Username</label>
			<input type="text" class="form-control" name="username" required>
		</div>
		
		<div class="form-group mb-3">
			<label>Email</label>
			<input type="email" class="form-control" name="email" required>
		</div>
		
		<div class="form-group mb-3">
			<label>Password</label>
			<input type="password" class="form-control" name="password" required>
		</div>
		
		<div class="form-group mb-3">
			<label>Confirm Password</label>
			<input type="password" class="form-control" name="passwordConf" required>
		</div>
		
		<div class="form-group d-flex align-items-center mb-3">
			<input type="submit" class="btn btn-primary" value="Create User">
		</div>
	</form>
</div>

<div class="col py-3">
	<h3>Existing Users</h3>
	
	<?php $users = $mysqli->query("SELECT * FROM `users`"); $i = 0; ?>
	
	<?php if($users->num_rows > 0) : ?>
		<div class="existingUsers row">
			<?php while($user = $users->fetch_assoc()) : ?>
				<div id="user<?php echo $user['id']; ?>" class="existingUser col-lg-6 col-xl-4 mb-3">
					<div class="existingUserHeader bg-primary text-white p-3">
						<span class="username h6"><?php echo $user['username']; ?></span>
					</div>
					
					<div class="existingUserBody border border-light shadow p-3">
						<p class="name font-weight-bold"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></p>
						<p class="email"><a href="mailto: <?php echo $user['email']; ?>"><?php echo $user['email']; ?></a></p>
						
						<div class="form-group mb-n1">
							<input type="button" class="btn btn-primary mb-1" name="editUser" data-id="<?php echo $user['id']; ?>" value="Edit User">

							<?php if($users->num_rows > -1 && $i > -1) : ?>
								<input type="button" class="btn btn-danger mb-1" name="deleteUser" value="Delete User">
							<?php endif; ?>
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

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
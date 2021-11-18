<?php 
	$title = 'Manage Users';
	require_once(dirname(__FILE__) . '/includes/header.php'); 
    
    checkaccess(basename(__FILE__));

    if(isset($_POST['username'])) {
        $_POST['username'] = strtolower(preg_replace('/[^a-zA-Z0-9]/i', '', $_POST['username']));
    }

	//Create User
	if(isset($_POST['createUser'])) {
		$c = $_POST;
		
		$password = validatepassword($_POST['password'], $_POST['passwordConf']);
		
		if(is_array($password)) {
			$status = $password['status'];
			$message = $password['message'];
		}
		else {
			$create = $mysqli->prepare("INSERT INTO `users` (first_name, last_name, username, email, password, role) VALUES(?, ?, ?, ?, ?, ?)");
			$create->bind_param('sssssi', $_POST['firstName'], $_POST['lastName'], $_POST['username'], $_POST['email'], $password, $_POST['role']);
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
		
        if(isset($_POST['role'])) {
            $role = $_POST['role'];
        }
        else {
            $currentRole = $mysqli->prepare("SELECT role FROM `users` WHERE id = ?");
            $currentRole->bind_param('i', $_POST['id']);
            $currentRole->execute();
            $roleResult = $currentRole->get_result();
            
            if($roleResult->num_rows > 0) {
                $role = $roleResult->fetch_array()[0];
            }
            else {
                $role = 1;
            }
        }
        
		if(!empty($_POST['password']) && !empty($_POST['passwordConf'])) {
			$password = validatepassword($_POST['password'], $_POST['passwordConf']);
			
			if(is_array($password)) {
				$status = $password['status'];
				$message = $password['message'];
			}
			else {
				$edit = $mysqli->prepare("UPDATE `users` SET first_name = ?, last_name = ?, username = ?, email = ?, password = ?, role = ? WHERE id = ?");
				$edit->bind_param('sssssii', $_POST['firstName'], $_POST['lastName'], $_POST['username'], $_POST['email'], $password, $role, $_POST['id']);
			}
		}
		else {
			$edit = $mysqli->prepare("UPDATE `users` SET first_name = ?, last_name = ?, username = ?, email = ?, role = ? WHERE id = ?");
			$edit->bind_param('ssssii', $_POST['firstName'], $_POST['lastName'], $_POST['username'], $_POST['email'], $role, $_POST['id']);
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
		$delete = $mysqli->prepare("DELETE FROM `users` WHERE id = ? AND id <> ? AND id <> 1");
		$delete->bind_param('ii', $_POST['id'], $_SESSION['adminid']);
		$delete->execute();
		
		if($delete->error) {
			$status = 'danger';
			$deleteMessage = 'Failed to delete user';
		}
	}

    //Create Role
    if(isset($_POST['createRole'])) {
        $createRole = $mysqli->prepare("INSERT INTO `roles` (name) VALUES(?)");
        $createRole->bind_param('s', ucwords($_POST['name']));
        $createRole->execute();
        
        if($createRole->error) {
            $status = 'danger';
            $roleMessage = 'Failed to create role, ensure the name is unique';
        }
        else {
            $status = 'success';
            $roleMessage = 'Role created successfully';
        }
    }

    //Delete Role
    if(isset($_POST['deleteRole'])) {
        $deleteRole = $mysqli->prepare("DELETE FROM `roles` WHERE id = ?");
        $deleteRole->bind_param('i', $_POST['id']);
        $deleteRole->execute();
        
        if($deleteRole->error) {
            $status = 'danger';
            $deleteroleMessage = 'Failed to delete role';
        }
        else {
            $updateRoles = $mysqli->prepare("UPDATE `users` SET role = 2 WHERE role = ?");
            $updateRoles->bind_param('i', $_POST['id']);
            $updateRoles->execute();
            
            if($updateRoles->error) {
                $status = 'warning';
                $deleteroleMessage = 'Role deleted successfully, but failed to update users, you must manually update users to a new role';
            }
            else {
                $status = 'success';
                $deleteroleMessage = 'Deleted role and updated users';
            }
        }
    }

    //Edit Role
    if(isset($_POST['saveRole'])) {
        $openRoleModal = true;
		$messageId = $_POST['id'];
        
        $access = json_encode($_POST['access']);
        $access = ($access === 'null' ? null : $access);
        
        $updateRole = $mysqli->prepare("UPDATE `roles` SET access = ? WHERE id = ?");
        $updateRole->bind_param('si', $access, $_POST['id']);
        $updateRole->execute();
        
        if($updateRole->error) {
            $status = 'danger';
            $editroleMessage = 'Failed to save changes';
        }
        else {
            $status = 'success';
            $editroleMessage = 'Changes saved successfully';
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
        
        <div class="form-group mb-3">
            <label>Role</label>
            <select class="form-control" name="role" required>
                <?php $roles = $mysqli->query("SELECT * FROM `roles` ORDER BY id ASC"); ?>
                
                <?php if($roles->num_rows > 0) : ?>
                    <?php while($role = $roles->fetch_assoc()) : ?>
                        <option value="<?php echo $role['id']; ?>" <?php echo ($c['role'] == $role['id'] ? 'selected' : ($role['id'] == 1 ? 'selected' : '')); ?>><?php echo $role['name']; ?></option>
                    <?php endwhile; ?>
                <?php endif; ?>
                
                <option value="-1"><?php echo BASIC_USER; ?></option>
            </select>
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
    
    <hr>
    
    <h3>Roles</h3>
    <p>Create a new role to customize page access.</p>
    
    <form id="createRole" method="post">
        <div class="form-group mb-3">
            <label>Role Name</label>
            <input type="text" class="form-control" name="name" required>
            <small class="text-muted">This name must be unique</small>
        </div>
        
        <div class="form-group d-flex align-items-center mb-3">
			<input type="submit" class="btn btn-primary" name="createRole" value="Create Role">
		</div>
        
        <?php if(isset($roleMessage)) : ?>
            <div class="alert alert-<?php echo $status; ?>">
                <?php echo $roleMessage; ?>
            </div>
        <?php endif; ?>
    </form>
    
    <?php $roles = $mysqli->query("SELECT * FROM `roles` WHERE id > 1 ORDER BY id ASC"); ?>
    
    <?php if($roles->num_rows > 0) : ?>
        <hr>
    
        <ul class="list-group rolesList">
            <?php while($role = $roles->fetch_assoc()) : ?>
                <?php 
                    $access = json_decode($role['access'], true); 
                    $access = ($access == null ? [] : $access);
                ?>    
            
                <li class="list-group-item">
                    <form class="deleteRole d-block d-xl-flex align-items-start justify-content-end mb-n1" method="post">
                        <span class="ms-0 me-auto"><?php echo $role['name']; ?></span>
                        
                        <?php if($role['id'] > 1) : ?>
                            <input type="button" class="btn btn-primary py-0 ms-1 mb-1" data-bs-toggle="modal" data-bs-target="#role<?php echo $role['id']; ?>modal" value="Edit">
                        <?php endif; ?>
                        
                        <?php if($role['id'] > 2) : ?>
                            <input type="hidden" name="id" value="<?php echo $role['id']; ?>">
                            <input type="submit" class="btn btn-danger py-0 me-0 ms-1 mb-1" name="deleteRole" value="Delete">
                        <?php endif; ?>
                    </form>
                    
                    <?php if($role['id'] > 0) : ?>
                        <div id="role<?php echo $role['id']; ?>modal" class="modal fade" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Role <?php echo $role['id'] . '. ' . $role['name']; ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <form class="editRole" method="post">
                                            <input type="hidden" name="id" value="<?php echo $role['id']; ?>">

                                            <div class="form-group mb-3">
                                                <label>Page Access</label>
                                                <select class="form-control" name="access[]" multiple>
                                                    <?php 
                                                        $pages = array_filter(glob(dirname(__FILE__) . '/*'), 'is_file'); 
                                                        $postTypes = $mysqli->query("SELECT * FROM `post_types`");
                                                    ?>

                                                    <?php if($postTypes->num_rows > 0) : ?>
                                                        <?php while($postType = $postTypes->fetch_assoc()) : ?>
                                                            <?php $postTypeName = 'posttype_' . $postType['name']; var_dump($access) ?>
                                                    
                                                            <option value="<?php echo $postTypeName; ?>" <?php echo (in_array($postTypeName, $access) ? 'selected' : ''); ?>>Manage Content: <?php echo ucwords(str_replace('-', ' ', $postType['name'])); ?></option>
                                                        <?php endwhile; ?>
                                                    <?php endif; ?>
                                                    
                                                    <?php if(!empty($pages)) : ?>
                                                        <?php foreach($pages as $page) : ?>
                                                            <?php $basename = pathinfo($page)['basename']; ?>
                                                    
                                                            <?php if(!in_array($basename, ALLOWED_PAGES)) : ?>
                                                                <option value="<?php echo $basename; ?>"<?php echo (in_array($basename, $access) ? 'selected' : ''); ?>><?php echo ucwords(str_replace('-', ' ', pathinfo($page)['filename'])); ?></option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>

                                                <small class="text-muted">
                                                    Click and drag, or CTRL/CMD + click to select more than one item. CTRL/CMD + click to deselect items.
                                                </small>
                                            </div>

                                            <div class="form-group d-flex align-items-center mb-3">
                                                <input type="submit" class="btn btn-primary" name="saveRole" value="Save Changes">
                                            </div>

                                            <?php if(isset($editroleMessage) && isset($messageId) && $messageId == $role['id']) : ?>
                                                <div class="alert alert-<?php echo $status; ?>">
                                                    <?php echo $editroleMessage; ?>
                                                </div>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endwhile; ?>
        </ul>
    
        <?php if(isset($deleteroleMessage)) : ?>
            <div class="alert alert-<?php echo $status; ?> mt-3">
                <?php echo $deleteroleMessage; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="col py-3">
	<h3>Existing Users</h3>
	
	<?php 
        $users = $mysqli->query(
            "SELECT users.*, roles.name as role_name FROM `users` AS users
            LEFT OUTER JOIN `roles` AS roles ON roles.id = users.role"
        ); 
    
        $i = 0; 
    ?>
	
	<?php if($users->num_rows > 0) : ?>
		<div class="existingUsers row">
			<?php while($user = $users->fetch_assoc()) : ?>
				<div id="user<?php echo $user['id']; ?>" class="existingUser d-flex flex-column col-lg-6 col-xl-4 mb-3">
					<div class="existingUserHeader <?php echo ($user['role'] == -1 ? 'bg-dark' : 'bg-primary'); ?> text-white p-3">
						<span class="username h6"><?php echo $user['id'] . '. ' . $user['username']; ?><small> (Role: <?php echo (!empty($user['role_name']) ? $user['role_name'] : BASIC_USER); ?>)</small></span>
					</div>
					
					<div class="existingUserBody d-flex flex-column flex-grow-1 border border-light shadow p-3">
						<p class="name font-weight-bold mb-0"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></p>
						<p class="email"><a href="mailto: <?php echo $user['email']; ?>"><?php echo $user['email']; ?></a></p>
						
						<div class="form-group mt-auto mb-n1">
							<input type="button" class="btn btn-primary mb-1" name="editUser" data-id="<?php echo $user['id']; ?>" data-bs-toggle="modal" data-bs-target="#modal<?php echo $user['id']; ?>" value="Edit User">

							<?php if($users->num_rows > 1 && $i > 0 && $user['id'] != $_SESSION['adminid']) : ?>
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
								<form class="editUser" method="post">
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
                                    
                                    <?php if($user['id'] != $_SESSION['adminid']) : ?>
                                        <div class="form-group mb-3">
                                            <label>Role</label>
                                            <select class="form-control" name="role" required>
                                                <?php $roles = $mysqli->query("SELECT * FROM `roles` ORDER BY id ASC"); ?>

                                                <?php if($roles->num_rows > 0) : ?>
                                                    <?php while($role = $roles->fetch_assoc()) : ?>
                                                        <option value="<?php echo $role['id']; ?>" <?php echo ($user['role'] == $role['id'] ? 'selected' : ''); ?>><?php echo $role['name']; ?></option>
                                                    <?php endwhile; ?>
                                                <?php endif; ?>
                                                
                                                <option value="-1" <?php echo ($user['role'] == -1 ? 'selected' : ''); ?>><?php echo BASIC_USER; ?></option>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                    
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
<?php elseif($openRoleModal == true) : ?>
	<script>
		$(document).ready(function() {
			$("#role" + <?php echo $messageId; ?> + "modal").removeClass("fade").modal("show").addClass("fade");
		});
	</script>
<?php endif; ?>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
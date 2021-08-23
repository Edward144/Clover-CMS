<?php 
	$title = 'My Profile';
	require_once(dirname(__FILE__) . '/includes/header.php'); 

    $getProfile = $mysqli->prepare(
        "SELECT users.*, roles.name AS role_name FROM `users` AS users
            LEFT OUTER JOIN `roles` AS roles ON roles.id = users.role
        WHERE users.id = ?");
    $getProfile->bind_param('i', $_SESSION['adminid']);
    $getProfile->execute();
    $profileResult = $getProfile->get_result();

    if($profileResult->num_rows > 0) {
        $profile = $profileResult->fetch_assoc();
    }
    else {
        $profile = [];
    }

    //Update Profile
    if(isset($_POST['saveProfile'])) {
        $allowUpdate = true;
        
        //Check if email exists
        $checkEmail = $mysqli->prepare("SELECT COUNT(*) FROM `users` WHERE email = ? AND id <> ?");
        $checkEmail->bind_param('si', $_POST['email'], $_SESSION['adminid']);
        $checkEmail->execute();
        $emailCount = $checkEmail->get_result()->fetch_array()[0];
        
        if($emailCount > 0) {
            $allowUpdate = false;
            $status = 'danger';
            $message = 'This email is already in use';
        }
        
        //Check if username exists
        $checkUsername = $mysqli->prepare("SELECT COUNT(*) FROM `users` WHERE username = ? AND id <> ?");
        $checkUsername->bind_param('si', $_POST['username'], $_SESSION['adminid']);
        $checkUsername->execute();
        $usernameCount = $checkUsername->get_result()->fetch_array()[0];
        
        if($usernameCount > 0) {
            $allowUpdate = false;
            $status = 'danger';
            $message = 'This username is already in use';
        }
        
        if($allowUpdate == true) {
            if(!empty($_POST['password']) && $_POST['password'] == $_POST['passwordConf']) {
                $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
                
                $update = $mysqli->prepare("UPDATE `users` SET first_name = ?, last_name = ?, email = ?, username = ?, password = ? WHERE id = ?");
                $update->bind_param('sssssi', $_POST['firstName'], $_POST['lastName'], $_POST['email'], $_POST['username'], $password, $_SESSION['adminid']);
            }
            else {
                $update = $mysqli->prepare("UPDATE `users` SET first_name = ?, last_name = ?, email = ?, username = ? WHERE id = ?");
                $update->bind_param('ssssi', $_POST['firstName'], $_POST['lastName'], $_POST['email'], $_POST['username'], $_SESSION['adminid']);
            }

            $update->execute();

            if($update->error) {
                $status = 'danger';
                $message = 'Failed to update profile';
            }
            else {
                $status = 'success';
                $message = 'Successfully updated profile';
            }
        }
    }
?>

<div class="col py-3">
    <form id="manageProfile" method="post">
        <div class="form-group mb-3">
            <label>First Name</label>
            <input type="text" class="form-control" name="firstName" value="<?php echo (!empty($_POST['firstName']) ? $_POST['firstName'] : $profile['first_name']); ?>" required>
        </div>
        
        <div class="form-group mb-3">
            <label>Last Name</label>
            <input type="text" class="form-control" name="lastName" value="<?php echo (!empty($_POST['lastName']) ? $_POST['lastName'] : $profile['last_name']); ?>" required>
        </div>
        
        <div class="form-group mb-3">
            <label>Email</label>
            <input type="email" class="form-control" name="email" value="<?php echo (!empty($_POST['email']) ? $_POST['email'] : $profile['email']); ?>" required>
        </div>
        
        <div class="form-group mb-3">
            <label>Username</label>
            <input type="text" class="form-control" name="username" value="<?php echo (!empty($_POST['username']) ? $_POST['username'] : $profile['username']); ?>" required>
        </div>
        
        <div class="form-group mb-3">
            <label>Password</label>
            <input type="password" class="form-control" name="password">
        </div>
        
        <div class="form-group mb-3">
            <label>Confirm Password</label>
            <input type="password" class="form-control" name="passwordConf">
            <small class="text-muted">Leave both password fields if you do not wish to change the password</small>
        </div>
        
        <div class="form-group mb-3">
            <label>Role</label>
            <input type="text" class="form-control" value="<?php echo $profile['role_name']; ?>" disabled>
        </div>
        
        <div class="form-group d-flex align-items-center mb-3">
			<input type="submit" class="btn btn-primary" name="saveProfile" value="Save Profile">
		</div>
		
		<?php if(isset($message)) : ?>
			<div class="alert alert-<?php echo $status; ?> mb-0">
				<?php echo $message; ?>
			</div>
		<?php endif; ?>
    </form>
</div>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
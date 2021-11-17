<?php 
    
    require_once(dirname(__FILE__) . '/includes/database.php'); 
	require_once(dirname(__FILE__) . '/includes/functions.php');

    if(issignedin()) {
        header('Location: myaccount');
    }

    $title = 'Sign In';

    if(isset($_POST['signin'])) {
		$checkUser = $mysqli->prepare("SELECT id, username, password, role FROM `users` WHERE username = ?");
		$checkUser->bind_param('s', $_POST['username']);
		$checkUser->execute();
		$userResult = $checkUser->get_result();
		
		if($userResult->num_rows > 0) {
			$user = $userResult->fetch_assoc();
			
			if(password_verify($_POST['password'], $user['password'])) {
				$failed = false;
				
				$_SESSION['profileid'] = $user['id'];
				$_SESSION['profileuser'] = $user['username'];
                
                if($user['role'] > 0) {
                    $_SESSION['adminid'] = $_SESSION['profileid'];
                    $_SESSION['adminuser'] = $_SESSION['profileuser'];
                }
                
                header('Location: myaccount');
				exit();
			}
			else {
				$failed = true;
			}
		}
		else {
			$failed = true;
		}
		
		if($failed == true) {
			$status = 'danger';
			$loginmessage = 'Username or password is incorrect';
		}
	}

    require_once(dirname(__FILE__) . '/includes/header.php'); 

?>

<div class="content">
    <div class="col-md-6 mx-auto my-3">
        <div class="bg-light rounded shadow-sm p-3">
            <form id="profilesignin" method="post">
                <h1>Sign in to your account</h1>

                <div class="form-group mb-3">
                    <label>Username</label>
                    <input type="text" class="form-control" name="username" value="<?php echo $_POST['username']; ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label>Password</label>
                    <input type="password" class="form-control" name="password" value="<?php echo $_POST['password']; ?>" required>
                </div>

                <div class="form-group d-flex align-items-end justify-content-end">
                    <input type="submit" class="btn btn-primary text-light" name="signin" value="Sign In">
                </div>
                
                <?php if(isset($loginmessage)) : ?>
                    <div class="alert alert-<?php echo $status; ?> mb-0 mt-3">
                        <?php echo $loginmessage; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <p class="my-3"><a href="signup">Don't have an account? Sign up here</a></p>
    </div>
</div>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
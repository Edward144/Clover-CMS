<?php 
    
    require_once(dirname(__FILE__) . '/includes/database.php'); 
	require_once(dirname(__FILE__) . '/includes/functions.php');

    if(issignedin()) {
        header('Location: myaccount');
    }

    $title = 'Sign In';

    //Sign user in
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

    //Request password reset link
    if(isset($_POST['requestreset'])) {
        $error = false;
        
        $checkEmail = $mysqli->prepare("SELECT * FROM `users` WHERE email = ? AND role = -1");
        $checkEmail->bind_param('s', $_POST['email']);
        $checkEmail->execute();
        $emailResult = $checkEmail->get_result();
        
        if($emailResult->num_rows > 0) {
            $user = $emailResult->fetch_assoc();
            $token = randomstring(191);
			
			$genToken = $mysqli->prepare("INSERT INTO `password_reset` (email, token) VALUES(?, ?)");
			$genToken->bind_param('ss', $user['email'], $token);
			$genToken->execute();
            
            $to = $user['email'];
			$subject = 'Password reset | ' . $_SERVER['SERVER_NAME'];
			$content = 
				'<p>Hi ' . $user['first_name'] . ' ' . $user['last_name'] . ' <strong>(' . $user['username'] . ')</strong>,</p>
				
				<p>You have requested to reset your password, click the link below to do so.
				<br><small>(This link will expire after 24 hours)</small></p>
				
				<p><a style="background: #009688; color: #fff; padding: 0.5rem; border-radius: 4px; text-decoration: none;" href="https://' . $_SERVER['SERVER_NAME'] . ROOT_DIR . 'signin?resetpassword&token=' . $token . '" target="_blank">Reset my password</a></p>';
			
			if(!sendemail($to, $subject, $content)) {
				$error = true;
			}
        }
        else {
            $error = true;
        }
        
        if($error == false) {
            $status = 'info';
            $loginmessage = 'If the supplied email exists within our records, then an email will be sent to it';
        }
        else {
            $status = 'danger';
            $loginmessage = 'An unexpected error has occurred, please try again later';
        }
    }

    //Reset users password
    if(isset($_GET['resetpassword'])) {
        if(!isset($_GET['token'])) {
            http_response_code(404);
            header('Location: signin');
            exit();
        }
        
        $date = date('Y-m-d H:i:s', strtotime('-1 day'));
	
        $checkToken = $mysqli->prepare("SELECT * FROM `password_reset` WHERE token = ? AND date_generated >= ? AND expired = 0 ORDER BY id DESC LIMIT 1");
        $checkToken->bind_param('ss', $_GET['token'], $date);
        $checkToken->execute();
        $tokenResult = $checkToken->get_result();

        if($tokenResult->num_rows <= 0) {
            $expired = true;
        }
        else {
            $expired = false;

            if(isset($_POST['generate'])) {
                $generated = randomstring();

                echo json_encode([
                    'status' => 'info',
                    'message' => 'Generated password: <strong>' . $generated .'</strong>',
                    'password' => $generated
                ]);
                exit();
            }
        }

        $expireTokens = $mysqli->prepare("UPDATE `password_reset` SET expired = 1 WHERE token = ? OR date_generated < ?");
        $expireTokens->bind_param('ss', $_GET['token'], $date);
        $expireTokens->execute();
    }

    //Update Password
	if(isset($_POST['resetpassword'])) {
		$getEmail = $mysqli->prepare("SELECT email FROM `password_reset` WHERE token = ? ORDER BY id DESC LIMIT 1");
		$getEmail->bind_param('s', $_GET['token']);
		$getEmail->execute();
		$emailResult = $getEmail->get_result();
		
		if($emailResult->num_rows > 0) {
			$email = $emailResult->fetch_array()[0];
			$password = password_hash($_POST['password'], PASSWORD_BCRYPT);
			
			$updatePass = $mysqli->prepare("UPDATE `users` SET password = ? WHERE email = ?");
			$updatePass->bind_param('ss', $password, $email);
			$ex = $updatePass->execute();
			
			if($ex === false) {
				$status = 'danger';
				$loginmessage = 'Failed to reset password, you can <a href="signin?forgottenpassword">request a new reset link</a> and try again';
			}
			else {
				$status = 'success';
				$loginmessage = 'Password reset successfully, <a href="signin">you can now return to login</a>';
			}
		}
	}

    require_once(dirname(__FILE__) . '/includes/header.php'); 

?>

<div class="content">
    <div class="col-lg-6 mx-auto my-3">
        <div class="bg-light rounded shadow-sm p-3">
            <?php if(isset($_GET['forgottenpassword'])) : ?>
                <form id="profilerequest" method="post">
                    <h1>Request a password reset</h1>
                    
                    <p>Enter your email below, if it exists within our records then an email will be sent to you containing your username and a link to reset your password if required.</p>
                    
                    <div class="form-group mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" value="<?php echo $_POST['email']; ?>" required autofocus>
                    </div>
                    
                    <div class="form-group d-flex align-items-end justify-content-end">
                        <input type="submit" class="btn btn-primary text-light" name="requestreset" value="Submit Request">
                    </div>

                    <?php if(isset($loginmessage)) : ?>
                        <div class="alert alert-<?php echo $status; ?> mb-0 mt-3">
                            <?php echo $loginmessage; ?>
                        </div>
                    <?php endif; ?>
                </form>
            <?php elseif(isset($_GET['resetpassword'])) : ?>
                <form id="profilereset" method="post">
                    <?php if($expired == true) : ?>
                        <?php if(empty($loginmessage)) : ?>
                            <p class="mb-0">This link has expired, <a href="signin" class="ms-auto me-0">return to the login screen</a> and use the reset password option again.</p>
                        <?php else : ?>
                            <div class="alert alert-<?php echo $status; ?> mb-0">
                                <?php echo $loginmessage; ?>	
                            </div>
                        <?php endif; ?>
                    <?php else : ?>
                        <h1>Set a new password</h1>
                    
                        <div class="form-group mb-3">
                            <label>Password</label>
                            <input type="password" class="form-control" name="password" value="<?php echo $_POST['password']; ?>" required autofocus>
                        </div>
                    
                        <div class="form-group mb-3">
                            <label>Confirm Password</label>
                            <input type="password" class="form-control" name="passwordConf" value="<?php echo $_POST['passwordConf']; ?>" required>
                        </div>

                        <div class="form-group d-flex align-items-end justify-content-end">
                            <input type="submit" class="btn btn-primary text-light" name="resetpassword" value="Reset Password">
                        </div>

                        <?php if(isset($loginmessage)) : ?>
                            <div class="alert alert-<?php echo $status; ?> mb-0 mt-3">
                                <?php echo $loginmessage; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </form>
            <?php else : ?>
                <form id="profilesignin" method="post">
                    <h1>Sign in to your account</h1>

                    <div class="form-group mb-3">
                        <label>Username</label>
                        <input type="text" class="form-control" name="username" value="<?php echo $_POST['username']; ?>" required autofocus>
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
            <?php endif; ?>
        </div>

        <p class="my-3">
            <small>
                <a href="signup" class="me-3">Don't have an account? Sign up here</a><wbr>
                
                <?php if(isset($_GET['forgottenpassword'])) : ?>
                    <a href="signin">Return to sign in</a>
                <?php else : ?>
                    <a href="signin?forgottenpassword">I have forgotten my username or password</a>
                <?php endif; ?>
            </small>
        </p>
    </div>
</div>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
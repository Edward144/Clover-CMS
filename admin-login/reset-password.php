<?php 
	require_once(dirname(__DIR__) . '/includes/database.php');
	require_once(dirname(__DIR__) . '/includes/functions.php');

	if(isset($_SESSION['adminid'])) {
		header('Location: ../admin');
		exit();
	}

	//Check token
	if(!isset($_GET['token'])) {
		http_response_code(404);
		header('Location: ./');
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
		elseif(isset($_POST['doreset'])) {

		}
	}

	$expireTokens = $mysqli->prepare("UPDATE `password_reset` SET expired = 1 WHERE token = ? OR date_generated < ?");
	$expireTokens->bind_param('ss', $_GET['token'], $date);
	$expireTokens->execute();

	//Update Password
	if(isset($_POST['doreset'])) {
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
				$resetmessage = 'Failed to reset password, you can <a href="admin-login/forgotten-password">request a new reset link</a> and try again';
			}
			else {
				$status = 'success';
				$resetmessage = 'Password reset successfully, <a href="admin-login">you can now return to login</a>';
			}
		}
	}
?>

<!DOCTYPE html>

<html lang="en">
	<head>
		<base href="<?php echo BASE_DIR; ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<title>Admin Login | Reset Your Password</title>
		
		<link rel="preconnect" href="https://fonts.gstatic.com">
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
		<link rel="stylesheet" href="css/adminStyle.min.css">
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
		<script src="bootstrap-5.0.1/bootstrap.min.js"></script>
		<script src="https://kit.fontawesome.com/a05d626b05.js" crossorigin="anonymous"></script>
	</head>
	
	<body>
		<div class="wrapper d-flex flex-column align-items-stretch" id="adminLoginPage">
			<header id="pageHeader" class="container-fluid bg-white border-bottom border-light shadow py-3">
				<a href="./" class="d-inline-block text-primary">Return to site</a>
			</header>
			
			<div class="main container-fluid flex-column flex-grow-1">				
				<div class="content">
					<form id="adminLogin" class="reset shadow rounded bg-white overflow-hidden mx-auto my-5" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
						<input type="hidden" name="doreset">
						
						<div class="formHeader text-center bg-primary text-white p-3">
							<h1><span class="fa fa-unlock-alt me-3"></span>Admin Login</h1>
							<h3 class="mb-0">Reset Password</h3>
						</div>

						<div class="formBody border-right border-left border-right border-light p-3">
							<?php if($expired == true) : ?>
								<?php if(empty($resetmessage)) : ?>
									<p class="mb-0">This link has expired, <a href="admin-login" class="ms-auto me-0">return to the login screen</a> and use the reset password option again.</p>
								<?php else : ?>
									<div class="alert alert-<?php echo $status; ?> mb-0">
										<?php echo $resetmessage; ?>	
									</div>
								<?php endif; ?>
							<?php else : ?>
								<p>Create your new password. Passwords must be at least 12 characters and contain at least one uppercase, one lowercase and one digit. You can also generate a random password.</p>

								<div class="mb-3">
									<label>Password</label>

									<div class="input-group">
										<span class="input-group-text"><span class="fa fa-key"></span></span>
										<input type="password" class="form-control" name="password" value="<?php echo $_POST['password']; ?>" required autofocus>
									</div>
								</div>

								<div class="mb-3">
									<label>Confirm Password</label>

									<div class="input-group">
										<span class="input-group-text"><span class="fa fa-key"></span></span>
										<input type="password" class="form-control" name="passwordConf" value="<?php echo $_POST['passwordConf']; ?>" required>
									</div>
								</div>

								<div class="d-block d-sm-flex align-items-center mb-n3">
									<input type="submit" class="btn btn-primary me-3 mb-3" value="Update Password">
									<input type="button" class="btn btn-secondary me-3 mb-3" name="generate" value="Generate Password">

									<a href="admin-login" class="d-block ms-auto me-0">Return to login</a>
								</div>

								<?php if(isset($loginmessage)) : ?>
									<div class="alert alert-<?php echo  $status; ?> mb-0 mt-3">
										<?php echo $loginmessage; ?>
									</div>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					</form>
				</div>
			</div>
			
			<footer id="pageFooter" class="container-fluid bg-white border-top border-light shadow text-end">
				<a href="https://github.com/Edward144/Clover-CMS" target="_blank" class="d-inline-block text-primary"><small>Powered by Clover CMS</small></a>
			</footer>
		</div>
	</body>
	
	<script src="js/adminLogin.min.js"></script>
</html>
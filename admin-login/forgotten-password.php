<?php 
	require_once(dirname(__DIR__) . '/includes/database.php'); 
	require_once(dirname(__DIR__) . '/includes/functions.php'); 

	if(isset($_SESSION['adminid'])) {
		header('Location: ../admin');
		exit();
	}

	if(isset($_POST['sendreset'])) {
		$findUser = $mysqli->prepare("SELECT id, first_name, last_name, email FROM `users` WHERE email = ? LIMIT 1");
		$findUser->bind_param('s', $_POST['email']);
		$findUser->execute();
		$userResult = $findUser->get_result();
		
		if($userResult->num_rows > 0) {
			$user = $userResult->fetch_assoc();
			$token = randomstring(191);
			
			$genToken = $mysqli->prepare("INSERT INTO `password_reset` (email, token) VALUES(?, ?)");
			$genToken->bind_param('ss', $user['email'], $token);
			$genToken->execute();
			
			$to = $user['email'];
			$subject = 'Password reset | ' . $_SERVER['SERVER_NAME'];
			$content = 
				'<p>Hi ' . $user['first_name'] . ' ' . $user['last_name'] . '</p>
				
				<p>You have requested to reset your password, click the link below to do so.
				<br><small>(This link will expire after 24 hours)</small></p>
				
				<p><a style="background: #009688; color: #fff; padding: 0.5rem; border-radius: 4px; text-decoration: none;" href="https://' . $_SERVER['SERVER_NAME'] . ROOT_DIR . 'admin-login/reset-password?token=' . $token . '" target="_blank">Reset my password</a></p>';
			
			if(!systememail($to, $subject, $content)) {
				$error = true;
				$status = 'danger';
				$loginmessage = 'An unexpected error has occurred';
			}
		}
		
		if(!isset($error)) {
			$status = 'info';
			$loginmessage = 'A reset link will be sent to this email address if it exists';
		}
	}
?>

<!DOCTYPE html>

<html>
	<head>
		<base href="<?php echo BASE_DIR; ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<title>Admin Login | Forgotten Password</title>
		
		<link rel="preconnect" href="https://fonts.gstatic.com">
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Roboto&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
		<link rel="stylesheet" href="css/style.min.css">
		
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
			
			<div class="main container-fluid flex-grow-1">				
				<div class="content">
					<form id="adminLogin" class="shadow rounded bg-white overflow-hidden mx-auto my-5" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
						<input type="hidden" name="sendreset">
						
						<div class="formHeader text-center bg-primary text-white p-3">
							<h1><span class="fa fa-unlock-alt me-3"></span>Admin Login</h1>
							<h3 class="mb-0">Forgotten Password</h3>
						</div>

						<div class="formBody border-right border-left border-right border-light p-3">
							<p>If you have forgotten your password then enter your email into the field below. If the supplied email exists on the system then a reset link will be sent to it.</p>
							
							<div class="mb-3">
								<label>Email</label>
								
								<div class="input-group">
									<span class="input-group-text"><span class="fa fa-envelope"></span></span>
									<input type="email" class="form-control" name="email" value="<?php echo $_POST['email']; ?>" required>
								</div>
							</div>
							
							<div class="d-flex align-items-center">
								<input type="submit" class="btn btn-primary me-3" value="Request Reset">
								
								<a href="admin-login" class="ms-auto me-0">Return to login</a>
							</div>
							
							<?php if(isset($loginmessage)) : ?>
								<div class="alert alert-<?php echo  $status; ?> mb-0 mt-3">
									<?php echo $loginmessage; ?>
								</div>
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
</html>
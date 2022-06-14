<?php 
	require_once(dirname(__DIR__) . '/includes/database.php'); 
	require_once(dirname(__DIR__) . '/includes/functions.php'); 

	if(isset($_SESSION['adminid'])) {
		header('Location: ../admin');
		exit();
	}

	if(isset($_POST['dologin'])) {
		$checkUser = $mysqli->prepare("SELECT id, username, password FROM `users` WHERE username = ? AND role > 0");
		$checkUser->bind_param('s', $_POST['username']);
		$checkUser->execute();
		$userResult = $checkUser->get_result();
		
		if($userResult->num_rows > 0) {
			$user = $userResult->fetch_assoc();
			
			if(password_verify($_POST['password'], $user['password'])) {
				$failed = false;
				
				$_SESSION['adminid'] = $user['id'];
				$_SESSION['adminuser'] = $user['username'];
                $_SESSION['profileid'] = $_SESSION['adminid'];
                $_SESSION['profileuser'] = $_SESSION['adminuser'];
				
                if(!empty($_SESSION['adminredirect'])) {
                    header('Location: ' . $_SESSION['adminredirect']);
                }
                else {
				    header('Location: ../admin');
                }
                
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
?>

<!DOCTYPE html>

<html lang="en">
	<head>
		<base href="<?php echo BASE_DIR; ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<title>Admin Login</title>
		
		<link rel="preconnect" href="https://fonts.gstatic.com">
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
		<link rel="stylesheet" href="css/admin.min.css">
		
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
					<form id="adminLogin" class="shadow rounded bg-white overflow-hidden mx-auto my-5" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
						<input type="hidden" name="dologin">
						<input type="hidden" name="redirectto" value="<?php echo $_SESSION['adminredirect']; ?>">
                        
						<div class="formHeader text-center bg-primary text-white p-3">
							<h1 class="mb-0"><span class="fa fa-unlock-alt me-3"></span>Admin Login</h1>
						</div>

						<div class="formBody border-right border-left border-right border-light p-3">
							<div class="mb-3">
								<label>Username</label>
								
								<div class="input-group">
									<span class="input-group-text"><span class="fa fa-user"></span></span>
									<input type="text" class="form-control" name="username" value="<?php echo $_POST['username']; ?>" required autofocus>
								</div>
							</div>

							<div class="mb-3">
								<label>Password</label>
								
								<div class="input-group">
									<span class="input-group-text"><span class="fa fa-key"></span></span>
									<input type="password" class="form-control" name="password" value="<?php echo $_POST['password']; ?>" required>
								</div>
							</div>
							
							<div class="d-flex align-items-center">
								<input type="submit" class="btn btn-primary me-3" value="Sign In">
								
								<a href="admin-login/forgotten-password" class="ms-auto me-0">Forgotten password?</a>
							</div>
							
							<?php if(isset($loginmessage)) : ?>
								<div class="alert alert-<?php echo $status; ?> mb-0 mt-3">
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
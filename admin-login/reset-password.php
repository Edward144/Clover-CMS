<?php 
	require_once(dirname(__DIR__) . '/includes/database.php');
	require_once(dirname(__DIR__) . '/includes/functions.php');

	//If sessions are set redirect to admin

	//Check token
	if(!isset($_GET['token'])) {
		http_response_code(404);
		header('Location: ./');
		exit();
	}
	
	$date = date('Y-m-d H:i:s', strtotime('-1 day'));
	
	$checkToken = $mysqli->prepare("SELECT * FROM `password_reset` WHERE token = ? AND date_generated >= ? AND expired = 0 LIMIT 1");
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
?>

<!DOCTYPE html>

<html>
	<head>
		<base href="<?php echo BASE_DIR; ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<title>Admin Login | Reset Your Password</title>
		
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
					<form id="adminLogin" class="col-lg-6 shadow rounded bg-white overflow-hidden mx-auto my-5" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
						<input type="hidden" name="doreset">
						
						<div class="formHeader text-center bg-primary text-white p-3">
							<h1><span class="fa fa-unlock-alt me-3"></span>Admin Login</h1>
							<h3 class="mb-0">Reset Password</h3>
						</div>

						<div class="formBody border-right border-left border-right border-light p-3">
							<?php if($expired == true) : ?>
								<p class="mb-0">This link has expired, <a href="admin-login" class="ms-auto me-0">return to the login screen</a> and use the reset password option again.</p>
							<?php else : ?>
								<p>Create your new password. Passwords must be at least 12 characters and contain at least one uppercase, one lowercase and one digit. You can also generate a random password.</p>

								<div class="mb-3">
									<label>Password</label>

									<div class="input-group">
										<span class="input-group-text"><span class="fa fa-key"></span></span>
										<input type="password" class="form-control" name="password" value="<?php echo $_POST['password']; ?>" required>
									</div>
								</div>

								<div class="mb-3">
									<label>Confirm Password</label>

									<div class="input-group">
										<span class="input-group-text"><span class="fa fa-key"></span></span>
										<input type="password" class="form-control" name="passwordConf" value="<?php echo $_POST['passwordConf']; ?>" required>
									</div>
								</div>

								<div class="d-block d-md-flex align-items-center">
									<input type="submit" class="btn btn-primary me-3" value="Update Password">
									<input type="button" class="btn btn-secondary me-3" name="generate" value="Generate Password">

									<a href="admin-login" class="ms-auto me-0">Return to login</a>
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
	
	<script>
		$("input[name='generate']").click(function() {			
			$.ajax({
				url: window.location.pathname,
				method: "post",
				dataType: "json",
				data: ({generate: true}),
				success: function(data) {
					$(".formBody .alert").remove();
					$("input[name='password'],input[name='passwordConf']").val(data["password"]);
					$(".formBody").append("<div class='alert alert-" + data["status"] + " mb-0 mt-3'>" + data["message"] + "</div>");
				},
				fail(a, b, c) {
					alert(a);
				}
			});
		});
	</script>
</html>
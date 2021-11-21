<?php 
    
    require_once(dirname(__FILE__) . '/includes/database.php'); 
	require_once(dirname(__FILE__) . '/includes/functions.php');
    
    if(issignedin()) {
        header('Location: myaccount');
    }

    $title = 'Sign Up';

    //Register User
    if(isset($_POST['signup'])) {
        //Check if the email already exists
        $checkEmail = $mysqli->prepare("SELECT COUNT(*) FROM `users` WHERE email = ?");
        $checkEmail->bind_param('s', $_POST['email']);
        $checkEmail->execute();
        $emailResult = $checkEmail->get_result();
        
        if($emailResult->fetch_array()[0] > 0) {
            $status = 'danger';
            $signupmessage = 'This email address has already been registered.';
        }
        else {
            //Create username from first and last names and check if it is available
            $username = strtolower(substr(preg_replace('/[^a-zA-Z]/i', '', $_POST['firstName'] . $_POST['lastName']), 0, 20));
            $usernameAvailable = false;
            
            $minRand = 1000;
            $maxRand = 9999;
            $ui = rand($minRand, $maxRand);
            
            $checkUsername = $mysqli->prepare("SELECT COUNT(*) FROM `users` WHERE username = ?");
            $usernameToCheck = $username . $ui;
            
            while($usernameAvailable == false) {                
                $checkUsername->bind_param('s', $usernameToCheck);
                $checkUsername->execute();
                $usernameResult = $checkUsername->get_result()->fetch_array()[0];
                
                if($usernameResult == 0) {
                    $usernameAvailable = true;
                    $username = $usernameToCheck;
                }
                else {
                    $ui = rand($minRand, $maxRand);
                    $usernameToCheck = $username . $ui;
                }
            }
            
            //Create user entry
            if($_POST['password'] == $_POST['passwordConf']) {
                $password = PASSWORD_HASH($_POST['password'], PASSWORD_BCRYPT);
                
                $createUser = $mysqli->prepare("INSERT INTO `users_pending` (first_name, last_name, email, username, password, role) VALUES(?, ?, ?, ?, ?, -1)");
                $createUser->bind_param('sssss', $_POST['firstName'], $_POST['lastName'], $_POST['email'], $username, $password);
                $createUser->execute();
                
                if(!$createUser->error) {
                    $lastId = $mysqli->insert_id;
                    $content = 
                        '<p>Hi ' . $_POST['firstName'] . ' ' . $_POST['lastName'] . ',</p>
                        
                        <p>Thank you for registering at ' . $_SERVER['SERVER_NAME'] . '. Please click on the link below to verify your email address. You will then be able to login with your username <strong>' . $username . '</strong> and your chosen password.</p>
                        
                        <p><a style="background: #009688; color: #fff; padding: 0.5rem; border-radius: 4px; text-decoration: none;" href="https://' . $_SERVER['SERVER_NAME'] . ROOT_DIR . 'myaccount?id=' . $lastId . '&email=' . $_POST['email'] . '" target="_blank">Verify my email address</a></p>';
                    
                    if(sendemail($_POST['email'], 'Email verification required', $content)) {
                        $status = 'success';
                        $signupmessage = 
                            '<strong>Registration successful</strong><br>
                            Before you can login you must verify your email address by clicking on the link that has been sent to you.';
                    }
                    else {
                        $status = 'warning';
                        $signupmessage = 
                            '<strong>Registration successful</strong><br>
                            We were unable to send an email verification link, you will be unable to sign in until your email has been verified. Please contact us for further assistance, or try registering again later.';
                    }
                }   
                else {
                    $status = 'danger';
                    $signupmessage = 
                        '<strong>Registration unsuccessful</strong><br>
                        An error has occurred during registration, please contact us for further assistance or try again later.';
                }
            }
            else {
                $status = 'danger';
                $signupmessage = 'Passwords do not match';
            }
        }
    }

    require_once(dirname(__FILE__) . '/includes/header.php'); 

?>

<div class="content">
    <div class="col-lg-6 mx-auto my-3">
        <div class="bg-light rounded shadow-sm p-3">
            <form id="profilesignup" method="post">
                <h1>Register for an account</h1>

                <div class="form-group mb-3">
                    <label>First Name</label>
                    <input type="text" class="form-control" name="firstName" value="<?php echo $_POST['firstName']; ?>" required autofocus>
                </div>
                
                <div class="form-group mb-3">
                    <label>Last Name</label>
                    <input type="text" class="form-control" name="lastName" value="<?php echo $_POST['lastName']; ?>" required>
                </div>
                
                <div class="form-group mb-3">
                    <label>Email</label>
                    <input type="email" class="form-control" name="email" value="<?php echo $_POST['email']; ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label>Password</label>
                    <input type="password" class="form-control" name="password" value="<?php echo $_POST['password']; ?>" required>
                </div>
                
                <div class="form-group mb-3">
                    <label>Confirm Password</label>
                    <input type="password" class="form-control" name="passwordConf" value="<?php echo $_POST['passwordConf']; ?>" required>
                </div>

                <div class="form-group d-flex align-items-end justify-content-end">
                    <input type="submit" class="btn btn-primary text-light" name="signup" value="Sign Up">
                </div>
                
                <?php if(isset($signupmessage)) : ?>
                    <div class="alert alert-<?php echo $status; ?> mb-0 mt-3">
                        <?php echo $signupmessage; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <p class="my-3"><small><a href="signin">Already have an account? Sign in here</a></small></p>
    </div>
</div>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
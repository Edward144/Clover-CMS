<?php 
    
    require_once(dirname(__FILE__) . '/includes/database.php'); 
	require_once(dirname(__FILE__) . '/includes/functions.php');

    if(!issignedin() && isset($_GET['id']) & isset($_GET['email'])) {
        //Validate pending email address
        $checkEmail = $mysqli->prepare("SELECT * FROM `users_pending` WHERE id = ? AND email = ?");
        $checkEmail->bind_param('is', $_GET['id'], $_GET['email']);
        $checkEmail->execute();
        $emailResult = $checkEmail->get_result();
        
        if($emailResult->num_rows > 0) {
            $pendingAccount = $emailResult->fetch_assoc();
            
            //Copy details to users table
            $copyAccount = $mysqli->prepare(
                "INSERT IGNORE INTO `users` (first_name, last_name, email, username, password, role)
                SELECT first_name, last_name, email, username, password, role FROM `users_pending` WHERE id = ? AND email = ?");
            $copyAccount->bind_param('is', $_GET['id'], $_GET['email']);
            $copyAccount->execute();
            
            if($copyAccount->affected_rows > 0) {
                $lastId = $mysqli->insert_id;
                
                $deletePending = $mysqli->prepare("DELETE FROM `users_pending` WHERE email = ?");
                $deletePending->bind_param('s', $_GET['email']);
                $deletePending->execute();
                
                $_SESSION['profileid'] = $lastId;
                $_SESSION['profileuser'] = $pendingAccount['username'];
            }
        }
        else {
            echo 'This verification link is no longer active, try <a href="signin">signing in</a> or if that does not work then try <a href="signup">registering again</a>';
            exit();
        }
    }
    elseif(!issignedin()) {
        header('Location: signin');
    }
    
    $basicUser = BASIC_USER;

    $userDetails = $mysqli->prepare(
        "SELECT users.*, IF(users.role = -1, '{$basicUser}', roles.name) AS type FROM `users` 
            LEFT OUTER JOIN `roles` ON roles.id = users.role
        WHERE users.id = ?"
    );
    $userDetails->bind_param('i', $_SESSION['profileid']);
    $userDetails->execute();
    $userResult = $userDetails->get_result();

    if(isset($_GET['logout']) || $userResult->num_rows <= 0) {
        unset($_SESSION['profileid']);
        unset($_SESSION['profileuser']);
        unset($_SESSION['adminid']);
        unset($_SESSION['adminuser']);
        
        header('Location: signin');
        exit();
    }

    $user = $userResult->fetch_assoc();

    $title = 'My Account: ' . $user['username'];

    //Update details
    if(isset($_POST['updateAccount'])) {
        
    }

    require_once(dirname(__FILE__) . '/includes/header.php'); 

?>

<div class="content">
    <div class="my-3">
        <div class="bg-light rounded shadow-sm p-3">
            <span class="float-sm-end d-block text-end"><a href="<?php echo explode('?', $_SERVER['REQUEST_URI'])[0] ?>?logout" class="btn btn-dark">Sign Out</a></span>
            <h1>My Account: <?php echo $_SESSION['profileuser']; ?></h1>
            
            <form id="manageaccount" method="post">
                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                
                <div class="row">
                    <div class="col-md form-group mb-3">
                        <label>First Name</label>
                        <input type="text" class="form-control" name="firstName" value="<?php echo (!empty($_POST['firstName']) ? $_POST['firstName'] : $user['first_name']); ?>" required>
                    </div>
                    
                    <div class="col-md form-group mb-3">
                        <label>Last Name</label>
                        <input type="text" class="form-control" name="lastName" value="<?php echo (!empty($_POST['lastName']) ? $_POST['lastName'] : $user['last_name']); ?>" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md form-group mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" value="<?php echo (!empty($_POST['email']) ? $_POST['email'] : $user['email']); ?>" required>
                    </div>
                    
                    <div class="col-md form-group mb-3">
                        <label>Account Type</label>
                        <input type="text" class="form-control" value="<?php echo $user['type']; ?>" readonly>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
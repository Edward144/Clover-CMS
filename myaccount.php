<?php 
    
    require_once(dirname(__FILE__) . '/includes/database.php'); 
	require_once(dirname(__FILE__) . '/includes/functions.php');

    if(signstatus()['signin'] == false) {
        http_response_code(403);
        header('Location: ' . ROOT_DIR);
        exit();
    }

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
        if(!empty($_POST['password']) && $_POST['password'] == $_POST['passwordConf']) {
            $password = PASSWORD_HASH($_POST['password'], PASSWORD_BCRYPT);
            
            $updateAccount = $mysqli->prepare("UPDATE `users` SET first_name = ?, last_name = ?, email = ?, password = ? WHERE id = ?");
            $updateAccount->bind_param('ssssi', $_POST['firstName'], $_POST['lastName'], $_POST['email'], $password, $_SESSION['profileid']);
        }
        else {
            $updateAccount = $mysqli->prepare("UPDATE `users` SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
            $updateAccount->bind_param('sssi', $_POST['firstName'], $_POST['lastName'], $_POST['email'], $_SESSION['profileid']);
        }
        
        $updateAccount->execute();
        
        if(!$updateAccount->error) {
            $status = 'success';
            $accountmessage = 'Changes saved successfully';
        }
        else {
            $status = 'danger';
            $accountmessage = 'Changes failed to save, please try again later';
        }
    }

    //Get comments
    $commentsCount = $mysqli->prepare("SELECT COUNT(*) FROM `comments` WHERE registered = ?");
    $commentsCount->bind_param('i', $_SESSION['profileid']);
    $commentsCount->execute();
    $commentsCountResult = $commentsCount->get_result()->fetch_array()[0];

    $cPagination = new pagination($commentsCountResult, 'cpage');
    $cPagination->load();

    $comments = $mysqli->prepare(
        "SELECT comments.*, posts.name as post_name, posts.url as post_url, post_types.name as post_type FROM `comments`
            LEFT OUTER JOIN `posts` ON posts.id = comments.post_id
            LEFT OUTER JOIN `post_types` ON post_types.id = posts.post_type_id
        WHERE registered = ? ORDER BY date_posted DESC LIMIT {$cPagination->itemsPerPage} OFFSET {$cPagination->offset}"
    );
    $comments->bind_param('i', $_SESSION['profileid']);
    $comments->execute();
    $commentsResult = $comments->get_result();

    require_once(dirname(__FILE__) . '/includes/header.php'); 

?>

<div class="content">
    <div class="bg-light rounded shadow-sm my-3 p-3">
        <span class="float-sm-end d-block text-end"><a href="<?php echo explode('?', $_SERVER['REQUEST_URI'])[0] ?>?logout" class="btn btn-dark">Sign Out</a></span>
        <h1>My Account: <?php echo $_SESSION['profileuser']; ?></h1>

        <form id="manageaccount" method="post">                
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

            <p><a href="#" id="togglePassword" data-bs-toggle="collapse" data-bs-target="#updatePassword" aria-expanded="false">Update Password</a></p>

            <div id="updatePassword" class="row collapse">
                <div class="col-md form-group mb-3">
                    <label>New Password</label>
                    <input type="password" class="form-control" name="password" value="<?php echo (!empty($_POST['password']) ? $_POST['password'] : ''); ?>">
                </div>

                <div class="col-md form-group mb-3">
                    <label>Confirm New Password</label>
                    <input type="password" class="form-control" name="passwordConf" value="<?php echo (!empty($_POST['passwordConf']) ? $_POST['passwordConf'] : ''); ?>">
                </div>
            </div>

            <div class="form-group d-flex align-items-center justify-content-end">
                <input type="submit" class="btn btn-primary text-light" name="updateAccount" value="Save Changes">
            </div>

            <?php if(isset($accountmessage)) : ?>
                <div class="alert alert-<?php echo $status; ?> mb-0 mt-3">
                    <?php echo $accountmessage; ?>
                </div>
            <?php endif; ?>
        </form>
    </div>
    
    <?php if($commentsResult->num_rows > 0) : ?>
        <div class="bg-light rounded shadow-sm my-3 p-3">
            <h2>Comment history</h2>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Comment</th>
                            <th class="text-center">Commented On</th>
                            <th class="text-center">Date Posted</th>
                            <th class="text-center">Approved?</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <?php while($comment = $commentsResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo substr($comment['content'], 0, 100) . (strlen($comment['content']) > 100 ? '...' : ''); ?></td>
                                <td class="text-center"><a href="<?php echo $comment['post_url']; ?>"><?php echo $comment['post_name']; ?></a></td>
                                <td class="text-center"><?php echo date('d/m/Y H:i', strtotime($comment['date_posted'])); ?></td>
                                <td class="text-center"><?php echo ($comment['approved'] == 1 ? '<span class="fa fa-check-circle text-success"></span>' : '<span class="fa fa-times-circle text-danger"></span>'); ?></td>
                                <td class="text-center">
                                    <?php if($comment['approved'] == 1 && $comment['modified'] == 0) : ?>
                                        <a href="<?php echo $comment['post_url'] . '#comment' . $comment['id']; ?>">View / Edit</a>
                                    <?php elseif($comment['approved'] == 1 && $comment['modified'] == 1) : ?>
                                        <a href="<?php echo $comment['post_url'] . '#comment' . $comment['id']; ?>">View</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <?php echo $cPagination->display(); ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
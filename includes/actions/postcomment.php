<?php

    require_once(dirname(__DIR__) . '/database.php');
    require_once(dirname(__DIR__) . '/functions.php');
    
    if(isset($_POST['postid']) && isset($_POST['replyto'])) {
        $author = 'Unknown';
        $registered = 0;
        $approved = 0;
        
        $checkApproved = $mysqli->query("SELECT value FROM `settings` WHERE name = 'comment_approval'");
        
        if($checkApproved->num_rows > 0) {
            $approval = $checkApproved->fetch_array()[0];
            $approved = ($approval == 'approved' ? 1 : 0);
        }
        
        if(isset($_SESSION['adminid'])) {
            $checkUser = $mysqli->prepare("SELECT CONCAT(first_name, ' ', last_name) AS author FROM `users` WHERE id = ?");
            $checkUser->bind_param('i', $_SESSION['adminid']);
            $checkUser->execute();
            $userResult = $checkUser->get_result();
            
            if($userResult->num_rows > 0) {
                $author = $userResult->fetch_array()[0];
                $registered = $_SESSION['adminid'];
            }
        }
        elseif(!empty($_POST['author'])) {
            $author = $_POST['author'];
        }
        
        $postComment = $mysqli->prepare("INSERT INTO `comments` (post_id, author, registered, content, original_content, reply_to, approved, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $postComment->bind_param('isissiis', $_POST['postid'], $author, $registered, $_POST['content'], $_POST['content'], $_POST['replyto'], $approved, $_SERVER['REMOTE_ADDR']);
        $postComment->execute();
        
        if($postComment->error) {
            $_SESSION['status' . $_POST['replyto']] = 'danger';
            $_SESSION['message' . $_POST['replyto']] = 'There was an error posting your comment';
        }
        else {
            $_SESSION['status' . $_POST['replyto']] = 'success';
            $_SESSION['message' . $_POST['replyto']] = 'Your comment has been posted successfully' . ($approved == 0 ? ', your comment will appear once it has been approved' : '');
        }
    }

    $noId = explode('#', $_POST['returnurl'])[0];
    $queryString = '?' . explode('?', $noId)[1];
    $redirectUri = explode('?', $_POST['returnurl'])[0] . $queryString . (isset($_POST['replyto']) ? '#post' . $_POST['replyto'] : '');

    header('Location: ' . $redirectUri);

?>
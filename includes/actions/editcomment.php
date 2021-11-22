<?php

    require_once(dirname(__DIR__) . '/database.php');
    require_once(dirname(__DIR__) . '/functions.php');
    
    if(isset($_POST['editComment'])) {
        $updateComment = $mysqli->prepare("UPDATE `comments` SET edited_from = content, content = ?, edited = 1, edit_date = ? WHERE id= ? AND registered = ?");
        $updateComment->bind_param('ssii', $_POST['comment'], date('Y-m-d H:i:s'), $_POST['id'], $_SESSION['profileid']);
        $updateComment->execute();
        
        if(!$updateComment->error && $updateComment->affected_rows > 0) {
            $_SESSION['status' . $_POST['id']] = 'success';
            $_SESSION['messsage' . $_POST['id']] = 'Comment updated successfully';
        }
        else {
            $_SESSION['status' . $_POST['id']] = 'danger';
            $_SESSION['messsage' . $_POST['id']] = 'Failed to update comment, please try again later';
        }
    }

    $noId = explode('#', $_POST['returnurl'])[0];
    $queryString = (!empty(explode('?', $noId)[1]) ? '?' . explode('?', $noId)[1] : '');
    $redirectUri = explode('?', $_POST['returnurl'])[0] . $queryString . '#comment' . $_POST['id'];

    header('Location: ' . $redirectUri);
    
?>
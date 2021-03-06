<?php

    if(isset($_POST['loadcomments'])) {
        require_once(dirname(__DIR__) . '/database.php');
        
        $loadComments = new comments($_POST['contentid'], $_POST['parent'], $_POST['offset']);
        $loadComments->display();
        
        exit();
    }

    class comments {
        private $commentsOpen = false;
        private $loadcaptcha = false;
        private $sitekey;
        private $secretkey;
        private $output = '';
        
        public function __construct($postid, $parent = 0, $offset = 0) {
            global $mysqli;
            
            if(isset($postid) && is_numeric($postid)) {
                //Check for Recaptcha details
                $settings = $mysqli->query("SELECT * FROM `settings` WHERE name = 'recaptcha_sitekey_v2' OR name = 'recaptcha_secretkey_v2'");
                
                if($settings->num_rows == 2) {
                    while($setting = $settings->fetch_assoc()) {
                        switch($setting['name']) {
                            case 'recaptcha_sitekey_v2': 
                                $this->sitekey = $setting['value'];
                                break;
                            case 'recaptcha_secretkey_v2':
                                $this->secretkey = $setting['value'];
                                break;
                        }
                    }
                    
                    if(!empty($this->sitekey) && !empty($this->secretkey)) {
                        $this->loadcaptcha = true;
                    }
                }
                
                //Check that this post allows comments
                $checkAllowed = $mysqli->prepare("SELECT allow_comments FROM `posts` WHERE id = ?");
                $checkAllowed->bind_param('i', $postid);
                $checkAllowed->execute();
                $allowedResult = $checkAllowed->get_result();
                
                if($allowedResult->num_rows > 0) {
                    $allowed = $allowedResult->fetch_array()[0];
                    $this->commentsOpen = ($allowed == 1 ? true : false);
                }
                
                //Load existing comments
                $this->output .= $this->loadcomments($postid, $parent, $offset);
            }
        }
        
        public function display() {
            echo $this->output;
            
            foreach($_SESSION as $key => $session) {
                if(strpos($key, 'status') !== false || strpos($key, 'message') !== false) {
                    unset($_SESSION[$key]);
                }
            }
        }
        
        private function loadcomments($postid, $parent, $offset) {
            global $mysqli;
            
            $output = '';
            $offset = (!is_numeric($offset) || $offset < 0 ? 0 : $offset);
            $limit = ($parent == 0 ? 'LIMIT 10 OFFSET ' . $offset : '');
            
            $checkComments = $mysqli->prepare("SELECT * FROM `comments` WHERE post_id = ? AND reply_to = ? AND approved = 1 ORDER BY date_posted DESC {$limit}");
            $checkComments->bind_param('ii', $postid, $parent);
            $checkComments->execute();
            $checkResult = $checkComments->get_result();
            
            $commentsCount = $mysqli->prepare("SELECT COUNT(*) FROM `comments` WHERE post_id = ? AND reply_to = ? AND approved = 1");
            $commentsCount->bind_param('ii', $postid, $parent);
            $commentsCount->execute();
            $commentsCount = $commentsCount->get_result()->fetch_array()[0];
            
            if($checkResult->num_rows > 0) {
                if($parent == 0 && $offset == 0) {
                    $output .=
                        '<div class="comments bg-light p-3" id="comments' . $postid . '">
                            <h4 class="mb-3">Comments</h4>';
                }
                
                while($comment = $checkResult->fetch_assoc()) {
                    $commentDetails = '';
                    
                    if($comment['registered'] > 0) {
                        $checkUser = $mysqli->prepare("SELECT first_name, last_name FROM `users` WHERE id = ?");
                        $checkUser->bind_param('i', $comment['registered']);
                        $checkUser->execute();
                        $userResult = $checkUser->get_result();
                        
                        if($userResult->num_rows > 0) {
                            $user = $userResult->fetch_assoc();
                            $author = $user['first_name'] . ' ' . $user['last_name'];
                        }
                        else {
                            $author = $comment['author'];
                        }
                        
                        $isGuest = false;
                    }
                    else {
                        $author = $comment['author'];
                        $isGuest = true;
                    }
                    
                    if($comment['modified'] == 1) {
                        $commentDetails .=
                            '<span class="commentModified text-muted flex-grow-1"><small>This comment has been modified by an administrative user</small></span>';
                    }
                    
                    $output .=
                        '<div class="comment border-rounded mb-3 mt-3 ms-3" id="comment' . $comment['id'] . '">
                            <div class="commentBody bg-white border-rounded py-2 px-3 mb-2">' . htmlspecialchars(trim($comment['content'])) . '</div>
                            
                            <div class="commentFooter d-flex align-items-start justify-content-end mb-3">' .
                                $commentDetails .
                                '<span class="commentAuthor ms-3">' . ($isGuest == true ? '(Guest) ' : '') . '<strong>' . htmlspecialchars(trim($author)) . '</strong></span>
                                <span class="commentDate"><small>&nbsp;on ' . date('d/m/Y H:i', strtotime($comment['date_posted'])) . '</small></span>' .
                                ($comment['edited'] == 1 ? '&nbsp;edited on&nbsp;<span class="commentEditDate"><small>' . date('d/m/Y H:i', strtotime($comment['edit_date'])) . '</small></span>' : '') .
                            '</div>' .
                            $this->postcomment($postid, $comment['id']) .
                            $this->loadcomments($postid, $comment['id'], 0) .
                        '</div>';
                }
                
                if($parent == 0 && $offset == 0) {
                    $output .= 
                            ($commentsCount > 10 ? '<a href="#" onclick="comments_load(' . $postid . ', ' . $parent . '); return false;">Load More Comments</a>' : '') .
                            $this->postcomment($postid) .
                        '</div>';
                    
                    if($this->loadcaptcha == true) {
                        $output .=
                            '<script>
                                var recaptchaSitekey = "' . $this->sitekey . '";
                            </script>
                            <script src="https://www.google.com/recaptcha/api.js?onload=recaptchaOnload&render=explicit" async defer></script>';       
                    }
                }
            }
            elseif($this->commentsOpen == true && $parent == 0) {
                $output =
                    '<div class="comments bg-light p-3" id="comments' . $postid . '">
                        <h4 class="mb-3">Comments</h4>
                        <p>There are no comments yet.</p>' .
                        $this->postcomment($postid) .
                    '</div>';
            }

            return $output;
        }
        
        private function postcomment($postid, $commentid = 0) {
            global $mysqli;
            
            $commentDetails = [];
            
            if($commentid > 0) {
                $comment = $mysqli->prepare("SELECT * FROM `comments` WHERE id = ?");
                $comment->bind_param('i', $commentid);
                $comment->execute();
                $commentResult = $comment->get_result();
                
                if($commentResult->num_rows > 0) {
                    $commentDetails = $commentResult->fetch_assoc();
                }
            }
            
            if($this->commentsOpen == true) {                
                if(!empty($_SESSION['adminid']) || !empty($_SESSION['userid'])) {
                    $checkUser = $mysqli->prepare("SELECT id, first_name, last_name FROM `users` WHERE id = ? OR id = ? LIMIT 1");
                    $checkUser->bind_param('ii', $_SESSION['adminid'], $_SESSION['userid']);
                    $checkUser->execute();
                    $userResult = $checkUser->get_result();
                    
                    if($userResult->num_rows > 0) {
                        $user = $userResult->fetch_assoc();
                    }
                    else {
                        $user['id'] = 0;
                        $user['first_name'] = 'Unknown';
                        $user['last_name'] = 'User';
                    }
                    
                    $guestName = 
                        '<input type="hidden" name="registered" value="' . $user['id'] . '">
                        
                        <div class="form-group mb-3">
                            <label>Your name</label>
                            <input type="text" class="form-control" value="' . $user['first_name'] . ' ' . $user['last_name'] . '" readonly>
                        </div>';
                }
                else {
                    $guestName =
                        '<div class="form-group mb-3">
                            <label class="required">Your name</label>
                            <input type="text" class="form-control" name="author" required>
                        </div>';
                }
                
                return 
                    ($commentid == 0 ? '<hr><h5>Post a comment</h5>' :'<h6 class="text-end mt-n4"><small>' . (isset($_SESSION['profileid']) && $_SESSION['profileid'] == $commentDetails['registered'] && $commentDetails['modified'] == 0 ? '<a href="#" class="commentEdit me-3">Edit</a>' : '') . '<a href="#" class="commentReply" data-bs-toggle="collapse" data-bs-target="#post' . $commentid .'" aria-expanded="' . (!empty($_SESSION['message' . $commentid]) ? 'true' : 'false') . '">Reply</a></small></h6>') .
                    
                    '<div class="commentForm ' . ($commentid > 0 ? (!empty($_SESSION['message' . $commentid]) ? 'collapse show' : 'collapse') : '') . '" id="post' . $commentid . '">
                        <form class="postComment" action="includes/actions/postcomment.php" method="post">
                            <input type="hidden" name="returnurl" value="' . $_SERVER['REQUEST_URI'] . '">
                            <input type="hidden" name="postid" value="' . $postid . '">
                            <input type="hidden" name="replyto" value="' . $commentid . '">' .
                            $guestName .

                            '<div class="form-group mb-3">
                                <label class="required">Your comment</label>
                                <textarea class="form-control" name="content" maxlength="1000" required></textarea>
                            </div>' .
                            
                            ($this->loadcaptcha == true ? 
                            '<div class="form-group mb-3">
                                <div id="recaptcha' . $commentid . '" class="recaptcha"></div>
                            </div>' : '') .
                    
                            '<div class="form-group d-flex align-items-center justify-content-end">
                                <input type="submit" class="btn btn-primary text-white" value="' . ($commentid == 0 ? 'Post Comment' : 'Post Reply') . '">
                            </div>' .
                            
                            (!empty($_SESSION['message' . $commentid]) ? '<div class="mt-3 mb-0 py-2 alert alert-' . $_SESSION['status' . $commentid] . '">' . $_SESSION['message' . $commentid] . '</div>' : '') .
                        '</form>
                    </div>';
            }
            elseif($this->commentsOpen == false && $commentid == 0) {
                return 
                    '<hr>
                    <span class="commentsClosed text-muted">Comments have been disabled</span>';
            }
        }
    }

?>
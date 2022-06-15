<?php 
	$title = 'Dashboard';
	require_once(dirname(__FILE__) . '/includes/header.php'); 

    $getUser = $mysqli->prepare("SELECT * FROM `users` WHERE id = ?");
    $getUser->bind_param('i', $_SESSION['adminid']);
    $getUser->execute();
    $userResult = $getUser->get_result();

    if($userResult->num_rows > 0) {
        $user = $userResult->fetch_assoc();
    }
    else {
        $user = [];
    }

    $getPostTypes = $mysqli->query("SELECT * FROM `post_types`");

    //Recent logins
    $recentSignins = $mysqli->query("SELECT * FROM `users` WHERE role >= 0 AND last_signin <> '' AND last_signin IS NOT NULL ORDER BY last_signin DESC LIMIT 5");
?>

<div class="col py-3">
    <h2>Welcome <small><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></small></h2>
    <hr>
    
    <?php if($getPostTypes->num_rows > 0) : ?>
        <h3>Latest Edits</h3>
    
        <div class="row latestEdits mb-n3">
            <?php while($postType = $getPostTypes->fetch_assoc()) : ?>
                <?php
                    $posts = $mysqli->prepare(
                        "SELECT posts.id, posts.name, posts.last_edited, CONCAT(users.first_name, ' ', users.last_name) AS edited_by FROM `posts` AS posts
                            LEFT OUTER JOIN `users` AS users ON users.id = posts.last_edited_by
                        WHERE post_type_id = ? ORDER BY posts.last_edited DESC LIMIT 5"
                    );
                    $posts->bind_param('i', $postType['id']);
                    $posts->execute();
                    $postsResult = $posts->get_result();
                    $postCount = $postsResult->num_rows;
                ?>
            
                <?php if($postCount > 0) : ?>
                    <div class="col-lg-6 col-xl-4 mb-3">
                        <div class="postTypeEdits <?php echo $postType['name']; ?>Edits">
                            <h5 class="mb-0 px-3 py-2 bg-primary text-white"><?php echo ucwords(str_replace('-', ' ', $postType['name'])); ?></h5>
                            
                            <ul class="list-group flex-grow-1">
                                <?php while($post = $postsResult->fetch_assoc()) : ?>
                                    <li class="list-group-item d-flex align-items-start justify-content-between flex-wrap">
                                        <a href="admin/manage-content/<?php echo $postType['name'] . '?id=' . $post['id']; ?>"><span><?php echo $post['name']; ?></span></a>
                                        <span class="ms-auto me-0"><?php echo date('d/m/Y H:i', strtotime($post['last_edited'])) . ' by ' . (!empty($post['edited_by']) ? $post['edited_by'] : 'Unknown'); ?></span>
                                    </li>
                                <?php endwhile; ?>
                                
                                <?php /*for($postCount; $postCount < 5; $postCount++) : ?>
                                    <li class="list-group-item">
                                        <span>&nbsp;</span>
                                        <span>&nbsp;</span>
                                    </li>
                                <?php endfor;*/ ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endwhile; ?>
        </div>

        <hr>
    <?php endif; ?>

    <?php if($recentSignins->num_rows > 0) : ?>
        <div class="row recentSignins mb-n3">
            <div class="col-lg-6 col-xl-4 mb-3">
                <div class="signinList">
                    <h5 class="mb-0 px-3 py-2 bg-primary text-white">Recent User Logins</h5>
                    
                    <ul class="list-group flex-grow-1">
                        <?php while($signin = $recentSignins->fetch_assoc()) : ?>
                            <li class="list-group-item d-flex align-items-start justify-content-between flex-wrap">
                                <span><?php echo $signin['first_name'] . ' ' . $signin['last_name'] . ' (' . $signin['username'] . ')'; ?></span>
                                <span class="ms-auto me-0"><?php echo date('d/m/Y H:i', strtotime($signin['last_signin'])); ?></span>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>

        <hr>
    <?php endif; ?>
</div>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
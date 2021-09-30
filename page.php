<?php 
    $content = $mysqli->prepare("SELECT * FROM `posts` WHERE id = ?");
    $content->bind_param('i', $contentId);
    $content->execute();
    $contentResult = $content->get_result();

    if($contentResult->num_rows <= 0) {
        http_response_code(404);
        include_once($_SERVER['DOCUMENT_ROOT'] . ROOT_DIR . '404.php');
        exit();
    }

    $content = $contentResult->fetch_assoc();

    $title = (!empty($content['meta_title']) ? $content['meta_title'] : $content['name']);
    $description = (!empty($content['meta_description']) ? $content['meta_description'] : $content['excerpt']);
    $author = (!empty($content['meta_author']) ? $content['meta_author'] : $content['author']);
    $keywords = (!empty($content['meta_keywords']) ? $content['meta_keywords'] : '');

    require_once(dirname(__FILE__) . '/includes/header.php');

    echo carousel($content['id']);
?>

<div class="uc content">
    <?php parsecontent($content['content']); ?>
</div>

<?php 
    if($content['id'] == $settingsArray['newspage']) {
        echo listposts();
    }

    $comments = new comments($content['id']); 
    $comments->display();
?>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
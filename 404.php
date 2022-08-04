<?php 
    $title = '404 - This page could not be found';
    $content = [];
    http_response_code(404);
    require_once(dirname(__FILE__) . '/includes/header.php'); 
?>

<h1>This page could not be found</h1>
<p>You can return to the <a href="<?php echo ROOT_DIR; ?>">homepage</a> or <a href="javascript:history.go(-1);">go back to the last page you were on.</a></p>

<span class="bg404">404</span>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
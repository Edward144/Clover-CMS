<?php 
	$title = '404 - Page Not Found';
	require_once(dirname(__FILE__) . '/includes/header.php'); 
?>

<div class="col py-3">
    <h1>This page does not exist</h1>
    <p>Go to the <a href="./admin">dashboard</a> or return to the <a href="./admin" onclick="window.history.back(); return false;">last page</a> you were on</p>
</div>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
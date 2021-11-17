<?php 
    
    require_once(dirname(__FILE__) . '/includes/database.php'); 
	require_once(dirname(__FILE__) . '/includes/functions.php');

    if(!issignedin()) {
        header('Location: signin');
    }

    $title = 'My Account';

    require_once(dirname(__FILE__) . '/includes/header.php'); 

?>

<div class="content">
    myaccount <?php echo $_SESSION['profileuser']; ?>
</div>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
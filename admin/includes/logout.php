<?php 

	session_start();
	
	unset($_SESSION['adminid']);
	unset($_SESSION['adminuser']);
	
	header('Location: ../../admin-login');
	
?>
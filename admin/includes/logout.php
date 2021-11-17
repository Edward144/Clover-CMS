<?php 

	session_start();
	
	unset($_SESSION['adminid']);
	unset($_SESSION['adminuser']);
	unset($_SESSION['profileid']);
	unset($_SESSION['profileuser']);
	
	header('Location: ../../admin-login');
	
?>
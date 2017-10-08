<?php
	require_once "DB.class.php";
	
	$db = new DB();
	$cleared = $db -> clearCart();
	
	echo $cleared;
?>
<?php
	require_once "DB.class.php";
	
	if( !checkSession() )
	{
		header('Location: login.php?loc=cartClear');
	}

	$user = $_SESSION['uid'];

	$db = new DB();
	$cleared = $db -> clearCart($user);
	
	echo $cleared;
?>
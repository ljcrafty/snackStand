<?php
	require_once "LIB_project1.php";
	
	if( !checkSession() )
	{
		header('Location: login.php?loc=cartAdd');
	}

	//if an id was passed
	if( canGet('id') == 0 || canGet('id') )
	{
		require_once "DB.class.php";
		
		$id = intVal(canGet('id'));
		$user = $_SESSION['uid'];
		
		//increment cart and record num remaining
		$db = new DB();
		$remaining = $db -> incrementCart( $id, $user );
		
		//if -1 was returned, an error occurred with the request
		echo $remaining;
	}
?>
<?php
	require_once "LIB_project1.php";
	
	if( !checkSession() )
	{
		echo "";
		die();
	}

	session_unset();
	session_destroy();
	
	echo "success";
?>
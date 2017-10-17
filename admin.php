<?php
	require_once "DB.class.php";
	require_once "LIB_project1.php";
	
	//check for session to see if they need to log in
	if( !checkSession() )
	{
		header('Location: login.php?loc=admin');
	}

	echo getHeader("Admin");
	echo nav();
	
	echo "<main><button onclick='window.location = \"edit.php\"' id='addItem'>
		Add an Item</button>".getAdminTable()."</main>";
	
	if( $msg = trim(canGet('msg'), '"') )
	{
		if( explode(":", $msg)[0] == "Error" )
		{
			echo notify($msg);
		}
		else
		{
			echo "<script>$.notify(".$msg.", {position: 'top center', className: 'success'});</script>";
		}
	}
	
	echo footer();
?>
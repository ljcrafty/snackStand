<?php
	require_once "DB.class.php";
	require_once "LIB_project1.php";
	
	//check for session to see if they need to log in

	echo getHeader("Admin");
	echo nav();
	
	echo "<main><button onclick='window.location = \"edit.php\"' id='addItem'>
		Add an Item</button>".getAdminTable()."</main>";
	
	if( canGet('msg') )
	{
		echo "<script>$.notify('".canGet('msg')."', {position: 'top center', className: 'success'});</script>";
	}
	
	echo footer();
?>
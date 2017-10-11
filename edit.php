<?php
	require_once "DB.class.php";
	require_once "LIB_project1.php";
	
	//check for session to see if they need to log in

	echo getHeader("Edit Item");
	echo nav();
	
	//submission
	if( !empty($_POST) )
	{
		//problem with inputs in name or type
		if( !validateAttrs($_POST) )
		{
			echo getEditEndPage(-1, 'There was a problem saving.\nMake sure all inputs are valid');
			die();
		}
		
		$db = new DB();
		
		//add item instead of update
		if( $_POST['id'] == -1 )
		{
			///////////////////////////////////////////////////////////////////////////////////////
			//need to add thing here and in else to deal with image and collect the right imgName//
			/////////////////////////////////////////////////////////////////////////////////////// 
			$temp = overwriteSales($_POST);
			
			$result = $db -> addItem($temp);
		}
		else
		{
			//update start
			//overriding sale price if taken off sale
			$temp = overwriteSales($_POST);
		
			$result = $db -> updateItem($temp);
		}
		
		//query returned 0 rows
		if( !$result )
		{
			echo getEditEndPage($_POST['id'], 'There was a problem saving.\nMake sure all inputs are valid');
			die();
		}
		
		header("Location: admin.php?msg='Changes Saved'");;
		die();
	}
	
	$id = canGet('id');
	
	if( $id == '' )
	{
		$id = -1;
	}
	
	echo getEditEndPage($id, '');
?>
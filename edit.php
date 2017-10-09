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
		
		//add item instead of update
		if( $_POST['id'] == -1 )
		{
			echo "add coming later";
			
			//on success, header("Location: admin.php?msg='Item added'");
			die();
		}
		
		//update start
		//overriding sale price if taken off sale
		$temp = $_POST;
		
		if( !array_key_exists('isSale', $temp ) )
		{
			$temp['salePrice'] = 0;
		}
		
		$db = new DB();
		$result = $db -> updateItem($temp);
		
		//query returned 0 rows
		if( !$result )
		{
			echo getEditEndPage($_POST['id'], 'There was a problem saving.\nMake sure all inputs are valid');
			die();
		}
		
		echo getEditEndPage($_POST['id'], '')."<script>
			$.notify('Changes Saved', {position: 'top center', className: 'success'})</script>";
		die();
	}
	
	$id = canGet('id');
	
	if( $id == '' )
	{
		$id = -1;
	}
	
	echo getEditEndPage($id, '');
?>
<?php
	require_once "DB.class.php";
	require_once "LIB_project1.php";
	
	if( !checkSession() )
	{
		header('Location: login.php?loc=cart');
	}

	echo getHeader("Cart");
	echo nav();
	
	$user = $_SESSION['uid'];
	$db = new DB();
	$data = $db -> getCart($user);
	$empty = ( $data != '' ? "<button id='empty' onclick='empty()'>Empty Cart</button>" : 
		"<h2>There are no items in your cart! <a href='index.php'>Go Shopping!</a></h2>" );
	
	echo "<main>\n
		$data\n
		$empty</main>";
	
	echo footer();
?>
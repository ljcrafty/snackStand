<?php
	function getHeader($title)
	{
		return "<!DOCTYPE html>
		<html>
		  	<head>
				<meta charset='UTF-8'>
				<title>$title</title>
		  		<link rel='stylesheet' href='css/style.css' type='text/css'>
		  		<script src='js/functions.js'></script>
		  	</head>
		  	<body>\n";
	}

	function nav()
	{
		return "<header>\n
		<h1>Lauren's Snack Stand</h1>\n
		<nav>
			<a href='index.php'>Home</a>\n
			<a href='cart.php'>Cart</a>\n
			<a href='login.php'>Admin</a>
		</nav>
		</header>";
	}
	
	function footer()
	{
		return "<footer>By Lauren Johnston</footer>\n</body></html>";
	}
	
	function canGet( $index )
	{
		if( isset($_GET) && !empty($_GET) )
		{
			return $_GET[$index];
		}
		else
		{
			return "";
		}
	}
?>
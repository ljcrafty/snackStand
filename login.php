<?php
	require_once "DB.class.php";
	require_once "LIB_project1.php";
	
	echo getHeader("Login");
	echo nav();

	//submission
	if( !empty($_POST) )
	{
		//form was not full
		if( !$_POST['username'] || !$_POST['password'] )
		{
			echo getLogin('Please enter both username and password.');
			echo footer();
			die;
		}

		//get user data
		$db = new DB();
		$data = $db -> getuser($_POST['username']);

		//username is right
		if( strlen($_POST['password']) <= 30 && !empty($data) )
		{
			$pwd = sha1($_POST['password']);

			if( strVal($data[0]['password']) == $pwd )
			{
				header('Location: admin.php');
			}
			else
			{
				echo getLogin('Incorrect username or /password/.');
				echo footer();
				die();
			}
		}
		else //username wrong
		{
			echo getLogin('Incorrect username or password.');
			echo footer();
			die();
		}
	}

	echo getLogin('');
	echo footer();
?>
<?php
	require_once "DB.class.php";
	require_once "LIB_project1.php";

	echo getHeader("Login");

	//redirected from another page
	if( !empty($_GET['loc']) )
	{
		switch( $_GET['loc'] )
		{
			case 'cart':
				$msg = "Please login to access your cart";
				break;

			case 'cartAdd':
				$msg = "Please login to add to your cart";
				break;

			case 'cartClear':
				$msg = "Please login to clear your cart";
				break;

			case 'admin':
				$msg = "Please login to access admin tools";
				break;

			case 'edit':
				$msg = "Please login to edit an item";
				break;
			
			default:
				$msg = "Please login";
		}

		echo notify($msg);
	}

	echo nav();

	//submission
	if( !empty($_POST) )
	{
		//form was not full
		if( !$_POST['username'] || !$_POST['password'] )
		{
			echo getLogin('Please enter both username and password.');
			echo footer();
			die();
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
				session_name( 'snacks' );
				session_start();

				$_SESSION['user'] = $_POST['username'];
				$_SESSION['uid'] = $data[0]['id'];
				
				switch( $_GET['loc'] )
				{
					case 'cart':
					case 'cartAdd':
					case 'cartClear':
						header('Location: cart.php');
						break;

					case 'admin':
					case 'edit':
						header('Location: admin.php');
						break;
					
					default:
						header('Location: index.php');
				}
				die();
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
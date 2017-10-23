<?php
	require_once "DB.class.php";

	/*
		Creates an HTML header for a file
		$title	- the title to be included in the tab of the browser
		returns	- the HTML header for a file
	*/
	function getHeader($title)
	{
		return "<!DOCTYPE html>
		<html>
		  	<head>
				<meta charset='UTF-8'>
				<title>$title</title>
		  		<link rel='stylesheet' href='css/style.css' type='text/css'>
		  		<link rel='stylesheet' href='//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css'>
		  		<script src='js/functions.js'></script>
		  		<script src='https://code.jquery.com/jquery-1.12.4.js'></script>
		  		<script src='js/notify.js'></script>
		  	</head>
		  	<body>\n";
	}
	
	/*
		Creates an HTML navigation bar for a file
		returns	- the HTML nav bar for a file
	*/
	function nav()
	{
		if(isset($_SESSION) || checkSession())
		{
			$logout = "<div>Hello {$_SESSION['user']}! <a href='javascript: logout()'>Logout</a></div>";
		}
		else
		{
			$logout = "";
		}

		return "<header>\n
		<h1>Lauren's Snack Stand</h1>\n
		$logout\n
		<nav>
			<a href='index.php'>Home</a>\n
			<a href='cart.php'>Cart</a>\n
			<a href='admin.php'>Admin</a>
		</nav>
		</header>\n
		<div id='alert'></div>";
	}

	/*
		Gets the login form and any notifications necessary
		$msg	- the message to show in a notification, if any. The message
					will appear as an error
		returns	- the HTML to show the login form and notifications
	*/
	function getLogin( $msg )
	{
		return "<main>
			<form action='login.php?loc={$_GET['loc']}' method='POST' id='login'>\n
				<div><span>Username: </span><input type='text' name='username' id='user'/></div>\n
				<div><span>Password: </span><input type='password' name='password' /></div>\n
				<input type='submit' value='Login'/>
			</form>\n
		</main>\n
		<script>
			document.getElementById('user').focus();
		</script>".notify($msg);
	}
	
	/*
		Creates previous and next buttons
		$page	- the current page (that was sent in the URL)
		$len	- the total number of items in the list (to be made
					into pages of 5 items each)
		returns	- the HTML to create previous and next buttons
	*/
	function getPrevButtons( $page = 0, $len )
	{
		$usrPge = $page + 1;
		$pgeLen = ceil($len / 5);
		$str = "";
		
		//add buttons for pages
		$prev = ($page == 0 ? "disabled" : "");
		$next = (($usrPge) * 5 >= $len ? "disabled" : "");

		$str .= "<div class='pages'><button class='page' onclick='nextPage($page, -1)' $prev>Previous</button>";
		$str .= "<p>Page $usrPge of $pgeLen</p>";
		$str .= "<button class='page' onclick='nextPage($page, 1)' $next>Next</button></div>";
		
		return $str;
	}
	
	/*
		Gets the table of products, but more consolidated for the admin to edit
		returns	- the HTML for the admin table of products
	*/
	function getAdminTable()
	{
		$db = new DB();
		$sale = $db -> getSales("!=");
		$catalogue = $db -> getSales();
		
		$str = "<h2 class='tweenTable'>Sale Items</h2>\n<table>\n
			<tr><th>&nbsp;</th><th>Name</th><th>Description</th>
			<th>Original Price</th><th>Sale Price</th><th>Quantity</th>
			<th>Image Name</th></tr>";
		
		foreach($sale as $item)
		{
			$salePr = number_format($item -> salePrice(), 2, '.', '');
			$price = number_format($item -> price(), 2, '.', '');
			$str .= "\n<tr>
				<td><a href='edit.php?id={$item -> id()}'>Edit</a></td>
				<td>{$item -> name()}</td>
				<td>{$item -> descr()}</td>
				<td>\${$price}</td>
				<td>\${$salePr}</td>
				<td>{$item -> quant()}</td>
				<td>{$item -> imgName()}</td>
			</tr>";
		}
		
		$str .= "</table>\n<h2 class='tweenTable'>Catalogue Items</h2>\n<table>
			<tr><th>&nbsp;</th><th>Name</th><th>Description</th>
			<th>Price</th><th>Quantity</th>
			<th>Image Name</th></tr>";
		
		foreach($catalogue as $item)
		{
			$price = number_format($item -> price(), 2, '.', '');
			$str .= "\n<tr>
				<td><a href='edit.php?id={$item -> id()}'>Edit</a></td>
				<td>{$item -> name()}</td>
				<td>{$item -> descr()}</td>
				<td>\${$price}</td>
				<td>{$item -> quant()}</td>
				<td>{$item -> imgName()}</td>
			</tr>";
		}
		
		$str .= "</table>";
		
		return $str;
	}
	
	/*
		Creates a form to edit an item including the existing info for the item. Can
			also create a form for adding an item to the database
		$id		- the id of the item to edit. If a new item is being added, id should be -1.
					Default is -1.
		returns	- the HTML to create a form to edit or add an item in the database
	*/
	function getEditForm( $id = -1 )
	{	
		$id = intVal($id);
		$db = new DB();
		
		//giving blank form
		if( $id == -1 || !is_int($id) )
		{
			$id = -1;
			$isSale = false;
			$name = '';
			$desc = '';
			$salePr = 0;
			$price = 0;
			$quantity = 0;
			$imgName = "<span>Image Name:</span><input name='imgName' />";
		}
		else
		{
			$item = $db -> getItem( $id );
			
			//bad id
			if( empty($item) )
			{
				//<input name='imgName' type='file' />

				$id = -1;
				$isSale = false;
				$name = '';
				$desc = '';
				$salePr = 0;
				$price = 0;
				$quantity = 0;
				$imgName = "<span>Image Name:</span><input name='imgName' />";
			}
			else
			{
				/*$imgName = "<label for='imgName'>Choose a file to upload:</label>
					<div>{$data['imgName']} <a href='js: selectImg()'>Upload New</a></div>
					<input name='imgName' value='{$data['imgName']}' style='display: none;'/>";
				*/
				$data = $item[0];
				
				$name = $data -> name();
				$desc = $data -> descr();
				$salePr = $data -> salePrice();
				$price = $data -> price();
				$quantity = $data -> quant();
				$imgName = "<span>Image Name:</span><input name='imgName' value='{$data -> imgName()}'/>";
				$isSale = $data -> salePrice() != 0;
			}
		}
		$checked = ($isSale ? "checked" : "");
		$saleInput = (!$isSale ? "style='display: none'" : "");
		$title = ($name == '' ? "Add Item" : "Edit $name");
		
		$str = "<h3>$title</h3>\n
		<form action='edit.php' method='POST' id='edit'>
			<input style='display: none' name='id' value='$id'/>\n
			<div><span>Name: </span><input name='name' value='$name'/></div>\n
			<div><span>Description: </span><textarea name='description'>$desc</textarea></div>\n
			<div><span>Quantity: </span><input name='quant' value='$quantity'/></div>\n
			<div><span>Price: </span><input name='price' value='$price'/></div>\n
			<div>
				<label for='isSale'>On Sale: </label><input type='checkbox' id='isSale' onchange='showSale()' name='isSale' $checked/>
			</div>\n
			<div id='salePriceDiv' $saleInput>
				<span>Sale Price: </span><input type='text' name='salePrice' value='$salePr'/>
			</div>\n
			<div>$imgName</div>\n
			<input type='submit' value='Save' />\n
			<input type='button' onclick='window.location=\"admin.php\"' value='Cancel'/>\n
		</form>";
		
		return $str;
	}
	//<span>Image Name: </span><input name='imgName' value='$imgName'/>
	
	/*
		Gets the end of the edit page with some notifications if necessary
		$id		- the id of the item being edited
		$msg	- the message to show as a notification (or empty string for
					no message)
	*/
	function getEditEndPage( $id, $msg )
	{
		$str = "<main>".getEditForm($id).notify($msg)."</main>
		<script>
			document.getElementsByTagName('input')[1].focus();
		</script>";
	
		$str .= footer();
		 
		return $str;
	}
	
	/*
		Creates an HTML footer for a file
		returns	- the HTML footer for a file including the end body and html tags
					but not the end main tag
	*/
	function footer()
	{
		return "<footer>By Lauren Johnston</footer>\n</body></html>";
	}

	/*
		Creates an error notification on the client screen
		$msg	- the message to display in the error
		returns	- the HTML to create the error message
	*/
	function notify($msg)
	{
		return "<script>
			$.notify('$msg', {position: 'top center', className: 'error'});
		</script>";
	}
	
	/*
		Overwrites the sales price if the isSale flag is not turned on. Accounts
			for when the UI does not show the sale price to the user, but a value is entered.
			It is assumed that validateAttrs has been run.
		$attrs	- the attributes to be checked
		returns	- an array with the changed value if necessary
	*/
	function overwriteSales( $attrs )
	{
		$temp = $attrs;
		
		if( !array_key_exists('isSale', $temp ) )
		{
			$temp['salePrice'] = 0;
		}
		
		return $temp;
	}
	
	/*
		Validates an array of attributes to be used in editing an item
		$attrs	- the attributes to validate
		returns	- 0 if there is an error, 1 if there is not
	*/
	function validateAttrs( $attrs )
	{
		//checking for correct keys
		if( !array_key_exists('id', $attrs) || !array_key_exists('name', $attrs) ||
			!array_key_exists('description', $attrs) || !array_key_exists('quant', $attrs) || 
			!array_key_exists('imgName', $attrs) || !array_key_exists('salePrice', $attrs) || 
			!array_key_exists('price', $attrs) )
		{
			return 0;
		}
		
		$id 	= $attrs['id'];
		$name 	= $attrs['name'];
		$desc 	= $attrs['description'];
		$quant 	= $attrs['quant'];
		$img 	= $attrs['imgName'];
		$price 	= $attrs['price'];
		$sale 	= $attrs['salePrice'];
		
		//checking for correct values
		if( ($id != -1 && intVal($id) == 0 && $id != 0) || ($id != -1 && intVal($id) == 1 && $id != 1) || 
			!is_string($name) || strlen($name) > 30 || !is_string($desc) || 
			intVal($quant) < 0 || (intval($quant) == 0 && $quant != 0) || !is_string($img) || 
			strlen($img) > 70 || floatVal($price) <= 0 || floatVal($sale) < 0 )
		{
			return 0;
		}

		//quant, sale price, and price
		if( (intVal($quant) == 0 && $quant != 0) ||
			(floatVal($sale) == 0 && $sale != 0) ||
			(floatVal($price) == 0 && $price != 0) )
		{
			return 0;
		}
		
		return 1;
	}

	/*
		Sanitize an input value by stripping tags and escaping SQL strings
		$param	- the input to sanitize
		returns	- the sanitized value
	*/
	function sanitize($param)
	{
		return strip_tags( $param );
	}

	/*
		Checks an id (product or user) to make sure that it's valid to query with
		$id		- the id to check
		returns - whether or not the id is valid
	*/
	function checkId($id)
	{
		if( $id < 0 )
		{
			return 0;
		}

		if( (intVal($id) == 0 && $id != 0) || (intVal($id) == 1 && $id != 1) )
		{
			return 0;
		}

		return 1;
	}

	/*
		Checks if a session is in session
		returns	- if a session is currently available and if it has a user key 
					(meaning someone logged in)
	*/
	function checkSession()
	{
		session_name('snacks');
		session_start();

		if( isset($_SESSION) && !empty($_SESSION['user']) && !empty($_SESSION['uid']) )
		{
			return 1;
		}
		
		return 0;
	}
	
	/*
		Checks if there is a value in the $_GET array for a given index
		$index	- the index to check for in the array
		returns	- the value at the given index in the $_GET array, or 
					empty string if the $_GET array is empty or unset
	*/
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
	
	/*
		Creates a heading to be used in the sections of products
		$type	- the type of product in the section the heading is for.
					Possible options are 'products', 'sales', and 'cart'.
		returns	- a heading that either says 'Catalogue', 'Sale!' or 'Your
					Cart', depending on the type. Default value is 'Lauren's
					Snack Stand'.
	*/
	function prodTableHeading( $type )
	{
		switch($type)
		{
			case "products":
				return "<h2>Catalogue</h2>";
				break;
				
			case "sales":
				return "<h2>Sale!</h2>";
				break;
				
			case "cart":
				return "<h2>Your Cart</h2>";
				break;
			
			default:
				return "<h2>Lauren's Snack Stand</h2>";
		}
	}
	
	/*
		Creates a table from product data
		$data	- the data to create the table from (array of Items)
		returns - a string holding the HTML for the product list
	*/
	function prodTable( $data )
	{
		$isCart = $data[0] -> imgName() == '';
		$str = "";
		$total = 0.0;
		$locTotal = '';

		//format data into columns
		foreach( $data as $sale )
		{
			$isProd = $sale -> salePrice() == 0;
			$star = ($isProd ? "" : "<span class='star'>&#9733;</span>");
			$img = ($isCart ? "{$data[0] -> imgName()}" :
				"<div class='prodImg'><img alt=\"{$sale -> name()}\" src='img/{$sale -> imgName()}'/></div>\n" );

			$saleNum = number_format( floatval($sale -> salePrice()), 2, '.', '' );
			$salePr = ($isProd  ? "" : "<span class='salePr'>\$$saleNum</span>\n" );
			
			$prNum = number_format( floatval($sale -> price()), 2, '.', '' );
			$pr = ( $isProd ? "<span class='price'>\$$prNum</span>\n" : 
				"<span class='priceStrike'>\$$prNum</span>\n" );

			//changes for the cart
			if( $isCart )
			{
				$price = ($isProd ? $sale -> price() : $sale -> salePrice());
				
				//keep track of totals
				$locTotal = floatVal( $price ) * intVal( $sale -> quantCart() );
				$total += $locTotal;
				
				$locTotal = "<span class='locTotal'>\$".number_format( $locTotal, 2, '.', '' )."</span>";
			
				//number in the cart
				$remaining = $sale -> quantCart()." in the cart";
			}
			else
				$remaining = ($sale -> quant() == 0 ? "None left!" : "Only {$sale -> quant()} left!");
				
			$cart = ($isCart ? "" : 
				"<button class='cart' onclick='addToCart({$sale -> id()})'>Add to Cart</button>");
	
			$str .= "<div class='prod' id='prod{$sale -> id()}'>
				$img
				<div class='prodDesc'>\n
					<h2>$star {$sale -> name()}</h2>\n
					<p>{$sale -> descr()}</p>\n
					$pr
					$salePr
					<p>$remaining</p>\n
					$cart
				</div>\n
				$locTotal
			</div>\n";
		}
		$totStr = ( $total != 0.0 ? "<span class='right'>\$".number_format( $total, 2, '.', '' )."</span>" : "" );
		$str .= $totStr;

		return $str;
	}
?>
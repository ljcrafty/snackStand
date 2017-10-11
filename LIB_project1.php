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
		return "<header>\n
		<h1>Lauren's Snack Stand</h1>\n
		<nav>
			<a href='index.php'>Home</a>\n
			<a href='cart.php'>Cart</a>\n
			<a href='login.php'>Admin</a>
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
			<form action='login.php' method='POST' id='login'>\n
				<div><span>Username: </span><input type='text' name='username' id='user'/></div>\n
				<div><span>Password: </span><input type='password' name='password' /></div>\n
				<input type='submit' value='Login'/>
			</form>\n
		</main>\n
		<script>
			document.getElementById('user').focus();
			$.notify('$msg', {position: 'top center', className: 'error'});
		</script>";
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
			$salePr = number_format($item['salePrice'], 2, '.', '');
			$price = number_format($item['price'], 2, '.', '');
			$str .= "\n<tr>
				<td><a href='edit.php?id={$item['id']}'>Edit</a></td>
				<td>{$item['name']}</td>
				<td>{$item['description']}</td>
				<td>\${$price}</td>
				<td>\${$salePr}</td>
				<td>{$item['quant']}</td>
				<td>{$item['imgName']}</td>
			</tr>";
		}
		
		$str .= "</table>\n<h2 class='tweenTable'>Catalogue Items</h2>\n<table>
			<tr><th>&nbsp;</th><th>Name</th><th>Description</th>
			<th>Price</th><th>Quantity</th>
			<th>Image Name</th></tr>";
		
		foreach($catalogue as $item)
		{
			$price = number_format($item['price'], 2, '.', '');
			$str .= "\n<tr>
				<td><a href='edit.php?id={$item['id']}'>Edit</a></td>
				<td>{$item['name']}</td>
				<td>{$item['description']}</td>
				<td>\${$price}</td>
				<td>{$item['quant']}</td>
				<td>{$item['imgName']}</td>
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
			$imgName = '';
		}
		else
		{
			$item = $db -> getItem( $id );
			
			//bad id
			if( empty($item) )
			{
				$id = -1;
				$isSale = false;
				$name = '';
				$desc = '';
				$salePr = 0;
				$price = 0;
				$quantity = 0;
				$imgName = '';
			}
			else
			{
				$data = $item[0];
				
				$name = $data['name'];
				$desc = $data['description'];
				$salePr = $data['salePrice'];
				$price = $data['price'];
				$quantity = $data['quant'];
				$imgName = $data['imgName'];
				$isSale = $data['salePrice'] != 0;
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
			<div><label for='imgName'>Choose a file to upload:</label><input name='imgName' type='file' /></div>\n
			<div><span>Price: </span><input name='price' value='$price'/></div>\n
			<div>
				<label for='isSale'>On Sale: </label><input onchange='showSale()' name='isSale' type='checkbox' $checked/>
			</div>\n
			<div id='salePriceDiv' $saleInput>
				<span>Sale Price: </span><input type='text' name='salePrice' value='$salePr'/>
			</div>\n
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
		$str = "<main>".getEditForm($id)."
			<script>
				$.notify('$msg', {position: 'top center', className: 'error'});
			</script>	
		</main>";
	
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
		
		$id = $attrs['id'];
		$name = $attrs['name'];
		$desc = $attrs['description'];
		$quant = $attrs['quant'];
		$img = $attrs['imgName'];
		$price = $attrs['price'];
		$sale = $attrs['salePrice'];
		
		//checking for correct values
		if( !is_int(intVal($id)) || !is_string($name) || strlen($name) > 30 || 
			!is_string($desc) || !is_int(intVal($quant)) || $quant < 0 ||
			!is_string($img) || !is_float(floatval($price)) || $price <= 0 || 
			!is_float(floatVal($sale)) || $sale = 0 )
		{
			return 0;
		}
		
		return 1;
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
		$data	- the data to create the table from
		returns - a string holding the HTML for the product list
	*/
	function prodTable( $data )
	{
		$isCart = !array_key_exists('imgName', $data[0]);
		$isProd = $data[0]['salePrice'] == 0;
		$str = "";
		$total = 0.0;
		$locTotal = '';

		//format data into columns
		foreach( $data as $sale )
		{
			$isProd = $sale['salePrice'] == 0;
			$star = ($isProd ? "" : "<span class='star'>&#9733;</span>");
			$img = ($isCart ? "" :
				"<div class='prodImg'><img src='img/{$sale['imgName']}'/></div>\n" );

			$saleNum = number_format( floatval($sale['salePrice']), 2, '.', '' );
			$salePr = ($isProd  ? "" : "<span class='salePr'>\$$saleNum</span>\n" );
			
			$prNum = number_format( floatval($sale['price']), 2, '.', '' );
			$pr = ( $isProd ? "<span class='price'>\$$prNum</span>\n" : 
				"<span class='priceStrike'>\$$prNum</span>\n" );

			//changes for the cart
			if( $isCart )
			{
				$price = ($isProd ? $sale['price'] : $sale['salePrice']);
				
				//keep track of totals
				$locTotal = floatVal( $price ) * intVal( $sale['quantCart'] );
				$total += $locTotal;
				
				$locTotal = "<span class='locTotal'>\$".number_format( $locTotal, 2, '.', '' )."</span>";
			
				//number in the cart
				$remaining = $sale['quantCart']." in the cart";
			}
			else
				$remaining = ($sale['quant'] == 0 ? "None left!" : "Only {$sale['quant']} left!");
				
			$cart = ($isCart ? "" : 
				"<button class='cart' onclick='addToCart({$sale['id']})'>Add to Cart</button>");
	
			$str .= "<div class='prod' id='prod{$sale['id']}'>
				$img
				<div class='prodDesc'>\n
					<h2>$star {$sale['name']}</h2>\n
					<p>{$sale['description']}</p>\n
					$pr
					$salePr
					<p>$remaining</p>\n
					$cart
				</div>\n
				$locTotal
			</div>\n";
		}
		$totStr = ( $total != 0.0 ? "<span class='right'>\$".number_format( $total, 2, '.', '' )."</span>" : "" );
		$str .= $totStr."</div>";

		return $str;
	}
?>
<?php
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
		Creates an HTML footer for a file
		returns	- the HTML footer for a file including the end body and html tags
					but not the end main tag
	*/
	function footer()
	{
		return "<footer>By Lauren Johnston</footer>\n</body></html>";
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
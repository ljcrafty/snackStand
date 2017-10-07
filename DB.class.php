<?php

class DB
{
	private $connection;

	//Create the DB object and the connection to the database
	function __construct()
	{
		try
		{
			$this -> connection = new PDO("mysql:host={$_SERVER['DB_SERVER']};dbname={$_SERVER['DB']}", 
				$_SERVER['DB_USER'], $_SERVER['DB_PASSWORD']);
				
			$this -> connection -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch( PDOException $e )
		{
			echo "Connect Failed: ".$e -> getMessage();
			die();
		}
	}
	
	/*
		Gets all of the items on sale in the database
		returns - an array of the sale items
	*/
	function getSales()
	{
		$query = "SELECT * FROM products WHERE salePrice != 0";
		return $this -> noParamQuery($query);
	}
	
	/*
		Gets all of the items on sale in the database as a table
		returns - a structured set of divs to display the sale items
	*/
	function getSaleTable()
	{
		$data = $this -> getSales();
		$str = $this -> prodTable($data);

		return $str;
	}
	
	/*
		Gets all of the items for sale that aren't on sale in the database
		returns - an array of the non-sale items
	*/
	function getProducts()
	{
		$query = "SELECT * FROM products WHERE salePrice = 0";
		return $this -> noParamQuery($query);
	}
	
	/*
		Gets all of the items for sale that aren't on sale in the database
		$page	- the page that the user requested
		returns - a structured set of divs to display the non-sale items including
					buttons for paging
	*/
	function getProdTable( $page = 0 )
	{
		$data = $this -> getProducts();
		$len = count($data);
		
		//cut the data according to page number
		$data = array_slice( $data, $page * 5, 5 );
		$str = $this -> prodTable($data);

		//add buttons for pages
		$prev = ($page == 0 ? "disabled" : "");
		$next = (($page + 1) * 5 >= $len ? "disabled" : "");

		$str .= "<button class='page' onclick='nextPage($page, -1)' $prev>Previous</button>";
		$str .= "<button class='page' onclick='nextPage($page, 1)' $next>Next</button>";

		return $str;
	}
	
	/*
		Gets a specific item for sale in the database
		$id		- the id of the item requested
		returns - one item for sale
	*/
	function getItem( $id )
	{
		$query = "SELECT * FROM products WHERE id = ?";
		
	}

	//helper functions
	/*
		Executes a query that does not require parameters
		$query	- the query you want to execute
		returns - the result of a fetchAll call on the statement;
					In other words, an array of the results
	*/
	private function noParamQuery( $query )
	{
		try
		{
			$stmt = $this -> connection -> prepare($query);
			$stmt -> execute();
			return $stmt -> fetchAll();
		}
		catch( PDOException $e )
		{
			echo "Query Error:".$e -> getMessage();
			die();
		}
	}

	/*
		Creates a table from product data
		$data	- the data to create the table from
		returns - a string holding the HTML for the product list
	*/
	private function prodTable( $data )
	{
		$isProd = $data[0]['salePrice'] == 0;
		$heading = ($isProd ? "<h2>Catalogue</h2>" : "<h2>Sale!</h2>");
		$str = ($isProd ? "<div id='products'>\n" : "<div id='sales'>\n").$heading;

		//format data into columns
		foreach( $data as $sale )
		{
			$saleNum = number_format( floatval($sale['salePrice']), 2, '.', '' );
			$salePr = ($isProd  ? "" : "<span class='salePr'>\$$saleNum</span>\n" );
			$star = ($isProd ? "" : "&#9733;");

			$prNum = number_format( floatval($sale['price']), 2, '.', '' );
			$pr = ( $isProd ? "<span class='price'>\$$prNum</span>\n" : 
				"<span class='priceStrike'>\$$prNum</span>\n" );

			$remaining = ($sale['quant'] == 0 ? "None left!" : "Only {$sale['quant']} left!");
	
			$str .= "<div class='prod'>
				<div class='prodImg'><img src='img/{$sale['imgName']}'/></div>\n
				<div class='prodDesc'>\n
					<h2>$star {$sale['name']}</h2>\n
					<p>{$sale['description']}</p>\n
					$pr
					$salePr
					<p>$remaining</p>\n
					<button class='cart' onclick='addToCart({$sale['id']})'>Add to Cart</button>
				</div>\n
			</div>\n";
		}
		$str .= "</div>";

		return $str;
	}
}
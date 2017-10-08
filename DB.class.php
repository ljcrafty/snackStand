<?php
require_once "LIB_project1.php";

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
		Gets all of the items on sale or for sale in the database
		$equals - a string containing whether the salePrice should
					equal 0, or not equal 0. Only accepted values are
					"=" and "!=". Default is "=".
		returns - an array of the sale or catalogue items
	*/
	function getSales( $equals )
	{
		if( $equals != '=' && $equals != '!=' )
		{
			$equals = '=';
		}
		
		$query = "SELECT * FROM products WHERE salePrice $equals 0";
		return $this -> query($query);
	}
	
	/*
		Gets all of the items on sale in the database as a table
		returns - a structured set of divs to display the sale items
	*/
	function getSaleTable()
	{
		$data = $this -> getSales( '!=' );
		$str = "<div id='sales'>\n".prodTableHeading('sales');
		$str .= prodTable($data);

		return $str;
	}
	
	/*
		Gets all of the items for sale that aren't on sale in the database
		$page	- the page that the user requested
		returns - a structured set of divs to display the non-sale items including
					buttons for paging
	*/
	function getProdTable( $page = 0 )
	{
		$data = $this -> getSales( '=' );
		$len = count($data);
		$pgeLen = ceil($len / 5);
		
		if($page > $pgeLen)
		{
			return '';
		}
		
		$str = "<div id='products'>\n".prodTableHeading('products');
		$str .= getPrevButtons($page, $len);
		
		//cut the data according to page number
		$data = array_slice( $data, $page * 5, 5 );
		$str .= prodTable($data);
		
		$str .= getPrevButtons($page, $len);

		return $str;
	}
	
	/*
		Gets a specific item for sale in the database
		$id		- the id of the item requested
		returns - the result set for the query
	*/
	function getItem( $id )
	{
		$query = "SELECT * FROM products WHERE id = :id";
		$params = array('id' => $id);
		
		return $this -> query($query, $params);
	}
	
	/*
		Increments the appropriate rows in the database when an item
			is added to the cart
		$id		- the id of the row to increment counts of
		returns	- the number of items left that can be added to the cart.
					-1 is returned if there was an error
	*/
	function incrementCart( $id )
	{
		$params = array('id' => $id);
		$query = "SELECT * FROM `products` LEFT JOIN `cart` ON products.id = cart.id WHERE products.id = :id";
		$result = $this -> query( $query, $params );
		
		//if empty array, the id is wrong
		if( empty($result) || $result[0]['quant'] == 0 )
		{
			return -1;
		}
		
		//check that it is in stock
		if($result[0]['quant'] > 0)
		{
			//remove from products table first
			$newQuant = $result[0]['quant'] - 1;
			$query = 'UPDATE products SET quant = :newQuant WHERE id = :id';
			$params = array( 'id' => $id, 'newQuant' => $newQuant );
			
			$numRows = $this -> query( $query, $params );
		
			//first query worked
			if( $numRows == 1 )
			{
				//product is not in cart already
				if( $result[0]['id'] == null )
				{
					$params = array('id' => $id);
					$query = 'INSERT INTO cart VALUES( :id, 1 )';
				}
				else
				{
					$newCart = $result[0]['quantCart'] + 1;
					$query = 'UPDATE cart SET quantCart = :newCart WHERE id = :id';
					$params = array( 'id' => $id, 'newCart' => $newCart );
				}	
				$result = $this -> query( $query, $params );
			
				if( $result == 1 )
					return $newQuant;
				
				//second query was unsuccessful
				return -1;
			}
			
			//first query didn't work
			return -1;
		}
		
		//there's nothing to add to the cart
		return 0;
	}
	
	/*
		Gets a table with all of the items in the cart
		returns	- HTML populated with the item details for items in the cart
					or a header saying there are no items in the cart
	*/
	function getCart()
	{
		$query = "SELECT cart.id, products.name, products.description, 
			products.price, products.salePrice, cart.quantCart
			FROM `cart` LEFT JOIN `products` 
			ON products.id = cart.id";
		$data = $this -> query($query);
		
		if( count($data) > 0 )
		{
			return prodTable($data);
		}
		else
		{
			return "";
		}
	}
	
	/*
		Clears the cart table of all of its rows
		returns	- whether or not the clear was successful
	*/
	function clearCart()
	{
		$query = "SELECT * FROM cart JOIN products ON cart.id = products.id";
		$result = $this -> query($query);
		
		foreach( $result as $row )
		{
			$query = "UPDATE products SET quant = :quant WHERE id = :id";
			$params = array('quant' => $row['quantCart'] + $row['quant'],
				'id' => $row['id']);
				
			$this -> query($query, $params);
		}
	
		$query = "DELETE FROM cart";
		$result = $this -> query($query);
		
		return $result != 0;
	}

	//helper functions
	/*
		Executes a query that does not require parameters
		$query	- the query you want to execute
		$params	- the parameters to be bound into the query. They should
					be named parameters and the key should reflect the proper names.
					Default is empty array.
		returns - the result of a fetchAll call on the statement;
					In other words, an array of the results
	*/
	private function query( $query, $params = array() )
	{
		try
		{
			$stmt = $this -> connection -> prepare($query);
			$stmt -> execute( $params );

			//check to see what function to use to send results back
			$first = explode( ' ', trim($query) )[0];

			//fetch all for selection commands
			if( strcasecmp($first, 'SELECT') == 0 )
			{
				return $stmt -> fetchAll();
			}
			else //return affected rows otherwise
			{
				return $stmt -> rowCount();
			}
			
		}
		catch( PDOException $e )
		{
			echo "Query Error:".$e -> getMessage();
			die();
		}
	}
}
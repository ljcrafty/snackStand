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
					"=" and "!=". Default is "=". '=' = prod, '!=' = sales
		returns - an array of the sale or catalogue items
	*/
	function getSales( $equals = "=" )
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
		if( $page < 0 )
		{
			return "";
		}
		$page = intVal($page);

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
		if( !checkId($id) )
		{
			return array();
		}

		$newId = intVal($id);
		$query = "SELECT * FROM products WHERE id = :id";
		$params = array('id' => intVal($newId));
		
		return $this -> query($query, $params);
	}
	
	/*
		Updates an item in the products table in the database
		$attrs	- the attributes to change in the database. Attributes
					in the parameter should be as follows:
						name: 			varchar(30)
						description: 	mediumtext
						quant:			int
						imgName:		varchar(20)
						price:			float
						salePrice:		float
						id:				tinyint(5)
		returns	- the number of rows affected by the query. 0 if the query
					failed for any reason.
	*/
	function updateItem( $attrs )
	{
		if( !validateAttrs($attrs) || $attrs['id'] < 0 || 
			!$this -> checkSaleCount($attrs['id'], $attrs['salePrice'] != 0) )
		{
			return 0;
		}
		
		$query = "UPDATE products SET name = :name, 
			description = :desc, 
			quant = :quant, 
			imgName = :img, 
			price = :price, 
			salePrice = :sale
			WHERE id = :id";
		
		//setup for params	
		$id 	= sanitize($attrs['id']);
		$name 	= sanitize($attrs['name']);
		$desc 	= sanitize($attrs['description']);
		$quant 	= sanitize($attrs['quant']);
		$img 	= sanitize($attrs['imgName']);
		$price 	= sanitize($attrs['price']);
		$sale 	= sanitize($attrs['salePrice']);
		
		$params = array('name' => strVal($name),
			'desc' 	=> strVal($desc) ,
			'quant' => intVal($quant) ,
			'img' 	=> strVal($img) ,
			'price' => floatVal($price) ,
			'sale' 	=> floatVal($sale) ,
			'id' 	=> intval($id));
		
		$result = $this -> query( $query, $params );
		return $result;
	}
	
	/*
		Adds an item in the products table in the database
		$attrs	- the attributes to add in the database. Attributes
					in the parameter should be as follows:
						name: 			varchar(30)
						description: 	mediumtext
						quant:			int
						imgName:		varchar(20)
						price:			float
						salePrice:		float
					An id attribute will be ignored and the item will be added
					as the next possible index in the database
		returns	- the number of rows affected by the query. 0 if the query
					failed for any reason.
	*/
	function addItem( $attrs )
	{
		if( !validateAttrs($attrs) || 
			!$this -> checkSaleCount($attrs['id'], $attrs['salePrice'] != 0, $attrs) )
		{
			return 0;
		}
		
		$query = "INSERT INTO products (id, name, description, quant, imgName, price, salePrice) 
			VALUES (null,
				:name,
				:desc,
				:quant,
				:imgName,
				:price,
				:salePrice
			)";
		
		$name 	= sanitize($attrs['name']);
		$desc 	= sanitize($attrs['description']);
		$quant 	= sanitize($attrs['quant']);
		$img 	= sanitize($attrs['imgName']);
		$price 	= sanitize($attrs['price']);
		$sale 	= sanitize($attrs['salePrice']);
		
		$params = array( 'name' => strVal($name),
			'desc' 		=> strVal($desc),
			'quant' 	=> intVal($quant),
			'imgName' 	=> strVal($img),
			'price' 	=> floatVal($price),
			'salePrice' => floatVal($sale));
		
		return $this -> query( $query, $params );
	}
	
	/*
		Checks to see if an update can be made while still keeping the required 3-5 sale count
		$id		- the id of the item that will be updated
		$onSale	- whether or not the item will be on sale after the update
		$attrs	- the list of attributes for the item (used when item is being inserted into
					the DB, because attributes cannot be pulled from the DB)
		returns	- whether or not the update can occur given the number of items on sale after
					the update
	*/
	function checkSaleCount( $id, $onSale, $attrs = null )
	{
		//query for items on sale
		$data = $this -> getSales('!=');
		
		$newId = intval($id);

		if( $newId < 0 )
		{
			if( $attrs == null )
				return 0;

			$cur = $attrs;
		}
		else
		{
			$cur = $this -> getItem( $newId )[0];
		}
		
		if( !$cur || !array_key_exists('salePrice', $cur) )
		{
			return 0;
		}
		
		switch( count($data) )
		{
			//short circuit for four items on sale because any change is ok
			case 4:
				return 1;
			
			//if 3 items on sale
			case 3:
				//if item is on sale and will be taken off, return 0
				if( $cur['salePrice'] != 0 && !$onSale )
				{
					return 0;
				}
				
				//otherwise ok
				return 1;
				
			//if 5 items on sale	
			case 5:
				//if item is not on sale and will be put on sale, return 0
				if( $cur['salePrice'] == 0 && $onSale )
				{
					return 0;
				}
				
				//otherwise ok
				return 1;
		}
	}
	
	/*
		Increments the appropriate rows in the database when an item
			is added to the cart
		$id		- the id of the row to increment counts of
		returns	- the number of items left that can be added to the cart.
					-1 is returned if there was an error
	*/
	function incrementCart( $id, $user )
	{
		if( !checkId($id) || !checkId($user) )
		{
			return -1;
		}

		$newId = intVal($id);
		$userId = intVal($user);

		$params = array('id' => intVal($newId));
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
			$params = array( 'id' => $newId, 'newQuant' => $newQuant );
			
			$this -> connection -> beginTransaction();
			$numRows = $this -> query( $query, $params );
		
			//first query worked
			if( $numRows == 1 )
			{
				//if there is only one row and it has no user id
				if( count($result) == 1 && $result[0]['user'] == null )
				{
					//no one has this item in their cart
					$params = array('id' => $newId, 'user' => $userId);
					$query = 'INSERT INTO cart VALUES( :id, 1, :user )';
				}
				else //someone has this item in their cart
				{
					$exists = false;
					
					//look through rows
					foreach( $result as $row )
					{
						if( $row['user'] == $userId )
						{
							$exists = true;					
						}
					}
					
					if( !$exists )//item is already in user's cart
					{
						$params = array('id' => $newId, 'user' => $userId);
						$query = 'INSERT INTO cart VALUES( :id, 1, :user )';
					}
					else//item is not already is user's cart
					{
						$newCart = $result[0]['quantCart'] + 1;
						$query = 'UPDATE cart SET quantCart = :newCart WHERE id = :id && user = :user';
						$params = array( 'id' => $newId, 'newCart' => $newCart , 'user' => $userId );
					}
				}
					
				$result = $this -> query( $query, $params );
			
				if( $result == 1 )
				{
					$this -> connection -> commit();
					return $newQuant;
				}
				
				//second query was unsuccessful
				$this -> connection -> rollBack();
				return -1;
			}
			
			//first query didn't work
			$this -> connection -> rollBack();
			return -1;
		}
		
		//there's nothing to add to the cart
		return 0;
	}
	
	/*
		Gets a table with all of the items in the cart
		$user	- the user id of the user to return the cart of
		returns	- HTML populated with the item details for items in the cart
					or a header saying there are no items in the cart
	*/
	function getCart( $user )
	{
		if( !checkId($user) )
		{
			return '';
		}
		
		$userId = intVal($user);
	
		$query = "SELECT cart.id, products.name, products.description, 
			products.price, products.salePrice, cart.quantCart
			FROM `cart` LEFT JOIN `products` 
			ON products.id = cart.id WHERE cart.user = :user";
		$params = array('user' => $userId);
		$data = $this -> query($query, $params);
		
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
		$user	- the id of the user to clear the cart for
		returns	- whether or not the clear was successful
	*/
	function clearCart($user)
	{
		if( !checkId($user) )
		{
			return false;
		}
		
		$userId = intVal($user);
	
		$query = "SELECT * FROM cart JOIN products ON cart.id = products.id WHERE user = :user";
		$params = array('user' => $userId);
		$result = $this -> query($query, $params);

		//start transaction
		$this -> connection -> beginTransaction();
		
		//replace items that were in the cart
		foreach( $result as $row )
		{
			$query = "UPDATE products SET quant = :quant WHERE id = :id";
			$params = array('quant' => $row['quantCart'] + $row['quant'],
				'id' => $row['id']);
				
			$numRows = $this -> query($query, $params);

			//transaction handling
			if( !$numRows )
			{
				$this -> connection -> rollBack();
				return false;
			}
		}
	
		$query = "DELETE FROM cart WHERE user = :user";
		$params = array('user' => $userId);
		$result = $this -> query($query, $params);
		
		//transaction handling
		if( !$result )
		{
			$this -> connection -> rollBack();
			return false;
		}

		$this -> connection -> commit();
		return $result != 0;
	}

	/*
		Gets information about a user in the users table
		$username	- the username of the row to get data for
		returns		- the row for the given username or an empty array
						if the username is not in the table
	*/
	function getUser( $username )
	{
		if( strlen($username) > 25 )
		{
			return array();
		}

		$user = sanitize( strVal($username) );

		$query = "SELECT * FROM users WHERE username = :user";
		$params = array('user' => strVal($user));

		$result = $this -> query($query, $params);

		return $result;
	}

	//helper functions
	/*
		Executes a query that does not require parameters
		$query	- the query you want to execute
		$params	- the parameters to be bound into the query. They should
					be named parameters and the key should reflect the proper names.
					Default is empty array.
		returns - the result of a fetchAll call on the statement for SELECT queries;
					In other words, an array of the results. For other queries, rowCount
					is returned, meaning the number of rows affected by the query
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
			echo "Query Error: ".$e -> getMessage();
			die();
		}
	}
}
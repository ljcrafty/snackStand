/*
	Makes a request to add an item to the cart
	id		- the id of the item to add to the cart
	returns	- nothing, but it changes the HTML to reflect the change
				and gives an alert to notify the user
*/
function addToCart( id )
{
	var http = new XMLHttpRequest();
	http.onreadystatechange = function()
	{
		if( this.readyState == 4 && this.status == 200 )
		{
			var rows = this.responseText;
			var text = '';
			
			if(rows < 0)
			{
				$.notify("A problem occurred.\n" + 
					"The product might not be in stock", {position: "top center", className: "error"});
			}
			else
			{
				//if the call was successful, update display
				if( rows > 0 )
				{
					text = "Only " + rows + " left!";
				}
				else
				{
					text = "None left!";
				}
			
				var div = document.getElementById('prod' + id);
				var parent = div.getElementsByTagName('div')[1];
				parent.getElementsByTagName('p')[1].innerHTML = text;
				
				$.notify("Added to cart!", {position: "top center", className: "success"});
			}
		}
	}
	
	http.open( "GET", "cartAdd.php?id=" + id, true );
	http.send();
}

/*
	Empty the cart of all its rows
*/
function empty()
{
	var http = new XMLHttpRequest();
	http.onreadystatechange = function()
	{
		if( this.readyState == 4 && this.status == 200 )
		{
			if( this.responseText )
			{
				var main = document.getElementsByTagName("main")[0];
				main.innerHTML = "<h2>There are no items in your cart! <a href='index.php'>Go Shopping!</a></h2>";
				$.notify("Cart emptied!", {position: "top center", className: "success"});
			}
			else
			{
				$.notify("There was a problem emptying the cart.", {position: "top center", className: "error"});
			}
		}
	}
	
	http.open( "GET", "cartEmpty.php", true );
	http.send();
}

/*
	Moves from one page of products to the next
	curPage		- the page of products the user is currently on
	direction	- the direction of the movement: 1 will move to the next page
					-1 will move to the previous page
*/
function nextPage( curPage, direction = 1 )
{
	if( (curPage == 0 && direction == -1) || curPage < 0 )
	{
		window.location = "index.php?page=0#products";
		return;
	}
	
	var pages = document.getElementsByClassName('pages')[0];
	var p = pages.getElementsByTagName('p')[0].innerHTML;
	var maxPage = parseInt( p.split(' ')[3] );
	
	if( curPage >= maxPage || (curPage == maxPage - 1 && direction == 1) )
	{
		window.location = "index.php?page=" + (maxPage - 1) + "#products";
		return;
	}
	
	window.location = "index.php?page=" + (parseInt(curPage) + 1 * direction) + "#products";
}
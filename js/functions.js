function addToCart( id )
{

}

/*
	Moves from one page of products to the next
	curPage		- the page of products the user is currently on
	direction	- the direction of the movement: 1 will move to the next page
					-1 will move to the previous page
*/
function nextPage( curPage, direction = 1 )
{
	window.location = "index.php?page=" + (parseInt(curPage) + 1 * direction) + "#products";
	
	
}
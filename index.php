<?php
	require_once "DB.class.php";
	require_once "LIB_project1.php";

	echo getHeader("Lauren's Snack Stand");
	echo nav();

	$db = new DB();
	echo '<main>'.$db -> getSaleTable();
	
	$page = ( canGet('page') ? canGet('page') : 0 );
	$table = $db -> getProdTable( $page ).'</main>';
	
	if( $table )
	{
		echo $table;
	}
	else
	{
		echo "<h2>Content Unavailable</h2>";
	}
	
	echo footer();
?>
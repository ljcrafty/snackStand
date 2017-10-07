<?php
	require_once "DB.class.php";
	require_once "LIB_project1.php";

	echo getHeader("Lauren's Snack Stand");
	echo nav();

	$db = new DB();
	echo '<main>'.$db -> getSaleTable();
	
	$page = ( empty(canGet('page')) ? 0 : canGet('page') );
	echo $db -> getProdTable( $page ).'</main>';
	
	echo footer();
?>
<?php 
	session_start();
	$sessionname = $_POST['sessionname'];
	if($sessionname != "destroy"){
		$sessionval = $_POST['sessionval'];
		$_SESSION["etrax"][$sessionname] = $sessionval;
	}else{
		session_destroy();
	}
?>
<?php
	session_start();
	if(!isset($_SESSION["etrax"]["usertype"]) && preg_match('/gesucht.jpg'.$_GET['img'])){
	header("Location: index.php");
	}
	$file = '../../../secure/data/'.$_SESSION["etrax"]["EID"].'/'. $_GET['img'];
	if($_GET['type']=='poi'){
		header('Content-Type: image/jpg');
		header('Expires: Sat, 12 Oct 1991 05:00:00 GMT');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		readfile( $file);
	}else{
		if (file_exists($file)){
			header('Content-Type: image/jpeg');
			header('Expires: Sat, 12 Sep 1996 05:00:00 GMT');
			header('Cache-Control: no-cache, no-store, must-revalidate');
			$img = file_get_contents($file);
		}else{
			$img = file_get_contents('../img/no-pic.jpg');
		}
		echo 'data:image/jpeg;base64,'.base64_encode($img);
	}
?>
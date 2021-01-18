<?php
	session_start();
	if(isset($_SESSION['stillworking']) && $_SESSION['stillworking'] > time() - 7200) {
		$_SESSION['stillworking'] = time();
		$session_data = [];
		foreach ($_SESSION['etrax'] as $key => $value) {
			$session_data[$key] = $value;
		}
		echo json_encode($session_data);
	}else{
		session_destroy();
		echo 'loggedout';
	}
?>
<?php

$value_to = $separate_it = '';
foreach($json_nodes as $key => $value){
	if (strpos($key, 'sha256_') !== false){ //strikes Hashing nach sha256
		$key = str_replace("sha256_","",$key);
		$value = hash("sha256",htmlspecialchars($value,ENT_QUOTES),false);
	}
	$value_to .= $separate_it.$key .' = "'.$value.'"';
	$separate_it = ', ';
}
$insert = $db->prepare("UPDATE $table SET $value_to WHERE $skey LIKE '$sentry'");
if(preg_match('/^[a-zA-Z0-9_]*$/', $table)){
	$insert->execute() or die(print_r($insert->errorInfo()));
}else{
	exit("Datenbank nicht erreichbar");
}

?>

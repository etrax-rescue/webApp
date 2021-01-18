<?php

$sql_query= $db->prepare("SELECT $column FROM $table where $skey LIKE :sentry");
$sql_query->bindValue(":sentry", $sentry, PDO::PARAM_STR);
if(preg_match('/^[a-zA-Z0-9_]*$/', $column) && preg_match('/^[a-zA-Z0-9_]*$/', $table)){
	$sql_query->execute() or die(print_r($sql_query->errorInfo()));
}else{
	exit("Datenbank nicht erreichbar");
}
$results = $sql_query->fetchAll(PDO::FETCH_ASSOC);
foreach($results as $result){
	$val = ($decrypt ? string_decrypt($result[$column]) : $result[$column]);
	if($column=="pois"){
		$val = substr(substr($val, 1), 0, -1);
	}
	return print_r(json_decode($val, true),true);
}

?>

<?php
$newjson = $jsondata = [];
$sql_query= $db->prepare("SELECT $column FROM $table WHERE $skey LIKE :sentry");
$sql_query->bindValue(":sentry", $sentry, PDO::PARAM_STR);
if(preg_match('/^[a-zA-Z0-9_]*$/', $column) && preg_match('/^[a-zA-Z0-9_]*$/', $table)){
	$sql_query->execute() or die(print_r($sql_query->errorInfo()));
}else{
	exit("Datenbank nicht erreichbar");
}

if($table == "user"){
	$user_array = [];
	while ($sql_json = $sql_query->fetch(PDO::FETCH_ASSOC)){
		$json = string_decrypt($sql_json[$column]);
		$json_array = json_decode($json);
		array_push($user_array,$json_array);
	}
	$json = json_encode($user_array);
	print_r($json);
}else if($column == "pois" || $column == "suchgebiete"){
	$sql_json = $sql_query->fetch(PDO::FETCH_ASSOC);
	if($sql_json[$column] != ''){
		$json = string_decrypt($sql_json[$column]);
		print_r($json);
	}else{
		$json_array = [];
		$json = json_encode($json_array);
		print_r($json);
	}
}else if($column == "personen_im_einsatz" || $column == "gruppen" || $column == "orginfo"){
	$sql_json = $sql_query->fetch(PDO::FETCH_ASSOC);
	if($sql_json[$column] != ''){
		$json_string = string_decrypt($sql_json[$column]);
		$json_array = json_decode($json_string);
		$json = [];
		foreach($json_array as $nr => $person){
			$json_data = $person ->data;
			array_push($json,$json_data[0]);
		}
		$json = json_encode($json);
	}else{
		$json_array = [];
		$json = json_encode($json_array);
	}
	print_r($json);
}else if($column == "maps" || $column == "funktionen" || $column == "suchprofildata"){
	$sql_json = $sql_query->fetch(PDO::FETCH_ASSOC);
	if($sql_json[$column] != ''){
		$json = $sql_json[$column];//string_decrypt($sql_json[$column]);
		$json_array = json_decode($json, true);
	}else{
		$json_array = array();
	}
	$json = json_encode($json_array);
	print_r($json);
}else{
	$sql_json = $sql_query->fetch(PDO::FETCH_ASSOC);
	if($sql_json[$column] != ''){
		$json = string_decrypt($sql_json[$column]);
		$json_array = json_decode($json, true);
	}else{
		$json_array = array();
	}
	$json = json_encode($json_array);
	print_r($json);
}

?>

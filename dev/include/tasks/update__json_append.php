<?php

if($column == "pois"){
						
	settype($json_nodes['geometry']['coordinates'][0], "float");
	settype($json_nodes['geometry']['coordinates'][1], "float");
	
	if($sql_json[$column] != ""){
		$json = string_decrypt($sql_json[$column]);
		$json_array = json_decode($json, true);
	}else{
		echo 'no POI';
	}
	if(isset($json_array)){
		echo 'features';
		array_push($json_array["features"],$json_nodes);
	}else{
		$json_array["type"] = "FeatureCollection";
		$json_array += ["features" => array($json_nodes)];
		print_r($json_array);
	}
}else if($column == "suchgebiete"){
		$SearchCoords = $json_nodes['geometry']['coordinates'];
		$json_nodes['geometry']['coordinates'] = [];
		$NewPoint = "";
		$NewPoints = [];
		if($values != 'Point'){
			//umwandeln der Koordinaten von String in Float
			foreach ($SearchCoords as &$coords) {
				$X = array((float)$coords[0]);
				$Y = array((float)$coords[1]);
				$NewPoint = array_merge($X, $Y);
				array_push($NewPoints,$NewPoint);
			}
			//Umgebaute Koordinaten wieder in das json schreiben
			array_push($json_nodes['geometry']['coordinates'],$NewPoints);
		}else{
			array_push($NewPoints, (float)$SearchCoords[0][0], (float)$SearchCoords[0][1]);
			$json_nodes['geometry']['coordinates'] = $NewPoints;
		}
		unset($NewPoints);
	
	if($sql_json[$column] != ""){
		$json = string_decrypt($sql_json[$column]);
		$json_array = json_decode($json, true);
	}else{
		echo 'no Searcharea';
	}
	if(isset($json_array)){
		echo 'features';
		array_push($json_array["features"],$json_nodes);
	}else{
		$json_array["type"] = "FeatureCollection";
		$json_array += ["features" => array($json_nodes)];
		print_r($json_array);
	}
}else if($column == "personen_im_einsatz" || $column == "gruppen" || $column == "funk" || $column == "protokoll" || $column == "orginfo"){
	if($sql_json[$column] != "" && !empty($sql_json[$column]) && string_decrypt($sql_json[$column]) != "null"){
		$json = string_decrypt($sql_json[$column]);
		$json_array = json_decode($json, true);
	}else{
		$json_array = [];
	}
	$values = ($values) ? $values : '0';
	array_push($json_array,['id' => $values, 'data' => array($json_nodes)]);
	echo $column;
}else{
	if($sql_json[$column] != ""){
		$json = string_decrypt($sql_json[$column]);
		$json_array = json_decode($json, true);//print_r($json_array);
	}else{
		$json_array = [];
	}
	array_push($json_array,[$values => array($json_nodes)]);//print_r($json_array);
}
//echo gettype($json_array);
$json_array = json_encode($json_array);
print_r($json_array);
$encrypted = string_encrypt($json_array);
$insert = $db->prepare("UPDATE $table SET $column = :encrypted WHERE $skey LIKE :sentry");
$insert->bindValue(":sentry", $sentry, PDO::PARAM_STR);
$insert->bindValue(":encrypted", $encrypted, PDO::PARAM_STR);
if(preg_match('/^[a-zA-Z0-9_]*$/', $column) && preg_match('/^[a-zA-Z0-9_]*$/', $table)){
	$insert->execute() or die(print_r($insert->errorInfo()));
}else{
	exit("Datenbank nicht erreichbar");
}

?>

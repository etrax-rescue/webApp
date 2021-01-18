<?php
	$json = string_decrypt($sql_json[$column]);
	$json_array = json_decode($json, true);
	
	if($column == "suchgebiete"){
		$nodeID = array_shift($json_nodes);
		$new_properties = array_replace($json_array["features"][$nodeID]["properties"], $json_nodes);
		$json_array["features"][$nodeID]["properties"] = $new_properties;
	}else if($column == "personen_im_einsatz" || $column == "gruppen" || $column == "protokoll" || $column == "orginfo"){
		foreach($json_array as $nr => $wert){
			foreach($wert as $key => $value){
				if($value == $values){
					foreach($json_nodes as $nkey => $nvalue){
						$json_array[$nr]['data'][0][$nkey] = $nvalue;
					}
				}
			}
		}
	}else if($column == "data" && $table == "settings"){
		foreach($json_array[0] as $key => $value){
			if($value == $values){
				foreach($json_nodes as $nkey => $nvalue){
					$json_array[0][$nkey] = $nvalue;
				}
			}
		}
	}else{
		foreach($json_array as $key => $value){
			if(array_key_exists($values, $json_array)) {
					$new_properties = array_replace($json_array[$key][$values][0], $json_nodes);
					$json_array[$key][$values][0] = $new_properties;
			}
		}
	}
	print_r($json_array);
	$json_array = json_encode($json_array);
	$encrypted = string_encrypt($json_array);
	$insert = $db->prepare("UPDATE $table SET $column = :encrypted WHERE $skey LIKE :sentry");
	$insert->bindValue(":encrypted", $encrypted, PDO::PARAM_STR);
	$insert->bindValue(":sentry", $sentry, PDO::PARAM_STR);
	if(preg_match('/^[a-zA-Z0-9_]*$/', $column) && preg_match('/^[a-zA-Z0-9_]*$/', $table)){
		$insert->execute() or die(print_r($insert->errorInfo()));
	}else{
		exit("Datenbank nicht erreichbar");
	}
?>

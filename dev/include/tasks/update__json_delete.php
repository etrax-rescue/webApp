<?php

if($column == "pois" || $column == "suchgebiete" || $column == "orginfo"){
	if($sql_json[$column] != ""){
		$json = string_decrypt($sql_json[$column]);
		$json_array = json_decode($json, true);
		if($column == "suchgebiete" || $column == "pois"){
			foreach($json_array["features"] as $i => $val){
				if (array_search($json_nodes, $val['properties'])) {
					$num = ($column == "pois") ? $i - 1 : $i;
					array_splice($json_array["features"],$i,1);
				}
			};
		}else{
			//array_splice($json_array["data"],array_search($json_nodes, array_keys($json_array["data"])),1);
			$ii = 0;
			$json_t = "[";
			foreach($json_array as $org_tt){
				if($json_array[$ii]["id"] != $json_nodes){
					$json_t .= '{"id":"'.$json_array[$ii]["id"].'","data":[{';
					//$json_t["id"] = $json_array[$ii]["id"];
					$iii = 0;
					foreach($json_array[$ii]['data'] as $inner){
						//$json_t[$json_array[$ii]["id"]]["data"][$key_t] = $val_t;
						foreach($json_array[$ii]['data'][$iii] as $key_t => $val_t){
							$json_t .= '"'.$key_t.'":"'.$val_t.'",';
							$iii++;
						}
						$json_t .= '"Na":"OIDA",';
					}
					$json_t = substr($json_t,0,strlen($json_t)-1)."}]},";
					
				}
				$ii++;
			}
			$json_array = substr($json_t,0,strlen($json_t)-1)."]";
		}
	}else{
		unset($json_array[0]["features"],$json_nodes);
	}
	if($column != "orginfo"){
		$json_array = json_encode($json_array);
	}
	$encrypted = string_encrypt($json_array);
}else if($column == "protokoll"){
	$json = string_decrypt($sql_json[$column]);
	$json_array = json_decode($json, true);
	array_splice($json_array,(intval($values)-1),1);print_r($json_array);
	$json_array = json_encode($json_array);
	$encrypted = string_encrypt($json_array);
}else{
	$encrypted = '';
}

$insert = $db->prepare("UPDATE $table SET $column = :encrypted WHERE $skey LIKE :sentry");
$insert->bindValue(":sentry", $sentry, PDO::PARAM_STR);
$insert->bindValue(":encrypted", $encrypted, PDO::PARAM_STR);
if(preg_match('/^[a-zA-Z0-9_]*$/', $column) && preg_match('/^[a-zA-Z0-9_]*$/', $table)){
	$insert->execute() or die(print_r($insert->errorInfo()));
}else{
	exit("Datenbank nicht erreichbar");
}
?>

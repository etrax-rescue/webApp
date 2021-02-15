<?php

function read_write_db($vars){
	
	//print_r($_GET);
	/*if($vars === false){
		$decrypt = false;
		switch($_SERVER['REQUEST_METHOD']){
			case 'POST': 
				$type = $_POST["database"]["type"];
				$action = $_POST["database"]["action"];
				$table = $_POST["database"]["table"];
				$column = $_POST["database"]["column"];
				$select = $_POST["select"];
				$json_nodes = $_POST["json_nodes"];
				$values = $_POST["values"];
			break;
			case 'GET': 
				$type = $_GET["type"];
				$action = $_GET["action"];
				$table = $_GET["table"];
				$column = $_GET["column"];
				$json_nodes = $_GET["json_nodes"];
				$values = $_GET["values"];
				$values = $_GET["values"];
				if($table == "settings"){
					$select = array("EID"=>$_GET["EID"]);
					$decrypt = true;
				}else{
					$select = $_GET["select"];
				}
			break;
		}
	}else{
		if(isset($vars)){
			$type = $vars["type"];
			$action = $vars["action"];
			$table = $vars["table"];
			$column = $vars["column"];
			$select = array("EID" => $vars["select"]);
			$json_nodes = $vars["json_nodes"];
			$values = $vars["values"];
		}else{
			echo 'keine vars';
		}
	}*/

	if(isset($vars)){
		$type = $vars["type"];
		$action = $vars["action"];
		$table = $vars["table"];
		$column = $vars["column"];
		$select = is_array($vars["select"]) ? $vars["select"] : array("EID" => $vars["select"]);
		$json_nodes = $vars["json_nodes"];
		$values = $vars["values"];
	}else{
		echo 'keine vars';
	}
			
	global $db;
	
	//print_r($select);
	switch ($action) {
		case "update":
			foreach ($select as $skey => $sentry) {
				($skey == "EID") ?
					$sql_query= $db->prepare("SELECT ".$column." FROM ".$table." where ".$skey." = ".$sentry) : 
					$sql_query= $db->prepare("SELECT ".$column." FROM ".$table." where ".$skey." LIKE '".$sentry."'");
					
				$sql_query->execute($sql_query->errorInfo());
				$sql_json = $sql_query->fetch(PDO::FETCH_ASSOC);
// ACTION update // TYPE json ///////////////////////////////////////////////////////////////////////////////////////////
				if($type == "json" ){
					if($sql_json[$column] != null){
						$userjson =string_decrypt($sql_json[$column]);
						$data = json_decode($userjson, true);
					}else{
						$data = [];
					}
					//Wert neu setzten
					foreach ($json_nodes as $key => $value) {
						if (strpos($key, 'md5_') !== false){
							$key = str_replace("md5_","",$key);
							//for ($s = '', $i = 0, $z = strlen($a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')-1; $i != 32; $x = rand(0,$z), $s .= $a[$x], $i++); 
							$s = random_bytes(32);
							$str = htmlspecialchars($value, ENT_QUOTES).$s;
							$strmd5 = md5($str);
							$value = $strmd5.':'.$s;
						}
						if (strpos($key, 'sha256_') !== false){ //strikes Hashing nach sha256
							$key = str_replace("sha256_","",$key);
							$value = hash("sha256",htmlspecialchars($value,ENT_QUOTES),false);
						}
						$data[0][$key] = $value;
					}
					//Ausgabe
					foreach ($data[0] as $key => $entry) {
						echo $key ."=>". $entry."<br>";
					}
					// in DB schreiben
					print_r($data);
					$encrypted = string_encrypt(json_encode($data, JSON_UNESCAPED_UNICODE));
					$insert = $db->prepare("UPDATE ".$table." SET ".$column." = :encrypted WHERE ".$skey." = :entry");
					$insert->bindValue(":encrypted", $encrypted, PDO::PARAM_STR);
					$insert->bindValue(":entry", $sentry, PDO::PARAM_STR);
					$insert->execute() or die(print_r($insert->errorInfo()));
					
// ACTION update // TYPE json_admin ///////////////////////////////////////////////////////////////////////////////////////////
				}elseif($type == "json_admin" ){
					if($sql_json[$column] != null){
						$userjson =string_decrypt($sql_json[$column]);
						$data = json_decode($userjson, true);
					}else{
						$data = [];
					}
					//Wert neu setzten
					foreach ($json_nodes as $key => $value) {
						if (strpos($key, 'md5_') !== false){
							$key = str_replace("md5_","",$key);
							//for ($s = '', $i = 0, $z = strlen($a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')-1; $i != 32; $x = rand(0,$z), $s .= $a[$x], $i++); 
							$s = random_bytes(32);
							$str = htmlspecialchars($value, ENT_QUOTES).$s;
							$strmd5 = md5($str);
							$value = $strmd5.':'.$s;
						}
						if (strpos($key, 'sha256_') !== false){ //strikes Hashing nach sha256
							$key = str_replace("sha256_","",$key);
							$value = hash("sha256",htmlspecialchars($value,ENT_QUOTES),false);
							echo "<br>Ich bins!<br><br>";
						}
						$data[0][$key] = $value;
					}
					//Ausgabe
					foreach ($data[0] as $key => $entry) {
						echo $key ."=>". $entry."<br>";
					}
					// in DB schreiben
					print_r($data);
					$encrypted = string_encrypt(json_encode($data, JSON_UNESCAPED_UNICODE));
					$insert = $db->prepare("UPDATE ".$table." SET ".$column." = :encrypted WHERE ".$skey." = :entry");
					$insert->bindValue(":encrypted", $encrypted, PDO::PARAM_STR);
					$insert->bindValue(":entry", $sentry, PDO::PARAM_STR);
					$insert->execute() or die(print_r($insert->errorInfo()));
					
					if(!empty($values)){ //Sobald die Values nicht leer sind, werden diese upgedated. Unabhängig davon ob als Type JSON, JSON_APPEND, JSON_DELETE oder anderes definiert wurde.
						$and = $comma = $value = "";
						$where = $skey." = '".$sentry."'";
						foreach ($values as $name => $val){
							if (strpos($name, 'md5_') !== false){
								//echo "Key: ".$name." Value: ".$val."<br>";
								$name = str_replace("md5_","",$name);
								//for ($s = '', $i = 0, $z = strlen($a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')-1; $i != 32; $x = rand(0,$z), $s .= $a[$x], $i++); 
								$s = random_bytes(32);
								$str = htmlspecialchars($val, ENT_QUOTES).$s;
								$strmd5 = md5($str);
								$val = $strmd5.':'.$s;
								//echo "Modified Key: ".$name." Modified Value: ".$val."<br>";
							}
							if (strpos($name, 'sha256_') !== false){ //strikes Hashing nach sha256
								$name = str_replace("sha256_","",$name);
								$val = hash("sha256",htmlspecialchars($val,ENT_QUOTES),false);
							}
							$value .= $comma.$name." = '".$val."'";
							$and = " AND ";
							$comma = ",";
						}
						//echo $value." WHERE ".$where;
						//schreiben
						$insert = $db->prepare("UPDATE ".$table." SET ".$value." WHERE ".$where);
						$insert->execute() or die(print_r($insert->errorInfo()));
					}
					
// ACTION update // TYPE json_append ///////////////////////////////////////////////////////////////////////////////////////////
				}else if($type == "json_append" ){
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
						if($sql_json[$column] != ""){
							$json = string_decrypt($sql_json[$column]);
							$json_array = json_decode($json, true);
						}else{
							$json_array = [];
						}
						$values = ($values) ? $values : '0';
						array_push($json_array,['id' => $values, 'data' => array($json_nodes)]);
					}else{
						if($sql_json[$column] != ""){
							$json = string_decrypt($sql_json[$column]);
							$json_array = json_decode($json, true);print_r($json_array);
						}else{
							$json_array = [];
						}
						array_push($json_array,[$values => array($json_nodes)]);print_r($json_array);
					}
					//echo gettype($json_array);
					$json_array = json_encode($json_array);
					print_r($json_array);
					$encrypted = string_encrypt($json_array);
					($skey == "EID") ? 
						$insert = $db->prepare("UPDATE ".$table." SET ".$column." = :encrypted WHERE ".$skey." = ".$sentry) : 
						$insert = $db->prepare("UPDATE ".$table." SET ".$column." = :encrypted WHERE ".$skey." = :".$sentry."");
						
					$insert->bindValue(":encrypted", $encrypted, PDO::PARAM_STR);
					$insert->execute() or die(print_r($insert->errorInfo()));
					
// ACTION update // TYPE json_update ///////////////////////////////////////////////////////////////////////////////////////////
				}else if($type == "json_update"){
					
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
					//print_r($json_array);
					$json_array = json_encode($json_array);
					$encrypted = string_encrypt($json_array);
					($skey == "EID") ?
						$insert = $db->prepare("UPDATE ".$table." SET ".$column." = :encrypted WHERE ".$skey." = ".$sentry) : 
						$insert = $db->prepare("UPDATE ".$table." SET ".$column." = :encrypted WHERE ".$skey." = :".$sentry."");
						
					$insert->bindValue(":encrypted", $encrypted, PDO::PARAM_STR);
					$insert->execute() or die(print_r($insert->errorInfo()));
// ACTION update // TYPE json_replace ///////////////////////////////////////////////////////////////////////////////////////////
				}else if($type == "json_replace"){
					
					$json = string_decrypt($sql_json[$column]);
					$json_array = json_decode($json, true);
					if($column == "suchgebiete"){
						foreach($json_array['features'] as $key => $val_array){
							$properties = $val_array['properties'];
							$property = array_search($values, $properties);
							if($property){
								foreach($json_nodes as $nkey => $nvalue){
									$val_array['properties'][$nkey] = $nvalue;
								}
								$json_array['features'][$key] = $val_array;
							}
						}
					}else if($column == "personen_im_einsatz"){
						$json_array = $json_nodes;
					}
					
					//$key = array_search($values, $json_array);
					//print_r($json_array);
					$json_array = json_encode($json_array);
					
					print_r($json_array);
					$encrypted = string_encrypt($json_array);
					($skey == "EID") ?
						$insert = $db->prepare("UPDATE ".$table." SET ".$column." = :encrypted WHERE ".$skey." = ".$sentry) : 
						$insert = $db->prepare("UPDATE ".$table." SET ".$column." = :encrypted WHERE ".$skey." = :".$sentry."");
						
					$insert->bindValue(":encrypted", $encrypted, PDO::PARAM_STR);
					$insert->execute() or die(print_r($insert->errorInfo()));
					
// ACTION update // TYPE json_delete ///////////////////////////////////////////////////////////////////////////////////////////
				}else if($type == "json_delete"){
					if($column == "pois" || $column == "suchgebiete" || $column == "orginfo"){
						if($sql_json[$column] != ""){
							$json = string_decrypt($sql_json[$column]);
							$json_array = json_decode($json, true);
							if($column == "pois"){
								array_splice($json_array["features"],$json_nodes,1);
							}else if($column == "suchgebiete"){
								array_splice($json_array["features"],$json_nodes,1);
							}else if($column == "orginfo"){
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
					echo "UPDATE ".$table." SET ".$column." = :encrypted WHERE ".$skey." = ".$sentry;
					($skey == "EID") ?
						$insert = $db->prepare("UPDATE ".$table." SET ".$column." = :encrypted WHERE ".$skey." = ".$sentry) : 
						$insert = $db->prepare("UPDATE ".$table." SET ".$column." = :encrypted WHERE ".$skey." = '".$sentry."'");
						
					$insert->bindValue(":encrypted", $encrypted, PDO::PARAM_STR);
					$insert->execute() or die(print_r($insert->errorInfo()));
// ACTION update // TYPE no-json ///////////////////////////////////////////////////////////////////////////////////////////
				}else if($type == "no-json"){
					$value_to = $separate_it = '';
					foreach($json_nodes as $key => $value){
						$value_to .= $separate_it.$key .' = "'.$value.'"';
						$separate_it = ', ';
					}
					$insert = $db->prepare('UPDATE '.$table.' SET '.$value_to.' WHERE '.$skey.' = "'.$sentry.'"');
					$insert->execute() or die(print_r($insert->errorInfo()));
					
				}
			}
		break;
		case "insert":
			//print_r($select);
			//foreach ($select as $skey => $sentry) {
				$where_t = "";
				$value_t = "";
				
// ACTION insert // TYPE json ///////////////////////////////////////////////////////////////////////////////////////////
				if($type == "json" ){
					$data = array();
					
					//Wert neu setzten
					foreach ($json_nodes as $key => $value) {
						if (strpos($key, 'md5_') !== false){
							$key = str_replace("md5_","",$key);
							//for ($s = '', $i = 0, $z = strlen($a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')-1; $i != 32; $x = rand(0,$z), $s .= $a[$x], $i++); 
							$s = random_bytes(32);
							$str = htmlspecialchars($value, ENT_QUOTES).$s;
							$strmd5 = md5($str);
							$value = $strmd5.':'.$s;
						}
						if (strpos($key, 'sha256_') !== false){ //strikes Hashing nach sha256
							$key = str_replace("sha256_","",$key);
							$value = hash("sha256",htmlspecialchars($value,ENT_QUOTES),false);
						}
						$data[0][$key] = $value;
					}
					//Ausgabe
					foreach ($data[0] as $key => $entry) {
						echo $key ."=>". $entry."<br>";
					}
					// in DB schreiben
					$encrypted = string_encrypt(json_encode($data, JSON_UNESCAPED_UNICODE));
					//$insert = $db->prepare("INSERT INTO ".$table." (".$skey.") VALUES (:encrypted)");
					$where_t .= $column.",";
					$value_t .= "'".$encrypted."',";
					/*$insert->bindValue(":encrypted", $encrypted, PDO::PARAM_STR);
					$insert->bindValue(":".$skey."", $sentry, PDO::PARAM_STR);
					$insert->execute() or die(print_r($insert->errorInfo()));*/
				}elseif($type == "newEntry" ){
					$insert = $db->prepare("INSERT INTO ".$table." (".$skey.") VALUES (:encrypted)");
					$insert->bindValue(":encrypted", $encrypted, PDO::PARAM_STR);
					$insert->bindValue(":".$skey."", $sentry, PDO::PARAM_STR);
					$insert->execute() or die(print_r($insert->errorInfo()));
				}
				
				if(!empty($values)){ //Sobald die Values nicht leer sind, werden diese upgedated. Unabhängig davon ob als Type JSON, JSON_APPEND, JSON_DELETE oder anderes definiert wurde.				
					$and = $comma = $value = "";
					//$where = $skey." = '".$sentry."'";
					foreach ($values as $name => $val){
						if (strpos($name, 'md5_') !== false){
							$name = str_replace("md5_","",$name);
							//for ($s = '', $i = 0, $z = strlen($a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')-1; $i != 32; $x = rand(0,$z), $s .= $a[$x], $i++); 
							$s = random_bytes(32);
							$str = htmlspecialchars($val, ENT_QUOTES).$s;
							$strmd5 = md5($str);
							$v = $strmd5.':'.$s;
							//$value .= $comma.$name." = '".$v."'";
							//$comma = ",";
							$where_t .= $name.",";
							$value_t .= "'".$v."',";
						}elseif (strpos($name, 'sha256_') !== false){ //strikes Hashing nach sha256
							$name = str_replace("sha256_","",$name);
							$where_t .= $name.",";
							$v = hash("sha256",htmlspecialchars($val, ENT_QUOTES),false);
							$value_t .= "'".$v."',";
							
						}else{
							//$value .= $comma.$name." = '".$val."'";
							//$comma = ",";
							if($table == 'settings' && $name != 'EID' && $name != 'typ'){
								$val = string_encrypt(json_encode($val, JSON_UNESCAPED_UNICODE));
							}
							
							$where_t .= $name.",";
							$value_t .= "'".$val."',";
						}
					}
					
					//echo $value." WHERE ".$where;
					//schreiben
					//$insert = $db->prepare("INSERT INTO ".$table." (".$where.") VALUES (".$value.")");
					//$insert->execute() or die(print_r($insert->errorInfo()));
				}
				//schreiben
					$insert = $db->prepare("INSERT INTO ".$table." (".substr($where_t,0,-1).") VALUES (".substr($value_t,0,-1).")");
					$insert->execute() or die(print_r($insert->errorInfo()));
					
			//}
		break;
		case "read":
			foreach ($select as $skey => $sentry) {
				//echo $key ."=>". $entry."<br>";
				
// ACTION read // TYPE json ///////////////////////////////////////////////////////////////////////////////////////////
				if($type == "json"){
					$newjson = $jsondata = [];
					if($skey == "EID"){
					$sql_query= $db->prepare("SELECT ".$column." FROM ".$table." where ".$skey." = ".$sentry);
					}else{
					$sql_query= $db->prepare("SELECT ".$column." FROM ".$table." where ".$skey." LIKE '".$sentry."'");
					}
					$sql_query->execute($sql_query->errorInfo());
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
				}else{
					$sql_query= $db->prepare("SELECT ".$column." FROM ".$table." where ".$skey." LIKE '".$sentry."'");
					$sql_query->execute($sql_query->errorInfo());
					$results = $sql_query->fetchAll(PDO::FETCH_ASSOC);
					foreach($results as $result){
						$val = ($decrypt ? string_decrypt($result[$column]) : $result[$column]);
						if($column=="pois"){
							$val = substr(substr($val, 1), 0, -1);
						}
						print_r(json_decode($val, true));
					}
				}
			}
		break;
		case "read_return":
			foreach ($select as $skey => $sentry) {
				//echo $key ."=>". $entry."<br>";
				
// ACTION read // TYPE json ///////////////////////////////////////////////////////////////////////////////////////////
				if($type == "json"){
					$newjson = $jsondata = [];
					if($skey == "EID"){
					$sql_query= $db->prepare("SELECT ".$column." FROM ".$table." where ".$skey." = ".$sentry);
					}else{
					$sql_query= $db->prepare("SELECT ".$column." FROM ".$table." where ".$skey." LIKE '".$sentry."'");
					}
					$sql_query->execute($sql_query->errorInfo());
					$sql_query->execute($sql_query->errorInfo());
					if($table == "user"){
						$user_array = [];
						while ($sql_json = $sql_query->fetch(PDO::FETCH_ASSOC)){
							$json = string_decrypt($sql_json[$column]);
							$json_array = json_decode($json);
							array_push($user_array,$json_array);
						}
						$json = json_encode($user_array);
						return print_r($json,true);
					}else if($column == "pois" || $column == "suchgebiete"){
						$sql_json = $sql_query->fetch(PDO::FETCH_ASSOC);
						if($sql_json[$column] != ''){
							$json = string_decrypt($sql_json[$column]);
							return print_r($json,true);
						}else{
							$json_array = [];
							$json = json_encode($json_array);
							return print_r($json,true);
						}
					}else if($column == "personen_im_einsatz" || $column == "gruppen" || $column == "orginfo"){
						$sql_json = $sql_query->fetch(PDO::FETCH_ASSOC);
						if($sql_json[$column] != ''){
							$json_string = string_decrypt($sql_json[$column]);
							$json_array = json_decode($json_string);
							$json = [];
							if(!empty($json_array)){
								foreach($json_array as $nr => $person){
									$json_data = $person ->data;
									array_push($json,$json_data[0]);
								}
							}
							$json = json_encode($json);
						}else{
							$json_array = [];
							$json = json_encode($json_array);
						}
						return print_r($json,true);
					}else if($column == "maps"){
						$sql_json = $sql_query->fetch(PDO::FETCH_ASSOC);
						if($sql_json[$column] != ''){
							$json = $sql_json[$column];
							$json_array = json_decode($json, true);
						}else{
							$json_array = array();
						}
						$json = json_encode($json_array);
						return print_r($json,true);
					}else{
						$sql_json = $sql_query->fetch(PDO::FETCH_ASSOC);
						if($sql_json[$column] != ''){
							$json = string_decrypt($sql_json[$column]);
							$json_array = json_decode($json, true);
						}else{
							$json_array = array();
						}
						$json = json_encode($json_array);
						return print_r($json,true);
					}
				}else{
					$sql_query= $db->prepare("SELECT ".$column." FROM ".$table." where ".$skey." LIKE '".$sentry."'");
					$sql_query->execute($sql_query->errorInfo());
					$results = $sql_query->fetchAll(PDO::FETCH_ASSOC);
					foreach($results as $result){
						$val = ($decrypt ? string_decrypt($result[$column]) : $result[$column]);
						if($column=="pois"){
							$val = substr(substr($val, 1), 0, -1);
						}
						return print_r(json_decode($val, true),true);
					}
				}
			}
		break;
		case "delete":
			//die in $select übergebenen Werte werden zur Auswahl von WHERE genützt um eine exakte Auswahl in der DB zu ermöglichen
			if($select){	
				$where = "";
				$x = 0;
				foreach ($select as $skey => $sentry) {
					if($x >0 ){
						$where = $where." AND ".$skey." = '".$sentry."'";
					} else {
						$where = "".$skey." = '".$sentry."'";
					}
					$x++;
				}
				$delete = $db->prepare("DELETE FROM ".$table." WHERE ".$where);
				$delete->execute() or die(print_r($delete->errorInfo()));
			} else {
				echo "Es wurde kein Feld zur Identifikation angegeben!";
			}
		break;
		case "mysql_update":
			//die in $select übergebenen Werte werden zur Auswahl von WHERE genützt um eine exakte Auswahl in der DB zu ermöglichen
			if(isset($select)){	
				$where = "";
				$x = 0;
				foreach ($select as $skey => $sentry) {
					if($x >0 ){
						$where = $where." AND ".$skey." = '".$sentry."'";
					} else {
						$where = "".$skey." = '".$sentry."'";
					}
					$x++;
				}
				$delete = $db->prepare("UPDATE ".$table." SET ".$column." = ".$values." WHERE ".$where);
				$delete->execute() or die(print_r($delete->errorInfo()));
			} else {
				echo "Es wurde kein Feld zur Identifikation angegeben!";
			}
		break;
	}
}
?>

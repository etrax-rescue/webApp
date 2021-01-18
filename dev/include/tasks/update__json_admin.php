<?php

if($sql_json[$column] != null){
	$userjson =string_decrypt($sql_json[$column]);
	$data = json_decode($userjson, true);
}else{
	$data = [];
}
//Wert neu setzten
foreach ($json_nodes as $key => $value) {
	if (strstr($key, 'md5_') !== false){ //strikes Hashing nach sha256
		$key = str_replace("md5_","",$key);
		for ($s = '', $i = 0, $z = strlen($a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')-1; $i != 32; $x = rand(0,$z), $s .= $a[$x], $i++); 
		$str = htmlspecialchars($value, ENT_QUOTES).$s;
		$strmd5 = md5($str);
		$value = $strmd5.':'.$s;
		$data[0][$key] = $value;
	}elseif (strstr($key, 'sha256_') !== false){
		$key = str_replace("sha256_","",$key);
		$value = hash("sha256",htmlspecialchars($value,ENT_QUOTES),false);
		$insert = $db->prepare("UPDATE $table SET  $key = '$value' WHERE $skey LIKE '$sentry'");
		if(preg_match('/^[a-zA-Z0-9_]*$/', $table)){
			$insert->execute() or die(print_r($insert->errorInfo()));
		}else{
			exit("Datenbank nicht erreichbar");
		}
	}elseif (strstr($key, 'insert_') !== false){echo $key;
		$key = str_replace("insert_","",$key);
		echo "UPDATE $table SET  $key = $value WHERE $skey LIKE $sentry";
		$insert = $db->prepare("UPDATE $table SET  $key = '$value' WHERE $skey LIKE '$sentry'");
		if(preg_match('/^[a-zA-Z0-9_]*$/', $table)){
			$insert->execute() or die(print_r($insert->errorInfo()));
		}else{
			exit("Datenbank nicht erreichbar");
		}
	}else{
		$data[0][$key] = $value;
	}
}
//Ausgabe
/*foreach ($data[0] as $key => $entry) {
	echo $key ."=>". $entry."<br>";
}*/
// in DB schreiben
//print_r($data);
$encrypted = string_encrypt(json_encode($data, JSON_UNESCAPED_UNICODE));
$insert = $db->prepare("UPDATE $table SET $column = :encrypted WHERE $skey Like :entry");
$insert->bindValue(":encrypted", $encrypted, PDO::PARAM_STR);
$insert->bindValue(":entry", $sentry, PDO::PARAM_STR);
if(preg_match('/^[a-zA-Z0-9_]*$/', $column) && preg_match('/^[a-zA-Z0-9_]*$/', $table)){
	$insert->execute() or die(print_r($insert->errorInfo()));
}else{
	exit("Datenbank nicht erreichbar");
}

if(!empty($values)){ //Sobald die Values nicht leer sind, werden diese upgedated. Unabhängig davon ob als Type JSON, JSON_APPEND, JSON_DELETE oder anderes definiert wurde.
	$and = $comma = $value = "";
	$where = $skey." = '".$sentry."'";
	foreach ($values as $name => $val){
		if (strpos($key, 'md5_') === true){ //strikes Hashing nach sha256
			$key = str_replace("md5_","",$key);
			for ($s = '', $i = 0, $z = strlen($a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')-1; $i != 32; $x = rand(0,$z), $s .= $a[$x], $i++); 
			$str = htmlspecialchars($value, ENT_QUOTES).$s;
			$strmd5 = md5($str);
			$value = $strmd5.':'.$s;
			$data[0][$key] = $value;
		}elseif (strpos($key, 'sha256_') === true){
			$key = str_replace("sha256_","",$key);
			$value = hash("sha256",htmlspecialchars($value,ENT_QUOTES),false);
			$insert = $db->prepare("UPDATE $table SET  $key = '$value' WHERE $skey LIKE '$sentry'");
			if(preg_match('/^[a-zA-Z0-9_]*$/', $table)){
				$insert->execute() or die(print_r($insert->errorInfo()));
			}else{
				exit("Datenbank nicht erreichbar");
			}
		}elseif (strpos($key, 'insert_') === true){
			$key = str_replace("insert_","",$key);
			echo "UPDATE $table SET  $key = $value WHERE $skey LIKE $sentry";
			$insert = $db->prepare("UPDATE $table SET  $key = '$value' WHERE $skey LIKE '$sentry'");
			if(preg_match('/^[a-zA-Z0-9_]*$/', $table)){
				$insert->execute() or die(print_r($insert->errorInfo()));
			}else{
				exit("Datenbank nicht erreichbar");
			}
		}else{
			$data[0][$key] = $value;
		}
	}
	//echo $value." WHERE ".$where;
	//schreiben
	$insert = $db->prepare("UPDATE ".$table." SET ".$value." WHERE ".$where);
	if(preg_match('/^[a-zA-Z0-9_]*$/', $column) && preg_match('/^[a-zA-Z0-9_]*$/', $table)){
		$insert->execute() or die(print_r($insert->errorInfo()));
	}
}
?>

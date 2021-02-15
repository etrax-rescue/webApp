<?php

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
	$encrypted = string_encrypt(json_encode($data, JSON_UNESCAPED_UNICODE));
	$insert = $db->prepare("UPDATE $table SET $column = :encrypted WHERE $skey LIKE :sentry");
	$insert->bindValue(":encrypted", $encrypted, PDO::PARAM_STR);
	$insert->bindValue(":sentry", $sentry, PDO::PARAM_STR);
	if(preg_match('/^[a-zA-Z0-9_]*$/', $column) && preg_match('/^[a-zA-Z0-9_]*$/', $table)){
		$insert->execute() or die(print_r($insert->errorInfo()));
	}else{
		exit("Datenbank nicht erreichbar");
	}
?>

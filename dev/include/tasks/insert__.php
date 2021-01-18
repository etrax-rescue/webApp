<?php

//Sobald die Values nicht leer sind, werden diese upgedated. Unabhängig davon ob als Type JSON, JSON_APPEND, JSON_DELETE oder anderes definiert wurde.				
$and = $comma = $value = "";
//$where = $skey." = '".$sentry."'";
foreach ($values as $name => $val){
	if (strpos($name, 'md5_') !== false){
		$name = str_replace("md5_","",$name);
		for ($s = '', $i = 0, $z = strlen($a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')-1; $i != 32; $x = rand(0,$z), $s .= $a[$x], $i++); 
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
$insert = $db->prepare("INSERT INTO ".$table." (".substr($where_t,0,-1).") VALUES (".substr($value_t,0,-1).")");
if(preg_match('/^[a-zA-Z0-9_]*$/', $table)){
	$insert->execute() or die(print_r($insert->errorInfo()));
}else{
	exit("Datenbank nicht erreichbar");
}

?>

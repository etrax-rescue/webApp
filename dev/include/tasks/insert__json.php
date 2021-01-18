<?php

$data = array();
					
//Wert neu setzten
foreach ($json_nodes as $key => $value) {
	if (strpos($key, 'md5_') !== false){
		$key = str_replace("md5_","",$key);
		for ($s = '', $i = 0, $z = strlen($a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')-1; $i != 32; $x = rand(0,$z), $s .= $a[$x], $i++); 
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
$where_t .= $column.",";
$value_t .= "'".$encrypted."',";
$insert = $db->prepare("INSERT INTO ".$table." (".substr($where_t,0,-1).") VALUES (".substr($value_t,0,-1).")");
if(preg_match('/^[a-zA-Z0-9_]*$/', $column) && preg_match('/^[a-zA-Z0-9_]*$/', $table)){
	$insert->execute() or die(print_r($insert->errorInfo()));
}else{
	exit("Datenbank nicht erreichbar");
}

?>

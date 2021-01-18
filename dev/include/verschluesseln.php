<?php
$workstation_sql = $db->prepare("SELECT ID,TAN FROM workstations");
$workstation_sql->execute($workstation_sql->errorInfo());
while ($row = $workstation_sql->fetch(PDO::FETCH_ASSOC)){
	$wTAN = $row['TAN'];
	$wID = $row['ID'];
}
$securestring = $dbinfo[1].$wID.$wTAN;

function qs($x) {
	while(9<$x) {
		$x = (int)($x/10) + $x%10;
	}
	return $x;
}

function decryptdb($val,$table,$field) {
	global $tobecrypted, $securestring, $secretkey, $secretiv;
	$datafield =$table.".".$field; 
	$encrypt = in_array($datafield, $tobecrypted) ? true : false;
	if($encrypt){
		$output = string_decrypt($val);
		$value = explode("|%|",$output);
		if(isset($value[1])){
			$val = $value[1];
			return $val;
		}else{
			return $val;
		}
	}else{
		return $val;
	}
}

// Neue Encryption
$cipher = "aes-256-cbc";

function string_encrypt($string) {
	global $cipher, $secretkey;
	// Get the iv length required for the cipher method
	$ivlen = openssl_cipher_iv_length($cipher);
	// Create an iv with the length of ivlen
	$iv = random_bytes($ivlen);
	// Encrypt the provided string
	$encrypted = openssl_encrypt($string, $cipher, $secretkey, $options=0, $iv);
	// Prepend the base64 encoded iv to the encrypted data separated with a ":" as delimiter
	$encrypted = base64_encode($iv).":".$encrypted;
	return $encrypted;
}

function string_decrypt($string) {
	global $cipher, $secretkey;
	// Split the provided string on the ":" delimiter
	$exploded_string = explode(":", $string);
	// Get the iv
	$iv = base64_decode($exploded_string[0]);
	if(isset($exploded_string[1])){
		// Get the encrypted data
		$encrypted_data = $exploded_string[1];
		// Decrypt the data
		$decrypted = openssl_decrypt($encrypted_data, $cipher, $secretkey, $options=0, $iv);
		return $decrypted;
	} else {
		return "";
	}
}

// Ende

function encrypt($string) {
	global $secretiv, $secretkey;
	$output = false;
	$encrypt_method = "AES-256-CBC";
	// hash
	$key = hash('sha256', $secretkey);

	// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
	$iv = substr(hash('sha256', $secretiv), 0, 16);
	$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
	$output = base64_encode($output);
	return $output;
}

function decrypt($string) {
	global $secretiv, $secretkey;
	$output = false;
	$encrypt_method = "AES-256-CBC";
	// hash
	$key = hash('sha256', $secretkey);

	// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
	$iv = substr(hash('sha256', $secretiv), 0, 16);
	$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
	return $output;
}

?>
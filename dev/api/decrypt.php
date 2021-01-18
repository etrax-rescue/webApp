<?php
session_start();
require "../../../secure/info.inc.php";
require "../../../secure/secret.php";
require "../include/verschluesseln.php";
require_once '../vendor/autoload.php';
use Ahc\Jwt\JWT;
try {
	$token = $_SESSION["etrax"]["token"] ?? '';
	(new JWT($jwtsecret, 'HS256', 86400, 10))->decode($token);
} catch (\Ahc\Jwt\JWTException $e) {
	exit("Datenbank nicht erreichbar");
}
$file = $_GET['href'];
$eid = $_GET['eid'];
if($eid != ""){
	if(file_exists("../../../secure/data/".$eid."/".$file.".txt")){
		$encrypted_txt = file_get_contents("../../../secure/data/".$eid."/".$file.".txt");
	}else{
		echo "Error: ".$eid."/".$file.".txt existiert nicht";
	}
}else{
	if(file_exists("../../../secure/data/".$file.".txt")){
		$encrypted_txt = file_get_contents("../../../secure/data/".$file.".txt");
	}else{
		echo "Error: ".$file.".txt existiert nicht";
	}
}
$decrypted_txt = decrypt($encrypted_txt);
echo $decrypted_txt;
?>
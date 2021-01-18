<?php 
//Dieses Script erneuert die SESSION Werte und setzt weiter unten die User spezifischen Schreib- und Leserechte wenn eine EID im Cookie definiert ist.

//if (htmlspecialchars($_POST["sessionstart"], ENT_QUOTES)) {
if (!empty($_POST["sessionstart"])) {
	//Das ist der Fall wenn die Funktion per AJAX aufgerufen wird
	session_start();
	//$_SESSION["etrax"] = $_SESSION["etrax"];
	if(file_exists("../../../secure/info.inc.php") && !function_exists('qs')){
		require "../../../secure/info.inc.php";
		require "../../../secure/secret.php";
		require "../include/verschluesseln.php";
	}elseif(file_exists("../../../../secure/info.inc.php") && !function_exists('qs')){
		require "../../../../secure/info.inc.php";
		require "../../../../secure/secret.php";
		require "../../include/verschluesseln.php";
	}
}
//Berechtigungen für den User vorerst auf false setzen.
$USER["dev"] = $USER["einsatzleitung"] = $USER["gleich"] = $USER["zeichnen"] = $USER["zuweisen"] = $USER["lesen"] = false;
	
if(isset($_SESSION["etrax"]["UID"])){
	if($_SESSION["etrax"]["UID"] != ""){
		//Userdaten aus DB holen und Session neu schreiben
		$user_sql = $db->prepare("SELECT EID,OID,UID,FID,username,data FROM user WHERE UID = '".$_SESSION["etrax"]["UID"]."'");
		$user_sql->execute($user_sql->errorInfo());
		$rowuser = $user_sql->fetch(PDO::FETCH_ASSOC);
		$userdata_decrypted = json_decode(substr(string_decrypt($rowuser["data"]), 1, -1));
		$_SESSION["etrax"]["adminEID"] = $rowuser["EID"];
		$_SESSION["etrax"]["adminOID"] = $_SESSION["etrax"]["OID"] = $rowuser["OID"];
		$_SESSION["etrax"]["adminID"] = $_SESSION["etrax"]["UID"] = $_SESSION["etrax"]["id"] = $rowuser["UID"];
		$_SESSION["etrax"]["FID"] = $rowuser["FID"];
		$_SESSION["etrax"]["name"] = isset($userdata_decrypted->name) ? $userdata_decrypted->name : "";
		$_SESSION["etrax"]["dienstnummer"] = isset($userdata_decrypted->dienstnummer) ? $userdata_decrypted->dienstnummer : "";
		$FID = explode(".",$rowuser["FID"]);
		if(isset($FID[1]) && is_numeric($FID[1]) && is_numeric($FID[0])){
			$_SESSION["etrax"]["userlevel"] = $FID[0];
			$_SESSION["etrax"]["userrechte"] = $FID[1];
			$_SESSION["etrax"]["usertype"] = "administrator";
			$_SESSION["etrax"]["etraxadmin"] = true;
			$_SESSION["etrax"]["mapadmin"] = true;
			//setcookie("mapadmin", true, 0);
			
		}else{
			$_SESSION["etrax"]["userlevel"] = 10; //Falls irgendwas in der FID steht wird userlevel auf 10 gesetzt (Standard User)
			$_SESSION["etrax"]["userrechte"] = false; //Falls irgendwas in der FID steht wird userrechte auf false gesetzt
			$_SESSION["etrax"]["etraxadmin"] = false;
			$_SESSION["etrax"]["mapadmin"] = false;
			$_SESSION["etrax"]["usertype"] = "registered";
			//echo '<script>window.location.href = "einsatzwahl.php"</script>';
			
		}
		//Schreibrechte für Einsatz festlegen. Voraussetzung: im Cookie ist EID gesetzt
		$USER["dev"] = $USER["einsatzleitung"] = $USER["gleich"] = $USER["zeichnen"] = $USER["zuweisen"] = $USER["lesen"] = false; // Alle Rechte auf false setzen - 2020-11-02
		
		if(isset($_SESSION["etrax"]["EID"])) {
			if(is_numeric($_SESSION["etrax"]["EID"])&& $_SESSION["etrax"]["EID"] >= 0){
				$EID = $_SESSION["etrax"]["EID"];
				$OID_admin = $_SESSION["etrax"]["adminOID"];
					
				//$einsatz_query = $db->prepare("SELECT OID,Ogleich,Ozeichnen,Ozuweisen,Osehen FROM settings WHERE EID = ".$EID."");
				$einsatz_query = $db->prepare("SELECT data FROM settings WHERE EID = ".$EID."");
				$einsatz_query->execute($einsatz_query->errorInfo());
				$einsatz = $einsatz_query->fetch(PDO::FETCH_ASSOC);
				$settings_data_json = json_decode(substr(string_decrypt($einsatz['data']), 1, -1));
				/*$OID = $einsatz['OID'];
				$Ogleich = $einsatz['Ogleich'];
				$Ozeichnen = $einsatz['Ozeichnen'];
				$Ozuweisen = $einsatz['Ozuweisen'];
				$Osehen = $einsatz['Osehen'];
				*/
				if($settings_data_json != ''){
					$OID = $settings_data_json->OID;
					$Ogleich = $settings_data_json->Ogleich;
					$Ozeichnen = $settings_data_json->Ozeichnen;
					$Ozuweisen = $settings_data_json->Ozuweisen;
					$Osehen = $settings_data_json->Osehen;
					
					//Alle OIDs die irgendwie im Einsatz teilnehmen
					$oids_t = explode(",",$OID.",".$Ogleich.",".$Ozeichnen.",".$Ozuweisen.",".$Osehen);
					$oids_t = array_unique(array_filter($oids_t));
					
					//Festlegen der Userrechte über Organisation und individuelle Rechte
					$USER["dev"] = $USER["einsatzleitung"] = $USER["gleich"] = $USER["zeichnen"] = $USER["zuweisen"] = $USER["lesen"] = false;
					if(in_array($OID_admin,$oids_t) || $OID_admin == "DEV" ){ //Ist die OID des Users nicht im Einsatz kann der User keine Rechte bekommen
						if($_SESSION["etrax"]["userrechte"] === false || $_SESSION["etrax"]["userrechte"] == ""){ //Hat ein user keine Adminrechte, bekommt er false gesetzt und soll in diese schleife nicht hineinkommen
							$USER["lesen"] = $USER["dev"] = $USER["einsatzleitung"] = $USER["gleich"] = $USER["zeichnen"] = $USER["zuweisen"] = false;
							
						} else {
							switch($_SESSION["etrax"]["userrechte"]) {
								case "0": //DEV User
									$USER["dev"] = $USER["einsatzleitung"] = $USER["gleich"] = $USER["zeichnen"] = $USER["zuweisen"] = $USER["lesen"] = true; //DEV User bekommen alle Rechte gesetzt
									
								break;
								case "1": //Einsatzleitung für eigene Organisation
									$USER["einsatzleitung"] = $USER["gleich"] = $USER["zeichnen"] = $USER["zuweisen"] = $USER["lesen"] = true; //
									$USER["dev"] = false;
									
								break;
								case "2": //Alle Rechte
									$USER["gleich"] = $USER["zeichnen"] = $USER["zuweisen"] = $USER["lesen"] = true; //
									$USER["dev"] = $USER["einsatzleitung"] = false;
									
								break;
								case "3": //Suchgebiet zeichnen und zuweisen
									$USER["zeichnen"] = $USER["zuweisen"] = $USER["lesen"] = true; //
									$USER["dev"] = $USER["einsatzleitung"] = $USER["gleich"] = false;
									
								break;
								case "4": //Suchgebiet zuweisen
									$USER["zuweisen"] = $USER["lesen"] = true; //
									$USER["dev"] = $USER["einsatzleitung"] = $USER["gleich"] = $USER["zeichnen"] = false;
									
								break;
								case "5": //Nur lesen
									$USER["lesen"] = true; //
									$USER["dev"] = $USER["einsatzleitung"] = $USER["gleich"] = $USER["zeichnen"] = $USER["zuweisen"] = false;
									
								break;
							}
						} 
							
						$oids_t = explode(",",$OID);
						$oids_t = array_unique(array_filter($oids_t));
						if(!in_array($OID_admin,$oids_t)) { //Organisation ist nicht Primärorganisation
							$USER["einsatzleitung"] = false;
							
							$oids_t = explode(",",$Ogleich);
							$oids_t = array_unique(array_filter($oids_t));
							if(!in_array($OID_admin,$oids_t)) { //Organisation ist nicht gleichberechtigt
								$USER["einsatzleitung"] = $USER["gleich"] = false;
							}
							
							$oids_t = explode(",",$Ozeichnen);
							$oids_t = array_unique(array_filter($oids_t));
							if(!in_array($OID_admin,$oids_t)) { //Organisation darf nicht Suchgebiete zeichnen
								$USER["einsatzleitung"] = $USER["gleich"] = $USER["zeichnen"] = false;
							}
							
							$oids_t = explode(",",$Ozuweisen);
							$oids_t = array_unique(array_filter($oids_t));
							if(!in_array($OID_admin,$oids_t)) { //Organisation darf nicht Suchgebiete zuweisen
								$USER["einsatzleitung"] = $USER["gleich"] = $USER["zuweisen"] = false;
							}
							
							$oids_t = explode(",",$Osehen);
							$oids_t = array_unique(array_filter($oids_t));
							if(!in_array($OID_admin,$oids_t)) { //Organisation darf nicht lesen/sehen
								$USER["einsatzleitung"] = $USER["gleich"] = $USER["zeichnen"] = $USER["zuweisen"] = $USER["lesen"] = false;
							}
							
							if($OID_admin == "DEV"){
								$USER["dev"] = $USER["einsatzleitung"] = $USER["gleich"] = $USER["zeichnen"] = $USER["zuweisen"] = $USER["lesen"] = true; //DEV User bekommen alle Rechte gesetzt
							}
						}
					}
				}
			}
		}
		//Schreiben der Leserechte
		$_SESSION["etrax"]["USER"]["dev"] = $USER["dev"];
		$_SESSION["etrax"]["USER"]["einsatzleitung"] = $USER["einsatzleitung"];
		$_SESSION["etrax"]["USER"]["gleich"] = $USER["gleich"];
		$_SESSION["etrax"]["USER"]["zeichnen"] = $USER["zeichnen"];
		$_SESSION["etrax"]["USER"]["zuweisen"] = $USER["zuweisen"];
		$_SESSION["etrax"]["USER"]["lesen"] = $USER["lesen"];
	} else { //Das sollte nur ggf. Fehler abdecken und daher die Session löschen.
		$_SESSION["etrax"] = [];
	}
} else { //Das sollte nur ggf. Fehler abdecken und daher die Session löschen.
	$_SESSION["etrax"] = [];
}

?>
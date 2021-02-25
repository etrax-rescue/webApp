<?php
session_start();
require "../../../secure/info.inc.php";
require "../../../secure/secret.php";
require "../include/verschluesseln.php";
require "../include/rwdb.php";

$EID = $_POST['EID'];
$adminUID = $_POST['UID'];
$trackingbeginn = intval($_POST['trackstart']);
$counterBOS = false;
$is_DEV = $adminUID == "DEV" ? true : false;


$hiddentracks = isset($_SESSION["etrax"]["hiddentracks"]) ? $_SESSION["etrax"]["hiddentracks"] : [];
//print_r($hiddentracks);

$token = $ORG_status = $OIDs = [];
$org_query= $db->prepare("SELECT OID,token,status FROM organisation");
$org_query->execute() or die(print_r($org_query->errorInfo(), true));
while ($org_data = $org_query->fetch(PDO::FETCH_ASSOC)){
	$token[$org_data['OID']] = $org_data['token'];
	$OIDs[$org_data['token']] = $org_data['OID'];
	$ORG_status[$org_data['OID']] = $org_data['status'];
	if($org_data['status'] != null){
		$statusjson = $org_data['status'];
		$ORGstatus = json_decode($statusjson, true);
		$ORG_status[$org_data['OID']] = $ORGstatus['all'];
	}
}

$status = $UserimEinsatz = $userdata = $User = $gruppenID = $protokoll = $gruppenimEinsatz = [];
$valid_status = false;
$sql_query = $db->prepare("SELECT data,personen_im_einsatz,gruppen,protokoll FROM settings WHERE EID =".$EID);
$sql_query->execute() or die(print_r($sql_query->errorInfo(), true));
while ($sql_json = $sql_query->fetch(PDO::FETCH_ASSOC)){
	if($sql_json['gruppen'] != null){
		$gruppenjson = string_decrypt($sql_json['gruppen']);
		$gruppenimEinsatz = json_decode($gruppenjson, true);
		foreach($gruppenimEinsatz as $group){
			$gruppenID[$group['id']] = $group['data'][0];
		}
	}
	if($sql_json['protokoll'] != null){
		$protokolljson = string_decrypt($sql_json['protokoll']);
		$protokollimEinsatz = json_decode($protokolljson, true);
		foreach($protokollimEinsatz as $proto){
			array_push($protokoll, $proto);
		}
	}
	if($sql_json['data'] != null){
		$datajson = string_decrypt($sql_json['data']);
		$Einsatzdata = json_decode($datajson, true);
		foreach($Einsatzdata as $Edata){
			$OID_prim = $Edata['OID'];
			$OIDs_im_Einsatz = (substr($Edata['Osehen'], 0, 1) == ',') ?  substr($Edata['Osehen'], 1): $Edata['Osehen'];
		}
	}
	if($sql_json['personen_im_einsatz'] != null){
		$json = string_decrypt($sql_json['personen_im_einsatz']);
		$UserimEinsatz = json_decode($json, true);
		foreach($UserimEinsatz as $user){
			$personen_im_einsatz[$user['id']] = $user['data'];
		}
	}
}

$userjson = '';
$user_dnr = $user_bos = $members = $member = [];
//$OIE = str_replace($OIDs_im_Einsatz,",","','");
$user_query= $db->prepare("SELECT UID,OID,data FROM user");//  WHERE OID IN ('".$OIE."')
$user_query->execute() or die(print_r($user_query->errorInfo(), true));
while ($user_data = $user_query->fetch(PDO::FETCH_ASSOC)){
	if($user_data['data'] != null){
		$datajson = string_decrypt($user_data['data']);
		$userdata = json_decode($datajson, true);
		foreach($userdata as $data){
			$members[] = $data;
			$member_bos[$user_data['UID']] = (isset($data['bos'])) ? $data['bos'] : '';
			$member_dnr[$user_data['UID']] = (isset($data['dienstnummer'])) ? $data['dienstnummer'] : '';
			foreach($data as $key => $val){
				$member[$user_data['UID']][$key] = (isset($val)) ? $val : '';
			}
		}
	}
}
//echo array_search("21020", $member_dnr).'<br>';
//var_dump($personen_im_einsatz);
//var_dump($member);

$userstatus = $db->prepare("SELECT ID,EID,OID,UID,lon,lat,status,token,nummer FROM status WHERE OID = ''");
$userstatus->execute() or die(print_r($userstatus->errorInfo()));
while($rowstatus = $userstatus->fetch(PDO::FETCH_ASSOC)){
	$OID = $rowstatus['OID'] != '' ? $rowstatus['OID'] : $OIDs[$rowstatus['token']];
	$UID = $rowstatus['UID'] != '' ? $rowstatus['UID'] : (array_search($rowstatus['nummer'], $member_dnr) ? array_search($rowstatus['nummer'], $member_dnr) : array_search($rowstatus['nummer'], $member_bos));
	$EID = (empty($rowstatus['EID']) || $rowstatus['EID'] == 0) ? $EID : $rowstatus['EID'];
	$status = $rowstatus['status'];
	$lon = $rowstatus['lon'];
	$lat = $rowstatus['lat'];
	// Versuchen die letzt Position für die App holen
	if($lon == "" || $lat == ""){
		$coords_query= $db->prepare("SELECT lat,lon FROM tracking WHERE herkunft = 'APP' AND EID = '".$EID."' AND token = '".$rowstatus['token']."' AND nummer = '".$rowstatus['nummer']."' order by timestamp desc LIMIT 1");
		$coords_query->execute() or die(print_r($coords_query->errorInfo(), true));
		while ($coords_data = $coords_query->fetch(PDO::FETCH_ASSOC)){
			$lat = $coords_data['lat'];
			$lon = $coords_data['lon'];
		}
	}
	$dienstnummer = $member[$UID]['dienstnummer'];
	$name = $member[$UID]['name'];
	$phone = $member[$UID]['telefon'];
	$email = $member[$UID]['email'];
	$bos = $member[$UID]['bos'];
	$typ = $member[$UID]['typ'];
	$pause = $member[$UID]['pause'];
	$sender = "active";
	if(isset($personen_im_einsatz[$UID])){
		$update_type = "json_update";
		$gruppe = $personen_im_einsatz[$UID][0]['gruppe'] ? $personen_im_einsatz[$UID][0]['gruppe'] : 0;
		$aktivierungszeit = $personen_im_einsatz[$UID][0]['aktivierungszeit'] ? $personen_im_einsatz[$UID][0]['aktivierungszeit'] : time();
		$eingerueckt = $personen_im_einsatz[$UID][0]['eingerueckt'] ? $personen_im_einsatz[$UID][0]['eingerueckt'] : '';
		$inPause = $personen_im_einsatz[$UID][0]['inPause'] ? $personen_im_einsatz[$UID][0]['inPause'] : '';
		$ausPause = $personen_im_einsatz[$UID][0]['ausPause'] ? $personen_im_einsatz[$UID][0]['ausPause'] : '';
		$abgemeldet = $personen_im_einsatz[$UID][0]['abgemeldet'] ? $personen_im_einsatz[$UID][0]['abgemeldet'] : '';
	}else{
		$update_type = "json_append";
		$gruppe = "0";
		$aktivierungszeit = time();
		$eingerueckt = $inPause = $ausPause = $abgemeldet = '';
	}
	$prot_id = count($protokoll);
	$use_in_protokoll = $ORG_status[$OID][$status]["doku"];
	$also_tracking = $ORG_status[$OID][$status]["tracking"];
	$text_status = $ORG_status[$OID][$status]["text"];
	$text_read = true;
	$gName = $gruppe != "0" ? " ".$gruppenID[$gruppe]["name"] : " keine Gruppenbezeichnung";
	$text_tracking = $also_tracking > 0 ? "<br><a href='javascript:;' class='centerPOI' data-coords-lon='".$lon."' data-coords-lat='".$lat."'><i class='material-icons'>search</i> Lat: ".$lat." Lon: ".$lon."</a>" : '';
	$text_phone = $phone != '' ? " Telefon: ".$phone : "";
	$text_bos = $bos != '' ? " BOS: ".$bos : "";
	$text_callback = 'Sprechwunsch Einsatzleitung '.$name.$text_bos.$text_phone.$text_tracking;
	$text_protokoll =  "Mitglied: ".$name.'<br>'.$gName.$text_bos.$text_phone.$text_tracking;
	$poi_text =  $name.' '.$gName;

	
	if(strpos($OIDs_im_Einsatz, $OID) !== false){// Nur Organisationen die im Einsatz sind dürfen schreiben
		$insert_status = $db->prepare("UPDATE status SET OID = '$OID', EID = $EID, UID = '$UID' WHERE ID = ".$rowstatus['ID']);
		$insert_status->execute() or die(print_r($insert_status->errorInfo()));
		if($ORG_status[$OID][$status]){

			//Status Zuweisungen
			$user_status = ["1","2","3","9","10","11"];
			$gruppen_status = ["4","5","6","7","8"];
			$message_status = ["12","13","14","15"];
			
			//Userstatusänderung
			if (in_array($status, $user_status)) {
				$geht_in_einsatz = false;
				switch($status){
					case 1:
						if($abgemeldet == ""){
							if($inPause != "" && $ausPause == ""){
								$ausPause = time();
							}
						}else{
							$inPause = $abgemeldet;
							$ausPause = time();
							
						}
						$geht_in_einsatz = true;
					break;
					case 2:
						$gruppe = "0";
						$abgemeldet = "";
						$geht_in_einsatz = true;
					break;
					case 3:
						$gruppe = "0";
						$abgemeldet = "";
						$geht_in_einsatz = true;
					break;
					case 9;
						$gruppe = "0";
						$abgemeldet = "";
						$inPause = time();
						$geht_in_einsatz = true;
					break;
					case 10:
						$gruppe = "0";
						$abgemeldet = "";
						$geht_in_einsatz = true;
					break;
					case 11:
						$gruppe = "";
						$abgemeldet = time();
						//aktiveEID löschen
						$update = $db->prepare("UPDATE user SET aktiveEID='' WHERE UID= ? AND OID = ? ");
						$update->bindParam(1, $UID, PDO::PARAM_STR);
						$update->bindParam(2, $OID, PDO::PARAM_STR);
						$update->execute() or die(print_r($update->errorInfo()));
					break;
				}
				$json = array("UID" => "".$UID."", "OID" => "".$OID."", "dienstnummer" => "".$dienstnummer."", "name" => "".$name."", "phone" => "".$phone."", "email" => "".$email."", "bos" => "".$bos."", "typ" => "".$typ."", "pause" => ($pause/60), "sender" => $sender, "gruppe" => $gruppe, "status" => $status, "aktivierungszeit" => $aktivierungszeit, "eingerueckt" => $eingerueckt, "inPause" => $inPause, "ausPause" => $ausPause, "abgemeldet" => $abgemeldet);
				$vars = array("table"=>"settings","column"=>"personen_im_einsatz","action"=>"update","type"=>$update_type,"select"=>$EID,"values"=>$UID,"json_nodes"=>$json);
				ob_start(); //Unterdrückt die Ausgabe von read_write_db();
					read_write_db($vars);
				ob_end_clean();
				if($geht_in_einsatz){
					//aktiveEID setzten
					$update = $db->prepare("UPDATE user SET `aktiveEID`= ?  WHERE `UID`= ? AND OID = ? ");
					$update->bindParam(1, $EID, PDO::PARAM_STR);
					$update->bindParam(2, $UID, PDO::PARAM_STR);
					$update->bindParam(3, $OID, PDO::PARAM_STR);
					$update->execute() or die(print_r($update->errorInfo()));
					$geht_in_einsatz = false;
				}
			}elseif(in_array($status, $gruppen_status)) {
				//Gruppenstatusänderung
				if(isset($gruppenID[$gruppe]["aktuellerStatus"])){
					$gruppenstatus = ['rückt aus','beginnt Suche','Suche beendet','wartet auf Transport','rückt ein'];
					$text_status = $text_status != '' ? $text_status : $gruppenstatus[$status - 4];
					$status_aktuell = $gruppenID[$gruppe]["aktuellerStatus"];
					$status_history = $gruppenID[$gruppe]["status"];
					$status_history .= $status_aktuell."&&".date("Y-m-d H:i:s").";";
					$status_nodes = array("aktuellerStatus" => $text_status, "zeit" => date("Y-m-d H:i:s"), "status" => $status_history);
					$vars = array('table'=>'settings','action'=>'update','type'=>'json_update','column'=>'gruppen','select'=>$EID,'values'=>$gruppe,'json_nodes'=>$status_nodes);
					ob_start();
					read_write_db($vars);
					ob_end_clean();
					$searcharea_nodes = array('status'=>$text_status);
					$searcharea_vars = array('table'=>'settings','action'=>'update','type'=>'json_replace','column'=>'suchgebiete','select'=>$EID,'values'=>'e_group-'.$gruppenID[$gruppe]["id"], 'json_nodes'=>$searcharea_nodes);
					ob_start();
					read_write_db($searcharea_vars);
					ob_end_clean();
				}
				
			}elseif(in_array($status, $message_status)) {//Benachrichtigungen mit "Pushnofitification" für als Overlay des Kartenfensters
				$text_read = false;
			}
			if($use_in_protokoll){
				//Wenn die OID nicht die der primären Organisation ist, wird noch ein zweiter Eintrag mit der OID der Primärorganisation geschrieben
				if($OID_prim != $OID && $OID_prim != "" && $status > 11 && $status < 15){	
					$message = array("id" => $prot_id, "oid" => $OID, "type" => "protokoll", "phone" => $phone, "bos" => $bos, "name" => $name, "read" => $text_read, "betreff" => $text_status, "deleted" => "", "text" => $text_protokoll, "zeit" => date("Y-m-d H:i:s"));
					$vars = array('table'=>'settings','action'=>'update','type'=>'json_append','column'=>'protokoll','select'=>$EID,'values'=>$prot_id,'json_nodes'=>$message);
					ob_start();
						read_write_db($vars);
					ob_end_clean();
				}else{
					$message = array("id" => $prot_id, "oid" => $OID, "type" => "protokoll", "phone" => $phone, "bos" => $bos, "name" => $name, "read" => $text_read, "betreff" => $text_status, "deleted" => "", "text" => $text_protokoll, "zeit" => date("Y-m-d H:i:s"));
					$vars = array('table'=>'settings','action'=>'update','type'=>'json_append','column'=>'protokoll','select'=>$EID,'values'=>$prot_id,'json_nodes'=>$message);
					ob_start();
						read_write_db($vars);
					ob_end_clean();
				}
				//Bei Personenfund wird ein POI gesetzt
				if($status > 11 && $status < 15){
					echo $lat, $lon;
					$poi = array(
						"type" => "Feature", 
						"properties" => array(
							"oid" => $OID, 
							"uid" => $UID, 
							"name" => $text_status, 
							"color" => "#c00", 
							"beschreibung" => $poi_text, 
							"img" => "", 
							"poi" => time()*1000
						), 
						"geometry" => array(
							"type" => "Point", 
							"coordinates" => array(
								"0" => $lon, 
								"1" => $lat
							)
						)
					);
					$vars = array('table'=>'settings','action'=>'update','type'=>'json_append','column'=>'pois','select'=>$EID,'values'=>'','json_nodes'=>$poi);
					//print_r($vars);
					ob_start(); //Unterdrückt die Ausgabe von read_write_db();
						read_write_db($vars);
					ob_end_clean();
				}
			}
		}
	}
}

?>

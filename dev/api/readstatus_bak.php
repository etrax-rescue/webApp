<?php
session_start();
require "../../../secure/info.inc.php";
require "../../../secure/secret.php";
require "../include/verschluesseln.php";
require "../include/rwdb.php";

$EID = $_POST['EID'];
$trackingbeginn = intval($_POST['trackstart']);
$counterBOS = false;


$hiddentracks = isset($_SESSION["etrax"]["hiddentracks"]) ? $_SESSION["etrax"]["hiddentracks"] : [];
//print_r($hiddentracks);

$token = [];
$org_query= $db->prepare("SELECT OID,token,status FROM organisation");
$org_query->execute($org_query->errorInfo());
while ($org_data = $org_query->fetch(PDO::FETCH_ASSOC)){
	$token[$org_data['OID']] = $org_data['token'];
	$ORG_status[$org_data['OID']] = $org_data['status'];
	if($org_data['status'] != null){
		$statusjson = $org_data['status'];
		$ORGstatus = json_decode($statusjson, true);
		$ORG_status[$org_data['OID']] = $ORGstatus['all'];
	}
}


$status = $UserimEinsatz = $userdata = $User = $gruppen = $protokoll = $gruppenimEinsatz = [];
$valid_status = false;
$sql_query = $db->prepare("SELECT data,personen_im_einsatz,gruppen,protokoll FROM settings WHERE EID =".$EID);
$sql_query->execute($sql_query->errorInfo());
while ($sql_json = $sql_query->fetch(PDO::FETCH_ASSOC)){
	if($sql_json['gruppen'] != null){
		$gruppenjson = string_decrypt($sql_json['gruppen']);
		$gruppenimEinsatz = json_decode($gruppenjson, true);
		foreach($gruppenimEinsatz as $group){
			array_push($gruppen, $group['data'][0]);
		}
	}
	if($sql_json['protokoll'] != null){
		$protokolljson = string_decrypt($sql_json['protokoll']);
		$protokollimEinsatz = json_decode($protokolljson, true);
		foreach($protokollimEinsatz as $proto){
			array_push($protokoll, $proto['data'][0]);
		}
	}
	if($sql_json['data'] != null){
		$datajson = string_decrypt($sql_json['data']);
		$Einsatzdata = json_decode($datajson, true);
		foreach($Einsatzdata as $Edata){
			$OID_prim = $Edata['OID'];
		}
	}
	//var_dump($gruppen);
	// Vars
	
	$gName = $Ugroup = "";

	if($sql_json['personen_im_einsatz'] != null){
		$json = string_decrypt($sql_json['personen_im_einsatz']);
		$UserimEinsatz = json_decode($json, true);
		foreach($UserimEinsatz as $user){
			$userdata = $user['data'];
			$gName = "keine Gruppenbezeichnung";
			array_push($User, $user['data'][0]);
			$userdata = $user['data'][0];
			$DNR = $userdata['dienstnummer'];
			$nummer = (isset($userdata['bos']) && $userdata['bos'] != "") ? $userdata['bos'] : $DNR;
			$OID = $userdata['OID'];
			$UID = $userdata['UID'];
			$name = $userdata['name'];
			$telefon = $userdata['phone'];
			$email = $userdata['email'];
			$bos = $userdata['bos'];
			$typ = $userdata['typ'];
			$pause = $userdata['pause'];
			
			$userstatus = $db->prepare("SELECT ID,EID,OID,UID,lon,lat,status,token,nummer FROM status WHERE nummer = '".$nummer."'");
			$userstatus->execute() or die(print_r($userstatus->errorInfo()));
			while ($rowstatus = $userstatus->fetch(PDO::FETCH_ASSOC)){
				if(in_array($rowstatus['token'],$token)){
					//$valid_track = true;
					
					if(empty($rowstatus['EID']) || $rowstatus['EID'] == 0){
						$insert_tracking = $db->prepare("UPDATE status SET EID = '" . $EID . "' WHERE ID = ".$rowstatus['ID']);
						$insert_tracking->execute() or die(print_r($insert_tracking->errorInfo()));
					}
					if(empty($rowstatus['OID'])){
						$insert_tracking = $db->prepare("UPDATE status SET OID = '" . $OID . "' WHERE ID = ".$rowstatus['ID']);
						$insert_tracking->execute() or die(print_r($insert_tracking->errorInfo()));
					}
					if(empty($rowstatus['UID'])){
						$insert_tracking = $db->prepare("UPDATE status SET UID = '" . $UID . "' WHERE ID = ".$rowstatus['ID']);
						$insert_tracking->execute() or die(print_r($insert_tracking->errorInfo()));
					}
				}
				//echo array_search($rowstatus["UID"], $UserimEinsatz);
				//Schnittstellentext auf Status übersetzen
				echo 'in Array? '.in_array($rowstatus["UID"], $UID);
					if($ORG_status[$OID][$rowstatus['status']]){
						//Statusmeldungen:
						$setuserstatus = $setanmeldung = $setabmeldung = $setpause = $setgruppenstatus = $sendmessage = $search_pie = $search_user = false;
						$protokolltext = $statustext = "";
						
						//User zum Einsatz anmelden
						if(in_array($rowstatus['status'], [1,9])){								
							//Prüfen ob der User im Einsatz existiert
							/*if(in_array($rowstatus["UID"], $UID)){
								//Mitglied ist noch nicht im Einsatz
								$anmelden = array("UID" => "".$UID."", "OID" => "".$OID."", "dienstnummer" => "".$DNR."", "name" => "".$name."", "phone" => "".$telefon."",	"email" => "".$email."", "bos" => "".$bos."", "typ" => "".$typ."", "pause" => ($pause/60), "sender" => "", "gruppe" => "0", "status" => $rowstatus['status'], "aktivierungszeit" => "".time()."", "eingerueckt" => "", "inPause" => "", "ausPause" =>"", "abgemeldet" => "");
								$vars = array("table"=>"settings","column"=>"personen_im_einsatz","action"=>"update","type"=>"json_append","select"=>$EID,"values"=>$UID,"json_nodes"=>$anmelden);
								read_write_db($vars);
								//aktiveEID setzen
								$update = $db->prepare("UPDATE user SET `aktiveEID`= ?  WHERE `UID`= ? AND OID = ? ");
								$update->bindParam(1, $EID, PDO::PARAM_STR);
								$update->bindParam(2, $UID, PDO::PARAM_STR);
								$update->bindParam(3, $OID, PDO::PARAM_STR);
								$update->execute() or die(print_r($update->errorInfo()));
							}else{ //User ist angemeldet und in Pause --> Pause wird beendet
								$loggedout_time = $userdata['abgemeldet'];
								if($loggedout_time == ""){
									if($userdata["inPause"] != "" && $userdata["ausPause"] == ""){
										//Mitglied in Pause stellen = Timestamp für inPause setzen
										$pause = array("gruppe" => "0", "status" => $rowstatus['status'], "ausPause" => "".time()."");
										$vars = array('table'=>'settings','action'=>'update','type'=>'json_update','column'=>'personen_im_einsatz','select'=>$EID,'values'=>$UID,'json_nodes'=>$pause);
										read_write_db($vars);
									}
								}else{
									$pause = array("gruppe" => "0", "status" => $rowstatus['status'], "inPause" => $loggedout_time, "ausPause" => "".time()."", "abgemeldet" => "");
									$vars = array('table'=>'settings','action'=>'update','type'=>'json_update','column'=>'personen_im_einsatz','select'=>$EID,'values'=>$UID,'json_nodes'=>$pause);
									read_write_db($vars);
								}
							}*/
						}
						
					}
				$status = "";
					
			}
		}
	}
}

?>

<?php
session_start();
require "../../../secure/info.inc.php";
require "../../../secure/secret.php";
require "../include/verschluesseln.php";
require "../pdf/coordinates.php";
require "../include/rwdb.php";

$EID = $_POST['EID'];
$strokewidth = $_SESSION["etrax"]["strokewidth"];
$Einsatz_anfang = date($_POST['anfang']);
$maxspeed = $_POST['maxspeed'];
$minspeed = $_POST['minspeed'];
$minpunkte = isset($_POST['minpunkte']) ? intval($_POST['minpunkte']) : 5;
$trackingbeginn = intval($_POST['trackstart']);
$trackpause = $_POST['trackpause'];
$trackreloading = intval($_POST['newtrackloading'])/1000;
$counterBOS = false;


$hiddentracks = isset($_SESSION["etrax"]["hiddentracks"]) ? $_SESSION["etrax"]["hiddentracks"] : [];
//print_r($hiddentracks);

function entfernung($lat, $lon, $lastlat, $lastlon){
	$utm = ll2utm($lat,$lon);
	$utmlast = ll2utm($lastlat,$lastlon);
	$dx = $utm["x"] - $utmlast["x"];
	$dy = $utm["y"] - $utmlast["y"];
	$dist = pow($dx, 2) + pow($dy, 2);
	$entfernung = sqrt($dist);
	return $entfernung;
}
$token = [];
$org_query= $db->prepare("SELECT token FROM organisation");
$org_query->execute() or die(print_r($org_query->errorInfo(), true));
while ($org_data = $org_query->fetch(PDO::FETCH_ASSOC)){
	array_push($token, $org_data['token']);
}
				
//User im Einsatz holen
$EinsatzGruppen = $color = $usergruppe = $groupID = $status = $UserimEinsatz = $GPStracker = $userdata = [];
function MitgliederimEinsatz(){
	global $db, $EID, $UserimEinsatz, $GPStracker, $EinsatzGruppen, $GPStracker, $userdata_arr;
	$i = 0;
	$sql_query = $db->prepare("SELECT gruppen,personen_im_einsatz FROM settings WHERE EID =".$EID);
	$sql_query->execute() or die(print_r($sql_query->errorInfo(), true));
	while ($sql_json = $sql_query->fetch(PDO::FETCH_ASSOC)){
		if($sql_json['gruppen'] != null){ // Keine Gruppen, kein Tracking
			$json = string_decrypt($sql_json['gruppen']);
			$gruppen_infos = json_decode($json, true);
			foreach($gruppen_infos as $groups){
					//array_push($EinsatzGruppen, $groups);
					$EinsatzGruppen[$groups["id"]] = $groups;
			}
			if($sql_json['personen_im_einsatz'] != null){
				$json = string_decrypt($sql_json['personen_im_einsatz']);
				$UserimEinsatz = json_decode($json, true);
				foreach($UserimEinsatz as $user){
					foreach($user['data'] as $userdata){
						//if($userdata['gruppe'] != '' && $userdata['gruppe'] != '0' && $userdata['abgemeldet'] == '' && ($userdata['inPause'] == '' || ($userdata['inPause'] != '' && $userdata['ausPause'] != '') )){ //Wenn der User keiner Gruppe zugewiesen ist, nicht abgemeldet und nicht in Pause ist!
						if($userdata['gruppe'] != '' && ($userdata['gruppe'] != '0' || isset($userdata['zugewiesen'])) && $userdata['abgemeldet'] == '' && ($userdata['inPause'] == '' || ($userdata['inPause'] != '' && $userdata['ausPause'] != '') )){ //Wenn der User einer Gruppe zugewiesen ist bzw. wenn die Gruppe 0 (keine Zuweiseung) ist,d ann muss der User schon mal zugewiesen gewesen sein. Ist der Pausenwert , nicht abgemeldet und nicht in Pause ist!
							array_push($GPStracker, $user['data'][0]);
						}
						//Userdaten in array schreiben
						$userdata_arr[$userdata["UID"]]["UID"] = isset($userdata["UID"]) ? $userdata["UID"] : "";
						$userdata_arr[$userdata["UID"]]["OID"] = isset($userdata["OID"]) ? $userdata["OID"] : "";
						$userdata_arr[$userdata["UID"]]["dienstnummer"] = isset($userdata["dienstnummer"]) ? $userdata["dienstnummer"] : "";
						$userdata_arr[$userdata["UID"]]["name"] = isset($userdata["name"]) ? $userdata["name"] : "";
						$userdata_arr[$userdata["UID"]]["phone"] = isset($userdata["phone"]) ? $userdata["phone"] : "";
						$userdata_arr[$userdata["UID"]]["email"] = isset($userdata["email"]) ? $userdata["email"] : "";
						$userdata_arr[$userdata["UID"]]["bos"] = isset($userdata["bos"]) ? $userdata["bos"] : "";
						$userdata_arr[$userdata["UID"]]["typ"] = isset($userdata["typ"]) ? $userdata["typ"] : "";
						$userdata_arr[$userdata["UID"]]["pause"] = isset($userdata["pause"]) ? $userdata["pause"] : "";
						$userdata_arr[$userdata["UID"]]["sender"] = isset($userdata["sender"]) ? $userdata["sender"] : "";
						$userdata_arr[$userdata["UID"]]["ausbildungen"] = isset($userdata["ausbildungen"]) ? $userdata["ausbildungen"] : "";
						$userdata_arr[$userdata["UID"]]["gruppe"] = isset($userdata["gruppe"]) ? $userdata["gruppe"] : "";
						$userdata_arr[$userdata["UID"]]["status"] = isset($userdata["status"]) ? $userdata["status"] : "";
						$userdata_arr[$userdata["UID"]]["aktivierungszeit"] = isset($userdata["aktivierungszeit"]) ? $userdata["aktivierungszeit"] : "";
						$userdata_arr[$userdata["UID"]]["eingerueckt"] = isset($userdata["eingerueckt"]) ? $userdata["eingerueckt"] : "";
						$userdata_arr[$userdata["UID"]]["inPause"] = isset($userdata["inPause"]) ? $userdata["inPause"] : "";
						$userdata_arr[$userdata["UID"]]["ausPause"] = isset($userdata["ausPause"]) ? $userdata["ausPause"] : "";
						$userdata_arr[$userdata["UID"]]["abgemeldet"] = isset($userdata["abgemeldet"]) ? $userdata["abgemeldet"] : "";
						$userdata_arr[$userdata["UID"]]["zugewiesen"] = isset($userdata["zugewiesen"]) ? $userdata["zugewiesen"] : "";
					}
				}
			}
		}
	}
	return ['EinsatzGruppen', 'GPStracker', 'userdata_arr'];
}
MitgliederimEinsatz();

//Vervollständigung der Trackingdaten
foreach ($GPStracker as $key => $userdata) {
	$trackheader = $trackcoords = $comma = $BOSnr = $OID = $UID = $sender = $DNR = $rGID = $groupID = $group_status = '';
	//Überprüfen ob die Tracker Gruppen zugewiesen sind
	if($userdata['gruppe'] != ""){
		$BOSnr = (isset($userdata['bos']) && $userdata['bos'] != "") ? $userdata['bos'] : "";
		$OID = $userdata['OID'];
		$UID = $userdata['UID'];
		$sender = $userdata['sender'];
		$DNR = $userdata['dienstnummer'];
		$groupID = $userdata['gruppe'];//$EinsatzGruppen[$rGID]['data'][0]['id'];
		$rGID = intval($userdata['gruppe']) - 1;
		$counterBOS = true;
		//Auswahl der Trackingdaten, die fehlende EID, OID, UID oder gruppen Werte haben.
		if($BOSnr != ""){
			$usertracks = $db->prepare("SELECT ID,EID,OID,UID,lon,lat,timestamp,timestamp_server,herkunft,speed,gruppe,nummer,token FROM tracking WHERE timestamp > ".$trackingbeginn." AND ((UID LIKE '".$UID."') OR (nummer LIKE '".$BOSnr."') OR (nummer LIKE '".$DNR."')) AND CONVERT(hdop,UNSIGNED INTEGER) < 70 AND (EID = '' OR OID = '' OR UID = '' OR gruppe = '') ORDER BY timestamp DESC");
		}else{	
			$usertracks = $db->prepare("SELECT ID,EID,OID,UID,lon,lat,timestamp,timestamp_server,herkunft,speed,gruppe,nummer,token FROM tracking WHERE timestamp > ".$trackingbeginn." AND ((UID LIKE '".$UID."') OR (nummer LIKE '".$DNR."')) AND CONVERT(hdop,UNSIGNED INTEGER) < 70 ORDER BY timestamp DESC");
		}
		$usertracks->execute() or die(print_r($usertracks->errorInfo(), true));
		
		$track_loop = 0;
		while ($rowtracks = $usertracks->fetch(PDO::FETCH_ASSOC)){
			
			//$track_timestamp = $rowtracks['timestamp']/1000;
			//Trackingdaten die über die BOS Schnittstelle kommen, haben keine EID, OID und UID. Diese wird hier vergeben
			if($rowtracks['herkunft'] == "BOS"){
				if(empty($rowtracks['EID']) || $rowtracks['EID']== 0){
					$insert_tracking = $db->prepare("UPDATE tracking SET EID = '" . $EID . "' WHERE ID = ".$rowtracks['ID']);
					$insert_tracking->execute() or die(print_r($insert_tracking->errorInfo()));
				}
				if(empty($rowtracks['OID']) || $rowtracks['OID']== 0){
					$insert_tracking = $db->prepare("UPDATE tracking SET OID = '" . $OID . "' WHERE ID = ".$rowtracks['ID']);
					$insert_tracking->execute() or die(print_r($insert_tracking->errorInfo()));
				}
				if(empty($rowtracks['UID'])){
					$insert_tracking = $db->prepare("UPDATE tracking SET UID = '" . $UID . "' WHERE ID = ".$rowtracks['ID']);
					$insert_tracking->execute() or die(print_r($insert_tracking->errorInfo()));
					$sendet = ($sender == 'active') ? array("sender" => "active") : array("sender" => "inactive");
					$vars = array('table'=>'settings','action'=>'update','type'=>'json_update','column'=>'personen_im_einsatz','select'=>$EID,'values'=>$UID,'json_nodes'=>$sendet);
					ob_start(); //Unterdrückt die Ausgabe von read_write_db();
						read_write_db($vars);
					ob_end_clean();
				}
			}
			//Zuweisung der Gruppen ID zum jeweiligen Eintrag in der Table
			if(empty($rowtracks['gruppe'])){
				$insert_tracking = $db->prepare("UPDATE tracking SET gruppe = '" . $groupID . "' WHERE ID = ".$rowtracks['ID']." AND UID = '".$UID."'");
				$insert_tracking->execute() or die(print_r($insert_tracking->errorInfo()));
				$groupID = $groupID;
				
			}
			
		}
		
	}
}
//Aufbau der Tracks über die Einsatzgruppen
$speed = $canwrite = $lastlon = $lastlat = $lastspeed = $tpunktnr = 0;
$gpxtracks_temp = "";
$gpxtracks = '{"type":"FeatureCollection",
	"features":
	[
		';
$loop = $lasttstamp = $track_loop = 0;
$trackend = "";
$trackNr = 1;

foreach ($EinsatzGruppen as $key => $egroup) {
	//Auswahl der in der Gruppe vorkommenden Trackingdaten
	$user_in_group = $db->prepare("SELECT DISTINCT(UID) FROM tracking WHERE EID = '".$EID."' AND gruppe = '".$key."'");
	$user_in_group->execute() or die(print_r($user_in_group->errorInfo()));
	
	while ($uid_t = $user_in_group->fetch(PDO::FETCH_ASSOC)){
		$uid_t = $uid_t["UID"]; //UID eines Mitglieds in der Gruppe, für das Trackingdaten in der table liegen
		//Userinformation aus der Liste aller am Einsatz beteiligten User holen:
		if(isset($userdata_arr[$uid_t])){
			//echo "<br>Line 170: uid_t: ".$uid_t." ".$key."<br>";
					
			$userdata = $userdata_arr[$uid_t];
		
	
			//print_r($GPStracker);
			$trackheader = $trackcoords = $comma = $BOSnr = $Username = $Usertel = $OID = $UID = $sender = $DNR = $aktivierungszeit = $rGID = $group_name = $groupID = $group_status = '';
			//Überprüfen ob die Tracker Gruppen zugewiesen sind
			//echo "<br> User Gruppe: ".$userdata['gruppe'];
			//if(intval($userdata['gruppe'])){
		
			$BOSnr = (isset($userdata['bos']) && $userdata['bos'] != "") ? $userdata['bos'] : "";
			$Username = $userdata['name'];
			$Usertel = $userdata['phone'];
			$OID = $userdata['OID'];
			$UID = $userdata['UID'];
			$sender = $userdata['sender'];
			//echo "<br>Line 186: sender: ".$sender."<br>";
			$status = $userdata['status'];
			$DNR = $userdata['dienstnummer'];
			$aktivierungszeit = $userdata['aktivierungszeit'];
			$groupID = $key;//$EinsatzGruppen[$rGID]['data'][0]['id'];
			$rGID = intval($userdata['gruppe']) - 1;
			$group_name = isset($EinsatzGruppen[$groupID]['data'][0]['name']) ? $EinsatzGruppen[$groupID]['data'][0]['name'] : "Keiner Gruppe zugewiesen";
			//Fix 2020-11-02: Wenn User aktuell in keiner Gruppe ist (=> Group ID 0), dann kommt es zu einer Fehlermeldung.
			//$group_status = $EinsatzGruppen[$groupID]['data'][0]['status'];
			$group_status = isset($EinsatzGruppen[$groupID]['data'][0]['status']) ? $EinsatzGruppen[$groupID]['data'][0]['status'] : "Kein Status";
			if(strpos($userdata['typ'], 'SANI') !== false){
				$track_icon = 'img/SANI.png';
				$ausbildung = '<br>Sanitäter';
			}elseif(strpos($userdata['typ'], 'TA') !== false){
				$track_icon = 'img/TA.png';
				$ausbildung = '<br>Tierarzt';
			}else{
				$track_icon = 'img/gk.png';
				$ausbildung = '';
			}
			
			$counterBOS = true;
			
			//Layer baut die einzelnen Usertracks auf auf
			// Nur Tracks nach der Aktivierung des Users und seit Einsatzbeginn werden angezeigt
			// und von Usern die active Tracker sind 
			if($sender == 'active'){
				
				$opacity = 0.5;
				$in_token = '("' . implode('","', $token) .'")';
				$usertracks = $db->prepare("SELECT lon,lat,timestamp FROM tracking WHERE EID LIKE $EID AND lon NOT LIKE '' AND lat NOT LIKE '' AND herkunft IN ('APP','GPX','BOS') AND token IN " . $in_token . " AND timestamp > ".$trackingbeginn." AND speed <= '".$maxspeed."' AND ((UID LIKE '".$UID."')) AND CONVERT(hdop,UNSIGNED INTEGER) < 70 AND gruppe = '".$groupID."' ORDER BY timestamp DESC");
			
				$usertracks->execute() or die(print_r($usertracks->errorInfo(), true));
				$track_loop = 0;
				
				$usertrackcount = $usertracks->rowCount();
				$track_counter = 0;

				while ($rowtracks = $usertracks->fetch(PDO::FETCH_ASSOC)){
					$track_counter += 1;
					
					$timediff = $tlat = $tlon = $gruppe = $gpxtrackcolor = "";
					$gruppe = $group_name;
					$tlat =  $rowtracks['lat'];
					$tlon =  $rowtracks['lon'];

					$track_timestamp = $rowtracks['timestamp']/1000;
					$pointtime = date('H:i d.m.Y', $track_timestamp);
					$tracktime = date('d.m.y H:i', $track_timestamp);
					$timediff = $lasttstamp - $track_timestamp;// Zeit zwischen dem letzen Track und dem Aktuellen in Sekunden
					$entfernung = entfernung($tlat, $tlon, $lastlat, $lastlon);// Funktion entfernung berechnet die Entfernung in Metern
									
					$trackcolor = isset($EinsatzGruppen[$groupID]['data'][0]['color']) ? $EinsatzGruppen[$groupID]['data'][0]['color'] : "#FF3333";
					$trackend ='
					]]}}';
					
					
					
					$speed = $timediff == 0 ? "0" : $speed = $entfernung/$timediff;
					//neuen Track beginnen
					if($entfernung < ($timediff*$maxspeed) && $entfernung <= 150 && $timediff <= $trackpause){ //Die maximale Entfernung zwischen Punkten ist jetzt mit 150m fixiert; Bei importierten GPX Tracks wird nur auf die maximale Geschwindigkeit gefiltert, da der Track normalerweise sehr gut und hochauflösend ist.
						if($counterBOS){// Header für den Track schreiben
							$counterBOS = false;
							if($loop > 0){
								$gpxtracks_temp .= '
								]]}},';
								if($track_loop >= $minpunkte){
									$gpxtracks .= $gpxtracks_temp;
									$gpxtracks_temp = "";
									$track_loop = 0;
								} else {
									$gpxtracks_temp = "";
									$track_loop = 0;
								}
							}
							$loop = 0;
							$sw =  (in_array($trackNr, $hiddentracks)) ? 0 : $strokewidth;
							$gpxtracks_temp .= '{
								"type": "Feature",
								"properties": {
									"tracknr": "'.$trackNr.'",
									"time": "'.$pointtime.'",
									"name": "<b style=color:'.$trackcolor.'>'.$userdata['name'].$ausbildung.'</b>",
									"uid": "'.$UID.'",
									"gid": "'.$groupID.'",
									"id": "'.$group_name.'",
									"strokewidth": "'.$sw.'",
									"strokecolor": "'.$trackcolor.'",
									"opacity": "'.$opacity.'",
									"tracklon": "'.$tlon.'",
									"tracklat": "'.$tlat.'",
									"speed": "'.$speed.'",
									"trackloop": "'.$track_loop.'",
									"trackloop_var": "'.$minpunkte.'",
									"img": "'.$track_icon.'",
									"beschreibung": "<b style=color:'.$trackcolor.'>Track '.$trackNr.' mit '.$group_name.'</b><br>DNR: '.$DNR.'<br>BOS: '.$BOSnr.'<br><small>'.$tracktime.'</small>"
								},
								"geometry": {
								"type": "MultiLineString",
								"coordinates": [
									[
							';
							$gpxtracks_temp .= '['.$tlon.', '.$tlat.']';
							$trackNr++;
							$track_loop++;
						}else{// Koordinaten schreiben
							$gpxtracks_temp .=',
							['.$tlon.', '.$tlat.']';
							$track_loop++;
						}

					}else if($counterBOS === false){
						$counterBOS = true;
						$loop = 1;
					}
					$lastlon = $tlon;
					$lastlat = $tlat;
					$lasttstamp = $track_timestamp;

					if($counterBOS === false){
						$loop = 1;
						
					}
					if($usertrackcount==$track_counter){
						$gpxtracks_temp = str_replace('"opacity": "0.3"','"opacity": "1"',$gpxtracks_temp);
					}
				}
			}
			if($track_loop >= $minpunkte){
				$gpxtracks .= $gpxtracks_temp.$trackend.",";
				$gpxtracks_temp = "";
				$track_loop = 0;
			} else {
				$gpxtracks_temp = "";
				$track_loop = 0;
			}
			
		} else { //Falls die UID nicht im Array ist
			break;
		}
	
	} //Ende einzelner User in der Gruppe durchlaufen
	
}// Einsatzgruppen Ende
	if($track_loop >= $minpunkte){
		$track_loop = 0;
	} else {
		$gpxtracks = substr($gpxtracks,0,-1);
	}
	$gpxtracks.='
	]
}';

$encrypted_txt = encrypt($gpxtracks);
if(!file_exists($datapath.$EID.'/tracks.txt')) {
	mkdir($datapath.$EID, 0777);
}
$file = $datapath.$EID.'/tracks.txt';
file_put_contents($file, '');
file_put_contents($file, $encrypted_txt);
chmod($file, 0777);
?>

<?php
require "../../secure/info.inc.php";
require "../../secure/secret.php";
require "../v5/verschluesseln.php";


$timeonly = (isset($_GET["timeonly"]) &&  htmlspecialchars($_GET["timeonly"]) == "true") ? true : false ;
$test = (isset($_GET["test"]) &&  htmlspecialchars($_GET["test"]) == "true") ? true : false;
unset($_GET);
require "../v5/include/rwdb.php";

$start = microtime(true);
//Import BOS JSON to Database
if(!empty($_FILES["jsonfile"]["tmp_name"])){
	$readjson = file_get_contents($_FILES["jsonfile"]["tmp_name"]);
	
} elseif($test){
	//Nur für den Schnittstellentest
	$readjson = file_get_contents("data/temp.json");
	$test = true;
}

$json = utf8_encode($readjson);
$data=json_decode($json, true);
$org_token = $data['token'];

$org_check = false;
// Prüfung des ORG Tokens über die DB und JSON mit Statusmeldungen holen
$sql_query= $db->prepare("SELECT OID,status FROM organisation WHERE token LIKE '".$org_token."'");
$sql_query->execute($sql_query->errorInfo());
$results = $sql_query->fetch(PDO::FETCH_ASSOC);
if($sql_query->rowCount() > 0){
	if(!$timeonly) {echo "Org found in DB  <BR>";}
	$org_check = true;
	$OID = $results["OID"];
	$stati = json_decode($results['status'],true);
} else {
	if(!$timeonly) {echo "Org not found in DB <BR>";}
}

$OID_prim = ""; // OID der primären Organisation - muss ermittelt werden.

if($org_check ){
	$EID = $UID = $bos = $lat = $lon = $name =  "";
	$statusID = 1;
	$array_data = $data['data'];
	foreach ($array_data as $row) {
		foreach ($row["properties"] as $proprow) {
			if(isset($row["nummer"])){
				$dienstnummer = $bos = $nummer = $row["nummer"];
				//$status = $proprow["status"];
				$status = utf8_encode($proprow["status"]);
				//$status = mb_convert_encoding($proprow["status"], 'UTF-8', mb_detect_encoding($proprow["status"], 'UTF-8', true));
				$lat = $proprow["lat"];
				$lon = $proprow["lon"];
				$timestamp = $proprow["timestamp"];
				$hdop = $proprow["hdop"];
				$altitude = $proprow["altitude"];
				$speed = $proprow["speed"];

				//User holen, Gruppe und EID finden vl auf jSon umstellen
				//$EID = $dienstnummer = $name = $gruppe = $UID = $telefon = ""; //Gruppen ID wird übergeben - Für Suchgebiet muss gruppe "e_group".$gruppe angesprochen werden
				$EID = $name = $gruppe = $UID = $telefon = ""; //Gruppen ID wird übergeben - Für Suchgebiet muss gruppe "e_group".$gruppe angesprochen werden
				$aktivierungszeit = 0;			

				//Trackingdaten in die DB schreiben
				$insert_tracking = $db->prepare("INSERT INTO tracking (lat,lon,timestamp,hdop,altitude,speed,nummer,herkunft,EID,gruppe,OID,UID,oidmitglied) VALUES ('" . $lat . "', '" . $lon . "', '" . ($timestamp*1000) . "', '" . $hdop . "', '" . $altitude . "', '" . $speed . "','" .$dienstnummer. "',  'BOS', '" . $EID . "', '" . $gruppe . "', '" . $OID . "', '" . $UID . "', '" . $OID . "')");
				$insert_tracking->execute() or die(print_r($insert_tracking->errorInfo()));
				
				if(!$timeonly) {echo "INSERT INTO tracking (lat,lon,timestamp,hdop,altitude,speed,nummer,herkunft,EID,gruppe,OID,UID,oidmitglied) VALUES ('" . $lat . "', '" . $lon . "', '" . ($timestamp*1000) . "', '" . $hdop . "', '" . $altitude . "', '" . $speed . "','" .$dienstnummer. "',  'BOS', '" . $EID . "', '" . $gruppe . "', '" . $OID . "', '" . $UID . "', '" . $OID . "')";}
				//if(!$timeonly) {echo $dienstnummer;}
				//echo($stati["bos"][$status]);
				//if(array_key_exists($status,$stati["bos"])){
				//if data is send from BOS
				if(isset($stati["bos"][$status])){
					$statusID = intval($stati["bos"][$status]);
				}
			}else{
				$status = $proprow["status"];
				$dienstnummer = $UID = $row["uid"];
				//if data is not send from BOS
				$statusID = intval($status);
			}
			//Schnittstellentext auf Status übersetzen
			if(isset($statusID)){
				//Der übermittelte Status ist bei der Organisation vorhanden.
				echo "<br>Status ID: ".$statusID."<br>";
				

				if($dienstnummer != ""){
					//Statusmeldungen:
					$setuserstatus = $setanmeldung = $setabmeldung = $setpause = $setgruppenstatus = $sendmessage = $search_pie = $search_user = false;
					$protokolltext = $statustext = "";
					switch($statusID){
						case 1: //Anmeldung zum Einsatz
							$setuserstatus = $setanmeldung = $search_user = true;
							//$statusnr = 1;
							$protokolltext = "Mitglied &&username&& hat sich zum Einsatz angemeldet.";
						break;
						case 2: //Anreise
							$setuserstatus = $setanmeldung = $search_user = true;
							//$statusnr = 2;
							$protokolltext = "Mitglied &&username&& ist in Anreise zum Einsatzort.";
						break;
						case 3: //Am Berufungsort
							$setuserstatus = $setanmeldung = $search_user = true;
							$statustext = "im Dienst";
							//$statusnr = 3;
							$protokolltext = "Mitglied &&username&& ist am Berufungsort eingetroffen.";
						break;
						case 4: //Ins Suchgebiet
							$setuserstatus = $setgruppenstatus = $search_pie = true;
							$statustext = "rückt aus";
							//$statusnr = 4;
							$protokolltext = "&&gruppenname&& rückt ins Suchgebiet &&suchgebietname&& aus.";
						break;
						case 5: //Beginn Suche
							$setuserstatus = $setgruppenstatus = $search_pie = true;
							$statustext = "sucht";
							//$statusnr = 5;
							$protokolltext = "&&gruppenname&& beginnt mit Suche im Suchgebiet &&suchgebietname&&.";
						break;
						case 6: //Ende Suche
							$setuserstatus = $setgruppenstatus = $search_pie = true;
							$statustext = "Suche beendet";
							//$statusnr = 6;
							$protokolltext = "&&gruppenname&& hat Suche im Suchgebiet &&suchgebietname&& beendet.";
						break;
						case 7: //Wartet auf Transport
							$setuserstatus = $setgruppenstatus = $search_pie = true;
							$statustext = "wartet auf Transport";
							//$statusnr = 7;
							$protokolltext = "&&gruppenname&& wartet auf Transport aus Suchgebiet &&suchgebietname&&.";
						break;
						case 8: //Rückweg EL
							$setuserstatus = $setgruppenstatus = $search_pie = true;
							$statustext = "rückt ein";
							//$statusnr = 8;
							$protokolltext = "&&gruppenname&& ist am Rückweg zur Einsatzleitung.";
						break;
						case 9: //Pause Mitglied
							$setuserstatus = $setpause = $search_pie = true;
							$statustext = "in Pause";
							//$statusnr = 9;
							$protokolltext = "Mitglied &&username&& geht in Pause.";
						break;
						case 10: //Heimweg
							$setuserstatus = $setabmeldung = $search_pie = true;
							//$statusnr = 10;
							$protokolltext = "Mitglied &&username&& verlässt den Einsatzort in Richtung Heimat.";
						break;
						case 11: //Abgemeldet vom Einsatz
							$setuserstatus = $setabmeldung = $search_pie = true;
							//$statusnr = 11;
							$protokolltext = "Mitglied &&username&& meldet sich vom Einsatz ab.";
						break;
						case 12: //Fund lebend, RD benötigt
							$setuserstatus = $sendmessage = $search_pie = true;
							$statustext = "Personenfund: Person lebt, Rettungsdienst wird benötigt.";
							//$statusnr = 12;
							$protokolltext = "Person lebend gefunden, Rettungsdienst wird benötigt. Informationen zur Meldung: Mitglied: &&username&&, Koordinaten: <a href='javascript:;' class='centerPOI' data-coords-lon='&&RW&&' data-coords-lat='&&HW&&'><i class='material-icons zoomin'>zoom_in</i> Lat: &&HW&& Lon: &&RW&&</a>.";
						break;
						case 13: //Fund lebend, kein RD benötigt
							$setuserstatus = $sendmessage = $search_pie = true;
							$statustext = "Personenfund: Person lebt, Kein Rettungsdienst benötigt.";
							//$statusnr = 13;
							$protokolltext = "Person lebend gefunden, Kein Rettungsdienst benötigt. Informationen zur Meldung: Mitglied: &&username&&, Koordinaten: <a href='javascript:;' class='centerPOI' data-coords-lon='&&RW&&' data-coords-lat='&&HW&&'><i class='material-icons zoomin'>zoom_in</i> Lat: &&HW&& Lon: &&RW&&</a>.";
						break;
						case 14: //Fund tot
							$setuserstatus = $sendmessage = $search_pie = true;
							$statustext = "Personenfund: Person tot.";
							//$statusnr = 14;
							$protokolltext = "Person tot gefunden. Informationen zur Meldung: Mitglied: &&username&&, Koordinaten: <a href='javascript:;' class='centerPOI' data-coords-lon='&&RW&&' data-coords-lat='&&HW&&'><i class='material-icons zoomin'>zoom_in</i> Lat: &&HW&& Lon: &&RW&&</a>.";
						break;
						case 15: //Sprechwunsch
							$setuserstatus = $sendmessage = $search_pie = $search_user = true;
							$statustext = "Sprechwunsch!";
							//$statusnr = 15;
							$protokolltext = "Sprechwunsch Einsatzleitung. Mitglied &&username&& per Direktruf oder Mobiltelefon kontaktieren. Informationen zur Meldung: Mitglied: &&username&&, Koordinaten: <a href='javascript:;' class='centerPOI' data-coords-lon='&&RW&&' data-coords-lat='&&HW&&'><i class='material-icons zoomin'>zoom_in</i> Lat: &&HW&& Lon: &&RW&&</a>.";
						break;
						
					}
					//Protokollieren - Organisation wählt selbst, welcher Status protokolliert werden soll
					if(isset($stati["all"][$status]["doku"]) && $stati["all"][$status]["doku"]){ $writeprotokoll = true;}
		
			//Informationen zum User holen (nur bei Statusgabe!)

					$tage = $test ? 30 : 1; //Suchzeitraum von Einsätzen einschränken (Es wird nur in Einsätzen gesucht, die in den letzten X Tagen aktualisiert wurden
					//Über personen im Einsatz
					$EID_lastupdate = "1970-01-01 00:00:01";
					if($search_pie && $UID === ""){
						//User zur Funkkennung holen - es wird der User aus den Einsatzsettings geholt. Diese werden zeitlich eingeschränkt über $tage
						$sql_query= $db->prepare("SELECT EID,data,gruppen,personen_im_einsatz,lastupdate FROM `settings` WHERE lastupdate BETWEEN SUBDATE(CURDATE(), INTERVAL ".$tage." DAY) AND NOW() ORDER BY `settings`.`lastupdate` DESC");
						$sql_query->execute($sql_query->errorInfo());
						while ($einsatz = $sql_query->fetch(PDO::FETCH_ASSOC)){
							$einsatz_data = json_decode(substr(string_decrypt($einsatz['data']), 1, -1),true);
							$OID_prim = isset($einsatz_data->OID) ? $einsatz_data->OID : ""; //Primäre Organisation ermitteln.
							$einsatz_gruppen = json_decode(substr(string_decrypt($einsatz['gruppen']), 1, -1),true);
							$einsatz_pie = json_decode(string_decrypt($einsatz['personen_im_einsatz']),true);
							if(!empty($einsatz_pie)){
								foreach($einsatz_pie as $personen){
									foreach($personen['data'] as $persondata){
										if(isset($persondata['OID']) && $persondata['OID'] == $OID && isset($persondata['bos']) && $persondata['bos'] == $bos){	
											if(!$timeonly) {echo "EID ".$einsatz["EID"]." ";}
											if(isset($persondata['abgemeldet']) && $persondata['abgemeldet'] == ""){	//Die Person ist noch im Einsatz
												if(!$timeonly) {echo "<br>Mitglied laut PIE: ".$persondata['name']." - ".$persondata['dienstnummer']." - ".$persondata['UID']."<BR>";}
												if(isset($persondata['aktivierungszeit']) && $persondata['aktivierungszeit'] > $aktivierungszeit){ //Falls ein User im Suchzeitraum bei mehreren Einsätzen aktiv war, wird jener mit dem spätesten Aktivierungszeitpunkt gesucht
													//Gruppen durchlaufen
													$aktivierungszeit = $persondata['aktivierungszeit'];
													$insert = true;
													$name = isset($persondata['name']) ? $persondata['name'] : "";
													$UID = isset($persondata['UID']) ? $persondata['UID'] : "";
													$OID = isset($persondata['OID']) ? $persondata['OID'] : "";
													$dienstnummer = isset($persondata['dienstnummer']) ? $persondata['dienstnummer'] : "";
													$telefon = isset($persondata['phone']) ? $persondata['phone'] : "";
													$email = isset($persondata['email']) ? $persondata['email'] : "";
													$bos = isset($persondata['bos']) ? $persondata['bos'] : "";
													$typ = isset($persondata['typ']) ? $persondata['typ'] : "";
													$pause = isset($persondata['pause']) ? $persondata['pause'] : "";
													$gruppe = isset($persondata['gruppe']) ? $persondata['gruppe'] : "0"; //Gruppen ID wird übergeben - Für Suchgebiet muss gruppe "e_group".$u_gruppe angesprochen werden
													$EID = $einsatz["EID"];
													$EID_lastupdate = $einsatz["lastupdate"]; // Für Suche aktueller EID für Stati 12-15 benötigt
												}
											} else {
												if(!$timeonly) {echo "Gefunden, aber nicht mehr im Einsatz<BR>";}
											}
										}
									}
								}
							}
						
						}
					}
					
					//Über Usertable
					if($search_user || ($search_pie && $name == "")){ // Statusmeldungen 12-15 benötigen Informationen über den User und können vor Anmeldung gesetzt werden
						if($UID !== "") {
							$sql_query = $db->prepare("SELECT UID, OID,data,aktiveEID FROM `user` WHERE UID = '".$UID."'");
							$sql_query->execute($sql_query->errorInfo());
							$user = $sql_query->fetch(PDO::FETCH_ASSOC);
							if($user == null) abort(204);
							$data_user_json = json_decode(substr(string_decrypt($user['data']), 1, -1));

							$name = $data_user_json->name ?? ""; 
							$dienstnummer   = $data_user_json->dienstnummer ?? ""; 
							$typ   = $data_user_json->typ ?? ""; 
							$pause   = isset($data_user_json->pause) ? $data_user_json->pause/60 : 0; 
							$ausbildungen   = $data_user_json->ausbildungen ?? ""; 
							$email   = $data_user_json->email ?? ""; 
							$bos   = $data_user_json->bos ?? ""; 
							$telefon  = $data_user_json->telefon ?? ""; 
							$einsatzfaehig  = $data_user_json->einsatzfaehig ?? "0"; 
							$notfallkontakt   = $data_user_json->notfallkontakt ?? ""; 
							$notfallinfo   = $data_user_json->notfallinfo ?? ""; 
							$kommentar   = $data_user_json->kommentar ?? "";
							$aktiveEID = $user['aktiveEID'];
							echo "<br>Mitglied laut Usertable1: ".$name." - ".$dienstnummer."<br>";
						} else {
							$sql_query = $db->prepare("SELECT UID, OID,data,aktiveEID FROM `user` WHERE OID = '".$OID."'");
							$sql_query->execute($sql_query->errorInfo());
							while ($user = $sql_query->fetch(PDO::FETCH_ASSOC)){
								$data_user_json = json_decode(substr(string_decrypt($user['data']), 1, -1));
							
								if(isset($data_user_json->bos) && $data_user_json->bos == $bos){
									$UID = $user['UID'];
									$name = isset($data_user_json->name) ? $data_user_json->name : ""; 
									$dienstnummer   = isset($data_user_json->dienstnummer) ? $data_user_json->dienstnummer : ""; 
									$typ   = isset($data_user_json->typ) ? $data_user_json->typ : ""; 
									$pause   = isset($data_user_json->pause) ? $data_user_json->pause/60 : 0; 
									$ausbildungen   = isset($data_user_json->ausbildungen) ? $data_user_json->ausbildungen : ""; 
									$email   = isset($data_user_json->email) ? $data_user_json->email : ""; 
									$bos   = isset($data_user_json->bos) ? $data_user_json->bos : ""; 
									$telefon  = isset($data_user_json->telefon) ? $data_user_json->telefon : ""; 
									$einsatzfaehig  = isset($data_user_json->einsatzfaehig) ? $data_user_json->einsatzfaehig : "0"; 
									$notfallkontakt   = isset($data_user_json->notfallkontakt) ? $data_user_json->notfallkontakt : ""; 
									$notfallinfo   = isset($data_user_json->notfallinfo) ? $data_user_json->notfallinfo : ""; 
									$kommentar   = isset($data_user_json->kommentar) ? $data_user_json->kommentar : "";
									$aktiveEID = $user['aktiveEID'];
									if(!$timeonly) {echo "<br>Mitglied laut Usertable2: ".$name." - ".$dienstnummer."<br>";}
									break;
								}
							}
							
						}
					}
					
					//EID ermitteln, falls noch nicht gesetzt - es wird die EID vom zuletzt aktualisierten Einsatz geholt, der noch nicht abgeschlossen wurde und innerhalb des Tage Suchzeitraums liegt
					if($EID == "" || ($EID != "" && ($statusID >= 12 && $statusID <=15))){
						
						$einsatz_query = $db->prepare("SELECT EID,data,lastupdate FROM settings  WHERE lastupdate BETWEEN SUBDATE(CURDATE(), INTERVAL ".$tage." DAY) AND NOW() ORDER BY `lastupdate` DESC");
						$einsatz_query->execute($einsatz_query->errorInfo());
						while ($einsaetze = $einsatz_query->fetch(PDO::FETCH_ASSOC)){
							$einsatz_data = json_decode(substr(string_decrypt($einsaetze["data"]), 1, -1));
							$OID_t = isset($einsatz_data->OID) ? $einsatz_data->OID : "";
							$OID_prim = isset($einsatz_data->OID) ? $einsatz_data->OID : ""; //Primäre Organisation ermitteln.
							$Ogleich = isset($einsatz_data->Ogleich) ? $einsatz_data->Ogleich : "";
							$Ozeichnen = isset($einsatz_data->Ozeichnen) ? $einsatz_data->Ozeichnen : "";
							$Ozuweisen = isset($einsatz_data->Ozuweisen) ? $einsatz_data->Ozuweisen : "";
							$Osehen = isset($einsatz_data->Osehen) ? $einsatz_data->Osehen : "";
							$ende = isset($einsatz_data->ende) ? $einsatz_data->ende : "";
							//Alle OIDs die irgendwie im Einsatz teilnehmen
							$oids_t = explode(",",$OID_t.",".$Ogleich.",".$Ozeichnen.",".$Ozuweisen.",".$Osehen);
							$oids_t = array_unique(array_filter($oids_t));
							if(in_array($OID,$oids_t)){
								
								if($ende == "" && $EID == "" || ($EID != "" && strtotime($EID_lastupdate) < strtotime($einsaetze['lastupdate']))){
									if(!$timeonly) {echo "<br>EID ermitteln: EID: ".$einsaetze['EID']."<br><br>";}
									$EID = $einsaetze['EID'];
									$EID_lastupdate = $einsaetze['lastupdate'];
								}
							}
							
						}
					}
					
					
		//Bearbeiten der Statusmeldungen
					$returnvalue = ""; //Variable zum Unterdrücken der Ausgaben von read_write_db()
					//Statusmeldung des Users setzen
					if($setuserstatus){ //Befüllen der Statustable mit EID, OID, UID, Status und Timestamp
						$insert_status = $db->prepare("INSERT INTO status (EID,OID,UID,status) VALUES ('" . $EID . "', '" . $OID . "', '" . $UID . "', '" . $statusID . "')");
						$insert_status->execute() or die(print_r($insert_status->errorInfo()));
						echo $EID .', ' . $OID . ', ' . $UID . ', ' . $statusID;
						$status_setzen = array("status" => $statusID);
						$vars = array('table'=>'settings','action'=>'update','type'=>'json_update','column'=>'personen_im_einsatz','select'=>$EID,'values'=>$UID,'json_nodes'=>$status_setzen);
						//print_r($vars);
						ob_start(); //Unterdrückt die Ausgabe von read_write_db();
							read_write_db($vars);
						ob_end_clean();
						
					}
					
					//User zum Einsatz anmelden
					if($setanmeldung){ //User beim Einsatz anmelden. Gleiches Verhalten wie Anmeldung in Ressourcenverwaltung
						//JSON schreiben über require read_write_db.php
						//$vars = array();
						$vars = array('table'=>'settings','action'=>'read_return','type'=>'json','column'=>'personen_im_einsatz','select'=>$EID,'values'=>"",'json_nodes'=>"");
						$user = (read_write_db($vars));
						$user_arr = json_decode($user,true);
						$check_arr = array_column($user_arr, "UID");
						
						//Prüfen ob der User im Einsatz existiert
						if(array_search($UID,$check_arr) === false){
							//Mitglied ist noch nicht im Einsatz
							$anmelden = array("UID" => "".$UID."", "OID" => "".$OID."", "dienstnummer" => "".$dienstnummer."", "name" => "".$name."", "phone" => "".$telefon."",	"email" => "".$email."", "bos" => "".$bos."", "typ" => "".$typ."", "pause" => ($pause/60), "sender" => "", "gruppe" => "0", "status" => $statusID, "aktivierungszeit" => "".time()."", "eingerueckt" => "", "inPause" => "", "ausPause" =>"", "abgemeldet" => "");
							$vars = array('table'=>'settings','action'=>'update','type'=>'json_append','column'=>'personen_im_einsatz','select'=>$EID,'values'=>$UID,'json_nodes'=>$anmelden);
							ob_start(); //Unterdrückt die Ausgabe von read_write_db();
								read_write_db($vars);
							ob_end_clean();
							//aktiveEID setzen
							$update = $db->prepare("UPDATE user SET `aktiveEID`='".$EID."' WHERE `UID`='".$UID."' AND OID = '".$OID."'");
							$update->execute() or die(print_r($update->errorInfo()));
							if(!$timeonly) {echo "aktiveEID auf ".$EID." gesetzt.<br>";}
						}
						if(array_search($UID,$check_arr) !== false){ //User ist angemeldet und in Pause --> Pause wird beendet
							$uid_in_pie = array_search($UID,$check_arr); //id im array
							$loggedout_time = $user_arr[$uid_in_pie]['abgemeldet'];
							if($loggedout_time == ""){
								if($user_arr[$uid_in_pie]["inPause"] != "" && $user_arr[$uid_in_pie]["ausPause"] == ""){
									//Mitglied in Pause stellen = Timestamp für inPause setzen
									$pause = array("gruppe" => "0", "status" => $statusID, "ausPause" => "".time()."");
									$vars = array('table'=>'settings','action'=>'update','type'=>'json_update','column'=>'personen_im_einsatz','select'=>$EID,'values'=>$UID,'json_nodes'=>$pause);
									//print_r($vars);
									ob_start(); //Unterdrückt die Ausgabe von read_write_db();
										read_write_db($vars);
									ob_end_clean();
								}
							}else{
								$pause = array("gruppe" => "0", "status" => $statusID, "inPause" => $loggedout_time, "ausPause" => "".time()."", "abgemeldet" => "");
								$vars = array('table'=>'settings','action'=>'update','type'=>'json_update','column'=>'personen_im_einsatz','select'=>$EID,'values'=>$UID,'json_nodes'=>$pause);
								//print_r($vars);
								ob_start(); //Unterdrückt die Ausgabe von read_write_db();
									read_write_db($vars);
								ob_end_clean();
							}
						}
						
						
					}
					
					//User in Pause stellen
					if($setpause){ //User in Pause stellen. Gleiches Verhalten wie Anmeldung in Ressourcenverwaltung
						$vars = array('table'=>'settings','action'=>'read_return','type'=>'json','column'=>'personen_im_einsatz','select'=>$EID,'values'=>$UID,'json_nodes'=>"");
						$user = (read_write_db($vars));
						$user_arr = json_decode($user,true); //Werte als JSON
						$check_arr = array_column($user_arr, "UID");
						
						if(array_search($UID,$check_arr) !== false){ //User muss angemeldet sein und darf keinen Pausenwert haben um in Pause gehen zu können
							$uid_in_pie = array_search($UID,$check_arr); //id im array
							if($user_arr[$uid_in_pie]["inPause"] == ""){ //User war noch nicht in Pause
								//Mitglied in Pause stellen = Timestamp für inPause setzen
								$pause = array("gruppe" => 0, "status" => $statusID, "inPause" => "".time()."");
								$vars = array('table'=>'settings','action'=>'update','type'=>'json_update','column'=>'personen_im_einsatz','select'=>$EID,'values'=>$UID,'json_nodes'=>$pause);
								//print_r($vars);
								ob_start(); //Unterdrückt die Ausgabe von read_write_db();
									read_write_db($vars);
								ob_end_clean();
							}
						}
						
					}
					
					//Gruppenstatus ändern
					if($setgruppenstatus){ //Status der Gruppe in der User zugewiesen ist aktualisieren. Dabei ist nur eine Erhöhung möglich 
						$vars = array('table'=>'settings','action'=>'read_return','type'=>'json','column'=>'personen_im_einsatz','select'=>$EID,'values'=>$UID,'json_nodes'=>"");
						$user = (read_write_db($vars));
						$user_arr = json_decode($user,true); //Werte als JSON
						$check_arr = array_column($user_arr, "UID");
						
						if(array_search($UID,$check_arr) !== false){ //User muss angemeldet sein und darf keinen Pausenwert haben um den Gruppenstatus setzen zu können
							$uid_in_pie = array_search($UID,$check_arr); //id im array
							if($user_arr[$uid_in_pie]["gruppe"] != "" || $user_arr[$uid_in_pie]["gruppe"] != "0"){ //User ist einer Gruppe zugewiesen
								$GID = $user_arr[$uid_in_pie]["gruppe"]; //Zugewiesene Gruppe
								$vars_gruppe = array('table'=>'settings','action'=>'read_return','type'=>'json','column'=>'gruppen','select'=>$EID,'values'=>$GID,'json_nodes'=>"");
								$gruppen_arr = json_decode(read_write_db($vars_gruppe),true); //Werte als JSON
								$check_gruppen = array_column($gruppen_arr, "gruppe");
								$gig = array_search(("e_group".$GID),$check_gruppen); //Gruppen ID im array
								$gStatusAktuell = $gruppen_arr[$gig]["aktuellerStatus"];
								$gStatus = $gruppen_arr[$gig]["status"];
								switch($gStatusAktuell){
									case "neu": //Gruppe ist neu
										if($statusID >= 4 && $statusID <= 8){
											$gStatus = $gStatus.$statustext."&&".date("Y-m-d H:i:s").";";
											//Gruppenstatus übermitteln
											//$status_nodes = array("aktuellerStatus" => $statusID, "zeit" => date("Y-m-d H:i:s"), "status" => $gStatus);
											$status_nodes = array("aktuellerStatus" => $statustext, "zeit" => date("Y-m-d H:i:s"), "status" => $gStatus);
											$vars = array('table'=>'settings','action'=>'update','type'=>'json_update','column'=>'gruppen','select'=>$EID,'values'=>$GID,'json_nodes'=>$status_nodes);
											ob_start(); //Unterdrückt die Ausgabe von read_write_db();
												read_write_db($vars);
											ob_end_clean();
											echo "<br>Gruppenstatus ".$statustext." gesetzt<br>";
										}
									break;
									case "rückt aus": //Gruppe am Weg ist Einsatzgebiet
										if($statusID >= 5 && $statusID <= 8){
											$gStatus = $gStatus.$statustext."&&".date("Y-m-d H:i:s").";";
											//Gruppenstatus übermitteln
											//$status_nodes = array("aktuellerStatus" => $statusID, "zeit" => date("Y-m-d H:i:s"), "status" => $gStatus);
											$status_nodes = array("aktuellerStatus" => $statustext, "zeit" => date("Y-m-d H:i:s"), "status" => $gStatus);
											$vars = array('table'=>'settings','action'=>'update','type'=>'json_update','column'=>'gruppen','select'=>$EID,'values'=>$GID,'json_nodes'=>$status_nodes);
											ob_start(); //Unterdrückt die Ausgabe von read_write_db();
												read_write_db($vars);
											ob_end_clean();
											echo "<br>Gruppenstatus ".$statustext." gesetzt<br>";
										}
									break;
									case "Im Suchgebiet": //Gruppe ist im Suchgebiet
										if($statusID >= 5 && $statusID <= 8){
											$gStatus = $gStatus.$statustext."&&".date("Y-m-d H:i:s").";";
											//Gruppenstatus übermitteln
											//$status_nodes = array("aktuellerStatus" => $statusID, "zeit" => date("Y-m-d H:i:s"), "status" => $gStatus);
											$status_nodes = array("aktuellerStatus" => $statustext, "zeit" => date("Y-m-d H:i:s"), "status" => $gStatus);
											$vars = array('table'=>'settings','action'=>'update','type'=>'json_update','column'=>'gruppen','select'=>$EID,'values'=>$GID,'json_nodes'=>$status_nodes);
											ob_start(); //Unterdrückt die Ausgabe von read_write_db();
												read_write_db($vars);
											ob_end_clean();
											echo "<br>Gruppenstatus ".$statustext." gesetzt<br>";
										}
									break;
									case "sucht": //Gruppe ist bei der Suche
										if($statusID >= 6 && $statusID <= 8){
											$gStatus = $gStatus.$statustext."&&".date("Y-m-d H:i:s").";";
											//Gruppenstatus übermitteln
											//$status_nodes = array("aktuellerStatus" => $statusID, "zeit" => date("Y-m-d H:i:s"), "status" => $gStatus);
											$status_nodes = array("aktuellerStatus" => $statustext, "zeit" => date("Y-m-d H:i:s"), "status" => $gStatus);
											$vars = array('table'=>'settings','action'=>'update','type'=>'json_update','column'=>'gruppen','select'=>$EID,'values'=>$GID,'json_nodes'=>$status_nodes);
											ob_start(); //Unterdrückt die Ausgabe von read_write_db();
												read_write_db($vars);
											ob_end_clean();
											echo "<br>Gruppenstatus ".$statustext." gesetzt<br>";
										}
									break;
									case "Suche beendet": //Gruppe ist mit der Suche fertig
										if($statusID >= 7 && $statusID <= 8){
											$gStatus = $gStatus.$statustext."&&".date("Y-m-d H:i:s").";";
											//Gruppenstatus übermitteln
											//$status_nodes = array("aktuellerStatus" => $statusID, "zeit" => date("Y-m-d H:i:s"), "status" => $gStatus);
											$status_nodes = array("aktuellerStatus" => $statustext, "zeit" => date("Y-m-d H:i:s"), "status" => $gStatus);
											$vars = array('table'=>'settings','action'=>'update','type'=>'json_update','column'=>'gruppen','select'=>$EID,'values'=>$GID,'json_nodes'=>$status_nodes);
											$returnvalue = read_write_db($vars);ob_start(); //Unterdrückt die Ausgabe von read_write_db();
												read_write_db($vars);
											ob_end_clean();
											echo "<br>Gruppenstatus ".$statustext." gesetzt<br>";
										}
									break;
									case "wartet auf Transport": //Gruppe wartet auf Transport zurück
										if($statusID == 8){
											$gStatus = $gStatus.$statustext."&&".date("Y-m-d H:i:s").";";
											//Gruppenstatus übermitteln
											//$status_nodes = array("aktuellerStatus" => $statusID, "zeit" => date("Y-m-d H:i:s"), "status" => $gStatus);
											$status_nodes = array("aktuellerStatus" => $statustext, "zeit" => date("Y-m-d H:i:s"), "status" => $gStatus);
											$vars = array('table'=>'settings','action'=>'update','type'=>'json_update','column'=>'gruppen','select'=>$EID,'values'=>$GID,'json_nodes'=>$status_nodes);
											ob_start(); //Unterdrückt die Ausgabe von read_write_db();
												read_write_db($vars);
											ob_end_clean();
											echo "<br>Gruppenstatus ".$statustext." gesetzt<br>";
										}
									break;
									case "rückt ein": //Gruppe kehrt zurück in die Basis
										echo "<br>Gruppenstatus ist rückt ein<br>";
									break;
									case "zurück": //Gruppe ist zurück
										echo "<br>Gruppenstatus ist zurück<br>";
									break;
									case "löschen": //Gruppe ist gelöscht und scheint daher auch nicht mehr auf
										echo "<br>Gruppenstatus ist löschen<br>";
									break;
								}
							}
						}
						
					}
					
					//"Pushnofitification" für als Overlay des Kartenfensters
					if($sendmessage){ //"Pushnofitification" für als Overlay des Kartenfensters
						//Wenn primäre OID noch nicht ermittelt, wird diese hier geholt.
						
						$sql_query = $db->prepare("SELECT data FROM `settings` WHERE EID = '".$EID."'");
						$sql_query->execute($sql_query->errorInfo());
						$settingsdata = $sql_query->fetch(PDO::FETCH_ASSOC);
						$einsatz_data = json_decode(substr(string_decrypt($settingsdata['data']), 1, -1));
						$OID_prim = isset($einsatz_data->OID) ? $einsatz_data->OID : ""; //Primäre Organisation ermitteln.

						
						//Gruppennamen ermitteln
						$vars = array('table'=>'settings','action'=>'read_return','type'=>'json','column'=>'personen_im_einsatz','select'=>$EID,'values'=>$UID,'json_nodes'=>"");
						$user = (read_write_db($vars));
						$user_arr = json_decode($user,true); //Werte als JSON
						$check_arr = array_column($user_arr, "UID");
						$gName = "keine Gruppenbezeichnung";
						if(array_search($UID,$check_arr) !== false){ //User ist angemeldet
							$uid_in_pie = array_search($UID,$check_arr); //id im array
							if($user_arr[$uid_in_pie]["gruppe"] != "" || $user_arr[$uid_in_pie]["gruppe"] != "0"){ //User ist einer Gruppe zugewiesen
								$GID = $user_arr[$uid_in_pie]["gruppe"]; //Zugewiesene Gruppe
								$vars_gruppe = array('table'=>'settings','action'=>'read_return','type'=>'json','column'=>'gruppen','select'=>$EID,'values'=>$GID,'json_nodes'=>"");
								$gruppen_arr = json_decode(read_write_db($vars_gruppe),true); //Werte als JSON
								$check_gruppen = array_column($gruppen_arr, "gruppe");
								$gig = array_search(("e_group".$GID),$check_gruppen); //Gruppen ID im array
								$gName = $gruppen_arr[$gig]["name"];
							} 
						}
						
						$vars = array('table'=>'settings','action'=>'read_return','type'=>'json','column'=>'protokoll','select'=>$EID,'values'=>$UID,'json_nodes'=>"");
						$protokoll = (read_write_db($vars));
						$prot_arr = json_decode($protokoll,true); //Anzahl an Protokolleinträgen
						$prot_id = 0;
						foreach($prot_arr as $prot_key){
							$prot_id = $prot_key["id"] + 1;
						}
						$protokolltext = str_replace(array("&&username&&", "&&gruppenname&&", "&&RW&&", "&&HW&&"), array($name,$gName,substr($lon,0,7),substr($lat,0,7)), $protokolltext);
						$message = array("id" => $prot_id, "oid" => $OID, "type" => "protokoll", "phone" => $telefon, "bos" => $bos, "name" => $name, "read" => false, "betreff" => $statustext, "deleted" => "", "text" => $protokolltext, "zeit" => date("Y-m-d H:i:s"));
						$vars = array('table'=>'settings','action'=>'update','type'=>'json_append','column'=>'protokoll','select'=>$EID,'values'=>$prot_id,'json_nodes'=>$message);
						//print_r($vars);
						ob_start(); //Unterdrückt die Ausgabe von read_write_db();
							read_write_db($vars);
						ob_end_clean();
						
						//Wenn die OID nicht die der primären Organisation ist, wird noch ein zweiter Eintrag mit der OID der Primärorganisation geschrieben
						if($OID_prim != $OID && $OID_prim != "" && $statusID < 15){
							$vars = array('table'=>'settings','action'=>'read_return','type'=>'json','column'=>'protokoll','select'=>$EID,'values'=>$UID,'json_nodes'=>"");
							$protokoll = (read_write_db($vars));
							$prot_arr = json_decode($protokoll,true); //Anzahl an Protokolleinträgen
							$prot_id = 0;
							foreach($prot_arr as $prot_key){
								$prot_id = $prot_key["id"] + 1;
							}
							$protokolltext = str_replace(array("&&username&&", "&&gruppenname&&", "&&RW&&", "&&HW&&"), array($name,$gName,substr($lon,0,7),substr($lat,0,7)), $protokolltext);
							$message = array("id" => $prot_id, "oid" => $OID_prim, "type" => "protokoll", "phone" => $telefon, "bos" => $bos, "name" => $name, "read" => false, "betreff" => $statustext, "deleted" => "", "text" => $protokolltext, "zeit" => date("Y-m-d H:i:s"));
							$vars = array('table'=>'settings','action'=>'update','type'=>'json_append','column'=>'protokoll','select'=>$EID,'values'=>$prot_id,'json_nodes'=>$message);
							//print_r($vars);
							ob_start(); //Unterdrückt die Ausgabe von read_write_db();
								read_write_db($vars);
							ob_end_clean();
						}
						
						//Bei Personenfund wird ein POI gesetzt
						if($statusID >= 12 && $statusID <= 14){
							$protokolltext = substr($protokolltext,0,strpos($protokolltext,", Koordinaten: <a"));
							$poi = array("type" => "Feature", "properties" => array("oid" => $OID, "uid" => $UID, "name" => "Personenfund", "color" => "#c00", "beschreibung" => $protokolltext, "img" => "", "poi" => time()*1000), "geometry" => array("type" => "Point", "coordinates" => array("0" => $lon, "1" => $lat)));
							$vars = array('table'=>'settings','action'=>'update','type'=>'json_append','column'=>'pois','select'=>$EID,'values'=>'','json_nodes'=>$poi);
							//print_r($vars);
							ob_start(); //Unterdrückt die Ausgabe von read_write_db();
								read_write_db($vars);
							ob_end_clean();
						}
					}
					
					//Eintrag ins Ereignisprotokoll
					//if($writeprotokoll){ //Status mit Daten zum Nutzer, Timestamp und Koordinaten ins Ereignisprotokoll schreiben.
						if($stati["all"][$statusID]["doku"] && $statusID <= 11){ //Kontrollieren ob der Status dokumentiert werden soll. Stati 12-15 werden automatisch dokumentiert.
							//Gruppennamen ermitteln
							$vars = array('table'=>'settings','action'=>'read_return','type'=>'json','column'=>'personen_im_einsatz','select'=>$EID,'values'=>$UID,'json_nodes'=>"");
							$user = (read_write_db($vars));
							$user_arr = json_decode($user,true); //Werte als JSON
							$check_arr = array_column($user_arr, "UID");
							$gName = "keine Gruppenbezeichnung";
							if(array_search($UID,$check_arr) !== false){ //User ist angemeldet
								$uid_in_pie = array_search($UID,$check_arr); //id im array
								if($user_arr[$uid_in_pie]["gruppe"] != "" || $user_arr[$uid_in_pie]["gruppe"] != "0"){ //User ist einer Gruppe zugewiesen
									$GID = $user_arr[$uid_in_pie]["gruppe"]; //Zugewiesene Gruppe
									$vars_gruppe = array('table'=>'settings','action'=>'read_return','type'=>'json','column'=>'gruppen','select'=>$EID,'values'=>$GID,'json_nodes'=>"");
									$gruppen_arr = json_decode(read_write_db($vars_gruppe),true); //Werte als JSON
									$check_gruppen = array_column($gruppen_arr, "gruppe");
									$gig = array_search(("e_group".$GID),$check_gruppen); //Gruppen ID im array
									$gName = $gruppen_arr[$gig]["name"];
								} 
							}
							
							$vars = array('table'=>'settings','action'=>'read_return','type'=>'json','column'=>'protokoll','select'=>$EID,'values'=>$UID,'json_nodes'=>"");
							$protokoll = (read_write_db($vars));
							$prot_arr = json_decode($protokoll,true); //Anzahl an Protokolleinträgen
							foreach($prot_arr as $prot_key){
								$prot_id = $prot_key["id"] + 1;
							}
							$protokolltext = str_replace(array("&&username&&", "&&gruppenname&&", "&&RW&&", "&&HW&&"), array($name,$gName,substr($lon,0,7),substr($lat,0,7)), $protokolltext);
							$message = array("id" => $prot_id, "oid" => $OID, "type" => "protokoll", "phone" => $telefon, "bos" => $bos, "name" => $name, "read" => true, "betreff" => $statustext, "deleted" => "", "text" => $protokolltext, "zeit" => date("Y-m-d H:i:s"));
							$vars = array('table'=>'settings','action'=>'update','type'=>'json_append','column'=>'protokoll','select'=>$EID,'values'=>$prot_id,'json_nodes'=>$message);
							//print_r($vars);
							ob_start(); //Unterdrückt die Ausgabe von read_write_db();
								read_write_db($vars);
							ob_end_clean();
						}
						
					//User vom Einsatz abmelden
					if($setabmeldung){ //User vom Einsatz abmelden. Gleiches Verhalten wie Anmeldung in Ressourcenverwaltung
						$vars = array('table'=>'settings','action'=>'read_return','type'=>'json','column'=>'personen_im_einsatz','select'=>$EID,'values'=>"",'json_nodes'=>"");
						$user = (read_write_db($vars));
						$user_arr = json_decode($user,true); //Werte als JSON
						$check_arr = array_column($user_arr, "UID");
						
						if(array_search($UID,$check_arr) !== false){ //User muss angemeldet sein um sich abmelden zu können
							//Mitglied aus Personen im Einsatz nehmen = Timestamp für Ende setzen
							$abmelden = array("gruppe" => "", "status" => "", "abgemeldet" => "".time()."");
							$vars = array('table'=>'settings','action'=>'update','type'=>'json_update','column'=>'personen_im_einsatz','select'=>$EID,'values'=>$UID,'json_nodes'=>$abmelden);
							//print_r($vars);
							ob_start(); //Unterdrückt die Ausgabe von read_write_db();
								read_write_db($vars);
							ob_end_clean();
							//aktiveEID setzen
							$update = $db->prepare("UPDATE user SET `aktiveEID`='' WHERE `UID`='".$UID."' AND OID = '".$OID."'");
							$update->execute() or die(print_r($update->errorInfo()));
							if(!$timeonly) {echo $UID." aktiveEID entfernt.<br>";}
						}
						if(!$timeonly) {echo "<br>Set Abmeldung.<br>";}
					}
					//}
					
				}// Ende If Dienstnummer != ""
			} //Ende if Status gefunden
			$status = "";
			
		}
	}
}

if($test) {
	echo "<br><b>Dauer:</b> ".((microtime(true)-$start)*1000000)."µs";
	
}

?>
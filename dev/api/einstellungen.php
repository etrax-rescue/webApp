<?php  //Einstellungen zum Einsatz - primär Kooperation
session_start();
if(!isset($_SESSION["etrax"]["usertype"])){
header("Location: index.php");
}
require "../../../secure/info.inc.php";
require "../../../secure/secret.php";
require "../include/verschluesseln.php";


$EID = $_SESSION["etrax"]["EID"];
$strokewidth = $_SESSION["etrax"]["strokewidth"];

define("sessionstart",false);
require "../include/sessionhandler.php"; //Der Sessionhandler schreibt die Sessionwerte neu


$einsatz_query = $db->prepare("SELECT EID,data,lastupdate FROM settings WHERE EID = ".$EID."");
$einsatz_query->execute($einsatz_query->errorInfo());
$einsatz = $einsatz_query->fetch(PDO::FETCH_ASSOC);
$einsatz_data_json = json_decode(substr(string_decrypt($einsatz['data']), 1, -1));
$einsatzname = isset($einsatz_data_json->einsatz) ? $einsatz_data_json->einsatz : "";
$start = strtotime(isset($einsatz_data_json->anfang) ? strtotime($einsatz_data_json->anfang) : "");
$ort = isset($einsatz_data_json->einsatz) ? $einsatz_data_json->einsatz : "";
$einsatzende = strtotime(isset($einsatz_data_json->ende) ? strtotime($einsatz_data_json->ende) : "");
$restrictedExtent = isset($einsatz_data_json->restrictedExtent) ? $einsatz_data_json->restrictedExtent : "0";
$HFquote = isset($einsatz_data_json->HFquote) ? $einsatz_data_json->HFquote : "2.5";
$trackpause = isset($einsatz_data_json->trackpause) ? $einsatz_data_json->trackpause : "60";
$trackstart = isset($einsatz_data_json->trackstart) ? $einsatz_data_json->trackstart : "";
$newtrackloading = isset($einsatz_data_json->newtrackloading) ? $einsatz_data_json->newtrackloading : "60000";
$trackreload = isset($einsatz_data_json->trackreload) ? $einsatz_data_json->trackreload : "1";
$minpunktefuertrack = isset($einsatz_data_json->minpunkte) ? $einsatz_data_json->minpunkte : "5";
$minspeed = isset($einsatz_data_json->minspeed) ? $einsatz_data_json->minspeed : "0.001";
$maxspeed = isset($einsatz_data_json->maxspeed) ? $einsatz_data_json->maxspeed : "3.3336";
$readposition = isset($einsatz_data_json->readposition) ? $einsatz_data_json->readposition : "30";
$distance = isset($einsatz_data_json->distance) ? $einsatz_data_json->distance : "50";
$OID = isset($einsatz_data_json->OID) ? $einsatz_data_json->OID : "";
$Ogleich = isset($einsatz_data_json->Ogleich) ? $einsatz_data_json->Ogleich : "";
$Ozeichnen = isset($einsatz_data_json->Ozeichnen) ? $einsatz_data_json->Ozeichnen : "";
$Ozuweisen = isset($einsatz_data_json->Ozuweisen) ? $einsatz_data_json->Ozuweisen : "";
$Osehen = isset($einsatz_data_json->Osehen) ? $einsatz_data_json->Osehen : "";

//Query das OID auf am Einsatz beteiligte OIDs beschränkt für ein MySQL Statement (OID = 'DEV' OR OID = 'XY')
$oids_t = explode(",",$OID.",".$Ogleich.",".$Ozeichnen.",".$Ozuweisen.",".$Osehen);
$oids_t = array_unique(array_filter($oids_t));
$OID_q = "OID = 'DEV' OR ";
$i = 0;

$OID_admin = $_SESSION["etrax"]["adminOID"];

foreach($oids_t as $oid_t){
	if($i == 0){
		$OID_q = $OID_q."OID = '".$oid_t."'";
	} else{
		$OID_q = $OID_q." OR OID = '".$oid_t."'";
	}
	$i++;
}
$OID_q = "(".$OID_q.")";
$orginfo_json = '';
if($USER["lesen"]){ //Mindestens Leserechte werden benötigt um etwas angezeigt zu bekommen.
	$sql_suchtyp = $db->prepare("SELECT orginfo FROM settings WHERE EID = ".$EID."");
	$sql_suchtyp->execute($sql_suchtyp->errorInfo());
	while ($sqlsuchtyp = $sql_suchtyp->fetch(PDO::FETCH_ASSOC)){
		if(!empty($sqlsuchtyp['orginfo'])){
			//$orginfo_json = json_decode(substr(string_decrypt($sqlsuchtyp['orginfo']), 1, -1),true);
			$orginfo_json = json_decode(string_decrypt($sqlsuchtyp['orginfo']),true);
		}
	}

	$oids_el = $oid_el = $elids = $elid = "";
	/*if($orginfo_json != ""){
		foreach($orginfo_json as $pkey => $pvalue) {
			
			if($pvalue){
				$$pkey = $pvalue;
				$oid_el = explode("_",$pkey,2);
				$oids_el .= ",".$oid_el[0];
				$elid = explode("-",$pkey,-1); //durch -1 wird ein leeres array zurückgegeben wenn nichts gefunden wird
				if(isset($elid[0])){
					$elids .= ",".$elid[0];
				}
			}else{
				$$pkey = "";
			}
		}
	}*/
	$org2show = $elid2show = array();
	if($orginfo_json != ""){
		$ii = 0;
		foreach($orginfo_json as $org_tt){
			if(!strpos($orginfo_json[$ii]["id"],"_")){ // Findet die Nodes mit Organisationen	
				$oids_el .= ",".$orginfo_json[$ii]["id"];
				foreach($orginfo_json[$ii]['data'] as $orgdata){
					//$org2show[$orgdata["id"]]["ebeginndatum"] .= ",".$orgdata["ebeginndatum"];
					$org2show[$orgdata["id"]]["id"] = isset($orgdata["id"]) ? $orgdata["id"] : "";
					$org2show[$orgdata["id"]]["ebeginndatum"] = isset($orgdata["ebeginndatum"]) ? $orgdata["ebeginndatum"] : "";
					$org2show[$orgdata["id"]]["ebeginnzeit"] = isset($orgdata["ebeginnzeit"]) ? $orgdata["ebeginnzeit"] : "";
					$org2show[$orgdata["id"]]["eendedatum"] = isset($orgdata["eendedatum"]) ? $orgdata["eendedatum"] : "";
					$org2show[$orgdata["id"]]["eendezeit"] = isset($orgdata["eendezeit"]) ? $orgdata["eendezeit"] : "";
					$org2show[$orgdata["id"]]["ersteller"] = isset($orgdata["ersteller"]) ? $orgdata["ersteller"] : "";
					$org2show[$orgdata["id"]]["verstecken"] = isset($orgdata["verstecken"]) ? $orgdata["verstecken"] : "";
					$org2show[$orgdata["id"]]["orgname"] = isset($orgdata["orgname"]) ? $orgdata["orgname"] : "";
					$org2show[$orgdata["id"]]["orgadresse"] = isset($orgdata["orgadresse"]) ? $orgdata["orgadresse"] : "";
				}
			} else { //Die IDs der Einsatzleiter
				$elids .= ",".$orginfo_json[$ii]["id"];
				foreach($orginfo_json[$ii]['data'] as $orgdata){
					//$org2show[$orgdata["id"]]["ebeginndatum"] .= ",".$orgdata["ebeginndatum"];
					$elid2show[$orgdata["id"]]["id"] = isset($orgdata["id"]) ? $orgdata["id"] : "";
					$elid2show[$orgdata["id"]]["oid"] = isset($orgdata["oid"]) ? $orgdata["oid"] : "";
					$elid2show[$orgdata["id"]]["name"] = isset($orgdata["name"]) ? $orgdata["name"] : "";
					$elid2show[$orgdata["id"]]["tel"] = isset($orgdata["tel"]) ? $orgdata["tel"] : "";
					$elid2show[$orgdata["id"]]["vondatum"] = isset($orgdata["vondatum"]) ? $orgdata["vondatum"] : "";
					$elid2show[$orgdata["id"]]["vonzeit"] = isset($orgdata["vonzeit"]) ? $orgdata["vonzeit"] : "";
					$elid2show[$orgdata["id"]]["bisdatum"] = isset($orgdata["bisdatum"]) ? $orgdata["bisdatum"] : "";
					$elid2show[$orgdata["id"]]["biszeit"] = isset($orgdata["biszeit"]) ? $orgdata["biszeit"] : "";
					$elid2show[$orgdata["id"]]["verstecken"] = isset($orgdata["verstecken"]) ? $orgdata["verstecken"] : "";
				}	
			}				
			
			$ii++;
		}
	}
	$oids_el = implode(",",$oids_t).",".$oids_el;
	$oids_el = array_unique(array_filter(explode(",",$oids_el)));
	$elids = array_unique(array_filter(explode(",",$elids)));
	
	?>
		<button data-toggle="collapse" data-target="#einst_rw" class="btn btn-light w-100 collapsed text-left"><h5 class="fa" >Personen mit mindestens Leserechten bei diesen Einsatz:</h5></button>
		<div id="einst_rw" class="collapse">
			<table id="etraxadmins" class="col-12">
				<tr class="text-left">
					<th>Name</th>
					<th>Organisation</th>
					<th>gültig für Einsatz</th>
					<th> </th>
				</tr>
			<?php
			//Infos zur Organisation (wird auch für Berechtigung genützt)
			$oidname_query = $db->prepare("SELECT OID,data,orgfreigabe FROM organisation");
			$oidname_query->execute($oidname_query->errorInfo());
			//Array mit den Organisationsnamen erstellen
			$oidname = array();
			while ($roworg = $oidname_query->fetch(PDO::FETCH_ASSOC)){
				$data_org_json = json_decode(substr(string_decrypt($roworg['data']), 1, -1));
				$oidname[$roworg["OID"]] = array('kurzname' => $data_org_json->kurzname,'bezeichnung' => $data_org_json->bezeichnung,'OID' => $roworg["OID"], 'orgfreigabe' => $roworg["orgfreigabe"]);
			}
			
			//Korrektur 02.02.2020 PT
			$admin_users = $db->prepare("SELECT ID,UID,OID,FID,data,EID FROM user WHERE (EID = '0' OR EID LIKE '".$EID."' OR EID LIKE '".$EID.",%' OR EID LIKE '%,".$EID."' OR EID LIKE '%,".$EID.",%') AND ".$OID_q." ORDER BY OID ASC");
			$admin_users->execute() or die(print_r($sql_query->errorInfo()));
			while ($rowadmin = $admin_users->fetch(PDO::FETCH_ASSOC)){
					$data_admin = json_decode(substr(string_decrypt($rowadmin['data']), 1, -1));
						if(($rowadmin['OID'] == "DEV")){
							$login = $data_admin->username;
							$aoid = $oidname[$rowadmin['OID']]['kurzname'];
							$name = $data_admin->name;
							echo "<tr class='adminrow".$rowadmin['UID']." text-left'><td>".$name."</td><td>".$aoid."</td><td>Alle Einsätze - Global</td><td class=' text-right'></td></tr>";
						}else{
							//Kontrolle ob EID 0 ist oder die jeweilige vom Einsatz enthält
							$eids = explode(",",$rowadmin["EID"]);
							if($rowadmin["EID"] == "0"){	
								$login = $data_admin->username;
								$aoid = $oidname[$rowadmin['OID']]['kurzname'];
								$name = $data_admin->name;
								echo "<tr class='adminrow".$rowadmin['UID']." text-left'><td>".$name."</td><td>".$aoid."</td><td>Alle Einsätze der Organisation</td><td class=' text-right'></td></tr>";
							}
							if(in_array($EID,$eids)){ //Temporäre Administratoren
								$login = $data_admin->username;
								$aoid = $oidname[$rowadmin['OID']]['kurzname'];
								$name = $data_admin->name;
								echo "<tr class='adminrow".$rowadmin['UID']." text-left'><td>".$name."</td><td>".$aoid."</td><td>Diesen Einsatz - Temporär</td><td class=' text-right'><a href='#' class='delete' data-eid='".$rowadmin['EID']."' data-uid='".$rowadmin['UID']."' data-fid='".$rowadmin['FID']."' data-id='".$rowadmin['ID']."' title='Administrator löschen'><i class='material-icons red'>highlight_off</i></a></td></tr>";
							}
						}
				}
				
			?>
			</table>
			<button class="btn btn-primary mt-4" type="button" data-toggle="modal" data-target=".userliste">neuen  Administrator anlegen</button>
		</div>
		<div id="allsettings" class="border-top mt-4">
			<button data-toggle="collapse" data-target="#einst_ber" class="btn btn-light w-100 collapsed text-left"><h5 class="fa">Organisationsberechtigungen ändern:</h5></button>
			<div id="einst_ber" class="collapse">
				<form id="changeleadsettings" method="post">
					<input type="hidden" name="table" value="settings">
					<input type="hidden" name="and_EID" value="<?php echo $_SESSION["etrax"]["EID"];?>">
					
						<?php 
						$hasRights = array();
						$title_array = array("OID"=>"EL:","Ogleich"=>"Alle Rechte:","Ozeichnen"=>"Suchgebiete<br>zeichnen:","Ozuweisen"=>"Suchgebiete<br>zuweisen:","Osehen"=>"Informationen<br>lesen:");
						echo "<table class='table  table-bordered table-striped table-sm '><tr><th> </th>";
						foreach ($title_array as $orgkey => $value) {
							echo "<th>".$value."</th>";
						}
						echo "</tr>";
						$jtemp = json_decode($oidname[$OID]["orgfreigabe"],true); //Organisationsfreigaben der anlegenden Organisation anzeigen
						$oid_data = "";
						foreach (array_reverse($oidname) as $orgname) { //Freigabe je Organisation
							$oid_data .= $orgname['OID'].",";
							if(isset($jtemp[$orgname["OID"]])){
								echo "<tr><th>".$orgname["kurzname"]."</th>";
								if($orgname['OID']=="DEV" || $orgname['OID']==$OID){
									$disabled = " disabled";
								}else{
									$disabled = "";
								}
								$org = array(
									"OID" => $OID,
									"Ogleich" => $Ogleich,
									"Ozeichnen" => $Ozeichnen,
									"Ozuweisen" => $Ozuweisen,
									"Osehen" => $Osehen
									);
								
								//$oid_query = $db->prepare("SELECT s.OID,Ogleich,Ozeichnen,Ozuweisen,Osehen FROM settings s WHERE EID = ".$EID."");
								//$oid_query->execute($oid_query->errorInfo());
								//$orgs = $oid_query->fetchALL(PDO::FETCH_ASSOC);
								//foreach ($orgs as $org) {
									foreach ($org as $orgkey => $value) {
										$singleOID = explode(",",$value);
										$checked = "";
										if (in_array($orgname['OID'], $singleOID) || $orgname['OID'] == "DEV") {
											array_push($hasRights,$orgname['OID']);
										}
										if($orgkey == "OID"){
											$disabled = " disabled";
										} else {
											if($orgname['OID']=="DEV" || $orgname['OID']==$OID ){
												$disabled = " disabled";
											}else{
												$disabled = "";
											}
										}
										if(in_array($orgname['OID'], $hasRights)){
											$checked = "checked";
										}
										if($USER["gleich"]){ //Ausgabe der Infos zum Modifizieren
											echo '<td><input class="'.$orgkey.' '.$orgkey.'_'.$orgname['OID'].'" type="checkbox" value="'.$orgname['OID'].'" name="'.$orgkey.'" '.$checked.$disabled.'></td>';
										} else { //Ausgabe als Dummy
											echo '<td><input class="" type="checkbox" value="" name="" '.$checked.' disabled></td>';
										}
										$hasRights = array();
									}
								//}
								echo "</tr>";
							}
						}
						$oid_data = substr($oid_data,0,-1);
						echo "</table>";
						if($USER["gleich"]){?>
					<button class="btn btn-primary mt-4 saveleadsettings" type="button" data-toggle="modal" data-form="changeleadsettings" data-oids="<?php echo $oid_data;?>">speichern</button>
						<?php } ?>
				</form>
			</div>
			<div id="einsatz_organisationen" class="border-top mt-4">
				<button data-toggle="collapse" data-target="#einst_bet" class="btn btn-light w-100 collapsed text-left"><h5 class="fa">Beteiligte Organisationen:</h5></button>
				<div id="einst_bet" class="collapse">
					<div class="d-flex align-items-end flex-column mb-4">
						<a href="pdf/einsatzberichtpdf.php?eb_organisationen=1" target="_blank" class="btn btn-primary mt-2" data-toggle="tooltip" data-placement="top" data-original-title="Liste der beteiligten Organisationen drucken" title="Liste der beteiligten Organisationen drucken"><i class="material-icons color-white">print</i></a>
					</div>
					<div id="einst_bet_org">
					<?php
						$x = 1;
						foreach($oids_el as $oid_t){ 
							if($oid_t != "DEV"){
								if(substr($oid_t,0,4) == "Temp"){ //Temporäre Organisationen filtern
									$temp_org = true;
									$col_prim = "#b53d00";
									$col_sec = "#ff9d3f";
								} else {
									$temp_org = false;
									$col_prim = "#78909c";
									$col_sec = "#a7c0cd";
								}
								if(($oid_t == $OID_admin && $USER["gleich"]) || $USER["einsatzleitung"]){
									$readonly = "";
								} else {
								$readonly = "readonly";}
								$verstecken = isset($org2show[$oid_t]["verstecken"]) ? $org2show[$oid_t]["verstecken"] : "false";
								if($verstecken == "false" || $verstecken == $_SESSION["etrax"]["OID"]){ // Versteckt Organisationseinträge, wenn diese entsprechend markiert sind.
									?>
									<div class="<?php echo $oid_t; ?>_bet_org">
										<?php if(!$temp_org){ ?>
										<button data-toggle="collapse" data-target="#<?php echo $oid_t; ?>_bet_org" class="collapsed w-100 mb-4 text-left btn btn-info"><?php echo $oidname[$oid_t]['bezeichnung']; ?></button>
										<?php } ?>
										<?php if($temp_org){ ?>
										<div class="form-group row mt-4 mb-3 ml-2 mr-0" style="background-color:<?php echo $col_prim; ?>;border-radius:10px 0px 0px 10px;">
											<button data-toggle="collapse" data-target="#<?php echo $oid_t; ?>_bet_org" class="collapsed col-11"><div class="col-sm-11"><input <?php echo $readonly; ?> class="mb-2 form-control-plaintext checkJSON border-bottom border-white border-top-0" name="<?php echo $oid_t; ?>_orgname" id="<?php echo $oid_t; ?>_orgname" type="text" value="<?php echo $org2show[$oid_t]["orgname"]; ?>" tabindex="<?php echo $x;$x++; ?>"></div></button>
										<?php if(($oid_t == $OID_admin && $USER["gleich"]) || $USER["einsatzleitung"] || $org2show[$oid_t]["ersteller"] == $OID_admin){ //Löschen der Daten nur möglich wenn Primärorganisation oder Berechtigung gleich gesetzt?>
											<button class='delete_elorg float-right' data-id="<?php echo $oid_t; ?>" style='border:0px;background-color:rgba(0,0,0,0);'><i class='material-icons' style='color:#FFF;'>delete</i></button>
										</div>
										<div class="bg-info p-4 text-white rounded" id="<?php echo $oid_t; ?>_del_confirm" class="collapse" style="display:none">Soll die Organisation wirklich gelöscht werden? <button type="button" class="btn btn-primary delete_elorg_confirm m-2" data-id="<?php echo $oid_t; ?>">Ja</button><button type="button" class="btn btn-danger abbruch m-2" data-dismiss="modal">nein</button></div>
										<?php } else { ?>
										</div>
										<?php }?>
										<?php } ?>
										
										<div id="<?php echo $oid_t; ?>_bet_org" class="collapse">
											<div class="form-group row ml-2">
												<?php if($temp_org){ ?>
												<label for="<?php echo $oid_t; ?>_orgadresse" class="col-sm-2 col-form-label" ><b>Anschrift:</b></label>
												<div class="col-sm-10">
													<input class="mb-2 form-control-plaintext checkJSON border-bottom border-dark border-top-0" name="<?php echo $oid_t; ?>_orgadresse" id="<?php echo $oid_t; ?>_orgadresse" type="text" value="<?php echo $org2show[$oid_t]["orgadresse"]; ?>" tabindex="<?php echo $x;$x++; ?>">
												</div>
												<?php } ?>
												<label for="<?php echo $oid_t; ?>_ebeginndatum" class="col-sm-3 col-form-label" ><b>Einsatzbeginn:</b></label>
												<div class="col-sm-5">
													<input <?php echo $readonly; ?> class="mb-2 form-control-plaintext checkJSON border-bottom border-dark border-top-0" name="<?php echo $oid_t; ?>_ebeginndatum" id="<?php echo $oid_t; ?>_ebeginndatum" type="date" value="<?php echo isset($org2show[$oid_t]["ebeginndatum"]) ? $org2show[$oid_t]["ebeginndatum"] : ""; ?>" tabindex="<?php echo $x;$x++; ?>">
												</div>
												<label for="<?php echo $oid_t; ?>_ebeginnzeit" class="col-sm-2 col-form-label" ><b>Uhrzeit:</b></label>
												<div class="col-sm-2">
													<input <?php echo $readonly; ?> class="mb-2 form-control-plaintext checkJSON border-bottom border-dark border-top-0" name="<?php echo $oid_t; ?>_ebeginnzeit" id="<?php echo $oid_t; ?>_ebeginnzeit" type="time" value="<?php echo isset($org2show[$oid_t]["ebeginnzeit"]) ? $org2show[$oid_t]["ebeginnzeit"] : "";?>" tabindex="<?php echo $x;$x++; ?>">
												</div>
												<label for="<?php echo $oid_t; ?>_eendedatum" class="col-sm-3 col-form-label" ><b>Einsatzende:</b></label>
												<div class="col-sm-5">
													<input <?php echo $readonly; ?> class="mb-2 form-control-plaintext checkJSON border-bottom border-dark border-top-0" name="<?php echo $oid_t; ?>_eendedatum" id="<?php echo $oid_t; ?>_eendedatum" type="date" value="<?php echo  isset($org2show[$oid_t]["eendedatum"]) ? $org2show[$oid_t]["eendedatum"] : ""; ?>" tabindex="<?php echo $x;$x++; ?>">
												</div>
												<label for="<?php echo $oid_t; ?>_eendezeit" class="col-sm-2 col-form-label" ><b>Uhrzeit:</b></label>
												<div class="col-sm-2">
													<input <?php echo $readonly; ?> class="mb-2 form-control-plaintext checkJSON border-bottom border-dark border-top-0" name="<?php echo $oid_t; ?>_eendezeit" id="<?php echo $oid_t; ?>_eendezeit" type="time" value="<?php echo isset($org2show[$oid_t]["eendezeit"]) ? $org2show[$oid_t]["eendezeit"] : "";?>" tabindex="<?php echo $x;$x++; ?>">
													<input name="<?php echo $oid_t; ?>_ersteller" id="<?php echo $oid_t; ?>_ersteller" type="hidden" value="<?php $var_t = $oid_t."_ersteller"; if(isset($$var_t)){echo $$var_t;}?>">
												</div>
												
												<?php if(($oid_t == $OID_admin && $USER["gleich"]) || $USER["einsatzleitung"] || isset($org2show[$oid_t]["ersteller"]) && $org2show[$oid_t]["ersteller"] == $OID_admin){ //Speichern der Daten nur möglich wenn Primärorganisation oder Berechtigung gleich gesetzt?>
												<?php if($temp_org){ // Temporäre Organisationen können versteckt werden?>
												<label class="col-sm-10 col-form-label pl-4" for="<?php echo $oid_t; ?>_verstecken">Diese Information nur für die eigene Organisation anzeigen</label>
												<div class="col-sm-1">
													<input type="checkbox" class="form-control" id="<?php echo $oid_t; ?>_verstecken" name="<?php echo $oid_t; ?>_verstecken" value="<?php echo $_SESSION["etrax"]["OID"]; ?>" <?php  $$var_t = isset($org2show[$oid_t]["verstecken"]) ? $org2show[$oid_t]["verstecken"] : "false"; echo $$var_t != "false" ? "checked" : ""; $$var_t = isset($org2show[$oid_t]["ersteller"]) ? $org2show[$oid_t]["ersteller"] : ""; echo (!$temp_org || $$var_t != $_SESSION["etrax"]["OID"]) ? " disabled" : "";?>>
												</div>
												<?php } else { ?>
													<input type="hidden" id="<?php echo $oid_t; ?>_verstecken" name="<?php echo $oid_t; ?>_verstecken" value="<?php echo $_SESSION["etrax"]["OID"]; ?>">
												<?php }?>
												<div class="col-sm-12 pt-4">
													<button class="btn btn-primary eingabe org_speichern float-right" id="" data-oid_t="<?php echo $oid_t; ?>" data-new="<?php echo isset($org2show[$oid_t]["id"]) ? "update" : "append";?>" tabindex="<?php echo $x;$x++; ?>">Speichern</button>
												</div>
												<?php } ?>
											</div>
											<?php
											$y = 0;
											foreach($elids as $elid_t){ 
												$oid_el_t = explode("_",$elid_t);
												if($oid_el_t[0] == $oid_t){ $y++;
												$verstecken = isset($elid2show[$elid_t]["verstecken"]) ? $elid2show[$elid_t]["verstecken"] : "false";
												if($verstecken == "false" || $verstecken == $_SESSION["etrax"]["OID"]){ // Versteckt Administratoren, wenn diese entsprechend markiert sind.?>
												<div class="form-group row ml-4 mr-1" id="<?php echo $elid_t; ?>_div" style="background-color:<?php echo $col_sec; ?>;padding:5px;border-radius:10px;">
													<div class="col-sm-11" style="border-bottom:1px solid <?php echo $col_prim; ?>;"><b>Einsatzleiter</b></div>
													<?php if((isset($elid2show[$elid_t]["oid"]) && $elid2show[$elid_t]["oid"] == $OID_admin && $USER["gleich"]) || $USER["einsatzleitung"] || $org2show[$oid_t]["ersteller"] == $OID_admin){ //Löschen der Daten nur möglich wenn Primärorganisation oder Berechtigung gleich gesetzt?>
														<div class="col-sm-1 text-right" style="border-bottom:1px solid <?php echo $col_prim; ?>;"><button class='delete_elorg' data-id="<?php echo $elid_t; ?>" style='border:0px;background-color:rgba(0,0,0,0);'><i class='material-icons' style='color:#D3302F;'>delete</i></button></div>
														<div class="bg-info p-4 text-white rounded col-12" id="<?php echo $elid_t; ?>_del_confirm" class="collapse" style="display:none">Soll dieser Einsatzleiter wirklich gelöscht werden? <button type="button" class="btn btn-success delete_elorg_confirm m-2" data-id="<?php echo $elid_t; ?>">Ja</button><button type="button" class="btn btn-danger abbruch m-2" data-dismiss="modal">nein</button></div>
													<?php } ?>
													<label for="<?php echo $elid_t; ?>-el_name" class="col-sm-2 col-form-label" ><b>Name:</b></label>
														<div class="col-sm-4">
															<input class="mb-2 form-control-plaintext checkJSON input-sm border-bottom border-white border-top-0" name="<?php echo $elid_t; ?>-el_name" id="<?php echo $elid_t; ?>-el_name" type="text" value="<?php echo isset($elid2show[$elid_t]["name"]) ? $elid2show[$elid_t]["name"] : ""; ?>" tabindex="<?php echo $x;$x++; ?>">
														</div>
													<label for="<?php echo $elid_t; ?>-el_tel" class="col-sm-2 col-form-label" ><b>Tel.Nr.:</b></label>
														<div class="col-sm-4">
															<input class="mb-2 form-control-plaintext checkJSON input-sm border-bottom border-white border-top-0" name="<?php echo $elid_t; ?>-el_tel" id="<?php echo $elid_t; ?>-el_tel" type="text" value="<?php echo isset($elid2show[$elid_t]["tel"]) ? $elid2show[$elid_t]["tel"] : ""; ?>" tabindex="<?php echo $x;$x++; ?>">
														</div>
													<label for="<?php echo $elid_t; ?>-el_vondatum" class="col-sm-2 col-form-label" ><b>Von:</b></label>
														<div class="col-sm-4">
															<input class="mb-2 form-control-plaintext checkJSON input-sm border-bottom border-white border-top-0" name="<?php echo $elid_t; ?>-el_vondatum" id="<?php echo $elid_t; ?>-el_vondatum" type="date" value="<?php echo isset($elid2show[$elid_t]["vondatum"]) ? $elid2show[$elid_t]["vondatum"] : ""; ?>" tabindex="<?php echo $x;$x++; ?>">
														</div>
													<label for="<?php echo $elid_t; ?>-el_vonzeit" class="col-sm-2 col-form-label" ><b>Uhrzeit:</b></label>
														<div class="col-sm-4">
															<input class="mb-2 form-control-plaintext checkJSON input-sm border-bottom border-white border-top-0" name="<?php echo $elid_t; ?>-el_vonzeit" id="<?php echo $elid_t; ?>-el_vonzeit" type="time" value="<?php echo isset($elid2show[$elid_t]["vonzeit"]) ? $elid2show[$elid_t]["vonzeit"] : "";?>" tabindex="<?php echo $x;$x++; ?>">
														</div>
													<label for="<?php echo $elid_t; ?>-el_bisdatum" class="col-sm-2 col-form-label" ><b>Bis:</b></label>
														<div class="col-sm-4">
															<input class="mb-2 form-control-plaintext checkJSON input-sm border-bottom border-white border-top-0" name="<?php echo $elid_t; ?>-el_bisdatum" id="<?php echo $elid_t; ?>-el_bisdatum" type="date" value="<?php echo isset($elid2show[$elid_t]["bisdatum"]) ? $elid2show[$elid_t]["bisdatum"] : ""; ?>" tabindex="<?php echo $x;$x++; ?>">
														</div>
													<label for="<?php echo $elid_t; ?>-el_biszeit" class="col-sm-2 col-form-label" ><b>Uhrzeit:</b></label>
														<div class="col-sm-4">
															<input class="mb-2 form-control-plaintext checkJSON input-sm border-bottom border-white border-top-0" name="<?php echo $elid_t; ?>-el_biszeit" id="<?php echo $elid_t; ?>-el_biszeit" type="time" value="<?php echo isset($elid2show[$elid_t]["biszeit"]) ? $elid2show[$elid_t]["biszeit"] : "";?>" tabindex="<?php echo $x;$x++; ?>">
															<input name="<?php echo $elid_t; ?>-el_ersteller" id="<?php echo $elid_t; ?>-el_ersteller" type="hidden" value="<?php $var_t = $elid_t."-el_ersteller"; if(isset($$var_t)){echo $$var_t;}?>">
														</div>
													<?php if((isset($elid2show[$elid_t]["oid"]) && $elid2show[$elid_t]["oid"] == $OID_admin && $USER["gleich"]) || $USER["einsatzleitung"] || $org2show[$oid_t]["ersteller"] == $OID_admin){ //Speichern der Daten nur möglich wenn Primärorganisation oder Berechtigung gleich gesetzt?>
													<label class="col-sm-10 col-form-label pl-4" for="<?php echo $elid_t; ?>-el_verstecken">Diese Information nur für die eigene Organisation anzeigen</label>
														<div class="col-sm-1">
															<input type="checkbox" class="form-control" id="<?php echo $elid_t; ?>-el_verstecken" name="<?php echo $elid_t; ?>-el_verstecken" value="<?php echo $_SESSION["etrax"]["OID"]; ?>" <?php  $$var_t = isset($elid2show[$elid_t]["verstecken"]) ? $elid2show[$elid_t]["verstecken"] : "false"; echo $$var_t != "false" ? "checked" : ""; $$var_t = isset($elid2show[$elid_t]["ersteller"]) ? $elid2show[$elid_t]["ersteller"] : ""; echo (!$temp_org || $$var_t != $_SESSION["etrax"]["OID"]) ? " disabled" : "";?>">
														</div>
													
													<div class="pt-4">
														<button class="btn btn-primary eingabe el_speichern float-right" id="" data-elid_t="<?php echo $elid_t; ?>" data-oid_t="<?php echo isset($elid2show[$elid_t]["oid"]) ? $elid2show[$elid_t]["oid"] : "";?>" data-new="update" tabindex="">Speichern</button>
													</div>
													<?php } ?>
												</div>
											
											<?php } //Verstecken
											}} ?>
										<?php if(($oid_t == $OID_admin && $USER["gleich"]) || $USER["einsatzleitung"] || isset($org2show[$oid_t]["ersteller"]) &&  $org2show[$oid_t]["ersteller"] == $OID_admin){ ?>
											<button  class="btn btn-primary add_el ml-auto mb-4" data-oid="<?php echo $oid_t; ?>">Einsatzleiter hinzufügen</button>
										<?php } ?>
										</div>
									</div>
									<?php 
								} //Verstecken Organisationseintrag
							}
						}
							
							if($USER["gleich"]){ ?>
							<input type="hidden" id="elids" value="<?php echo implode(",", $elids); ?>">
							<input type="hidden" id="orgids" value="<?php echo implode(",", $oids_el); ?>">
						</div>
						<button  class="btn btn-primary bearbeiten mt-4" id="einst_bet_neu">Organisation hinzufügen</button>							
				<?php } ?>
					
				</div>
			</div>
			<div id="einsatz_organisationen" class="border-top mt-4">
				<button data-toggle="collapse" data-target="#einst_dar" class="btn btn-light w-100 collapsed text-left"><h5 class="fa">Voreinstellungen:</h5></button>
				<?php $disabled = $USER["gleich"] ? "" : " disabled"; ?>
				<div id="einst_dar" class="collapse">
					<form id="changesettings" method="post" class="mt-4">
						<div class="form-group">
							<label for="trackstart">Startdatum der Trackanzeige:[TT.MM.JJJJ HH:MM:SS]</label>
							<input class="form-control" type="text" name="trackstart" id="trackstart" <?php echo $disabled; ?> value="<?php echo date("d.m.Y H:i:s", $trackstart); ?>">
						</div>
						<div class="form-group">
							<label for="trackpause">Pausezeit bevor neuer Track geschrieben wird in s [3600]:</label>
							<input class="form-control" type="number" name="trackpause" id="trackpause" <?php echo $disabled; ?> value="<?php echo $trackpause; ?>">
						</div>
						<div class="form-group">
							<label for="newtrackloading">Timing für das neuladen der Usertracks in s [60]:</label>
							<input class="form-control" type="number" name="newtrackloading" id="newtrackloading" <?php echo $disabled; ?> value="<?php echo $newtrackloading/1000; ?>">
						</div>
						<div class="form-group">
							<label for="trackreload">Tracks nach <?php echo $newtrackloading/1000; ?> Sekunden neu schreiben:</label>
							<select name="trackreload" <?php echo $disabled; ?> id="trackreload" size="1">
								<option value="1"<?php if($trackreload == "1"){echo " selected";}?>>Ja</option>
								<option value="0"<?php if($trackreload == "0"){echo " selected";}?>>Nein</option>
							</select>
						</div>
						<div class="form-group">
							<label for="minpunkte">Minimale Anzahl an getrackten Punkten/Track [5]:</label>
							<input class="form-control" type="number" name="minpunkte" id="minpunkte" <?php echo $disabled; ?> value="<?php echo $minpunktefuertrack; ?>">
						</div>
						<div class="form-group">
							<label for="minspeed">Minimale Geschwingigkeit in m/s [0.001]:</label>
							<input class="form-control" type="text"  name="minspeed" id="minspeed" <?php echo $disabled; ?> value="<?php echo $minspeed; ?>" pattern="[0]+(\.\d{3})?$">
						</div>
						<div class="form-group">
							<label for="maxspeed">Maximale Geschwingigkeit in m/s [3.3335]:</label>
							<input class="form-control" type="text" name="maxspeed" id="maxspeed" <?php echo $disabled; ?> value="<?php echo $maxspeed; ?>" pattern="[3]+(\.\d{4})?$">
						</div>
						<?php if($USER["gleich"]){ ?>
						<button class="btn btn-primary mt-4 savesettings" type="button" data-toggle="modal" data-form="changesettings">eTrax Settings speichern</button>
						<?php } ?>
					</form>
				</div>
			</div>
		</div>
			
		<script>
			var eid = <?php echo $EID?>;//sessionStorage.getItem("eid");
			var wp = "api/";
			var rwdb = "read_write_db.php";
			
			function make_token(length) {
				   var result           = '';
				   var characters       = 'ABCDEFGH0123456789IJKLMNPQRSTUVWXYZ0123456789abcdefghijklmn0123456789opqrstuvwxyz0123456789';
				   var charactersLength = characters.length;
				   for ( var i = 0; i < length; i++ ) {
					  result += characters.charAt(Math.floor(Math.random() * charactersLength));
				   }
				   return result;
				}
				
			function escapeHTML(text) {
				var map = {
					'&': '&amp;',
					'<': '&lt;',
					'>': '&gt;',
					'"': '&quot;',
					"'": '&#039;',
					"\\": ''
				};
				if(typeof text === 'undefined'){
				} else {
					return text.replace(/[&<>"'\\]/g, function(m) { return map[m]; });
				}
			}
			
				
			/*let call_settings_table = function(a,t,c,val,json,callback,return_val){
				var jsondata = (json) ? json : '';//JSON.stringify(json)
				var jsonval = (val) ? val : '';
				//console.log('Values:' + val + ', json_nodes:' + jsondata);
				let database = {action: a,type: t,table: 'settings',column: c};
				jQuery.ajax({
					url: wp+'/'+rwdb,
					type: "post",
					scriptCharset: "utf-8" ,
					data: {
						database: database,
						select: {EID: eid},
						values: jsonval,
						json_nodes: jsondata
					}
				}).done(function(data){
					let value = (return_val == 1) ? data : '';
					(callback) ? callback(value) : '';
				});
			}
			
			
			let database_call = function(db,a,t,c,s,val,json,callback,return_val){
				var jsonval = (val) ? val : '';
				var jsondata = (json) ? json : '';
				var select = (s) ? s : '';
				let database = {table: db,column: c,action: a,type: t};
				jQuery.ajax({
					url: wp+rwdb,
					type: "post",
					scriptCharset: "utf-8" ,
					data: {
						database: database,
						select: select,
						values: jsonval,
						json_nodes: jsondata
					}
				}).done(function(data){
					let value = (return_val == 1) ? data : '';
					(callback) ? callback(value) : '';
				});
			}*/
			jQuery(function() {
				jQuery('[data-toggle="tooltip"]').tooltip();

				let Neuen_Administrator_anlegen = function(that){
					jQuery(".userliste").modal("hide");
					let newadminuser_rights = jQuery('.setRightAdminTemp').val(),
						newadminuserUID = that.attr("data-uid"),
						newadminuser = that.attr("data-name"),
						newadminorgname = that.attr("data-orgname"),
						newadminuserID = that.attr("data-dnr"),
						eid = sessionStorage.getItem("eid"),
						eids = that.attr("data-eid");
					if(eids == "-1" || eids == ""){
						eids = eid;
					} else {
						eids = eids+","+eid;
					}
					let values = {
						FID: ""+newadminuser_rights+"",
						EID: ""+eids+""
					};
					
					let Neuen_Administrator_anzeigen = function(){
						jQuery("#etraxadmins tbody").append("<tr class='adminrow"+newadminuserUID+"'><td>"+newadminuser+"</td><td>"+newadminorgname+"</td><td>Diesen Einsatz - Temporär</td><td class='text-right'><a href='#' class='delete' data-eid='"+eids+"' data-UID='"+newadminuserUID+"'><i class='material-icons red'>highlight_off</i></a></td></tr>");
					}
					
					database_call('user','update','no-json','data',{'UID': newadminuserUID},'',values,Neuen_Administrator_anzeigen,0);
				}
				
				jQuery(".chooseadmin").click(function(){
					Neuen_Administrator_anlegen(jQuery(this));
				});
				
				
				
				//Adminuser löschen
				let Administrator_loeschen = function(that){
					var admin=that.attr("data-uid");
					var fid=that.attr("data-fid");
					//var eid = sessionStorage.getItem("eid"); //EID des Einsatzes
					var eids = that.attr("data-eid");
					if (eids.includes(",")){
						if (eids.includes(","+eid)){
							eids = eids.replace(","+eid,"");
						}
						if (eids.includes(eid+",")){
							eids = eids.replace(eid+",","");
						}
						
					} else {
						var fid = "10";
						var eids = "-1";
					}
					var db_vars = {
						FID: ""+fid+"",
						EID: ""+eids+""
					};
				
					let Administrator_Eintrag_entfernen = function() {
						jQuery("tr.adminrow" + admin).remove();
					}
					
					database_call('user','update','no-json','data',{'UID': admin},'',db_vars,Administrator_Eintrag_entfernen,0);
					
				}
				
				jQuery("#etraxadmins").on("click","a.delete", function(){
					Administrator_loeschen($(this));
				});
				
				
				//Organisationsberechtigungen ändern - NEU
				let Organisationsberechtigungen_aendern = function(){
					//var eid = sessionStorage.getItem("eid"); //EID des Einsatzes
					var oids = $(".saveleadsettings").data('oids');
					oids = oids.split(',');
					console.log(oids);
					var Ogleich = [];
					var Ozeichnen = [];
					var Ozuweisen = [];
					var Osehen = [];
					for(k = 0; k < (oids.length); k++	){
						if($(".Ogleich_"+oids[k]).is(':checked')){
							Ogleich[k] = Ozeichnen[k] = Ozuweisen[k] = Osehen[k] = oids[k];
							$(".Ozeichnen_"+oids[k]).prop('checked', true).prop( "disabled", true );
							$(".Ozuweisen_"+oids[k]).prop('checked', true).prop( "disabled", true );
							$(".Osehen_"+oids[k]).prop('checked', true).prop( "disabled", true );
						} else {
							$(".Ozeichnen_"+oids[k]).prop( "disabled", false );
							$(".Ozuweisen_"+oids[k]).prop( "disabled", false );
							$(".Osehen_"+oids[k]).prop( "disabled", false );
						}
						if($(".Ozeichnen_"+oids[k]).is(':checked')){
							Ozeichnen[k] = Osehen[k] = oids[k];
							$(".Osehen_"+oids[k]).prop('checked', true).prop( "disabled", true );
						} else {
							if(!$(".Ozuweisen_"+oids[k]).is(':checked')){
								$(".Osehen_"+oids[k]).prop( "disabled", false );
							}
						}
						if($(".Ozuweisen_"+oids[k]).is(':checked')){
							Ozuweisen[k] = Osehen[k] = oids[k];
							$(".Osehen_"+oids[k]).prop('checked', true).prop( "disabled", true );
						} else {
							if(!$(".Ozeichnen_"+oids[k]).is(':checked')){
								$(".Osehen_"+oids[k]).prop( "disabled", false );
							}
						}
						if($(".Osehen_"+oids[k]).is(':checked')){ Osehen[k] = oids[k];}
					}
					Ogleich = Ogleich.join();
					Ozeichnen = Ozeichnen.join();
					Ozuweisen = Ozuweisen.join();
					Osehen = Osehen.join();
					var json_nodes = {
						Ogleich: ""+Ogleich+"",
						Ozeichnen: ""+Ozeichnen+"",
						Ozuweisen: ""+Ozuweisen+"",
						Osehen: ""+Osehen+""
					};
					
					database_call('settings','update','json','data',{EID: eid},'', json_nodes,'',0);
				}
				
				
				jQuery("#changeleadsettings input").change(function(){
					Organisationsberechtigungen_aendern();
				});
				
				jQuery(".savetracksettings").click(function(e){
					var sessionname = jQuery(this).attr("data-session");
					var value = jQuery("#strokewidth").val();
					jQuery.ajax({
						url: "api/updatesession.php",
						type: "post",
						data: {
							sessionname: sessionname,
							sessionval: value
						}
					}).done(function(e) {
						console.log(e);
						jQuery(".modal.settingsmodal").modal("hide");
						location.reload();
					});
				});
				
				//Neuen Organisation hinzufügen
				jQuery("#einst_bet_neu").click(function(e){
					var oid_t = 'Temp'+make_token(6);
					$('#einst_bet_org').append(''+
					'<div class="'+oid_t+'_bet_org">'+
						'<h6 class=""><input class="mb-2 form-control-plaintext checkJSON border-bottom border-white border-top-0" name="'+oid_t+'_orgname" id="'+oid_t+'_orgname" type="text" value="" placeholder="Organisationsbezeichnung"></h6>'+
						'<div class="form-group row m-2">'+
							'<label for="'+oid_t+'_orgadresse" class="col-sm-2 col-form-label" ><b>Anschrift:</b></label>'+
							'<div class="col-sm-10">'+
								'<input class="mb-2 form-control-plaintext checkJSON border-bottom border-white border-top-0" name="'+oid_t+'_orgadresse" id="'+oid_t+'_orgadresse" type="text" value="">'+
							'</div>'+
							'<label for="'+oid_t+'_ebeginndatum" class="col-sm-3 col-form-label" ><b>Einsatzbeginn:</b></label>'+
							'<div class="col-sm-5">'+
								'<input class="mb-2 form-control-plaintext checkJSON border-bottom border-white border-top-0" name="'+oid_t+'_ebeginndatum" id="'+oid_t+'_ebeginndatum" type="date" value="">'+
							'</div>'+
							'<label for="'+oid_t+'_ebeginnzeit" class="col-sm-2 col-form-label" ><b>Uhrzeit:</b></label>'+
							'<div class="col-sm-2">'+
								'<input class="mb-2 form-control-plaintext checkJSON border-bottom border-white border-top-0" name="'+oid_t+'_ebeginnzeit" id="'+oid_t+'_ebeginnzeit" type="time" value="">'+
							'</div>'+
							'<label for="'+oid_t+'_eendedatum" class="col-sm-3 col-form-label" ><b>Einsatzende:</b></label>'+
							'<div class="col-sm-5">'+
								'<input class="mb-2 form-control-plaintext checkJSON border-bottom border-white border-top-0" name="'+oid_t+'_eendedatum" id="'+oid_t+'_eendedatum" type="date" value="">'+
							'</div>'+
							'<label for="'+oid_t+'_eendezeit" class="col-sm-2 col-form-label" ><b>Uhrzeit:</b></label>'+
							'<div class="col-sm-2">'+
								'<input class="mb-2 form-control-plaintext checkJSON border-bottom border-white border-top-0" name="'+oid_t+'_eendezeit" id="'+oid_t+'_eendezeit" type="time" value="">'+
								'<input name="'+oid_t+'_ersteller" id="'+oid_t+'_ersteller" type="hidden" value="<?php echo $_SESSION["etrax"]["OID"]; ?>">'+
							'</div>'+
							'<label class="col-sm-10 col-form-label pl-4" for="'+oid_t+'_verstecken">Diese Information nur für die eigene Organisation anzeigen</label>'+
							'<div class="col-sm-1">'+
								'<input type="checkbox" class="form-control" id="'+oid_t+'_verstecken" name="'+oid_t+'_verstecken" value="<?php echo $_SESSION["etrax"]["OID"]; ?>">'+
							'</div>'+
							'<div class="col-sm-12 pt-4">'+
								'<button class="btn btn-primary eingabe org_speichern float-right mb-3" id="" data-oid_t="'+oid_t+'" data-new="append" tabindex="">Speichern</button>'+
								'<button class="abbrechen btn btn-danger float-right mr-2 mb-3" data-dismiss="modal" tabindex="">Abbrechen</button>'+
							'</div>'+
						'</div>'+
						//'<button  class="btn btn-success add_el" data-oid="'+oid_t+'" style="margin-top:15px;margin-bottom:15px;">Einsatzleiter hinzufügen</button>'+
					'</div>'+
						'');
					
					//var orgids = $('#orgids').val();
					//$('#orgids').val(orgids+','+oid_t);
				});
				
				//Neuen Organisation hinzufügen
				$("body").delegate(".org_speichern", "click", function(e){
				//jQuery(".org_speichern").click(function(e){
					var oid_t = $(this).data("oid_t");
					var neu = $(this).data("new");
					if(oid_t.substring(0,4) != "Temp"){
					var	json_nodes = {	"id" : oid_t,
										"ebeginndatum" : escapeHTML($('#'+oid_t+'_ebeginndatum').val()),
										"ebeginnzeit" : escapeHTML($('#'+oid_t+'_ebeginnzeit').val()),
										"eendedatum" : escapeHTML($('#'+oid_t+'_eendedatum').val()),
										"eendezeit" : escapeHTML($('#'+oid_t+'_eendezeit').val()),
										"ersteller" : escapeHTML($('#'+oid_t+'_verstecken').val()),
										"verstecken" : $('#'+oid_t+'_verstecken').prop("checked") ? $('#'+oid_t+'_verstecken').val() : false
									};
					} else {
						//Für Temporäre Organisationen
					var	json_nodes = {	"id" : oid_t,
										"ebeginndatum" : escapeHTML($('#'+oid_t+'_ebeginndatum').val()),
										"ebeginnzeit" : escapeHTML($('#'+oid_t+'_ebeginnzeit').val()),
										"eendedatum" : escapeHTML($('#'+oid_t+'_eendedatum').val()),
										"eendezeit" : escapeHTML($('#'+oid_t+'_eendezeit').val()),
										"ersteller" : escapeHTML($('#'+oid_t+'_verstecken').val()),
										"verstecken" : $('#'+oid_t+'_verstecken').prop("checked") ? $('#'+oid_t+'_verstecken').val() : false,
										"orgname" : escapeHTML($('#'+oid_t+'_orgname').val()),
										"orgadresse" : escapeHTML($('#'+oid_t+'_orgadresse').val())
									};
						
					}
					if(neu == "append"){	
						database_call('settings','update','json_append','orginfo',{EID: eid},oid_t,json_nodes,'',0);
						var orgids = $('#orgids').val();
						$('#orgids').val(orgids+','+oid_t);
						location.reload();
					} else { 
						//Update
						database_call('settings','update','json_update','orginfo',{EID: eid},oid_t,json_nodes,'',0);
					}
				});
				
				//Neuen Einsatzleiter hinzufügen
				jQuery(".add_el").click(function(e){
					var oid = $(this).data("oid");
					var elids = $('#elids').val();
					var id = oid+'_'+make_token(6);
					$('.'+oid+'_bet_org').append(''+
					'<div class="form-group row ml-4 mr-1" style="background-color:#aee571;border-radius:5px;">'+
						'<label for="'+id+'-el_name" class="col-sm-2 col-form-label" ><b>Name:</b></label>'+
							'<div class="col-sm-4">'+
								'<input class="mb-2 form-control-plaintext checkJSON border-bottom border-white border-top-0" name="'+id+'-el_name" id="'+id+'-el_name" type="text" value="" tabindex="1000">'+
							'</div>'+
						'<label for="'+id+'-el_tel" class="col-sm-2 col-form-label" ><b>Tel.Nr.:</b></label>'+
							'<div class="col-sm-4">'+
								'<input class="mb-2 form-control-plaintext checkJSON border-bottom border-white border-top-0" name="'+id+'-el_tel" id="'+id+'-el_tel" type="text" value="" tabindex="1001">'+
							'</div>'+
						'<label for="'+id+'-el_vondatum" class="col-sm-2 col-form-label" ><b>Von:</b></label>'+
							'<div class="col-sm-4">'+
								'<input class="mb-2 form-control-plaintext checkJSON border-bottom border-white border-top-0" name="'+id+'-el_vondatum" id="'+id+'-el_vondatum" type="date" value="" tabindex="1002">'+
							'</div>'+
						'<label for="'+id+'-el_vonzeit" class="col-sm-2 col-form-label" ><b>Uhrzeit:</b></label>'+
							'<div class="col-sm-4">'+
								'<input class="mb-2 form-control-plaintext checkJSON border-bottom border-white border-top-0" name="'+id+'-el_vonzeit" id="'+id+'-el_vonzeit" type="time" value="" tabindex="1003">'+
							'</div>'+
						'<label for="'+id+'-el_bisdatum" class="col-sm-2 col-form-label" ><b>Bis:</b></label>'+
							'<div class="col-sm-4">'+
								'<input class="mb-2 form-control-plaintext checkJSON border-bottom border-white border-top-0" name="'+id+'-el_bisdatum" id="'+id+'-el_bisdatum" type="date" value="" tabindex="1004">'+
							'</div>'+
						'<label for="'+id+'-el_biszeit" class="col-sm-2 col-form-label" ><b>Uhrzeit:</b></label>'+
							'<div class="col-sm-4">'+
								'<input class="mb-2 form-control-plaintext checkJSON border-bottom border-white border-top-0" name="'+id+'-el_biszeit" id="'+id+'-el_biszeit" type="time" value="" tabindex="1005">'+
								'<input name="'+id+'-el_ersteller" id="'+id+'-el_ersteller" type="hidden" value="'+ oid + '">'+
							'</div>'+
						'<label class="col-sm-10 col-form-label pl-4" for="'+id+'-el_verstecken">Diese Information nur für die eigene Organisation anzeigen</label>'+
							'<div class="col-sm-1">'+
								'<input type="checkbox" class="form-control" id="'+id+'-el_verstecken" name="'+id+'-el_verstecken" value="<?php echo $_SESSION["etrax"]["OID"]; ?>" tabindex="1006">'+
							'</div>'+
						'<div class="col-sm-12 pt-4">'+
								'<button class="btn btn-primary eingabe el_speichern float-right mb-3" id="" data-elid_t="'+id+'" data-oid_t="'+oid+'" data-new="append" tabindex="">Speichern</button>'+
								'<button class="abbrechen btn btn-danger float-right mr-2 mb-3" data-dismiss="modal" tabindex="">Abbrechen</button>'+
							'</div>'+
					'</div>'+
						'');
						
					$('#elids').val(elids+','+id);
				});
				
				//Neuen Einsatzleiter hinzufügen
				//jQuery(".el_speichern").click(function(e){
				$("body").delegate(".el_speichern", "click", function(e){
					var oid_t = $(this).data("oid_t");
					var elid_t = $(this).data("elid_t");
					var neu = $(this).data("new");
					
					var	json_nodes = {	"id" : elid_t,
										"oid" : oid_t,
										"name" : escapeHTML($('#'+elid_t+'-el_name').val()),
										"tel" : escapeHTML($('#'+elid_t+'-el_tel').val()),
										"vondatum" : escapeHTML($('#'+elid_t+'-el_vondatum').val()),
										"vonzeit" : escapeHTML($('#'+elid_t+'-el_vonzeit').val()),
										"bisdatum" : escapeHTML($('#'+elid_t+'-el_bisdatum').val()),
										"biszeit" : escapeHTML($('#'+elid_t+'-el_biszeit').val()),
										"verstecken" : $('#'+elid_t+'-el_verstecken').prop("checked") ? $('#'+elid_t+'-el_verstecken').val() : false
									};
						
					if(neu == "append"){	
						database_call('settings','update','json_append','orginfo',{EID: eid},elid_t,json_nodes,'',0);
						var orgids = $('#orgids').val();
						$('#orgids').val(orgids+','+oid_t);
						location.reload();
					} else { 
						//Update
						database_call('settings','update','json_update','orginfo',{EID: eid},elid_t,json_nodes,'',0);
					}
				});
				
				//Löschen - Bestätigung - Organisation oder EL
				jQuery(".delete_elorg").click(function(e){
					var id = $(this).data("id");
					$("#"+id+"_del_confirm").show();
					
				});
				
				//Löschen - Durchführen - Organisation oder EL
				jQuery(".delete_elorg_confirm").click(function(e){
					var id = $(this).data("id");
					database_call('settings','update','json_delete','orginfo',{EID: eid},'',id,'',0);
					location.reload();
				});
				
				/*
				//Infos zu den Organisationen speichern
				let Organisationsinfos_speichern = function() {
					var eid = sessionStorage.getItem("eid"); //EID des Einsatzes
					var elids = $('#elids').val();
					if (elids.includes(",")){	
						elids = elids.split(",");
						elids = elids.filter(function () { return true }); //entfernt keys mit leeren values
					} else {
						elids = [elids];
					}
					var orgids = $('#orgids').val();
					if (orgids.includes(",")){	
						orgids = orgids.split(",");
						orgids = orgids.filter(function () { return true }); //entfernt keys mit leeren values
					} else {
						orgids = [orgids];
					}
					//für read_write_db
					var database = {
						type: "json",		// defining datatype json/single value (json/val)
						action: "update",	//action read or write
						table: "settings",	// DB Table
						column: "orginfo"		// DB Table column for jsons to be changed
					};
					// Entries to be display (key: value) 
					// bei json auslesen nur 1 Eintrag!
					var select = {
						EID : ""+eid+""
					}
					// json Node to be changed (nodename: value)
					var json_nodes = {};
					//Infos zur Organisation speichern
					orgids.forEach(function(oid,i){
						obj_temp = {};
						obj_temp[oid+'_ebeginndatum'] = escapeHTML($('#'+oid+'_ebeginndatum').val());
						obj_temp[oid+'_ebeginnzeit'] = escapeHTML($('#'+oid+'_ebeginnzeit').val());
						obj_temp[oid+'_eendedatum'] = escapeHTML($('#'+oid+'_eendedatum').val());
						obj_temp[oid+'_eendezeit'] = escapeHTML($('#'+oid+'_eendezeit').val());
						obj_temp[oid+'_ersteller'] = escapeHTML($('#'+oid+'_ersteller').val());
						obj_temp[oid+'_verstecken'] = $('#'+oid+'_verstecken').prop("checked") ? $('#'+oid+'_verstecken').val() : false;
						//Für Temporäre Organisationen
						if(oid.substring(0,4) == "Temp"){
							obj_temp[oid+'_orgname'] = escapeHTML($('#'+oid+'_orgname').val());
							obj_temp[oid+'_orgadresse'] = escapeHTML($('#'+oid+'_orgadresse').val());
						}
						$.extend(json_nodes,obj_temp);
					});
					
					//Infos zu den Einsatzleitern speichern
					elids.forEach(function(elid,i){
						obj_temp = {};
						obj_temp[elid+'-el_name'] = escapeHTML($('#'+elid+'-el_name').val());
						obj_temp[elid+'-el_tel'] = escapeHTML($('#'+elid+'-el_tel').val());
						obj_temp[elid+'-el_vondatum'] = escapeHTML($('#'+elid+'-el_vondatum').val());
						obj_temp[elid+'-el_vonzeit'] = escapeHTML($('#'+elid+'-el_vonzeit').val());
						obj_temp[elid+'-el_bisdatum'] = escapeHTML($('#'+elid+'-el_bisdatum').val());
						obj_temp[elid+'-el_biszeit'] = escapeHTML($('#'+elid+'-el_biszeit').val());
						obj_temp[elid+'-el_ersteller'] = escapeHTML($('#'+elid+'-el_ersteller').val());
						obj_temp[elid+'-el_verstecken'] = $('#'+elid+'-el_verstecken').prop("checked") ? $('#'+elid+'-el_verstecken').val() : false;
						$.extend(json_nodes,obj_temp);
					});
					console.log(json_nodes);
					// direct changes in db ( fieldname: value)
					
					//database_call('settings','update','json','orginfo',{EID: eid},'',json_nodes,'Organisationsinfos_gespeichern',0);
					database_call('settings','update','json','orginfo',{EID: eid},'',json_nodes,'',0);
					
				}*/
				
				
				
				/*$("#einst_bet_submit").click(function() {	
					Organisationsinfos_speichern();
				});*/
				
				//Darstellungs Einstellungen speichern
				let Einstellungen_speichern = function(){
					var HFquote = $("#HFquote").val();
					var ts = $("#trackstart").val();
					var date = new Date(ts.substr(6,4),(ts.substr(3,2)-1),ts.substr(0,2),ts.substr(11,2),ts.substr(14,2),ts.substr(17,2));
					console.log(date);
					var trackstart = Math.floor(date.getTime() / 1000);
					console.log(trackstart);
					var trackpause = $("#trackpause").val();
					var newtrackloading = $("#newtrackloading").val()*1000;
					var trackreload = $("#trackreload").val();
					var minpunkte = $("#minpunkte").val();
					var minspeed = $("#minspeed").val();
					var maxspeed = $("#maxspeed").val();
					
					var json_nodes = {
						HFquote: ""+HFquote+"",
						trackstart: ""+trackstart+"",
						trackpause: ""+trackpause+"",
						newtrackloading: ""+newtrackloading+"",
						trackreload: ""+trackreload+"",
						minpunkte: ""+minpunkte+"",
						minspeed: ""+minspeed+"",
						maxspeed: ""+maxspeed+""
						
					};
					
					database_call('settings','update','json','data',{EID: eid},'',json_nodes,'Organisationsinfos_gespeichern',0);
					//database_call('settings','update','json','data',{EID: eid},'',json_nodes,'',0);
				}
				
				let Organisationsinfos_gespeichern = function(){
					$(".settingsmodal .modal-body").load( "api/einstellungen.php", {"notroot":2} );
					$(".protokoll").show().find(".form-control").prop('readonly', true).removeClass("form-control").addClass("form-control-plaintext");
				}
				
				jQuery(".savesettings").click(function(e){
					Einstellungen_speichern();
				});
			});
		</script>		
	<?php  
	
	} else {
		echo "Sie verfügen nicht über die notwendigen Rechte um den Inhalt angezeigt zu bekommen.";
	}?>	
	
		
		
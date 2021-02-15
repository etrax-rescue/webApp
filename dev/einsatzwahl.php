<?php
session_start();
($_SESSION["etrax"]["usertype"] ? '' : header('Location: index.php'));
require "../../secure/info.inc.php";
require "../../secure/secret.php";
require "include/verschluesseln.php";
define("sessionstart",false);
require "include/sessionhandler.php"; //Der Sessionhandler schreibt die Sessionwerte neu
require "include/startseitentexte.php"; //Textelemente auf der Startseite - für Datenschutzinfo

$beobachter = ($_SESSION["etrax"]["usertype"] == "beobachter") ? true : false;
$userlevel = $_SESSION["etrax"]["userlevel"];
$userrechte = $_SESSION["etrax"]["userrechte"];
$UID_admin = $_SESSION["etrax"]["UID"];
$OID_admin = $_SESSION["etrax"]["adminOID"];

$EID_query = $db->prepare("SELECT EID FROM settings ORDER BY EID DESC LIMIT 1");
$EID_query->execute() or die(print_r($EID_query->errorInfo(), true));
while ($row = $EID_query->fetch(PDO::FETCH_ASSOC)){
$EID = $row['EID'];
}
$imEinsatzCount = $EIDaktuell = 0;
//Einsatz wählen in dem User zugewiesen ist
$imEinsatz_query = $db->prepare("SELECT aktiveEID FROM user WHERE UID = ? ");
$imEinsatz_query->bindParam(1, $_SESSION["etrax"]["UID"], PDO::PARAM_STR);
$imEinsatz_query->execute();
//ErrorInfo
$errorInfo = $imEinsatz_query->errorInfo();
echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";

while ($row = $imEinsatz_query->fetch(PDO::FETCH_ASSOC)){
	$einsatz_query = $db->prepare("SELECT EID,data FROM settings WHERE EID = ? ");
	$einsatz_query->bindParam(1, $row["aktiveEID"], PDO::PARAM_STR);
	$einsatz_query->execute();
	//ErrorInfo
	$errorInfo = $einsatz_query->errorInfo();
	echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
	
	while ($einsaetze = $einsatz_query->fetch(PDO::FETCH_ASSOC)){
		$einsatz_data = json_decode(substr(string_decrypt($einsaetze["data"]), 1, -1));
		$imEinsatzCount += 1;
		$EIDaktuell = $einsaetze['EID'];
		$_SESSION["etrax"]["EID"] = $EIDaktuell;
		$Enameaktuell = $einsatz_data->einsatz;
	}
}

//User aus der DB holen
$db_mitglieder = $db->prepare("SELECT * FROM user WHERE UID = '".$_SESSION["etrax"]["UID"]."'");
$db_mitglieder->execute() or die(print_r($db_mitglieder->errorInfo(), true));
$user_arr;
//print_r($db_mitglieder);
$n_user = 0;
$letterl = $usernames = $alledienstnummern = "";
while ($res_mg = $db_mitglieder->fetch(PDO::FETCH_ASSOC)){
	$data_user_json = json_decode(substr(string_decrypt($res_mg['data']), 1, -1));
	$user_arr = array('UID' => $res_mg['UID'], 
						'OID'   => $res_mg['OID'], 
						'FID'   => $res_mg['FID'], 
						'name'   => isset($data_user_json->name) ? $data_user_json->name : "", 
						'dienstnummer'   => isset($data_user_json->dienstnummer) ? $data_user_json->dienstnummer : "", 
						'typ'   => isset($data_user_json->typ) ? $data_user_json->typ : "", 
						'pause'   => (isset($data_user_json->pause) && is_numeric($data_user_json->pause)) ? $data_user_json->pause/60 : 0, 
						'username'   => isset($data_user_json->username) ? $data_user_json->username : "", 
						'password'   => isset($data_user_json->pwd) ? $data_user_json->pwd : "", 
						'ausbildungen'   => isset($data_user_json->ausbildungen) ? $data_user_json->ausbildungen : "", 
						'email'   => isset($data_user_json->email) ? $data_user_json->email : "", 
						'bos'   => isset($data_user_json->bos) ? $data_user_json->bos : "", 
						'telefon'   => isset($data_user_json->telefon) ? $data_user_json->telefon : "", 
						'einsatzfaehig'   => isset($data_user_json->einsatzfaehig) ? $data_user_json->einsatzfaehig : "0", 
						'notfallkontakt'   => isset($data_user_json->notfallkontakt) ? $data_user_json->notfallkontakt : "", 
						'notfallinfo'   => isset($data_user_json->notfallinfo) ? $data_user_json->notfallinfo : "", 
						'kommentar'   => isset($data_user_json->kommentar) ? $data_user_json->kommentar : "",
						'lastupdate'   => $res_mg['lastupdate']);
	$n_user++;
	$usernames .= isset($data_user_json->username) ? ";".$data_user_json->username : "";
	$alledienstnummern .= isset($data_user_json->dienstnummer) ? ";".$data_user_json->dienstnummer : "";
}

($_SESSION["etrax"]["usertype"] == "administrator") ? $UserOID = $_SESSION["etrax"]["OID"] : $UserOID = $_SESSION["etrax"]["OID"];
//$org_sql = $db->prepare("SELECT data, funktionen FROM organisation WHERE OID LIKE '".$UserOID."'");
//Liste der in einer Organisation vorkommenden Funktionen aufbauen
$org_sql = $db->prepare("SELECT OID, data, funktionen FROM organisation");
$org_sql->execute() or die(print_r($org_sql->errorInfo(), true));
$oids = array();
while ($roworg = $org_sql->fetch(PDO::FETCH_ASSOC)){
	$orgdata_decrypted = json_decode(substr(string_decrypt($roworg['data']), 1, -1));
	if($roworg['OID'] == $UserOID){ //Setzen der Session variablen
		$_SESSION["etrax"]["ORGname"] = isset($orgdata_decrypted->bezeichnung) ? $orgdata_decrypted->bezeichnung :"";
		$_SESSION["etrax"]["ORGnameshort"] = isset($orgdata_decrypted->kurzname) ? $orgdata_decrypted->kurzname :"";
		$_SESSION["etrax"]["ORGadresse"] = isset($orgdata_decrypted->adresse) ? $orgdata_decrypted->adresse :"";
		$datenschutzbeauftragter = isset($orgdata_decrypted->datenschutzbeauftragter) ? $orgdata_decrypted->datenschutzbeauftragter :"Es wurde noch kein Datenschutzbeauftragter hinterlegt.";
		//Verfügbare Funktionen in der Organisation
		$funktionen = json_decode($roworg['funktionen'], true);
		$fun_list = array();
		if(!empty($funktionen)){
			foreach($funktionen as $key => $val){
				$fun_list[$val["kurz"]] = $val["lang"];
				}
		}
	}
	$oids[$roworg['OID']] = isset($orgdata_decrypted->kurzname) ? $orgdata_decrypted->kurzname :"";
}

require("include/header.html");
?>		
		<script src="vendor/js/jquery-3.5.1.min.js"></script>
		<script src="vendor/js/bootstrap.bundle.min.js"></script>
	</head>
	<body>
		<?php
		//Ausgabe
		require('include/edit-navbar.php');
		//Kopfzeile
	
		//Infozeile für die Entwicklung
		if($_SESSION["etrax"]["adminOID"] == "DEV" || preg_match("/@testmagic/",$_SESSION["etrax"]["name"])){ ?>
		<div class="infofooter">
			<div class="btn-group dropup">
				<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					Developerinfo
				</button>
				<ul class="dropdown-menu pt-2">
					<li class="dropdown-item small"><?php echo "EID - Session: ".(isset($_SESSION["etrax"]["EID"]) ? $_SESSION["etrax"]["EID"] : 'noch nicht gesetzt');?></li>
					<li class="dropdown-item small"><?php echo "OID - Session: ".$_SESSION["etrax"]["OID"];?></li>
					<li class="dropdown-item small"><?php echo "Userlevel - Session: ".$_SESSION["etrax"]["userlevel"];?></li>
					<li class="dropdown-item small"><?php echo "Userrechte - Session: ".$_SESSION["etrax"]["userrechte"];?></li>
					<li class="dropdown-item small"><?php echo "FID - Session: ".$_SESSION["etrax"]["FID"];?></li>
					<li class="dropdown-item small"><?php echo "UID - Session: ".$_SESSION["etrax"]["UID"];?></li>
					<li class="dropdown-item small"><?php echo "usertype - Session: ".$_SESSION["etrax"]["usertype"];?></li>
					<li class="dropdown-item small"><?php echo "adminEID - Session: ".$_SESSION["etrax"]["adminEID"];?></li>
					<li class="dropdown-item small"><?php echo "adminOID - Session: ".$_SESSION["etrax"]["adminOID"];?></li>
					<li class="dropdown-item small"><?php echo "adminID - Session: ".$_SESSION["etrax"]["adminID"];?></li>
					<li class="dropdown-item small"><?php echo "name - Session: ".$_SESSION["etrax"]["name"];?></li>
					<li class="dropdown-item small"><?php echo "dienstnummer - Session: ".$_SESSION["etrax"]["dienstnummer"];?></li>
					<li class="dropdown-item small"><?php echo 'etraxadmin - Session: '.($_SESSION["etrax"]["etraxadmin"] ? 'true': 'false'); ?></li>
					<li class="dropdown-item small"><?php echo 'mapadmin - Session: '.($_SESSION["etrax"]["mapadmin"] ? 'true': 'false'); ?></li>
					<li class="dropdown-item small"><?php echo 'Token: '.$_SESSION["etrax"]["token"]; ?></li>
				</ul>
			</div>
		</div>
		<?php }	?>
	
		<div class="modal fade einsatzneu" tabindex="-1" role="dialog" id="einsatzneu" aria-labelledby="neuereinsatzheader" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="neuereinsatzheader">Neuer Einsatz</h5>
					</div>
					<div class="modal-body">
						<div class="col-12 text-left">
							<div class="form-group">
								<label for="einsatzname">Einsatzort:</label>
								<input type="text" name="einsatzname" id="einsatzname" value="">
							</div>
							<div class="form-group">
								<?php 
								if(isset($_SESSION['etrax']['googleAPI']) && $_SESSION['etrax']['googleAPI'] != ''){
									echo '<label for="einsatzlon">RW:</label><input type="text" name="einsatzlon" id="einsatzlon" value=""><br>
										<label for="einsatzlat">HW:</label><input type="text" name="einsatzlat" id="einsatzlat" value=""><br>';
								}else{
									echo '<label for="einsatzlon">RW:</label><input type="text" name="einsatzlon" id="einsatzlon" placeholder="16.334" value=""><br>
										<label for="einsatzlat">HW:</label><input type="text" name="einsatzlat" id="einsatzlat" placeholder="48.3036" value=""><br>';
								}
								?>
							</div>
							<div class="form-group">
								<label for="time">Einsatzbeginn:</label>
								<input type="text" name="einsatzstart" id="einsatzstart" value="<?php echo date('Y-m-d H:i:s'); ?>" > 
							</div>
							<div class="form-group">
								<label for="primorg">Primärorganisation:</label>
								<select name="primorg" id="primorg" size="1" class="">
								<?php
								//Schleife für das array der Organisationen
								$db_org = $db->prepare("SELECT * FROM organisation WHERE aktiv = 1");
								$db_org->execute() or die(print_r($db_org->errorInfo(), true));
								$org_arr_check = array();
								$n_org = $bundesland_sort = 0;
								while ($reso_check = $db_org->fetch(PDO::FETCH_ASSOC)){
									$data_org_json_check = json_decode(substr(string_decrypt($reso_check['data']), 1, -1));
									
									$org_arr_check[$reso_check["OID"]]["OID"] = $reso_check["OID"];
									$org_arr_check[$reso_check["OID"]]["bezeichnung"] = isset($data_org_json_check->bezeichnung) ? $data_org_json_check->bezeichnung : "";
									$org_arr_check[$reso_check["OID"]]["kurzname"] = isset($data_org_json_check->kurzname) ? $data_org_json_check->kurzname : "";
									$org_arr_check[$reso_check["OID"]]["orgfreigabe"] = $reso_check["orgfreigabe"];
									$org_arr_check[$reso_check["OID"]]["aktiv"] = $reso_check["aktiv"];
									$n_org++;
								}
								$k = 0;
								//Eigene Organisation zuerst
									echo "<option class='' value='".$org_arr_check[$_SESSION["etrax"]["OID"]]["OID"]."'>".$org_arr_check[$_SESSION["etrax"]["OID"]]["kurzname"]."</option>";
								
								//JSON mit den von eigener Organisation freigegebenen Organisationen
								$jtemp = json_decode($org_arr_check[$_SESSION["etrax"]["OID"]]["orgfreigabe"],true); 
								if($n_org > 0) { //Wenn keine weiteren Organisationen angelegt sind.
									foreach($org_arr_check as $org => $val){
										if($_SESSION["etrax"]["OID"] != $val["OID"]){
										//Checken ob die OID von der Organisation freigegeben wurde
										$jtemp2 = json_decode($org_arr_check[$val["OID"]]["orgfreigabe"],true); //JSON mit den von temporärer Organisation freigegebenen Organisationen
										if(isset($jtemp[$val["OID"]])){
											$ctemp = "checked"; //Für Checkbox - eigene Freigabe
											if(isset($jtemp2[$_SESSION["etrax"]["OID"]])){ 	
												//Für Organisation freigegeben und von Organisation freigegeben
												//$ctext = $org_arr_check[$_SESSION["etrax"]["OID"]]["kurzname"]." und  ".$org_arr_check[$val["OID"]]["kurzname"]." tauschen Daten aus.";
												echo "<option class='' value='".$org_arr_check[$val["OID"]]["OID"]."'>".$org_arr_check[$val["OID"]]["kurzname"]."</option>";
											} else {
												//Für Organisation freigegeben und von Organisation nicht freigegeben
											}
										} else {
											$ctemp = "";
											if(isset($jtemp2[$_SESSION["etrax"]["OID"]])){ 	
												//Für Organisation nicht freigegeben und von Organisation freigegeben
											} else {
												//Für Organisation freigegeben und von Organisation nicht freigegeben
											}
										}
										$k++;
										}
									}
								}?>
							</select>
							</div>
							<div class="form-group e_typ">
								<input type="radio" name="typ" id="e" value="einsatz" checked> <label for="e">Einsatz</label><br>
								<input type="radio" name="typ" id="ue" value="uebung"> <label for="ue">Übung</label>
							</div>
						</div>
						<div class="modal-footer">
							<div id="einsatzbeginn" class="col-12 d-flex justify-content-center">
								<button type="button" class="btn btn-success beginn mr-3" data-eid="<?php echo $neueEID;?>"><i class="material-icons color-white">check_circle</i> starten</button>
								<button type="button" class="btn btn-danger abbruch"><i class="material-icons color-white">cancel</i> abbrechen</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div id="angemeldet"></div>
		<div class="servicediv"  id="einsatzauswahl">
			
				<?php //Anzeige der Einsätze
					$n_einsatz = $n_uebung = 1;
					if(!isset($_POST['einsatz']) && $_SESSION["etrax"]["usertype"] == "administrator"){
						$adminEIDs = explode(",",$_SESSION["etrax"]["adminEID"]);
						//Wenn die Userrechte nicht mindestens "Alle Rechte" sind, wird der Button nicht angezeigt
						if($userrechte <= 2){
							echo '<button class="btn btn-primary m-4" type="button" id="einsatzneu">neuer Einsatz / neue Übung</button>';
						}
						$einsatz_query = $db->prepare("SELECT EID,data,typ FROM settings ORDER BY typ ASC, EID DESC");
						$einsatz_query->execute() or die(print_r($einsatz_query->errorInfo(), true));
						$switch_uebung = "";
						while ($einsaetze = $einsatz_query->fetch(PDO::FETCH_ASSOC)){
							$einsatztyp = $einsaetze["typ"];
							$einsatz_data = json_decode(substr(string_decrypt($einsaetze["data"]), 1, -1));
							$OID = isset($einsatz_data->OID) ? $einsatz_data->OID : "";
							$Ogleich = isset($einsatz_data->Ogleich) ? $einsatz_data->Ogleich : "";
							$Ozeichnen = isset($einsatz_data->Ozeichnen) ? $einsatz_data->Ozeichnen : "";
							$Ozuweisen = isset($einsatz_data->Ozuweisen) ? $einsatz_data->Ozuweisen : "";
							$Osehen = isset($einsatz_data->Osehen) ? $einsatz_data->Osehen : "";
							$ende = isset($einsatz_data->ende) ? $einsatz_data->ende : "";
							//Alle OIDs die irgendwie im Einsatz teilnehmen
							$oids_t = explode(",",$OID.",".$Ogleich.",".$Ozeichnen.",".$Ozuweisen.",".$Osehen);
							$oids_t = array_unique(array_filter($oids_t));
							
							//($einsaetze['EID'] == $neueEID-1) ? $newE = " selected" : $newE = "";
							if($einsatz_data->ende == ""){
								$styling = " border-green";
								$header_styling = " color-green";
							}else{
								$styling = " border-red bg-red-50'";
								$header_styling = " color-red";
							}
							$beendet = $einsatz_data->ende == "" ? "" : ", beendet";
							

							if(in_array($OID_admin,$oids_t) || $OID_admin == "DEV" ){
								//if($ende == "" || $OID_admin == "DEV"){
								if($einsatztyp === "einsatz"){
									$button_text =  "Zum Einsatz";
									$uebung = "Einsätze";
									$uebung_color = " text-success";
									$style2hide = $n_einsatz > 4 ? "style='display:none'" : "";
									$class2hide = $n_einsatz > 4 ? " ShowMoreE" : "";
									$n_einsatz++;
								}else{
									$button_text =  "Zur Übung";
									$uebung = "Übungen";
									$uebung_color = " text-info";
									$style2hide = $n_uebung > 4 ? "style='display:none'" : "";
									$class2hide = $n_uebung > 4 ? " ShowMoreU" : "";
									$n_uebung++;
								}
									//Wenn die Userrechte nicht mindestens "Permanenter Einsatzleiter" sind und der Admintyp der Organisationsadministrator ist, wird der Button nicht angezeigt
									if($userlevel <= 3 && $userrechte <= 1){
										$delete_button ='<button class="e-delete btn btn-danger mt-4 mr-2" data-eid="'.$einsaetze["EID"].'" data-ename="'.$einsatz_data->einsatz.'">Löschen</button>';
									} else {
										$delete_button = "";
									}
									if($switch_uebung != $einsatztyp){
										if($switch_uebung == ""){	
											echo "<div class='category card m-4'><h2 class='card-header'>".$uebung."</h2><div class='card-body grid p-0 pt-4 pb-4'>";
										} else {	
											echo "</div>";
											echo $n_einsatz > 5 ? "<div><button class='btn btn-success float-right m-4' id='ShowMoreE' type='button'>Alle Einsätze anzeigen</button></div>" : "";
											echo "</div><div class='category uebung card m-4'><h2 class='card-header'>".$uebung."</h2><div class='card-body grid p-0 pt-4 pb-4'>";
										}
									}
									$switch_uebung = $einsatztyp;
									
									
									
									$bg_color = $ende == "" ? "class='card-header ".$uebung_color."'" : "class='card-header text-secondary'";
									
									if ($_SESSION["etrax"]["usertype"] == "administrator" && $_SESSION["etrax"]["adminEID"] == 0){
										$EName = isset($einsatz_data->einsatz) ? $einsatz_data->einsatz : "";
										?>
										<div id="e<?php echo $einsaetze['EID']; ?>-card" class="einsatz-card <?php echo $class2hide.'" '.$style2hide; ?>>
										<form class="einsatzauswahl" method="post" action="einsatz-start.php">
											<input type="hidden" value="<?php echo $einsaetze['EID']; ?>" name="einsatz" class="einsatzID">
											<div class="card mb-4 <?php echo $styling;?>">
												<div class="card-body">
													<h5 class="card-title <?php echo $uebung_color.$header_styling; ?>"><?php echo $einsatz_data->einsatz.$beendet; ?></h5>
													<p class="card-text"><?php echo date("d.m.Y",strtotime($einsatz_data->anfang)); ?></p>
													<ul class="list-group">
														<li  class="list-group-item bg-success color-white font-weight-bold">Primärorganisation:</li>
														<li class="list-group-item"><?php echo $oids[$OID]; ?></li>
														<?php
														$i = 0;
														if(count($oids_t) > 1){
															foreach($oids_t as $oid_tc){
																echo $i == 0 ? '<li class="list-group-item bg-info color-white font-weight-bold">Beteiligte Organisationen:</li>' : "";
																echo ($oid_tc != $OID && $oid_tc != "DEV") ? '<li class="list-group-item">'.$oids[$oid_tc].'</li>' : "";
															
																$i++;
																
															}
														}
														?>
													</ul>
													<a class="einsatz-start btn btn-primary float-right mt-4 color-white" data-eid="<?php echo $einsaetze['EID']; ?>" data-name="<?php echo $einsatz_data->einsatz; ?>"><?php echo $button_text?></a>
													<?php echo ($ende ? $delete_button : ''); ?>
												</div>
											</div>
										</form>
										</div>
										<?php
										// echo "<option value='".$einsaetze['EID']."' data-einsatzname='".$einsatz_data->einsatz."'".$styling.$newE." ".$bg_color.">".$uebung.$einsatz_data->einsatz." [".$einsatz_data->anfang."]</option>\n";
										
											$newE = "";
									}elseif(in_array($einsaetze['EID'],$adminEIDs) && $ende == ""){ //Anzeige für die temporären Administratoren
										$EName = isset($einsatz_data->einsatz) ? $einsatz_data->einsatz : "";
										?>
										<div class="col-12 col-md-6 col-lg-4 einsatz-card">
										<form id="einsatzauswahl" method="post" action="einsatz-start.php">
											<input type="hidden" value="<?php echo $einsaetze['EID']; ?>" name="einsatz" >
											<div class="card mb-4" <?php echo $styling; ?>>
												<h5 <?php echo $bg_color; ?>><?php echo date("d.m.Y",strtotime($einsatz_data->anfang)); ?></h5>
												<div class="card-body">
													<h5 class="card-title <?php echo $uebung_color; ?>"><?php echo $uebung.$beendet; ?></h5>
														<p class="card-text"><?php echo $einsatz_data->einsatz; ?></p>
													<ul class="list-group">
														<li  class="list-group-item bg-success color-white font-weight-bold">Primärorganisation:</li>
														<li class="list-group-item"><?php echo $oids[$OID]; ?></li>
														<?php
														$i = 0;
														if(count($oids_t) > 1){
															foreach($oids_t as $oid_tc){
																echo $i == 0 ? '<li class="list-group-item bg-info color-white font-weight-bold">Beteiligte Organisationen:</li>' : "";
																echo ($oid_tc != $OID && $oid_tc != "DEV") ? '<li class="list-group-item">'.$oids[$oid_tc].'</li>' : "";
															
																$i++;
																
															}
														}
														?>
													</ul>
													<button class="btn btn-primary float-right mt-4" type="submit"><?php echo ($uebung == "Übung") ? "Zur Übung" : "Zum Einsatz";?></button>
												</div>
											</div>
										</form>
										</div>
										<?php
										//echo "<option value='".$einsaetze['EID']."' data-einsatzname='".$einsatz_data->einsatz."'".$styling.$newE." ".$bg_color.">".$uebung.$einsatz_data->einsatz." [".$einsatz_data->anfang."]</option>\n";
									}
								//}
							}
							$newE = "";
						}
						
					}else{ 
						if(isset($Enameaktuell)){ ?>
							<div class="category card m-4 p-4 suchgebiete">
								<div class="card-header"><h3> Sie sind im Einsatz <?php echo $Enameaktuell; ?> angemeldet.</h3></div>
								<h5 class="card-title mt-4">Angelegte Suchgebiete</h5>
								<div class="card-body">
								</div>
								<h5 class="card-title mt-4">Eigenen Track hochladen</h5>
								<form class="col-12 col-lg-4" id="gpximporter" enctype="multipart/form-data">
									<input name="EID" id="EID" type="hidden" value="<?php echo $EID;?>">
									<input name="gpxsender" id="gpxsender" type="hidden" value="<?php echo $UID_admin;?>">
									<input name="gpxsenderDNR" id="gpxsenderDNR" type="hidden" value="<?php echo $_SESSION["etrax"]["dienstnummer"];?>">
									<input name="gpxsenderOID" id="gpxsenderOID" type="hidden" value="<?php echo $_SESSION["etrax"]["adminOID"];?>">
									<div class="input-group mt-2 mb-2">
										<div class="input-group-prepend">
											<label for="sender" class="input-group-text pl-2 pr-2">Sender</label>
										</div>
										<input type="text" id="gpxsendername" class="form-control p-2" value="<?php echo $_SESSION["etrax"]["name"];?>" id="sender" readonly>
									</div>
									<div class="input-group mt-2 mb-2">
										<div class="input-group-prepend">
											<label for="gpxgruppe" class="input-group-text pl-2 pr-2">Gruppe</label>
										</div>
										<select class="custom-select p-2" id="gpxgruppe"></select>
									</div>
									<div class="input-group mt-2 mb-2">
										<div class="input-group-prepend">
											<label for="gpxfile" class="custom-file-label">GPX Datei</label>
										</div>
										<input name="gpxfile" id="gpxfile" type="file" class="custom-file-input">
										<label for="gpxfile" class="custom-file-label">Track-Datei wählen</label>
									</div>
									<input class="btn btn-primary float-right" name="submit" type="submit" id="submitgpx" value="Importieren">
								</form>
							</div>
						<?php }else{ ?>
						<div class="category card m-4 suchgebiete">
							<div class="card-header"><h3> Sie sind in keinem Einsatz angemeldet.</h3></div>
							<div class="card-body">
							</div>
						</div>

						<?php }} ?>
			
				</div>
				<?php echo $n_einsatz > 5 ? "<div><button class='btn btn-success float-right m-4' id='ShowMoreU' type='button'>Alle Übungen anzeigen</button></div>" : ""; ?>
			</div>
		</div>
		<!-- User Update Overlay Anfang -->
		<div class="modal fade usermodal" tabindex="-1" role="dialog" aria-labelledby="usermodalheader" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="modal-title mr-auto" id="usersmodalheader">User Details</h3>
						<button  class="btn btn-primary user_modify mr-2">Bearbeiten</button>
						<button class="btn btn-success user_modify_save abschliessen mr-2" tabindex="15" data-uid="">Speichern</button>
						<button type="button" class="btn btn-secondary schliessen" data-dismiss="modal">Schliessen</button>
						<span class="usersync_info" style='color:#D3302F;display:none'>Ihre Userdaten werden importiert und können daher hier nicht bearbeitet werden.</span>
					</div>
					<div class="modal-body">
						<form class="p-4">
							<div class="admin_field form-group row">
								<input type="hidden" class="x_uid" id="uid" value="<?php echo $user_arr["UID"];?>">
								<input type="hidden" class="x_oid" id="oid" value="<?php echo $user_arr["UID"];?>">
								<input type="hidden" class="x_pwd_old" value="<?php echo $user_arr["password"];?>">
								<input type="hidden" class="x_fid" id="fid" value="<?php echo $user_arr["FID"];?>">
								<label for="name" class="col-md-3 col-form-label">Name:</label>
								<div class="col-md-9">
									<input class="form-control-plaintext form-control x_user_edit x_name" readonly type="text" name="name" id="name" placeholder="Vollständiger Name des Users" value="<?php echo $user_arr["name"];?>">
								</div>
							</div>
							<div class="admin_field form-group row">
								<label for="dienstnummer" class="col-md-3 col-form-label">Dienstnummer:</label>
								<div class="col-md-9">
									<input class="form-control-plaintext form-control x_dienstnummer" readonly type="text" name="dienstnummer" id="dienstnummer" placeholder="Dienstnummer" value="<?php echo $user_arr["dienstnummer"];?>">
								</div>
							</div>
							<div class="admin_field form-group row">
								<label for="typ" class="col-md-3 col-form-label">Typ:</label>
								<div class="col-md-9">
									<input class="form-control-plaintext form-control x_typ" readonly type="text" name="typ" id="typ" placeholder="Typ" value="<?php echo $user_arr["typ"];?>">
								</div>
							</div>
							<div class="admin_field form-group row">
								<label for="username" class="col-md-3 col-form-label">Username:</label>
								<div class="col-md-9">
									<input class="form-control-plaintext form-control x_username" readonly type="text" name="username" id="username" placeholder="Username" value="<?php echo $user_arr["username"];?>">
								</div>
							</div>
							<div class="admin_field form-group row">
								<label for="pwd" class="col-md-3 col-form-label">Passwort:</label>
								<div class="col-md-9">
									<input class="form-control x_user_edit x_pwd pwdcheck" type="password" name="pwd" id="pwd" value="<?php echo $user_arr["password"];?>" aria-describedby="pwdHelp">
									<small class="form-text PasswortHelp">Das Passwort muss folgende Kriterien erfüllen:</small>
									<small class="text-danger ml-4 letter">Kleinbuchstaben</small><br>
									<small class="text-danger ml-4 capital">Großbuchstaben</small><br>
									<small class="text-danger ml-4 number">Zahlen</small><br>
									<small class="text-danger ml-4 length">Mindestens 8 Zeichen</small><br>
								</div>
							</div>
							<div class="admin_field form-group row">
								<label for="repwd" class="col-md-3 col-form-label">Passwort Wiederholung:</label>
								<div class="col-md-9">
									<input class="form-control x_user_edit x_repwd repwdcheck" type="password" name="repwd" id="repwd" placeholder="Feld leer lassen für keine Änderung" value="" aria-describedby="pwdHelp">
									<small class="text-danger ml-4 match">Die Passwörter müssen übereinstimmen</small><br>
								</div>
							</div>
							<div class="admin_field form-group row">
								<label for="email" class="col-md-3 col-form-label">E-mailadresse:</label>
								<div class="col-md-9">
									<input class="form-control x_user_edit x_email" type="email" name="email" id="email" placeholder="E-Mailadresse" value="<?php echo$user_arr["email"];?>">
								</div>
							</div>
							<div class="admin_field form-group row">	
								<label for="einsatzfaehig" class="col-md-3 col-form-label">Einsatzfähig</label>
								<div class="col-md-9">
									<select disabled name="einsatzfaehig" id="einsatzfaehig" size="1" class="form-control x_einsatzfaehig">
										<option value="1">Ja</option>
										<option value="0">Nein</option>
									</select>
								</div>
							</div>
							<div class="admin_field form-group row">
								<label for="bos" class="col-md-3 col-form-label">BOS Kennung:</label>
								<div class="col-md-9">
									<input class="form-control x_user_edit x_bos" type="text" name="bos" id="bos" placeholder="BOS" value="<?php echo $user_arr["bos"];?>">
								</div>
							</div>
							<div class="admin_field form-group row">
								<label for="telefon" class="col-md-3 col-form-label">Telefonnummer:</label>
								<div class="col-md-9 input-group">
									<input class="form-control x_user_edit x_telefon" type="text" name="telefon" id="telefon" placeholder="Telefon" value="<?php echo $user_arr["telefon"];?>">
								</div>
							</div>
							<div class="admin_field form-group row">
								<label for="notfallkontakt" class="col-md-3 col-form-label">Notfallkontakt:</label>
								<div class="col-md-9">
									<input class="form-control x_user_edit x_notfallkontakt" notfallkontakte="text" name="notfallkontakt" id="notfallkontakt" placeholder="Notfallkontakt" value="<?php echo $user_arr["notfallkontakt"];?>">
								</div>
							</div>
							<div class="admin_field form-group row">
								<label for="notfallinfo" class="col-md-3 col-form-label">Notfallinfo:</label>
								<div class="col-md-9">
									<input class="form-control x_user_edit x_notfallinfo" type="text" name="notfallinfo" id="notfallinfo" placeholder="Notfallinfo" value="<?php echo $user_arr["notfallinfo"];?>">
								</div>
							</div>
							<div class="admin_field form-group row">
								<label for="kommentar" class="col-md-3 col-form-label">Kommentar für Einsatzleiter:</label>
								<div class="col-md-9">
									<textarea class="form-control-plaintext x_kommentar" readonly type="text" name="kommentar" id="kommentar" placeholder="kommentar"><?php echo $user_arr["kommentar"];?></textarea>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<!-- Overlay Datenschutzinfo -->
		<div class="modal fade dsmodal" tabindex="-1" id="dsmodal" role="dialog" aria-labelledby="dsmodalheader" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="modal-title" id="dsmodalheader" style="font-weight:bold;">Informationen zum Datenschutz</h3>
					</div>
					<div class="modal-body">
						<?php
							echo isset($text["datenschutz_user"]) ? $text["datenschutz_user"] : "";
						?>
						<div class="m-4 p-4 bg-info rounded ">
							Verantwortlich für den Datenschutz ihrer Organisation ist: <?php echo $datenschutzbeauftragter; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Overlay Datenschutzinfo Ende -->
		<div class="modal fade feedback" tabindex="-1" role="dialog" aria-labelledby="messanger" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="messanger"></h5>
						<div class="modal-body">
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Schliessen</button>
					</div>
				</div>
			</div>
		</div>
		<?php if(isset($API_KEYS["google_api_places"]) && $API_KEYS["google_api_places"] != ''){ ?>
			<script src="//maps.googleapis.com/maps/api/js?key=<?php echo $API_KEYS["google_api_places"]; ?>&libraries=places" async defer></script>
		<?php } ?>
		<script>
			window.time = "<?php echo date('Y-m-d H:i:s');?>";
			window.OID = "<?php echo $_SESSION['etrax']['OID'];?>";
			window.md5time = "<?php echo md5(time()); ?>";
		</script>
		<script>
		$(function() {
			$("body").on("click","#ShowMoreE",function(){
				$(".ShowMoreE").show();
				$('.category .grid').masonry({
					columnWidth: '.einsatz-card',
					itemSelector: '.einsatz-card',
					percentPosition: true
				})
			});
			$("body").on("click","#ShowMoreU",function(){
				$(".ShowMoreU").show();
				$('.category .grid').masonry({
					columnWidth: '.einsatz-card',
					itemSelector: '.einsatz-card',
					percentPosition: true
				})
			});
		});
		</script>
		<script src="js/einsatzwahl.js"></script>
		<script src="vendor/js/masonry.min.js"></script>
		<!-- User Update Overlay Ende-->
	</body>
</html>

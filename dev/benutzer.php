<?php
session_start();
//($_SESSION["etrax"]["usertype"] ? '' : header('Location: index.php'));
//Wenn nicht zumindest Leserechte vorhanden sind, wird auf die Einsatzwahl Seite zurückgeleitet
if(!$_SESSION["etrax"]["USER"]["lesen"]){header('Location: einsatzwahl.php');exit;}

require "../../secure/info.inc.php";
require "../../secure/secret.php";
require "include/verschluesseln.php";
if (isset($_GET["id"])) {
	$id = htmlspecialchars($_GET["id"]);
} else {
	$id = "";
}

$EID = $_SESSION["etrax"]["EID"];
$myOID = $_SESSION["etrax"]["OID"];
$userlevel = $_SESSION["etrax"]["userlevel"];
$userrechte = $_SESSION["etrax"]["userrechte"];

$einsatz_query = $db->prepare("SELECT data FROM settings WHERE EID = ? ");
$einsatz_query->bindParam(1, $EID, PDO::PARAM_STR);
$einsatz_query->execute() or die(print_r($einsatz_query->errorInfo()));
$einsatz = $einsatz_query->fetch(PDO::FETCH_ASSOC);
$einsatz_data_json = json_decode(substr(string_decrypt($einsatz['data']), 1, -1));
$trackstart = strtotime($einsatz_data_json->anfang);
$OID = $einsatz_data_json->OID;
$Ogleich = $einsatz_data_json->Ogleich;
$Ozeichnen = $einsatz_data_json->Ozeichnen;
$Ozuweisen = $einsatz_data_json->Ozuweisen;
$Osehen = $einsatz_data_json->Osehen;

//Query das OID auf am Einsatz beteiligte OIDs beschränkt für ein MySQL Statement (OID = 'DEV' OR OID = 'XY')
$oids_t = explode(",", $OID . "," . $Ogleich . "," . $Ozeichnen . "," . $Ozuweisen . "," . $Osehen);
$oids_t = array_unique(array_filter($oids_t));
$OID_q = "OID = 'DEV' OR ";
$i = 0;
foreach ($oids_t as $oid_t) {
	if ($i == 0) {
		$OID_q = $OID_q . "OID = '" . $oid_t . "'";
	} else {
		$OID_q = $OID_q . " OR OID = '" . $oid_t . "'";
	}
	$i++;
}
$OID_q = "(" . $OID_q . ")";


//Infos zur Organisation (wird auch für Berechtigung genützt)
$oidname_query = $db->prepare("SELECT OID,data,orgfreigabe,funktionen FROM organisation");
$oidname_query->execute() or die(print_r($oidname_query->errorInfo()));
//Array mit den Organisationsnamen erstellen
$oidname = [];
$oid_list = [];

while ($roworg = $oidname_query->fetch(PDO::FETCH_ASSOC)) {
	$data_org_json = json_decode(substr(string_decrypt($roworg['data']), 1, -1));
	$oidname[$roworg["OID"]] = array('kurzname' => $data_org_json->kurzname, 'OID' => $roworg["OID"], 'orgfreigabe' => $roworg["orgfreigabe"]);
	array_push($oid_list, $roworg["OID"]);
	$function_list[$roworg["OID"]] = json_decode($roworg["funktionen"], true);
}

include("include/header.html");
?>
<script src="vendor/js/jquery-3.5.1.min.js"></script>
<script src="vendor/js/bootstrap.bundle.min.js"></script>
</head>

<body id="benutzer" class="background">
	<div class="modal fade feedback" tabindex="-1" role="dialog" aria-labelledby="messanger" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="messanger"></h5>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Schliessen</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade changeuser" tabindex="-1" role="dialog" aria-labelledby="changeuserheader" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title mr-auto" id="changeuser"></h5>
						<button type="button" class="btn btn-primary mr-2">Änderungen speicher</button>
						<button type="button" class="btn btn-secondary" data-dismiss="modal">ohne Speichern schliessen</button>
				</div>
				<form id="changeUser" class="d-flex">
					<div class="modal-body">
						<div class="col-12">
							<?php
							foreach ($oid_list as $oid) {
								if($oid != 'DEV'){
									$f_options = '';
									if (count($function_list[$oid]) > 0) {
										foreach ($function_list[$oid] as $function) {
											$f_options .= '<option value="' . $function["kurz"] . '">' . $function["lang"] . '</option>';
										}
									}
									echo '<span class="org-' . $oid . '">';
									echo ($f_options != '<option value="">Funktion wählen</option>') ? 'Im Einsatz als: <select name="typ" class="usertyp">' . $f_options . '</select></span>' : 'Keine Funktionen definiert';
									echo '</span>';
								}
							}
							?>
						</div>
					</div>
					<div class="modal-footer">
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="modal fade popuptext" tabindex="-1" role="dialog" aria-labelledby="popuptextheader" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="popuptextheader"></h5>
				</div>
				<div class="modal-body text-center"></div>
				<div class="modal-footer">
					<button type="button" id="externsubmit" class="btn btn-success aktivieren">aktivieren</button>
					<button type="button" class="btn btn-secondary deaktivieren" data-dismiss="modal">nicht aktivieren</button>
				</div>
			</div>
		</div>
	</div>
	<div class="col-12 mb-4 pb-4">
		<div class="userlist row mb-4 pb-4">
			<div id="memberlist" class="col-12 col-lg-4 p-2">
				<div class="bg-lightgray p-2">
					<h4 class="btn btn-secondary w-100">User und Material</h4>
					<input class="btn btn-outline-primary bg-white color-black sorterinput listfilter mr-2 col-8" placeholder="filtern nach Name, DNR, BOS" data-target="#memberlist #members ul li" id="usersort" type="text"><i title="Liste neu laden" class="sync material-icons">backspace</i>
					<div class="clearfix"></div>
					<ul id="members" class="list-group">

						<?php
						if ($trackstart != 0) {
							$einsatzbeginn = $trackstart;
						}

						//Trackinguser finden
						$trackerNum = [];
						function trackingUser()
						{
							global $db, $trackerNum;
							$query_tracker = $db->prepare("SELECT UID FROM tracking WHERE EID LIKE ? GROUP BY UID");
							$query_tracker->bindParam(1, $_SESSION["etrax"]["EID"], PDO::PARAM_STR);
							$query_tracker->execute();
							
							while ($result = $query_tracker->fetch(PDO::FETCH_ASSOC)) {
								array_push($trackerNum, $result['UID']);
							}
						}
						trackingUser();

						$UserimEinsatz = [];
						function MitgliederimEinsatz()
						{
							global $db, $EID, $UserimEinsatz;
							$sql_query = $db->prepare("SELECT personen_im_einsatz FROM settings WHERE EID = ? ");
							$sql_query->bindParam(1, $EID, PDO::PARAM_STR);
							$sql_query->execute();
							//ErrorInfo
							$errorInfo = $sql_query->errorInfo();
							echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
							
							while ($sql_json = $sql_query->fetch(PDO::FETCH_ASSOC)) {
								if ($sql_json['personen_im_einsatz'] != null) {
									$json = string_decrypt($sql_json['personen_im_einsatz']);
									$UserimEinsatz = json_decode($json, true);
								}
							}
							return $UserimEinsatz;
						}
						MitgliederimEinsatz();

						$tooltip_text = $EIDaktuell = $EID;
						$s = [];
						$gone = [];
						// User im Einsatz und Pause holen
						$HFcount = $IEcount = 0;
						$User_in_Pause = $User_im_Einsatz = $Material_im_Einsatz = $User_im_Einsatz_UID = [];
						if (is_array($UserimEinsatz) || is_object($UserimEinsatz)) {
							foreach ($UserimEinsatz as $member) {
								foreach ($member['data'] as $memberdata) {
									$s[$memberdata['UID']] = $memberdata['status'];
									$gone[$memberdata['UID']] = $memberdata['abgemeldet'];
									foreach ($memberdata as $u_key => $u_value) {
										${"m_" . $u_key} = $u_value;
									}
									if (in_array($m_UID, $trackerNum)) {
										$ist_sender = "inactive";
										$sendet = " sendet ";
										$tracker_icon = "<i class='material-icons text-info' data-toggle='tooltip' data-placement='top' title='schickt Trackingdaten'>location_on</i>";
										$sendet_txt = ' ist als Tracker aktiv' . $m_bos;
									} else {
										$sendet = $sendet_txt = $ist_sender = $tracker_icon = "";
									}
									if ($m_abgemeldet != "") {
										$status = "useroutoforder";
										$buttontype = "btn-outline-danger";
										$arrowleft = $_SESSION["etrax"]["USER"]["zuweisen"] ? "<i class='moveleft material-icons'>arrow_back</i>" : "";
										$arrowright = "";
									} else {
										$status = "currentuser";
										$buttontype = "btn-outline-secondary";
										$arrowleft = $_SESSION["etrax"]["USER"]["zuweisen"] ? "<i class='moveleft material-icons'>arrow_back</i>" : "";
										//Verschieben nur für User mit mehr als Leserechten möglich
										$arrowright = $_SESSION["etrax"]["USER"]["zuweisen"] ? "<i class='moveright material-icons ml-auto'>arrow_forward</i>" : "";
									}
									if (!$m_abgemeldet) {
										array_push($User_im_Einsatz_UID, $m_UID);
									}
									$ausbildungen = isset($m_ausbildungen) ? $m_ausbildungen : '';
									if ($m_OID) {
										$org = $m_OID;
									} else {
										$org = "";
									}
									if (!$m_abgemeldet && $m_status != 9) {
										if($m_typ == "Material"){
											$org = $m_typ;
											array_push($Material_im_Einsatz, $m_UID);
										}
										if ($m_status >= 3 && $m_status != 10 && $m_status != 11) {
											$bos = ($m_bos) ? "<br>BOS: " . $m_bos : '';
											$phone = ($m_phone) ? "<br>TEL: " . $m_phone : '';
											$tooltip_text = $oidname[$m_OID]['kurzname'] . "<br>" . $m_typ . " " . $m_dienstnummer . " " . $sendet_txt . $bos . $phone;
											$m_typ == "HF" ? $HFcount += 1 : '';
											$IEcount += 1;
											$ist_im_Einsatz = "<li data-toggle='tooltip' data-html='true' data-placement='right' title='" . $tooltip_text . "' class='btn " . $buttontype . " list-group-item mt-2 w-100 d-flex' data-uid='" . $m_UID . "' data-oid='" . $org . "' data-typ='" . $m_typ . "' data-dienstnummer='" . $m_dienstnummer . "' data-ausbildungen='" . $ausbildungen . "' data-name='" . $m_name . "' data-bos='" . $m_bos . "' data-phone='" . $m_phone . "' data-pause='" . $m_pause . "' data-sendet='" . $ist_sender . "' data-oldEID='" . $EID . "' data-externorganisation='" . $EID . "'" . $sendet_txt . ">" . $arrowleft . "<span class='user'><span class='sr-only'>" . $oidname[$m_OID]['kurzname'] . " " . $m_dienstnummer . " " . $m_bos . "</span>" . $m_name . "<span class='badge badge-info ml-1'>" . $m_typ . "</span></span><span class='d-inline-block typ ml-2'>" . $tracker_icon . "</span>" . $arrowright . "</li>\n";
											array_push($User_im_Einsatz, $ist_im_Einsatz);
										}
									} elseif ($m_status == 9) {
										$ist_in_Pause = "<li data-toggle='tooltip' data-html='true' data-placement='left' title='" . $tooltip_text . "' class='btn " . $buttontype . " list-group-item mt-2 w-100 d-flex" . $sendet . "' data-uid='" . $m_UID . "' data-oid='" . $org . "' data-typ='" . $m_typ . "' data-dienstnummer='" . $m_dienstnummer . "' data-ausbildungen='" . $ausbildungen . "' data-name='" . $m_name . "' data-bos='" . $m_bos . "' data-phone='" . $m_phone . "' data-pause='" . $m_pause . "' data-sendet='" . $ist_sender . "' data-oldEID='" . $EID . "' data-externorganisation='" . $EID . "'" . $sendet_txt . ">" . $arrowleft . "<span class='user'>" . $m_name . "<span class='badge badge-info ml-1'>" . $m_typ . "</span></span></li>\n";
										array_push($User_in_Pause, $ist_in_Pause);
									}
								}
							}
						}

						// User aus DB holen neu PT 2020-05-01
						$db_mitglieder = $db->prepare("SELECT UID,OID,FID,EID,aktiveEID,data,lastupdate FROM user WHERE ".$OID_q." ORDER BY OID ASC");
						$db_mitglieder->execute();
						//ErrorInfo
						$errorInfo = $db_mitglieder->errorInfo();
						echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
						
						$user_arr = array();
						//print_r($db_mitglieder);
						$n_user = 0;
						$letter1 = "";

						function j_decode($json)
						{
							//$json = substr($json, 1, -1);
							$json_decoded = json_decode($json);
							return $json_decoded[0];
						}

						while ($res_mg = $db_mitglieder->fetch(PDO::FETCH_ASSOC)) {
							$data_user_json = j_decode(string_decrypt($res_mg['data']));
							if (isset($data_user_json->einsatzfaehig) && $data_user_json->einsatzfaehig == "1") {	//um zu verhinden, dass nicht einsatzfähige angezeigt werden
								$user_arr[] = array(
									'UID' => $res_mg['UID'],
									'OID'   => $res_mg['OID'],
									'FID'   => $res_mg['FID'],
									'aktiveEID'   => $res_mg['aktiveEID'],
									'name'   => $data_user_json->name,
									'dienstnummer'   => isset($data_user_json->dienstnummer) ? $data_user_json->dienstnummer : "",
									'typ'   => isset($data_user_json->typ) ? $data_user_json->typ : "",
									'pause'   => isset($data_user_json->pause) ? $data_user_json->pause / 60 : 0,
									'username'   => isset($data_user_json->username) ? $data_user_json->username : "",
									'ausbildungen'   => isset($data_user_json->ausbildungen) ? $data_user_json->ausbildungen : "",
									'email'   => isset($data_user_json->email) ? $data_user_json->email : "",
									'bos'   => isset($data_user_json->bos) ? $data_user_json->bos : "",
									'telefon'   => isset($data_user_json->telefon) ? $data_user_json->telefon : "",
									'einsatzfaehig'   => isset($data_user_json->einsatzfaehig) ? $data_user_json->einsatzfaehig : "",
									'notfallkontakt'   => isset($data_user_json->notfallkontakt) ? $data_user_json->notfallkontakt : "",
									'notfallinfo'   => isset($data_user_json->notfallinfo) ? $data_user_json->notfallinfo : "",
									'kommentar'   => isset($data_user_json->kommentar) ? $data_user_json->kommentar : "",
									'lastupdate'   => $res_mg['lastupdate']
								);
								$n_user++;
							}
						}
						//Sortieren vorbereiten
						$name = array();
						foreach ($user_arr as $nr => $inhalt) {
							$name[$nr]  = strtolower($inhalt['name']);
						}
						//Sortieren
						//array_multisort($name, SORT_ASC, $user_arr);
						$oid_loop = "";
						$org_open = $other_org = false;
						$loop_index = 0;
						$statusicon = '';
						foreach ($user_arr as $nr => $result) {
							$unserinfo = $sendet = $p_EID = $imeinsatz = $imeinsatzclass = "";
							$buttonclass = "btn-outline-secondary";
							//Prüfen ob Organisation für die Einsatzführende Organisation die Freigabe erteilt hat
							$jtadmin = json_decode($oidname[$result['OID']]["orgfreigabe"], true); //Organisationsfreigaben der abgerufenen Organisation anzeigen
							if (array_key_exists($OID, $jtadmin) || $result['OID'] == $OID) {
								//Anzeige aller organisationen nur, wenn die Berechtigung user auf "Alle Rechte" bzw. Organisation auf gleich gestellt ist
								if($_SESSION["etrax"]["USER"]["gleich"] || $result['OID'] == $_SESSION["etrax"]["OID"]){
									$show_user = $other_org = TRUE;
								} else {
									$show_user = FALSE;
									$other_org = TRUE;
								}
							} else {
								$show_user = $other_org = FALSE;
							}
							if ($result['OID'] != "DEV") { // User von eTrax Developmentteam werden nicht angezeigt
								if ($oid_loop != $result["OID"]) {
									$oid_loop = $result["OID"];
									$org_open = ($loop_index == 0) ? false : true;
									if ($org_open) {
										echo "</ul>";
									}
									$show = ($result['OID'] == $myOID) ? 'show' : '';
									$expande = ($result['OID'] == $myOID) ? 'true' : 'false';
									echo "
									<li class='btn btn-link font-weight-bold color-white bg-red mt-3' data-toggle='collapse' data-target='#collapse" . $oid_loop . "' aria-expanded=" . $expande . " aria-controls='collapse" . $oid_loop . "'>" . $oidname[$result['OID']]['kurzname'] . "</li><ul id='collapse" . $oid_loop . "' class='pl-0 collapse " . $show . "' aria-labelledby='headingOne" . $oid_loop . "'>";
									if (!$show_user) {
										echo $other_org ? "<li class='font-weight-bold color-red mt-3 p-1'>Ihre Rechte erlauben keine Anzeige Mitglieder anderer Organisationen</li>" : "<li class='font-weight-bold color-red mt-3 p-1'>Diese Organisation hat keine Freigabe für die Zusammenarbeit erteilt</li>";
									}
								}
								$loop_index++;

								if ($show_user) { //Nur bei wechselseitiger Freigabe werden User angezeigt
									//in_array($result["UID"], $activetracker) ? $sendet = " sendet" : $sendet = "";
									if (in_array($result["UID"], $trackerNum)) {
										$ist_sender = "active";
										$sendet = " sendet";
										$tracker_icon = '<i class="material-icons text-info" data-toggle="tooltip" data-placement="top" title="schickt Trackingdaten">location_on</i>';
									} else {
										$sendet = $sendet_txt = $ist_sender = $tracker_icon = "";
									}
									if ($result["kommentar"] != "") {
										$unserinfo = "<i class='kommentar material-icons text-info' data-toggle='tooltip' data-placement='top' title='" . $result["kommentar"] . "'>info_outline</i> ";
									}

									if ($EIDaktuell != $result["aktiveEID"] && ($result["aktiveEID"] != NULL && $result["aktiveEID"] != "")) {
										$buttonclass = "btn-outline-danger";
										$imeinsatz = ' ist noch in einem anderen Einsatz aktiv!<br>Bei Zuweisung wird ' . $result["typ"] . ' ' . $result["name"] . ' dort außer Dienst genommen und hier aktiviert!';
									} else {
										$imeinsatz = '';
										$buttonclass = "btn-outline-secondary";
									}

									//echo (isset($s[''.$result["UID"].'']) ? $s[''.$result["UID"].''] : '');
									if (!isset($s['' . $result["UID"] . '']) || $s['' . $result["UID"] . ''] < 3 || $s['' . $result["UID"] . ''] >= 10) {
										if (!isset($s['' . $result["UID"] . ''])  || ($s['' . $result["UID"] . ''] > 2 && $s['' . $result["UID"] . ''] != 10)) {
											$statusicon = '';
										} else if ($s['' . $result["UID"] . ''] == 1) {
											$statusicon = '<i class="material-icons" data-toggle="tooltip" data-placement="top" title="" data-original-title="angemeldet">check_box</i>';
										} else if ($s['' . $result["UID"] . ''] == 2) {
											$statusicon = '<i class="material-icons text-info" data-toggle="tooltip" data-placement="top" title="" data-original-title="In Anfahrt">directions_car</i>';
										} else if ($s['' . $result["UID"] . ''] == 10 && $gone['' . $result["UID"] . ''] == '') {
											$statusicon = '<i class="material-icons text-info" data-toggle="tooltip" data-placement="top" title="" data-original-title="Am Heimweg">directions_car</i>';
										}
										$m_status = (isset($s[''.$result["UID"].'']) ? $s[''.$result["UID"].''] : '');
										echo "<li  data-toggle='tooltip' data-placement='right' title='" . $result['typ'] . " " . $result['dienstnummer'] . $imeinsatz . " <br> ' data-html='true' class='btn " . $buttonclass . " list-group-item w-100 mt-2 mitglied d-flex" . $imeinsatzclass . "' data-dienstnummer='" . $result["dienstnummer"] . "' data-uid='" . $result["UID"] . "' data-oid='" . $result["OID"] . "' data-orgname= '" . $oidname[$result['OID']]['kurzname'] . "' data-typ='" . $result["typ"] . "'  data-name='" . $result["name"] . "' data-ausbildungen='" . $result["ausbildungen"] . "' data-bos='" . $result["bos"] . "' data-phone='" . $result["telefon"] . "' data-email='" . $result["email"] . "' data-pause='" . $result["pause"] . "'  data-sendet='" . $ist_sender . "'  data-oldEID='" . $p_EID . "'><i title='Mitglied editieren' class='edituser material-icons'>mode_edit</i><span class='d-inline-block user'><span class='sr-only'>" . $result["dienstnummer"] . " " . $result["bos"] . "</span>" . $result["name"] . "<span class='badge badge-info typ ml-1 mr-1'>" . $result["typ"] . "</span>" . $statusicon . $unserinfo . "</span><span class='d-inline-block ml-1'>" . $tracker_icon . "</span>";
										//Mehr als Leserechte erforderlich um in Einsatz nehmen zu können
										echo $_SESSION["etrax"]["USER"]["zuweisen"] ? "<i class='moveright material-icons ml-auto'>arrow_forward</i></li>\n" : "</li>\n";
										
									}
								}
							}
						}
						?>
						</ul>
						<li class="btn btn-link font-weight-bold color-white bg-red mt-3" data-toggle="collapse" data-target="#collapseMaterial" aria-expanded="true" aria-controls="collapseMaterial">Material <?php echo $oidname[$_SESSION["etrax"]["OID"]]['kurzname']; ?></li>
						<ul id="collapseMaterial" class="pl-0 collapse" aria-labelledby="headingOneMaterial">
							<?php
							$sql_ressourcen = $db->prepare("SELECT ID,OID,RID,data,typ,aktiveEID FROM ressourcen WHERE OID = ? ");
							$sql_ressourcen->bindParam(1, $_SESSION["etrax"]["OID"], PDO::PARAM_STR);
							$sql_ressourcen->execute();
							//ErrorInfo
							$errorInfo = $sql_ressourcen->errorInfo();
							echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
							while ($sql_json = $sql_ressourcen->fetch(PDO::FETCH_ASSOC)) {
								$ressource = json_decode(string_decrypt($sql_json['data']), true);
								if(!in_array($sql_json['RID'].'-'.$sql_json['OID'], $Material_im_Einsatz)) {
									if ($ressource[0]['name'] !== ''){
										echo "<li class='btn  btn-outline-secondary list-group-item w-100 mt-2 mitglied d-flex mitglied material' data-dienstnummer='" . $ressource[0]['kennung'] . "' data-uid='" . $sql_json['RID'] . "-" . $sql_json['OID'] . "' data-oid='".$sql_json['OID']."' data-oldEID='" . $sql_json['aktiveEID'] . "' data-typ='Material' data-name='" . $ressource[0]['typ'] . " " . $ressource[0]['name'] . "' data-ausbildungen='' data-bos='' data-phone='' data-email='' data-pause='' data-sendet=''>";
										echo '<span class="d-inline-block user">'.$ressource[0]['typ'] . ' ' . $ressource[0]['name'] . ' ' . $ressource[0]['kennung'].' <span class="badge badge-info typ ml-1 mr-1">Material</span></span><span class="d-inline-block ml-1"></span>';
										echo $_SESSION["etrax"]["USER"]["zuweisen"] ? "<i class='moveright material-icons ml-auto'>arrow_forward</i></li>\n" : "</li>\n";
									}
								}
							}
							?>
						</ul>
					</ul>
				</div>
			</div>
			<div id="imEinsatz" class="groupbox col-12 col-lg-4 p-2">
				<div class="bg-lightgray p-2">
					<h4 class="btn bg-green color-white w-100">Im Einsatz: <?php echo 'HF = <span class="HFcount">'.$HFcount.'</span>, H = <span class="Hcount">'.($IEcount - $HFcount);?></span></h4>
					<input class="btn btn-outline-primary bg-white color-black sorterinput listfilter mr-2 col-8" placeholder="filtern nach Name, DNR, BOS" id="einsatzusersort" data-target="#inEinsatz .members li" type="text"><i title="Löschen" class="sync material-icons">backspace</i>
					<div class="clearfix"></div>
					<ul class="members list-group w-100">
						<?php
						foreach ($User_im_Einsatz as $user) {
							echo $user;
						}
						?>
					</ul>
				</div>
			</div>
			<div id="inPause" class="groupbox col-12 col-lg-4 p-2">
				<div class="bg-lightgray p-2">
					<h4 class="btn bg-red color-white w-100">In Pause</h4>
					<div class="clearfix"></div>
					<ul class="members list-group w-100">
						<?php
						foreach ($User_in_Pause as $user) {
							echo $user;
						}
						?>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<script>
		window.time = "<?php echo date('Y-m-d H:i:s'); ?>";
		window.OID = "<?php echo $_SESSION["etrax"]["OID"]; ?>";
		window.md5time = "<?php echo md5(time()); ?>";
	</script>
	<script src="js/benutzer.js"></script>
</body>

</html>
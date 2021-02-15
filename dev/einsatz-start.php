<?php
session_start();
//($_SESSION["etrax"]["usertype"] ? '' : header('Location: index.php'));
require "../../secure/info.inc.php";
require "../../secure/secret.php";
require "include/verschluesseln.php";
define("sessionstart", false);
//Reihenfolge getauscht 2020-11-02 - EID wird im Sessionhandler benötigt
$EID = isset($_POST["einsatz"]) ? $_POST["einsatz"] : $_SESSION["etrax"]["EID"];
$_SESSION["etrax"]["aktiveEID"] = $EID;
require "include/sessionhandler.php"; //Der Sessionhandler schreibt die Sessionwerte neu
//Wenn nicht zumindest Leserechte vorhanden sind, wird auf die Einsatzwahl Seite zurückgeleitet
if($_SESSION["etrax"]["userrechte"] > 5){header('Location: einsatzwahl.php');exit;}
require "include/include.php"; //Definition des Kartenmaterials
$strokewidth = $_SESSION["etrax"]["strokewidth"];
$beobachter = ($_SESSION["etrax"]["usertype"] == "beobachter") ? true : false;

$UID_admin = $_SESSION["etrax"]["UID"];
$OID_admin = $_SESSION["etrax"]["adminOID"];
$FID = $_SESSION["etrax"]["FID"];
$userrecht = $_SESSION["etrax"]["userrechte"];
$userlevel = $_SESSION["etrax"]["userlevel"];

$einsatz_query = $db->prepare("SELECT EID,data,lastupdate,typ FROM settings WHERE EID = ? ");
$einsatz_query->bindParam(1, $EID, PDO::PARAM_STR);
$einsatz_query->execute();
//ErrorInfo
$errorInfo = $einsatz_query->errorInfo();
echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";

$einsatz = $einsatz_query->fetch(PDO::FETCH_ASSOC);
$einsatztyp = $einsatz['typ'] == "uebung" ? "Übung" : "Einsatz";
$einsatz_data_json = json_decode(substr(string_decrypt($einsatz['data']), 1, -1));
$_SESSION["etrax"]["Einsatzname"] = $einsatzname = isset($einsatz_data_json->einsatz) ? $einsatz_data_json->einsatz : "";
$start = isset($einsatz_data_json->anfang) ? strtotime($einsatz_data_json->anfang) : "";
$ort = isset($einsatz_data_json->einsatz) ? $einsatz_data_json->einsatz : "";
$einsatzende = isset($einsatz_data_json->ende) ? $einsatz_data_json->ende : "";
$restrictedExtent = isset($einsatz_data_json->restrictedExtent) ? $einsatz_data_json->restrictedExtent : "0";
$HFquote = isset($einsatz_data_json->HFquote) ? $einsatz_data_json->HFquote : "2.5";
$trackpause = isset($einsatz_data_json->trackpause) ? $einsatz_data_json->trackpause : "3600";
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

if ($_SESSION["etrax"]["userrechte"] <= 5) { //Mindestens Leserechte werden benötigt um etwas angezeigt zu bekommen.

	//Infos zur Organisation (wird auch für Berechtigung und setzen der Einheiten genützt)
	$oidname_query = $db->prepare("SELECT OID,data,orgfreigabe,einheiten FROM organisation");
	$oidname_query->execute() or die(print_r($oidname_query->errorInfo(), true));
	//Array mit den Organisationsnamen erstellen
	$oidname = array();
	while ($roworg = $oidname_query->fetch(PDO::FETCH_ASSOC)) {
		$data_org_json = json_decode(substr(string_decrypt($roworg['data']), 1, -1));
		$oidname[$roworg["OID"]] = array('kurzname' => $data_org_json->kurzname, 'OID' => $roworg["OID"], 'orgfreigabe' => $roworg["orgfreigabe"]);
		//Setzen der Einheiten für die Anzeige
		if ($roworg["OID"] == $_SESSION["etrax"]["OID"]) { //OID des Users
			$einheiten_t = json_decode($roworg['einheiten'], true);
			$_SESSION["etrax"]["aunit"] = isset($einheiten_t["aunit"]) ? $einheiten_t["aunit"] : "ha";
			$_SESSION["etrax"]["afactor"] = isset($einheiten_t["afactor"]) ? $einheiten_t["afactor"] : "10000";
			$_SESSION["etrax"]["lunit"] = isset($einheiten_t["lunit"]) ? $einheiten_t["lunit"] : "m";
			$_SESSION["etrax"]["lfactor"] = isset($einheiten_t["lfactor"]) ? $einheiten_t["lfactor"] : "1";
		}
	}
	$ende = ($einsatzende == "") ? 0 : $einsatzende;

	include("include/header.html");

?>
	<script src="vendor/js/jquery-3.5.1.min.js"></script>
	<script src="vendor/js/bootstrap.bundle.min.js"></script>
	<script>
		$(function() {
			//Einsatzprotokoll - alle wählen
			$(".eb_select_all").click(function() {
				$(".eb_select_all").hide();
				$(".eb_unselect_all").show();
				$(".eb_all").prop("checked", true);
			});
			//Einsatzprotokoll - alle abwählen
			$(".eb_unselect_all").click(function() {
				$(".eb_select_all").show();
				$(".eb_unselect_all").hide();
				$(".eb_all").prop("checked", false);
			});
			//Einsatzprotokoll - Kurzbericht wählen
			$(".eb_select_kurzbericht").click(function() {
				$(".eb_select_all").show();
				$(".eb_unselect_all").hide();
				$(".eb_all").prop("checked", false);
				$(".eb_kurz").prop("checked", true);
			});
		});
	</script>
	</head>

	<body class="background einsatz-start">
		<?php
		$user_arr = [];
		//Ausgabe
		require('include/edit-navbar.php');
		//Kopfzeile

		//Infozeile für die Entwicklung
		// Userrechte
		$ur = "";
		if ($_SESSION["etrax"]["userrechte"] == 0) {
			$ur .= "DEV|";
		}
		if ($_SESSION["etrax"]["userrechte"] == 1) {
			$ur .= "EL|";
		}
		if ($_SESSION["etrax"]["userrechte"] == 2) {
			$ur .= "=|";
		}
		if ($_SESSION["etrax"]["userrechte"] == 3) {
			$ur .= "ZE|";
		}
		if ($_SESSION["etrax"]["userrechte"] == 4) {
			$ur .= "ZU|";
		}
		if ($_SESSION["etrax"]["userrechte"] == 5) {
			$ur .= "LE|";
		}
		?>
		<div class="modal fade settingsmodal" tabindex="-1" role="dialog" aria-labelledby="settingsmodalheader" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title mr-auto" id="settingsmodalheader"><?php echo $einsatztyp; ?> Einstellungen:</h5>
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Schliessen</button>
					</div>
					<div class="modal-body">
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade loescheeinsatz" tabindex="-1" role="dialog" aria-labelledby="einsatzloeschenheader" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="einsatzloeschenheader"><?php echo $einsatztyp; ?>:<?php echo $ort; ?> löschen:</h5>
					</div>
					<div class="modal-body text-center">
						<button type="button" class="btn btn-success" id="einsatzloeschen">Ja</button>
						<button type="button" class="btn btn-danger abbruch" data-dismiss="modal">nein</button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade einsatzende" tabindex="-1" role="dialog" aria-labelledby="einsatzendeheader" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="einsatzendeheader"><?php echo $einsatztyp; ?> beenden:</h5>
					</div>
					<div class="modal-body text-center">
						<?php if ($einsatztyp == "Übung") { ?>
							<p>Durch klick auf Ja wird die Übung beendet und alle Ressourcen aus der Übung genommen.<br><strong>Eine Bearbeitung ist anschließend nicht mehr möglich!</strong></p>
						<?php } else { ?>
							<p>Durch klick auf Ja wird der Einsatz beendet und alle Ressourcen aus dem Einsatz genommen.<br><strong>Eine Bearbeitung ist anschließend nicht mehr möglich!</strong></p>
						<?php } ?>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-success" id="einsatzende">Ja</button>
						<button type="button" class="btn btn-danger abbruch" data-dismiss="modal">nein</button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal funkprotokollmodal" tabindex="-1" role="dialog" id="funkprotokoll" aria-labelledby="funkprotokollheader" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title mr-auto" id="funkprotokollheader">Funkprotokoll:</h5>
						<a href="pdf/einsatzberichtpdf.php?eb_funk=1" target="_blank" class="btn btn-primary" data-toggle="tooltip" data-placement="right" data-original-title="Funkprotokoll drucken" title="Funkprotokoll drucken"><i class="material-icons color-white">print</i></a>
						<button type="button" class="btn btn-secondary ml-2" data-dismiss="modal">Schliessen</button>
					</div>
					<div class="modal-body">
						<div class="col-12 text-center">
							<div class="servicediv protokoll">
								<form id="funkprotokoll" class="text-left">
								<div class="input-group mb-3">
										<div class="input-group-prepend">
										  	<span class="input-group-text" id="uhrzeit">Zeit</span>
										</div>
										<input type="text" class="form-control" name="ftime" id="ftime" value="" aria-label="Aktuelle Zeit" aria-describedby="uhrzeit">
										<div class="input-group-append">
											<input type="button" class="btn btn-primary" id="changetime" value="Zeit aktualisieren">
										</div>
									</div>
									<div class="input-group mb-3">
										<div class="input-group-prepend">
										  	<span class="input-group-text" id="funkmittel">Funkmittel</span>
										</div>
										<input type="text" class="form-control" name="funkid" id="funkid" value="" aria-label="Funkmittel" aria-describedby="funkmittel">
									</div>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text" id="spruch">Spruch</span>
										</div>
										<textarea class="form-control" name="funkprotokolltext" id="funkprotokolltext" aria-label="spruch"></textarea>
									</div>
									<input class="btn btn-primary protokollieren mt-4 mb-4" type="button" value="protokollieren" id="funktextsubmit">
								</form>
							</div>
							<div id="funkprotokolldiv">
								<div class="input-group mb-3">
									<div class="input-group-prepend">
										<span class="input-group-text w-auto" id="protokollsorter">Protokollierte Funksprüche</span>
									</div>
									<input type="text" class="form-control sortliste" placeholder="filtern nach ..." data-listID="#funkprotokolliert" aria-label="protokollsorter" aria-describedby="protokollsorter">
								</div>
								<ul id="funkprotokolliert"></ul>
							</div>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade protokollmodal" tabindex="-1" role="dialog" id="ereignisprotokoll" aria-labelledby="ereignisprotokollheader" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title mr-auto" id="ereignisprotokollheader">Ereignisprotokoll:</h5>
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Schliessen</button>
					</div>
					<div class="modal-body">
						<div class="servicediv protokoll">
							<form id="protokoll">
								<input type="hidden" name="ptime" id="ptime" value="<?php echo date('Y-m-d H:i:s'); ?>"> 
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">Ereignis</span>
									</div>
									<textarea class="form-control" name="protokolltext" id="protokolltext" aria-label="Ereignistext"></textarea>
								</div>
								<input class="btn btn-primary protokollieren mt-4 mb-4" type="button" value="protokollieren" id="textsubmit">
							</form>
						</div>
						<div id="protokolldiv">
							<div class="input-group mb-3">
								<div class="input-group-prepend">
									<span class="input-group-text w-auto" id="protokollsorter">Protokollierte Ereignisse</span>
								</div>
								<input type="text" class="form-control sortliste" placeholder="filtern nach ..." data-listID="#protokolliert" aria-label="protokollsorter" aria-describedby="protokollsorter">
							</div>
							<ul id="protokolliert"></ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade gesuchtePerson vermisst" tabindex="-1" role="dialog" aria-labelledby="gesuchtePerson" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title mr-auto" id="gesuchtePerson">Angaben zur gesuchten Person:</h5>
						<a href="pdf/einsatzberichtpdf.php?eb_vermisst=1" target="_blank" class="mr-2 btn btn-primary" data-toggle="tooltip" data-placement="right" data-original-title="Datenblatt vermisste Person drucken" title="Datenblatt vermisste Person drucken"><i class="material-icons color-white">print</i></a>
						<a href="pdf/handzettel.php" target="_blank" class="btn btn-primary" data-toggle="tooltip" data-placement="right" data-original-title="Handzettel zur vermissten Person drucken" title="Handzettel zur vermissten Person drucken"><i class="material-icons color-white">description</i></a>
					</div>
					<div class="modal-body">
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade checklisteEinsatz" tabindex="-1" role="dialog" aria-labelledby="checklisteEinsatz" aria-hidden="true" data-keyboard="<?php if ($USER["gleich"]) {echo "false";} ?>" data-backdrop="<?php if ($USER["gleich"]) {echo "static";} ?>">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title mr-auto" id="checklisteEinsatz">Einsatzcheckliste:</h5>
						<a href="pdf/einsatzberichtpdf.php?eb_checkliste=1" target="_blank" class="btn btn-primary" data-toggle="tooltip" data-placement="right" data-original-title="Datenblatt vermisste Person drucken" title="Datenblatt vermisste Person drucken"><i class="material-icons color-white">print</i></a>
						<?php if($USER["gleich"]){ ?>
							<button  class="btn btn-primary checklist-bearbeiten ml-2 pb-2 pt-2" type="button">Bearbeiten</button>
							<button class="btn btn-primary eingabe ml-2 pb-2 pt-2" id="checklistesubmit" tabindex="15" type="button">Speichern</button>
							<button class="btn btn-secondary eingabe abbrechen ml-2 pb-2 pt-2" tabindex="16" type="button">Abbrechen</button>
						<?php } ?>
							<button type="button" class="btn btn-secondary schliessen ml-2 pb-2 pt-2" data-dismiss="modal" type="button">Schliessen</button>
					</div>
					<div class="modal-body">
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade berichtEinsatz" tabindex="-1" role="dialog" aria-labelledby="einsatzberichte" aria-hidden="true" data-keyboard="<?php if ($USER["zuweisen"]) {echo "false";} ?>" data-backdrop="<?php if ($USER["zuweisen"]) {echo "static";} ?>">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title mr-auto" id="einsatzberichte">Einsatzbericht:</h5>
						<a href="pdf/einsatzberichtpdf.php?eb_einsatzbericht=1" target="_blank" class="btn btn-primary" data-toggle="tooltip" data-placement="center" data-original-title="Datenblatt vermisste Person drucken" title="Datenblatt vermisste Person drucken"><i class="material-icons color-white">print</i></a>
						<?php if($USER["zuweisen"]){ ?>
							<button  class="btn btn-primary berichtbearbeiten ml-2 pb-2 pt-2" type="button">Bearbeiten</button>
							<button class="btn btn-primary eingabe ml-2 pb-2 pt-2" id="einsatzberichtsubmit" type="button" data-ebid="eb_<?php echo $_SESSION["etrax"]["adminOID"];?>" tabindex="15">Speichern</button>
							<button class="btn btn-secondary eingabe abbrechen ml-2 pb-2 pt-2" type="button" data-dismiss="modal">Abbrechen</button>
						<?php } ?>
						<button type="button" class="btn btn-secondary schliessen ml-2 pb-2 pt-2" type="button" data-dismiss="modal">Schliessen</button>
					</div>
					<div class="modal-body">
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade anreise" tabindex="-1" role="dialog" aria-labelledby="anreiseheader" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="anreiseheader">In Anreise:</h5>
					</div>
					<div class="modal-body">
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade feedback" tabindex="-1" role="dialog" aria-labelledby="messanger" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title mr-auto" id="messanger"></h5>
						<button type="button" class="btn btn-secondary ml-2" data-dismiss="modal">Schliessen</button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade protokolle" tabindex="-1" role="dialog" id="protokolle" aria-labelledby="protokolleheader" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title mr-auto" id="protokolleheader">Elemente Auswählen:</h5>
						<input class="btn btn-primary" type="button" onclick="getElementById('reportform').submit()" value="Protokoll erstellen" tabindex="4">
						<button type="button" class="btn btn-secondary ml-2" data-dismiss="modal">Schliessen</button>
					</div>
					<div class="modal-body">
						<div class="col-12">
							<button class="btn btn-primary eb_select_all" type="button" style="display: none">Alle wählen</button>
							<button class="btn btn-primary eb_unselect_all" type="button">Keine wählen</button>
							<button class="btn btn-primary eb_select_kurzbericht" type="button">Kurzbericht</button>

							<form action="pdf/einsatzberichtpdf.php" target="_blank" method="post" id="reportform">
								<div class="form-check">
									<input class="form-check-input eb_all eb_kurz" type="checkbox" value="1" id="eb_deckblatt" name="eb_deckblatt" checked>
									<label class="form-check-label" for="eb_deckblatt">
										Deckblatt
									</label>
								</div>
								<div class="form-check">
									<input class="form-check-input eb_all" type="checkbox" value="1" id="eb_inhaltsverzeichnis" name="eb_inhaltsverzeichnis" checked>
									<label class="form-check-label" for="eb_inhaltsverzeichnis">
										Inhaltsverzeichnis
									</label>
								</div>
								<div class="form-check">
									<input class="form-check-input eb_all eb_kurz" type="checkbox" value="1" id="eb_einsatzbericht" name="eb_einsatzbericht" checked>
									<label class="form-check-label" for="eb_einsatzbericht">
										Einsatzbericht
									</label>
								</div>
								<div class="form-check">
									<input class="form-check-input eb_all eb_kurz" type="checkbox" value="1" id="eb_vermisst" name="eb_vermisst" checked>
									<label class="form-check-label" for="eb_vermisst">
										Beschreibung vermisste Person
									</label>
								</div>
								<div class="form-check">
									<input class="form-check-input eb_all eb_kurz" type="checkbox" value="1" id="eb_organisationen" name="eb_organisationen" checked>
									<label class="form-check-label" for="eb_organisationen">
										Beteiligte Organisationen
									</label>
								</div>
								<div class="form-check">
									<input class="form-check-input eb_all" type="checkbox" value="1" id="eb_checkliste" name="eb_checkliste" checked>
									<label class="form-check-label" for="eb_checkliste">
										Checkliste Einsatz
									</label>
								</div>
								<div class="form-check">
									<input class="form-check-input eb_all" type="checkbox" value="1" id="eb_personen" name="eb_personen" checked>
									<label class="form-check-label" for="eb_personen">
										Personen im Einsatz
									</label>
								</div>
								<div class="form-check">
									<input class="form-check-input eb_all" type="checkbox" value="1" id="eb_personen_kurz" name="eb_personen_kurz" checked>
									<label class="form-check-label" for="eb_personen_kurz">
										Personen im Einsatz Übersichtstabelle
									</label>
								</div>
								<div class="form-check">
									<input class="form-check-input eb_all" type="checkbox" value="1" id="eb_gruppen" name="eb_gruppen" checked>
									<label class="form-check-label" for="eb_gruppen">
										Gruppeneinteilung
									</label>
								</div>
								<div class="form-check">
									<input class="form-check-input eb_all" type="checkbox" value="1" id="eb_ereignis" name="eb_ereignis" checked>
									<label class="form-check-label" for="eb_ereignis">
										Ereignisprotokoll
									</label>
								</div>
								<div class="form-check">
									<input class="form-check-input eb_all" type="checkbox" value="1" id="eb_funk" name="eb_funk" checked>
									<label class="form-check-label" for="eb_funk">
										Funkprotokoll
									</label>
								</div>
								<div class="form-check">
									<input class="form-check-input eb_all" type="checkbox" value="1" id="eb_suchgebiet" name="eb_suchgebiet" checked>
									<label class="form-check-label" for="eb_suchgebiet">
										Suchgebiete
									</label>
								</div>
								<label for="kartenmaterial">Kartenmaterial</label>
								<select id="kartenmaterial" name="kartenmaterial" class="setRight custom-select custom-select-lg mb-3">
									<?php
									//Gewählte Karten auflisten
									$db_org = $db->prepare("SELECT maps FROM organisation WHERE OID = ? ");
									$db_org->bindParam(1, $OID_admin, PDO::PARAM_STR);
									$db_org->execute();
									//ErrorInfo
									$errorInfo = $db_org->errorInfo();
									echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
									
									$res_map = $db_org->fetch(PDO::FETCH_ASSOC);
									$maps = $res_map["maps"];
									foreach ($path as $key => $val) {
										if ($val["printable"] && strpos($maps, '"' . $val["name_js"] . '"') > 0) {
											echo '<option value="' . $key . '">' . $val["name"] . '</option>';
										}
									}
									?>

								</select>
								<div class="form-check">
									<input class="form-check-input eb_all eb_kurz" type="checkbox" value="1" id="eb_uebersicht" name="eb_uebersicht" checked>
									<label class="form-check-label" for="eb_uebersicht">
										Übersichtskarten
									</label>
								</div>
							</form>
							<ul class="list-group text-left list-group-flush">
								<?php if ($_SESSION["etrax"]["etraxadmin"] === true && time() < 0) { ?>

									<li class="list-group-item-danger list-group-item list-group-item-action">
										<?php echo $einsatztyp; ?> löschen:
										<a href="#" class="deleteeinsatz" title="<?php echo $einsatztyp; ?> löschen"><i class="material-icons">highlight_off</i></a>
									</li>

								<?php } ?>
							</ul>
							<div class="clearfix"></div>

						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
		<?php
		if ($_SESSION["etrax"]["etraxadmin"] === true) {
		?>
			<div class="modal fade userliste" tabindex="-1" role="dialog" id="protokolle" aria-labelledby="userlisteheader" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="userlisteheader">Neuen temporären Administrator anlegen:</h5>
						</div>
						<div class="modal-body">
							<div class="col-12 text-center">
								<label for="berechtigung">Berechtigung für diesen Einsatz</label>
								<select id="berechtigung" class="setRightAdminTemp custom-select custom-select-lg mb-3">
									<option value="8.1">Einsatzleitung</option>
									<option value="8.2">Alle Rechte</option>
									<option value="8.3">Suchgebiete zeichnen & zuweisen</option>
									<option value="8.4">Suchgebiete zuweisen</option>
									<option value="8.5">Informationen lesen</option>
								</select>
								Personen filtern nach: <input placeholder="Filter eingeben" id="memberbox" type="text">
								<ul id="mitgliederliste" class="text-left">
									<?php
									//Mitglieder aus der DB holen
									$db_mitglieder = $db->prepare("SELECT OID,UID,data,EID FROM user WHERE " . $OID_q . " AND EID NOT LIKE ? AND EID NOT LIKE '0' ORDER BY OID ASC");
									//echo "SELECT data,OID,UID, FROM user WHERE ".$OID_q." AND EID NOT LIKE '".$EID."' ORDER BY OID ASC";
									$db_mitglieder->bindParam(1, $EID, PDO::PARAM_STR);
									$db_mitglieder->execute();
									//ErrorInfo
									$errorInfo = $db_mitglieder->errorInfo();
									echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
									
									$oid_loop = $jtadmin = "";

									while ($result = $db_mitglieder->fetch(PDO::FETCH_ASSOC)) {
										//Prüfen ob Organisation für die Einsatzführende Organisation die Freigabe erteilt hat
										$jtadmin = json_decode($oidname[$result['OID']]["orgfreigabe"], true); //Organisationsfreigaben der abgerufenen Organisation anzeigen
										if (array_key_exists($OID, $jtadmin) || $result['OID'] == $OID) {
											$show_user = TRUE;
										} else {
											$show_user = FALSE;
										}
										if ($oid_loop != $result["OID"]) {
											$oid_loop = $result["OID"];
											echo "<li><b style='color:#9a0007;'>" . $oidname[$result['OID']]['kurzname'] . "</b></li>";
											if (!$show_user) {
												echo "<li>Diese Organisation hat keine Freigabe für die Zusammenarbeit erteilt</li>";
											}
										}
										if ($show_user) { //Nur bei wechselseitiger Freigabe werden User angezeigt
											$data_new_admin = json_decode(substr(string_decrypt($result['data']), 1, -1));
											$username = isset($data_new_admin->username) ? $data_new_admin->username : "";
											$dnr = isset($data_new_admin->dienstnummer) ? $data_new_admin->dienstnummer : "";
											$name = isset($data_new_admin->name) ? $data_new_admin->name : "";
											echo "<li><a href='#' class='chooseadmin' data-name='" . $name . "' data-oid='" . $result['OID'] . "' data-orgname='" . $oidname[$result['OID']]['kurzname'] . "' data-uid='" . $result['UID'] . "' data-eid='" . $result['EID'] . "'>" . $dnr . " " . $name . "</a></li>";
										}
									}

									$db = null;
									?>
								</ul>
								<div class="clearfix"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>
		<div id="angemeldet"></div>
		<div id="mama">
			<div class="servicediv">
				<?php
				if ($_SESSION["etrax"]["adminOID"] == "DEV" || preg_match("/@testmagic/", $_SESSION["etrax"]["name"])) { ?>
					<div class="infofooter position-fixed">
						<div class="btn-group dropdown">
							<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
								Developerinfo
							</button>
							<ul class="dropdown-menu pt-2">
								<li class="dropdown-item small"><?php echo "EID - Session: " . (isset($_SESSION["etrax"]["EID"]) ? $_SESSION["etrax"]["EID"] : 'noch nicht gesetzt'); ?></li>
								<li class="dropdown-item small"><?php echo "OID - Session: " . $_SESSION["etrax"]["OID"]; ?></li>
								<li class="dropdown-item small"><?php echo "Userlevel - Session: " . $_SESSION["etrax"]["userlevel"]; ?></li>
								<li class="dropdown-item small"><?php echo "Userrechte - Session: " . $_SESSION["etrax"]["userrechte"]; ?></li>
								<li class="dropdown-item small"><?php echo "FID - Session: " . $_SESSION["etrax"]["FID"]; ?></li>
								<li class="dropdown-item small"><?php echo "UID - Session: " . $_SESSION["etrax"]["UID"]; ?></li>
								<li class="dropdown-item small"><?php echo "usertype - Session: " . $_SESSION["etrax"]["usertype"]; ?></li>
								<li class="dropdown-item small"><?php echo "adminEID - Session: " . $_SESSION["etrax"]["adminEID"]; ?></li>
								<li class="dropdown-item small"><?php echo "adminOID - Session: " . $_SESSION["etrax"]["adminOID"]; ?></li>
								<li class="dropdown-item small"><?php echo "adminID - Session: " . $_SESSION["etrax"]["adminID"]; ?></li>
								<li class="dropdown-item small"><?php echo "name - Session: " . $_SESSION["etrax"]["name"]; ?></li>
								<li class="dropdown-item small"><?php echo "dienstnummer - Session: " . $_SESSION["etrax"]["dienstnummer"]; ?></li>
								<li class="dropdown-item small"><?php echo 'etraxadmin - Session: ' . ($_SESSION["etrax"]["etraxadmin"] ? 'true' : 'false'); ?></li>
								<li class="dropdown-item small"><?php echo 'mapadmin - Session: ' . ($_SESSION["etrax"]["mapadmin"] ? 'true' : 'false'); ?></li>
								<li class="dropdown-item small"><?php echo 'USER[dev] - Session: ' . ($_SESSION["etrax"]["USER"]["dev"] ? 'true' : 'false'); ?></li>
								<li class="dropdown-item small"><?php echo 'USER[einsatzleitung] - Session: ' . ($_SESSION["etrax"]["USER"]["einsatzleitung"] ? 'true' : 'false'); ?></li>
								<li class="dropdown-item small"><?php echo 'USER[gleich] - Session: ' . ($_SESSION["etrax"]["USER"]["gleich"] ? 'true' : 'false'); ?></li>
								<li class="dropdown-item small"><?php echo 'USER[zeichnen] - Session: ' . ($_SESSION["etrax"]["USER"]["zeichnen"] ? 'true' : 'false'); ?></li>
								<li class="dropdown-item small"><?php echo 'USER[zuweisen] - Session: ' . ($_SESSION["etrax"]["USER"]["zuweisen"] ? 'true' : 'false'); ?></li>
								<li class="dropdown-item small"><?php echo "Flächen Einheit: " . $_SESSION["etrax"]["aunit"]; ?></li>
								<li class="dropdown-item small"><?php echo "Flächen Faktor: " . $_SESSION["etrax"]["afactor"]; ?></li>
								<li class="dropdown-item small"><?php echo "Längen Einheit: " . $_SESSION["etrax"]["lunit"]; ?></li>
								<li class="dropdown-item small"><?php echo "Längen Faktor: " . $_SESSION["etrax"]["lfactor"]; ?></li>
							</ul>
						</div>
						<div class="btn-group dropdown">
							<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
								Data JSON Einsatz
							</button>
							<div class="dropdown-menu p-2">
								<p><small class="text-monospace  text-wrap">
										<?php print_r($einsatz_data_json); ?>
									</small></p>
							</div>
						</div>
					</div>
				<?php }	?>
				<div id="login" class="text-center <?php echo $einsatztyp == "Übung" ? "bg-warning" : ""; ?>">
					<?php
					echo ($_SESSION["etrax"]["USER"]["gleich"] === true) ? "<h2>" . $einsatztyp . ": <span contenteditable='true' class='ename ename_editable'>" . $ort . "</span></h2>" : "<h2>" . $EID . "." . $einsatztyp . ":<br><span class='ename'>" . $ort . "</span></h2>";
					?>
					<div class="d-flex flex-column text-left">
						<div class="d-flex starticons align-middle">
							<a href="mapview.html" class="mr-auto" target="_blank" data-toggle="tooltip" data-placement="left" title="zur Kartenansicht"><i class="material-icons">map</i>Arbeitskarte</a>
							<a href="https://tutorials.etrax.at/Tutorial_Arbeitskarte.mp4" target="_blank" data-toggle="tooltip" data-placement="right" title="Videotutorial ansehen"><i class="material-icons">play_circle_outline</i></a>
						</div>
						<?php
						if ($_SESSION["etrax"]["etraxadmin"] === true || $beobachter) {
							if (!$ende) {
								if ($_SESSION["etrax"]["etraxadmin"] === true) { ?>
									<div class="d-flex starticons benutzer">
										<a href="benutzer.php" class="mr-auto" target="_blank" data-toggle="tooltip" data-placement="left" title="zur Ressourcenverwaltung"><i class="material-icons">group</i>Ressourcenverwaltung</a>
										<a href="https://tutorials.etrax.at/Tutorial_Ressourcenverwaltung.mp4" target="_blank" data-toggle="tooltip" data-placement="right" title="Videotutorial ansehen"><i class="material-icons">play_circle_outline</i></a>
									</div>
									<?php if ($_SESSION["etrax"]["USER"]["zuweisen"]) { ?>
										<div class="d-flex starticons funkprotokoll screen">
											<a href="#" class="mr-auto" data-toggle="tooltip" data-placement="left" title="zum Funkprotokoll"><i class="material-icons">forum</i>Funkprotokoll</a>
											<a href="https://tutorials.etrax.at/Tutorial_Funkprotokoll.mp4" target="_blank" data-toggle="tooltip" data-placement="right" title="Videotutorial ansehen"><i class="material-icons">play_circle_outline</i></a>
										</div>
										<div class="d-flex starticons protokoll screen">
											<a href="#" class="mr-auto" data-toggle="tooltip" data-placement="left" title="zum Protokoll"><i class="material-icons">description</i>Protokoll</a>
										</div>
									<?php } ?>
								<?php } 
								
								if ($_SESSION["etrax"]["etraxadmin"] === true) { ?>
										<div class="d-flex starticons open-gesucht">
											<a href="#" class="mr-auto" data-toggle="tooltip" data-placement="left" title="gesuchte Person anzeigen"><i class="material-icons">face</i>Vermisste Person</a>
											<a href="https://tutorials.etrax.at/Tutorial_Vermisste_Person.mp4" target="_blank" data-toggle="tooltip" data-placement="right" title="Videotutorial ansehen"><i class="material-icons">play_circle_outline</i></a>
										</div>
									<?php if ($_SESSION["etrax"]["USER"]["zuweisen"]) { ?>
										<div class="d-flex starticons settings">
											<a href="#" class="mr-auto" data-toggle="tooltip" data-placement="left" title="eTrax Administration"><i class="material-icons">settings</i><?php echo $einsatztyp; ?> Einstellungen</a>
											<a href="https://tutorials.etrax.at/Tutorial_Einsatzeinstellungen.mp4" target="_blank" data-toggle="tooltip" data-placement="right" title="Videotutorial ansehen"><i class="material-icons">play_circle_outline</i></a>
										</div>
									<?php
									}
									?>
									<div class="starticons anreise screen">
										<a href="mapview.html#anreise" target="_blank" data-toggle="tooltip" data-placement="left" title="Mitglieder in Anreise"><i class="material-icons">directions_car</i>Anreisekarte</a>
									</div>
									<?php
									//if($_SESSION["etrax"]["userrechte"] <= 2){
									if ($USER["gleich"]) {
									?>
										<div class="starticons checkliste">
											<a href="#" data-toggle="tooltip" data-placement="left" title="Einsatzcheckliste öffnen"><i class="material-icons">check_box</i>Einsatzcheckliste</a>
										</div>
									<?php }
								}
							}
							if ($_SESSION["etrax"]["etraxadmin"] === true) {
								if ($USER["zuweisen"]) {

									?>
									<div class="d-flex starticons showbericht">
										<a href="#" class="mr-auto" data-toggle="tooltip" data-placement="left" title="Einsatzbericht schreiben"><i class="material-icons">description</i>Einsatzbericht</a>
										<a href="https://tutorials.etrax.at/Tutorial_Einsatzbericht.mp4" target="_blank" data-toggle="tooltip" data-placement="right" title="Videotutorial ansehen"><i class="material-icons">play_circle_outline</i></a>
									</div>
								<?php }
								if ($USER["lesen"]) {
								?>
									<div class="d-flex starticons showprotokoll screen">
										<a href="#" class="mr-auto" data-toggle="tooltip" data-placement="left" title="Speichern der Protokolle"><i class="material-icons">print</i>Einsatzdokumentation drucken</a>
										<a href="https://tutorials.etrax.at/Tutorial_Einsatzdokumentation_drucken.mp4" target="_blank" data-toggle="tooltip" data-placement="right" title="Videotutorial ansehen"><i class="material-icons">play_circle_outline</i></a>
									</div>
									<?php
								}
								if (!$ende) { //Nach Einsatzende nicht mehr sichtbar
									if ($USER["einsatzleitung"]) { //Nur die einsatzleitende Organisation darf den Einsatz beenden
									?>
										<div id="einsatzendesetzen">
											<div class="starticons screen">
												<a class=" beenden" href="#" data-toggle="tooltip" data-placement="left" title="Einsatz beenden"><i class="material-icons">highlight_off</i>Einsatz beenden</a>
											</div>
										</div>
						<?php }
								} //Nach Einsatzende nicht mehr sichtbar
							}
						} ?>
					</div>
					<div class="clearfix"></div>
					<a class="btn btn-primary mt-4 text-white" href="einsatzwahl.php"><i class="material-icons text-white">arrow_back</i>Zurück zur Einsatzauswahl</a>
					<div class="text-right mt-4"><a href="index.php"><img src="img/etrax-kl.png" alt="eTrax"></a></div>
				</div>
			</div>
		</div>
		<script>
			window.time = "<?php echo date('Y-m-d H:i:s'); ?>";
			window.rpath = "api/";
		</script>
		<script src="js/einsatz-start.js"></script>
	</body>

	</html>
<?php
} //Ende wenn User keine Leserechte hat (d.h. wenn das Cookie manipuliert wurde)
?>
<?php
session_start();
if(!isset($_SESSION["etrax"]["usertype"])){
header("Location: index.php");
}
require "../../../secure/info.inc.php";
require "../../../secure/secret.php";
require "../include/verschluesseln.php";
require "../include/include.php"; //Definition des Kartenmaterials
require "coordinates.php";

$EID = $_SESSION["etrax"]["EID"];
$OID_admin = $_SESSION["etrax"]["adminOID"];
$baseURL = "../";

//Infos zur eigenen Organsiation holen
$db_org = $db->prepare("SELECT OID,data,suchef,suchew,suchem,suchep FROM organisation");
$db_org->execute($db_org->errorInfo());
$org_arr = array();
while ($reso = $db_org->fetch(PDO::FETCH_ASSOC)){
	$data_org_json = json_decode(substr(string_decrypt($reso['data']), 1, -1));
	$org_arr[$reso["OID"]]["OID"] = $reso["OID"];
	$org_arr[$reso["OID"]]["bezeichnung"] = $data_org_json->bezeichnung;
	$org_arr[$reso["OID"]]["kurzname"] = $data_org_json->kurzname;
	$org_arr[$reso["OID"]]["adresse"] = $data_org_json->adresse;
	$org_arr[$reso["OID"]]["flaechensuche"] = $reso["suchef"];
	$org_arr[$reso["OID"]]["wegsuche"] = $reso["suchew"];
	$org_arr[$reso["OID"]]["punktsuche"] = $reso["suchep"];
	$org_arr[$reso["OID"]]["mantrailer"] = $reso["suchem"];
}


require $baseURL."include/sessionhandler.php"; //Der Sessionhandler schreibt die Sessionwerte neu

if($USER["lesen"]){ //Mindestens Leserechte werden benötigt um etwas angezeigt zu bekommen.

//Steuerung per $_GET

//Suchgebiet
$suchgebiet_ID = (isset($_GET["SID"])) ? htmlspecialchars($_GET["SID"]) : $suchgebiet_ID = '';
//Karte
$map = (isset($_GET["map"])) ? htmlspecialchars($_GET["map"]) : 'etraxtopo';

//Funktionen
// Umrechnung der Einheiten
	function runden($num,$unit,$factor){
		$value =(round($num,-2));
		$value = ($value / $factor);
		$newval = ($unit != 'm' && $unit != 'm&sup2;') ? number_format($value, 1, ',', '.') : number_format($value, 0, ',', '.');
		return($newval." ".$unit);
	}


//Einsatzberichte laden
//$sql_einsatzbericht = $db->prepare("SELECT OID,Ogleich,Ozeichnen,Ozuweisen,Osehen,data,gesucht,einsatzbericht,orginfo,anfang,ende,suchtyp FROM settings WHERE EID = ".$EID."");
$sql_einsatzbericht = $db->prepare("SELECT data,gesucht,einsatzbericht,orginfo FROM settings WHERE EID = ".$EID."");
	$sql_einsatzbericht->execute($sql_einsatzbericht->errorInfo());
	while ($sqleinsatzbericht = $sql_einsatzbericht->fetch(PDO::FETCH_ASSOC)){
		$einsatzbericht_json = ($sqleinsatzbericht['einsatzbericht'] != '') ? json_decode(substr(string_decrypt($sqleinsatzbericht['einsatzbericht']), 1, -1)) : '';
		$gesucht_json = ($sqleinsatzbericht['gesucht'] != '') ? json_decode(substr(string_decrypt($sqleinsatzbericht['gesucht']), 1, -1)) : '';
		$settings_data_json = json_decode(substr(string_decrypt($sqleinsatzbericht['data']), 1, -1));
		$einsatz_anfang = isset($settings_data_json->anfang) ? $settings_data_json->anfang : "";
		$einsatz_ende = isset($settings_data_json->ende) ? $settings_data_json->ende : "";
		
		if(isset($gesucht_json->suchtyp)){
			$searchtyp = $db->prepare("SELECT name FROM suchprofile WHERE cid = '".$gesucht_json->suchtyp."'");
			$searchtyp->execute($searchtyp->errorInfo());
			$suchtyp = $searchtyp->fetch(PDO::FETCH_ASSOC);
			$suchtyp = $suchtyp["name"];
			$einsatz_suchtyp = $gesucht_json->suchtyp;
		} else {
			$suchtyp = $einsatz_suchtyp = "";
		}
		
		$OIDprim = isset($settings_data_json->OID) ? $settings_data_json->OID : "";
		$OID = isset($settings_data_json->OID) ? $settings_data_json->OID : "";
		$Ogleich = isset($settings_data_json->Ogleich) ? $settings_data_json->Ogleich : "";
		$Ozeichnen = isset($settings_data_json->Ozeichnen) ? $settings_data_json->Ozeichnen : "";
		$Ozuweisen = isset($settings_data_json->Ozuweisen) ? $settings_data_json->Ozuweisen : "";
		$Osehen = isset($settings_data_json->Osehen) ? $settings_data_json->Osehen : "";
		$orginfo_json = json_decode(substr(string_decrypt($sqleinsatzbericht['orginfo']), 1, -1));
	}
	if(!empty($einsatzbericht_json)){
		foreach($einsatzbericht_json as $pkey => $pvalue) {
			if($pkey == "eb_prim"){
				$eb_prim = $pvalue;
			}
			if($pkey == "eb_".$_SESSION["etrax"]["adminOID"]){
				$eb_org = $pvalue;
			}
		}
	}

	//Query das OID auf am Einsatz beteiligte OIDs beschränkt für ein MySQL Statement (OID = 'DEV' OR OID = 'XY')
	$oids_t = explode(",",$OID.",".$Ogleich.",".$Ozeichnen.",".$Ozuweisen.",".$Osehen);
	$oids_t = array_unique(array_filter($oids_t));
	$OID_q = "OID = 'DEV' OR ";
	$i = 0;
	foreach($oids_t as $oid_t){
		if($i == 0){
			$OID_q = $OID_q."OID = '".$oid_t."'";
		} else{
			$OID_q = $OID_q." OR OID = '".$oid_t."'";
		}
		$i++;
	}
	$OID_q = "(".$OID_q.")";


	$oids_el = $oid_el = $elids = $elid = "";
	if($orginfo_json != ""){
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
	}
	$oids_el = implode(",",$oids_t).",".$oids_el; //$oids_el ist die vollständige Liste aller am Einsatz beteiligten Organisation (in eTrax und temporäre für den Einsatz
	$oids_el = array_unique(array_filter(explode(",",$oids_el)));
	$elids = array_unique(array_filter(explode(",",$elids)));
	

// Include the main TCPDF library (search for installation path).
require_once('../vendor/tcpdf/tcpdf.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {
	
	
    
    //Page header
   /* public function Header() {
		
			$this->SetY(10);
			// Set font
			$this->SetFont('merriweathersanslight', '', 8);
			// Title
			$this->Cell(0, 15, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
		
    }*/

    // Page footer
    public function Footer() {
		
			// Position at 15 mm from bottom
			$this->SetY(-12);
			$image_file = 'images/etrax.png';
			$this->Image($image_file, 15, '', 15, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
			// Set font
			
		
    }
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator('Created with eTrax | rescue');
$pdf->SetAuthor($org_arr[$_SESSION["etrax"]["adminOID"]]["kurzname"]);
$pdf->SetTitle('Suchgebiet - Einsatz '.$_SESSION["etrax"]["Einsatzname"]);
$pdf->SetSubject('');
$pdf->SetKeywords('');

//Style Definiton
$style = "<style>
    h1 {
        color: #455a64;
        font-family: oswaldlight;
        font-size: 28pt;
        text-decoration: '';
		text-align: center;
		letter-spacing: 5px;
		text-transform: uppercase;
		border-bottom: 2px solid #D3302F;
		
    }
	.h1_deckblatt {
        color: #455a64;
        font-family: oswaldlight;
        font-size: 48pt;
        text-decoration: '';
		text-align: center;
		letter-spacing: 5px;
		text-transform: uppercase;
		
		
    }
	h2 {
        color: #455a64;
        font-family: oswaldlight;
        font-size: 20pt;
        text-decoration: '';
		text-align: left;
    }
	h3, .h3 {
        color: #455a64;
        font-family: oswaldlight;
		line-height: 10px;
        font-size: 16pt;
        text-decoration: '';
		text-align: left;
		border-bottom: 1px solid #718792;
    }
	h4 {
        color: #455a64;
        font-family: oswaldb;
        font-size: 12pt;
        text-decoration: '';
		text-align: left;
    }
	h5, .h5 {
        color: #455a64;
        font-family: oswaldlight;
		line-height: 10px;
        font-size: 12pt;
        text-decoration: '';
		text-align: left;
    }
	.bold, b, strong{
        color: #000;
        font-family: rokkittb;
        font-size: 10pt;
        text-decoration: '';
		text-align: left;
    }
	.deleted_msg{
        text-decoration: 'line-through';
	}
	.tableheader {
        color: #455a64;
        font-family: oswaldb;
        font-size: 10pt;
        text-decoration: '';
		text-align: left;
	}
	ul {
		list-style-type: square;
		
	}
	em {
		font-family: rokkittlight;
        font-size: 10pt;
        font-style: oblique;
		letter-spacing: 2px;
	}
	.rot {
		color: #D3302F;
	}
	.hellrot {
		color: #ff6659;
	}
	.hinweis {
		color: #D3302F;
		font-family: rokkittlight;
		font-size: 10pt;
	}
	.blocksatz {
		text-align: justify;
	}
	.org_in_tab {
		font-family: oswaldlight;
		line-height: 14px;
		font-size:12px;
	}
	.leg_poi {
		font-family: oswaldlight;
		line-height: 11px;
		font-size:9px;
	}
	.copyright {
		font-family: oswaldlight;
		font-size: 9pt;
	}
	</style>";

// set header and footer fonts
//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setHeaderFont(Array('merriweathersanslight', '', 8));
$pdf->setFooterFont(Array('merriweathersanslight', '', 8));

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(25, 25, 20);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/ger.php')) {
    require_once(dirname(__FILE__).'/lang/ger.php');
    $pdf->setLanguageArray($l);
}

// set font
$pdf->SetFont('rokkittlight', '', 10);
//$pdf->SetFont('dejavusanscondensed', '', 10);

//Suchgebiete aufbauen
	$einsatz_query = $db->prepare("SELECT suchgebiete,pois,data FROM settings WHERE EID = ".$EID."");
	$einsatz_query->execute($einsatz_query->errorInfo());
	while ($einsatz = $einsatz_query->fetch(PDO::FETCH_ASSOC)){
		$data_json = string_decrypt($einsatz['data']);
		$data = json_decode($data_json, true);
		$suchgebiete_json = string_decrypt($einsatz['suchgebiete']);
		$suchgebiete = json_decode($suchgebiete_json, true);
		$pois_json = string_decrypt($einsatz['pois']);
		$pois = json_decode($pois_json, true);
		
	}
	
	$overview = false;
	//Alle Suchgebiete für Darstellung
	$suchgebiet_all = array();
	if(!empty($suchgebiete["features"])){
		//foreach($suchgebiete["features"] as $gebiet){
			$nr_i = 0;
			foreach($suchgebiete["features"] as $gebiet2){
				$i = 0;
				$x_temp_i = $y_temp_i = 0;
				//Abfangen der Punktsuche
				if(isset($suchgebiete['features'][$nr_i]['properties']['typ'])) {
					if($suchgebiete['features'][$nr_i]['properties']['typ'] != "Punktsuche" && $suchgebiete['features'][$nr_i]['properties']['typ'] != "EL") {
						foreach($suchgebiete['features'][$nr_i]['geometry']['coordinates'][0] as $xy){
							if($x_temp_i != $suchgebiete['features'][$nr_i]['geometry']['coordinates'][0][$i][0] && $y_temp_i != $suchgebiete['features'][$nr_i]['geometry']['coordinates'][0][$i][1]){ //entfernt Mehrfacheinträge
								//Aufbauen des Suchgebiets
								$suchgebiet_all[$nr_i][] = array('x' => $suchgebiete['features'][$nr_i]['geometry']['coordinates'][0][$i][0],
													  'y' => $suchgebiete['features'][$nr_i]['geometry']['coordinates'][0][$i][1],
													  'typ' => $suchgebiete['features'][$nr_i]['properties']['typ']);
							}
							$x_temp_i = $suchgebiete['features'][$nr_i]['geometry']['coordinates'][0][$i][0];
							$y_temp_i = $suchgebiete['features'][$nr_i]['geometry']['coordinates'][0][$i][1];
							$i++;
							if($suchgebiete['features'][$nr_i]['properties']['id'] == $suchgebiet_ID){
								if(strpos($suchgebiete['features'][$nr_i]['properties']['typ'],"bersicht") != false){
									$overview = true;
								}
							}
						}
					} elseif($suchgebiete['features'][$nr_i]['properties']['typ'] == "Punktsuche" || $suchgebiete['features'][$nr_i]['properties']['typ'] == "EL") {
					//Es wird ein "Kreis" um den ursprünglichen Mittelpunkt gezeichnet
						for($r = 0; $r <= 24; $r++){
							$suchgebiet_all[$nr_i][] = array('x' => ($suchgebiete['features'][$nr_i]['geometry']['coordinates'][0] + (0.0005*cos($r*15*pi()/180))*((cos($suchgebiete['features'][$nr_i]['geometry']['coordinates'][0]*pi()/180)))),
								  'y' => ($suchgebiete['features'][$nr_i]['geometry']['coordinates'][1] + (0.0005*sin($r*15*pi()/180))*(1-(sin($suchgebiete['features'][$nr_i]['geometry']['coordinates'][1]*pi()/180)*0.5))),
								  'typ' => $suchgebiete['features'][$nr_i]['properties']['typ']);
						}
								  
					}
				}
				$nr_i++;
				
			}
		//}
	}
	
	//Alle Tracks für die Darstellung
	//Trackdarstellung neu, basierend auf GeoJSON
	$tracks_all = array();
	$track_nr = $point_nr = 0;
	//Einlesen des Files
	if(file_exists("../../../secure/data/".$EID."/tracks.txt")){
		$track_arr = json_decode(decrypt(file_get_contents("../../../secure/data/".$EID."/tracks.txt")),true);
		foreach($track_arr["features"] as $key => $track){ //Schleife für die einzelnen Tracks
				//echo "<br>Gruppe ".$track["properties"]["id"]."";
				//echo "<br>Koordinaten:";
				foreach($track_arr["features"][$key]["geometry"]["coordinates"][0] as $key_c => $coord_t){ //Schleife durch die Koordinatenpunkte
					$tracks_all[$track_nr][$point_nr] = array('x' => $coord_t[0],
																  'y' => $coord_t[1],
																  'typ' => "Track");
					$point_nr++;
				}
				$point_nr = 0;
				$track_nr++;
			
		}
	}
	//Ende Tracks für Darstellung
	
	//Bildschirm ausdruck
	$print_view = false;
	if(isset($_GET["ul"]) && isset($_GET["lr"])){
		$print_view = true;
		$group_name = "";
		$e_group = "";
		$suchgebiete = json_decode('{"type":"FeatureCollection","features":[{"type":"Feature","properties":{"name":"'.$group_name.'","typ":"Bildschirmausdruck","id":"","color":"#dc3545","beschreibung":"","img":"","gruppe":"'.$e_group.'","OID":"DEV","strokecolor":"#ffffff","strokewidth":"1","lineDash":"5","fillcolor":"#ffffff","status":"","masse":""},"geometry":{"type":"Polygon","coordinates":[[['.htmlspecialchars($_GET["ul"]).'],['.htmlspecialchars($_GET["lr"]).']]]}}]}', true);
	}
	
	if(isset($_GET["format"]) && array_key_exists(htmlspecialchars($_GET["format"]),$papier)) { $get_format = htmlspecialchars($_GET["format"]); } else { $get_format = "";}
	
	switch($get_format){
		default: //A4 quer
			$papier_w = $papier["A4q"]["width"]; //Papierbreite in mm
			$papier_h = $papier["A4q"]["height"]; //Papierbreite in mm
			$abstand_oben = 30; //Oberer Bildabstand in mm - wenn nichts eingefüllt wird, wird das Bild zentriert
			$abstand_links = 15; //Linker Bildabstand in mm - wenn nichts eingefüllt wird, wird das Bild zentriert
			$dpi = 120; //angestrebte dots per inch (~ 96 ist die Pixeldichte bei einem Monitor)
			$orientation = $papier["A4q"]["orientation"];
			$p_format = $papier["A4q"]["format"];
			if($print_view || $overview){
				$xKacheln = 5; // Anzahl der horizontalen Kacheln
				$yKacheln = 3; // Anzahl der vertikalen Kacheln
			} else {
				$xKacheln = 3; // Anzahl der horizontalen Kacheln
				$yKacheln = 3; // Anzahl der vertikalen Kacheln
			}
			if($xKacheln % 2 == 0){$xTiles_add = 2;} else {$xTiles_add = 2;}
			if($yKacheln % 2 == 0){$yTiles_add = 2;} else {$yTiles_add = 2;}
		break;
		case "A3q":
			$papier_w = $papier["A3q"]["width"]; //Papierbreite in mm
			$papier_h = $papier["A3q"]["height"]; //Papierbreite in mm
			$abstand_oben = 30; //Oberer Bildabstand in mm - wenn nichts eingefüllt wird, wird das Bild zentriert
			$abstand_links = 15; //Linker Bildabstand in mm - wenn nichts eingefüllt wird, wird das Bild zentriert
			$dpi = 135; //angestrebte dots per inch (~ 96 ist die Pixeldichte bei einem Monitor)
			$orientation = $papier["A3q"]["orientation"];
			$p_format = $papier["A3q"]["format"];
			
			if($print_view || $overview){
				$xKacheln = 8; // Anzahl der horizontalen Kacheln
				$yKacheln = 5; // Anzahl der vertikalen Kacheln
			} else {
				$xKacheln = 5; // Anzahl der horizontalen Kacheln
				$yKacheln = 5; // Anzahl der vertikalen Kacheln
			}
			if($xKacheln % 2 == 0){$xTiles_add = 2;} else {$xTiles_add = 2;}
			if($yKacheln % 2 == 0){$yTiles_add = 2;} else {$yTiles_add = 2;}
		break;
		
	}
	
	//Voreinstellungen zum Karten zeichnen
	/*$papier_w = 297; //Papierbreite in mm
	$papier_h = 210; //Papierbreite in mm
	$abstand_oben = 30; //Oberer Bildabstand in mm - wenn nichts eingefüllt wird, wird das Bild zentriert
	$abstand_links = 15; //Linker Bildabstand in mm - wenn nichts eingefüllt wird, wird das Bild zentriert
	$dpi = 120; //angestrebte dots per inch (~ 96 ist die Pixeldichte bei einem Monitor)*/
	$px_w = round($papier_w/25.4*$dpi);	
	$px_h = round($papier_h/25.4*$dpi);

	//Karten definieren - werden jetzt per require aus include/include.php geholt
	
	
	
		
	//Styles fürs Zeichnen
	$style_suchgebiet = array('width' => 1.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 100, 60));
	if($print_view || $overview){ //Polygone und Linien sind anders gefärbt
		$style_suchgebiet_alle = array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(245, 65, 160));
		$style_wegsuche_alle = array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => '4,2', 'color' => array(245, 65, 160));
	} else {
		$style_suchgebiet_alle = array('width' => .75, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(66, 135, 245));
		$style_wegsuche_alle = array('width' => .75, 'cap' => 'butt', 'join' => 'miter', 'dash' => '4,2', 'color' => array(66, 135, 245));
	}
	$style_grid_hauptintervall = array('width' => .5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(10, 10, 10));
	$style_grid_hilfsintervall = array('width' => .15, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(10, 10, 10));
	$style_legende = array('width' => 0.15, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
	$style_poi = array('width' => .75, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(211, 48, 47));
	$style_el = array('width' => .7, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
	$style_tracks_alle = array('width' => .5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(245, 5, 5));
	
	
	
	
	//Schleife baut die einzelnen Suchgebietsseiten auf
	$nr = 0;
	if(!empty($suchgebiete["features"])){
		foreach($suchgebiete["features"] as $gebiet){
			if($suchgebiet_ID == "" || $suchgebiet_ID == $suchgebiete['features'][$nr]['properties']['id']){ //SID definiert
				// add a page
				$pdf->AddPage($orientation, $p_format);


		
		
				//Das gewählte Suchgebiet
				$i = 0;
				$x_temp = $y_temp = 0;
				$suchgebiet = array();
				if(isset($suchgebiete['features'][$nr]['properties']['typ'])) {
					if($suchgebiete['features'][$nr]['properties']['typ'] != "Punktsuche") {
						foreach($suchgebiete['features'][$nr]['geometry']['coordinates'][0] as $xy){
							if($x_temp != $suchgebiete['features'][$nr]['geometry']['coordinates'][0][$i][0] && $y_temp != $suchgebiete['features'][$nr]['geometry']['coordinates'][0][$i][1]){ //entfernt Mehrfacheinträge
								//Aufbauen des Suchgebiets
								$suchgebiet[] = array('x' => $suchgebiete['features'][$nr]['geometry']['coordinates'][0][$i][0],
													  'y' => $suchgebiete['features'][$nr]['geometry']['coordinates'][0][$i][1]);
							}
							$x_temp = $suchgebiete['features'][$nr]['geometry']['coordinates'][0][$i][0];
							$y_temp = $suchgebiete['features'][$nr]['geometry']['coordinates'][0][$i][1];
							$i++;
						}
					} elseif($suchgebiete['features'][$nr]['properties']['typ'] == "Punktsuche") {
					//Es wird ein "Kreis" um den ursprünglichen Mittelpunkt gezeichnet
						for($r = 0; $r <= 24; $r++){
							$suchgebiet[] = array('x' => ($suchgebiete['features'][$nr]['geometry']['coordinates'][0] + (0.0005*cos($r*15*pi()/180))*((cos($suchgebiete['features'][$nr]['geometry']['coordinates'][0]*pi()/180)))),
								  'y' => ($suchgebiete['features'][$nr]['geometry']['coordinates'][1] + (0.0005*sin($r*15*pi()/180))*(1-(sin($suchgebiete['features'][$nr]['geometry']['coordinates'][1]*pi()/180)*0.5))));
						}
					}
				}
				
				//Min und Max Werte aus Polygon holen
				$j = count($suchgebiet);
				$lat_min = $lat_max = $lon_min = $lon_max = $i = 0;
				if($j > 1){ //Polygon zeichnen erst ab mindestens 2 Punkten möglich
					foreach($suchgebiet as $sg){
						if($i == 0){
							$lon_min = $lon_max = $sg["x"];
							$lat_min = $lat_max = $sg["y"];
							
						} else {
							if($sg["x"] < $lon_min){ $lon_min = $sg["x"];}
							if($sg["x"] > $lon_max){ $lon_max = $sg["x"];}
							if($sg["y"] < $lat_min){ $lat_min = $sg["y"];}
							if($sg["y"] > $lat_max){ $lat_max = $sg["y"];}
						}
						$i++;
					}
				}
				
				//Kartenausschnitt drucken
				/*$latlon_temp = "";
				if(isset($_GET["ul"]) && isset($_GET["lr"])){
					$latlon_temp = explode(",",htmlspecialchars($_GET["ul"]));
					$lon_min = $latlon_temp[0];
					$lat_max = $latlon_temp[1];
					$latlon_temp = explode(",",htmlspecialchars($_GET["lr"]));
					$lon_max = $latlon_temp[0];
					$lat_min = $latlon_temp[1];
				}*/
				
				//Berechnung Zoom Level
				$zlevel = array(22,21,20,19,18,17,16,15,14,13,12,11,10,9,8,7,6,5,4,3,2,1,0);
				$z = $xT_1 = $xT_2 = $yT_1 = $yT_2 = $i = $zx = $zy = 0;
				foreach($zlevel as $zt){
					$n = pow(2, $zt);
					$latRad = $lat_max * pi() / 180;
					$xT_1 = floor($n * (($lon_min + 180) / 360));
					$yT_1 = floor($n * (1-(log(tan($latRad) + 1/cos($latRad)) /pi())) / 2);
					$latRad = $lat_min * pi() / 180;
					$xT_2 = ceil($n * (($lon_max + 180) / 360));
					$yT_2 = ceil($n * (1-(log(tan($latRad) + 1/cos($latRad)) /pi())) / 2);
					if($i == 0){ //für den Fall, dass es ein sehr kleines Polygon wäre
						//$zx = $zy = $zt;
					} else {
						if((($xT_2 - $xT_1)) <=  $xKacheln && $zx == 0) { // Zoomlevel für X Ausdehnung
							$zx = $zt;
							if($print_view){$zx = $zx+1;}
						}
						if((($yT_2 - $yT_1)) <=  $yKacheln && $zy == 0) { // Zoomlevel für X Ausdehnung
							$zy = $zt;
							if($print_view){$zy = $zy+1;}
						}
					}
					$i++;
				}
				$z = min($zx,$zy,$path[$map]['zlim']);
				//Berechnen der Tiles mit finalisiertem Zoom Level
				$n = pow(2, $z);
				$latRad = $lat_max * pi() / 180;
				$xT_1 = floor($n * (($lon_min + 180) / 360));
				$yT_1 = floor($n * (1-(log(tan($latRad) + 1/cos($latRad)) /pi())) / 2);
				$latRad = $lat_min * pi() / 180;
				$xT_2 = ceil($n * (($lon_max + 180) / 360));
				$yT_2 = ceil($n * (1-(log(tan($latRad) + 1/cos($latRad)) /pi())) / 2);
								
				$xTiles_sub = floor(($xKacheln-($xT_2 - $xT_1)+1)/2); //Anzahl an Tiles um die nach links korrigiert werden muss
				$yTiles_sub = floor(($yKacheln-($yT_2 - $yT_1)+1)/2); //Anzahl an Tiles um die nach links korrigiert werden muss
				
				//Berechnung der Tiles
				$lon = $lon_min;
				$lat = $lat_max;
				
				$latRad = $lat * pi() / 180;
				$n = pow(2, $z);
				$xTile = round($n * (($lon + 180) / 360));
				$yTile = round($n * (1-(log(tan($latRad) + 1/cos($latRad)) /pi())) / 2);
				
				//Bildmitte ausrechnen für Tile Auswahl
				$n = pow(2, $z);
				$xT_m = ($n * ((($lon_min+$lon_max)/2 + 180) / 360)); //Mittelpunkt x
				$xTile = $xT_m - ($xKacheln + $xTiles_add)/2;
				if($xTile - floor($xTile) <=0.5){
					$xTile = floor($xTile);
				} else {
					$xTile = floor($xTile)+1;
				}
				$latRad = ($lat_min+$lat_max)/2 * pi() / 180;
				$yT_m = $n * (1-(log(tan($latRad) + 1/cos($latRad)) /pi())) / 2; // Mittelpunkt y
				$yTile = $yT_m - ($yKacheln + $yTiles_add)/2;
				if($yTile - floor($yTile) <=0.5){
					$yTile = floor($yTile);
				} else {
					$yTile = floor($yTile)+1;
				}
				
			//Versuch richtig zu zentrieren	
				//Zusammenkopieren der Grafiken - x/y vertauscht (z.B. Basemap)
				$png = array();
				$tile_error = 0;
				for($x = 0; $x < $xKacheln+$xTiles_add; $x++){
					for($y = 0; $y < $yKacheln+$yTiles_add; $y++){
						if($path[$map]['dir'] == 'xy'){
							if($path[$map]['format'] == "png"){
								$png[$x][$y] = @imagecreatefrompng($path[$map]['path'].$z."/".($xTile + $x)."/".($yTile + $y).".png");
							}
							if($path[$map]['format'] == "jpeg"){
								$png[$x][$y] = @imagecreatefromjpeg($path[$map]['path'].$z."/".($xTile + $x)."/".($yTile + $y).".jpeg");
							}
						} else {
							if($path[$map]['format'] == "png"){
								$png[$x][$y] = @imagecreatefrompng($path[$map]['path'].$z."/".($yTile + $y)."/".($xTile + $x).".png");
							}
							if($path[$map]['format'] == "jpeg"){
								$png[$x][$y] = @imagecreatefromjpeg($path[$map]['path'].$z."/".($yTile + $y)."/".($xTile + $x).".jpeg");
							}
						}
						if(!$png[$x][$y]){ // Für den Fall, dass das Tile nicht verfügbar ist
							$png[$x][$y] = imagecreatetruecolor(256, 256); //Anlegen eines Blanko Bildfiles
							$white = imagecolorallocate($png[$x][$y], 255, 255, 255);
							imagefill($png[$x][$y], 0, 0, $white);
							$tile_error++;
						}
					}
				}
				if($tile_error > 0){ $tile_error = '<span class="hinweis">Hinweis: Gewähltes Kartenmaterial im Ausschnitt nicht verfügbar.</span>';} else { $tile_error = '';}
					
				
				$dst = imagecreatetruecolor(($xKacheln+$xTiles_add)*256, ($yKacheln+$yTiles_add)*256); //Anlegen eines Blanko Bildfiles
				$white = imagecolorallocate($dst, 255, 255, 255);
				imagefill($dst, 0, 0, $white);
				for($x = 0; $x < ($xKacheln+$xTiles_add); $x++){
					for($y = 0; $y < ($yKacheln+$yTiles_add); $y++){
						imagecopymerge($dst, $png[$x][$y], (($x*256)), (($y*256)), 0, 0, 256, 256, 100);
						imagedestroy($png[$x][$y]);
					}
				}
				//Bildmitte ausrechnen
				$n = pow(2, $z);
				$xT_m = ($n * ((($lon_min+$lon_max)/2 + 180) / 360));
				$xTile_m = ($xT_m - $xTile)/($xKacheln+$xTiles_add); //Bildmittelpunkt als Relativwert zum linken Bildrand
				$latRad = ($lat_min+$lat_max)/2 * pi() / 180;
				$yT_m = $n * (1-(log(tan($latRad) + 1/cos($latRad)) /pi())) / 2;
				$yTile_m = ($yT_m - $yTile)/($yKacheln+$yTiles_add); //Bildmittelpunkt als Relativwert zum oberen Bildrand
				//xTile und yTile für die weitere Berechnung korrigieren
				$xTile = ($xT_m - ($xKacheln)/2);
				$yTile = ($yT_m - ($yKacheln)/2);
				
				//Richtig zuschneiden:
				$x_o = floor($xKacheln+2)*256*$xTile_m - ($xKacheln)*256/2;
				$y_o = floor($yKacheln+2)*256*$yTile_m - ($yKacheln)*256/2;
				//$html .= "x_o: ".$x_o." y_o: ".$y_o."<br>";
				
				$dst2 = imagecrop($dst, array('x' => $x_o, 'y' => $y_o, 'width' => $xKacheln*256, 'height' => $yKacheln*256));
				
				
			//richtig Zentrieren ende
				
				//Neues Grafikfile anlegen
				if($px_w > 256*$xKacheln){
					if($abstand_links == ""){
						$margin_left = round(($px_w - 256*$xKacheln)/2); //in Pixel
						
					} elseif($abstand_links > 0){
						$margin_left = round($abstand_links/25.4*$dpi);
					}
					$width = $px_w;	
				} else {
					$margin_left = 0;
					$width = 256*$xKacheln;
				}
				if($px_h > 256*$yKacheln){
					if($abstand_oben == ""){
						$margin_top = round(($px_h - 256*$yKacheln)/2);
					} elseif($abstand_oben > 0){
						$margin_top = round($abstand_oben/25.4*$dpi);
					}
					$height = $px_h;
				} else {
					$margin_top = 0;
					$height = 256*$yKacheln;
				}
				//echo "Width: ".$width." Height: ".$height."<br>";
				$dst = imagecreatetruecolor($width, $height);
				$white = imagecolorallocate($dst, 255, 255, 255);
				imagefill($dst, 0, 0, $white);
				
				/*for($x = 0; $x < $xKacheln; $x++){
					for($y = 0; $y < $yKacheln; $y++){
						imagecopymerge($dst, $png[$x][$y], (($x*256)+$margin_left), (($y*256)+$margin_top), 0, 0, 256, 256, 100);
					}
				}*/	
				
				
				imagecopymerge($dst, $dst2, $margin_left, $margin_top, 0, 0, $xKacheln*256, $yKacheln*256, 100);
				
				//Bild speichern
				imagepng($dst, 'images/maptemp'.$nr.'.png');
				imagedestroy($dst);
				imagedestroy($dst2);
				
				// -- set new background ---

				// get the current page break margin
				$bMargin = $pdf->getBreakMargin();
				// get current auto-page-break mode
				$auto_page_break = $pdf->getAutoPageBreak();
				// disable auto-page-break
				$pdf->SetAutoPageBreak(false, 0);
				// set bacground image
				//$pdf->Image($img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
				$pdf->Image('images/maptemp'.$nr.'.png', 0, 0, $papier_w, $papier_h, '', '', '', false, $dpi, '', false, false, 0);
				// restore auto-page-break status
				//$pdf->SetAutoPageBreak($auto_page_break, $bMargin);
				// set the starting point for the page content
				//$pdf->setPageMark();
				
				$pdf->Bookmark('Suchgebiet '.($nr+1).' - '.$gebiet['properties']["name"], 1, 1, '', '', array(28,49,58));
				$sgebietname = (isset($gebiet["properties"]["name"]) && strpos($gebiet["properties"]["name"], "hlen!") == false) ? $gebiet["properties"]["name"] : "";
				$html = '<h3>Einsatz '.($_SESSION["etrax"]["Einsatzname"]).'</h3>';
				if($gebiet['properties']["typ"] == "Wegsuche"){
					$laenge_t = explode(".",$gebiet['properties']["masse"]);
					$html .= '<h5>Wegsuche - '.$sgebietname.' - Länge: '.runden($laenge_t[0],$_SESSION["etrax"]["lunit"],$_SESSION["etrax"]["lfactor"]).'</h5>';
				}
				if($gebiet['properties']["typ"] == "Punktsuche"){
					$laenge_t = explode(".",$gebiet['properties']["masse"]);
					$html .= '<h5>Punktsuche - '.$sgebietname.'</h5>';
				}
				if($gebiet['properties']["typ"] == "Mantrailer"){
					$laenge_t = explode(".",$gebiet['properties']["masse"]);
					$html .= '<h5>Mantrailer - '.$sgebietname.'</h5>';
				}
				if($gebiet['properties']["typ"] == "Suchgebiet"){
					$laenge_t = explode(".",$gebiet['properties']["masse"]);
					$html .= '<h5>Suchgebiet - '.$sgebietname.' - Fläche: '.runden($laenge_t[0],$_SESSION["etrax"]["aunit"],$_SESSION["etrax"]["afactor"]).'</h5>';
				}
				$pdf->SetXY(15,10);
				$pdf->writeHTML($style.$html, true, false, true, false, '');
				$html = "";
				
			//Copyrightvermerk Kartenmaterial
				$pos_l = ($margin_left)/$dpi*25.4;
				$pos_r = $pos_l + (($xKacheln*256))/$dpi*25.4;
				$pos_t = (($yKacheln*256+$margin_top))/$dpi*25.4+1;
				//$pos_t = 195/25.4*$dpi;
				//$pdf->writeHTMLCell(150,5,$pos_l, $pos_t, $path[$map]['copyright']." ".$tile_error, 1, 0, 1, true, 'Left', true);
				$pdf->writeHTMLCell(170, 5, 15, $pos_t, $style.'<span class="copyright bold">'.$path[$map]['copyright']." ".$tile_error.'</span>', 1, 0, 1, true, 'J', true);
				$pdf->writeHTMLCell(100, 5, ($pos_r - 100), $pos_t, $style.'<span class="copyright bold" style="text-align: right;">Koordinatengitter: UTM</span>', 1, 0, 1, true, 'R', true);
				$pdf->writeHTMLCell(170, 5, 30, -12, $style."Karte erstellt von ".$org_arr[$OID_admin]["bezeichnung"], 1, 0, 1, true, 'J', true);
				
				//Lat/Lon auf Karten
				$linestyle = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 0, 0));
				$pdf->SetLineStyle($linestyle);
				
				//Berechnung upper-lower-/left-right
				$n = pow(2, $z);
				$lon_deg_ul = ($xTile) / $n * 360.0 - 180.0;
				$lat_deg_ul = rad2deg(atan(sinh(pi() * (1 - 2 * ($yTile)/ $n))));
				$lon_deg_ll = ($xTile) / $n * 360.0 - 180.0;
				$lat_deg_ll = rad2deg(atan(sinh(pi() * (1 - 2 * ($yTile + $yKacheln)/ $n))));
				$lon_deg_ur = ($xTile + $xKacheln) / $n * 360.0 - 180.0;
				$lat_deg_ur = rad2deg(atan(sinh(pi() * (1 - 2 * ($yTile)/ $n))));
				$lon_deg_lr = ($xTile + $xKacheln) / $n * 360.0 - 180.0;
				$lat_deg_lr = rad2deg(atan(sinh(pi() * (1 - 2 * ($yTile + $yKacheln)/ $n))));
				
				//Koordinaten in UTM für Grid
				$ul_utm = ll2utm($lat_deg_ul,$lon_deg_ul);
				$ll_utm = ll2utm($lat_deg_ll,$lon_deg_ll);
				$ur_utm = ll2utm($lat_deg_ur,$lon_deg_ur);
				$lr_utm = ll2utm($lat_deg_lr,$lon_deg_lr);
				
				$x_scale_u = ($lon_deg_ur-$lon_deg_ul)/(256*$xKacheln); //ermitteln des Maßstaßbs je Pixel
				$x_scale_l = ($lon_deg_lr-$lon_deg_ll)/(256*$xKacheln);
				$y_scale_l = ($lat_deg_ul-$lat_deg_ll)/(256*$yKacheln); //ermitteln des Maßstaßbs je Pixel
				$y_scale_r = ($lat_deg_ur-$lat_deg_lr)/(256*$yKacheln);
				$x_scale = min($x_scale_u,$x_scale_l);
				$y_scale = min($y_scale_r,$y_scale_l);
				
				$x_papier = ($lon_deg_ul - ($margin_left*$x_scale)); //Linke obere Ecke des Blattes in lat/lon
				$y_papier = ($lat_deg_ul + ($margin_top*$y_scale)); 
				/*
				$x_temp = ($lon_deg_ul - $x_papier)/$x_scale/$dpi*25.4;
				$y_temp = ($y_papier - $lat_deg_ul)/$y_scale/$dpi*25.4;
				$html = "X_temp: ".$x_temp." Y_temp: ".$y_temp."lat: ".$lat_deg_ul." lon: ".$lon_deg_ul."<br>";
				$pdf->Circle($x_temp,$y_temp,.5);
				$x_temp = ($lon_deg_ur - $x_papier)/$x_scale/$dpi*25.4;
				$y_temp = ($y_papier - $lat_deg_ur)/$y_scale/$dpi*25.4;
				$html .= "X_temp: ".$x_temp." Y_temp: ".$y_temp."lat: ".$lat_deg_ur." lon: ".$lon_deg_ur."<br>";
				$pdf->Circle($x_temp,$y_temp,.5);
				$x_temp = ($lon_deg_ll - $x_papier)/$x_scale/$dpi*25.4;
				$y_temp = ($y_papier - $lat_deg_ll)/$y_scale/$dpi*25.4;
				$html .= "X_temp: ".$x_temp." Y_temp: ".$y_temp."lat: ".$lat_deg_ll." lon: ".$lon_deg_ll."<br>";
				$pdf->Circle($x_temp,$y_temp,.5);
				$x_temp = ($lon_deg_lr - $x_papier)/$x_scale/$dpi*25.4;
				$y_temp = ($y_papier - $lat_deg_lr)/$y_scale/$dpi*25.4;
				$html .= "X_temp: ".$x_temp." Y_temp: ".$y_temp."lat: ".$lat_deg_lr." lon: ".$lon_deg_lr."<br>";
				$html .= "X_scale_u: ".$x_scale_u." Y_scale_l: ".$y_scale_l."<br>";
				$html .= "X_scale_l: ".$x_scale_l." Y_scale_r: ".$y_scale_r."<br>";
				$html .= "X_papier: ".$x_papier." Y_Papier: ".$y_papier."<br>";/*/
				
				//Clipping der ausgegebenen Polygone
				// Start clipping
				$pdf->StartTransform();

				// Draw clipping rectangle
				$pdf->Rect($margin_left/$dpi*25.4, $margin_top/$dpi*25.4, $xKacheln*256/$dpi*25.4, $yKacheln*256/$dpi*25.4, 'CNZ');
				//echo "Clipping: ".($margin_left/$dpi*25.4)." , ".($margin_top/$dpi*25.4).", ".($xKacheln*256/$dpi*25.4).", ".($yKacheln*256/$dpi*25.4)."<br>";
				
				//Alle anderen Suchgebiete als Polygon zeichnen
					$xx = 0;
					$typ_t = "";
					// set alpha to semi-transparency
					$pdf->SetAlpha(0.9);
					foreach($suchgebiet_all as $sg_o){
						$new_polygon_all = $typ_t = "";
						if($xx != $nr || $print_view || $overview){	//damit eigenes Suchgebiet nicht doppelt gezeichnet wird
							//echo "Suchgebiet Nr. ".$xx."<br>";
							//foreach($sg_o as $sg){
							foreach($suchgebiet_all[$xx] as $sg){
								$new_polygon_all .= (($sg["x"] - $x_papier)/$x_scale/$dpi*25.4).";".(($y_papier - $sg["y"])/$y_scale/$dpi*25.4).";";
								//echo "<br>SID :".$nr." - Gebiet: ".$xx." Position: ".(($sg["x"] - $x_papier)/$x_scale/$dpi*25.4).";".(($y_papier - $sg["y"])/$y_scale/$dpi*25.4).";<br>";
								$typ_t = $sg["typ"];
							}
							$new_polygon_all = explode(";",substr($new_polygon_all,0,-1));
							
							if($typ_t == "Suchgebiet" || $typ_t == "Punktsuche"){		
								$pdf->Polygon($new_polygon_all, 'D', array('all' => $style_suchgebiet_alle), array(220, 220, 220));
								//echo $typ_t."<br>";
							}
							if($typ_t == "Wegsuche"){		
								$pdf->PolyLine($new_polygon_all, 'D', array('all' => $style_wegsuche_alle), array(220, 220, 220));
								//echo $typ_t."<br>";
							}
							
							
						}
						$xx++;
					}
					// Transparenz wieder zurücksetzen
					$pdf->SetAlpha(1);
				//Alle anderen Suchgebiet Zeichnen Ende
				
				//Suchgebiet als Polygon zeichnen
					$new_polygon = "";
					foreach($suchgebiet as $sg){
						$new_polygon .= (($sg["x"] - $x_papier)/$x_scale/$dpi*25.4).";".(($y_papier - $sg["y"])/$y_scale/$dpi*25.4).";";
					}
					$new_polygon = explode(";",substr($new_polygon,0,-1));
					//$html .= $new_polygon;
					
					// set alpha to semi-transparency
					$pdf->SetAlpha(0.9);

					if($gebiet['properties']["typ"] == "Suchgebiet" || $gebiet['properties']["typ"] == "Punktsuche"){		
						$pdf->Polygon($new_polygon, 'D', array('all' => $style_suchgebiet), array(220, 220, 220));
					}
					if($gebiet['properties']["typ"] == "Wegsuche"){		
						$pdf->PolyLine($new_polygon, 'D', array('all' => $style_suchgebiet), array(220, 220, 220));
					}
					// Transparenz wieder zurücksetzen
					$pdf->SetAlpha(1);
				//Suchgebiet Zeichnen Ende
				
				//Tracks zeichnen
					$xt = 0;
					$typ_t = "";
					// set alpha to semi-transparency
					$pdf->SetAlpha(0.9);
					foreach($tracks_all as $tr_o){
						$new_polygon_all = $typ_t = "";
							//echo "Track Nr. ".$xt."<br>";
							//foreach($tr_o as $tr){
							foreach($tracks_all[$xt] as $tr){
								$new_polygon_all .= (($tr["x"] - $x_papier)/$x_scale/$dpi*25.4).";".(($y_papier - $tr["y"])/$y_scale/$dpi*25.4).";";
								//echo "<br>Track :".$nr." - Gebiet: ".$xt." Position: ".(($tr["x"] - $x_papier)/$x_scale/$dpi*25.4).";".(($y_papier - $tr["y"])/$y_scale/$dpi*25.4).";<br>";
								$typ_t = $tr["typ"];
							}
							$new_polygon_all = explode(";",substr($new_polygon_all,0,-1));
							//print_r($new_polygon_all);
							$pdf->PolyLine($new_polygon_all, 'D', array('all' => $style_tracks_alle), array(220, 220, 220));
							//echo $typ_t."<br>";
							
						$xt++;
					}
					// Transparenz wieder zurücksetzen
					$pdf->SetAlpha(1);
				//Tracks Zeichnen Ende
				
				// Stop clipping
				$pdf->StopTransform();
				//Ende Clipping der ausgegebenen Polygone
				
			//Min - Max Bereiche des Kartenfensters ermitteln
				$xmin_map = floor(min($ul_utm["x"],$ll_utm["x"],$ur_utm["x"],$lr_utm["x"])/6)*6; //Ergibt den minimalen linken Längengrad des UTM Streifens im Kartenausschnitt + 6 ist rechter Rand
				$xmax_map = floor(max($ul_utm["x"],$ll_utm["x"],$ur_utm["x"],$lr_utm["x"])/6)*6; //Ergibt den maximalen linken Längengrad des UTM Streifens im Kartenausschnitt + 6 ist rechter Rand
				$ymin_map =floor(min($ul_utm["y"],$ll_utm["y"],$ur_utm["y"],$lr_utm["y"])); //Ergibt den minimalen Breitengrad des UTM Streifens im Kartenausschnitt
				$ymax_map = floor(max($ul_utm["y"],$ll_utm["y"],$ur_utm["y"],$lr_utm["y"])); //Ergibt den maximalen Breitengrad des UTM Streifens im Kartenausschnitt
				//min/max lat_lon gerundet
				$xmin_map_ll = floor(min($lon_deg_ul,$lon_deg_ll,$lon_deg_ur,$lon_deg_lr)/6)*6; //Ergibt den minimalen linken Längengrad des UTM Streifens im Kartenausschnitt + 6 ist rechter Rand
				$xmax_map_ll = floor(max($lon_deg_ul,$lon_deg_ll,$lon_deg_ur,$lon_deg_lr)/6)*6; //Ergibt den maximalen linken Längengrad des UTM Streifens im Kartenausschnitt + 6 ist rechter Rand
				$ymin_map_ll =floor(min($lat_deg_ul,$lat_deg_ll,$lat_deg_ur,$lat_deg_lr)); //Ergibt den minimalen Breitengrad des UTM Streifens im Kartenausschnitt
				$ymax_map_ll = floor(max($lat_deg_ul,$lat_deg_ll,$lat_deg_ur,$lat_deg_lr)); //Ergibt den maximalen Breitengrad des UTM Streifens im Kartenausschnitt
				
				//min/max lat_lon
				$xmin_map_ll_f = (min($lon_deg_ul,$lon_deg_ll,$lon_deg_ur,$lon_deg_lr)); //Ergibt den minimalen linken Längengrad des UTM Streifens im Kartenausschnitt + 6 ist rechter Rand
				$xmax_map_ll_f = (max($lon_deg_ul,$lon_deg_ll,$lon_deg_ur,$lon_deg_lr)); //Ergibt den maximalen linken Längengrad des UTM Streifens im Kartenausschnitt + 6 ist rechter Rand
				$ymin_map_ll_f =(min($lat_deg_ul,$lat_deg_ll,$lat_deg_ur,$lat_deg_lr)); //Ergibt den minimalen Breitengrad des UTM Streifens im Kartenausschnitt
				$ymax_map_ll_f = (max($lat_deg_ul,$lat_deg_ll,$lat_deg_ur,$lat_deg_lr)); //Ergibt den maximalen Breitengrad des UTM Streifens im Kartenausschnitt
					
				
				//POIs zeichnen
				
					//Schriftarten ändern
					$pdf->SetFont('helvetica', '', 8);
					

				$legende_eintrag = array();
				if($gebiet['properties']["typ"] == "Suchgebiet"){$legende_eintrag[] = array("name" => "Suchgebiet", "typ" => $gebiet['properties']["typ"], "style" => $style_suchgebiet, "bez" => "", "x" => "", "y" => "");} // Suchgebiet ist erster Eintrag in der Legende
				if($gebiet['properties']["typ"] == "Wegsuche"){$legende_eintrag[] = array("name" => "Wegsuche", "typ" => $gebiet['properties']["typ"], "style" => $style_suchgebiet, "bez" => "", "x" => "", "y" => "");} // Suchgebiet ist erster Eintrag in der Legende
				if($gebiet['properties']["typ"] == "Punktsuche"){$legende_eintrag[] = array("name" => "Suchpunkt", "typ" => $gebiet['properties']["typ"], "style" => $style_suchgebiet, "bez" => "", "x" => "", "y" => "");} // Suchgebiet ist erster Eintrag in der Legende
				if($xx > 1){$legende_eintrag[] = array("name" => "Andere Suchgebiete", "typ" => "Suchgebiet_andere", "style" => $style_suchgebiet_alle, "bez" => "", "x" => "", "y" => "");} // Wenn andere Suchgebiete vorhanden sind
				if($xt > 0){$legende_eintrag[] = array("name" => "Trackingdaten", "typ" => "Tracks", "style" => $style_tracks_alle, "bez" => "", "x" => "", "y" => "");} // Wenn Trackingdaten vorhanden sind
				$poi_x_t = $data[0]["elon"];
				$poi_y_t = $data[0]["elat"];
				if($poi_x_t >= $xmin_map_ll_f && $poi_x_t <= $xmax_map_ll_f && $poi_y_t >= $ymin_map_ll_f && $poi_y_t <= $ymax_map_ll_f){
					$legende_eintrag[] = array("name" => "Einsatzleitung", "typ" => "EL", "style" => $style_el, "bez" => "EL", "x" => $poi_x_t, "y" => $poi_x_t); // Einsatzleitung einzeichnen
					$pdf->Polygon(array((($poi_x_t - $x_papier)/$x_scale/$dpi*25.4-2.7),(($y_papier - $poi_y_t)/$y_scale/$dpi*25.4-4),(($poi_x_t - $x_papier)/$x_scale/$dpi*25.4),(($y_papier - $poi_y_t)/$y_scale/$dpi*25.4),(($poi_x_t - $x_papier)/$x_scale/$dpi*25.4+2.7),(($y_papier - $poi_y_t)/$y_scale/$dpi*25.4-4)), 'DF', array('all' => $style_el), array(0, 0, 0));
					$pdf->Circle((($poi_x_t - $x_papier)/$x_scale/$dpi*25.4),(($y_papier - $poi_y_t)/$y_scale/$dpi*25.4-5), 3,0,360, 'DF', $style_el, array(255, 255, 255));
					$pdf->SetXY((($poi_x_t - $x_papier)/$x_scale/$dpi*25.4-3),(($y_papier - $poi_y_t)/$y_scale/$dpi*25.4-5));
					$pdf->Cell(6,6, "EL", 0, $ln=0, 'C', 0, '', 0, false, 'C', 'C');
				}
					//echo "Features: ".count(($pois['features']))."<br>";
					$new_poi = "";
					$poi_nr = 0;
					//echo "Count von pois: ".count($pois)."<br>";
				$X_temp = $pdf->GetX();
				$Y_temp = $pdf->GetY();
				$skip = 0;
				$alphabet = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
				if(!empty($pois)){
					foreach($pois['features'] as $poi){
						if($pois['features'][$poi_nr]['properties']['name'] != "Einsatz Startpunkt") {	
							//echo "poi Nr. ".$poi_nr."<br>";
							$poi_x_t = $pois['features'][$poi_nr]['geometry']['coordinates'][0];
							$poi_y_t = $pois['features'][$poi_nr]['geometry']['coordinates'][1];
							if($poi_x_t >= $xmin_map_ll_f && $poi_x_t <= $xmax_map_ll_f && $poi_y_t >= $ymin_map_ll_f && $poi_y_t <= $ymax_map_ll_f && isset($pois['features'][$poi_nr])){
								$new_poi .= (($poi_x_t - $x_papier)/$x_scale/$dpi*25.4).";".(($y_papier - $poi_y_t)/$y_scale/$dpi*25.4).";";
								if(isset($pois['features'][$poi_nr]['properties']['name']) && $pois['features'][$poi_nr]['properties']['name'] != "POI") {
									$poiname_t = $pois['features'][$poi_nr]['properties']['name'];
								} elseif($pois['features'][$poi_nr]['properties']['name'] == "POI" && isset($pois['features'][$poi_nr]['properties']['beschreibung'])) {
									$poiname_t = substr($pois['features'][$poi_nr]['properties']['beschreibung'],0,25);
								} else {
									$poiname_t = "Keine Beschreibung";
								}
								$legende_eintrag[] = array("name" => $poiname_t, "typ" => $pois['features'][$poi_nr]['geometry']['type'], "bez" => $alphabet[($poi_nr-$skip)], "x" => $poi_x_t, "y" => $poi_y_t );
								$pdf->Polygon(array((($poi_x_t - $x_papier)/$x_scale/$dpi*25.4-2.2),(($y_papier - $poi_y_t)/$y_scale/$dpi*25.4-4),(($poi_x_t - $x_papier)/$x_scale/$dpi*25.4),(($y_papier - $poi_y_t)/$y_scale/$dpi*25.4),(($poi_x_t - $x_papier)/$x_scale/$dpi*25.4+2.2),(($y_papier - $poi_y_t)/$y_scale/$dpi*25.4-4)), 'DF', array('all' => $style_poi), array(211, 48, 47));
								$pdf->Circle((($poi_x_t - $x_papier)/$x_scale/$dpi*25.4),(($y_papier - $poi_y_t)/$y_scale/$dpi*25.4-5), 2.5,0,360, 'DF', $style_poi, array(255, 255, 255));
								$pdf->SetXY((($poi_x_t - $x_papier)/$x_scale/$dpi*25.4-3),(($y_papier - $poi_y_t)/$y_scale/$dpi*25.4-5));
								$pdf->Cell(6,6, $alphabet[$poi_nr-$skip], 0, $ln=0, 'C', 0, '', 0, false, 'C', 'C');
							}
						} else { $skip++;}
							$poi_nr++; // Die Nummerierung bleibt gleich
					}
					
					$new_poi = explode(";",substr($new_poi,0,-1));
					//$html .= $new_polygon;
				}	
				$pdf->SetXY($X_temp,$Y_temp);
				//Schriftart zurücksetzen
				$pdf->SetFont('rokkittlight', '', 10);
				//POIs Zeichnen Ende

			//Koordinatengitter zeichnen
					$hauptint = array("20" => 1000,"19" => 1000,"18" => 1000,"17" => 1000,"16" => 1000,"15" => 1000,"14" => 2000,"13" => 5000,"12" => 10000,"11" => 10000,"10" => 20000,"9" => 50000,"8" => 100000); //Intervall (Meter) der Hauptlinien
					$hilfsint = array("20" => 20,"19" => 20,"18" => 10,"17" => 4,"16" => 4,"15" => 2,"14" => 2,"13" => 2,"12" => 2,"11" => 1,"10" => 2,"9" => 2,"8" => 2); // Anzahl der Hilflinien zwischen den Hauptintervallen
					
					
					//$html .= "xmin_map: ".$xmin_map." ymin_map: ".$ymin_map."<br>";
					//$html .= "xmax_map: ".$xmax_map." ymax_map: ".$ymax_map."<br>";
					
					//Schleife, die alle vorkommenden UTM Streifen durchläuft
					for($xl_utm = $xmin_map_ll; $xl_utm <= $xmax_map_ll; $xl_utm += 6) { //6 steht für die 6 Grad Sprünge im UTM Gitter
						$utm_name_temp = "UTM".($xl_utm/6+31); // Benennung des UTM Streifens
						//$html .= "UTM Name temp: ".$utm_name_temp."<br>";
						//Offset Gitternetz von linkem oberen Bildausschnitt
						$ulxO = (ceil($ul_utm["x"]/$hauptint[$z])*$hauptint[$z]); //Offset Oben Links in horizontaler Richtung
						$ulyO = (floor($ul_utm["y"]/$hauptint[$z])*$hauptint[$z]); //Offset Oben Links in vertikaler Richtung
						
						//Steigung berechnen
						$hor = ($ur_utm["y"] - $ul_utm["y"])/($ur_utm["x"] - $ul_utm["x"]);
						$ver = ($ll_utm["x"] - $ul_utm["x"])/($ll_utm["y"] - $ul_utm["y"]);
						//Grenzen und Schritte für das das Grid berechnen
						$xfrom = ($ulxO-$hauptint[$z]);
						$xto = ($ur_utm["x"]+$hauptint[$z]);
						$xstep = ($hauptint[$z]/$hilfsint[$z]);
						$yfrom = ($ulyO);
						$yto = ($ul_utm["y"]+$hauptint[$z]);
						$ystep = ($hauptint[$z]/$hilfsint[$z]);
						//$html .= "KOnfig yfrom: ".$yfrom." yto: ".$yto."<br>";
					
						//UTM Gittergrenze einzeichnen
						$arr_temp = array();
						$jj = 0;
						for($y_t = $ymin_map; $y_t <= $ymax_map; $y_t += ceil((($ymax_map-$ymin_map)/10)*1000)/1000) {
							$arr_t = utm2ll($xl_utm, $y_t, ($xl_utm/6+31), true);
							if(($arr_t["lon"] >= $xmin_map_ll_f && $arr_t["lon"] <= $xmax_map_ll_f) && ($arr_t["lat"] >= $ymin_map_ll_f && $arr_t["lat"] <= $ymax_map_ll_f)){ //Gitternetzlinien werden nur auf der Karte gezeichnet
								$arr_temp[$jj] = $arr_t;
							}
							$jj++;
						}
						// Einzeichnen fehlt noch
						
						//Vertikale Linien
						$jj = 0;
						for($x_t = $xfrom; $x_t < $xto; $x_t += $xstep) {
							//10 Stützpunkte für Linien
								$arr_temp = array();
								$new_polygon = "";
								$arr_coord_temp = array();
								//Versuch
								$yfrom = floor(($ll_utm["y"]+($x_t-$ul_utm["x"])*$hor));
								$yto = floor(($ul_utm["y"]+($x_t-$ul_utm["x"])*$hor));
								//$html .= "yfrom: ".$yfrom." numeric:".is_numeric($yfrom)." yto: ".$yto." numeric:".is_numeric($yto)."<br>";
								
								$iii = 0;
								for($y_t = $yfrom; $y_t <= $yto; $y_t += floor(($yto-$yfrom)/10)) {
									if($iii < 10){
									$arr_t = utm2ll($x_t, $y_t, ($xl_utm/6+31), true);
									} else {
										$arr_t = utm2ll($x_t, $yto, ($xl_utm/6+31), true); // damit der letzte Punkt am oberen Bildrand sitzt, unabhängig von Rundungsfehlern
									}
									//$html .= "arr_t_lon: ".$arr_t["lon"]." xmin_map_ll: ".$xmin_map_ll." arr_t_lat: ".$arr_t["lat"]." ymin_map_ll: ".$ymin_map_ll."<br>";
									if(($arr_t["lon"] >= $xmin_map_ll_f && $arr_t["lon"] <= $xmax_map_ll_f) ){ //Gitternetzlinien werden nur auf der Karte gezeichnet
										$arr_temp[$iii] = $arr_t;
										$arr_coord_temp[$iii] = array("x_utm" => $x_t, "x_papier" => (($arr_t["lon"] - $x_papier)/$x_scale/$dpi*25.4), "y_utm" => $y_t, "y_papier" => (($y_papier - $arr_t["lat"])/$y_scale/$dpi*25.4),"zone" => ($xl_utm/6+31));
										$new_polygon .= (($arr_temp[$iii]["lon"] - $x_papier)/$x_scale/$dpi*25.4).";".(($y_papier - $arr_temp[$iii]["lat"])/$y_scale/$dpi*25.4).";";
									$iii++;
									}
									
									//$html .= "yfrom: ".(($arr_temp[$iii]["lon"] - $x_papier)/$x_scale/$dpi*25.4)." numeric:".is_numeric((($arr_temp[$iii]["lon"] - $x_papier)/$x_scale/$dpi*25.4))." yto: ".(($y_papier - $arr_temp[$iii]["lat"])/$y_scale/$dpi*25.4)." numeric:".is_numeric((($y_papier - $arr_temp[$iii]["lat"])/$y_scale/$dpi*25.4))."<br>";
									
								}
								
								
							//Mit IF-Schleife abfangen ob Wert zwischen den +/- Werten des Streifens liegen
							$arr_utmcheck = utm2ll($x_t, ($ul_utm["y"]+$hauptint[$z]), ($xl_utm/6+31), true);
							if($arr_utmcheck["lon"] >= $xl_utm && $arr_utmcheck["lon"] <= ($xl_utm+6)){
								//UTM Streifen Namen
								$utmstreifen = (floor($arr_utmcheck["lon"]/6)+31); //Ergebnis ist z.B. 33
								if ($arr_utmcheck["lat"] >= 0) {
										$utmstreifen = $utmstreifen."N"; //Nordhalbkugel
									} else {
										$utmstreifen = $utmstreifen."S"; //Südhalbkugel
									}	 //Ergebnis ist z.B. 33N
								//Switch für gerade 1000er = Hauptintervall
								if(substr($x_t,-3) == "000") {	
									//Style für Hauptintervall
									$grid_style = $style_grid_hauptintervall;
								} else {
									//Style für Hilfsintervall
									$grid_style = $style_grid_hilfsintervall;
								}
									
									//print_r($arr_coord_temp);
									//Label einfügen
									if(!empty($arr_coord_temp) && !empty($arr_coord_temp[$jj]) && $arr_coord_temp[$jj]["x_papier"] >= 0 && $arr_coord_temp[$jj]["x_papier"] <= $papier_w && $arr_coord_temp[$jj]["y_papier"] >= 0 && $arr_coord_temp[$jj]["y_papier"] <= $papier_h){
										$ij = count($arr_coord_temp)-1;
										if($arr_coord_temp[$ij]["y_papier"] <= ($margin_top/$dpi*25.4+1)){
											$pdf->SetXY($arr_coord_temp[$ij]["x_papier"]-15, $arr_coord_temp[$ij]["y_papier"]-3);
											$pdf->Cell(30, 0, $utmstreifen." ".number_format(round($arr_coord_temp[$ij]["x_utm"]), 0, ',', '.'), 0, $ln=0, 'C', 0, '', 0, false, 'C', 'B');
										}
									}
									//Zeichnen
									if(!empty($new_polygon)){	
										$new_polygon = explode(";",substr($new_polygon,0,-1));
										$pdf->PolyLine($new_polygon, 'D', array('all' => $grid_style), array(220, 220, 220));
									}
									
							} //Ende IF für Streifenbreite	
							//$html .= $jj." - X Papier: ".$arr_coord_temp[$ij]["x_papier"]." , Y Papier: ".$arr_coord_temp[$ij]["y_papier"]."<br>";
							$jj++;
							
						}	// Ende Vertikale Linien
						
						//Horizontale Linien
						$yfrom = ($ulyO+$hauptint[$z]);
						$yto = ($ll_utm["y"]-$hauptint[$z]);
						$ystep = ($hauptint[$z]/$hilfsint[$z]);
						$jj = 0;
						//$html .= "yfrom: ".$yfrom." numeric: ".is_numeric($yfrom)." yto: ".$yto." numeric: ".is_numeric($yto)."<br>";
						for($y_t = $yfrom; $y_t > $yto; $y_t += (0-$ystep)) {
							//10 Stützpunkte für Linien
								$arr_temp = array();
								$new_polygon = "";
								$arr_coord_temp = array();
								//Versuch
								$xfrom = floor(($ll_utm["x"]+($y_t-$ll_utm["y"])*$ver));
								$xto = floor(($lr_utm["x"]+($y_t-$ll_utm["y"])*$ver));
								//$html .= "yfrom: ".$yfrom." numeric: ".is_numeric($yfrom)." yto: ".$yto." numeric: ".is_numeric($yto)."<br>";
								//$html .= "y_t: ".$y_t."<br>";
								
								$iii = 0;
								for($x_t = $xfrom; $x_t <= $xto; $x_t += floor(($xto-$xfrom)/10)) {
									if($iii < 10){
									$arr_t = utm2ll($x_t, $y_t, ($xl_utm/6+31), true);
									} else {
										$arr_t = utm2ll($xto, $y_t, ($xl_utm/6+31), true); // damit der letzte Punkt am rechten Bildrand sitzt, unabhängig von Rundungsfehlern
									}
									//$html .= "arr_t_lon: ".$arr_t["lon"]." xmin_map_ll: ".$xmin_map_ll." arr_t_lat: ".$arr_t["lat"]." ymin_map_ll: ".$ymin_map_ll."<br>";
									if(($arr_t["lat"] >= $ymin_map_ll_f && $arr_t["lat"] <= $ymax_map_ll_f) ){ //Gitternetzlinien werden nur auf der Karte gezeichnet
										$arr_temp[$iii] = $arr_t;
										$arr_coord_temp[$iii] = array("x_utm" => $x_t, "x_papier" => (($arr_t["lon"] - $x_papier)/$x_scale/$dpi*25.4), "y_utm" => $y_t, "y_papier" => (($y_papier - $arr_t["lat"])/$y_scale/$dpi*25.4),"zone" => ($xl_utm/6+31));
										$new_polygon .= (($arr_temp[$iii]["lon"] - $x_papier)/$x_scale/$dpi*25.4).";".(($y_papier - $arr_temp[$iii]["lat"])/$y_scale/$dpi*25.4).";";
									$iii++;
									}
									
									//$html .= "yfrom: ".(($arr_temp[$iii]["lon"] - $x_papier)/$x_scale/$dpi*25.4)." numeric:".is_numeric((($arr_temp[$iii]["lon"] - $x_papier)/$x_scale/$dpi*25.4))." yto: ".(($y_papier - $arr_temp[$iii]["lat"])/$y_scale/$dpi*25.4)." numeric:".is_numeric((($y_papier - $arr_temp[$iii]["lat"])/$y_scale/$dpi*25.4))."<br>";
									
								}
								
								
							//Mit IF-Schleife abfangen ob Wert zwischen den +/- Werten des Streifens liegen
							//$arr_utmcheck = utm2ll($x_t, ($ul_utm["y"]+$hauptint[$z]), ($xl_utm/6+31), true);
							//if($arr_utmcheck["lon"] >= $xl_utm && $arr_utmcheck["lon"] <= ($xl_utm+6)){
								//UTM Streifen Namen
								//$utmstreifen = (floor($arr_utmcheck["lon"]/6)+31); //Ergebnis ist z.B. 33
								/*if ($arr_utmcheck["lat"] >= 0) {
										$utmstreifen = $utmstreifen."N"; //Nordhalbkugel
									} else {
										$utmstreifen = $utmstreifen."S"; //Südhalbkugel
									}	 //Ergebnis ist z.B. 33N*/
								//Switch für gerade 1000er = Hauptintervall
								if(substr($y_t,-3) == "000") {	
									//Style für Hauptintervall
									$grid_style = $style_grid_hauptintervall;
								} else {
									//Style für Hilfsintervall
									$grid_style = $style_grid_hilfsintervall;
								}
									
									//print_r($arr_coord_temp);
									//Label einfügen
									if(!empty($arr_coord_temp) && !empty($arr_coord_temp[$jj]) && isset($arr_coord_temp[$jj]) && $arr_coord_temp[$jj]["x_papier"] >= 0 && $arr_coord_temp[$jj]["x_papier"] <= $papier_w && $arr_coord_temp[$jj]["y_papier"] >= 0 && $arr_coord_temp[$jj]["y_papier"] <= $papier_h){
										$ij = count($arr_coord_temp)-1;
										$x_papier_temp = $pdf->GetX();
										$y_papier_temp = $pdf->GetY();
										$pdf->SetXY($arr_coord_temp[0]["x_papier"]-3, $arr_coord_temp[0]["y_papier"]+15);
										//$pdf->SetXY($arr_coord_temp[$ij]["x_papier"]-15, $margin_top*$dpi/25.4-3);
										$pdf->StartTransform();
										$pdf->Rotate(90);
										$pdf->Cell(30, 0, number_format(round($arr_coord_temp[0]["y_utm"]), 0, ',', '.'), 0, $ln=0, 'C', 0, '', 0, false, 'C', 'C');
										$pdf->StopTransform();
										$pdf->SetXY($x_papier_temp,$y_papier_temp);
										//$pdf->Text($arr_coord_temp[0]["x_papier"], $arr_coord_temp[0]["y_papier"]-2, $utmstreifen." ".round($arr_coord_temp[0]["x_utm"]), false, false, true, 1, 0, 'left', false, false, 0, false, 'T', 'M', false);
									}
									//Zeichnen
									if(!empty($new_polygon)){
										$new_polygon = explode(";",substr($new_polygon,0,-1));
										$pdf->PolyLine($new_polygon, 'D', array('all' => $grid_style), array(220, 220, 220));
									}
									
							//} //Ende IF für Streifenbreite	
							//$html .= $jj." - X Papier: ".$arr_coord_temp[$ij]["x_papier"]." , Y Papier: ".$arr_coord_temp[$ij]["y_papier"]."<br>";
							//$html .= $jj." - X Papier: ".$arr_coord_temp[0]["x_papier"]." , Y Papier: ".$arr_coord_temp[0]["y_papier"]."<br>";
							$jj++;
							
						}	// Ende Horizontale Linien
						
					}
			// Ende Koordinatengitter zeichnen
			

			
			// Legende zeichnen
			if(!$print_view && !$overview){
				$leg_width = 220-(($margin_left + $xKacheln*256)/$dpi*25.4)-5;
				$leg_height = (($yKacheln*256+$margin_top)/$dpi*25.4)-115;
				//$leg_breite = 55;
				$pdf->SetLineStyle($style_legende);
				$leg_top = ($yKacheln*256+$margin_top)/$dpi*25.4-$leg_height;
				$leg_left = ($margin_left + $xKacheln*256)/$dpi*25.4+2;
				//$pdf->RoundedRect($leg_left, $leg_top, $leg_width, $leg_height, 2, '1010');
				$pdf->PolyLine(array($leg_left+$leg_width+2,$leg_top,$leg_left+$leg_width+2,$leg_top+$leg_height), 'D', array('all' => $style_grid_hilfsintervall), array(220, 220, 220));
				$pdf->SetXY($leg_left, $leg_top);
				$html_legende = '<h5>Legende</h5>';
				$pdf->writeHTMLCell($leg_width, 0, $leg_left, $leg_top, $style.$html_legende, 0, 0, 0, true, 'C', true);
				$leg_top = $leg_top + 8;
				$leg_height = $leg_height - 8;
				// get the current page break margin
				$bMargin = $pdf->getBreakMargin();
				// get current auto-page-break mode
				$auto_page_break = $pdf->getAutoPageBreak();
				// disable auto-page-break
				$pdf->SetAutoPageBreak(false, 0);
				$e_nr = $r_nr = $c_nr = 0;
				$r_max = floor(($leg_height - 5)/10);
				$leg_width = $leg_width-5;
				//print_r($legende_eintrag);
				foreach($legende_eintrag as $leg){
					if($e_nr < $r_max){ // maximale Anzahl an Legendeneinträgen
						//Beschriftung
						$pdf->SetXY((($margin_left + $xKacheln*256)/$dpi*25.4+1)+($leg_width)*($c_nr)+10, $leg_top+10*$r_nr+1);
						//$pdf->Cell(($leg_width/3-12), 10, substr($leg["name"],0,30), 0, $ln=0, 'left', 0, '', 0, false, 'C', 'C');
						if(strlen($leg["name"]) > 50){$dots = " ...";} else {$dots = "";}
						$pdf->writeHTMLCell(($leg_width-8), 10, (($margin_left + $xKacheln*256)/$dpi*25.4+1)+($leg_width)*($c_nr)+10, $leg_top+10*$r_nr+2, $style.'<span class="leg_poi">'.substr($leg["name"],0,50).$dots.'</span>', 0, 0, 0, true, 'J', true);
						//$pdf->MultiCell(($leg_width-8), 10, '<span class="leg_poi">'.substr($leg["name"],0,50).$dots.'</span>', 0, 'J', 0, 0, '', '', true, 0, false, true, 10, 'M');
						//$pdf->Cell(($leg_width/3-12), 10, substr($leg["name"],0,30), 0, $ln=0, 'left', 0, '', 0, false, 'C', 'C');
						
						//Symbol
						$pdf->SetFont('helvetica', '', 7);
							if($leg["typ"] == "Suchgebiet" || $leg["typ"] == "Punktsuche"){
								$pdf->Polygon(array(($leg_left+($leg_width/3-12)*($c_nr)),(($leg_top+10*$r_nr+5)-3),($leg_left+($leg_width/3-12)*($c_nr)+6),(($leg_top+10*$r_nr+5)-3),($leg_left+($leg_width/3-12)*($c_nr)+6),(($leg_top+10*$r_nr+5)+3),($leg_left+($leg_width/3-12)*($c_nr)),(($leg_top+10*$r_nr+5)+3)), 'D', array('all' => $style_suchgebiet), array(220, 220, 220));
							}
							if($leg["typ"] == "Wegsuche"){
								$pdf->PolyLine(array(($leg_left+($leg_width/3-12)*($c_nr)),(($leg_top+10*$r_nr+5)),($leg_left+($leg_width/3-12)*($c_nr)+6),(($leg_top+10*$r_nr+5))), 'D', array('all' => $style_suchgebiet), array(220, 220, 220));
							}
							if($leg["typ"] == "Tracks"){
								$pdf->PolyLine(array(($leg_left+($leg_width/3-12)*($c_nr)),(($leg_top+10*$r_nr+5)),($leg_left+($leg_width/3-12)*($c_nr)+6),(($leg_top+10*$r_nr+5))), 'D', array('all' => $style_tracks_alle), array(220, 220, 220));
							}
							if($leg["typ"] == "Suchgebiet_andere"){
								$pdf->Polygon(array(($leg_left+($leg_width/3-12)*($c_nr)),(($leg_top+10*$r_nr+5)-3),($leg_left+($leg_width/3-12)*($c_nr)+6),(($leg_top+10*$r_nr+5)-3),($leg_left+($leg_width/3-12)*($c_nr)+6),(($leg_top+10*$r_nr+5)+1),($leg_left+($leg_width/3-12)*($c_nr)),(($leg_top+10*$r_nr+5)+1)), 'D', array('all' => $style_suchgebiet_alle), array(220, 220, 220));
								$pdf->PolyLine(array(($leg_left+($leg_width/3-12)*($c_nr)),(($leg_top+10*$r_nr+5)+3),($leg_left+($leg_width/3-12)*($c_nr)+6),(($leg_top+10*$r_nr+5)+3)), 'D', array('all' => $style_wegsuche_alle), array(220, 220, 220));
							}
							if($leg["typ"] == "Point"){
								$pdf->Polygon(array((($leg_left+($leg_width/3)*($c_nr)+3)-1.7),((($leg_top+10*$r_nr+5))+1),($leg_left+($leg_width/3)*($c_nr)+3),(($leg_top+10*$r_nr+5))+4,(($leg_left+($leg_width/3)*($c_nr)+3)+1.7),((($leg_top+10*$r_nr+5))+1)), 'DF', array('all' => $style_poi), array(211, 48, 47));
								$pdf->Circle(($leg_left+($leg_width/3)*($c_nr)+3),(($leg_top+10*$r_nr+5)), 2,0,360, 'DF', $style_poi, array(255, 255, 255));
								$pdf->SetXY(($leg_left+($leg_width/3)*($c_nr)),(($leg_top+10*$r_nr+5)));
								$pdf->Cell(6,6, $leg["bez"], 0, $ln=0, 'C', 0, '', 0, false, 'C', 'C');
							}
							if($leg["typ"] == "EL"){
								$pdf->Polygon(array((($leg_left+($leg_width/3)*($c_nr)+3)-1.7),((($leg_top+10*$r_nr+5))+1),($leg_left+($leg_width/3)*($c_nr)+3),(($leg_top+10*$r_nr+5))+4,(($leg_left+($leg_width/3)*($c_nr)+3)+1.7),((($leg_top+10*$r_nr+5))+1)), 'DF', array('all' => $style_el), array(0, 0, 0));
								$pdf->Circle(($leg_left+($leg_width/3)*($c_nr)+3),(($leg_top+10*$r_nr+5)), 2,0,360, 'DF', $style_el, array(255, 255, 255));
								$pdf->SetXY(($leg_left+($leg_width/3)*($c_nr)),(($leg_top+10*$r_nr+5)));
								$pdf->Cell(6,6, $leg["bez"], 0, $ln=0, 'C', 0, '', 0, false, 'C', 'C');
							}
						$r_nr++;
						if($r_nr == $r_max){$c_nr++;$r_nr = 0;}
						$pdf->SetFont('rokkittlight', '', 10);
						$e_nr++;
					}
				} 
				
				$pdf->SetAutoPageBreak($auto_page_break, $bMargin);
				// set the starting point for the page content
				$pdf->setPageMark();
				
			// Legende zeichnen Ende
				$pdf->writeHTML($style.$html, true, false, true, false, '');
			}	
				
			
			//Beschreibung vermisste Person
			if(!$print_view && !$overview){
				$html = '<h4>Angaben zur gesuchten Person</h4>';
					// output the HTML content
					$alter = "";
					if(isset($gesucht_json->gesuchtalter) && !empty($gesucht_json->gesuchtalter)){
						$alter = $gesucht_json->gesuchtalter;
					} elseif(isset($gesucht_json->gesuchtgebdatum) && $gesucht_json->gesuchtgebdatum > 0){
						$alter = date_diff(date_create($gesucht_json->gesuchtgebdatum),date_create('today'))->y;
					} else {
						$alter = "";
					}
					
					$html = '<span class="bold">Name: </span>'.(isset($gesucht_json->gesuchtname) && !empty($gesucht_json->gesuchtname)? $gesucht_json->gesuchtname : "").'
						<br><span class="bold">Alter: </span>'.$alter.'
						<br><span class="bold">Beschreibung: </span><br>'.(isset($gesucht_json->gesuchtbeschreibung) && !empty($gesucht_json->gesuchtbeschreibung) ? $gesucht_json->gesuchtbeschreibung : "");
					// output the HTML content
					//$imgdata = base64_decode($gesucht_json->gesuchtbild);
					// The '@' character is used to indicate that follows an image data stream and not an image file name
					//$pdf->Image('@'.$imgdata,232,30,50,80,'','','',false,300, '',false,false,0,'T',false,false);
					$filename = is_file( "../../../secure/data/".$EID."/gesucht_big.jpg") ? "../../../secure/data/".$EID."/gesucht_big.jpg" : "../img/no-pic.jpg";
					$pdf->Image($filename,232,30,50,80,'','','',false,300, '',false,false,0,'T',false,false);
					$pdf->SetXY(190,10);
					$pdf->writeHTMLCell($style.$html, true, false, true, false, '');
					$pdf->writeHTMLCell(95, 15, 180, 15, $style.'<h4 align="center">Angaben zur gesuchten Person</h4>', 0, 0, 0, true, 'C', true); //Überschrift
					$pdf->writeHTMLCell(50, 80, 180, 30, $style.$html, 0, 0, 0, true, 'J', true);
					
					
				// Gruppeneinteilung
				
				$einsatz_query = $db->prepare("SELECT gruppen FROM settings WHERE EID = ".$EID."");
				
				$einsatz_query->execute($einsatz_query->errorInfo());
				$n_gruppen = 0;
				while ($einsatz = $einsatz_query->fetch(PDO::FETCH_ASSOC)){
					$data_gruppen = string_decrypt($einsatz['gruppen']);
					$gruppen = json_decode($data_gruppen, true);
					
				}
				
				$zeit = $gruppendata = $gruppen_html = $status_html = $html = $group_name  = $row_cc = "";
				$group_found = false;
				if($gruppen != ""){
					foreach($gruppen as $gruppe){
						foreach($gruppe['data'] as $gruppendata){
							if($gebiet['properties']["gruppe"] == $gruppendata['gruppe']){
								
								$group_found = true;
								if(!empty($gruppendata['zugewiesen'])){ //Abfangen einer neuen Gruppe ohne Mitglieder
									foreach($gruppendata['zugewiesen'] as $zugewiesen){	
										if(count($oids_t)>2){ $org_bez_t = ' <span class="org_in_tab">['.$org_arr[$zugewiesen["oid"]]["kurzname"].']</span>'; } else {$org_bez_t = "";}
											
										//if($zugewiesen["oid"] == $OID_admin || $OID_admin == "DEV"){
											$gruppen_html .= '<tr style="background-color:'.$row_cc.';" class="org_in_tab"><td>'.$zugewiesen["name"].$org_bez_t.'</td><td align="right">'.$zugewiesen["typ"].'</td></tr>';
										/*}else{
											$gruppen_html .= '<tr style="background-color:'.$row_cc.';"><td>'.$org_arr[$zugewiesen["oid"]]["kurzname"].'</td><td>'.$zugewiesen["dienstnummer"].'</td><td>'.$zugewiesen["typ"].'</td></tr>';
										}*/
										if($row_cc == ""){ $row_cc = "#DEDEDE"; } else {$row_cc = "";}
									}
								} else {
									$gruppen_html .= '<tr style="background-color:'.$row_cc.';"><td colspan="2">Dieser Gruppe wurden keine Mitglieder zugewiesen.</td></tr>';
								}
								
								$group_name = $gruppendata['name'];
								
							} else {
								
								//$group_name = "";
							}
						}
							//echo '<tr><td style="border:1px solid #000;">'.$row.'</td><td style="border:1px solid #000;">'.$zeit.'</td><td style="border:1px solid #000;">'.$gruppeinfo.'</td><td style="border:1px solid #000;">'.$person.'</td></tr>';
							
					
					} 
				}
				if(!$group_found){$gruppen_html .= '<tr style="background-color:'.$row_cc.';"><td colspan="2">Dem Suchgebiet wurde noch keine Gruppe zugewiesen.</td></tr>';}
				$html .= '<h5>Mitglieder '.$group_name.'</h5><table width="100%"><tr class="bold"><td width="75%">Name</td><td width="25%" align="right">Funktion</td></tr>'.$gruppen_html.'</table>';
				
				// output the HTML content
				$pdf->writeHTMLCell(60, 80, 220, 115, $style.$html, 0, 0, 0, true, 'J', true);
			
			}
			
			//Beschreibung Sucharten
			if($gebiet['properties']["typ"] == "Suchgebiet" || $gebiet['properties']["typ"] == "Wegsuche" || $gebiet['properties']["typ"] == "Punktsuche" || $gebiet['properties']["typ"] == "Mantrailer"){	
				// add a page
				$pdf->AddPage($orientation, $p_format);
				
				$html = '<h2>Beschreibung der Suchart durch die primäre Einsatzorganisation ['.$org_arr[$OIDprim]["kurzname"].']</h2>';
				
				// output the HTML content
				$pdf->writeHTML($style.$html, true, false, true, false, '');
				$pdf->SetY($pdf->getY()+10);
				
				if($gebiet['properties']["typ"] == "Suchgebiet"){
					$html = str_replace(array("<H1>","<h1>","</H1>","</h1>"),array("<H3>","<H3>","</H3>","</H3>"),$org_arr[$OIDprim]["flaechensuche"]);
				}
				if($gebiet['properties']["typ"] == "Wegsuche"){
					$html = str_replace(array("<H1>","<h1>","</H1>","</h1>"),array("<H3>","<H3>","</H3>","</H3>"),$org_arr[$OIDprim]["wegsuche"]);
				}
				if($gebiet['properties']["typ"] == "Punktsuche"){
					$html = str_replace(array("<H1>","<h1>","</H1>","</h1>"),array("<H3>","<H3>","</H3>","</H3>"),$org_arr[$OIDprim]["punktsuche"]);
				}
				if($gebiet['properties']["typ"] == "Mantrailer"){
					$html = str_replace(array("<H1>","<h1>","</H1>","</h1>"),array("<H3>","<H3>","</H3>","</H3>"),$org_arr[$OIDprim]["mantrailer"]);
				}
				// output the HTML content
				$pdf->writeHTML($style.$html, true, false, true, false, '');
				$pdf->SetY($pdf->getY()+10);
			
			}	
			
			
			} // Ende SID - Suchgebiets ID
			$nr++;
		
			
			
		if($print_view){ break; } // Beim Ausducken der Seitenansicht wird die Schleife nur 1 mal durchlaufen
		} 
	}


$filename = "Einsatz_".$_SESSION["etrax"]["EID"]."-".$_SESSION["etrax"]["adminOID"]."_Suchgebiet_";

//Close and output PDF document
//$pdf->Output($filename.'.pdf', 'I');  


//Close and output PDF document
//$pdf->Output($filename.'.pdf', 'I');

// Mail variablen
$from = $org_arr[$OID_admin]["bezeichnung"];
$to = $_GET['send_to'];
$subject = 'Einsatz '.$EID.': '. $_GET['typ'].' '.$group_name;
$message = 'Einsatz '.$EID.': '.$_GET['typ'].' '.$group_name.' erstellt von '.$org_arr[$OID_admin]["bezeichnung"];
//echo $to,$message;
$fileatt = $pdf->Output($filename.'.pdf', 'E');

//$attachment = chunk_split($fileatt);
$attachment = $fileatt;
$separator = md5(time());
$eol = PHP_EOL;

// main header
$headers  = "From: ".$from.$eol;
$headers .= "MIME-Version: 1.0".$eol; 
$headers .= "Content-Type: multipart/mixed; boundary=\"".$separator."\"";

// message
$body = "--".$separator.$eol;
$body .= "Content-Type: text/html; charset=\"UTF-8\"".$eol;
$body .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
$body .= $message.$eol;

// attachment
$body .= "--".$separator.$eol;
$body .= $attachment.$eol;
$body .= "--".$separator."--";
//echo $body;
// send message
if (mail($to, $subject, $body, $headers)) {
    echo "mail send ... OK";
} else {
    echo "mail send ... ERROR";
}
//============================================================+
// END OF FILE 
//============================================================+

} //Ende mindestens Leserechte

?>
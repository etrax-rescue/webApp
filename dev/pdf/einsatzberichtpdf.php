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
$db_org = $db->prepare("SELECT OID,data FROM organisation");
$db_org->execute($db_org->errorInfo());
$org_arr = array();
while ($reso = $db_org->fetch(PDO::FETCH_ASSOC)){
	$data_org_json = json_decode(substr(string_decrypt($reso['data']), 1, -1));
	$org_arr[$reso["OID"]]["OID"] = $reso["OID"];
	$org_arr[$reso["OID"]]["bezeichnung"] = $data_org_json->bezeichnung;
	$org_arr[$reso["OID"]]["kurzname"] = $data_org_json->kurzname;
	$org_arr[$reso["OID"]]["adresse"] = $data_org_json->adresse;
}

require $baseURL."include/sessionhandler.php"; //Der Sessionhandler schreibt die Sessionwerte neu

if($USER["lesen"]){ //Mindestens Leserechte werden benötigt um etwas angezeigt zu bekommen.

//Funktionen
// Umrechnung der Einheiten
	function runden($num,$unit,$factor){
		$value =(round($num,-2));
		$value = ($value / $factor);
		$newval = ($unit != 'm' && $unit != 'm&sup2;') ? number_format($value, 1, ',', '.') : number_format($value, 0, ',', '.');
		return($newval." ".$unit);
	}


//Einzelne Teile des Berichts drucken

switch($_SERVER['REQUEST_METHOD'])
{
	case 'GET':
		$eb_deckblatt = isset($_GET["eb_deckblatt"]) ? htmlspecialchars($_GET["eb_deckblatt"]) : 0;
		$eb_inhaltsverzeichnis = isset($_GET["eb_inhaltsverzeichnis"]) ? htmlspecialchars($_GET["eb_inhaltsverzeichnis"]): 0;
		$eb_einsatzbericht = isset($_GET["eb_einsatzbericht"]) ? htmlspecialchars($_GET["eb_einsatzbericht"]) : 0;
		$eb_vermisst = isset($_GET["eb_vermisst"]) ? htmlspecialchars($_GET["eb_vermisst"]) : 0;
		$eb_organisationen = isset($_GET["eb_organisationen"]) ? htmlspecialchars($_GET["eb_organisationen"]) : 0;
		$eb_checkliste = isset($_GET["eb_checkliste"]) ? htmlspecialchars($_GET["eb_checkliste"]) : 0;
		$eb_personen = isset($_GET["eb_personen"]) ? htmlspecialchars($_GET["eb_personen"]) : 0;
		$eb_personen_kurz = isset($_GET["eb_personen_kurz"]) ? htmlspecialchars($_GET["eb_personen_kurz"]) : 0;
		$eb_gruppen = isset($_GET["eb_gruppen"]) ? htmlspecialchars($_GET["eb_gruppen"]) : 0;
		$eb_ereignis = isset($_GET["eb_ereignis"]) ? htmlspecialchars($_GET["eb_ereignis"]) : 0;
		$eb_funk = isset($_GET["eb_funk"]) ? htmlspecialchars($_GET["eb_funk"]) : 0;
		$eb_suchgebiet = isset($_GET["eb_suchgebiet"]) ? htmlspecialchars($_GET["eb_suchgebiet"]) : 0;
		$kartenmaterial = isset($_GET["kartenmaterial"]) ? htmlspecialchars($_GET["kartenmaterial"]) : "etraxtopo";
		$eb_uebersicht = isset($_GET["eb_uebersicht"]) ? htmlspecialchars($_GET["eb_uebersicht"]) : 0;
	break;
	case 'POST':
		$eb_deckblatt = isset($_POST["eb_deckblatt"]) ? htmlspecialchars($_POST["eb_deckblatt"]) : 0;
		$eb_inhaltsverzeichnis = isset($_POST["eb_inhaltsverzeichnis"]) ? htmlspecialchars($_POST["eb_inhaltsverzeichnis"]): 0;
		$eb_einsatzbericht = isset($_POST["eb_einsatzbericht"]) ? htmlspecialchars($_POST["eb_einsatzbericht"]) : 0;
		$eb_vermisst = isset($_POST["eb_vermisst"]) ? htmlspecialchars($_POST["eb_vermisst"]) : 0;
		$eb_organisationen = isset($_POST["eb_organisationen"]) ? htmlspecialchars($_POST["eb_organisationen"]) : 0;
		$eb_checkliste = isset($_POST["eb_checkliste"]) ? htmlspecialchars($_POST["eb_checkliste"]) : 0;
		$eb_personen = isset($_POST["eb_personen"]) ? htmlspecialchars($_POST["eb_personen"]) : 0;
		$eb_personen_kurz = isset($_POST["eb_personen_kurz"]) ? htmlspecialchars($_POST["eb_personen_kurz"]) : 0;
		$eb_gruppen = isset($_POST["eb_gruppen"]) ? htmlspecialchars($_POST["eb_gruppen"]) : 0;
		$eb_ereignis = isset($_POST["eb_ereignis"]) ? htmlspecialchars($_POST["eb_ereignis"]) : 0;
		$eb_funk = isset($_POST["eb_funk"]) ? htmlspecialchars($_POST["eb_funk"]) : 0;
		$eb_suchgebiet = isset($_POST["eb_suchgebiet"]) ? htmlspecialchars($_POST["eb_suchgebiet"]) : 0;
		$kartenmaterial = isset($_POST["kartenmaterial"]) ? htmlspecialchars($_POST["kartenmaterial"]) : "etraxtopo";
		$eb_uebersicht = isset($_POST["eb_uebersicht"]) ? htmlspecialchars($_POST["eb_uebersicht"]) : 0;
	break;
	default:
		$eb_deckblatt = 1;
		$eb_inhaltsverzeichnis = 1;
		$eb_einsatzbericht = 1;
		$eb_vermisst = 1;
		$eb_organisationen = 1;
		$eb_checkliste = 1;
		$eb_personen = 1;
		$eb_personen_kurz = 1;
		$eb_gruppen = 1;
		$eb_ereignis = 1;
		$eb_funk = 1;
		$eb_suchgebiet = 1;
		$kartenmaterial = "etraxtopo";
		$eb_uebersicht = 1;
	break;
}

//Einsatzberichte laden
//$sql_einsatzbericht = $db->prepare("SELECT OID,Ogleich,Ozeichnen,Ozuweisen,Osehen,data,gesucht,einsatzbericht,orginfo,anfang,ende,suchtyp FROM settings WHERE EID = ".$EID."");
$sql_einsatzbericht = $db->prepare("SELECT data,gesucht,einsatzbericht,orginfo,typ FROM settings WHERE EID = ".$EID."");
	$sql_einsatzbericht->execute($sql_einsatzbericht->errorInfo());
	while ($sqleinsatzbericht = $sql_einsatzbericht->fetch(PDO::FETCH_ASSOC)){
		$einsatztyp = $sqleinsatzbericht['typ'] == "uebung" ? "Übung" : "Einsatz";
		$einsatzbericht_json = json_decode(substr(string_decrypt($sqleinsatzbericht['einsatzbericht']), 1, -1));
		$gesucht_json = json_decode(substr(string_decrypt($sqleinsatzbericht['gesucht']), 1, -1));
		$settings_data_json = json_decode(substr(string_decrypt($sqleinsatzbericht['data']), 1, -1));
		$einsatz_anfang = $settings_data_json->anfang;
		$einsatz_ende = $settings_data_json->ende;
		if(isset($settings_data_json->suchtyp) && $settings_data_json->suchtyp != ""){
			$searchtyp = $db->prepare("SELECT name FROM suchprofile WHERE cid = '".$settings_data_json->suchtyp."'");
			$searchtyp->execute($searchtyp->errorInfo());
			$suchtyp = $searchtyp->fetch(PDO::FETCH_ASSOC);
			$suchtyp = $suchtyp["name"];
		} else {
			$suchtyp = "";
		}
		$einsatz_suchtyp = $settings_data_json->suchtyp;
		$track_Einsatz_anfang = isset($settings_data_json->trackstart) ? $settings_data_json->trackstart : 0;
		$track_maxspeed = isset($settings_data_json->maxspeed) ? $settings_data_json->maxspeed : 0;
		$track_minspeed = isset($settings_data_json->minspeed) ? $settings_data_json->minspeed : 0;
		$track_trackpause = isset($settings_data_json->trackpause) ? $settings_data_json->trackpause : 0;
		/*$OIDprim = $sqleinsatzbericht['OID'];
		$OID = $sqleinsatzbericht['OID'];
		$Ogleich = $sqleinsatzbericht['Ogleich'];
		$Ozeichnen = $sqleinsatzbericht['Ozeichnen'];
		$Ozuweisen = $sqleinsatzbericht['Ozuweisen'];
		$Osehen = $sqleinsatzbericht['Osehen'];
		*/
		$OIDprim = $settings_data_json->OID;
		$OID = $settings_data_json->OID;
		$Ogleich = $settings_data_json->Ogleich;
		$Ozeichnen = $settings_data_json->Ozeichnen;
		$Ozuweisen = $settings_data_json->Ozuweisen;
		$Osehen = $settings_data_json->Osehen;
		//$orginfo_json = json_decode(substr(string_decrypt($sqleinsatzbericht['orginfo']), 1, -1));
		$orginfo_json = json_decode(string_decrypt($sqleinsatzbericht['orginfo']),true);
	}
	$eb_prim = $eb_org = "";
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
	$oids_el = $oid_el = $elids = $elid = "";
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
	$oids_el = implode(",",$oids_t).",".$oids_el; //$oids_el ist die vollständige Liste aller am Einsatz beteiligten Organisation (in eTrax und temporäre für den Einsatz
	$oids_el = array_unique(array_filter(explode(",",$oids_el)));
	$elids = array_unique(array_filter(explode(",",$elids)));
	
// Include the main TCPDF library (search for installation path).
//require_once('../vendor/tcpdf/tcpdf_include.php');
require_once('../vendor/tcpdf/tcpdf.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {


    //Page header
    public function Header() {
		if ($this->page == 1) {
			$this->SetY(10);
			// Set font
			$this->SetFont('merriweathersanslight', '', 8);
			// Title
			$this->Cell(0, 15, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
		} else {
			$this->SetY(10);
			// Set font
			$this->SetFont('merriweathersanslight', '', 8);
			// Title
			//$this->Cell(0, 15, 'Einsatzbericht '.$_SESSION["etrax"]["Einsatzname"], 0, false, 'R', 0, '', 0, false, 'M', 'M');
			$this->Cell(0, 15, $this->einsatztyp.' '.$_SESSION["etrax"]["Einsatzname"], 0, false, 'R', 0, '', 0, false, 'M', 'M');
			
		}
    }

    // Page footer
    public function Footer() {
		if ($this->page == 1) {	
			// Position at 15 mm from bottom
			$this->SetY(-15);
			$image_file = 'images/etrax.png';
			$this->Image($image_file, 10, 280, 15, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
			// Set font
			$this->SetFont('merriweathersanslight', '', 8);
			// Page number
			$this->Cell(30, 5, 'Erstellt mit eTrax | rescue', 0, false, 'L', 0, '', 0, false, 'T', 'M');
		} else {
			// Position at 15 mm from bottom
			$this->SetY(-15);
			// Set font
			$this->SetFont('merriweathersanslight', '', 8);
			// Page number
			$this->Cell(0, 10, 'Seite '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		}
    }
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

if($einsatztyp == "Übung"){
	$pdf->einsatztyp = "Übungsbericht";
} else {
	$pdf->einsatztyp = "Einsatzbericht";
}

// set document information
$pdf->SetCreator('Created with eTrax | rescue');
$pdf->SetAuthor($org_arr[$_SESSION["etrax"]["adminOID"]]["kurzname"]);
if($einsatztyp == "Übung"){
	$pdf->SetTitle('Übungsbericht '.$_SESSION["etrax"]["Einsatzname"]);
} else {
	$pdf->SetTitle('Einsatzbericht '.$_SESSION["etrax"]["Einsatzname"]);
}
$pdf->SetSubject('');
$pdf->SetKeywords('');
$row = $row_c = $row_cc = "";

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
		text-align: center;
    }
	h3 {
        color: #455a64;
        font-family: oswaldlight;
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
	.userinfo {
		font-family: oswaldlight;
		font-size: 9pt;
	}
	.symbol {
		font-family: symbol;
		font-size: 12pt;
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

//  ***********************************************************
//  **            Deckblatt                  
//  ***********************************************************
if($eb_deckblatt == "1"){
	if($einsatztyp == "Übung") {
		$html = '<div class="h1_deckblatt">Übungsbericht</span><h2>'.$settings_data_json->einsatz.'</h2>';
	} else {
		$html = '<div class="h1_deckblatt">Einsatzbericht</span><h2>'.$settings_data_json->einsatz.'</h2>';
	}

	// Organisationslogo hinzufügen wenn vorhanden
	if(file_exists("../orglogos/".$_SESSION["etrax"]["adminOID"].".png")){ 
		$logo = '<h2><img src="../orglogos/'.$_SESSION["etrax"]["adminOID"].'.png" style="text-align:center;width:350px;height:350px;"></h2>';
	} else {
		$logo = '<h2><img src="../orglogos/orglogofehlt.png" style="text-align:center;width:350px;height:350px;"></h2>';
	}

	$html2 = '<h2 style="padding-top:100px;color:#111111;font-size:18px;">'.$org_arr[$_SESSION["etrax"]["adminOID"]]["bezeichnung"].'</h2><br><span style="text-align:center;"><b>Druckdatum: </b>'.date('d.m.Y - H:i').'</span><br><span style="text-align:center;"><b>Bericht gedruckt von: </b>'.$_SESSION["etrax"]["name"].'</span>';
	$html3 = '<span class="hinweis" style="text-align:center;">Die Inhalte dieses Dokuments sind vertraulich und dürfen nur von berechtigten Personen eingesehen und in Rücksprache mit der erstellenden Person bzw. Organisation weitergeleitet werden.</span>';


	// add a page
	$pdf->AddPage();
	$pdf->SetY(70);
	// output the HTML content
	$pdf->writeHTML($style.$html, true, false, true, false, '');
	$pdf->writeHTML($style.$logo, true, false, true, false, '');
	$pdf->SetY($pdf->getY()+5);
	$pdf->writeHTML($style.$html2, true, false, true, false, '');
	$y = $pdf->getY();
	$pdf->writeHTMLCell(105,'',55,$y+5,$style.$html3, 0,0,0,true, 'J', true);

	// reset pointer to the last page
	$pdf->lastPage();

}
//  ***********************************************************
//  **            Einsatzberichte                
//  ***********************************************************
if($eb_einsatzbericht == "1"){

	// add a page
	$pdf->AddPage();

	if($einsatztyp == "Übung") {
		$pdf->Bookmark('Übungsbericht', 0, 1, '', '', array(28,49,58));
	} else {	
		$pdf->Bookmark('Einsatzbericht', 0, 1, '', '', array(28,49,58));
	}
	$pdf->Bookmark('Bericht der Primärorganisation', 1, 1, '', '', array(28,49,58));
	
	if($einsatztyp == "Übung") {
		$html = '<h1>Übungsbericht</h1>';
	} else {
		$html = '<h1>Einsatzbericht</h1>';
	}
	// output the HTML content
	$pdf->writeHTML($style.$html, true, false, true, false, '');
	$pdf->SetY($pdf->getY()+10);
	
	$html = '<h3>Bericht der Primärorganisation</h3>';
	// output the HTML content
	$pdf->writeHTML($style.$html, true, false, true, false, '');
	
	$pdf->SetY($pdf->getY()-4);
	$pdf->writeHTML($style.'<span class = "hinweis">Verfasst durch '.$org_arr[$OIDprim]["bezeichnung"].'</span>', true, false, true, false, '');
	$pdf->SetY($pdf->getY()+5);
	$pdf->writeHTML($style.'<span class="blocksatz">'.str_replace(array("<h1>","</h1>"),array("<h4>","</h4>"),$eb_prim).'</span>', true, false, true, false, '');

	$pdf->SetY($pdf->getY()+25);
	
	$pdf->Bookmark('Bericht der eigenen Organisation', 1, 2, '', '', array(28,49,58));
	
	$html = '<h3>Bericht der eigenen Organisation</h3>
	';
	// output the HTML content
	$pdf->writeHTML($style.$html, true, false, true, false, '');
	$pdf->SetY($pdf->getY()+5);
	$pdf->writeHTML($style.'<span class="blocksatz">'.str_replace(array("<h1>","</h1>"),array("<h4>","</h4>"),$eb_org).'</span>', true, false, true, false, '');

}
//  ***********************************************************
//  **            Allgemeine Informationen                
//  ***********************************************************
if($eb_vermisst == "1"){

	// add a page
	$pdf->AddPage();

	$pdf->Bookmark('Allgemeine Informationen', 0, 2, '', '', array(28,49,58));
	
	$html = '<h1>Allgemeine Informationen</h1>';
	// output the HTML content
	$pdf->writeHTML($style.$html, true, false, true, false, '');
	$pdf->SetY($pdf->getY()+10);
	
	$y = $pdf->getY();
	$pdf->SetY($y);
	$x = $pdf->getX();
	$y < 125 ? $cell_width = (140-5-$x) : $cell_width = 0;
	$pdf->Bookmark('Ort und Datum', 1, 1, '', '', array(28,49,58));
	$html = '<h3>Ort und Datum</h3>';
	// output the HTML content
	//$pdf->writeHTMLCell($cell_width, 0, $x, $y, $style.$html, 0, 0, 0, true, 'J', true);
	if($einsatztyp == "Übung") {
		$html .= '<span class="bold">Übungsort: </span>'.$settings_data_json->einsatz.'
			<br><span class="bold">Übungsbeginn: </span>'.date('d.m.Y - H:i',strtotime($einsatz_anfang)).'
			<br><span class="bold">Übungsende: </span>';
	} else {
		$html .= '<span class="bold">Einsatzort: </span>'.$settings_data_json->einsatz.'
			<br><span class="bold">Einsatzbeginn: </span>'.date('d.m.Y - H:i',strtotime($einsatz_anfang)).'
			<br><span class="bold">Einsatzende: </span>';
	}
		if(strtotime($einsatz_ende)>0){$html .= date('d.m.Y - H:i',strtotime($einsatz_ende));}
	// output the HTML content
	
	//$pdf->writeHTML($style.$html, true, false, true, false, '');
	$pdf->writeHTMLCell($cell_width, 0, $x, $y, $style.$html, 0, 1, 0, true, 'J', true);
	
	$y = $pdf->getY()+5;
	$pdf->SetY($y);
	$x = $pdf->getX();
	$y < 125 ? $cell_width = (140-5-$x) : $cell_width = 0;
	$pdf->Bookmark('Angaben zur vermissten Person', 1, 1, '', '', array(28,49,58));
	$html = '<h3>Angaben zur vermissten Person</h3>';
	// output the HTML content
	//$pdf->writeHTML($style.$html, true, false, true, false, '');
	if(isset($gesucht_json->gesuchtgebdatum) && $gesucht_json->gesuchtgebdatum > 0){
		$gebdatum = date('d.m.Y',strtotime($gesucht_json->gesuchtgebdatum));
	} else { $gebdatum = "";}
	$html .= '<span class="bold">Name: </span>'.(isset($gesucht_json->gesuchtname) && !empty($gesucht_json->gesuchtname) ? $gesucht_json->gesuchtname : "" );
	$html .= '<br><span class="bold">Alter: </span>'.(isset($gesucht_json->gesuchtalter) && !empty($gesucht_json->gesuchtalter) ? $gesucht_json->gesuchtalter : "");
	$html .= '	<br><span class="bold">Geburtsdatum: </span>'.$gebdatum;
	$html .= '	<br><span class="bold">SV-Nr.: </span>'.(isset($gesucht_json->gesuchtsvnr) && !empty($gesucht_json->gesuchtsvnr) ? $gesucht_json->gesuchtsvnr : "");
	$html .= '	<br><span class="bold">Adresse: </span>'.(isset($gesucht_json->gesuchtadresse) && !empty($gesucht_json->gesuchtadresse) ? $gesucht_json->gesuchtadresse : "");
	$html .= '	<br><span class="bold">Telefon: </span>'.(isset($gesucht_json->gesuchttelefon) && !empty($gesucht_json->gesuchttelefon) ? $gesucht_json->gesuchttelefon : "");
	$html .= '	<br><span class="bold rot">Erkrankungen: </span><span class="blocksatz">'.(isset($gesucht_json->gesuchterkrankungen) && !empty($gesucht_json->gesuchterkrankungen) ? $gesucht_json->gesuchterkrankungen : "" ).'</span>"';
	$html .= '	<br><span class="bold">Beschreibung für Externe: </span><span class="blocksatz">'.(isset($gesucht_json->gesuchtbeschreibungextern) && !empty($gesucht_json->gesuchtbeschreibungextern) ? $gesucht_json->gesuchtbeschreibungextern : "" ).'</span>';
	$html .= '	<br><span class="bold">Beschreibung für Suchteams: </span><span class="blocksatz">'.(isset($gesucht_json->gesuchtbeschreibung) && !empty($gesucht_json->gesuchtbeschreibung) ? $gesucht_json->gesuchtbeschreibung : "").'</span>';
	$html .= '	<br><span class="bold rot">Beschreibung Intern: </span><span class="blocksatz">'.(isset($gesucht_json->gesuchtbeschreibungintern) && !empty($gesucht_json->gesuchtbeschreibungintern) ? $gesucht_json->gesuchtbeschreibungintern : "").'</span>';
	$html .= '	<br><span class="bold">Abgängig seit: </span>'.date('d.m.Y',strtotime(isset($gesucht_json->alarmiertvermisst) && !empty($gesucht_json->alarmiertvermisst) ? $gesucht_json->alarmiertvermisst : "")).' <span class="bold">Uhrzeit: </span>'.date('H:i',strtotime(isset($gesucht_json->alarmiertvermisst) ? $gesucht_json->alarmiertvermisst : ""));
	$html .= '	<br><span class="bold">Klassifizierung: </span>'.$suchtyp.'';
	// output the HTML content
	$y = $pdf->getY();
	//$imgdata = base64_decode($gesucht_json->gesuchtbild);
	// The '@' character is used to indicate that follows an image data stream and not an image file name
	//$pdf->Image('@'.$imgdata,140,50,50,70,'','','',false,300, '',false,false,0,'T',false,false);
	$filename = is_file( "../../../secure/data/".$EID."/gesucht_big.jpg") ? "../../../secure/data/".$EID."/gesucht_big.jpg" : "../img/no-pic.jpg";
	$pdf->Image($filename,140,50,50,70,'','','',false,300, '',false,false,0,'T',false,false);
	//$pdf->SetY($y + 80);
	//$pdf->writeHTMLCell(105,'',25,$y-80,$style.$html, 0,0,0,true, 'J', true);
	//$pdf->writeHTML($style.$html, true, false, true, false, '');
	$pdf->writeHTMLCell($cell_width, 0, $x, $y, $style.$html, 0, 1, 0, true, 'J', true);
	$y = $pdf->getY()+5;
	$pdf->SetY($y);
	$x = $pdf->getX();
	$y < 125 ? $cell_width = (140-5-$x) : $cell_width = 0;
	$pdf->Bookmark('Angaben zur Alarmierung', 1, 1, '', '', array(28,49,58));
	$html = '<h3>Angaben zur Alarmierung</h3>';
	// output the HTML content
	//$pdf->writeHTML($style.$html, true, false, true, false, '');
	$html .= '<span class="bold">Alarmierung durch: </span>'.(isset($gesucht_json->alarmiertname) && !empty($gesucht_json->alarmiertname) ? $gesucht_json->alarmiertname : "").'
		<br><span class="bold">Telefon: </span>'.(isset($gesucht_json->alarmierttelefon) && !empty($gesucht_json->alarmierttelefon) ? $gesucht_json->alarmierttelefon : "").'
		<br><span class="bold">Datum: </span>'.(date('d.m.Y',strtotime(isset($gesucht_json->alarmiertdatum) && !empty($gesucht_json->alarmiertdatum) ? $gesucht_json->alarmiertdatum : ""))).' <span class="bold">Uhrzeit: </span>'.(isset($gesucht_json->alarmiertzeit) && !empty($gesucht_json->alarmiertzeit) ? $gesucht_json->alarmiertzeit : "").'
		';
	// output the HTML content
	//$pdf->writeHTML($style.$html, true, false, true, false, '');
	$pdf->writeHTMLCell($cell_width, 0, $x, $y, $style.$html, 0, 1, 0, true, 'J', true);
	$y = $pdf->getY()+5;
	$pdf->SetY($y);
	$x = $pdf->getX();
	$y < 125 ? $cell_width = (140-5-$x) : $cell_width = 0;
	$pdf->Bookmark('Kontaktdaten der Angehörigen', 1, 1, '', '', array(28,49,58));
	$html = '<h3>Kontaktdaten der Angehörigen</h3>';
	// output the HTML content
	//$pdf->writeHTML($style.$html, true, false, true, false, '');
	$html .= '<span class="bold">Name: </span>'.(isset($gesucht_json->kontaktname) && !empty($gesucht_json->kontaktname) ? $gesucht_json->kontaktname : "").'
		<br><span class="bold">Adresse: </span>'.(isset($gesucht_json->kontaktadresse) && !empty($gesucht_json->kontaktadresse) ? $gesucht_json->kontaktadresse : "").'
		<br><span class="bold">Telefon: </span>'.(isset($gesucht_json->kontakttelefon) && !empty($gesucht_json->kontakttelefon) ? $gesucht_json->kontakttelefon : "").'
		';
	// output the HTML content
	//$pdf->writeHTML($style.$html, true, false, true, false, '');
	$pdf->writeHTMLCell($cell_width, 0, $x, $y, $style.$html, 0, 1, 0, true, 'J', true);
	
}

//  ***********************************************************
//  **            Beteiligte Organisationen                
//  ***********************************************************
if($eb_organisationen == "1"){

	// add a page
	$pdf->AddPage();

	$pdf->Bookmark('Beteiligte Organisationen', 0, 3, '', '', array(28,49,58));
	
	$html = '<h1>Beteiligte Organisationen</h1>';
	// output the HTML content
	$pdf->writeHTML($style.$html, true, false, true, false, '');
	$pdf->SetY($pdf->getY()+10);
	
	$einsatz_query = $db->prepare("SELECT OID,Ogleich,Ozeichnen,Ozuweisen,Osehen FROM settings WHERE EID = ".$EID."");
	$einsatz_query->execute($einsatz_query->errorInfo());
	$einsatz = $einsatz_query->fetch(PDO::FETCH_ASSOC);
	/*$OID = $einsatz['OID'];
	$Ogleich = $einsatz['Ogleich'];
	$Ozeichnen = $einsatz['Ozeichnen'];
	$Ozuweisen = $einsatz['Ozuweisen'];
	$Osehen = $einsatz['Osehen'];
	
	//Alle OIDs die irgendwie im Einsatz teilnehmen
	$oids_t = explode(",",$OID.",".$Ogleich.",".$Ozeichnen.",".$Ozuweisen.",".$Osehen);
	$oids_t = array_unique(array_filter($oids_t));
	$oids_t = array_diff($oids_t,["DEV"]);*/
	
	foreach($oids_el as $oid_t){ 
		if($oid_t != "DEV"){ //DEV wird nicht angezeigt
			if(substr($oid_t,0,4) == "Temp"){ //Temporäre Organisationen filtern
				$temp_org = true;
				$oid_bez = (isset($org2show[$oid_t]["orgname"]) && $org2show[$oid_t]["orgname"] != "") ? $org2show[$oid_t]["orgname"] : "";
				$oid_adresse = (isset($org2show[$oid_t]["orgadresse"]) && $org2show[$oid_t]["orgadresse"] != "") ? $org2show[$oid_t]["orgadresse"] : "";
			} else {
				$temp_org = false;
				$oid_bez = $org_arr[$oid_t]["bezeichnung"];
				$oid_adresse = $org_arr[$oid_t]["adresse"];
			}
			$verstecken = isset($org2show[$oid_t]["verstecken"]) ? $org2show[$oid_t]["verstecken"] : "false";
			if($verstecken == "false" || $verstecken == $_SESSION["etrax"]["OID"]){ // Versteckt Organisationseinträge, wenn diese entsprechend markiert sind.
				$ebeginndatum = (isset($org2show[$oid_t]["ebeginndatum"]) && $org2show[$oid_t]["ebeginndatum"] != "") ? date('d.m.Y',strtotime($org2show[$oid_t]["ebeginndatum"])) : "";
				$ebeginnzeit = (isset($org2show[$oid_t]["ebeginnzeit"]) && $org2show[$oid_t]["ebeginnzeit"] != "") ? $org2show[$oid_t]["ebeginnzeit"] : "";
				$eendedatum = (isset($org2show[$oid_t]["eendedatum"]) && $org2show[$oid_t]["eendedatum"] != "") ? date('d.m.Y',strtotime($org2show[$oid_t]["eendedatum"])) : "";
				$eendezeit = (isset($org2show[$oid_t]["eendezeit"]) && $org2show[$oid_t]["eendezeit"] != "") ? $org2show[$oid_t]["eendezeit"] : "";
				$el_temp = "";
				//Einsatzleiter
				foreach($elids as $elid_t){ 
					$oid_el_t = explode("_",$elid_t);
					if($oid_el_t[0] == $oid_t){
						$verstecken = isset($elid2show[$elid_t]["verstecken"]) ? $elid2show[$elid_t]["verstecken"] : "false";
						if($verstecken == "false" || $verstecken == $_SESSION["etrax"]["OID"]){ // Versteckt Administratoren, wenn diese entsprechend markiert sind.
							$el_name = isset($elid2show[$elid_t]["name"]) ? $elid2show[$elid_t]["name"] : "";
							$el_tel = isset($elid2show[$elid_t]["tel"]) ? $elid2show[$elid_t]["tel"] : "";
							$el_vondatum = (isset($elid2show[$elid_t]["vondatum"]) && $elid2show[$elid_t]["vondatum"] != "") ? date('d.m.Y',strtotime($elid2show[$elid_t]["vondatum"])) : "";
							$el_bisdatum = (isset($elid2show[$elid_t]["bisdatum"]) && $elid2show[$elid_t]["bisdatum"] != "") ? date('d.m.Y',strtotime($elid2show[$elid_t]["bisdatum"])) : "";
							$el_vonzeit = (isset($elid2show[$elid_t]["vonzeit"]) && $elid2show[$elid_t]["vonzeit"] != "") ? $elid2show[$elid_t]["vonzeit"] : "";
							$el_biszeit = (isset($elid2show[$elid_t]["biszeit"]) && $elid2show[$elid_t]["biszeit"] != "") ? $elid2show[$elid_t]["biszeit"] : "";
							
							$el_temp .= '<tr><td><span class="bold">'.$el_name.'</span> (Tel.: '.$el_tel.')</td></tr>';
							$el_temp .= '<tr><td><b>Zeitraum:</b> '.$el_vondatum.' - '.$el_vonzeit.' bis  '.$el_bisdatum.' - '.$el_biszeit.'</td></tr>';
						}
					}
				}
				
				$pdf->Bookmark($oid_bez, 1, 1, '', '', array(28,49,58));
				$html = '<h3>'.$oid_bez.'</h3>';
				$pdf->writeHTML($style.$html, true, false, true, false, '');
				$html = '<table width="100%">
							<tr>
								<td width="50%" >
									<table>
										<tr class="tableheader"><td>Anschrift Organisation</td></tr>
										<tr><td>'.$oid_adresse.'</td></tr>
										<tr class="tableheader"><td>Im Einsatz</td></tr>
										<tr><td>Von: '.$ebeginndatum.' - '.$ebeginnzeit.'</td></tr>
										<tr><td>Bis: '.$eendedatum.' - '.$eendezeit.'</td></tr>
									</table>
								</td>
								<td width="50%">
									<table>
									<tr class="tableheader"><td>Einsatzleiter/Kontaktperson</td></tr>'.$el_temp.'
									</table>
								</td>
							</tr>
						</table>';
				// output the HTML content
				$pdf->writeHTML($style.$html, true, false, true, false, '');
			}
		}
	}

	
}
//  ***********************************************************
//  **            Checkliste Einsatz                
//  ***********************************************************
if($eb_checkliste == "1"){

	// add a page
	$pdf->AddPage();

	$pdf->Bookmark('Checkliste Einsatz', 0, 4, '', '', array(28,49,58));
	
	$html = '<h1>Checkliste Einsatz</h1>';
	$pdf->writeHTML($style.$html, true, false, true, false, '');
	$html = "";
	$pdf->SetY($pdf->getY()+10);
	
	$sql_checkliste = $db->prepare("SELECT checkliste FROM settings WHERE EID = ".$EID."");
	$sql_checkliste->execute($sql_checkliste->errorInfo());
	while ($sqlcheckliste = $sql_checkliste->fetch(PDO::FETCH_ASSOC)){
		if(!empty($sqlcheckliste['checkliste'])){
			$checkliste_json = json_decode(substr(string_decrypt($sqlcheckliste['checkliste']), 1, -1));
		}
	}
	if(!empty($checkliste_json)){
		foreach($checkliste_json as $pkey => $pvalue) {
			if($pvalue == "true" || $pkey == "bo_100" || $pkey == "ea_100"){
				if($pkey == "bo_100" || $pkey == "ea_100"){
					if($pvalue != ""){
						$$pkey = '<img src="../img/cb_checked.png" alt="" width="14" height="14" border="0" /> '.$pvalue;
					} else {
						$$pkey = $pvalue;
					}
				} else {
					$$pkey = "../img/cb_checked.png";
				}
			}else{
				$$pkey = "../img/cb_unchecked.png";
			}
		}
	
		$html .= '<h5>Eintreffen am Berufungsort:</h5>
					<ul class="bo" style="list-style-type:none">
						<li><img src="'.$bo_1.'" alt="" width="14" height="14" border="0" /> Alarmierende Stelle Informiert</li>
						<li><img src="'.$bo_2.'" alt="" width="14" height="14" border="0" /> Eintreffende Kräfte erfassen</li>
						<li><img src="'.$bo_3.'" alt="" width="14" height="14" border="0" /> Ansprechpartner am Einsatzort kontaktiert</li>
						<li><img src="'.$bo_4.'" alt="" width="14" height="14" border="0" /> Primärorganisation hat behördlichen Einsatzleiter über die Grenzen eines Sucheinsatzes informiert</li>
						<li><img src="'.$bo_5.'" alt="" width="14" height="14" border="0" /> Daten zur vermissten Person erfasst</li>
						<li><img src="'.$bo_6.'" alt="" width="14" height="14" border="0" /> Wohnort bzw. Initial Planning Point (IPP) abgeklärt</li>
						<li><img src="'.$bo_7.'" alt="" width="14" height="14" border="0" /> Benötigte Ressourcen überpüft:</li>
						<ul>
							<li><img src="'.$bo_8.'" alt="" width="14" height="14" border="0" /> Einschätzung Vermisstenbild</li>
							<li><img src="'.$bo_9.'" alt="" width="14" height="14" border="0" /> Betreuung der Angehörigen geregelt</li>
							<li>Beurteilung des Geländes:</li>
							<ul>
								<li><img src="'.$bo_10.'" alt="" width="14" height="14" border="0" /> Verfügbare Ressourcen reichen aus</li>
								<li><img src="'.$bo_11.'" alt="" width="14" height="14" border="0" /> Feuerwehr benötigt</li>
								<li><img src="'.$bo_12.'" alt="" width="14" height="14" border="0" /> Bergrettung benötigt</li>
								<li><img src="'.$bo_13.'" alt="" width="14" height="14" border="0" /> Flugpolizei benötigt</li>
								<li><img src="'.$bo_14.'" alt="" width="14" height="14" border="0" /> (weitere) Suchhunde benötigt</li>
								<li>'.$bo_100.'</li>
							</ul>
						</ul>
					<li><img src="'.$bo_15.'" alt="" width="14" height="14" border="0" /> Abfrage Spitäler, Verkehrsbetriebe, Taxi- und Busunternehmer</li>
					</ul>
					<h5>Suchtaktik entwickeln</h5>
					<ul class="st" style="list-style-type:none">
						<li>Gibt es bekannte Ziele der vermissten Person?</li>
						<ul>
							<li><img src="'.$st_1.'" alt="" width="14" height="14" border="0" /> Nein <span class="symbol">&#222;</span> Konzentrische Kreise um IPP bzw. PLS</li>
							<li><img src="'.$st_2.'" alt="" width="14" height="14" border="0" /> Ja <span class="symbol">&#222;</span> Points and Lines Ansatz</li>
						</ul>
					</ul>
					<h5>Personenfund</h5>
					<ul class="pf" style="list-style-type:none">
						<li><img src="'.$pf_1.'" alt="" width="14" height="14" border="0" /> Identifiziert als die vermisste Person</li>
						<li><img src="'.$pf_2.'" alt="" width="14" height="14" border="0" /> Zustand abgeklärt</li>
						<ul>
							<li><img src="'.$pf_3.'" alt="" width="14" height="14" border="0" /> Transport durch Gruppe/Organisation möglich</li>
							<li><img src="'.$pf_4.'" alt="" width="14" height="14" border="0" /> Rettungsdienst benötigt</li>
							<li><img src="'.$pf_5.'" alt="" width="14" height="14" border="0" /> Polizei erforderlich</li>
							<li><img src="'.$pf_6.'" alt="" width="14" height="14" border="0" /> Feuerwehr erforderlich</li>
							<li><img src="'.$pf_7.'" alt="" width="14" height="14" border="0" /> Bergrettung erforderlich</li>
						</ul>
						<li><img src="'.$pf_8.'" alt="" width="14" height="14" border="0" /> Behördlichen Einsatzleiter bzw. Polizei informiert</li>
						<li><img src="'.$pf_9.'" alt="" width="14" height="14" border="0" /> Alarmierende informiert</li>
						<li><img src="'.$pf_10.'" alt="" width="14" height="14" border="0" /> Alle Gruppen über Einsatzende informiert</li>
					</ul>
					<h5>Einsatzabbruch</h5>
					<ul class="ea" style="list-style-type:none">
						<li>Grund für den Einsatzabbruch</li>
						<ul>
							<li><img src="'.$ea_1.'" alt="" width="14" height="14" border="0" /> Verfügbare Ressourcen erschöpft</li>
							<li><img src="'.$ea_2.'" alt="" width="14" height="14" border="0" /> Fehlende Anhaltspunkte</li>
							<li><img src="'.$ea_3.'" alt="" width="14" height="14" border="0" /> Anordnung durch behördlichen Einsatzleiter</li>
							<li><img src="'.$ea_4.'" alt="" width="14" height="14" border="0" /> Gefährdungslage</li>
							<ul>
								<li>'.$ea_100.'</li>
							</ul>
						</ul>
					</ul>
					<h5>Einsatzende</h5>
					<ul class="ee" style="list-style-type:none">
						<li><img src="'.$ee_1.'" alt="" width="14" height="14" border="0" /> Alle Einsatzteams zurückgekehrt</li>
						<li><img src="'.$ee_2.'" alt="" width="14" height="14" border="0" /> Abschlussbesprechung durchgeführt</li>
						<li><img src="'.$ee_3.'" alt="" width="14" height="14" border="0" /> Abmeldung aller Mitglieder vom Einsatz</li>
					</ul>';

	
	}

	// output the HTML content
	$pdf->writeHTML($style.$html, true, false, true, false, '');

}
//  ***********************************************************
//  **            Personen im Einsatz                
//  ***********************************************************
if($eb_personen == "1" || $eb_personen_kurz == "1"){

	// add a page
	$pdf->AddPage();

	if($einsatztyp == "Übung") {
		$pdf->Bookmark('An der Übung beteiligte Personen', 0, 5, '', '', array(28,49,58));
	} else {	
		$pdf->Bookmark('Personen im Einsatz', 0, 5, '', '', array(28,49,58));
	}
	
	$einsatz_query = $db->prepare("SELECT data, gruppen, einteilung, personen_im_einsatz, protokoll FROM settings WHERE EID = ".$EID."");
	$einsatz_query->execute($einsatz_query->errorInfo());
	while ($einsatz = $einsatz_query->fetch(PDO::FETCH_ASSOC)){
		$data_json = string_decrypt($einsatz['data']);
		$data = json_decode($data_json, true);
		$data_gruppen = string_decrypt($einsatz['gruppen']);
		$gruppen = json_decode($data_gruppen, true);
		$data_gruppeneinteilung = string_decrypt($einsatz['einteilung']);
		$einteilung = json_decode($data_gruppeneinteilung, true);
		$pie_json = string_decrypt($einsatz['personen_im_einsatz']);
		$pie = json_decode($pie_json, true);
		$protokoll_json = string_decrypt($einsatz['protokoll']);
		$protokoll = json_decode($protokoll_json, true);
	}
	$funker = "";
	if(!empty($pie)){
		foreach($pie as $member){
			foreach($member['data'] as $memberdata){
				foreach($memberdata as $key => $value){
					$name = $memberdata['name'];
					if($memberdata['typ'] == 'Funk'){
						$funker = $name;
					}
				}
			}
		}
	}
	if($einsatztyp == "Übung") {
		$html = '<h1>An der Übung beteiligte Personen</h1>';
	} else {
		$html = '<h1>Personen im Einsatz</h1>';
	}
	if($OID_admin == "DEV"){ $html .= '<br><span class="rot">Nur User des Developmentteams bekommen eine Liste aller Teilnehmer aller Organisationen angezeigt. Ansonsten nur die vollständige Liste der eigenen Organisation.</span>';}
	// output the HTML content
	$pdf->writeHTML($style.$html, true, false, true, false, '');
	$pdf->SetY($pdf->getY()+10);
	
	//Aufbau nach Einsatzorganisationen
	$pers_sum = $hf_sum = $h_sum = $sonstige_sum = $dauer_sum = 0;
	$row_cc = "";
	$html_sumtab = '<h3>Übersicht</h3><table width="100%"><tr><td class="bold" width="60%">Organisation:</td><td class="bold" width="8%">Summe</td><td class="bold" width="8%">HF</td><td class="bold" width="8%">H</td><td class="bold" width="8%">Sonstige</td><td class="bold" width="8%" align="right">Std.</td></tr>';
	foreach($oids_el as $oid_t){ 
		if($oid_t != "DEV"){ //DEV wird nicht angezeigt
			
			$typ_c = $tab_c = $pers_org = $typ_list = $row_c = "";
			$hf_sum_org = $h_sum_org = $sonstige_sum_org = $dauer_sum_org = $dauer_pers = 0;
				if(!empty($pie)){
					foreach($pie as $personen){
						foreach($personen['data'] as $persondata){
							if($persondata['OID'] == $oid_t){	
								if($persondata['typ'] != "material"){ //Filterung von der Gruppe zugewiesenem Material
									//foreach($persondata as $key => $value){
									
										//$person = "<b>".$persondata['typ']." ".$persondata['name']."</b> ".$persondata['dienstnummer'];
										$von = date('d.m.y H:i', intval($persondata['aktivierungszeit']));
										if($persondata['abgemeldet'] > 0){	
											$bis = date('d.m.y H:i', intval($persondata['abgemeldet']));
										} else {
											$bis = "--";
										}
									//}
									//Berechnung der Einsatzdauer
									$pause_temp = 0;
									if($persondata['aktivierungszeit'] > 0 && $persondata['abgemeldet'] > 0){
										if($persondata['inPause'] > 0 &&  $persondata['ausPause'] > 0) { //Es wurde eine Pause angelegt und beendet
											$pause_temp = $persondata['ausPause'] - $persondata['inPause'];
										} elseif($persondata['inPause'] > 0 &&  $persondata['ausPause'] == "") { //Es wurde eine Pause angelegt und beendet
											$pause_temp = time() - $persondata['inPause'];
										} else {
											$pause_temp = 0;
										}
										$dauer_pers = round($persondata['abgemeldet'] - $persondata['aktivierungszeit'] - $pause_temp);
										
									} elseif($persondata['aktivierungszeit'] > 0 && $persondata['abgemeldet'] == ""){
										if($persondata['inPause'] > 0 &&  $persondata['ausPause'] > 0) { //Es wurde eine Pause angelegt und beendet
											$pause_temp = $persondata['ausPause'] - $persondata['inPause'];
										} elseif($persondata['inPause'] > 0 &&  $persondata['ausPause'] == "") { //Es wurde eine Pause angelegt und beendet
											$pause_temp = time() - $persondata['inPause'];
										}else {
											$pause_temp = 0;
										}
										$dauer_pers = round(time() - $persondata['aktivierungszeit'] - $pause_temp);
									} else {
										$dauer_pers = 0;
									}
									$dauer_sum += $dauer_pers;
									$dauer_sum_org += $dauer_pers;
									
									$typ_c .= $persondata['typ'].",";
									$tab_c .= '<tr style="background-color:'.$row_c.';"><td>'.$persondata['name'].'</td><td>'.$persondata['dienstnummer'].'</td><td>'.$persondata['typ'].'</td><td style="font-size:.75em;">'.$von.' bis: '.$bis.'</td><td align="right">'.sprintf('%02d:%02d', ($dauer_pers/ 3600),($dauer_pers/ 60 % 60)).'</td></tr>';
									if($row_c == ""){ $row_c = "#DEDEDE"; } else {$row_c = "";}
									$pers_sum +=1;
									if($persondata['typ'] == "HF") {$hf_sum += 1;$hf_sum_org += 1;}
									elseif($persondata['typ'] == "H") {$h_sum += 1;$h_sum_org += 1;}
									else{$sonstige_sum += 1;$sonstige_sum_org += 1;}
									$dauer_pers = 0;
								}
							}
							
						}
					} 
				}
			//if($tab_c != ""){
			if(substr($oid_t,0,4) != "Temp"){ //Filtert die temporären Organisationen heraus
				$typ_c = explode(",",substr($typ_c,0,-1));
				$pers_org = count($typ_c);
				$typ_t = array_unique(array_filter($typ_c)); //In der Organisation vorkommende Typen
				$x = 0;
				$typ_x = "";
				foreach($typ_t as $typ_s){
					foreach($typ_c as $typ_x){ if($typ_x == $typ_s){$x +=1;}} //Summe der Personen von diesem Typ
					$typ_list .= $typ_s.': '.$x.' ';
					$x = 0;
				}
				$html = '<h3>'.(isset($org_arr[$oid_t]["bezeichnung"]) ? $org_arr[$oid_t]["bezeichnung"] : "Keine Bezeichnung").'</h3>
				<span class="bold">Summe: '.$pers_org.'</span>  - davon '.$typ_list.'<br>
				<table width="100%">
				<tr><td class="bold" width="35%">Name</td><td class="bold" width="15%">Dienstnr.</td><td class="bold" width="12%">Funktion</td><td class="bold" width="28%">Anwesenheit</td><td class="bold" width="10%" align="right">Dauer*</td></tr>
				'.$tab_c.'<tr><td style="font-size:.75em;" colspan="5">* Netto Einsatzdauer! Etwaige Pausenzeiten sind in Abzug gebracht.</td></tr></table>';
				if($OID_admin == "DEV" || $OID_admin == $oid_t){ //DEV bekommt alle Organisationen detailliert angezeigt. Alle anderen User nur die der eigenen Organisation 	
					// output the HTML content
					if($eb_personen == "1"){
						$pdf->writeHTML($style.$html, true, false, true, false, '');
					}
				}
			$html_sumtab .= '<tr style="background-color:'.$row_cc.';"><td>'.(isset($org_arr[$oid_t]["bezeichnung"]) ? $org_arr[$oid_t]["bezeichnung"] : "Keine Bezeichnung").'</td><td>'.$pers_org.'</td><td>'.$hf_sum_org.'</td><td>'.$h_sum_org.'</td><td>'.$sonstige_sum_org.'</td><td align="right">'.sprintf('%02d', ($dauer_sum_org/ 3600)).'</td></tr>';
			if($row_cc == ""){ $row_cc = "#DEDEDE"; } else {$row_cc = "";}
			//}
			}
		}
	}
	

	
	if($eb_personen_kurz == "1"){	
		$html_sumtab .= '<tr class="bold"><td>Summe:</td><td>'.$pers_sum.'</td><td>'.$hf_sum.'</td><td>'.$h_sum.'</td><td>'.$sonstige_sum.'</td><td align="right">'.sprintf('%02d', ($dauer_sum/ 3600)).'</td></tr>';
		// output the HTML content
		$pdf->writeHTML($style.$html_sumtab, true, false, true, false, '');
		
	}
}
//  ***********************************************************
//  **            Gruppeneinteilung                
//  ***********************************************************
if($eb_gruppen == "1"){

	// add a page
	$pdf->AddPage();

	$pdf->Bookmark('Gruppeneinteilung', 0, 6, '', '', array(28,49,58));
	
	$html = '<h1>Gruppeneinteilung</h1>';
	if($OID_admin == "DEV"){ $html .= '<br><span class="rot">Nur User des Developmentteams bekommen die Namen aller Gruppenmitglieder angezeigt. Reguläre Nutzer sehen nur die Namen der eigenen Organisation und die Organisationsbezeichnung der anderen Organisationen.</span>';}
	// output the HTML content
	$pdf->writeHTML($style.$html, true, false, true, false, '');
	$pdf->SetY($pdf->getY()+10);
	
	//Gruppen darstellen
	$einsatz_query = $db->prepare("SELECT data, gruppen, einteilung, personen_im_einsatz, protokoll FROM settings WHERE EID = ".$EID."");
	$einsatz_query->execute($einsatz_query->errorInfo());
	while ($einsatz = $einsatz_query->fetch(PDO::FETCH_ASSOC)){
		$data_json = string_decrypt($einsatz['data']);
		$data = json_decode($data_json, true);
		$data_gruppen = string_decrypt($einsatz['gruppen']);
		$gruppen = json_decode($data_gruppen, true);
		$data_gruppeneinteilung = string_decrypt($einsatz['einteilung']);
		$einteilung = json_decode($data_gruppeneinteilung, true);
		$pie_json = string_decrypt($einsatz['personen_im_einsatz']);
		$pie = json_decode($pie_json, true);
		$protokoll_json = string_decrypt($einsatz['protokoll']);
		$protokoll = json_decode($protokoll_json, true);
	}
	
	$nr = 1;
	$zeit = $gruppendata = $gruppen_html = $material_html = $status_html = $html = $status_t = $st_t = $material_t = $row_cc = $row_ccm = $userinfo = "";
	if($gruppen != ""){
		foreach($gruppen as $gruppe){
			$material_html = $row_cc = $row_ccm = "";
			foreach($gruppe['data'] as $gruppendata){
				if(!empty($gruppendata['zugewiesen'])){ //Abfangen einer neuen Gruppe ohne Mitglieder
				$row_cc = $row_ccm = "";
					foreach($gruppendata['zugewiesen'] as $zugewiesen){	
						$commander = ($gruppendata['commander'] == $zugewiesen["uid"]) ? 'GK' : '';
						if($zugewiesen["oid"] == $OID_admin || $OID_admin == "DEV"){ //Mitglieder eigener Organisationen
							$userinfo = (isset($zugewiesen["info"]) && $zugewiesen["info"] != "") ? " <small>".$zugewiesen["info"]."</small>" : " <small></small>";
							if($zugewiesen["typ"] != "material"){ //Filterung von der Gruppe zugewiesenem Material
								$gruppen_html .= '<tr style="background-color:'.$row_cc.';"><td style="font-weight:bold">'.$zugewiesen["name"].$userinfo.'</td><td> '.$commander.'</td><td> '.$zugewiesen["dienstnummer"].'</td><td>'.$zugewiesen["typ"].'</td></tr>';
							} else {
								$material_t = explode(" ",$zugewiesen["name"],2);
								$material_t = isset($material_t[1]) ? $material_t[1] : "";
								$material_html .= '<tr style="background-color:'.$row_ccm.';"><td>'.$material_t.$userinfo.'</td><td>'.$zugewiesen["dienstnummer"].'</td></tr>';
							}
						}else{ //Mitglieder anderer Organisationen
							if($zugewiesen["typ"] != "material"){ //Filterung von der Gruppe zugewiesenem Material
								$gruppen_html .= '<tr style="background-color:'.$row_cc.';"><td>'.$org_arr[$zugewiesen["oid"]]["kurzname"].'</td><td>'.$commander.'</td><td>'.$zugewiesen["dienstnummer"].'</td><td>'.$zugewiesen["typ"].'</td></tr>';
							} 
						}
						$row_cc = $row_cc == "" ? "#DEDEDE" : "";
						$row_ccm = $row_ccm == "" ? "#DEDEDE" : "";
						$material_t = "";
					}
				} else {
					$gruppen_html .= '<tr style="background-color:'.$row_cc.';"><td colspan="3">Dieser Gruppe wurden keine Mitglieder zugewiesen.</td></tr>';
				}
				$row_cc = "";
				if(!empty($gruppendata['status'])){ //Abfangen Fehlende Stati
					$status_t = str_replace("&amp;","&",$gruppendata['status']); //Rückübersetzen von htmlentities für &, falls vorhanden.
					if(!is_array($status_t)){
						$status_t = explode(";", $status_t);
						foreach($status_t as $st){
							$st_t = explode("&&", $st);
							$status_html .= '<tr style="background-color:'.$row_cc.';"><td>'.(isset($st_t[0]) ? $st_t[0] : "").'</td><td>'.(isset($st_t[1]) ? $st_t[1] : "").'</td></tr>';
							if($row_cc == ""){ $row_cc = "#DEDEDE"; } else {$row_cc = "";}
						}
					} else {
						$status_html .= '<tr style="background-color:'.$row_cc.';"><td colspan="2">Statusmeldung fehlerhaft.</td></tr>';
					}
				} else {
					$status_html .= '<tr style="background-color:'.$row_cc.';"><td colspan="2">Es wurden keine Statusmeldungen übermittelt.</td></tr>';
				}
			}
				//echo '<tr><td style="border:1px solid #000;">'.$row.'</td><td style="border:1px solid #000;">'.$zeit.'</td><td style="border:1px solid #000;">'.$gruppeinfo.'</td><td style="border:1px solid #000;">'.$person.'</td></tr>';
				
		if($material_html != "") {
			$rowspan = 'rowspan="3"';
			$material_html_insert = '<tr><td width="55%" style="padding-top:10px;"><h5>Material</h5></td></tr><tr><td width="55%"><table width="100%"><tr class="bold"><td>Bezeichnung</td><td>Kennung</td></tr>'.$material_html.'</table></td></tr>';
		} else { $rowspan = ""; $material_html_insert = "";}
		$html .= '<h4>'.$gruppendata['name'].'</h4><table width="100%"><tr><td width="55%"><h5>Gruppenmitglieder</h5></td><td width="5%"></td><td width="40%"><h5>Übersicht Stati</h5></td></tr><tr><td width="55%"><table width="100%"><tr class="bold"><td width="60%">Name</td><td width="8%"> </td><td width="17%">Dienstnr.</td><td width="15%">Funktion</td></tr>'.$gruppen_html.'</table></td><td '.$rowspan.' width="5%"></td><td '.$rowspan.' width="40%"><table width="100%"><tr class="bold"><td>Status</td><td>Timestamp</td></tr>'.$status_html.'</table></td></tr>'.$material_html_insert.'<tr><td></td><td></td><td></td></tr></table>';
		$gruppen_html = $status_html = $row_cc = "";
		} 
	}
	// output the HTML content
	$pdf->writeHTML($style.$html, true, false, true, false, '');

}
//  ***********************************************************
//  **            Ereignisprotokoll                
//  ***********************************************************
if($eb_ereignis == "1"){
	$row_cc = "";
	// add a page
	$pdf->AddPage();

	$pdf->Bookmark('Ereignisprotokoll', 0, 7, '', '', array(28,49,58));
	
	$html = '<h1>Ereignisprotokoll</h1>';
	$oids_t = explode(",",$OID.",".$Ogleich.",".$Ozeichnen.",".$Ozuweisen.",".$Osehen);
	$oids_t = array_unique(array_filter($oids_t));
	if($OID_admin == "DEV"){
		$html .= '<br><span class="rot">Nur User des Developmentteams bekommen das gesamte Einsatzprotokoll angezeigt. Reguläre Nutzer nur die Einträge der eigenen Organisation.</span>';
	} elseif($OID_admin != "DEV" && count($oids_t) > 2){
		$html .= '<br>Es werden ausschließlich die Einträge von <span class="bold">'.$org_arr[$OID_admin]["bezeichnung"].'</span> angezeigt.';
	}
	// output the HTML content
	$pdf->writeHTML($style.$html, true, false, true, false, '');
	$pdf->SetY($pdf->getY()+10);
	
	//Ereignisprotokoll aufbauen
	$einsatz_query = $db->prepare("SELECT protokoll FROM settings WHERE EID = ".$EID."");
	$einsatz_query->execute($einsatz_query->errorInfo());
	while ($einsatz = $einsatz_query->fetch(PDO::FETCH_ASSOC)){
		//$data_json = string_decrypt($einsatz['data']);
		//$data = json_decode($data_json, true);
		$protokoll_json = string_decrypt($einsatz['protokoll']);
		$protokoll = json_decode($protokoll_json, true);
	}
	$protokoll_arr[] = array();
	if($protokoll != ""){
		foreach($protokoll as $message){
			foreach($message['data'] as $messagedata){
				$protokoll_arr[] = array('oid' => (isset($messagedata["oid"])) ? $messagedata["oid"] : "",
									'type' => (isset($messagedata["type"])) ? $messagedata["type"] : "",
									'phone' => (isset($messagedata["phone"])) ? $messagedata["phone"] : "",
									'bos' => (isset($messagedata["bos"])) ? $messagedata["bos"] : "",
									'name' => (isset($messagedata["name"])) ? $messagedata["name"] : "",
									'read' => (isset($messagedata["read"])) ? $messagedata["read"] : "",
									'betreff' => (isset($messagedata["betreff"])) ? $messagedata["betreff"] : "",
									'text' => (isset($messagedata["text"])) ? $messagedata["text"] : "",
									'zeit' => (isset($messagedata["zeit"])) ? $messagedata["zeit"] : "",
									'deleted' => (isset($messagedata["deleted"])) ? $messagedata["deleted"] : "",
									'funkmittel' => (isset($messagedata["funkmittel"])) ? $messagedata["funkmittel"] : "");
									
			}
		}
	
		//Einträge des Ereignisprotokolls nach Timestamp sortieren
		$date_r = array();
		foreach ($protokoll_arr as $nr => $inhalt)
		{
			$date_r[$nr]  = strtolower( isset($inhalt["zeit"]) ? $inhalt["zeit"] : 0);
		}
		//Sortieren
		array_multisort($date_r, SORT_ASC, $protokoll_arr);

		
		$nr = 1;
		$zeit = $gruppendata = $html = $del_class = $uhrzeit = $tag = $date = $ausfunk = "";
		foreach($protokoll_arr as $messagedata){
			//foreach($message['data'] as $messagedata){
				if(((isset($messagedata["oid"]) && $messagedata["oid"] == $OID_admin) || $OID_admin == "DEV") && (isset($messagedata["type"]) && ($messagedata["type"] == "protokoll" || $messagedata["type"] == "funk,protokoll"))){
					if($messagedata["deleted"] == "deleted"){ $del_class = "text-decoration:line-through;"; } else {$del_class = "";} 
					$date = date_create_from_format('Y-m-d H:i:s', $messagedata["zeit"]);
					$uhrzeit = date_format($date, 'H:i:s');
					if($tag != date_format($date, 'd.m.Y')){
						$html .= '<tr><td class="h5" colspan="3">'.date_format($date, 'd.m.Y').'</td></tr><tr class="bold"><td width="5%">#</td><td width="10%">Uhrzeit</td><td width="85%">Ereignis</td></tr>';
					}
					$tag = date_format($date, 'd.m.Y');
					$ausfunk = ($messagedata["type"] == "funk,protokoll") ? "[FUNK] " : "";
					$html .= '<tr style="background-color:'.$row_cc.';"><td>'.$nr.'</td><td>'.$uhrzeit.'</td><td style="'.$del_class.'">'.$ausfunk.$messagedata["text"].'</td></tr>';
					
					if($row_cc == ""){ $row_cc = "#DEDEDE"; } else {$row_cc = "";}
					$nr++;
				}
				
			//}
				//echo '<tr><td style="border:1px solid #000;">'.$row.'</td><td style="border:1px solid #000;">'.$zeit.'</td><td style="border:1px solid #000;">'.$gruppeinfo.'</td><td style="border:1px solid #000;">'.$person.'</td></tr>';
		} 

		$html = '<table width="100%">'.$html.'</table>';
		
		// output the HTML content
		$pdf->writeHTML($style.$html, true, false, true, false, '');
	}
}
//  ***********************************************************
//  **            Funkprotokoll                
//  ***********************************************************
if($eb_funk == "1"){
	$row_cc = "";
	// add a page
	$pdf->AddPage();

	$pdf->Bookmark('Funkprotokoll', 0, 8, '', '', array(28,49,58));
	
	$html = '<h1>Funkprotokoll</h1>';
	$oids_t = explode(",",$OID.",".$Ogleich.",".$Ozeichnen.",".$Ozuweisen.",".$Osehen);
	$oids_t = array_unique(array_filter($oids_t));
	if($OID_admin == "DEV"){
		$html .= '<br><span class="rot">Nur User des Developmentteams bekommen das gesamte Funkprotokoll angezeigt. Reguläre Nutzer nur die Einträge der eigenen Organisation.</span>';
	} elseif($OID_admin != "DEV" && count($oids_t) > 2){
		$html .= '<br>Es werden ausschließlich die Einträge von <span class="bold">'.$org_arr[$OID_admin]["bezeichnung"].'</span> angezeigt.';
	}
	
	// output the HTML content
	$pdf->writeHTML($style.$html, true, false, true, false, '');
	$pdf->SetY($pdf->getY()+10);
	
	//Funkprotokoll aufbauen
	$einsatz_query = $db->prepare("SELECT protokoll FROM settings WHERE EID = ".$EID."");
	$einsatz_query->execute($einsatz_query->errorInfo());
	while ($einsatz = $einsatz_query->fetch(PDO::FETCH_ASSOC)){
		//$data_json = string_decrypt($einsatz['data']);
		//$data = json_decode($data_json, true);
		$protokoll_json = string_decrypt($einsatz['protokoll']);
		$protokoll = json_decode($protokoll_json, true);
		
	}
	$fprotokoll_arr[] = array();
	if($protokoll != ""){
		foreach($protokoll as $message){
			foreach($message['data'] as $messagedata){
				$fprotokoll_arr[] = array('oid' => (isset($messagedata["oid"])) ? $messagedata["oid"] : "",
									'type' => (isset($messagedata["type"])) ? $messagedata["type"] : "",
									'phone' => (isset($messagedata["phone"])) ? $messagedata["phone"] : "",
									'bos' => (isset($messagedata["bos"])) ? $messagedata["bos"] : "",
									'name' => (isset($messagedata["name"])) ? $messagedata["name"] : "",
									'read' => (isset($messagedata["read"])) ? $messagedata["read"] : "",
									'betreff' => (isset($messagedata["betreff"])) ? $messagedata["betreff"] : "",
									'text' => (isset($messagedata["text"])) ? $messagedata["text"] : "",
									'zeit' => (isset($messagedata["zeit"])) ? $messagedata["zeit"] : "",
									'deleted' => (isset($messagedata["deleted"])) ? $messagedata["deleted"] : "",
									'funkmittel' => (isset($messagedata["funkmittel"])) ? $messagedata["funkmittel"] : "");
									
			}
		}
		//Einträge des Funkprotokolls nach Timestamp sortieren
		$date_r = array();
		foreach ($fprotokoll_arr as $nr => $inhalt)
		{
			$date_r[$nr]  = isset($inhalt["zeit"]) ? strtotime($inhalt["zeit"]) : 0;
		}
		//Sortieren
		array_multisort($date_r, SORT_ASC, $fprotokoll_arr);

		
		$nr = 1;
		$zeit = $gruppendata = $html = $del_class = $uhrzeit = $tag = $date = $funkmittel = "";
		foreach($fprotokoll_arr as $messagedata){
			//foreach($message['data'] as $messagedata){
				if(((isset($messagedata["oid"]) && $messagedata["oid"] == $OID_admin) || $OID_admin == "DEV") && (isset($messagedata["type"]) && ($messagedata["type"] == "funk" || $messagedata["type"] == "funk,protokoll"))){
					if($messagedata["deleted"] == "deleted"){ $del_class = "text-decoration:line-through;"; } else {$del_class = "";} 
					if($messagedata["funkmittel"] != ""){ $funkmittel = $messagedata["funkmittel"]; } else {$funkmittel = "";} 
					$date = date_create_from_format('Y-m-d H:i:s', $messagedata["zeit"]);
					
					$uhrzeit = date_format($date, 'H:i:s');
					if($tag != date_format($date, 'd.m.Y')){
						$html .= '<tr><td class="h5" colspan="3">'.date_format($date, 'd.m.Y').'</td></tr><tr class="bold"><td width="5%">#</td><td width="10%">Uhrzeit</td><td width="15%">Funkmittel</td><td width="70%">Ereignis</td></tr>';
					}
					$tag = date_format($date, 'd.m.Y');
					$html .= '<tr style="background-color:'.$row_cc.';"><td>'.$nr.'</td><td>'.$uhrzeit.'</td><td style="'.$del_class.'">'.$funkmittel.'</td><td style="'.$del_class.'">'.$messagedata["text"].'</td></tr>';
					
					if($row_cc == ""){ $row_cc = "#DEDEDE"; } else {$row_cc = "";}
					$nr++;
				}
				
			//}
				//echo '<tr><td style="border:1px solid #000;">'.$row.'</td><td style="border:1px solid #000;">'.$zeit.'</td><td style="border:1px solid #000;">'.$gruppeinfo.'</td><td style="border:1px solid #000;">'.$person.'</td></tr>';
		} 

		$html = '<table width="100%">'.$html.'</table>';
		
		// output the HTML content
		$pdf->writeHTML($style.$html, true, false, true, false, '');

	}
}	

//  ***********************************************************
//  **            Suchgebiete                
//  ***********************************************************

$pdf_seite_suchgebiet = $pdf->PageNo()+1;
$suchgebiet_table = "";
$flaeche_summe = $laenge_summe = 0;
if($eb_suchgebiet == "1"){

	//Das Übersichtsblatt wird am Ende erstellt
	
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
	/*$i = 0;
	$suchgebiete_arr[] = array();
	if(!empty($suchgebiete["features"])){
		foreach($suchgebiete["features"] as $gebiet){
			//foreach($sgb['features'] as $gebiet){
				$suchgebiete_arr[] = array('name' => $gebiet['properties']["name"],
									'typ' => $gebiet['properties']["typ"],
									'id' => $gebiet['properties']["id"],
									'beschreibung' => $gebiet['properties']["beschreibung"],
									'gruppe' => $gebiet['properties']["gruppe"],
									'oid' => $gebiet['properties']["OID"],
									'strokecolor' => $gebiet['properties']["strokecolor"],
									'strokewidth' => $gebiet['properties']["strokewidth"],
									'lineDash' => $gebiet['properties']["lineDash"],
									'fillcolor' => $gebiet['properties']["fillcolor"],
									'status' => $gebiet['properties']["status"],
									'masse' => $gebiet['properties']["masse"],
									'img' => $gebiet['properties']["img"],
									'type' => $gebiet['geometry']["type"],
									'coordinates' => $gebiet['geometry']["coordinates"]);
									
									
			//}
				
				
				$i++;
		}
	}*/
	
	//Alle Suchgebiete für Darstellung
	if(!empty($suchgebiete["features"])){
		foreach($suchgebiete["features"] as $gebiet){
			$nr_i = $nr_i_new = 0;
				$suchgebiet_all = array();
				foreach($suchgebiete["features"] as $gebiet2){
					
						$i = 0;
						$x_temp_i = $y_temp_i = 0;
						if(isset($suchgebiete['features'][$nr_i_new]['properties']['typ'])) { //area51 ist das initiale Suchgebiet, dass für ein funktionierendes JSON erforderlich ist.
							if($suchgebiete['features'][$nr_i_new]['properties']['typ'] != "Punktsuche" && $suchgebiete['features'][$nr_i_new]['properties']['typ'] != "EL") {
								foreach($suchgebiete['features'][$nr_i_new]['geometry']['coordinates'][0] as $xy){
									if($x_temp_i != $suchgebiete['features'][$nr_i_new]['geometry']['coordinates'][0][$i][0] && $y_temp_i != $suchgebiete['features'][$nr_i_new]['geometry']['coordinates'][0][$i][1]){ //entfernt Mehrfacheinträge
										//Aufbauen des Suchgebiets
										$suchgebiet_all[$nr_i_new][] = array('x' => $suchgebiete['features'][$nr_i_new]['geometry']['coordinates'][0][$i][0],
															  'y' => $suchgebiete['features'][$nr_i_new]['geometry']['coordinates'][0][$i][1],
															  'typ' => $suchgebiete['features'][$nr_i_new]['properties']['typ']);
									}
									$x_temp_i = $suchgebiete['features'][$nr_i_new]['geometry']['coordinates'][0][$i][0];
									$y_temp_i = $suchgebiete['features'][$nr_i_new]['geometry']['coordinates'][0][$i][1];
									$i++;
								}
							} elseif($suchgebiete['features'][$nr_i]['properties']['typ'] == "Punktsuche" || $suchgebiete['features'][$nr_i]['properties']['typ'] == "EL") { //Typ EL ist das initiale Suchgebiet, welches aber nicht dargestellt wird
							//Es wird ein "Kreis" um den ursprünglichen Mittelpunkt gezeichnet
								for($r = 0; $r <= 24; $r++){
									$suchgebiet_all[$nr_i_new][] = array('x' => ($suchgebiete['features'][$nr_i_new]['geometry']['coordinates'][0] + (0.0005*cos($r*15*pi()/180))*((cos($suchgebiete['features'][$nr_i_new]['geometry']['coordinates'][0]*pi()/180)))),
										  'y' => ($suchgebiete['features'][$nr_i_new]['geometry']['coordinates'][1] + (0.0005*sin($r*15*pi()/180))*(1-(sin($suchgebiete['features'][$nr_i_new]['geometry']['coordinates'][1]*pi()/180)*0.5))),
										  'typ' => $suchgebiete['features'][$nr_i_new]['properties']['typ']);
								}
										  
							}
						}
						$nr_i_new++;
					
					$nr_i++;
					
				}
			
		}
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
	
	
	//Voreinstellungen zum Karten zeichnen
	$papier_w = 210; //Papierbreite in mm
	$papier_h = 297; //Papierbreite in mm
	$abstand_oben = 50; //Oberer Bildabstand in mm - wenn nichts eingefüllt wird, wird das Bild zentriert
	$dpi = 120; //angestrebte dots per inch (~ 96 ist die Pixeldichte bei einem Monitor)
	$px_w = round($papier_w/25.4*$dpi);	
	$px_h = round($papier_h/25.4*$dpi);
	$xTiles_add = 2;
	$yTiles_add = 2;
	//Kartenmaterial aus POST
	$map = $kartenmaterial;

	
	//Karten definieren - per require aus include/include.php geholt
	
	$xKacheln = 3; // Anzahl der horizontalen Kacheln
	$yKacheln = 3; // Anzahl der vertikalen Kacheln
	
	//Styles fürs Zeichnen
	$style_suchgebiet = array('width' => 1.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 100, 60));
	$style_suchgebiet_alle = array('width' => .75, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(66, 135, 245));
	$style_wegsuche_alle = array('width' => .75, 'cap' => 'butt', 'join' => 'miter', 'dash' => '4,2', 'color' => array(66, 135, 245));
	$style_grid_hauptintervall = array('width' => .5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(10, 10, 10));
	$style_grid_hilfsintervall = array('width' => .15, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(10, 10, 10));
	$style_legende = array('width' => 0.15, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
	$style_poi = array('width' => .75, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(211, 48, 47));
	$style_el = array('width' => .7, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
	$style_tracks_alle = array('width' => .5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(245, 5, 5));
	
	
	//Schleife baut die einzelnen Suchgebietsseiten auf
	$nr = $nr_int = 0;
	$row_sum_c = "";
	if(!empty($suchgebiete["features"])){
		foreach($suchgebiete["features"] as $gebiet){
			if($gebiet['properties']["typ"] == "Suchgebiet" || $gebiet['properties']["typ"] == "Wegsuche" || $gebiet['properties']["typ"] == "Mantrailer" || $gebiet['properties']["typ"] == "Punktsuche"){
				// add a page
				$pdf->AddPage();

				
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
							//	  'y' => ($suchgebiete['features'][$nr]['geometry']['coordinates'][1] + 0.001*sin($r*15*pi()/180)));
						}
					}
				}
				//print_r($suchgebiet);
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
							//echo "Suchgebiet".($nr+1)." Kacheln X: ". ($xT_2 - $xT_1)." - Zoomlevel: ".$zt."<br>";
							$zx = $zt;
						}
						if((($yT_2 - $yT_1)) <=  $yKacheln && $zy == 0) { // Zoomlevel für Y Ausdehnung
							$zy = $zt;
							//echo "Suchgebiet".($nr+1)." Kacheln Y: ". ($yT_2 - $yT_1)." - Zoomlevel: ".$zt."<br>";
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
				
				//if($xKacheln % 2 == 0){$xTiles_add = 2;} else {$xTiles_add = 2;}
				//if($yKacheln % 2 == 0){$yTiles_add = 2;} else {$yTiles_add = 2;}
				
				
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
					$margin_left = round(($px_w - 256*$xKacheln)/2);
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
				$pdf->SetAutoPageBreak($auto_page_break, $bMargin);
				// set the starting point for the page content
				$pdf->setPageMark();
				
				$pdf->Bookmark('Suchgebiet '.($nr_int+1).' - '.$gebiet['properties']["name"], 1, 1, '', '', array(28,49,58));
				$sgebietname = (isset($gebiet["properties"]["name"]) && strpos($gebiet["properties"]["name"], "hlen!") == false) ? $gebiet["properties"]["name"] : "";
				$suchgebiet_table .= '<tr style="background-color:'.$row_sum_c.';"><td class="h5">Suchgebiet '.($nr_int+1).' - '.$sgebietname.'</td>';
				$html = '<h3>Suchgebiet '.($nr_int+1)." - ".$sgebietname.'</h3>';
				if($gebiet['properties']["typ"] == "Wegsuche"){
					$laenge_t = explode(".",$gebiet['properties']["masse"]);
					$html .= '<span class="bold">Wegsuche</span> - Länge: '.runden($laenge_t[0],$_SESSION["etrax"]["lunit"],$_SESSION["etrax"]["lfactor"]).'</br>';
					$suchgebiet_table .= '<td>Wegsuche</td> <td align="right">'.runden($laenge_t[0],$_SESSION["etrax"]["lunit"],$_SESSION["etrax"]["lfactor"]).'</td><td></td></tr>';
					$laenge_summe = $laenge_summe + $laenge_t[0];
				}
				if($gebiet['properties']["typ"] == "Punktsuche"){
					$laenge_t = explode(".",$gebiet['properties']["masse"]);
					$html .= '<span class="bold">Punktsuche</span>:</br>';
					$suchgebiet_table .= '<td>Punktsuche</td> <td align="right"></td><td></td></tr>';
					$laenge_summe = $laenge_summe;
				}
				if($gebiet['properties']["typ"] == "Mantrailer"){
					$laenge_t = explode(".",$gebiet['properties']["masse"]);
					$html .= '<span class="bold">Mantrailer</span>:</br>';
					$suchgebiet_table .= '<td>Mantrailer</td> <td align="right"></td><td></td></tr>';
					$laenge_summe = $laenge_summe;
				}
				if($gebiet['properties']["typ"] == "Suchgebiet"){
					$laenge_t = explode(".",$gebiet['properties']["masse"]);
					$html .= '<span class="bold">Suchgebiet</span> - Fläche: '.runden($laenge_t[0],$_SESSION["etrax"]["aunit"],$_SESSION["etrax"]["afactor"]).'</br>';
					$suchgebiet_table .= '<td>Suchgebiet</td> <td></td><td align="right">'.runden($laenge_t[0],$_SESSION["etrax"]["aunit"],$_SESSION["etrax"]["afactor"]).'</td></tr>';
					$flaeche_summe = $flaeche_summe + $laenge_t[0];
				}
				($row_sum_c == "" ? $row_sum_c = "#DEDEDE" : $row_sum_c = "");
				$pdf->writeHTML($style.$html, true, false, true, false, '');
				$html = "";
				
			//Copyrightvermerk Kartenmaterial
				$pos_l = ($margin_left)/$dpi*25.4;
				$pos_t = (($yKacheln*256+$margin_top))/$dpi*25.4+2;
				//$pdf->Text($pos_l, $pos_t, $path[$map]['copyright'], false, false, true, 0, 0, 'left', false, '', 0, false, 'T', 'M', false);
				$pdf->writeHTMLCell(170, 5, $pos_l, $pos_t, $style.$path[$map]['copyright']." ".$tile_error, 0, 0, 0, true, 'J', true);
				
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
							if($xx != $nr){	//damit eigenes Suchgebiet nicht doppelt gezeichnet wird
								$new_polygon_all = "";
								foreach($sg_o as $sg){
									$new_polygon_all .= (($sg["x"] - $x_papier)/$x_scale/$dpi*25.4).";".(($y_papier - $sg["y"])/$y_scale/$dpi*25.4).";";
									$typ_t = $sg["typ"];
								}
								$new_polygon_all = explode(";",substr($new_polygon_all,0,-1));
								
							
								if($typ_t == "Suchgebiet" || $typ_t == "Punktsuche"){		
									$pdf->Polygon($new_polygon_all, 'D', array('all' => $style_suchgebiet_alle), array(220, 220, 220));
								}
								if($typ_t == "Wegsuche"){		
									$pdf->PolyLine($new_polygon_all, 'D', array('all' => $style_wegsuche_alle), array(220, 220, 220));
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
							if($poi_x_t >= $xmin_map_ll_f && $poi_x_t <= $xmax_map_ll_f && $poi_y_t >= $ymin_map_ll_f && $poi_y_t <= $ymax_map_ll_f){
								$new_poi .= (($poi_x_t - $x_papier)/$x_scale/$dpi*25.4).";".(($y_papier - $poi_y_t)/$y_scale/$dpi*25.4).";";
								//$legende_eintrag[] = array("name" => $pois['features'][$poi_nr-1]['properties']['name'], "typ" => $pois['features'][$nr]['geometry']['type'], "style" => $style_poi, "bez" => $alphabet[$poi_nr-$skip], "x" => $poi_x_t, "y" => $poi_y_t );
								if(isset($pois['features'][$poi_nr]['properties']['name']) && $pois['features'][$poi_nr]['properties']['name'] != "POI") {
									$poiname_t = $pois['features'][$poi_nr]['properties']['name'];
								} elseif($pois['features'][$poi_nr]['properties']['name'] == "POI" && isset($pois['features'][$poi_nr]['properties']['beschreibung'])) {
									$poiname_t = substr($pois['features'][$poi_nr]['properties']['beschreibung'],0,25);
								} else {
									$poiname_t = "Keine Beschreibung";
								}
								$legende_eintrag[] = array("name" => $poiname_t, "typ" => $pois['features'][$poi_nr]['geometry']['type'], "style" => $style_poi, "bez" => $alphabet[$poi_nr-$skip], "x" => $poi_x_t, "y" => $poi_y_t );
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
									}
									
									//$html .= "yfrom: ".(($arr_temp[$iii]["lon"] - $x_papier)/$x_scale/$dpi*25.4)." numeric:".is_numeric((($arr_temp[$iii]["lon"] - $x_papier)/$x_scale/$dpi*25.4))." yto: ".(($y_papier - $arr_temp[$iii]["lat"])/$y_scale/$dpi*25.4)." numeric:".is_numeric((($y_papier - $arr_temp[$iii]["lat"])/$y_scale/$dpi*25.4))."<br>";
									$iii++;
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
											//$pdf->SetXY($arr_coord_temp[$ij]["x_papier"]-15, $margin_top*$dpi/25.4-3);
											$pdf->Cell(30, 0, $utmstreifen." ".number_format(round($arr_coord_temp[$ij]["x_utm"]), 0, ',', '.'), 0, $ln=0, 'C', 0, '', 0, false, 'C', 'B');
											//$pdf->Text($arr_coord_temp[0]["x_papier"], $arr_coord_temp[0]["y_papier"]-2, $utmstreifen." ".round($arr_coord_temp[0]["x_utm"]), false, false, true, 1, 0, 'left', false, false, 0, false, 'T', 'M', false);
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
									}
									
									//$html .= "yfrom: ".(($arr_temp[$iii]["lon"] - $x_papier)/$x_scale/$dpi*25.4)." numeric:".is_numeric((($arr_temp[$iii]["lon"] - $x_papier)/$x_scale/$dpi*25.4))." yto: ".(($y_papier - $arr_temp[$iii]["lat"])/$y_scale/$dpi*25.4)." numeric:".is_numeric((($y_papier - $arr_temp[$iii]["lat"])/$y_scale/$dpi*25.4))."<br>";
									$iii++;
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
									//if(!empty($arr_coord_temp) && !empty($arr_coord_temp[$jj]) && $arr_coord_temp[$ij]["x_papier"] >= 0 && $arr_coord_temp[$jj]["x_papier"] <= $papier_w && $arr_coord_temp[$jj]["y_papier"] >= 0 && $arr_coord_temp[$jj]["y_papier"] <= $papier_h){
									if(!empty($arr_coord_temp) && !empty($arr_coord_temp[$jj]) && $arr_coord_temp[$jj]["x_papier"] >= 0 && $arr_coord_temp[$jj]["x_papier"] <= $papier_w && $arr_coord_temp[$jj]["y_papier"] >= 0 && $arr_coord_temp[$jj]["y_papier"] <= $papier_h){
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
				$leg_width = ($xKacheln*256)/$dpi*25.4;
				$leg_height = 297-(($yKacheln*256+$margin_top)/$dpi*25.4+15)-15;
				//$leg_breite = 55;
				$leg_top = ($yKacheln*256+$margin_top)/$dpi*25.4-$leg_height;
				$leg_left = ($margin_left + $xKacheln*256)/$dpi*25.4+2;
				$pdf->SetLineStyle($style_legende);
				$pdf->RoundedRect($margin_left/$dpi*25.4, (($yKacheln*256+$margin_top)/$dpi*25.4)+15, $leg_width, $leg_height, 2, '1010');
				$pdf->SetXY($margin_left/$dpi*25.4+5, (($yKacheln*256+$margin_top)/$dpi*25.4+15)+2);
				$html_legende = '<h5>Legende</h5>';
				$pdf->writeHTML($style.$html_legende, true, false, true, false, 'J');
				
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
					if($e_nr < $r_max*3){ // maximale Anzahl an Legendeneinträgen
						//Beschriftung
						$pdf->SetXY(($margin_left/$dpi*25.4+5)+($leg_width/3)*($c_nr)+10, ((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr)-5);
						//$pdf->Cell(($leg_width/3-12), 10, substr($leg["name"],0,30), 0, $ln=0, 'left', 0, '', 0, false, 'C', 'C');
						if(strlen($leg["name"]) > 50){$dots = " ...";} else {$dots = "";}
						$pdf->MultiCell(($leg_width/3-12), 10, substr($leg["name"],0,50).$dots, 0, 'L', 0, 0, '', '', true, 0, false, true, 10, 'M');
						//$pdf->Cell(($leg_width/3-12), 10, substr($leg["name"],0,30), 0, $ln=0, 'left', 0, '', 0, false, 'C', 'C');
						
						//Symbol
						$pdf->SetFont('helvetica', '', 7);
							if($leg["typ"] == "Suchgebiet" || $leg["typ"] == "Punktsuche"){
								$pdf->Polygon(array((($margin_left/$dpi*25.4+5)+($leg_width/3-12)*($c_nr)),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr-3),(($margin_left/$dpi*25.4+5)+($leg_width/3-12)*($c_nr)+6),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr-3),(($margin_left/$dpi*25.4+5)+($leg_width/3-12)*($c_nr)+6),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr+3),(($margin_left/$dpi*25.4+5)+($leg_width/3-12)*($c_nr)),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr+3)), 'D', array('all' => $style_suchgebiet), array(220, 220, 220));
							}
							if($leg["typ"] == "Wegsuche"){
								$pdf->PolyLine(array((($margin_left/$dpi*25.4+5)+($leg_width/3-12)*($c_nr)),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr),(($margin_left/$dpi*25.4+5)+($leg_width/3-12)*($c_nr)+6),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr)), 'D', array('all' => $style_suchgebiet), array(220, 220, 220));
							}
							if($leg["typ"] == "Tracks"){
								$pdf->PolyLine(array((($margin_left/$dpi*25.4+5)+($leg_width/3-12)*($c_nr)),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr),(($margin_left/$dpi*25.4+5)+($leg_width/3-12)*($c_nr)+6),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr)), 'D', array('all' => $style_tracks_alle), array(220, 220, 220));
							}
							if($leg["typ"] == "Suchgebiet_andere"){
									$pdf->Polygon(array((($margin_left/$dpi*25.4+5)+($leg_width/3-12)*($c_nr)),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr-3),(($margin_left/$dpi*25.4+5)+($leg_width/3-12)*($c_nr)+6),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr-3),(($margin_left/$dpi*25.4+5)+($leg_width/3-12)*($c_nr)+6),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr+1),(($margin_left/$dpi*25.4+5)+($leg_width/3-12)*($c_nr)),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr+1)), 'D', array('all' => $style_suchgebiet_alle), array(220, 220, 220));
									$pdf->PolyLine(array((($margin_left/$dpi*25.4+5)+($leg_width/3-12)*($c_nr)),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr+3),(($margin_left/$dpi*25.4+5)+($leg_width/3-12)*($c_nr)+6),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr)+3), 'D', array('all' => $style_wegsuche_alle), array(220, 220, 220));
								}
							if($leg["typ"] == "Point"){
								$pdf->Polygon(array(((($margin_left/$dpi*25.4+5)+($leg_width/3)*($c_nr)+3)-1.7),(((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr)+1),(($margin_left/$dpi*25.4+5)+($leg_width/3)*($c_nr)+3),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr)+4,((($margin_left/$dpi*25.4+5)+($leg_width/3)*($c_nr)+3)+1.7),(((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr)+1)), 'DF', array('all' => $style_poi), array(211, 48, 47));
								$pdf->Circle((($margin_left/$dpi*25.4+5)+($leg_width/3)*($c_nr)+3),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr), 2,0,360, 'DF', $style_poi, array(255, 255, 255));
								$pdf->SetXY((($margin_left/$dpi*25.4+5)+($leg_width/3)*($c_nr)),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr));
								$pdf->Cell(6,6, $leg["bez"], 0, $ln=0, 'C', 0, '', 0, false, 'C', 'C');
							}
							if($leg["typ"] == "EL"){
								$pdf->Polygon(array(((($margin_left/$dpi*25.4+5)+($leg_width/3)*($c_nr)+3)-1.7),(((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr)+1),(($margin_left/$dpi*25.4+5)+($leg_width/3)*($c_nr)+3),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr)+4,((($margin_left/$dpi*25.4+5)+($leg_width/3)*($c_nr)+3)+1.7),(((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr)+1)), 'DF', array('all' => $style_el), array(0, 0, 0));
								$pdf->Circle((($margin_left/$dpi*25.4+5)+($leg_width/3)*($c_nr)+3),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr), 2,0,360, 'DF', $style_el, array(255, 255, 255));
								$pdf->SetXY((($margin_left/$dpi*25.4+5)+($leg_width/3)*($c_nr)),((($yKacheln*256+$margin_top)/$dpi*25.4+15)+15+10*$r_nr));
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
				
			$nr_int++;	
			}//Ende if Einschränkung auf Wegsuche und Flächensuche
			$nr++;
		} 
	}

}

// Übersichtsseite für Suchgebiete
if($eb_suchgebiet == "1"){
// add a page
	$pdf->AddPage();

	$pdf->Bookmark('Suchgebiete', 0, 9, '', '', array(28,49,58));
	
	$html = '<h1>Suchgebiete</h1>';
	$pdf->writeHTML($style.$html, true, false, true, false, '');
	
	$pdf->SetY($pdf->getY()+15);
	
	$html = '<table width="100%"><tr class="bold"><td width="45%" ></td><td width="15%" >Typ</td><td width="15%" align="right">Länge</td><td width="25%" align="right">Fläche</td></tr>'.$suchgebiet_table.'<tr class="bold"><td></td><td></td><td align="right">'.runden($laenge_summe,$_SESSION["etrax"]["lunit"],$_SESSION["etrax"]["lfactor"]).'</td><td align="right">'.runden($flaeche_summe,$_SESSION["etrax"]["aunit"],$_SESSION["etrax"]["afactor"]).'</td></tr></table>';
	// output the HTML content
	$pdf->writeHTML($style.$html, true, false, true, false, '');

	// Move page 7 to page 3
	$pdf->movePage($pdf->PageNo(), $pdf_seite_suchgebiet);
	
	// reset pointer to the last page
	$pdf->lastPage();

}

//  ***********************************************************
//  **            Übersichtskarten                
//  ***********************************************************
if($eb_uebersicht == "1"){
	$row_cc = "";
	
	$pdf->Bookmark('Übersichtskarten', 0, 10, '', '', array(28,49,58));
	
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
	
	//Alle Suchgebiete für Darstellung
	$suchgebiet_all = array();
	if(!empty($suchgebiete["features"])){
		//foreach($suchgebiete["features"] as $gebiet){
			$nr_i = 0;
			foreach($suchgebiete["features"] as $gebiet2){
				$i = 0;
				$x_temp_i = $y_temp_i = 0;
				//Abfangen der Punktsuche
				if(isset($suchgebiete['features'][$nr_i]['properties']['typ'])) {//Area51 ist der initiale Eintrag
					if($suchgebiete['features'][$nr_i]['properties']['typ'] != "Punktsuche" && $suchgebiete['features'][$nr_i]['properties']['typ'] != "EL") {
						foreach($suchgebiete['features'][$nr_i]['geometry']['coordinates'][0] as $xy){
							if($x_temp_i != $suchgebiete['features'][$nr_i]['geometry']['coordinates'][0][$i][0] && $y_temp_i != $suchgebiete['features'][$nr_i]['geometry']['coordinates'][0][$i][1]){ //entfernt Mehrfacheinträge
								//Aufbauen des Suchgebiets
								$suchgebiet_all[($nr_i)][] = array('x' => $suchgebiete['features'][$nr_i]['geometry']['coordinates'][0][$i][0],
													  'y' => $suchgebiete['features'][$nr_i]['geometry']['coordinates'][0][$i][1],
													  'typ' => $suchgebiete['features'][$nr_i]['properties']['typ']);
							}
							$x_temp_i = $suchgebiete['features'][$nr_i]['geometry']['coordinates'][0][$i][0];
							$y_temp_i = $suchgebiete['features'][$nr_i]['geometry']['coordinates'][0][$i][1];
							$i++;
						}
					} elseif($suchgebiete['features'][$nr_i]['properties']['typ'] == "Punktsuche" || $suchgebiete['features'][$nr_i]['properties']['typ'] == "EL") {
					//Es wird ein "Kreis" um den ursprünglichen Mittelpunkt gezeichnet
						for($r = 0; $r <= 24; $r++){
							$suchgebiet_all[($nr_i)][] = array('x' => ($suchgebiete['features'][$nr_i]['geometry']['coordinates'][0] + (0.0005*cos($r*15*pi()/180))*((cos($suchgebiete['features'][$nr_i]['geometry']['coordinates'][0]*pi()/180)))),
								  'y' => ($suchgebiete['features'][$nr_i]['geometry']['coordinates'][1] + (0.0005*sin($r*15*pi()/180))*(1-(sin($suchgebiete['features'][$nr_i]['geometry']['coordinates'][1]*pi()/180)*0.5))),
								  'typ' => $suchgebiete['features'][$nr_i]['properties']['typ']);
						}
								  
					}
				}
				$nr_i++;
				
				
				
			}
		//}
	}
		$papier_w = $papier["A4q"]["width"]; //Papierbreite in mm
		$papier_h = $papier["A4q"]["height"]; //Papierbreite in mm
		$abstand_oben = 30; //Oberer Bildabstand in mm - wenn nichts eingefüllt wird, wird das Bild zentriert
		$abstand_links = 15; //Linker Bildabstand in mm - wenn nichts eingefüllt wird, wird das Bild zentriert
		$dpi = 120; //angestrebte dots per inch (~ 96 ist die Pixeldichte bei einem Monitor)
		$orientation = $papier["A4q"]["orientation"];
		$p_format = $papier["A4q"]["format"];
		$xKacheln = 5; // Anzahl der horizontalen Kacheln
		$yKacheln = 3; // Anzahl der vertikalen Kacheln
		if($xKacheln % 2 == 0){$xTiles_add = 2;} else {$xTiles_add = 2;}
		if($yKacheln % 2 == 0){$yTiles_add = 2;} else {$yTiles_add = 2;}
		
		//Voreinstellungen zum Karten zeichnen
		$px_w = round($papier_w/25.4*$dpi);	
		$px_h = round($papier_h/25.4*$dpi);

		//Kartenmaterial aus POST
		$map = $kartenmaterial;


		//Styles fürs Zeichnen
		$style_suchgebiet = array('width' => 1.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 100, 60));
		$style_suchgebiet_alle = array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(245, 65, 160));
		$style_wegsuche_alle = array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => '4,2', 'color' => array(245, 65, 160));
		$style_grid_hauptintervall = array('width' => .5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(10, 10, 10));
		$style_grid_hilfsintervall = array('width' => .15, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(10, 10, 10));
		$style_legende = array('width' => 0.4, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$style_poi = array('width' => .75, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(211, 48, 47));
		$style_el = array('width' => .7, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		
		//Schleife baut die einzelnen Suchgebietsseiten auf
	$nr = $karte = 0;
	if(!empty($suchgebiete["features"])){
		foreach($suchgebiete["features"] as $gebiet){
			if(strpos($gebiet['properties']["typ"],"bersicht") != false){
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
							//	  'y' => ($suchgebiete['features'][$nr]['geometry']['coordinates'][1] + 0.001*sin($r*15*pi()/180)));
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
							
						}
						if((($yT_2 - $yT_1)) <=  $yKacheln && $zy == 0) { // Zoomlevel für X Ausdehnung
							$zy = $zt;
							
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
				$x_o = $x_o < 0 ? 0 : $x_o; // Falls der Offset negativ wäre
				$y_o = floor($yKacheln+2)*256*$yTile_m - ($yKacheln)*256/2;
				$y_o = $y_o < 0 ? 0 : $y_o; // Falls der Offset negativ wäre
				//$html .= "x_o: ".$x_o." y_o: ".$y_o."<br>";
				//echo "<br>xTile_m: ".$xTile_m." yTile_m: ".$yTile_m." x_o: ".$x_o." y_o: ".$y_o."<br>";
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
				
				//echo "<br>Margin_left: ".$margin_left." Margin top: ".$margin_top."<br>";
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
				
				$karte++;
				$pdf->Bookmark('Übersichtskarte '.($karte), 1, 1, '', '', array(28,49,58));
				$html = '<h3>Übersichtskarte '.($karte).'</h3>';
				
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
						if($xx != $nr){	//damit eigenes Suchgebiet nicht doppelt gezeichnet wird
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
					$poi_nr = $skip = 0;
					//echo "Count von pois: ".count($pois)."<br>";
				$X_temp = $pdf->GetX();
				$Y_temp = $pdf->GetY();
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
								$legende_eintrag[] = array("name" => $poiname_t, "typ" => $pois['features'][$poi_nr]['geometry']['type'], "bez" => $alphabet[$poi_nr-$skip], "x" => $poi_x_t, "y" => $poi_y_t );
								$pdf->Polygon(array((($poi_x_t - $x_papier)/$x_scale/$dpi*25.4-2.2),(($y_papier - $poi_y_t)/$y_scale/$dpi*25.4-4),(($poi_x_t - $x_papier)/$x_scale/$dpi*25.4),(($y_papier - $poi_y_t)/$y_scale/$dpi*25.4),(($poi_x_t - $x_papier)/$x_scale/$dpi*25.4+2.2),(($y_papier - $poi_y_t)/$y_scale/$dpi*25.4-4)), 'DF', array('all' => $style_poi), array(211, 48, 47));
								$pdf->Circle((($poi_x_t - $x_papier)/$x_scale/$dpi*25.4),(($y_papier - $poi_y_t)/$y_scale/$dpi*25.4-5), 2.5,0,360, 'DF', $style_poi, array(255, 255, 255));
								$pdf->SetXY((($poi_x_t - $x_papier)/$x_scale/$dpi*25.4-3),(($y_papier - $poi_y_t)/$y_scale/$dpi*25.4-5));
								$pdf->Cell(6,6, $alphabet[$poi_nr-$skip], 0, $ln=0, 'C', 0, '', 0, false, 'C', 'C');
							}
						} else {$skip++;}
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
		
			$leg_width = (($xKacheln*256)/$dpi*25.4)-4;
			$leg_height = 20;
			//$leg_breite = 55;
			$pdf->SetLineStyle($style_legende);
			$leg_top = ($yKacheln*256+$margin_top)/$dpi*25.4-$leg_height;
			$leg_left = ($margin_left)/$dpi*25.4+2;
			$pdf->RoundedRect($leg_left, $leg_top, $leg_width, $leg_height, 2, '1010', 'DF',$style_legende,array(255,255,255));
			$pdf->PolyLine(array($leg_left+$leg_width+2,$leg_top,$leg_left+$leg_width+2,$leg_top+$leg_height), 'D', array('all' => $style_grid_hilfsintervall), array(220, 220, 220));
			$pdf->SetXY($leg_left, $leg_top);
			$html_legende = '<h5>Legende</h5>';
			$pdf->writeHTMLCell($leg_width, 0, $leg_left, $leg_top, $style.$html_legende, 0, 0, 0, true, 'C', true);
			$leg_left = $leg_left + 3;
			$leg_top = $leg_top + 8;
			$leg_height = $leg_height - 5;
			// get the current page break margin
			$bMargin = $pdf->getBreakMargin();
			// get current auto-page-break mode
			$auto_page_break = $pdf->getAutoPageBreak();
			// disable auto-page-break
			$pdf->SetAutoPageBreak(false, 0);
			$e_nr = $r_nr = $c_nr = 0;
			$r_max = floor(($leg_height - 5)/10);
			$leg_width = $leg_width-5;
			//echo "<br>R Max: ".$r_max;
			//print_r($legende_eintrag);
			foreach($legende_eintrag as $leg){
				if($e_nr <= $r_max){ // maximale Anzahl an Legendeneinträgen
					//Beschriftung
					$pdf->SetXY(($leg_left+1)+($leg_width/5-12)*($c_nr)+10, ($leg_top+10*$r_nr+5)+10*$r_nr+1);
					//$pdf->Cell(($leg_width/3-12), 10, substr($leg["name"],0,30), 0, $ln=0, 'left', 0, '', 0, false, 'C', 'C');
					if(strlen($leg["name"]) > 50){$dots = " ...";} else {$dots = "";}
					$pdf->writeHTMLCell(($leg_width-8), 10, ($leg_left+1)+($leg_width/5)*($c_nr)+5, ($leg_top+(10*$r_nr+5)*$r_nr+1), $style.'<span class="leg_poi">'.substr($leg["name"],0,50).$dots.'</span>', 0, 0, 0, true, 'J', true);
					//$pdf->MultiCell(($leg_width-8), 10, '<span class="leg_poi">'.substr($leg["name"],0,50).$dots.'</span>', 0, 'J', 0, 0, '', '', true, 0, false, true, 10, 'M');
					//$pdf->Cell(($leg_width/3-12), 10, substr($leg["name"],0,30), 0, $ln=0, 'left', 0, '', 0, false, 'C', 'C');
					//echo "<br> X: ".(($leg_left+1)+($leg_width/5)*($c_nr)+10)." Y: ".($leg_top+(10*$r_nr+5)*$r_nr+1)."<br>";
					//Symbol
					$pdf->SetFont('helvetica', '', 7);
						if($leg["typ"] == "Suchgebiet_andere"){
							$pdf->Polygon(array(($leg_left+($leg_width/5-12)*($c_nr)),(($leg_top+10*$r_nr+5)-3),($leg_left+($leg_width/5-12)*($c_nr)+6),(($leg_top+10*$r_nr+5)-3),($leg_left+($leg_width/5-12)*($c_nr)+6),(($leg_top+10*$r_nr+5)+1),($leg_left+($leg_width/5-12)*($c_nr)),(($leg_top+10*$r_nr+5)+1)), 'D', array('all' => $style_suchgebiet_alle), array(220, 220, 220));
							$pdf->PolyLine(array(($leg_left+($leg_width/5-12)*($c_nr)),(($leg_top+10*$r_nr+5)+3),($leg_left+($leg_width/5-12)*($c_nr)+6),(($leg_top+10*$r_nr+5)+3)), 'D', array('all' => $style_wegsuche_alle), array(220, 220, 220));
						}
						if($leg["typ"] == "Point"){
							$pdf->Polygon(array((($leg_left+($leg_width/5)*($c_nr)+3)-1.7),((($leg_top+10*$r_nr+5))+1),($leg_left+($leg_width/5)*($c_nr)+3),(($leg_top+10*$r_nr+5))+4,(($leg_left+($leg_width/5)*($c_nr)+3)+1.7),((($leg_top+10*$r_nr+5))+1)), 'DF', array('all' => $style_poi), array(211, 48, 47));
							$pdf->Circle(($leg_left+($leg_width/5)*($c_nr)+3),(($leg_top+10*$r_nr+5)), 2,0,360, 'DF', $style_poi, array(255, 255, 255));
							$pdf->SetXY(($leg_left+($leg_width/5)*($c_nr)),(($leg_top+10*$r_nr+5)));
							$pdf->Cell(6,6, $leg["bez"], 0, $ln=0, 'C', 0, '', 0, false, 'C', 'C');
						}
						if($leg["typ"] == "EL"){
							$pdf->Polygon(array((($leg_left+($leg_width/5)*($c_nr)+3)-1.7),((($leg_top+10*$r_nr+5))+1),($leg_left+($leg_width/5)*($c_nr)+3),(($leg_top+10*$r_nr+5))+4,(($leg_left+($leg_width/5)*($c_nr)+3)+1.7),((($leg_top+10*$r_nr+5))+1)), 'DF', array('all' => $style_el), array(0, 0, 0));
							$pdf->Circle(($leg_left+($leg_width/5)*($c_nr)+3),(($leg_top+10*$r_nr+5)), 2,0,360, 'DF', $style_el, array(255, 255, 255));
							$pdf->SetXY(($leg_left+($leg_width/5)*($c_nr)),(($leg_top+10*$r_nr+5)));
							$pdf->Cell(6,6, $leg["bez"], 0, $ln=0, 'C', 0, '', 0, false, 'C', 'C');
						}
					$r_nr++;
					if($r_nr == $r_max){$c_nr++;$r_nr = 0;}
					$pdf->SetFont('rokkittlight', '', 10);
					//$e_nr++;
				}
			} 
			
			$pdf->SetAutoPageBreak($auto_page_break, $bMargin);
			// set the starting point for the page content
			$pdf->setPageMark();
				
			// Legende zeichnen Ende
				$pdf->writeHTML($style.$html, true, false, true, false, '');
				
						
			
			} // Einschränkung auf Übersichtskarte
			$nr++;
		
			
			
		
		} 
	}
}

//  ***********************************************************
//  **            Inhaltsverzeichnis                 
//  ***********************************************************
if($eb_inhaltsverzeichnis == "1"){
	// add a new page for TOC
	$pdf->addTOCPage("P","A4");

	// write the TOC title
	$pdf->writeHTML($style.'<h1>Inhaltsverzeichnis</h1>', true, false, true, false, '');
	$pdf->Ln();
	$pdf->SetY($pdf->getY()+15);
	
	// add a simple Table Of Content at first page
	// (check the example n. 59 for the HTML version)
	$pdf->addTOC(2, 'rokkittlight', '.', 'INDEX', '', array(28,49,58));

	// end of TOC page
	$pdf->endTOCPage();



	// reset pointer to the last page
	$pdf->lastPage();
}
// ---------------------------------------------------------



$filename = "EB_".$_SESSION["etrax"]["EID"]."-".$_SESSION["etrax"]["adminOID"];

//Close and output PDF document
$pdf->Output($filename.'.pdf', 'I');  ///ACHTUNG: Das ist nur der vorübergehende Pfad zum Testen!

//============================================================+
// END OF FILE 
//============================================================+

} //Ende mindestens Leserechte

?>
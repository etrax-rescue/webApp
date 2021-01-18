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
$db_org = $db->prepare("SELECT OID,data,suchef,suchew FROM organisation");
$db_org->execute($db_org->errorInfo());
$org_arr = array();
while ($reso = $db_org->fetch(PDO::FETCH_ASSOC)){
	$data_org_json = json_decode(substr(string_decrypt($reso['data']), 1, -1));
	$org_arr[$reso["OID"]]["OID"] = $reso["OID"];
	$org_arr[$reso["OID"]]["bezeichnung"] = isset($data_org_json->bezeichnung) ? $data_org_json->bezeichnung : "";
	$org_arr[$reso["OID"]]["kurzname"] = isset($data_org_json->kurzname) ? $data_org_json->kurzname : "";
	$org_arr[$reso["OID"]]["adresse"] = isset($data_org_json->adresse) ? $data_org_json->adresse : "";
	$org_arr[$reso["OID"]]["notrufnummer"] = isset($data_org_json->notrufnummer) ? $data_org_json->notrufnummer : "";
	
	
}


require $baseURL."include/sessionhandler.php"; //Der Sessionhandler schreibt die Sessionwerte neu

if($USER["lesen"]){ //Mindestens Leserechte werden benötigt um etwas angezeigt zu bekommen.

//Steuerung per $_GET

//Suchgebiet
$suchgebiet_ID = (isset($_GET["SID"])) ? htmlspecialchars($_GET["SID"]) : $suchgebiet_ID = '';
//Karte
$map = (isset($_GET["map"])) ? htmlspecialchars($_GET["map"]) : 'etraxtopo';


//Einsatzberichte laden
//$sql_einsatzbericht = $db->prepare("SELECT OID,Ogleich,Ozeichnen,Ozuweisen,Osehen,data,gesucht,einsatzbericht,orginfo,anfang,ende,suchtyp FROM settings WHERE EID = ".$EID."");
$sql_einsatzbericht = $db->prepare("SELECT data,gesucht,einsatzbericht,orginfo FROM settings WHERE EID = ".$EID."");
	$sql_einsatzbericht->execute($sql_einsatzbericht->errorInfo());
	while ($sqleinsatzbericht = $sql_einsatzbericht->fetch(PDO::FETCH_ASSOC)){
		$einsatzbericht_json = ($sqleinsatzbericht['einsatzbericht'] != '') ? json_decode(substr(string_decrypt($sqleinsatzbericht['einsatzbericht']), 1, -1)) : '';
		$gesucht_json = ($sqleinsatzbericht['gesucht'] != '') ? json_decode(substr(string_decrypt($sqleinsatzbericht['gesucht']), 1, -1)) : '';
		$settings_data_json = json_decode(substr(string_decrypt($sqleinsatzbericht['data']), 1, -1));
		$einsatz_anfang = isset($settings_data_json->anfang) ? $settings_data_json->anfang : "";
		$OIDprim = isset($settings_data_json->OID) ? $settings_data_json->OID : "";
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
    public function Header() {
		
			$this->SetY(10);
			// Set font
			$this->SetFont('merriweathersanslight', '', 8);
			// Title
			$this->Cell(0, 15, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
		
    }

    // Page footer
    public function Footer() {
		
			// Position at 15 mm from bottom
			$this->SetY(-12);
			$image_file = 'images/etrax.png';
			//$this->Image($image_file, 15, '', 15, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
			// Set font
			
		
    }
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator('Created with eTrax | rescue');
$pdf->SetAuthor($org_arr[$_SESSION["etrax"]["adminOID"]]["kurzname"]);
$pdf->SetTitle('Handzettel - Einsatz '.$_SESSION["etrax"]["Einsatzname"]);
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
	h6, .h6 {
        color: #D3302F;
        font-family: oswaldb;
		line-height: 17px;
        font-size: 12pt;
        text-decoration: '';
		text-align: center;
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
$pdf->SetAutoPageBreak(FALSE, PDF_MARGIN_BOTTOM);

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

	// add a page
	$pdf->AddPage("L","A4");
			
			
			for($x = 0; $x < 3; $x++){	
				$pdf->writeHTMLCell(79, 15, 10+($x*99), 10, $style.'<h1 align="center">Vermisst</h1>', 0, 0, 0, true, 'C', true); //Überschrift
					
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
						<br><span class="bold">Beschreibung: </span><br>'.(isset($gesucht_json->gesuchtbeschreibung) && !empty($gesucht_json->gesuchtbeschreibungextern) ? $gesucht_json->gesuchtbeschreibungextern : "");
					// output the HTML content
					//$imgdata = base64_decode($gesucht_json->gesuchtbild);
					// The '@' character is used to indicate that follows an image data stream and not an image file name
					//$pdf->Image('@'.$imgdata,24.510+($x*99),30,50,70,'','','',false,300, '',false,false,0,'T',false,false);
					$filename = is_file( "../../../secure/data/".$EID."/gesucht_big.jpg") ? "../../../secure/data/".$EID."/gesucht_big.jpg" : "../img/no-pic.jpg";
					$pdf->Image($filename,24.510+($x*99),30,50,70,'','','',false,300, '',false,false,0,'T',false,false);
					
					$pdf->writeHTMLCell(79, 0, 10+($x*99), 100, $style.$html, 0, 0, 0, true, 'J', true);
					if(isset($org_arr[$OIDprim]["notrufnummer"]) && !empty($org_arr[$OIDprim]["notrufnummer"])){
						$notrufnummer = ' oder rufen sie '.$org_arr[$OIDprim]["notrufnummer"];
					} else {
						if(isset($org_arr[$_SESSION["etrax"]["adminOID"]]["notrufnummer"]) && !empty($org_arr[$_SESSION["etrax"]["adminOID"]]["notrufnummer"])){
							$notrufnummer = ' oder rufen sie '.$org_arr[$_SESSION["etrax"]["adminOID"]]["notrufnummer"];
						} else {
							$notrufnummer = "";
						}
					}
					$pdf->writeHTMLCell(79, 0, 10+($x*99), 170, $style.'<h6>Wenn sie die vermisste Person sehen, kontaktieren sie bitte die Polizei'.$notrufnummer.'</h6><br><span style="font-size:9px;">Gedruckt am: '.date('d.m.Y').'<br>Erstellt durch: '.$org_arr[$_SESSION["etrax"]["adminOID"]]["bezeichnung"].'</span>', 0, 0, 0, true, 'J', true);
				
				
			}	
			
			//Schnittmarkierung
			$style_schnittmarke = array('width' => .10, 'cap' => 'butt', 'join' => 'miter', 'dash' => '1,1', 'color' => array(55, 55, 55));
			$pdf->SetXY(99,200);
			$pdf->StartTransform();
				$pdf->Rotate(90);
				// set font for chars
				$pdf->SetFont('zapfdingbats', '', 16);
				$pdf->Cell(5,5, TCPDF_FONTS::unichr(36), 0, $ln=0, 'C', 0, '', 0, false, 'C', 'M');
			$pdf->StopTransform();
			$pdf->PolyLine(array(99,0,99,210), 'D', array('all' => $style_schnittmarke), array(220, 220, 220));
			$pdf->SetXY(198,200);
			$pdf->StartTransform();
				$pdf->Rotate(90);
				// set font for chars
				$pdf->SetFont('zapfdingbats', '', 16);
				$pdf->Cell(5,5, TCPDF_FONTS::unichr(36), 0, $ln=0, 'C', 0, '', 0, false, 'C', 'M');
			$pdf->StopTransform();
			$pdf->PolyLine(array(198,0,198,210), 'D', array('all' => $style_schnittmarke), array(220, 220, 220));
				


$filename = "Einsatz_".$_SESSION["etrax"]["EID"]."-".$_SESSION["etrax"]["adminOID"]."_Handzettel";

//Close and output PDF document
$pdf->Output($filename.'.pdf', 'I');  

//============================================================+
// END OF FILE 
//============================================================+

} //Ende mindestens Leserechte

?>
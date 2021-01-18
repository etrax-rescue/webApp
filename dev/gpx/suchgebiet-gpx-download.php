<?php
session_start();
if(!isset($_SESSION["etrax"]["usertype"])){
header("Location: index.php");
}
require "../../../secure/info.inc.php";
require "../../../secure/secret.php";
require "../include/verschluesseln.php";
$EID = $_SESSION["etrax"]["EID"];


$suchgebiet_ID = (isset($_GET['SID'])) ? htmlspecialchars($_GET['SID']) : $suchgebiet_ID = '';
$suchgebiet_name = (isset($_GET['name'])) ? htmlspecialchars($_GET['name']) : $suchgebiet_ID = '';
$suchgebiet_title = (isset($_GET['title'])) ? htmlspecialchars($_GET['title']) : $suchgebiet_ID = '';

$einsatz_query = $db->prepare("SELECT suchgebiete FROM settings WHERE EID = ".$EID."");
$einsatz_query->execute($einsatz_query->errorInfo());
while ($einsatz = $einsatz_query->fetch(PDO::FETCH_ASSOC)){
	$suchgebiete_json = string_decrypt($einsatz['suchgebiete']);
	$suchgebiete = json_decode($suchgebiete_json, true);
	
}
$time = time();
$track = '';
header('Content-type: text/xml');
header('Content-Disposition: attachment; filename="'.urlencode($suchgebiet_name).'.gpx"');

$track .= '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>
';
$track .= '<gpx xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/11.xsd"
xmlns="http://www.topografix.com/GPX/1/1"
xmlns:ns3="http://www.garmin.com/xmlschemas/TrackPointExtension/v1"
xmlns:ns2="http://www.garmin.com/xmlschemas/GpxExtensions/v3" 
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:ns1="http://www.cluetrust.com/XML/GPXDATA/1/0" 
creator="eTrax" version="1.1">
		<metadata>
			<link href="www.etrax.at">
				<text>'.$suchgebiet_title.'</text>
			</link>
		</metadata>
	<trk>
	<name>'.$suchgebiet_title.'</name>
		<trkseg>';

$firstpoint = "";

$suchgebiet_all = array();
if(!empty($suchgebiete["features"])){
	//foreach($suchgebiete["features"] as $gebiet){
		$nr_i = 0;
		foreach($suchgebiete["features"] as $gebiet2){
			$i = 0;
			$x_temp_i = $y_temp_i = 0;
			if($suchgebiete['features'][$nr_i]['properties']['id'] == $suchgebiet_ID){
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
					//$track .= $x_temp_i.'-'.$y_temp_i.'-'.date("Y-m-d",$time).'T'.date("H:i:s",$time).'<br>';
					$track .= '
			<trkpt lat="'.$y_temp_i.'" lon="'.$x_temp_i.'">
				<time>'.date("Y-m-d",$time).'T'.date("H:i:s",$time).'Z</time>
			</trkpt>';
					//Um bei Suchgebieten das Polygon zu schließen, wird der erste Punkt als letzter Punkt gesetzt.
					if(isset($suchgebiete['features'][$nr_i]['properties']['typ']) && $suchgebiete['features'][$nr_i]['properties']['typ'] == "Suchgebiet" && $i == 1){
						$firstpoint = '
				<trkpt lat="'.$y_temp_i.'" lon="'.$x_temp_i.'">
					<time>'.date("Y-m-d",($time+20)).'T'.date("H:i:s",($time+20)).'Z</time>
				</trkpt>';
					}
				}
			}
			$nr_i++;
			$time+=1000;
		}
	//}
}
$track .= $firstpoint;
$track .= '
		</trkseg>
	</trk>
</gpx>';


echo $track;
?>
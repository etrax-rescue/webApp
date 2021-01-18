<?php
require "../../../secure/info.inc.php";
require "../../../secure/secret.php";
require "../include/verschluesseln.php";

$eid = $_POST['EID'];


$einsatzbeginn = strtotime('-60 minutes');
$minspeed = 0;
$maxspeed = 56;
$trackpause = 600;
$trackstart = strtotime('-120 minutes');
$minpunktefuertrack = 1;
$newtrackloading = 60000;

function entfernung($lat, $lon, $lastlat, $lastlon){
	$dx = $lon - $lastlon;
	$dy = $lat - $lastlat;
	$dist = pow($dx, 2) + pow($dy, 2);
	$entfernung = sqrt($dist);
	return $entfernung;
}

//Mitglieder aus der DB holen
$GPStracker = array();
$query_tracker = $db->prepare("SELECT DISTINCT nummer FROM tracking WHERE EID LIKE ".$eid);
$query_tracker->execute($query_tracker->errorInfo());
while ($trackerID = $query_tracker->fetch())
{	
	array_push($GPStracker, $trackerID['nummer']);
}

$speed = $canwrite = $lastlon = $lastlat = $lastspeed = $tpunktnr = 0;
$gpxtracks = '{"type":"FeatureCollection",
	"features":
	[
		';
$lastUid = 0;
$newTrack = false;
foreach ($GPStracker as $key => $nummer) {
	$Uid = $nummer;
	// Wenn ein neuer Tracker dann $newTrack 1 setzen
	if($lastUid != 0 && $lastUid != $Uid){
		$newTrack = true;
	}else{
		$newTrack = false;
	}
	$counter = 1;
	$newFeature = 1;
	$isData = false;
	
	//Layer baut die einzelnen Usertracks auf auf
	$usertracks = $db->prepare("SELECT lon,lat,timestamp,altitude,timestamp_server FROM tracking WHERE timestamp > ".($einsatzbeginn*1000)." AND ((UID LIKE '".$UID."') OR (nummer LIKE '".$BOSnr."')) AND CONVERT(hdop,UNSIGNED INTEGER) < 70 ORDER BY timestamp DESC");
	$usertracks->execute($usertracks->errorInfo());
	while ($rowtracks = $usertracks->fetch(PDO::FETCH_ASSOC))
	{
		if($counter == 1){
			$lastttsamp = intval($rowtracks['timestamp']/1000);
		}	
		$tlat =  $rowtracks['lat'];
		$tlon =  $rowtracks['lon'];
		$gpxtrackcolor = "#6610f2";

		if($tlon!="" && $tlat!=""){
			$ttsamp = intval($rowtracks['timestamp']/1000);
			$timediff = $lastttsamp-$ttsamp;// Zeit zwischen dem letzen Track und dem Aktuellen in Sekunden
			$entfernung = entfernung($timediff, $tlat, $tlon, $lastlat, $lastlon);// Funktion entfernung berechnet die Entfernung in Metern
			
			
			if($rowtracks['herkunft'] == "GPX"){
				$speed = 1;
			}else{
				$speed = $rowtracks['speed'];
			}
			//if($entfernung <= ($timediff*$maxspeed) && $speed >= $minspeed && $speed <= $maxspeed && $timediff < $trackpause){
			if($speed <= $maxspeed && $timediff <= $trackpause){
				if($counter == 1){
					$isData = true;
					$NewUser = $key;
					if($counter != 1){
						$gpxtracks .= ',';
					}
					if($newTrack == true){
						$gpxtracks .= ',';
						$newTrack = false;
					}
					$newFeature += 1;
					$gpxtracks .= '{
						"type": "Feature",
						"properties": {
							"name": "'.$Uid.'",
							"strokecolor": "'.$gpxtrackcolor.'"
						},
						"geometry": {
						"type": "MultiLineString",
						"coordinates": [
							[
					';
					$gpxtracks .= '['.$tlon.', '.$tlat.']';
				}else{
					$gpxtracks .= ',
					';
							$gpxtracks .= '['.$tlon.', '.$tlat.']';
				}
				$counter+=1;
			}else if($counter!=1){
				$isData = false;
				$gpxtracks .= '
			]]}},';
				$counter = 1;
			}
			$lastlon = $tlon;
			$lastlat = $tlat;
			$lastttsamp = $ttsamp;				
		}
	}
	if($isData){
	$lastUid = $Uid;
	$gpxtracks .= '
		]]}}';
	}
	}
	$gpxtracks .= '
	]
}';
echo $gpxtracks;
$encrypted_txt = encrypt($gpxtracks);
$file = $datapath.$eid.'/anreisetracks.txt';
file_put_contents($file, $encrypted_txt);
chmod($file, 0777);
?>
<?php
$sender = isset($_POST['gpxsender']) ? $_POST['gpxsender'] : null;
$gruppe = isset($_POST['gpxgruppe']) ? $_POST['gpxgruppe'] : 'bolb';
$UID = isset($_POST['gpxsenderUID']) ? $_POST['gpxsenderUID'] : null;
$OID = isset($_POST['gpxsenderOID']) ? $_POST['gpxsenderOID'] : null;
$nummer = isset($_POST['gpxsenderDNR']) ? $_POST['gpxsenderDNR'] : null;
$gpx = isset($_FILES['file']['name']) ? $_FILES['file']['name'] : null;
$EID = isset($_POST['EID']) ? $_POST['EID'] : null;
$EID = intval($EID);
require "../../../secure/info.inc.php";
require "../pdf/coordinates.php";

if(isset($gpx) && isset($gruppe)){	
	$uploaddir = '../gpximport';
	move_uploaded_file($_FILES['file']['tmp_name'], $uploaddir."/".$gpx);
	if (file_exists($uploaddir."/".$gpx)) {
		$xmlfile = simplexml_load_file($uploaddir."/".$gpx);
		foreach ($xmlfile->children() as $child){
			$name = $child->getName();
			if ($name == 'trk') {
				foreach( $child->children() AS $grandchild ) {
					$grandname = $grandchild->getName();
					if ($grandname == 'trkseg') {
						foreach( $grandchild->children() AS $greatgrandchild ) {
							$greatgrandname = $greatgrandchild->getName();
							if ($greatgrandname == 'trkpt') {
								$lat=$greatgrandchild['lat'];
								$lon=$greatgrandchild['lon'];
								foreach( $greatgrandchild->children() AS $elegreatgrandchild ) {
									$tzeit = ($elegreatgrandchild->getName()=='time') ? $elegreatgrandchild : '';
									if($elegreatgrandchild->getName()=='time'){$zeit=$elegreatgrandchild;}
									if($elegreatgrandchild->getName()=='ele'){$ele=$elegreatgrandchild;}
								}
								$zeit = str_replace("T", " ", $tzeit);
								$zeit = str_replace("Z", "", $zeit);
								$t = ($tzeit != '') ? strtotime($zeit)*1000 : time();
								$ele = ($ele != '') ? $ele : '268';
								$insert = $db->prepare("INSERT INTO tracking (EID,UID,OID,lat,lon,timestamp,hdop,altitude,speed,herkunft,nummer,gruppe) VALUES (".$EID.",'".$UID."','".$OID."','".$lat."','".$lon."','".$t."','0','".$ele."','0','GPX','".$nummer."','".$gruppe."')");
								$insert->execute() or die(print_r($insert->errorInfo()));
							}
						}
					}
				}
			}elseif($name == 'rte') {
				$nr = 0;
				$timestamp = time();
				foreach( $child->children() AS $grandchild ) {
					$grandname = $grandchild->getName();
					if ($grandname == 'rtept') {
						$grandname = $grandchild->getName();
						$lat=floatval($grandchild['lat']);
						$lon=floatval($grandchild['lon']);
						$ele="";
						foreach( $grandchild->children() AS $elegreatgrandchild ) {
								$ele = ($elegreatgrandchild['ele']) ? $ele=$elegreatgrandchild : '268';
						}
						if($nr > 0){
							$utm = ll2utm($lat,$lon);
							$utmlast = ll2utm($lastlat,$lastlon);
							$dx = $utm["x"] - $utmlast["x"];
							$dy = $utm["y"] - $utmlast["y"];
							$dist = pow($dx, 2) + pow($dy, 2);
							$entfernung = sqrt($dist);echo $entfernung;
							$d_time = $entfernung/2;
							$dist = 0;
							$timestamp += $d_time;
						}
							echo ceil($timestamp).' ';
						$lastlat=floatval($grandchild['lat']);
						$lastlon=floatval($grandchild['lon']);
						$nr ++;
						$insert = $db->prepare("INSERT INTO tracking (EID,UID,OID,lat,lon,timestamp,hdop,altitude,speed,herkunft,nummer,gruppe) VALUES (".$EID.",'".$UID."','".$OID."','".$lat."','".$lon."','".ceil($timestamp*1000)."','0','".$ele."','0','GPX','".$nummer."','".$gruppe."')");
						$insert->execute() or die(print_r($insert->errorInfo()));
					}
				}
			}
		}
		unlink($uploaddir."/".$gpx);
	} else {
	echo $gpx['type'];
		exit(' Failed to open gpx.');
	}
}else{
	echo 'no gpxfile';
	}
?>
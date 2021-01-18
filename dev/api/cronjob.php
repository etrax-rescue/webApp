<?php 
//Einsatzcheckliste
require "../../../secure/info.inc.php";
require "../../../secure/secret.php";
require "../include/verschluesseln.php";
require "../include/einsatz-loeschen.php";

if(isset($_POST['EID'])){
	einsatz_loeschen($_POST['EID'], $db);
}else{
	//Tage nach dem Beginn nach dem Einsätze und Übungen gelöscht werden
	$uebung_loeschen_nach = 32; //Wert in Tagen
	$einsatz_loeschen_nach = 366; //Wert in Tagen

	//Array mit den zu löschenden EIDs
	$EID2del = array();

	//Zu löschende Einsätze wähle
	$einsatz_query = $db->prepare("SELECT EID,data,typ FROM settings ORDER BY typ ASC, EID DESC");
	$einsatz_query->execute($einsatz_query->errorInfo());
	while ($einsaetze = $einsatz_query->fetch(PDO::FETCH_ASSOC)){
		$einsatztyp = $einsaetze["typ"];
		$einsatz_data = json_decode(substr(string_decrypt($einsaetze["data"]), 1, -1));
		$anfang = isset($einsatz_data->anfang) ? strtotime($einsatz_data->anfang) : time();
		$maxtime = ($einsatztyp === "einsatz") ? 3600*24*$einsatz_loeschen_nach : 3600*24*$uebung_loeschen_nach; //Maximal 366 Tage für Einsätze, 32 Tage für anderes
		if($anfang < (time() - $maxtime)){
			array_push($EID2del,$einsaetze['EID']);
		}
		echo "<br>Anfang: ".$anfang." time-maxtime: ".(time() - $maxtime)."<br>";
	}

	print_r($EID2del);

	foreach($EID2del as $EIDt){
		echo "<br>Vom Löschen betroffene EID: ".$EIDt."<br>";
		
		einsatz_loeschen($EIDt, $db);
	}
}
?>	
	
		
		
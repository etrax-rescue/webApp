<?php

function recursive_rmdir($dir) { 
	$rmdir = false;
	if( is_dir($dir) ) { 
		$objects = array_diff( scandir($dir), array('..', '.') );
		foreach ($objects as $object) { 
			$objectPath = $dir."/".$object;
			if( is_dir($objectPath) ){
				recursive_rmdir($objectPath);
			} else {
				unlink($objectPath);
			}
		} 
		$rmdir = rmdir($dir); 
	} 
	return $rmdir ? true : false;
}

function einsatz_loeschen($EIDt, $db){ 
	$return_value = "";
	
	//aktiveEID aus user table entfernen
	//********************************************************
	
	$usertable_query = $db->prepare("UPDATE user SET aktiveEID = NULL WHERE aktiveEID = ? ");
	$usertable_query->bindParam(1, $EIDt, PDO::PARAM_STR);
	$usertable_query->execute();
	//ErrorInfo
	$errorInfo = $usertable_query->errorInfo();
	$return_value .= strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "Datensätze in der user Table upgedated.<br>";

	
	
	//tracking table um Einsatzdaten bereinigen
	//********************************************************
	
	$trackingtable_query = $db->prepare("DELETE FROM tracking WHERE EID = ? ");
	$trackingtable_query->bindParam(1, $EIDt, PDO::PARAM_STR);
	$trackingtable_query->execute();
	//ErrorInfo
	$errorInfo = $trackingtable_query->errorInfo();
	$return_value .= strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "Datensätze aus der tracking Table gelöscht.<br>";




	//status table um Einsatzdaten bereinigen
	//********************************************************
	
	$statustable_query = $db->prepare("DELETE FROM status WHERE EID = ? ");
	$statustable_query->bindParam(1, $EIDt, PDO::PARAM_STR);
	$statustable_query->execute();
	//ErrorInfo
	$errorInfo = $statustable_query->errorInfo();
	$return_value .= strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "Datensätze aus der status Table gelöscht.<br>";

	
	
	//Einsatzverzeichnis in secure löschen
	$verzeichnis_geloescht = recursive_rmdir("../../../secure/data/".$EIDt);
	$return_value .= $verzeichnis_geloescht ? "Verzeichnis erfolgreich gelöscht.<br>" : "Das Verzeichnis konnte nicht erfolgreich gelöscht werden.<br>";
	
	//settings table um Einsatzdaten bereinigen
	//********************************************************
	
	$settingstable_query = $db->prepare("DELETE FROM settings WHERE EID = ? ");
	$settingstable_query->bindParam(1, $EIDt, PDO::PARAM_STR);
	$settingstable_query->execute();
	//ErrorInfo
	$errorInfo = $settingstable_query->errorInfo();
	$return_value .= strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "Einsatz mit <b>EID ".$EIDt."</b> aus der settings Table gelöscht.<br>";
	
	return $return_value;
}
?>

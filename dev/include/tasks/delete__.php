<?php
//die in $select übergebenen Werte werden zur Auswahl von WHERE genützt um eine exakte Auswahl in der DB zu ermöglichen
if($select){	
	$where = "";
	$x = 0;
	foreach ($select as $skey => $sentry) {
		if($x >0 ){
			$where = $where." AND ".$skey." = '".$sentry."'";
		} else {
			$where = "".$skey." = '".$sentry."'";
		}
		$x++;
	}
	$delete = $db->prepare("DELETE FROM ".$table." WHERE ".$where);
	
	if(preg_match('/^[a-zA-Z0-9_]*$/', $table)){
		$delete->execute() or die(print_r($delete->errorInfo()));
	}else{
		exit("Datenbank nicht erreichbar");
	}
} else {
	echo "Es wurde kein Feld zur Identifikation angegeben!";
}

?>

<nav class="editmenu d-flex bg-white">
	<div class="btn-group">
		<button type="button" class="btn border-0 btn-outline-danger color-red" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<span class="material-icons color-red">menu</span>
		</button>
		<div class="dropdown-menu">
			<?php
			
			if(is_numeric($userlevel) && $userlevel <= 6){ //Anzeigen Link Adminbereich für Globale und Organisations Administratoren sowie permanente Einsatzleiter
			
			?>
				<a class="btn border-0 btn-outline-danger" href="admin.index.php?do=" target="_blank"><i class="material-icons gray-dark pr-2">settings_applications</i> Administrationsbereich</a>
			<?php
			}
			?>
			<button class="btn border-0 btn-outline-danger showdatenschutz"  title="Informationen zum Datenschutz anzeigen"  data-toggle="modal" data-target="#dsmodal"><i class="material-icons gray-dark pr-2">remove_red_eye</i> Datenschutzinfo</button>
			
		</div>
	</div>
	<div class="btn-group">
		<button type="button" class="btn border-0 btn-outline-danger" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<i class="material-icons pr-2 color-red">face</i><?php echo $_SESSION["etrax"]["name"]; ?>
		</button>
		<div class="dropdown-menu">
			<?php
			if(isset($_SESSION["etrax"]["OID"]) && basename($_SERVER['SCRIPT_NAME']) === 'einsatzwahl.php'){
				$db_organisation = $db->prepare("SELECT usersync FROM organisation WHERE OID = '".$_SESSION["etrax"]["OID"]."'");
				$db_organisation->execute() or die(print_r($db_organisation->errorInfo(), true));
				
				while ($result = $db_organisation->fetch(PDO::FETCH_ASSOC)){
					$usersync = $result['usersync'];
				} 
				?>
				<button class="btn border-0 btn-outline-danger showuserdetails" title="Nutzerdaten anzeigen und bearbeiten"><i class="material-icons gray-dark pr-2">edit</i> Userdaten bearbeiten</button>
			<?php  } ?>
			<a class="btn border-0 btn-outline-danger logout" href="index.php" target="_self"><i class="material-icons gray-dark pr-2">power_settings_new</i> Logout</a>
		</div>
	</div>
</nav>
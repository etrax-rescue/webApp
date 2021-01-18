<nav class="editmenu d-flex bg-white">
	<?php // Nur für DEV User die Auswahl der Organisation
		if($userlevel == 0 && is_numeric($userlevel)){ 
	?>
	<div class="btn-group">
		<button type="button" class="btn border-0 btn-outline-danger" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<span class="material-icons">menu</span>
		</button>
		<div class="dropdown-menu">
			<div class="list-group" id="OID_select">
				<div  class="list-group-item"><strong>Organisation wählen:</strong></div>
				<?php
					$db_organisation = $db->prepare("SELECT * FROM organisation");
					$db_organisation->execute($db_organisation->errorInfo());
					$oidtemp = "";
					while ($result = $db_organisation->fetch(PDO::FETCH_ASSOC)){
						$oidtemp = $result['OID'];
						$data_org_json = json_decode(substr(string_decrypt($result['data']), 1, -1));
				?>
					<a href="#" data-oid="<?php echo $result["OID"]; ?>" class="list-group-item list-group-item-action<?php if ($result["OID"] == $oid2){ echo " active";} ?>"><?php echo isset($data_org_json->kurzname) ? $data_org_json->kurzname : ""; ?></a>
				<?php }	?>
			</div>
		</div>
	</div>
	<?php } ?>
	<div class="btn-group">
		<button type="button" class="btn border-0 btn-outline-danger" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<i class="material-icons pr-2">face</i><?php echo $_SESSION["etrax"]["name"]; ?>
		</button>
		<div class="dropdown-menu">
			<a class="btn border-0 btn-outline-danger logout" href="index.php" target="_self"><i class="material-icons gray-dark pr-2">power_settings_new</i>Logout</a>
		</div>
	</div>
</nav>
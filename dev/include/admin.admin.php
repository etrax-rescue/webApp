<section class="adminlist org_list bg-white">
<?php 
//if($userlevel > 0){$oidselect = "WHERE OID = '".$OID."'";} else {$OID = "";}
$db_organisation = $db->prepare("SELECT OID, data FROM organisation ".$oidselect);
$db_organisation->execute() or die(print_r($db_org->errorInfo(), true));

while ($result = $db_organisation->fetch(PDO::FETCH_ASSOC)){
	$data_org_json = json_decode(substr(string_decrypt($result['data']), 1, -1));
	$oid_temp = $result['OID'];	
	
?>



	<section id="admin_<?php echo($oid_temp);?>" class="user_list">
		
		
		<section id="<?php echo($result['OID']);?>_admin_details">
						
			<?php 
			//Button neuen Administrator anlegen
			if($userlevel <= 3 && is_numeric($userlevel)) { // Nur Organisations Admins und Developmentteam kann neue Administratoren anlegen
			?>
			<button id="user_<?php echo($oid_temp);?>_add_p" class="user_show_import admin_add btn btn-secondary float-right mt-2 mb-3 ml-3 mr-3" data-oid="<?php echo($oid_temp); ?>">
				<i class="material-icons rounded-circle color-white">person_add</i> Administrator hinzufügen
			</button>
			<button id="user_<?php echo($oid_temp);?>_upgrade_p" class="user_show_import admin_upgrade btn btn-primary float-right mt-2 mb-3 ml-3 mr-3" data-oid="<?php echo($oid_temp); ?>">
				<i class="material-icons rounded-circle color-white">verified_user</i> User zu Administrator upgraden
			</button>
			<?php
			}
			?>
			<!-- User to Admin Upgrade Overlay Anfang -->
			<div class="modal fade admin_upgrade_modal_<?php echo($oid_temp);?>" tabindex="-1" role="dialog" aria-labelledby="usermodalheader" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="settingsmodalheader" style="font-weight:bold;">User zu Administrator machen</h5>
						</div>
						<div class="modal-body">
						<ul class="members" style="list-style-type:none">
						<?php
						//Button User zu Administrator machen
						//User aus der DB holen
						$db_mitglieder = $db->prepare("SELECT * FROM user WHERE OID = '".$oid_temp."'");
						$db_mitglieder->execute() or die(print_r($db_mitglieder->errorInfo(), true));
						$user_arr = array();
						//print_r($db_mitglieder);
						$n_user = 0;
						$letter1 = "";
						while ($res_mg = $db_mitglieder->fetch(PDO::FETCH_ASSOC)){
							$data_user_json = json_decode(substr(string_decrypt($res_mg['data']), 1, -1));
							$user_arr[] = array('UID' => $res_mg['UID'], 
												'OID'   => $res_mg['OID'], 
												'dienstnummer'   => isset($data_user_json->dienstnummer) ? $data_user_json->dienstnummer : "", 
												'name'   => isset($data_user_json->name) ? $data_user_json->name : "", 
												'pwd'   => isset($data_user_json->pwd) ? $data_user_json->pwd : "", 
												'username'   => isset($data_user_json->username) ? $data_user_json->username : "", 
												'email'   => isset($data_user_json->email) ? $data_user_json->email : "");
							$n_user++;
						}
						if($n_user > 0) {
							//Sortieren vorbereiten
							$name = array();
							foreach ($user_arr as $nr => $inhalt)
							{
								$name[$nr]  = strtolower( $inhalt['name'] );
							}
							//Sortieren
							array_multisort($name, SORT_ASC, $user_arr);
					
							//Ausgabe
							foreach ($user_arr as $nr => $inhalt)
							{
								if(substr($inhalt['name'],0,1) != $letterl){
									echo "<li><h3><span class='badge badge-dark text-uppercase'>".substr($inhalt['name'],0,1)."</span></h3></li>";
								}
						?>
									<li class="float-none"><button class="upgrade_user_to_admin" style="border:0px;background-color:rgba(0,0,0,0);float:none;" data-usersync="<?php echo($usersync);?>" data-oid="<?php echo($inhalt['OID']);?>" data-uid="<?php echo($inhalt['UID']);?>" data-name="<?php echo($inhalt['name']);?>" data-pwd="<?php echo($inhalt['pwd']);?>"data-username="<?php echo($inhalt['username']);?>" data-email="<?php echo($inhalt['email']);?>"><?php echo($inhalt['name'])." - ".$inhalt['dienstnummer'];?></button></li>
						<?php	
							$letterl = substr($inhalt['name'],0,1);
							}
						} else { // Ende IF wenn keine Mitglieder angelegt sind	
							echo "<li>Es sind keine Mitglieder angelegt</li>";
						}
					?>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div>
			<div class="clearfix"></div>
			<ul class="members list-group">
			
			
<?php  		// User Anzeigen
			//Array mit Einsätzen aufbauen
			$db_settings = $db->prepare("SELECT EID, data FROM settings ORDER BY EID");
			$db_settings->execute() or die(print_r($db_settings->errorInfo(), true));
			$einsatz_arr = array();
			//print_r($db_mitglieder);
			while ($res_einsatz = $db_settings->fetch(PDO::FETCH_ASSOC)){
				$settings_json = json_decode(substr(string_decrypt($res_einsatz['data']), 1, -1));
				$einsatz_arr[$res_einsatz['EID']] = array('einsatz' => isset($settings_json->einsatz) ? $settings_json->einsatz : "", 
									'anfang'   => isset($settings_json->anfang) ? $settings_json->anfang : "");
			}
			
			
			//User aus der DB holen
			$db_admin = $db->prepare("SELECT * FROM user WHERE OID = '".$oid_temp."' AND FID < 10 AND EID != '-1' ORDER BY UID AND eid");
			$db_admin->execute() or die(print_r($db_admin->errorInfo(), true));
			$admin_arr = array();
			//print_r($db_mitglieder);
			$n_user = 0;
			$letter1 = $eidtemp = $uidtemp = "";
			while ($res_mg = $db_admin->fetch(PDO::FETCH_ASSOC)){
				$data_user_json = json_decode(substr(string_decrypt($res_mg['data']), 1, -1));
				$admin_arr[$res_mg['UID']] = array('UID' => $res_mg['UID'], 
									'OID'   => $res_mg['OID'], 
									'FID'   => $res_mg['FID'], 
									'name'   => isset($data_user_json->name) ? $data_user_json->name : "", 
									'login'   => isset($data_user_json->username) ? $data_user_json->username : "",
									'dienstnummer'   => isset($data_user_json->dienstnummer) ? $data_user_json->dienstnummer : "", 
									'typ'   => isset($data_user_json->typ) ? $data_user_json->typ : "", 
									'pause'   => (isset($data_user_json->pause) && is_numeric($data_user_json->pause)) ? $data_user_json->pause/60 : 0, 
									'username'   => isset($data_user_json->username) ? $data_user_json->username : "", 
									'password'   => isset($data_user_json->pwd) ? $data_user_json->pwd : "", 
									'ausbildungen'   => isset($data_user_json->ausbildungen) ? $data_user_json->ausbildungen : "", 
									'email'   => isset($data_user_json->email) ? $data_user_json->email : "", 
									'bos'   => isset($data_user_json->bos) ? $data_user_json->bos : "", 
									'telefon'   => isset($data_user_json->telefon) ? $data_user_json->telefon : "", 
									'einsatzfaehig'   => isset($data_user_json->einsatzfaehig) ? $data_user_json->einsatzfaehig : "0", 
									'notfallkontakt'   => isset($data_user_json->notfallkontakt) ? $data_user_json->notfallkontakt : "", 
									'notfallinfo'   => isset($data_user_json->notfallinfo) ? $data_user_json->notfallinfo : "", 
									'kommentar'   => isset($data_user_json->kommentar) ? $data_user_json->kommentar : "",
									'lastupdate'   => $res_mg['lastupdate'],
									'usersync' => $usersync,
									'fun_list_kurz'   => $fun_list_kurz,
									'fun_list_lang'   => $fun_list_lang,
									'eid'   => $res_mg['EID']);
				$n_user++;
			}
			?>
				<script>
					let admin_arr = <?php echo json_encode($admin_arr); ?>;
				</script>
			<?php
			if($n_user > 0) {
				//Sortieren vorbereiten
				$name = array();
				foreach ($admin_arr as $nr => $inhalt)
				{
					$name[$nr]  = strtolower( $inhalt['UID'] );
				}
				//Sortieren
				array_multisort($name, SORT_ASC, $admin_arr);
		
				//Ausgabe
				foreach ($admin_arr as $nr => $inhalt)
				{
					if(substr($inhalt['name'],0,1) != $letterl){
						echo "<li class='list-group-item list-group-item-action active'><strong>".substr($inhalt['name'],0,1)."</strong></li>";
					}
					$eidtemp = "";
					if($uidtemp != $inhalt['UID']){
						/*foreach ($admin_arr as $nr2 => $inhalt2){
							if($inhalt2['UID'] == $inhalt['UID']){
								$eidtemp = $eidtemp.",".$inhalt2['eid'];
							}
						}*/
					//$eidtemp = substr($eidtemp,1);
					$eidtemp = $inhalt['eid'];
					$eidtext_lang = "";
					if(substr($inhalt['FID'],0,1) <= 6){
						if(substr($inhalt['FID'],0,1) == 0){
							$eidtext = "Developmentteam - Berechtigt für alle Einsätze";
						}
						if(substr($inhalt['FID'],0,1) == 3){
							$eidtext = "Organisations Administrator - Berechtigt für alle Einsätze";
						}
						if(substr($inhalt['FID'],0,1) == 6){
							$eidtext = "Permanenter Einsatzleiter - Berechtigt für alle Einsätze";
						}
					} else {
						$eidtext = "Berechtigt für EIDs: ".$eidtemp;
						//Anzeige der Einsätze mit Namen:
						$eids_t = explode(",", $eidtemp);
						foreach($eids_t as $eid_tt){
							$eidtext_lang .= isset($einsatz_arr[$eid_tt]["einsatz"]) ? $eid_tt."%%".substr($einsatz_arr[$eid_tt]["einsatz"],0,25)."... [".substr($einsatz_arr[$eid_tt]["anfang"],0,10)."]§§" : "";
						}
						
					}
				?>
				<li class="showadmin list-group-item list-group-item-action d-flex justify-content-start">
					<a href="#admin" data-oid="<?php echo($inhalt['OID']);?>" data-uid="<?php echo($inhalt['UID']);?>" data-name="<?php echo($inhalt['name']);?>" data-login="<?php echo($inhalt['login']);?>" data-email="<?php echo($inhalt['email']);?>" data-fid="<?php echo($inhalt['FID']);?>" data-eids="<?php echo $eidtemp;?>" data-eidstext="<?php echo $eidtext_lang;?>"><?php echo($inhalt['name']);?> | <?php echo $eidtext; ?></a>
				</li>
				<?php							
					
					} 
					
				$uidtemp = $inhalt['UID'];
				$letterl = substr($inhalt['name'],0,1);
				}
			} else { // Ende IF wenn keine Administratoren angelegt sind	
				echo "<li>Es sind keine Administratoren angelegt</li>";
			}
		?>
				</ul>
			</div>
		</section>
	</section>
	
<?php
} //Ende Schleife Organisation für Administratoren
?>
</section>


		
<!-- Admin Update Overlay Anfang -->
<div class="modal fade adminmodal" tabindex="-1" role="dialog" aria-labelledby="usermodalheader" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title mr-auto" id="settingsmodalheader">Admin Details: </h5>
					<button type="button" class="btn btn-primary admin_modify ml-2 pb-2 pt-2" title="User Daten bearbeiten" data-toggle="tooltip" data-placement="bottom">Daten bearbeiten</button>
					<button type="button" class="btn btn-success abschliessen admin_modify_save ml-2 pb-2 pt-2" title="Änderungen speichern" data-toggle="tooltip" data-placement="bottom" style="display:none;" data-uid="">Änderungen speichern</button>
					<button type="button" class="btn btn-warning admin_modify_downgrade ml-2" title="User Administratorenberchtigung entziehen" data-toggle="tooltip" data-placement="bottom" data-uid=""><i class='material-icons text-dark'>cancel</i></button>
					<button type="button" class="btn btn-danger admin_modify_delete ml-2" title="Administrator inklusive User löschen" data-toggle="tooltip" data-placement="bottom" style="display:none;" data-uid=""><i class='material-icons text-white'>delete_forever</i></button>
			</div>
			<div class="modal-body">
				<div class="form-group row">
					<input type="hidden" class="y_uid" id="uid" value=""></input>
					<input type="hidden" class="y_eids" id="eids" value=""></input>
					<input type="hidden" class="y_oid" id="oid" value=""></input>
					<input type="hidden" class="y_username_old" value=""></input>
					<input type="hidden" class="y_pwd_old" value=""></input>
					<input type="hidden" class="y_userlevelalt" id="userlevelalt" value=""></input>
					<label  class="col-sm-3 col-form-label" for="y_name">Name</label>
					<div class="col-sm-9">
						<input disabled type="text" name="y_name" class="mb-2 form-control y_admin_edit y_name checkJSON" id="y_name" placeholder="Vollständiger Name des Administrators" value=""></input>
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="y_loginname">Username</label>
					<div class="col-sm-9">
						<input disabled type="text" name="y_loginname" class="mb-2 form-control y_admin_edit y_loginname checkJSON usernamecheckadmin" id="y_loginname" placeholder="Username des Administrators" value=""></input>
						<small class="text-danger loginerror" style="display:none;">Dieser Username ist bereits vergeben.</small><br>
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="y_pwd">Passwort</label>
					<div class="col-sm-9">
						<input disabled type="password" name="y_pwd" class="mb-2 form-control y_admin_edit y_pwd checkJSON pwdcheckadmin" id="y_pwd" aria-describedby="pwdHelp1" placeholder="Feld leer lassen für keine Änderung" value=""></input>
						<small class="form-text PasswortHelp">Das Passwort muss folgende Kriterien erfüllen:</small>
						<small class="text-danger ml-4 letter">Kleinbuchstaben</small><br>
						<small class="text-danger ml-4 capital">Großbuchstaben</small><br>
						<small class="text-danger ml-4 number">Zahlen</small><br>
						<small class="text-danger ml-4 length">Mindestens 8 Zeichen</small><br>
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="y_repwd">Passwort Wiederholung</label>
					<div class="col-sm-9">
						<input disabled type="password" name="y_repwd" class="mb-2 form-control y_admin_edit y_repwd checkJSON repwdcheckadmin" id="y_repwd" aria-describedby="pwdHelp1" placeholder="Feld leer lassen für keine Änderung" value=""></input>
						<small class="text-danger ml-4 match">Die Passwörter müssen übereinstimmen</small><br>
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="y_email">E-mailadresse</label>
					<div class="col-sm-9">
						<input disabled type="text" name="y_email" class="mb-2 form-control y_admin_edit y_email checkJSON" id="y_email" placeholder="E-Mailadresse" value=""></input>
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="adminlevel">Administratorenstufe</label>
					<div class="col-sm-9">
						<select disabled name="adminlevel" id="adminlevel" size="1" class="form-control <?php if($userlevel <= 3 && is_numeric($userlevel)) { echo "y_admin_edit";} ?> y_adminlevel">
							<?php if($userlevel == 0 && is_numeric($userlevel)){ echo "<option value='0'>Developmentteam</option>";} //Dieser Typ ist nur für Developmentteam verfügbar?>
							<option value="3">Organisations Admin</option>
							<option value="6">Permanenter Einsatzleiter</option>
							<option disabled="disabled" value="8">Temporärer Admin <-- Definieren über jeweiligen Einsatz!</option>
						</select>
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="zugriffsrecht">Berechtigungen des Users</label>
					<div class="col-sm-9">
						<select disabled name="zugriffsrecht" id="zugriffsrecht" size="1" class="form-control <?php if($userlevel <= 3 && is_numeric($userlevel)) { echo "y_admin_edit";} ?> y_zugriffsrecht">
							<?php if($userlevel == 0 && is_numeric($userlevel)){ echo "<option value='0'>Developmentteam</option>";} //Dieser Typ ist nur für Developmentteam verfügbar?>
							<option value="1">Einsatzleitung (Keine Einschränkung)</option>
							<option value="2">Alle Rechte</option>
							<option value="3">Suchgebiete zeichnen und zuweisen</option>
							<option value="4">Suchgebiete zuweisen</option>
							<option value="5">Nur lesen</option>
						</select>
					</div>
				</div>
				Berechtigung für folgende Einsätze:
				<ul class="admin_eids" style="list-style-type:none;">
				
				</ul>
			</div>
		</div>
	</div>
</div>
<!-- Admin Update Overlay Ende-->
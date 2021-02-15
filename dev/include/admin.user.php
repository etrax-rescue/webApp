<section class="userlist org_list mt-0" >
<?php 
//if($userlevel > 0){$oidselect = "WHERE OID = '".$OID."'";} else {$OID = "";}
$db_organisation = $db->prepare("SELECT OID, data, usersync, funktionen FROM organisation ".$oidselect);
$db_organisation->execute() or die(print_r($db_organisation->errorInfo(), true));

while ($result = $db_organisation->fetch(PDO::FETCH_ASSOC)){
	$data_org_json = json_decode(substr(string_decrypt($result['data']), 1, -1));
	$oid_temp = $result['OID'];

	//Verfügbare Funktionen in der Organisation
	$funktionen = json_decode($result['funktionen'], true);
	$fun_list_kurz = "";
	$fun_list_lang = "";
	if(!empty($funktionen)){
		foreach($funktionen as $key => $val){
			$fun_list_kurz .= $val["kurz"].";";
			$fun_list_lang .= $val["lang"].";";
			}
		$fun_list_kurz = substr($fun_list_kurz,0,-1);
		$fun_list_lang = substr($fun_list_lang,0,-1);
	}	
?>



	<section id="user_<?php echo($oid_temp);?>" class="user_list">
		
		<input type="hidden" class="alle_funktionen_kurz_<?php echo($oid_temp);?>" value="<?php echo($fun_list_kurz);?>">
		<input type="hidden" class="alle_funktionen_lang_<?php echo($oid_temp);?>" value="<?php echo($fun_list_lang);?>">
		<section id="<?php echo($result['OID']);?>_user_details">
			<!-- Overlay Userimport Anfang -->
			<div class="modal fade user_<?php echo($oid_temp);?>_add" tabindex="-1" role="dialog" aria-labelledby="usermodalheader" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="usermodalheader" style="font-weight:bold;">User Importieren</h5>
						</div>
						<div class="modal-body">
							 <div class="bg-info rounded mb-4 p-3">
								<small class="text-white">
									Die zu importierenden User sind mittels Copy&Paste aus der MS-Excel Vorlagedatei in das Eingabefeld zu übertragen. Alternativ können auch die Inhalte einer CSV oder anderen Datei übernommen werden. Bei den Importeinstellungen lassen sich entsprechende Spaltentrennzeichen, Textmaskierungen und Zeilenumbrüche definieren.<br>
									Die Reihenfolge der Spalten lautet Name, Dienstnummer, Typ, Username, Passwort, E-Mailadresse, BOS-Kennung, Telefonnummer, Notfallkontakt, Notfallinfo, Kommentar, Pause, Ausbildung.
								</small>
							</div>
							<div class="input-row">
								 
									<textarea id="user_<?php echo($oid_temp);?>_user" name="user" rows="10" style="width:100%";></textarea>
									<section id="user_<?php echo($oid_temp);?>_settings">
										<ul id="settings_show_<?php echo($oid_temp);?>" class="list-group">
											<li class="list-group-item bg-info text-white">
												<h4>Importeinstellungen</h4>
											</li>
											<li class="list-group-item bg-danger text-white">
												<label for="user_<?php echo($oid_temp);?>_iSep" class="pr-1">Spaltentrennzeichen:</label>
												<input type="text" id="user_<?php echo($oid_temp);?>_iSep" name="iSep" value="TAB" size="3">
											</li>
											<li class="list-group-item"><label for="user_<?php echo($oid_temp);?>_iLine" class="pr-1">Zeilentrennzeichen:</label>
												<select id="user_<?php echo($oid_temp);?>_iLine" name="iLine">
													<option value="n">\n</option>
													<option value="nr">\n\r</option>
													<option value="r">\r</option>
													<option value="rn">\r\n</option>
													<option value="br"><?php echo(htmlspecialchars("<br>")); ?></option>
												</select>
											</li>
											<li class="list-group-item">
												<label for="user_<?php echo($oid_temp);?>_iEnc" class="pr-1">Textmaskierung:</label>
												<input type="text" id="user_<?php echo($oid_temp);?>_iEnc" name="iEnc" value="" size="3">
											</li>
											<li class="list-group-item">
												<label for="user_<?php echo($oid_temp);?>_iEsc" class="pr-1">Escape Zeichen:</label>
												<input type="text" id="user_<?php echo($oid_temp);?>_iEsc" name="iEsc" value="" size="3">
											</li>
											<li class="list-group-item">
												<label for="user_<?php echo($oid_temp);?>_iDel" class="pr-1">Bestehende User vor Import löschen</label>
												<select id="user_<?php echo($oid_temp);?>_iDel" name="iDel">
													<option value="0">Nein</option>
													<option value="1">Ja</option>
												</select>
											</li>
											<li class="list-group-item">
												<label for="user_<?php echo($oid_temp);?>_iPwUp" class="pr-1">Passwörter bestehender Nutzer überschreiben:</label>
												<select id="user_<?php echo($oid_temp);?>_iPwUp" name="iPwUp">
													<option value="0">Nein</option>
													<option value="1">Ja</option>
												</select>
											</li>
										</ul>
									</section>
									<input type="hidden" id="user_<?php echo($oid_temp);?>_iOrg" name="iOrg" value="<?php echo($oid_temp);?>">
							</div>
							<div id="labelError"></div>
						</div>
						<div class="modal-footer">
							<button type="submit" id="preview" name="import" class="user_import_preview btn-submit btn btn-primary" data-oid="<?php echo($oid_temp); ?>">Importvorschau erzeugen</button>
						</div>
					</div>
				</div>
			</div>
			<!-- Overlay User Import Ende -->
			
			
			
			<?php 
			$usersync = $result['usersync'];
			if($usersync == 1){
				echo "<span style='color:#D3302F;'>Die User dieser Organisation werden synchronisiert und können daher hier nicht bearbeitet werden.</span>";
			} else {
			if($userlevel <= 3 && is_numeric($userlevel)){
				?>
				<button class="user_show_import btn btn-secondary float-right mt-2 mb-3 ml-3 mr-3" data-oid="user_<?php echo($oid_temp); ?>_add">
					<i class="material-icons rounded-circle color-white">person_add</i> User hinzufügen
				</button>
			<?php } ?>
			<div class="bg-white rounded p-3">
			<?php
			}
			?>
			<div class="clearfix"></div>
			<ul class="members list-group">
			
			
<?php  		// User Anzeigen
			//User aus der DB holen
			$db_mitglieder = $db->prepare("SELECT * FROM user WHERE OID = '".$oid_temp."'");
			$db_mitglieder->execute() or die(print_r($db_mitglieder->errorInfo(), true));
			$user_arr = array();
			//print_r($db_mitglieder);
			$n_user = 0;
			$letterl = $usernames = $alledienstnummern = "";
			while ($res_mg = $db_mitglieder->fetch(PDO::FETCH_ASSOC)){
				$data_user_json = json_decode(substr(string_decrypt($res_mg['data']), 1, -1));
				$user_arr[$res_mg['UID']] = array('UID' => $res_mg['UID'], 
									'OID'   => $res_mg['OID'], 
									'FID'   => $res_mg['FID'], 
									'name'   => isset($data_user_json->name) ? $data_user_json->name : "", 
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
									'fun_list_lang'   => $fun_list_lang);
				$n_user++;
				$usernames .= isset($data_user_json->username) ? ";".$data_user_json->username : "";
				$alledienstnummern .= isset($data_user_json->dienstnummer) ? ";".$data_user_json->dienstnummer : "";
			}
			?>
				<script>
					let user_arr= <?php echo json_encode($user_arr); ?>;
				</script>
			<?php
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
						echo "<li class='list-group-item list-group-item-action active'><strong>".substr($inhalt['name'],0,1)."</strong></li>";
					}
				$lvl = explode(".",$inhalt['FID'],2);
				if($lvl[0] < 8){
					if($inhalt['typ'] != "" && $inhalt['typ'] != null){
						$atyp = "<b>".$inhalt['typ']."</b> und <b>Administrator</b>";
					} else {
						$atyp = "<b>Administrator</b>";
					}
				} else { $atyp = "<b>".$inhalt['typ']."</b>";}
				
			?>
						<li class="showuser list-group-item list-group-item-action d-flex justify-content-start">
							<button class="border-0 bg-transparent" data-uid="<?php echo($inhalt['UID']);?>"><?php echo($inhalt['name'])." - ".$inhalt['dienstnummer']." - ".$atyp;?></button>
						</li>
			<?php	
				$letterl = substr($inhalt['name'],0,1);
				}
			} else { // Ende IF wenn keine Mitglieder angelegt sind	
				echo "<li>Es sind keine Mitglieder angelegt</li>";
			}
		?>
				</ul>
			</div>
			<?php
			// Liste aller Usernamen dieser Organisation
			echo '<input type="hidden" id="alleusernamen" name="alleusernamen" value="'.$usernames.'">';
			echo '<input type="hidden" id="alledienstnummern" name="alledienstnummern" value="'.$alledienstnummern.'">';
			?>
		</section>
	</section>
<?php
} //Ende Schleife Organisation für Mitglieder
?>
</section>

<!-- Overlay Importvorschau Anfang -->
<div class="modal fade importpreview" tabindex="-1" role="dialog" aria-labelledby="usermodalheader" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="usermodalheader" style="font-weight:bold;">Importvorschau User</h5>
			</div>
			<form id="userimportform" class="" accept-charset="UTF-8">
				<div class="modal-body">
					<div id="accordion" class="previewtable">
					</div>
					<input type="hidden" id="iDel" name="iDel" value="">
					<input type="hidden" id="iPwUp" name="iPwUp" value="">
					<input type="hidden" id="iOrg" name="iOrg" value="">
					
				
				</div>
				<div class="modal-footer">
					<button type="submit" id="submit" name="import" class="btn-submit btn btn-primary">Import durchführen</button>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- Overlay Importvorschau Ende -->

<!-- User Update Overlay Anfang -->
<div class="modal fade usermodal" tabindex="-1" role="dialog" aria-labelledby="usermodalheader" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title mr-auto" id="settingsmodalheader">User Details:</h5>
				<button type="button" class="btn btn-primary user_modify pb-2 pt-2">Daten bearbeiten</button>
				<button type="button" class="btn btn-success abschliessen user_modify_save ml-2 pb-2 pt-2" title="Änderungen speichern" data-toggle="tooltip" data-placement="bottom" style="display:none;" data-uid="">Änderungen speichern</button>
				<?php if($userlevel <= 3 && is_numeric($userlevel)){ ?>
				<button type="button" class="btn btn-danger user_modify_delete ml-2" title="User löschen" data-toggle="tooltip" data-placement="bottom" data-uid=""><i class='material-icons text-white'>delete_forever</i></button>
				<?php } ?>
			</div>
			<div class="modal-body">
				<div class="form-group row">
					<input type="hidden" class="x_uid" id="uid" value="">
					<input type="hidden" class="x_oid" id="oid" value="">
					<input type="hidden" class="x_pwd_old" value="">
					<input type="hidden" class="x_dienstnummer_old" value="">
					<input type="hidden" class="x_fid" id="fid" value="">
					<label for="name" class="col-sm-3 col-form-label">Name</label>
					<div class="col-sm-9">	
						<input disabled type="text" name="name" class="mb-2 form-control x_user_edit x_name checkJSON" id="name" placeholder="Vollständiger Name des Users" value="">
					</div>
				</div>
				<div class="form-group row">
					<label for="dienstnummer" class="col-sm-3 col-form-label">Dienstnummer</label>
					<div class="col-sm-9">	
						<input disabled type="text" name="dienstnummer" class="mb-2 form-control x_user_edit x_dienstnummer checkJSON dienstnummercheck" id="dienstnummer" placeholder="Dienstnummer" value="">
						<small class="text-danger dienstnummererror" style="display:none;">Diese Dienstnummer ist bereits vergeben bzw. nicht zulässig.</small><br>
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="typ">Typ</label>
					<div class="col-sm-9">
						<select disabled name="typ" id="typ" size="1" class="mb-2 form-control x_user_edit form-control x_typ">
							<option disabled="disabled" value="nur Administratorenrechte">Nur Administratorenrechte</option>
						</select>
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="pause">Pausenzeit in Minuten</label>
					<div class="col-sm-9">
						<input disabled type="text" name="pause" class="mb-2 form-control x_user_edit x_pause checkJSON" id="pause" placeholder="Pausenzeit in Minuten" value="">
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="username">Username</label>
					<div class="col-sm-9">
						<input disabled type="text" name="username" class="mb-2 form-control x_user_edit x_username checkJSON usernamecheck" id="username" placeholder="Username" value="">
						<small class="text-danger loginerror" style="display:none;">Dieser Username ist bereits vergeben.</small><br>
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-3 col-form-label" for="pwd">Passwort</label>
					<div class="col-sm-9">
						<input disabled type="password" name="pwd" class="mb-2 form-control x_user_edit x_pwd checkJSON pwdcheck" id="pwd" aria-describedby="pwdHelp2" value="">
						<small class="form-text PasswortHelp">Feld leer lassen für keine Änderung<br><br>Das Passwort muss folgende Kriterien erfüllen:</small>
						<small class="text-danger ml-4 letter">Kleinbuchstaben</small><br>
						<small class="text-danger ml-4 capital">Großbuchstaben</small><br>
						<small class="text-danger ml-4 number">Zahlen</small><br>
						<small class="text-danger ml-4 length">Mindestens 8 Zeichen</small><br>
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="pwd">Passwort Wiederholung</label>
					<div class="col-sm-9">
						<input disabled type="password" name="repwd" class="mb-2 form-control x_user_edit x_repwd checkJSON repwdcheck" id="repwd" aria-describedby="pwdHelp2" placeholder="Feld leer lassen für keine Änderung" value="">
						<small class="form-text PasswortHelp">Feld leer lassen für keine Änderung</small><br><br>
						<small class="text-danger ml-4 match">Die Passwörter müssen übereinstimmen</small><br>
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="email">E-mailadresse</label>
					<div class="col-sm-9">
						<input disabled type="email" name="email" class="mb-2 form-control x_user_edit x_email checkJSON" id="email" placeholder="E-Mailadresse" value="">
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="einsatzfaehig">Einsatzfähig</label>
					<div class="col-sm-9">
						<select disabled name="einsatzfaehig" id="einsatzfaehig" size="1" class="mb-2 form-control x_user_edit form-control x_einsatzfaehig">
							<option value="1">Ja</option>
							<option value="0">Nein</option>
						</select>
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="bos">BOS Kennung</label>
					<div class="col-sm-9">
						<input disabled bose="text" name="bos" class="mb-2 form-control x_user_edit x_bos checkJSON" id="bos" placeholder="bos" value="">
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="telefon">Telefonnummer</label>
					<div class="col-sm-9 input-group">
						<input disabled type="text" name="telefon" class="form-control x_user_edit x_telefon checkJSON" id="telefon" placeholder="telefon" value="">
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="notfallkontakt">Notfallkontakt</label>
					<div class="col-sm-9">
						<input disabled notfallkontakte="text" name="notfallkontakt" class="mb-2 form-control x_user_edit x_notfallkontakt checkJSON" id="notfallkontakt" placeholder="notfallkontakt" value="">
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="notfallinfo">Notfallinfo</label>
					<div class="col-sm-9">
						<input disabled type="text" name="notfallinfo" class="mb-2 form-control x_user_edit x_notfallinfo checkJSON" id="notfallinfo" placeholder="notfallinfo" value="">
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="kommentar">Kommentar für Einsatzleiter</label>
					<div class="col-sm-9">
						<input disabled type="text" name="kommentar" class="mb-2 form-control x_user_edit x_kommentar checkJSON" id="kommentar" placeholder="kommentar" value="">
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="ausbildungen">Ausbildungen - Werte mit Strichpunkt ; trennen</label>
					<div class="col-sm-9">
						<input disabled type="text" name="ausbildungen" class="mb-2 form-control x_user_edit x_ausbildungen checkJSON" id="ausbildungen" placeholder="Ausbildungen - getrennt mit Strichpunkt ;" value="">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- User Update Overlay Ende-->
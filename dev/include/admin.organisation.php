<!--<section class="orglist float-sm-none float-lg-left col-12" style="display: none;margin-top:35px;">-->
	
	<?php if($userlevel == 0 && is_numeric($userlevel)){ ?>
		<button class="org_add btn btn-secondary float-right mt-2 mb-3 ml-3 mr-3">
			<i class="material-icons rounded-circle color-white">add_circle</i> Organisation hinzufügen
		</button>
	<?php } ?>
	<?php 
	
	//Schleife für das array der Organisationen
	$db_org = $db->prepare("SELECT * FROM organisation");
	$db_org->execute($db_org->errorInfo());
	$org_arr = array();
	$ttoken = $toid = $oidtemp = "";
	while ($reso = $db_org->fetch(PDO::FETCH_ASSOC)){
		$data_org_json = json_decode(substr(string_decrypt($reso['data']), 1, -1));
		$appsettings_json = json_decode($reso['appsettings']);
		$org_arr[$reso["OID"]]["OID"] = $reso["OID"];
		$org_arr[$reso["OID"]]["bezeichnung"] = isset($data_org_json->bezeichnung) ? $data_org_json->bezeichnung : "";
		$org_arr[$reso["OID"]]["kurzname"] = isset($data_org_json->kurzname) ? $data_org_json->kurzname : "";
		$org_arr[$reso["OID"]]["adresse"] = isset($data_org_json->adresse) ? $data_org_json->adresse : "";
		$org_arr[$reso["OID"]]["land"] = isset($data_org_json->land) ? $data_org_json->land : "";
		$org_arr[$reso["OID"]]["bundesland"] = isset($data_org_json->bundesland) ? $data_org_json->bundesland : "";
		$org_arr[$reso["OID"]]["datenschutzbeauftragter"] = isset($data_org_json->datenschutzbeauftragter) ? $data_org_json->datenschutzbeauftragter : "";
		$org_arr[$reso["OID"]]["administrator"] = isset($data_org_json->administrator) ? $data_org_json->administrator : "";
		$org_arr[$reso["OID"]]["ansprechperson"] = isset($data_org_json->ansprechperson) ? $data_org_json->ansprechperson : "";
		$org_arr[$reso["OID"]]["maps"] = json_decode($reso['maps'],true);
		$org_arr[$reso["OID"]]["einheiten"] = json_decode($reso['einheiten'],true);
		$org_arr[$reso["OID"]]["token"] = $reso["token"];
		$org_arr[$reso["OID"]]["usersync"] = $reso["usersync"];
		$org_arr[$reso["OID"]]["orgfreigabe"] = $reso["orgfreigabe"];
		$org_arr[$reso["OID"]]["status"] = $reso["status"];
		$org_arr[$reso["OID"]]["funktionen"] = $reso["funktionen"];
		$org_arr[$reso["OID"]]["aktiv"] = $reso["aktiv"];
		$org_arr[$reso["OID"]]["readposition"] = isset($appsettings_json->readposition) ? $appsettings_json->readposition : "";
		$org_arr[$reso["OID"]]["distance"] = isset($appsettings_json->distance) ? $appsettings_json->distance : "";
		$org_arr[$reso["OID"]]["updateinfo"] = isset($appsettings_json->updateinfo) ? $appsettings_json->updateinfo : "";
		$ttoken .= $reso["token"].",";
		$toid .= $reso["OID"].",";
	}
	
	$db_organisation = $db->prepare("SELECT * FROM organisation ".$oidselect);
	$db_organisation->execute($db_organisation->errorInfo());
	
	while ($result = $db_organisation->fetch(PDO::FETCH_ASSOC)){
		$oidtemp = $result['OID'];
	if($userlevel == 0 || ($userlevel >0 && $oidtemp == $OID) && is_numeric($userlevel)){
		$data_org_json = json_decode(substr(string_decrypt($result['data']), 1, -1));
		
		
	?>
	
	
	
		<section id="<?php echo($oidtemp);?>" class="org_list pt-2 mb-4 bg-white">
			
			<button class="org_bearbeiten float-right btn btn-primary ml-3" data-oid="<?php echo($oidtemp);?>">
				<i class="material-icons color-white">edit</i> Organisation bearbeiten
			</button>
			<button class="org_update org_speichern_<?php echo($oidtemp);?> float-right btn btn-success ml-3" data-oid="<?php echo($oidtemp);?>" data-token="<?php echo $result['token'];?>"  data-id="<?php echo $result['ID'];?>">
				<i class="material-icons color-white">save</i> Änderungen speichern
			</button>
			<div class="clearfix"></div>
			<section class="col-12">	
				<div id="<?php echo($oidtemp);?>_org_details" class="row card" id="infos">
					<div class="card-header" id="infoheading">
						<h5>
						<button class="btn btn-link" data-toggle="collapse" data-target="#infocollapse" aria-expanded="false" aria-controls="infocollapse">
							Allgemeine Informationen</button>
						</h5>
					</div>		
					<div id="infocollapse" class="collapse w-100" aria-labelledby="infoheading" data-parent="#<?php echo($oidtemp);?>_org_details">
						<div class="form-group d-flex pl-3">
							<label for="Bezeichnung_<?php echo($oidtemp);?>">Bezeichnung</label>
							<input disabled type="text" name="Bezeichnung_<?php echo($oidtemp);?>" class="form-control checkJSON <?php if(($userlevel == 3 || $userlevel == 0) && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" id="Bezeichnung_<?php echo($oidtemp);?>" placeholder="Vollständiger Name der Organisation" value="<?php echo isset($data_org_json->bezeichnung) ? $data_org_json->bezeichnung : "";?>">
						</div>
						<div class="form-group d-flex pl-3">
							<label for="Kürzel_<?php echo($oidtemp);?>">Kürzel</label>
							<input disabled type="text" name="Kürzel_<?php echo($oidtemp);?>" class="form-control checkJSON <?php if(($userlevel == 3 || $userlevel == 0) && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" id="Kürzel_<?php echo($oidtemp);?>" placeholder="Kurznamen der Organisation" value="<?php echo isset($data_org_json->kurzname) ? $data_org_json->kurzname : "";?>">
						</div>
						<div class="form-group d-flex pl-3">
							<label for="Anschrift_<?php echo($oidtemp);?>">Anschrift</label>
							<input disabled type="text" name="Anschrift_<?php echo($oidtemp);?>" class="form-control checkJSON <?php if(($userlevel == 3 || $userlevel == 0) && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" id="Anschrift_<?php echo($oidtemp);?>" placeholder="Anschrift der Organisation" value="<?php echo isset($data_org_json->adresse) ? $data_org_json->adresse : "";?>">
						</div>
						<div class="form-group d-flex pl-3">
							<label for="Notrufnummer_<?php echo($oidtemp);?>">Notrufnummer (für Handzettel)</label>
							<input disabled type="text" name="Notrufnummer_<?php echo($oidtemp);?>" class="form-control checkJSON <?php if(($userlevel == 3 || $userlevel == 0) && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" id="Notrufnummer_<?php echo($oidtemp);?>" placeholder="Notrufnummer der Organisation" value="<?php echo isset($data_org_json->notrufnummer) ? $data_org_json->notrufnummer : "";?>">
						</div>
						<div class="form-group d-flex pl-3">
							<label for="Land_<?php echo($oidtemp);?>">Land</label>
							<select disabled name="Land" id="Land" size="1" class="form-control laenderwahl <?php if(($userlevel == 3 || $userlevel == 0) && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>">
								<option value="">Land auswählen</option>';
								<?php
								foreach($org_land as $land){
									echo '<option value="'.$land.'" '.((isset($data_org_json->land) && $data_org_json->land == $land) ? "selected" : "").'>'.$land.'</option>';
								}
								?>
							</select>
						</div>
						<div class="form-group d-flex pl-3">
							<label for="Bundesland_<?php echo($oidtemp);?>">Bundesland</label>
							<select disabled name="Bundesland" id="Bundesland" size="1" class="form-control bundeslaenderwahl">
							<?php
								foreach($org_land as $land){
									foreach($org_bland[$land] as $bland){
										echo "<option class='Land_".$land."' value='".$bland."'".((isset($data_org_json->bundesland) && $data_org_json->bundesland == $bland) ? 'selected' : '').">".$bland."</option>";
									}
								}
							?>
							</select>
						</div>
						<div class="form-group d-flex pl-3">
							<label for="Ansprechperson_<?php echo($oidtemp);?>">Ansprechperson</label>
							<input disabled type="text" name="Ansprechperson_<?php echo($oidtemp);?>" class="form-control checkJSON <?php if(($userlevel == 3 || $userlevel == 0) && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" id="Ansprechperson_<?php echo($oidtemp);?>" placeholder="Ansprechperson: Name, Adresse, Telefonnummer, Emailadresse" value="<?php echo isset($data_org_json->ansprechperson) ? $data_org_json->ansprechperson : "";?>">
						</div>
						<div class="form-group d-flex pl-3">
							<label for="Datenschutzbeauftragter_<?php echo($oidtemp);?>">Datenschutzbeauftragter</label>
							<input disabled type="text" name="Datenschutzbeauftragter_<?php echo($oidtemp);?>" class="form-control checkJSON <?php if(($userlevel == 3 || $userlevel == 0) && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" id="Datenschutzbeauftragter_<?php echo($oidtemp);?>" placeholder="Datenschutzbeauftragter: Name, Adresse, Telefonnummer, Emailadresse" value="<?php echo isset($data_org_json->datenschutzbeauftragter) ? $data_org_json->datenschutzbeauftragter : "";?>">
						</div>
						<div class="form-group d-flex pl-3">
							<label for="Administrator_<?php echo($oidtemp);?>">Administrator</label>
							<input disabled type="text" name="Administrator_<?php echo($oidtemp);?>" class="form-control checkJSON <?php if(($userlevel == 3 || $userlevel == 0) && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" id="Administrator_<?php echo($oidtemp);?>" placeholder="Administrator: Name, Adresse, Telefonnummer, Emailadresse" value="<?php echo isset($data_org_json->administrator) ? $data_org_json->administrator : "";?>">
						</div>
					</div>
				</div>
				<div id="<?php echo($oidtemp);?>_tech_info" class="row card" id="infos">
					<div class="card-header" id="techheading">
						<h5>
							<button class="btn btn-link" data-toggle="collapse" data-target="#techcollapse" aria-expanded="false" aria-controls="techcollapse">
							Technische Informationen</button>
						</h5>
					</div>	
					<div id="techcollapse" class="collapse w-100" aria-labelledby="techheading" data-parent="#<?php echo($oidtemp);?>_tech_info">	
						<div class="form-group d-flex pl-3">
							<label for="OID_<?php echo($oidtemp);?>">Organisations-ID</label>
							<input disabled type="text" name="OID_<?php echo($oidtemp);?>" class="form-control checkJSON <?php if($userlevel == 0 && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" id="OID_<?php echo($oidtemp);?>" placeholder="Einzigartige Organisations-ID (Buchstabe)" value="<?php echo($oidtemp);?>">
						</div>
					
						<div class="form-group d-flex pl-3">
							<label for="Token_<?php echo($oidtemp);?>">Organisations Token</label>
							<input disabled type="text" name="Token_<?php echo($oidtemp);?>" class="form-control checkJSON <?php if($userlevel == 0 && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" id="Token_<?php echo($oidtemp);?>" placeholder="Einzigartige Organisations-Token (8stellig) - wird automatisch erzeugt wenn leer" value="<?php echo $result['token'];?>">
						</div>
					
						<div class="form-group d-flex pl-3">
							<label for="Usersync_<?php echo($oidtemp);?>">User Sync eingerichtet</label>
							<input disabled type="checkbox" name="Usersync_<?php echo($oidtemp);?>" class=" <?php if($userlevel == 0 && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" id="Usersync_<?php echo($oidtemp);?>" <?php if($result['usersync'] == 1){echo "checked";} ?> >
						</div>
						<div class="form-group pl-3">
							<div class="font-weight-bold ">Software-Lizenzen: </div>
							<ul>
							<?php
							
							//$db_lizenzen = $db->prepare("SELECT * FROM lizenzen WHERE OID = '".$oidtemp."'");
							$db_lizenzen = $db->prepare("SELECT Laufzeit, workstations.bezeichnung
														FROM lizenzen
														LEFT JOIN workstations
														ON lizenzen.SID = workstations.SID WHERE lizenzen.OID = '".$oidtemp."'");
							$db_lizenzen->execute($db_lizenzen->errorInfo());
							while ($result_liz = $db_lizenzen->fetch(PDO::FETCH_ASSOC)){
								?>
								<li><?php echo $result_liz['bezeichnung']." bis ".$result_liz['Laufzeit'];?></li>
							<?php } ?>
							</ul>
						</div>	
					</div>	
				</div>	
				<!-- Organisationsfreigabe -->
				<div id="<?php echo($oidtemp);?>_freigabe" class="row card" id="infos">
					<div class="card-header" id="freigabeeading">
						<h5>
							<button class="btn btn-link" data-toggle="collapse" data-target="#freigabecollapse" aria-expanded="false" aria-controls="freigabecollapse">
							Organisationfreigabe</button>
						</h5>
					</div>
					<div id="freigabecollapse" class="collapse w-100" aria-labelledby="freigabeheading" data-parent="#<?php echo($oidtemp);?>_tech_info">
						<div class="form-group pl-3">
							<div class="infotext">Folgende Organisationen können mit dieser Organisation zusammenarbeiten und erhalten im Einsatz Zugriff auf alle Mitgliederdaten:</div>
							<ul class="members list-group">
								<?php
								//Schleife für das array der Organisationen
								$db_org = $db->prepare("SELECT * FROM organisation WHERE aktiv = 1");
								$db_org->execute($db_org->errorInfo());
								$org_arr_check = array();
								$n_org = $bundesland_sort = 0;
								while ($reso_check = $db_org->fetch(PDO::FETCH_ASSOC)){
									$data_org_json_check = json_decode(substr(string_decrypt($reso_check['data']), 1, -1));
									
									$org_arr_check[$reso_check["OID"]]["OID"] = $reso_check["OID"];
									$org_arr_check[$reso_check["OID"]]["bezeichnung"] = isset($data_org_json_check->bezeichnung) ? $data_org_json_check->bezeichnung : "";
									$org_arr_check[$reso_check["OID"]]["kurzname"] = isset($data_org_json_check->kurzname) ? $data_org_json_check->kurzname : "";
									$org_arr_check[$reso_check["OID"]]["adresse"] = isset($data_org_json_check->adresse) ? $data_org_json_check->adresse : "";
									$org_arr_check[$reso_check["OID"]]["land"] = isset($data_org_json_check->land) ? $data_org_json_check->land : "Z - kein Land gewählt";
									$org_arr_check[$reso_check["OID"]]["bundesland"] = isset($data_org_json_check->bundesland) ? $data_org_json_check->bundesland : "Z - kein Bundesland gewählt";
									if(isset($data_org_json_check->land) && isset($data_org_json_check->bundesland)){
										$bundesland_sort = $data_org_json_check->land." | ".$data_org_json_check->bundesland;
									} elseif(isset($data_org_json_check->land) && !isset($data_org_json_check->bundesland)){
										$bundesland_sort = $data_org_json_check->land;
									} elseif(!isset($data_org_json_check->land) && isset($data_org_json_check->bundesland)){
										$bundesland_sort = $data_org_json_check->bundesland;
									} else { $bundesland_sort = "Z - kein Land gewählt"; }
									$org_arr_check[$reso_check["OID"]]["bundesland_sort"] = $bundesland_sort;
									$org_arr_check[$reso_check["OID"]]["datenschutzbeauftragter"] = isset($data_org_json_check->datenschutzbeauftragter) ? $data_org_json_check->datenschutzbeauftragter : "";
									$org_arr_check[$reso_check["OID"]]["administrator"] = isset($data_org_json_check->administrator) ? $data_org_json_check->administrator : "";
									$org_arr_check[$reso_check["OID"]]["ansprechperson"] = isset($data_org_json_check->ansprechperson) ? $data_org_json_check->ansprechperson : "";
									$org_arr_check[$reso_check["OID"]]["token"] = $reso_check["token"];
									$org_arr_check[$reso_check["OID"]]["usersync"] = $reso_check["usersync"];
									$org_arr_check[$reso_check["OID"]]["orgfreigabe"] = $reso_check["orgfreigabe"];
									$org_arr_check[$reso_check["OID"]]["aktiv"] = $reso_check["aktiv"];
									$n_org++;
								}
								if($n_org > 0) {
									//Sortieren vorbereiten
									$name = array();
									foreach ($org_arr_check as $nr => $inhalt)
									{
										$name[$nr]  = strtolower( $inhalt['bundesland_sort'] );
									}
									//Sortieren
									array_multisort($name, SORT_ASC, $org_arr_check);
							
								}
								
								$k = 0;
								$bundesland_sort = "";
								//JSON mit den von dieser Organisation freigegebenen Organisationen
								$jtemp = json_decode($org_arr_check[$oidtemp]["orgfreigabe"],true); 
								if($n_org > 0) { //Wenn keine weiteren Organisationen angelegt sind.
									foreach($org_arr_check as $org => $val){
										if($val['bundesland_sort'] != $bundesland_sort){
												echo "<li class='list-group-item'>".$val['bundesland_sort']."</li>";
											}
										if($oidtemp != $val["OID"]){
										//Checken ob die OID von der Organisation freigegeben wurde
										$jtemp2 = json_decode($org_arr_check[$val["OID"]]["orgfreigabe"],true); //JSON mit den von dieser Organisation freigegebenen Organisationen
										$ccol = "";
										if(isset($jtemp[$val["OID"]])){
											$ctemp = "checked"; //Für Checkbox - eigene Freigabe
											if(isset($jtemp2[$oidtemp])){ 	
												$ccol = "#387002"; //Farbe wenn für Organisation freigegeben und von Organisation freigegeben
												$ctext = $org_arr_check[$oidtemp]["kurzname"]." und  ".$org_arr_check[$val["OID"]]["kurzname"]." tauschen Daten aus.";
											} else {
												$ccol = "#ff5722"; //Farbe wenn für Organisation freigegeben und von Organisation nicht freigegeben
												$ctext = "Der Datenaustausch erfolg zu  ".$org_arr_check[$val["OID"]]["kurzname"].", Sie erhalten aber keine Daten.";
											}
										} else {
											$ctemp = "";
											if(isset($jtemp2[$oidtemp])){ 	
												$ccol = "#00838f"; //Farbe wenn für Organisation nicht freigegeben und von Organisation freigegeben
												$ctext = "Sie teilen ihre Daten nicht mit ".$org_arr_check[$val["OID"]]["kurzname"].", erhalten aber von dieser Organisation Daten.";
											} else {
												$ccol = "#ccc"; //Farbe wenn für Organisation freigegeben und von Organisation nicht freigegeben
												$ctext = "Es werden keine Daten zwischen ".$org_arr_check[$oidtemp]["kurzname"]." und ".$org_arr_check[$val["OID"]]["kurzname"]." getauscht.";
											}
										}
										?>
											<li class="list-group-item"><input disabled <?php echo $ctemp; ?> type="checkbox" id="<?php echo "orgfreigabe-".$oidtemp."-".$k."";?>" value="<?php echo $val["OID"];?>" class="input_oid_<?php echo $oidtemp;?>"><span class="material-icons" title="<?php echo $ctext; ?>" data-toggle="tooltip" data-placement="bottom"  style="color:<?php echo $ccol; ?>;width:25px;">swap_horizontal_circle</span> <?php echo $val["kurzname"];?></li>
										<?php
										$k++;
										}
										$bundesland_sort = $val['bundesland_sort'];
									}
								}?>
					
							</ul>
						</div>
					</div>
				</div>
				<!-- Organisationslogo -->
				<div id="<?php echo($oidtemp);?>_logo" class="row card" id="infos">
					<div class="card-header" id="logoheading">
						<h5>
							<button class="btn btn-link" data-toggle="collapse" data-target="#logocollapse" aria-expanded="false" aria-controls="logocollapse">
							Organisationslogo</button>
						</h5>
					</div>
					<div id="logocollapse" class="collapse w-100" aria-labelledby="logoheading" data-parent="#<?php echo($oidtemp);?>_logo">
						<div class="form-group pl-3">
						<button class="<?php if(($userlevel == 3 || $userlevel == 0) && is_numeric($userlevel)){ echo "btn-logoupload";}?>" data-oid="<?php echo($oidtemp);?>">
							<?php if(file_exists("orglogos/".$oidtemp.".png")){ ?>
								<img id="<?php echo($oidtemp);?>logopreview" src="orglogos/<?php echo($oidtemp);?>.png" style="width:350px;height:350px;">
							<?php } else { ?>
								<img id="<?php echo($oidtemp);?>logopreview" src="orglogos/logoupload.png" style="width:350px;height:350px;">
							<?php } ?>	
							</button>
						</div>
					</div>
				</div>
				
				<!-- Texteditor -->
				<div id="<?php echo($oidtemp);?>_wegsuche" class="row card" id="infos">
					<div class="card-header" id="wegsucheheading">
						<h5>
							<button class="btn btn-link" data-toggle="collapse" data-target="#wegsuchecollapse" aria-expanded="false" aria-controls="wegsuchecollapse">
							Beschreibung der Suchart Wegsuche</button>
						</h5>
					</div>
					<div id="wegsuchecollapse" class="collapse w-100" aria-labelledby="wegsucheheading" data-parent="#<?php echo($oidtemp);?>_wegsuche">
						<div class="form-group pl-3">
							<form>
								<input id="<?php echo $oidtemp."_trix_weg";?>" type="hidden" name="content" value="<?php echo $result['suchew'];?>">
								<trix-editor input="<?php echo $oidtemp."_trix_weg";?>"></trix-editor>
							</form>
						</div>
					</div>
				</div>
				<div id="<?php echo($oidtemp);?>_flaeche" class="row card" id="infos">
					<div class="card-header" id="flaecheheading">
						<h5>
							<button class="btn btn-link" data-toggle="collapse" data-target="#flaechecollapse" aria-expanded="false" aria-controls="flaechecollapse">
							Beschreibung der Suchart Flächensuche</button>
						</h5>
					</div>
					<div id="flaechecollapse" class="collapse w-100" aria-labelledby="flaecheheading" data-parent="#<?php echo($oidtemp);?>_flaeche">
						<div class="form-group pl-3">
							<form>
								<input id="<?php echo $oidtemp."_trix_flach";?>" type="hidden" name="content" value="<?php echo $result['suchef'];?>">
								<trix-editor input="<?php echo $oidtemp."_trix_flach";?>"></trix-editor>
							</form>
						</div>
					</div>
				</div>
				<div id="<?php echo($oidtemp);?>_punkt" class="row card" id="infos">
					<div class="card-header" id="punktheading">
						<h5>
							<button class="btn btn-link" data-toggle="collapse" data-target="#punktcollapse" aria-expanded="false" aria-controls="punktcollapse">
							Beschreibung der Suchart Punktsuche</button>
						</h5>
					</div>
					<div id="punktcollapse" class="collapse w-100" aria-labelledby="punktheading" data-parent="#<?php echo($oidtemp);?>_punkt">
						<div class="form-group pl-3">
							<form>
								<input id="<?php echo $oidtemp."_trix_punkt";?>" type="hidden" name="content" value="<?php echo $result['suchep'];?>">
								<trix-editor input="<?php echo $oidtemp."_trix_punkt";?>"></trix-editor>
							</form>
						</div>
					</div>
				</div>
				<div id="<?php echo($oidtemp);?>_mantrail" class="row card" id="infos">
					<div class="card-header" id="mantrailheading">
						<h5>
							<button class="btn btn-link" data-toggle="collapse" data-target="#mantrailcollapse" aria-expanded="false" aria-controls="mantrailcollapse">
							Beschreibung der Suchart Mantrailing</button>
						</h5>
					</div>
					<div id="mantrailcollapse" class="collapse w-100" aria-labelledby="mantrailheading" data-parent="#<?php echo($oidtemp);?>_mantrail">
						<div class="form-group pl-3">
							<form>
								<input id="<?php echo $oidtemp."_trix_mantrail";?>" type="hidden" name="content" value="<?php echo $result['suchem'];?>">
								<trix-editor input="<?php echo $oidtemp."_trix_mantrail";?>"></trix-editor>
							</form>
						</div>
					</div>
				</div>
				
				<!-- Statustabelle -->
				<?php 
				$jtempS = $jt = "";
				//JSON mit den Funktionen
				$jtempS = json_decode($org_arr[$oidtemp]["status"],true);
									
				?>
				<div id="<?php echo($oidtemp);?>_status" class="row card" id="infos">
					<div class="card-header" id="statusheading">
						<h5>
							<button class="btn btn-link" data-toggle="collapse" data-target="#statuscollapse" aria-expanded="false" aria-controls="statuscollapse">
							Statusliste</button>
						</h5>
					</div>
					<div id="statuscollapse" class="collapse w-100" aria-labelledby="statusheading" data-parent="#<?php echo($oidtemp);?>_status">
						<div class="form-group pl-3">
							<div class="infotext">Hier können die Einstellungen für die Verwendung von Statusmeldungen definiert werden.
								<ul>
									<li><b>Schnittstellen Text:</b> Jener Wert, der bei Verwendung der BOS-Schnittstelle für den jeweiligen Status übergeben wird</li>
									<li><b>Verfügbar:</b> Legt fest, ob dieser Status in der eTrax | rescue APP verfügbar ist</li>
									<li><b>Tracking:</b> Legt fest, ob und mit welcher Genauigkeit das Tracking in der eTrax | rescue APP bei diesem Status aktiviert ist</li>
									<li><b>Dokumentieren:</b> Legt fest, ob das die Übermittlung dieses Status im Ereignisprotokoll geschrieben wird. Die Dokumentation ist nur möglich, wenn der Status auch verfügbar ist.</li>
								</ul>
								<b>Anmerkung zum Tracking: </b>Höhere Genauigkeit bewirkt einen höheren Stromverbrauch am Endgerät.
							</div>
						</div>
						<div>
							<?php //Werte für die Tracking Genauigkeit
								$tracking_t = array("0" => "kein Tracking", "1" => "&plusmn; 10km", "2" => "&plusmn; 100m", "3" => "&plusmn; 3m");
							?>
							<table>
								<thead>
									<tr>
										<th>#</th>
										<th>Status</th>
										<th style="width:50%;">Schnittstellen Text (BOS Schnittstelle)</th>
										<th>Verfügbar</th>
										<th>Tracking</th>
										<th>Dokumentieren</th>
									</tr>
								</thead>
								<tbody>
									<tr style="background-color:#C0D4DF;">
										<td>1</td>
										<td>Anmeldung</td>
										<td><input style="width:100%;" disabled type="text" id="<?php echo $oidtemp;?>_statustext_1" class="form-control checkJSON <?php if($userlevel <= 3 && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" placeholder="Wert der an der Schnittstelle übergeben wird" value="<?php if(isset($jtempS["all"]["1"]["text"])){echo $jtempS["all"]["1"]["text"]; }?>"></td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usestatus_1";?>" value="" <?php if(isset($jtempS["all"]["1"]["use"]) && $jtempS["all"]["1"]["use"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usestatus"></input></td>
										<td class="text-center">
											<select disabled id="<?php echo $oidtemp."_usetracking_1";?>" size="1"  class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usetracking">
													<?php
													foreach($tracking_t as $tkey => $tval){
														echo '<option value="'.$tkey.'" '.((isset($jtempS["all"]["1"]["tracking"]) && $jtempS["all"]["1"]["tracking"] == $tkey && is_numeric($jtempS["all"]["1"]["tracking"])) ? "selected" :  (((!isset($jtempS["all"]["1"]["tracking"]) || !is_numeric($jtempS["all"]["1"]["tracking"])) && $tkey == "0") ? "selected" : "")).'>'.$tval.'</option>';
													}
													?>
											</select>
										</td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usedoku_1";?>" value="" <?php if(isset($jtempS["all"]["1"]["doku"]) && $jtempS["all"]["1"]["doku"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usedoku"></input></td>
									</tr>
									<tr style="background-color:#C0D4DF;">
										<td>2</td>
										<td>In Anreise</td>
										<td><input style="width:100%;" disabled type="text" id="<?php echo $oidtemp;?>_statustext_2" class="form-control checkJSON <?php if($userlevel <= 3 && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" placeholder="Wert der an der Schnittstelle übergeben wird" value="<?php if(isset($jtempS["all"]["2"]["text"])){echo $jtempS["all"]["2"]["text"]; }?>"></td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usestatus_2";?>" value="" <?php if(isset($jtempS["all"]["2"]["use"]) && $jtempS["all"]["2"]["use"] == true){echo "checked";} ; ?>  class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usestatus"></input></td>
										<td class="text-center">
											<select disabled id="<?php echo $oidtemp."_usetracking_2";?>" size="1"  class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usetracking">
													<?php
													foreach($tracking_t as $tkey => $tval){
														echo '<option value="'.$tkey.'" '.((isset($jtempS["all"]["2"]["tracking"]) && $jtempS["all"]["2"]["tracking"] == $tkey && is_numeric($jtempS["all"]["2"]["tracking"])) ? "selected" : (((!isset($jtempS["all"]["2"]["tracking"]) || !is_numeric($jtempS["all"]["2"]["tracking"])) && $tkey == "2") ? "selected" : "")).'>'.$tval.'</option>';
													}
													?>
											</select>
										</td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usedoku_2";?>" value="" <?php if(isset($jtempS["all"]["2"]["doku"]) && $jtempS["all"]["2"]["doku"] == true){echo "checked";} ; ?>  class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usedoku"></input></td>
									</tr>
									<tr style="background-color:#C0D4DF;">
										<td>3</td>
										<td>Am Berufungsort</td>
										<td><input style="width:100%;" disabled type="text" id="<?php echo $oidtemp;?>_statustext_3" class="form-control checkJSON <?php if($userlevel <= 3 && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" placeholder="Wert der an der Schnittstelle übergeben wird" value="<?php if(isset($jtempS["all"]["3"]["text"])){echo $jtempS["all"]["3"]["text"]; }?>"></td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usestatus_3";?>" value="" <?php if(isset($jtempS["all"]["3"]["use"]) && $jtempS["all"]["3"]["use"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usestatus"></input></td>
										<td class="text-center">
											<select disabled id="<?php echo $oidtemp."_usetracking_3";?>" size="1"  class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usetracking">
													<?php
													foreach($tracking_t as $tkey => $tval){
														echo '<option value="'.$tkey.'" '.((isset($jtempS["all"]["3"]["tracking"]) && $jtempS["all"]["3"]["tracking"] == $tkey && is_numeric($jtempS["all"]["3"]["tracking"])) ? "selected" : (((!isset($jtempS["all"]["3"]["tracking"]) || !is_numeric($jtempS["all"]["3"]["tracking"])) && $tkey == "2") ? "selected" : "")).'>'.$tval.'</option>';
													}
													?>
											</select>
										</td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usedoku_3";?>" value="" <?php if(isset($jtempS["all"]["3"]["doku"]) && $jtempS["all"]["3"]["doku"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usedoku"></input></td>
									</tr>
									<tr>
										<td>4</td>
										<td>Ins Suchgebiet</td>
										<td><input style="width:100%;" disabled type="text" id="<?php echo $oidtemp;?>_statustext_4" class="form-control checkJSON <?php if($userlevel <= 3 && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" placeholder="Wert der an der Schnittstelle übergeben wird" value="<?php if(isset($jtempS["all"]["4"]["text"])){echo $jtempS["all"]["4"]["text"]; }?>"></td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usestatus_4";?>" value="" <?php if(isset($jtempS["all"]["4"]["use"]) && $jtempS["all"]["4"]["use"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usestatus"></input></td>
										<td class="text-center">
											<select disabled id="<?php echo $oidtemp."_usetracking_4";?>" size="1"  class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usetracking">
													<?php
													foreach($tracking_t as $tkey => $tval){
														echo '<option value="'.$tkey.'" '.((isset($jtempS["all"]["4"]["tracking"]) && $jtempS["all"]["4"]["tracking"] == $tkey && is_numeric($jtempS["all"]["4"]["tracking"])) ? "selected" : (((!isset($jtempS["all"]["4"]["tracking"]) || !is_numeric($jtempS["all"]["4"]["tracking"])) && $tkey == "3") ? "selected" : "")).'>'.$tval.'</option>';
													}
													?>
											</select>
										</td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usedoku_4";?>" value="" <?php if(isset($jtempS["all"]["4"]["doku"]) && $jtempS["all"]["4"]["doku"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usedoku"></input></td>
									</tr>
									<tr>
										<td>5</td>
										<td>Beginn Suche</td>
										<td><input style="width:100%;" disabled type="text" id="<?php echo $oidtemp;?>_statustext_5" class="form-control checkJSON <?php if($userlevel <= 3 && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" placeholder="Wert der an der Schnittstelle übergeben wird" value="<?php if(isset($jtempS["all"]["5"]["text"])){echo $jtempS["all"]["5"]["text"]; }?>"></td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usestatus_5";?>" value="" <?php if(isset($jtempS["all"]["5"]["use"]) && $jtempS["all"]["5"]["use"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usestatus"></input></td>
										<td class="text-center">
											<select disabled id="<?php echo $oidtemp."_usetracking_5";?>" size="1"  class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usetracking">
													<?php
													foreach($tracking_t as $tkey => $tval){
														echo '<option value="'.$tkey.'" '.((isset($jtempS["all"]["5"]["tracking"]) && $jtempS["all"]["5"]["tracking"] == $tkey && is_numeric($jtempS["all"]["5"]["tracking"])) ? "selected" : (((!isset($jtempS["all"]["5"]["tracking"]) || !is_numeric($jtempS["all"]["5"]["tracking"])) && $tkey == "3") ? "selected" : "")).'>'.$tval.'</option>';
													}
													?>
											</select>
										</td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usedoku_5";?>" value="" <?php if(isset($jtempS["all"]["5"]["doku"]) && $jtempS["all"]["5"]["doku"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usedoku"></input></td>
									</tr>
									<tr>
										<td>6</td>
										<td>Ende Suche</td>
										<td><input style="width:100%;" disabled type="text" id="<?php echo $oidtemp;?>_statustext_6" class="form-control checkJSON <?php if($userlevel <= 3 && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" placeholder="Wert der an der Schnittstelle übergeben wird" value="<?php if(isset($jtempS["all"]["6"]["text"])){echo $jtempS["all"]["6"]["text"]; }?>"></td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usestatus_6";?>" value="" <?php if(isset($jtempS["all"]["6"]["use"]) && $jtempS["all"]["6"]["use"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usestatus"></input></td>
										<td class="text-center">
											<select disabled id="<?php echo $oidtemp."_usetracking_6";?>" size="1"  class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usetracking">
													<?php
													foreach($tracking_t as $tkey => $tval){
														echo '<option value="'.$tkey.'" '.((isset($jtempS["all"]["6"]["tracking"]) && $jtempS["all"]["6"]["tracking"] == $tkey && is_numeric($jtempS["all"]["6"]["tracking"])) ? "selected" : (((!isset($jtempS["all"]["6"]["tracking"]) || !is_numeric($jtempS["all"]["6"]["tracking"])) && $tkey == "3") ? "selected" : "")).'>'.$tval.'</option>';
													}
													?>
											</select>
										</td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usedoku_6";?>" value="" <?php if(isset($jtempS["all"]["6"]["doku"]) && $jtempS["all"]["6"]["doku"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usedoku"></input></td>
									</tr>
									<tr>
										<td>7</td>
										<td>Warten auf Transport</td>
										<td><input style="width:100%;" disabled type="text" id="<?php echo $oidtemp;?>_statustext_7" class="form-control checkJSON <?php if($userlevel <= 3 && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" placeholder="Wert der an der Schnittstelle übergeben wird" value="<?php if(isset($jtempS["all"]["7"]["text"])){ echo $jtempS["all"]["7"]["text"]; } ?>"></td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usestatus_7";?>" value="" <?php if(isset($jtempS["all"]["7"]["use"]) && $jtempS["all"]["7"]["use"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usestatus"></input></td>
										<td class="text-center">
											<select disabled id="<?php echo $oidtemp."_usetracking_7";?>" size="1"  class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usetracking">
													<?php
													foreach($tracking_t as $tkey => $tval){
														echo '<option value="'.$tkey.'" '.((isset($jtempS["all"]["7"]["tracking"]) && $jtempS["all"]["7"]["tracking"] == $tkey && is_numeric($jtempS["all"]["7"]["tracking"])) ? "selected" : (((!isset($jtempS["all"]["7"]["tracking"]) || !is_numeric($jtempS["all"]["7"]["tracking"])) && $tkey == "3") ? "selected" : "")).'>'.$tval.'</option>';
													}
													?>
											</select>
										</td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usedoku_7";?>" value="" <?php if(isset($jtempS["all"]["7"]["doku"]) && $jtempS["all"]["7"]["doku"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usedoku"></input></td>
									</tr>
									<tr>
										<td>8</td>
										<td>Rückweg zur Einsatzleitung</td>
										<td><input style="width:100%;" disabled type="text" id="<?php echo $oidtemp;?>_statustext_8" class="form-control checkJSON <?php if($userlevel <= 3 && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" placeholder="Wert der an der Schnittstelle übergeben wird" value="<?php if(isset($jtempS["all"]["8"]["text"])){ echo $jtempS["all"]["8"]["text"]; }?>"></td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usestatus_8";?>" value="" <?php if(isset($jtempS["all"]["8"]["use"]) && $jtempS["all"]["8"]["use"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usestatus"></input></td>
										<td class="text-center">
											<select disabled id="<?php echo $oidtemp."_usetracking_8";?>" size="1"  class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usetracking">
													<?php
													foreach($tracking_t as $tkey => $tval){
														echo '<option value="'.$tkey.'" '.((isset($jtempS["all"]["8"]["tracking"]) && $jtempS["all"]["8"]["tracking"] == $tkey && is_numeric($jtempS["all"]["8"]["tracking"])) ? "selected" : (((!isset($jtempS["all"]["8"]["tracking"]) || !is_numeric($jtempS["all"]["8"]["tracking"])) && $tkey == "3") ? "selected" : "")).'>'.$tval.'</option>';
													}
													?>
											</select>
										</td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usedoku_8";?>" value="" <?php if(isset($jtempS["all"]["8"]["doku"]) && $jtempS["all"]["8"]["doku"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usedoku"></input></td>
									</tr>
									<tr style="background-color:#C0D4DF;">
										<td>9</td>
										<td>Pause</td>
										<td><input style="width:100%;" disabled type="text" id="<?php echo $oidtemp;?>_statustext_9" class="form-control checkJSON <?php if($userlevel <= 3 && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" placeholder="Wert der an der Schnittstelle übergeben wird" value="<?php if(isset($jtempS["all"]["9"]["text"])){ echo $jtempS["all"]["9"]["text"]; }?>"></td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usestatus_9";?>" value="" <?php if(isset($jtempS["all"]["9"]["use"]) && $jtempS["all"]["9"]["use"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usestatus"></input></td>
										<td class="text-center">
											<select disabled id="<?php echo $oidtemp."_usetracking_9";?>" size="1"  class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usetracking">
													<?php
													foreach($tracking_t as $tkey => $tval){
														echo '<option value="'.$tkey.'" '.((isset($jtempS["all"]["9"]["tracking"]) && $jtempS["all"]["9"]["tracking"] == $tkey && is_numeric($jtempS["all"]["9"]["tracking"])) ? "selected" : (((!isset($jtempS["all"]["9"]["tracking"]) || !is_numeric($jtempS["all"]["9"]["tracking"])) && $tkey == "0") ? "selected" : "")).'>'.$tval.'</option>';
													}
													?>
											</select>
										</td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usedoku_9";?>" value="" <?php if(isset($jtempS["all"]["9"]["doku"]) && $jtempS["all"]["9"]["doku"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usedoku"></input></td>
									</tr>
									<tr style="background-color:#C0D4DF;">
										<td>10</td>
										<td>Am Heimweg</td>
										<td><input style="width:100%;" disabled type="text" id="<?php echo $oidtemp;?>_statustext_10" class="form-control checkJSON <?php if($userlevel <= 3 && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" placeholder="Wert der an der Schnittstelle übergeben wird" value="<?php if(isset($jtempS["all"]["10"]["text"])){echo $jtempS["all"]["10"]["text"]; }?>"></td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usestatus_10";?>" value="" <?php if(isset($jtempS["all"]["10"]["use"]) && $jtempS["all"]["10"]["use"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usestatus"></input></td>
										<td class="text-center">
											<select disabled id="<?php echo $oidtemp."_usetracking_10";?>" size="1"  class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usetracking">
													<?php
													foreach($tracking_t as $tkey => $tval){
														echo '<option value="'.$tkey.'" '.((isset($jtempS["all"]["10"]["tracking"]) && $jtempS["all"]["10"]["tracking"] == $tkey && is_numeric($jtempS["all"]["10"]["tracking"])) ? "selected" : (((!isset($jtempS["all"]["10"]["tracking"]) || !is_numeric($jtempS["all"]["10"]["tracking"])) && $tkey == "2") ? "selected" : "")).'>'.$tval.'</option>';
													}
													?>
											</select>
										</td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usedoku_10";?>" value="" <?php if(isset($jtempS["all"]["10"]["doku"]) && $jtempS["all"]["10"]["doku"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usedoku"></input></td>
									</tr>
									<tr style="background-color:#C0D4DF;">
										<td>11</td>
										<td>Abmeldung</td>
										<td><input style="width:100%;" disabled type="text" id="<?php echo $oidtemp;?>_statustext_11" class="form-control checkJSON <?php if($userlevel <= 3 && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" placeholder="Wert der an der Schnittstelle übergeben wird" value="<?php if(isset($jtempS["all"]["11"]["text"])){echo $jtempS["all"]["11"]["text"]; }?>"></td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usestatus_11";?>" value="" <?php if(isset($jtempS["all"]["11"]["use"]) && $jtempS["all"]["11"]["use"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usestatus"></input></td>
										<td class="text-center">
											<select disabled id="<?php echo $oidtemp."_usetracking_11";?>" size="1"  class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usetracking">
													<?php
													foreach($tracking_t as $tkey => $tval){
														echo '<option value="'.$tkey.'" '.((isset($jtempS["all"]["11"]["tracking"]) && $jtempS["all"]["11"]["tracking"] == $tkey && is_numeric($jtempS["all"]["11"]["tracking"])) ? "selected" : (((!isset($jtempS["all"]["11"]["tracking"]) || !is_numeric($jtempS["all"]["11"]["tracking"])) && $tkey == "0") ? "selected" : "")).'>'.$tval.'</option>';
													}
													?>
											</select>
										</td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usedoku_11";?>" value="" <?php if(isset($jtempS["all"]["11"]["doku"]) && $jtempS["all"]["11"]["doku"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usedoku"></input></td>
									</tr>
									<tr style="background-color:#FF6659;">
										<td>12</td>
										<td>Fund lebend, RD benötigt</td>
										<td><input style="width:100%;" disabled type="text" id="<?php echo $oidtemp;?>_statustext_12" class="form-control checkJSON <?php if($userlevel <= 3 && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" placeholder="Wert der an der Schnittstelle übergeben wird" value="<?php if(isset($jtempS["all"]["12"]["text"])){echo $jtempS["all"]["12"]["text"]; }?>"></td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usestatus_12";?>" value="" <?php if(isset($jtempS["all"]["12"]["use"]) && $jtempS["all"]["12"]["use"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usestatus"></input></td>
										<td class="text-center">
											<select disabled id="<?php echo $oidtemp."_usetracking_12";?>" size="1"  class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usetracking">
													<?php
													foreach($tracking_t as $tkey => $tval){
														echo '<option value="'.$tkey.'" '.((isset($jtempS["all"]["12"]["tracking"]) && $jtempS["all"]["12"]["tracking"] == $tkey && is_numeric($jtempS["all"]["12"]["tracking"])) ? "selected" : (((!isset($jtempS["all"]["12"]["tracking"]) || !is_numeric($jtempS["all"]["12"]["tracking"])) && $tkey == "3") ? "selected" : "")).'>'.$tval.'</option>';
													}
													?>
											</select>
										</td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usedoku_12";?>" value="" <?php if(isset($jtempS["all"]["12"]["doku"]) && $jtempS["all"]["12"]["doku"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usedoku"></input></td>
									</tr>
									<tr style="background-color:#FF6659;">
										<td>13</td>
										<td>Fund lebend, kein RD benötigt</td>
										<td><input style="width:100%;" disabled type="text" id="<?php echo $oidtemp;?>_statustext_13" class="form-control checkJSON <?php if($userlevel <= 3 && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" placeholder="Wert der an der Schnittstelle übergeben wird" value="<?php if(isset($jtempS["all"]["13"]["text"])){echo $jtempS["all"]["13"]["text"]; }?>"></td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usestatus_13";?>" value="" <?php if(isset($jtempS["all"]["13"]["use"]) && $jtempS["all"]["13"]["use"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usestatus"></input></td>
										<td class="text-center">
											<select disabled id="<?php echo $oidtemp."_usetracking_13";?>" size="1"  class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usetracking">
													<?php
													foreach($tracking_t as $tkey => $tval){
														echo '<option value="'.$tkey.'" '.((isset($jtempS["all"]["13"]["tracking"]) && $jtempS["all"]["13"]["tracking"] == $tkey && is_numeric($jtempS["all"]["13"]["tracking"])) ? "selected" : (((!isset($jtempS["all"]["13"]["tracking"]) || !is_numeric($jtempS["all"]["13"]["tracking"])) && $tkey == "3") ? "selected" : "")).'>'.$tval.'</option>';
													}
													?>
											</select>
										</td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usedoku_13";?>" value="" <?php if(isset($jtempS["all"]["13"]["doku"]) && $jtempS["all"]["13"]["doku"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usedoku"></input></td>
									</tr>
									<tr style="background-color:#FF6659;">
										<td>14</td>
										<td>Fund tot</td>
										<td><input style="width:100%;" disabled type="text" id="<?php echo $oidtemp;?>_statustext_14" class="form-control checkJSON <?php if($userlevel <= 3 && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" placeholder="Wert der an der Schnittstelle übergeben wird" value="<?php if(isset($jtempS["all"]["14"]["text"])){echo $jtempS["all"]["14"]["text"]; }?>"></td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usestatus_14";?>" value="" <?php if(isset($jtempS["all"]["14"]["use"]) && $jtempS["all"]["14"]["use"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usestatus"></input></td>
										<td class="text-center">
											<select disabled id="<?php echo $oidtemp."_usetracking_14";?>" size="1"  class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usetracking">
													<?php
													foreach($tracking_t as $tkey => $tval){
														echo '<option value="'.$tkey.'" '.((isset($jtempS["all"]["14"]["tracking"]) && $jtempS["all"]["14"]["tracking"] == $tkey && is_numeric($jtempS["all"]["14"]["tracking"])) ? "selected" : (((!isset($jtempS["all"]["14"]["tracking"]) || !is_numeric($jtempS["all"]["14"]["tracking"])) && $tkey == "3") ? "selected" : "")).'>'.$tval.'</option>';
													}
													?>
											</select>
										</td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usedoku_14";?>" value="" <?php if(isset($jtempS["all"]["14"]["doku"]) && $jtempS["all"]["14"]["doku"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usedoku"></input></td>
									</tr>
									<tr style="background-color:#FF6659;">
										<td>15</td>
										<td>Sprechwunsch</td>
										<td><input style="width:100%;" disabled type="text" id="<?php echo $oidtemp;?>_statustext_15" class="form-control checkJSON <?php if($userlevel <= 3 && is_numeric($userlevel)){ echo "input_oid_".$oidtemp;}?>" placeholder="Wert der an der Schnittstelle übergeben wird" value="<?php if(isset($jtempS["all"]["15"]["text"])){ echo $jtempS["all"]["15"]["text"]; }?>"></td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usestatus_15";?>" value="" <?php if(isset($jtempS["all"]["15"]["use"]) && $jtempS["all"]["15"]["use"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usestatus"></input></td>
										<td class="text-center">
											<select disabled id="<?php echo $oidtemp."_usetracking_15";?>" size="1"  class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usetracking">
													<?php
													foreach($tracking_t as $tkey => $tval){
														echo '<option value="'.$tkey.'" '.((isset($jtempS["all"]["15"]["tracking"]) && $jtempS["all"]["15"]["tracking"] == $tkey && is_numeric($jtempS["all"]["15"]["tracking"])) ? "selected" : (((!isset($jtempS["all"]["15"]["tracking"]) || !is_numeric($jtempS["all"]["15"]["tracking"])) && $tkey == "3") ? "selected" : "")).'>'.$tval.'</option>';
													}
													?>
											</select>
										</td>
										<td class="text-center"><input disabled type="checkbox" id="<?php echo $oidtemp."_usedoku_15";?>" value="" <?php if(isset($jtempS["all"]["15"]["doku"]) && $jtempS["all"]["15"]["doku"] == true){echo "checked";} ; ?> class="<?php if($userlevel <= 3){ echo "input_oid_".$oidtemp;}?> <?php echo $oidtemp;?>_usedoku"></input></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<!-- Funktionen -->
				<div id="<?php echo($oidtemp);?>_funktionen" class="row card" id="infos">
					<div class="card-header" id="funktionenheading">
						<h5>
							<button class="btn btn-link" data-toggle="collapse" data-target="#funktionencollapse" aria-expanded="false" aria-controls="funktionencollapse">
							Funktionen</button>
						</h5>
					</div>
					<div id="funktionencollapse" class="collapse w-100" aria-labelledby="funktionenheading" data-parent="#<?php echo($oidtemp);?>_funktionen">
						<div class="form-group pl-3">
							<div class="infotext">Hier können die Funktionen welche Mitglieder im Einsatz erfüllen können definiert werden. Für jede Funktion ist eine Bezeichnung sowie ein Kürzel (max. 4 Zeichen) anzugeben. Mittels Checkbox kann definiert werden ob diese Funktion zur Rückmeldung in der App zur Verfügung steht.</div>
							<table class="functiontable_<?php echo$oidtemp; ?>">
								<thead>
									<tr>
										<th  style="width:40%;">Funktion</th>
										<th  style="width:30%;">Kürzel</th>
										<th  style="width:15%;">Rückmeldbar</th>
										<th style="width:15%;"><button class='function_org_add org_speichern_<?php echo $oidtemp; ?>' data-oid='<?php echo $oidtemp; ?>' title='Neue Funktion hinzufügen' data-toggle='tooltip' style='display: none; border: 0px; background-color:rgba(0,0,0,0)'><i class='material-icons' style='color:#AEEA00;'>add_circle</i></button></th>
									</tr>
								</thead>
								<tbody>
								<?php //vorhandene Einträge durchlaufen
								$jtemp3 = "";
								//JSON mit den Funktionen
								$jtemp3 = json_decode($org_arr[$oidtemp]["funktionen"],true);
								//print_r($jtemp3);
								if($jtemp3 != ""){
									foreach ($jtemp3 as $element) {
										//print_r($element);
											if($element["app"] == true){$checked_fun = "checked";} else { $checked_fun = "";}
											if($userlevel <= 3 && is_numeric($userlevel)){ $input_oid = "input_oid_".$oidtemp; $function_org_del = "function_org_del_".$oidtemp;} else {$input_oid = ""; $function_org_del = "";}
											echo "<tr>";
											echo "<td><input name='lang_".$oidtemp."[]' style='width:100%;' disabled type='text' id='' class='checkJSON ".$input_oid."' value='".$element["lang"]."'></td>";
											echo "<td><input name='kurz_".$oidtemp."[]' style='width:100%;' disabled type='text' id='' class='checkJSON ".$input_oid."' value='".$element["kurz"]."'></td>";
											echo "<td><input name='app_".$oidtemp."[]'style='width:100%;' disabled type='checkbox' id='' class='".$input_oid."' ".$checked_fun."></td>";
											echo "<td><button class='function_org_del ".$function_org_del."' title='Funktion löschen' data-toggle='tooltip' style='display: none; border: 0px; background-color:rgba(0,0,0,0)'><i class='material-icons' style='color:#D3302F;'>delete_forever</i></button></td>";
											echo "</tr>";
										
									} 
								}
								?>
								
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<!-- Einheiten -->
				<div id="<?php echo($oidtemp);?>_unit" class="row card" id="infos">
					<div class="card-header" id="unitheading">
						<h5>
							<button class="btn btn-link" data-toggle="collapse" data-target="#unitcollapse" aria-expanded="false" aria-controls="unitcollapse">
							Einheiten für Anzeige</button>
						</h5>
					</div>
					<div id="unitcollapse" class="collapse w-100" aria-labelledby="unitheading" data-parent="#<?php echo($oidtemp);?>_unit">
						<div class="form-group pl-3">
							<div class="infotext">Hier können sie einstellen, welche Einheit für die Anzeige von Flächen (Suchgebiete) und Längen (Wegsuchen) verwendet wird.</div>
							</div>
						<div class="form-group d-flex pl-3">
							<label for="area_<?php echo($oidtemp);?>">Flächen</label>
							<select disabled name="area_<?php echo($oidtemp);?>" id="area_<?php echo($oidtemp);?>" size="1" class="form-control kartenwahl input_oid_<?php echo($oidtemp);?>">
								<?php
									$x_m = 0;
									$data_m = "";
									foreach($einheit_flaeche as $area_t){
										$selected_t = "";
										$data_m = 'data-factor="'.$area_t["factor"].'" data-unit="'.$area_t["unit"].'"';
										if(isset($org_arr[$oidtemp]["einheiten"]["aunit"]) && $area_t["unit"] == $org_arr[$oidtemp]["einheiten"]["aunit"]){ $selected_t = " selected"; }
										echo "<option class='' ".$data_m." value='".$area_t["unit"]."' ".$selected_t.">".$area_t["unit"]."</option>";
										$x_m++;
									}
								
									
								?>
							</select>
						</div>
					
						<div class="form-group d-flex pl-3">
							<label for="length_<?php echo($oidtemp);?>">Längen</label>
							<select disabled name="length_<?php echo($oidtemp);?>" id="length_<?php echo($oidtemp);?>" size="1" class="form-control kartenwahl input_oid_<?php echo($oidtemp);?>">
								<?php
									$x_m = 0;
									$data_m = "";
									foreach($einheit_laenge as $length_t){
										$selected_t = "";
										$data_m = 'data-factor="'.$length_t["factor"].'" data-unit="'.$length_t["unit"].'"';
										if(isset($org_arr[$oidtemp]["einheiten"]["lunit"]) && $length_t["unit"] == $org_arr[$oidtemp]["einheiten"]["lunit"]){ $selected_t = " selected"; }
										echo "<option class='' ".$data_m." value='".$length_t["unit"]."' ".$selected_t.">".$length_t["unit"]."</option>";
										$x_m++;
									}
								
									
								?>
							</select>
						</div>
					
					</div>	
				</div>
				<!-- App Settings -->
				<div id="<?php echo($oidtemp);?>_app" class="row card" id="infos">
					<div class="card-header" id="appheading">
						<h5>
							<button class="btn btn-link" data-toggle="collapse" data-target="#appcollapse" aria-expanded="false" aria-controls="appcollapse">
							App Einstellungen</button>
						</h5>
					</div>
					<div id="appcollapse" class="collapse w-100" aria-labelledby="appheading" data-parent="#<?php echo($oidtemp);?>_app">
						<div class="form-group pl-3">
							<div class="infotext">Hier können die Einstellungen für die eTrax | rescue App der eigenen Organisation verändert werden.</div>
							</div>
						<div class="form-group d-flex pl-3">
							<label for="readposition_<?php echo($oidtemp);?>">Position erfassen alle</label>
							<select disabled name="readposition_<?php echo($oidtemp);?>" id="readposition_<?php echo($oidtemp);?>" size="1" class="form-control input_oid_<?php echo($oidtemp);?>">
								<?php
									$x_m = 0;
									$data_m = "";
									foreach($app_position as $app_pos_t){
										$selected_t = (isset($org_arr[$oidtemp]["readposition"]) && $app_pos_t["time"] == $org_arr[$oidtemp]["readposition"]) ? " selected" : "";
										echo "<option class='' value='".$app_pos_t["time"]."' ".$selected_t.">".$app_pos_t["text"]."</option>";
										$x_m++;
									}
								
									
								?>
							</select>
						</div>
					
						<div class="form-group d-flex pl-3">
							<label for="distance_<?php echo($oidtemp);?>">Mindestdistanz zwischen 2 gültigen Wegpunkten</label>
							<select disabled name="distance_<?php echo($oidtemp);?>" id="distance_<?php echo($oidtemp);?>" size="1" class="form-control input_oid_<?php echo($oidtemp);?>">
								<?php
									$x_m = 0;
									$data_m = "";
									foreach($app_distance as $app_dist_t){
										$selected_t = (isset($org_arr[$oidtemp]["distance"]) && $app_dist_t["distance"] == $org_arr[$oidtemp]["distance"]) ? " selected" : ""; 
										echo "<option class='' value='".$app_dist_t["distance"]."' ".$selected_t.">".$app_dist_t["text"]."</option>";
										$x_m++;
									}
								
									
								?>
							</select>
						</div>
					
						<div class="form-group d-flex pl-3">
							<label for="updateinfo_<?php echo($oidtemp);?>">Update der Suchinfos alle</label>
							<select disabled name="updateinfo_<?php echo($oidtemp);?>" id="updateinfo_<?php echo($oidtemp);?>" size="1" class="form-control input_oid_<?php echo($oidtemp);?>">
								<?php
									$x_m = 0;
									$data_m = "";
									foreach($app_suchinfo as $app_info_t){
										$selected_t = (isset($org_arr[$oidtemp]["updateinfo"]) && $app_info_t["time"] == $org_arr[$oidtemp]["updateinfo"]) ? " selected" : ""; 
										echo "<option class='' value='".$app_info_t["time"]."' ".$selected_t.">".$app_info_t["text"]."</option>";
										$x_m++;
									}
								
									
								?>
							</select>
						</div>
					</div>	
				</div>
				<!-- Karten Settings -->
				<div id="<?php echo($oidtemp);?>_karte" class="row card" id="infos">
					<div class="card-header" id="karteheading">
						<h5>
							<button class="btn btn-link" data-toggle="collapse" data-target="#kartecollapse" aria-expanded="false" aria-controls="kartecollapse">
							Auswahl Kartenmaterial</button>
						</h5>
					</div>
					<div id="kartecollapse" class="collapse w-100" aria-labelledby="karteheading" data-parent="#<?php echo($oidtemp);?>_karte">
						<div class="form-group pl-3">
							<div class="infotext">Hier kann ausgewählt werden, welches Kartenmaterial die Organisation nützen möchte sowie die Reihenfolge festgelegt werden, in der es in der Auswahl erscheint. Das verfügbare Material ist regional eingeschränkt.</div>
						</div>
						<div class="form-group d-flex pl-3">
						<?php
						foreach($path as $map){ 
							if($map["org"] == "" || in_array($oidtemp,explode(";",$map["org"])) || $_SESSION["etrax"]["USER"]["dev"]){ // Exklusiv verfügbares Kartenmaterial?>
											
						
							<div class="col-3 mt-2 mb-2 Karte_<?php echo $map["land"];?> Kartenwahl" <?php if((isset($data_org_json->land) && $data_org_json->land == $map["land"]) || $map["land"] == "world") {} else { echo "style='display:none;'";}?>>
								<h5><?php echo $map["name"]; ?></h5>
								<img class="mx-auto d-block" src="<?php echo $map["demotile"]; ?>"/>
								<p><?php if($map["land"] == "world"){ echo "<b>Verfügbarkeit:</b> Weltweit";} else { echo "<b>Verfügbarkeit:</b> ".$map["land"]."";} ?><br>
								<b>Maximales Zoomlevel:</b> <?php echo $map["zlim"]; ?></br>
								<?php echo $map["attributions"]; ?></br>
								<?php if($map["printable"]) { echo "<b>Ausdruckbar:</b>  Ja"; } else { echo "<b>Ausdruckbar:</b>  Nein";} ?><br>
								<?php if(in_array($oidtemp,explode(";",$map["org"])) || $_SESSION["etrax"]["USER"]["dev"]) { echo "<b>Exklusives Kartenmaterial:</b>  Ja"; }?><br>
								</p>
							</div>
						
						<?php 
							}
						}?>
						</div>
						<div class="form-group pl-3">
							<h5>Reihenfolge festlegen</h5>
							<?php
							$n_map = 6; //Maximale Anzahl an wählbaren Karten
							for ($x_n = 0; $x_n < $n_map; $x_n++){ 
							?>
							
							<div class="form-group d-flex">
								<label for="Karte_<?php echo $x_n;?>" class="col-sm-1 col-form-label" ><b>Karte <?php echo ($x_n+1);?></b></label>
								<div class="col-sm-3">
									<select disabled name="Karte_<?php echo ($x_n+1);?>" id="Karte_<?php echo ($x_n+1);?>" size="1" class="form-control kartenwahl input_oid_<?php echo($oidtemp);?>">
										<option value="">Keine Karte</option>
										<?php
											$x_m = 0;
											$data_m = "";
											foreach($path as $map){
												$selected_t = "";
												$data_m = 'data-kartenname="'.$map["name"].'" data-name="'.$map["name_js"].'" data-printname="'.$map["printname"].'" data-type="'.$map["type"].'" data-attributions="'.$map["attributions"].'" data-url="'.$map["url"].'" ';
												if($map["org"] == "" || in_array($oidtemp,explode(";",$map["org"]))){ // Exklusiv verfügbares Kartenmaterial
													if((isset($data_org_json->land) && $data_org_json->land == $map["land"]) || $map["land"] == "world") {
														if(isset($org_arr[$oidtemp]["maps"][$x_n]) && $map["name_js"] == $org_arr[$oidtemp]["maps"][$x_n]["name"]){ $selected_t = " selected"; }
														echo "<option class='Karte_".$map["land"]."' ".$data_m." value='".$map["name_js"]."' ".$selected_t.">".$map["name"]."</option>";
													} else {
														echo "<option class='Karte_".$map["land"]."' ".$data_m." value='".$map["name_js"]."' style='display:none;'>".$map["name"]."</option>";														
													}
												}
												$x_m++;
											}
										
											
										?>
									</select>
								</div>
							</div>
						<?php } ?>
						</div>	
					</div>	
				</div>
			</section>
		</section>
	<?php
	} //Ende If Überprüfung Usertype
	//$ttoken = $ttoken.decryptdb($result['token'],'organisation','token').",";
	//$toid = $toid.$oidtemp.",";
	} //Ende Abfrage Organisationen
	echo "<div id='token_oid' data-atoken='".$ttoken."' data-aoid='".$toid."'></div>";
	?>	
	<!--</section>-->
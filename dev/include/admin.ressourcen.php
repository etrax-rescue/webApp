<section class="reslist org_list bg-white">
<?php 
//if($userlevel > 0){$oidselect = "WHERE OID = '".$OID."'";} else {$OID = "";}
$db_organisation = $db->prepare("SELECT OID, data, usersync, funktionen FROM organisation ".$oidselect);
$db_organisation->execute() or die(print_r($db_organisation->errorInfo(), true));

while ($result = $db_organisation->fetch(PDO::FETCH_ASSOC)){
	$data_org_json = json_decode(substr(string_decrypt($result['data']), 1, -1));
	$oid_temp = $result['OID'];

	//Arten von Ressourcen werden über include/include.php geholt $ressource[""]
	$res_list_kurz = $res_list_lang = "";
	foreach($ressource as $key => $val){
		$res_list_kurz .= $val["typ_kurz"].";";
		$res_list_lang .= $val["typ_lang"].";";
	}
	$res_list_kurz = substr($res_list_kurz,0,-1);
	$res_list_lang = substr($res_list_lang,0,-1);
?>



	<section id="res_<?php echo($oid_temp);?>" class="user_list">
		<input type="hidden" class="alle_ressourcen_kurz_<?php echo($oid_temp);?>" value="<?php echo($res_list_kurz);?>"></input>
		<input type="hidden" class="alle_ressourcen_lang_<?php echo($oid_temp);?>" value="<?php echo($res_list_lang);?>"></input>
		
		<section id="<?php echo($result['OID']);?>_res_details">
			
			<button class="res_show_import btn btn-secondary float-right mt-2 mb-3 ml-3 mr-3" data-oid="<?php echo($oid_temp); ?>">
				<i class="material-icons rounded-circle color-white">directions_car</i> Ressource hinzufügen
			</button>
			<div class="bg-white rounded p-3">
			
				<ul class="members" style="list-style-type:none">
				
					
		<?php  		// Ressourcen Anzeigen
					//Ressourcen aus der DB holen
					$db_res = $db->prepare("SELECT * FROM ressourcen WHERE OID = '".$oid_temp."' ORDER BY typ ASC");
					$db_res->execute() or die(print_r($db_res->errorInfo(), true));
					$res_arr = array();
					//print_r($db_mitglieder);
					$n_res = 0;
					$letterl = "";
					while ($res_res = $db_res->fetch(PDO::FETCH_ASSOC)){
						$data_res_json = json_decode(substr(string_decrypt($res_res['data']), 1, -1));
						$res_arr[] = array('RID' => $res_res['RID'], 
											'OID'   => $res_res['OID'], 
											'typ'   => $res_res['typ'], 
											'name'   => isset($data_res_json->name) ? $data_res_json->name : "", 
											'kennung'   => isset($data_res_json->kennung) ? $data_res_json->kennung : "", 
											'beschreibung'   => isset($data_res_json->beschreibung) ? $data_res_json->beschreibung : "", 
											'typ_lang'   => isset($data_res_json->typ_lang) ? $data_res_json->typ_lang : "", 
											'lastupdate'   => $res_res['lastupdate']);
						$n_res++;
					}
					if($n_res > 0) {
						//Ausgabe
						foreach ($res_arr as $nr => $inhalt)
						{
							if($inhalt['typ'] != $letterl){
								echo "<li style='font-weight:bold;font-size:20px;'>".$ressource[$inhalt['typ']]["typ_lang"]."</li>";
							}
						
						
					?>
								<li class="showres" style="float:none;"><button  style="border:0px;background-color:rgba(0,0,0,0);float:none;" data-oid="<?php echo($inhalt['OID']);?>" data-rid="<?php echo($inhalt['RID']);?>" data-name="<?php echo($inhalt['name']);?>" data-kennung="<?php echo($inhalt['kennung']);?>" data-typ="<?php echo($inhalt['typ']);?>" data-typ_lang="<?php echo($inhalt['typ_lang']);?>" data-beschreibung="<?php echo($inhalt['beschreibung']);?>"><?php echo($inhalt['name'])." - ".$inhalt['kennung']." - ".$ressource[$inhalt['typ']]["typ_lang"];?></button></li>
					<?php	
						$letterl = $inhalt['typ'];
						}
					} else { // Ende IF wenn keine Mitglieder angelegt sind	
						echo "<li>Es sind keine Ressourcen angelegt</li>";
					}
			?>
				</ul>
			</div>
		</section>
	</section>
<?php
} //Ende Schleife Organisation für Ressourcen
?>
</section>

<!-- Ressourcen Update Overlay Anfang -->
<div class="modal fade resmodal" tabindex="-1" role="dialog" aria-labelledby="usermodalheader" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title mr-auto" id="settingsmodalheader">Ressourcen Details: </h5>
					<button type="button" class="btn btn-primary res_modify ml-2 pb-2 pt-2">Daten bearbeiten</button>
					<button type="button" class="btn btn-success abschliessen res_modify_save ml-2 pb-2 pt-2" title="Änderungen speichern" data-toggle="tooltip" data-placement="bottom" data-rid="" style="display:none;" data-uid="">Änderungen speichern</button>
					<?php if($userlevel <= 3 && is_numeric($userlevel)){ ?>
					<button type="button" class="btn btn-danger res_modify_delete ml-2" title="Ressource löschen" data-toggle="tooltip" data-placement="bottom" data-rid=""><i class='material-icons text-white'>delete_forever</i></button>
					<?php } ?>
			</div>
			<div class="modal-body">
				<div class="form-group row">
					<input type="hidden" class="z_rid" id="uid" value=""></input>
					<input type="hidden" class="z_oid" id="oid" value=""></input>
					<label for="name" class="col-sm-3 col-form-label">Bezeichnung</label>
					<div class="col-sm-9">	
						<input disabled type="text" name="name" class="mb-2 form-control z_res_edit z_name checkJSON" id="name" placeholder="Bezeichnung der Ressource" value=""></input>
					</div>
				</div>
				<div class="form-group row">
					<label for="kennung" class="col-sm-3 col-form-label">Kennung</label>
					<div class="col-sm-9">	
						<input disabled type="text" name="kennung" class="mb-2 form-control z_res_edit z_kennung checkJSON" id="kennung" placeholder="Kennung der Ressource (z.B. Kennzeichen)" value=""></input>
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="res_typ">Typ</label>
					<div class="col-sm-9">
						<select disabled name="res_typ" id="res_typ" size="1" class="mb-2 form-control z_res_edit form-control z_typ">
							
						</select>
					</div>
				</div>
				<div class="form-group row">
					<label  class="col-sm-3 col-form-label" for="beschreibung">Beschreibung</label>
					<div class="col-sm-9">
						<input disabled type="textbox" name="beschreibung" class="mb-2 form-control z_res_edit z_beschreibung checkJSON" id="beschreibung" placeholder="Beschreibungstext" value=""></input>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Ressourcen Update Overlay Ende-->
<?php //Einsatzcheckliste
session_start();
if(!isset($_SESSION["etrax"]["usertype"])){
header("Location: index.php");
}
require "../../../secure/info.inc.php";
require "../../../secure/secret.php";
require "../include/verschluesseln.php";


$EID = $_SESSION["etrax"]["EID"];
$baseURL = "../";

if(isset($_REQUEST['notroot']) && $_REQUEST['notroot'] == 2){
		$baseURL = "../";
		echo '<script src="vendor/js/jquery-3.5.1.min.js"></script>';
}

require $baseURL."include/sessionhandler.php"; //Der Sessionhandler schreibt die Sessionwerte neu

if($USER["lesen"]){ //Mindestens Leserechte werden benötigt um etwas angezeigt zu bekommen.

	$sql_checkliste = $db->prepare("SELECT checkliste FROM settings WHERE EID = ".$EID."");
	$sql_checkliste->execute($sql_checkliste->errorInfo());
	while ($sqlcheckliste = $sql_checkliste->fetch(PDO::FETCH_ASSOC)){
		if(!empty($sqlcheckliste['checkliste'])){
			$checkliste_json = json_decode(substr(string_decrypt($sqlcheckliste['checkliste']), 1, -1));
		}
	}
	if(!empty($checkliste_json)){
		foreach($checkliste_json as $pkey => $pvalue) {
			if($pvalue == "true" || $pkey == "bo_100" || $pkey == "ea_100"){
				if($pkey == "bo_100" || $pkey == "ea_100"){
					$$pkey = $pvalue;
				} else {
					$$pkey = " checked='checked'";
				}
			}else{
				$$pkey = "";
			}
		}

	?>
				<form id="checkliste" method="post">
					<h5>Eintreffen am Berufungsort:</h5>
					<div class="form-group row">
						<ul class="bo" style="list-style-type:none">
							<li><input type="checkbox"  disabled class="bo_1" <?php echo $bo_1; ?>></input> Alarmierende Stelle Informiert</li>
							<li><input type="checkbox"  disabled class="bo_2" <?php echo $bo_2; ?>></input> Eintreffende Kräfte erfassen</li>
							<li><input type="checkbox"  disabled class="bo_3" <?php echo $bo_3; ?>></input> Ansprechpartner am Einsatzort kontaktiert</li>
							<li><input type="checkbox"  disabled class="bo_4" <?php echo $bo_4; ?>></input> Primärorganisation hat behördlichen Einsatzleiter über die Grenzen eines Sucheinsatzes informiert</li>
							<li><input type="checkbox"  disabled class="bo_5" <?php echo $bo_5; ?>></input> Daten zur vermissten Person erfasst</li>
							<li><input type="checkbox"  disabled class="bo_6" <?php echo $bo_6; ?>></input> Wohnort bzw. Initial Planning Point (IPP) abgeklärt</li>
							<li><input type="checkbox"  disabled class="bo_7" <?php echo $bo_7; ?>></input> Benötigte Ressourcen überpüft:</li>
							<li style="padding-left:35px;"><input type="checkbox"  disabled class="bo_8" <?php echo $bo_8; ?>></input> Einschätzung Vermisstenbild</li>
							<li style="padding-left:35px;"><input type="checkbox"  disabled class="bo_9" <?php echo $bo_9; ?>></input> Betreuung der Angehörigen geregelt</li>
							<li style="padding-left:55px;">Beurteilung des Geländes:</li>
							<li style="padding-left:70px;"><input type="checkbox"  disabled class="bo_10" <?php echo $bo_10; ?>></input> Verfügbare Ressourcen reichen aus</li>
							<li style="padding-left:70px;"><input type="checkbox"  disabled class="bo_11" <?php echo $bo_11; ?>></input> Feuerwehr benötigt</li>
							<li style="padding-left:70px;"><input type="checkbox"  disabled class="bo_12" <?php echo $bo_12; ?>></input> Bergrettung benötigt</li>
							<li style="padding-left:70px;"><input type="checkbox"  disabled class="bo_13" <?php echo $bo_13; ?>></input> Flugpolizei benötigt</li>
							<li style="padding-left:70px;"><input type="checkbox"  disabled class="bo_14" <?php echo $bo_14; ?>></input> (weitere) Suchhunde benötigt</li>
							<li style="padding-left:70px;"></input> <input class="mb-2 form-control-plaintext bo_100 checkJSON" type="text" value="<?php echo $bo_100; ?>" disabled tabindex="8" placeholder="Weitere benötigte Organisationen"></li>
							<li><input type="checkbox"  disabled class="bo_15" <?php echo $bo_15; ?>></input> Abfrage Spitäler, Verkehrsbetriebe, Taxi- und Busunternehmer</li>
						</ul>
					</div>
					<h5>Suchtaktik entwickeln</h5>
					<div class="form-group row">
						<ul class="st" style="list-style-type:none">
							<li>Gibt es bekannte Ziele der vermissten Person?</li>
							<li style="padding-left:35px;"><input type="checkbox"  disabled class="st_1"<?php echo $st_1; ?>></input> Nein &rarr; Konzentrische Kreise um IPP bzw. PLS</li>
							<li style="padding-left:35px;"><input type="checkbox"  disabled class="st_2"<?php echo $st_2; ?>></input> Ja &rarr; Points and Lines Ansatz</li>
						</ul>
					</div>
					<h5>Personenfund</h5>
					<div class="form-group row">
						<ul class="pf" style="list-style-type:none">
							<li><input type="checkbox"  disabled class="pf_1" <?php echo $pf_1; ?>></input> Identifiziert als die vermisste Person</li>
							<li><input type="checkbox"  disabled class="pf_2" <?php echo $pf_2; ?>></input> Zustand abgeklärt</li>
							<li style="padding-left:35px;"><input type="checkbox"  disabled class="pf_3" <?php echo $pf_3; ?>></input> Transport durch Gruppe/Organisation möglich</li>
							<li style="padding-left:35px;"><input type="checkbox"  disabled class="pf_4" <?php echo $pf_4; ?>></input> Rettungsdienst benötigt</li>
							<li style="padding-left:35px;"><input type="checkbox"  disabled class="pf_5" <?php echo $pf_5; ?>></input> Polizei erforderlich</li>
							<li style="padding-left:35px;"><input type="checkbox"  disabled class="pf_6" <?php echo $pf_6; ?>></input> Feuerwehr erforderlich</li>
							<li style="padding-left:35px;"><input type="checkbox"  disabled class="pf_7" <?php echo $pf_7; ?>></input> Bergrettung erforderlich</li>
							<li><input type="checkbox"  disabled class="pf_8" <?php echo $pf_8; ?>></input> Behördlichen Einsatzleiter bzw. Polizei informiert</li>
							<li><input type="checkbox"  disabled class="pf_9" <?php echo $pf_9; ?>></input> Alarmierende informiert</li>
							<li><input type="checkbox"  disabled class="pf_10" <?php echo $pf_10; ?>></input> Alle Gruppen über Einsatzende informiert</li>
						</ul>
					</div>
					<h5>Einsatzabbruch</h5>
					<div class="form-group row">
						<ul class="ea" style="list-style-type:none">
							<li>Grund für den Einsatzabbruch</li>
							<li style="padding-left:35px;"><input type="checkbox"  disabled class="ea_1" <?php echo $ea_1; ?>></input> Verfügbare Ressourcen erschöpft</li>
							<li style="padding-left:35px;"><input type="checkbox"  disabled class="ea_2" <?php echo $ea_2; ?>></input> Fehlende Anhaltspunkte</li>
							<li style="padding-left:35px;"><input type="checkbox"  disabled class="ea_3" <?php echo $ea_3; ?>></input> Anordnung durch behördlichen Einsatzleiter</li>
							<li style="padding-left:35px;"><input type="checkbox"  disabled class="ea_4" <?php echo $ea_4; ?>></input> Gefährdungslage</li>
							<li style="padding-left:70px;"></input> <input class="mb-2 form-control-plaintext ea_100 checkJSON" type="text" value="<?php echo $ea_100; ?>" disabled tabindex="8" placeholder="Beschreibung Gefährdungslage"></li>
							
						</ul>
					</div>
					<h5>Einsatzende</h5>
					<div class="form-group row">
						<ul class="ee" style="list-style-type:none">
							<li><input type="checkbox"  disabled class="ee_1" <?php echo $ee_1; ?>></input> Alle Einsatzteams zurückgekehrt</li>
							<li><input type="checkbox"  disabled class="ee_2" <?php echo $ee_2; ?>></input> Abschlussbesprechung durchgeführt</li>
							<li><input type="checkbox"  disabled class="ee_3" <?php echo $ee_3; ?>></input> Abmeldung aller Mitglieder vom Einsatz</li>
						</ul>
					</div>
					
				</form>
		<?php
			}else{
				echo 'keine Checkliste verfügbar';
			}
		?>
			
			<script>
				var eid = sessionStorage.getItem("eid");
				var wp = "api";
				var rwdb = "read_write_db.php";
				
				function escapeHTML(text) {
					var map = {
						'&': '&amp;',
						'<': '&lt;',
						'>': '&gt;',
						'"': '&quot;',
						"'": '&#039;',
						"\\": ''
					};
					return text.replace(/[&<>"'\\]/g, function(m) { return map[m]; });
				}
				
				
				$(function(){
				//Verhindern der Eingabe von " und \ für JSON
					$(".checkJSON").keypress(function(e){
						var keyCode = e.which;
						// Unzulässige Zeichen 
						if ( keyCode <= 31 || keyCode == 34 || keyCode == 92 ) {
						e.preventDefault();
						$(".modal.feedback").modal('show').find("h5").html("In diesem Feld dürfen die Zeichen \", \\ und Tabulator nicht eingegeben werden!");
							setTimeout(function(){ $(".modal.feedback").modal('hide'); }, 2000);
						}
					});
					
					$(".ausgabe").hide();
					$(".eingabe").hide();

					$(".checklisteEinsatz").on("click", ".checklist-bearbeiten", function(){
						$("#checkliste input").removeAttr("disabled");
						$(".eingabe,.abbrechen").show();
						$(".checklist-bearbeiten,.schliessen,.ausgabe").hide();
					});
					$(".checklisteEinsatz").on("click", ".abbrechen", function(){
						$("#checkliste input").attr("disabled",true);
						$("#zielpersonsubmit,.abbrechen,.eingabe").hide();
						$(".checklist-bearbeiten,.schliessen").show();
					});
					

					//Checklistenwerte speichern
					$(".checklisteEinsatz").on("click", "#checklistesubmit", function() {
						
						//für read_write_db
							var database = {
								type: "json",		// defining datatype json/single value (json/val)
								action: "update",	//action read or write
								table: "settings",	// DB Table
								column: "checkliste"		// DB Table column for jsons to be changed
							};
							// Entries to be display (key: value) 
							// bei json auslesen nur 1 Eintrag!
							var select = {
								EID : ""+eid+""
							}
							// json Node to be changed (nodename: value)
							var json_nodes = {
								bo_100: ""+escapeHTML($('.bo_100').val())+"",
								ea_100: ""+escapeHTML($('.ea_100').val())+""
							};
							var chechliste_boxen = ["bo_1","bo_2","bo_3","bo_4","bo_5","bo_6","bo_7","bo_8","bo_9","bo_10","bo_11","bo_12","bo_13","bo_14","bo_15","st_1","st_2","pf_1","pf_2","pf_3","pf_4","pf_5","pf_6","pf_7","pf_8","pf_9","pf_10","ea_1","ea_2","ea_3","ea_4","ee_1","ee_2","ee_3"];
							chechliste_boxen.forEach(function(box,i){
								//if($(box).is(':checked')){json_nodes.""+box+"" = true;} else {var json_nodes.""+box+"" = false;}
								//if($(box).is(':checked')){Object.assign(json_nodes, {'box' : true});} else {Object.assign(json_nodes, {'box' : false});}
								obj_temp = {};
								if($("."+box).prop( "checked" )){obj_temp[box] = true;} else {obj_temp[box] = false;}
								$.extend(json_nodes,obj_temp);
							});
							console.log(json_nodes);
							
							// direct changes in db ( fieldname: value)
							var db_vars = {}
							
							$.ajax({
								url: wp+"/"+rwdb,
								type: "post",
								data: {
									database: database,
									select: select,
									values: db_vars,
									json_nodes: json_nodes
									
								},
							success: function () {
								//$(".checklisteEinsatz .modal-body").load( "api/checkliste.php", {"notroot":2} );
								$("#checkliste input").attr("disabled",true);
								$("#zielpersonsubmit,.abbrechen,.eingabe").hide();
								$(".checklist-bearbeiten,.schliessen").show();
								//$(".protokoll").show().find(".form-control").prop('readonly', true).removeClass("form-control").addClass("form-control-plaintext");
							}
							});
						
					});

				});

				
			</script>
	<?php } else {
		echo "Sie verfügen nicht über die notwendigen Rechte um den Inhalt angezeigt zu bekommen.";
	}?>	
	
		
		
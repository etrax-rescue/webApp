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
		//echo '<script src="vendor/js/jquery-3.5.1.min.js"></script>';
} 

//Infos zur eigenen Organsiation holen
$db_org = $db->prepare("SELECT OID,data FROM organisation WHERE OID = '".$_SESSION["etrax"]["adminOID"]."'");
$db_org->execute() or die(print_r($db_org->errorInfo(), true));
$org_arr = array();
while ($reso = $db_org->fetch(PDO::FETCH_ASSOC)){
	$data_org_json = json_decode(substr(string_decrypt($reso['data']), 1, -1));
	$org_arr[$reso["OID"]]["OID"] = $reso["OID"];
	$org_arr[$reso["OID"]]["bezeichnung"] = (isset($data_org_json->bezeichnung) ? $data_org_json->bezeichnung : "");
	$org_arr[$reso["OID"]]["kurzname"] = (isset($data_org_json->kurzname) ? $data_org_json->kurzname : "");
}
?>
<script type="text/javascript" src="vendor/js/trix.js"></script>
<style>
	span.trix-button-group.trix-button-group--file-tools {
			display:none;
	}
	button.trix-button.trix-button--icon.trix-button--icon-strike {
			display:none;
	}
	button.trix-button.trix-button--icon.trix-button--icon-link {
			display:none;
	}
	button.trix-button.trix-button--icon.trix-button--icon-quote {
			display:none;
	}
	button.trix-button.trix-button--icon.trix-button--icon-code {
			display:none;
	}
</style>
<?php 
require $baseURL."include/sessionhandler.php"; //Der Sessionhandler schreibt die Sessionwerte neu

if($USER["lesen"]){ //Mindestens Leserechte werden benötigt um etwas angezeigt zu bekommen.
	$sql_checkliste = $db->prepare("SELECT einsatzbericht FROM settings WHERE EID = ".$EID."");
	$sql_checkliste->execute() or die(print_r($sql_checkliste->errorInfo(), true));
	while ($sqlcheckliste = $sql_checkliste->fetch(PDO::FETCH_ASSOC)){
		if($sqlcheckliste['einsatzbericht']){
			$einsatzbericht_json = json_decode(substr(string_decrypt($sqlcheckliste['einsatzbericht']), 1, -1));
		}
	}
	$eb_prim = $eb_org = "";
	if(!empty($einsatzbericht_json)){
		foreach($einsatzbericht_json as $pkey => $pvalue) {
			if($pkey == "eb_prim"){
				$eb_prim = $pvalue;
			} 
			if($pkey == "eb_".$_SESSION["etrax"]["adminOID"]){
				$eb_org = $pvalue;
			}
		}
	}else{
		echo '<h3 class="color-red">Noch kein Bericht vorhanden</h3>';
		$eb_prim = "";
		$eb_org = "";
	}

	?>
				<form id="einsatzbericht" action="#" method="post">
					<h5>Einsatzbericht Primärorganisation:</h5>
					<div class="col-sm-12">
						<?php if($USER["einsatzleitung"]){ ?>
						<input id="eb_prim" type="hidden" name="content" value="<?php echo $eb_prim;?>">
								<trix-editor class="eb_prim" style="display:none;" input="eb_prim" aria-describedby="ebprimHelp"></trix-editor>
								<div class="eb_preview"><?php echo $eb_prim;?></div>
						<?php } else { ?>
						<div class="col-sm-12"  aria-describedby="ebprimHelp">
						<input id="eb_prim" type="hidden" name="content" value="<?php echo $eb_prim;?>">
							<?php echo $eb_prim;?>
						</div>
						<?php } ?>
						<small id="ebprimHelp" class="form-text text-muted color-red">
							Das ist der <b>offizielle Einsatzbericht</b>, erstellt durch die Primärorganisation. Dieser steht bei Einsätzen mit mehreren Organisationen allen zum unveränderten Ausdruck zur Verfügung.
						</small>
					</div>
					
					<h5 style="padding-top:35px;">Einsatzbericht <?php echo $org_arr[$_SESSION["etrax"]["adminOID"]]["kurzname"];?>:</h5>
					<div class="col-sm-12">
						<?php if($USER["zuweisen"]){ ?>
						<input id="eb_<?php echo $_SESSION["etrax"]["adminOID"];?>" type="hidden" name="content" value="<?php echo $eb_org;?>">
								<trix-editor class="eb_org" style="display:none;" input="eb_<?php echo $_SESSION["etrax"]["adminOID"];?>" aria-describedby="eborgHelp"></trix-editor>
								<div class="eb_preview"><?php echo $eb_org;?></div>
						<?php } else { ?>
						<div class="col-sm-12"  aria-describedby="eborgHelp">
							<?php echo $eb_org;?>
						</div>
						<?php } ?>
						<small id="eborgHelp" class="form-text text-muted color-red">
							Das ist der Einsatzbericht der eigenen Organisation (<?php echo $org_arr[$_SESSION["etrax"]["adminOID"]]["kurzname"];?>), welcher nur zum Ausdruck für die eigene Organisation zur Verfügung steht.
						</small>
					</div>
			</form>
			<script>
				var eid = sessionStorage.getItem("eid");
				var wp = "<?php echo $baseURL;?>";
				<?php
				//Prüfen ob read_write_db.php erreicht werden kann
				/*if(is_file($baseURL."api/read_write_db.php")){
					echo 'var rwdb = "api/read_write_db.php";';
				} elseif(is_file($baseURL."v5/api/read_write_db.php")) {
					echo 'var rwdb = "v5/api/read_write_db.php";';
				} else {*/
					echo 'var rwdb = "api/read_write_db.php";';
				//}
				?>
				
				function escapeJSON(text) {
					var map = {
						'"': "'",
						"\\": ''
					};
					return text.replace(/["\\]/g, function(m) { return map[m]; });
				}
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
				
				//Verhindern der Eingabe von " und \ für JSON
				$(document).ready(function(){
					$(".checkJSON").keypress(function(e){
						var keyCode = e.which;
						// Unzulässige Zeichen 
						if ( keyCode <= 31 || keyCode == 34 || keyCode == 92 ) {
					  e.preventDefault();
					  $(".modal.feedback").modal('show').find("h5").html("In diesem Feld dürfen die Zeichen \", \\ und Tabulator nicht eingegeben werden!");
						setTimeout(function(){ $(".modal.feedback").modal('hide'); }, 2000);
					}
				  });
				});
				
				$(function(){
					
					$(".ausgabe").hide();
					$(".eingabe").hide();

					$(".berichtbearbeiten").click(function(){
						$(".eb_preview").hide();
						$(".eb_prim,.eb_org").show();
						$(".eingabe,.abbrechen").show();
						$(".berichtbearbeiten,.schliessen,.ausgabe").hide();
					});
					$(".abbrechen").click(function(){
						$("#zielpersonsubmit,.abbrechen").hide();
						$(".berichtbearbeiten,.schliessen").show();
					});
					

					//Checklistenwerte speichern
					$("#einsatzberichtsubmit").click(function() {
						var ebid = $(this).data("ebid");
						//für read_write_db
							var database = {
								type: "json",		// defining datatype json/single value (json/val)
								action: "update",	//action read or write
								table: "settings",	// DB Table
								column: "einsatzbericht"		// DB Table column for jsons to be changed
							};
							// Entries to be display (key: value) 
							// bei json auslesen nur 1 Eintrag!
							var select = {
								EID : ""+eid+""
							}
							// json Node to be changed (nodename: value)
							var json_nodes = {
										eb_prim: ""+escapeJSON($('#eb_prim').val())+""
									};
							obj_temp = {};
							obj_temp[ebid] = escapeJSON($('#'+ebid).val());
							$.extend(json_nodes,obj_temp);
							// direct changes in db ( fieldname: value)
							var db_vars = {}
							
							$.ajax({
								//url: "" + wp + "" + rwdb + "" ,
								url: "" + rwdb + "" ,
								type: "post",
								data: {
									database: {
										type: 'json',
										action: "update",
										table: "settings",
										column: "einsatzbericht"
									},
									select: {
										EID : ""+eid+""
									},
									values: '',
									json_nodes: json_nodes
								},
							success: function () {
								$(".abbrechen,.eingabe").hide();
								$(".berichtbearbeiten,.schliessen").show();
							}
							});
						
					});

				});

				
			</script>
	<?php } else {
		echo "Sie verfügen nicht über die notwendigen Rechte um den Inhalt angezeigt zu bekommen.";
	}?>	
	
		
		
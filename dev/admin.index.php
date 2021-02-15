<?php
session_start();
if (!isset($_SESSION["etrax"]["usertype"])) {
	header("Location: index.php");
}
require "../../secure/info.inc.php";
require "../../secure/secret.php";
require "include/verschluesseln.php";
require "include/include.php";
require "include/sessionhandler.php"; //Der Sessionhandler schreibt die Sessionwerte neu
if (isset($_GET["id"])) {
	$id = htmlspecialchars($_GET["id"]);
} else {
	$id = "";
}
//OID zum bearbeiten für DEV User 
if (isset($_GET["OID"])) {
	$oid2 = htmlspecialchars($_GET["OID"]);
} else {
	$oid2 = "";
}
$EID = isset($_SESSION['etrax']['EID']) ? $_SESSION['etrax']['EID'] : "";
$OID = $_SESSION["etrax"]["OID"];

//FID in userlevel und userrechte trennen
if (isset($_SESSION["etrax"]["FID"])) {
	$FID = explode(".", $_SESSION["etrax"]["FID"]);
	$userlevel = $FID[0];
	if ($userlevel == "" || !is_numeric($userlevel)) {
		$userlevel = 10;
	}
	$userrechte = $FID[1];
	if ($userrechte == "") {
		$userrechte = 5;
	}
} else {
	$userlevel = 10;
	$userrechte = 5;
}

$OID_admin = $_SESSION["etrax"]["adminOID"];

if ($userlevel > 0) {
	$oidselect = "WHERE OID = '" . $OID . "'";
}
if ($userlevel == 0 && $oid2 != "" && $OID_admin == "DEV") {
	$oidselect = "WHERE OID = '" . $oid2 . "'";
} else {
	$oidselect = "WHERE OID = '" . $OID . "'";
}

$FID = $_SESSION["etrax"]["FID"];


//Ausgabe erfolgt nur für Utype kleiner 8
if ($userlevel < 8 && is_numeric($userlevel)) {
	include("include/header.html");
?>
	<?php
	//Switch für die Importfunktionen
	$do = htmlspecialchars(isset($_GET["do"]) ? $_GET["do"] : "");

	if ($do != "show") {
	?>
	<?php } ?>


	<script src="vendor/js/jquery-3.5.1.min.js"></script>
	<script src="vendor/js/bootstrap.bundle.min.js"></script>
	</head>

	<body id="admin_index" class="background">
		<div class="message" style="display:none;z-index:1000;background-color:#AAF255;font-size:2em;"></div>
		<!-- Kopfzeile -->
		<?php
		include "include/admin-navbar.php";

		//Switch für die Importfunktionen
		$do = htmlspecialchars(isset($_GET["do"]) ? $_GET["do"] : "");
		switch ($do) {
			default:
		?>
				<ul class="nav nav-tabs" id="adminTab" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="organisation-tab" data-toggle="tab" href="#organisation" role="tab" aria-controls="organisation" aria-selected="true">Einstellungen der Organisation </a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="user-tab" data-toggle="tab" href="#user" role="tab" aria-controls="user" aria-selected="false">User verwalten</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="ressourcen-tab" data-toggle="tab" href="#ressourcen" role="tab" aria-controls="ressourcen" aria-selected="false">Ressourcen verwalten</a>
					</li>
					<?php if($userlevel <= 3 && is_numeric($userlevel)){ ?>
						<li class="nav-item">
							<a class="nav-link" id="admin-tab" data-toggle="tab" href="#admin" role="tab" aria-controls="admin" aria-selected="false">Administratoren verwalten</a>
						</li>
					<?php } ?>
				</ul>
				<div class="tab-content" id="adminTabContent">
					<!-- Organisationen Anzeigen -->
					<div class="tab-pane fade show active" id="organisation" role="tabpanel" aria-labelledby="organisation-tab">
						<?php require "include/admin.organisation.php"; ?>
					</div>
					<!-- Organisationen anzeigen Ende -->

					<!-- Userverwaltung Anfang -->
					<div class="tab-pane fade" id="user" role="tabpanel" aria-labelledby="user-tab">
						<?php require "include/admin.user.php"; ?>
					</div>
					<!-- Userverwaltung Ende -->


					<!-- Ressourcenverwaltung Anfang -->
					<div class="tab-pane fade" id="ressourcen" role="tabpanel" aria-labelledby="ressourcen-tab">
						<?php require "include/admin.ressourcen.php"; ?>
					</div>
					<!-- Ressourcenverwaltung Ende -->

					<!-- Administratorenverwaltung Anfang -->
					<?php if($userlevel <= 3 && is_numeric($userlevel)){ ?>
						<div class="tab-pane fade" id="admin" role="tabpanel" aria-labelledby="admin-tab">
							<?php require "include/admin.admin.php"; ?>
						</div>
					<?php } ?>
					<!-- Administratorenverwaltung Ende -->
				</div>

		<?php //Infozeile für die Entwicklung
				// Wird nur included wenn das File vorhanden ist
				if (is_file("include/admin.info.php")) {
					include "include/admin.info.php";
				}
				break;

			case "import":
				$iDel = htmlspecialchars($_POST["iDel"]);
				$iPwUp = htmlspecialchars($_POST["iPwUp"]);
				$iOrg = htmlspecialchars($_POST["iOrg"]);
				if ($iDel == 1) { //Bestehende Nutzer der Organisation löschen
					$org_del = $db->prepare("DELETE FROM user WHERE OID = ? AND UID != ? AND (FID = '10' OR FID = '8.1' OR FID = '8.2' OR FID = '8.3' OR FID = '8.4' OR FID = '8.5')"); // Dadurch werden Administratoren nicht gelöscht
					$org_del->bindParam(1, $iOrg, PDO::PARAM_STR);
					$org_del->bindParam(2, $_SESSION["etrax"]["UID"], PDO::PARAM_STR);
					$org_del->execute();
					//ErrorInfo
					$errorInfo = $org_del->errorInfo();
					echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
					
				}
				foreach ($_POST["iImport"] as $i) {
					$UID = $iOrg . "-" . htmlspecialchars($_POST["i_2"][$i], ENT_QUOTES);
					$name = htmlspecialchars($_POST["i_1"][$i], ENT_QUOTES);
					$dienstnummer = htmlspecialchars($_POST["i_2"][$i], ENT_QUOTES);
					$typ = htmlspecialchars($_POST["i_3"][$i], ENT_QUOTES);
					$pause = isset($_POST["i_12"][$i]) ? htmlspecialchars($_POST["i_12"][$i], ENT_QUOTES) : 0;
					if (is_numeric($pause)) { // Prüfen ob der Pausenwert eine Zahl ist. Wenn ja, wird er mit 60 auf Sekunden umgerechnet.
						$pause =  ($pause + 0) * 60;
					} else {
						$pause = 0;
					}
					//if(htmlspecialchars($_POST["i_3"][$i],ENT_QUOTES) == "HF"){$pause = 3600;} else {$pause = 0;}
					$username = htmlspecialchars($_POST["i_4"][$i], ENT_QUOTES);

					//Username sha256
					$username_sha256 = hash("sha256", $iOrg . '-' . $username);

					//PWD auf md5 mit Salt
					//for ($s = '', $j = 0, $z = strlen($a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789') - 1; $j != 32; $x = rand(0, $z), $s .= $a{$x}, $j++);
					$s =  random_bytes(32);
					$str = htmlspecialchars($_POST["i_5"][$i], ENT_QUOTES) . $s;
					$strmd5 = md5($str);
					$newpassword = $strmd5 . ':' . $s;
					$pwd = $newpassword;

					$email = htmlspecialchars($_POST["i_6"][$i], ENT_QUOTES);
					$bos = isset($_POST["i_7"][$i]) ? htmlspecialchars($_POST["i_7"][$i], ENT_QUOTES) : "";
					$telefon = isset($_POST["i_8"][$i]) ? htmlspecialchars($_POST["i_8"][$i], ENT_QUOTES) : "";
					$einsatzfaehig = 1;
					$notfallkontakt = isset($_POST["i_9"][$i]) ? htmlspecialchars($_POST["i_9"][$i], ENT_QUOTES) : "";
					$notfallinfo = isset($_POST["i_10"][$i]) ? htmlspecialchars($_POST["i_10"][$i], ENT_QUOTES) : "";
					$kommentar = isset($_POST["i_11"][$i]) ? htmlspecialchars($_POST["i_11"][$i], ENT_QUOTES) : "";
					$ausbildungen = isset($_POST["i_13"][$i]) ? htmlspecialchars($_POST["i_13"][$i], ENT_QUOTES) : "";

					//Werte im JSON
					$data = array();
					$data[0]["name"] = $name;
					$data[0]["dienstnummer"] = $dienstnummer;
					$data[0]["typ"] = $typ;
					$data[0]["username"] = $username;
					$data[0]["email"] = $email;
					$data[0]["telefon"] = $telefon;
					$data[0]["bos"] = $bos;
					$data[0]["kommentar"] = $kommentar;
					$data[0]["notfallkontakt"] = $notfallkontakt;
					$data[0]["notfallinfo"] = $notfallinfo;
					$data[0]["ausbildungen"] = $ausbildungen;
					$data[0]["einsatzfaehig"] = $einsatzfaehig;
					$data[0]["pause"] = $pause;


					$db_vorhanden = $db->prepare("SELECT UID FROM user WHERE UID = ? AND OID = ? ");
					$db_vorhanden->bindParam(1, $UID, PDO::PARAM_STR);
					$db_vorhanden->bindParam(2, $iOrg, PDO::PARAM_STR);
					$db_vorhanden->execute();
					//ErrorInfo
					$errorInfo = $db_vorhanden->errorInfo();
					echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
					
					$uvorhanden = 0;
					while ($res_mg = $db_vorhanden->fetch(PDO::FETCH_ASSOC)) {
						$uvorhanden++;
					}
					if ($uvorhanden > 0) { //es gibt den User schon --> Update
						if ($iPwUp == 1) {
							$data[0]["pwd"] = $pwd;
							$pwd_up = "";
						} else { //Passwort beibehalten
							$sql_query = $db->prepare("SELECT data, pwd FROM user WHERE `UID`= ? ");
							$sql_query->bindParam(1, $UID, PDO::PARAM_STR);
							$sql_query->execute();
							//ErrorInfo
							$errorInfo = $sql_query->errorInfo();
							echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
							
							$sql_json = $sql_query->fetch(PDO::FETCH_ASSOC);
							//$userjson =string_decrypt($sql_json["data"]);
							//$data_old = json_decode($userjson, true);
							//$data[0]["pwd"] = $data_old["pwd"];
							$data[0]["pwd"] = $sql_json["pwd"];
							$pwd_up = ", `pwd` = '" . $sql_json["pwd"] . "'";
						}
						//JSON erzeugen
						$encrypted = string_encrypt(json_encode($data, JSON_UNESCAPED_UNICODE));
						//$update = $db->prepare("UPDATE user SET `UID`='".$UID."',`OID`='".$iOrg."',`data`='".$encrypted."',`username`='".$username_md5."'".$pwd_up.",`einsatzfaehig`='".$einsatzfaehig."',`pause`='".$pause."' WHERE dienstnummer = '".$dienstnummer."' AND OID = '".$iOrg."'");
						$update = $db->prepare("UPDATE user SET `UID`= ? ,`OID`= ? ,`data`= ? ,`username`= ? " . $pwd_up . " WHERE `UID`= ? AND OID = ? ");
						$update->bindParam(1, $UID, PDO::PARAM_STR);
						$update->bindParam(2, $iOrg, PDO::PARAM_STR);
						$update->bindParam(3, $encrypted, PDO::PARAM_STR);
						$update->bindParam(4, $username_sha256, PDO::PARAM_STR);
						$update->bindParam(5, $UID, PDO::PARAM_STR);
						$update->bindParam(6, $iOrg, PDO::PARAM_STR);
						$update->execute() or die(print_r($update->errorInfo()));
						//ErrorInfo
						$errorInfo = $update->errorInfo();
						echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
						echo "User " . $name . " upgedated.<br>";
					} else { //neuer User
						$data[0]["pwd"] = $pwd; //Passwort einfügen
						//JSON erzeugen
						$encrypted = string_encrypt(json_encode($data, JSON_UNESCAPED_UNICODE));
						$insert = $db->prepare("INSERT INTO user (`UID`, `OID`, `data`, `username`, `pwd`, `FID`, `EID`) VALUES (?,?,?,?,?, '10', '-1')");
						$insert->bindParam(1, $UID, PDO::PARAM_STR);
						$insert->bindParam(2, $iOrg, PDO::PARAM_STR);
						$insert->bindParam(3, $encrypted, PDO::PARAM_STR);
						$insert->bindParam(4, $username_sha256, PDO::PARAM_STR);
						$insert->bindParam(5, $pwd, PDO::PARAM_STR);
						$insert->execute() or die(print_r($insert->errorInfo()));
						//ErrorInfo
						$errorInfo = $insert->errorInfo();
						echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
						
						echo "User " . $name . " neu erstellt.<br>";
					}
				}
				break; //Ende Importieren der Mitglieder
			case "logo": //Beginn Logo Upload
				$errorimg = $_FILES["image"]["error"]; //stores any error code resulting from the transfer

				$valid_extensions = array('jpeg', 'jpg', 'png'); // valid extensions
				//$valid_extensions = array('2', '3'); // 2 = JPEG, 3 = PNG
				$path = 'orglogos/'; // upload directory
				if ($_FILES['image'] || !empty($_POST["uOrg"])) {
					$img = $_FILES['image']['name']; //stores the original filename from the client
					$tmp = $_FILES['image']['tmp_name']; //stores the name of the designated temporary file
					$org = $_POST["uOrg"];



					// get uploaded file's extension
					$ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
					//$ext = exif_imagetype($img);
					// can upload same image using rand function
					//$final_image = $org.".".$ext;
					$final_image = $org . ".png";
					// check's valid format
					/*if(in_array($ext, $valid_extensions)){ 
						$path = $path.$final_image; 
						if(move_uploaded_file($tmp,$path)){*/
					$path = $path . $final_image;
					if (exif_imagetype($tmp) == 3) {
						move_uploaded_file($tmp, $path);
						$success = true;
					} else {
						if (imagepng(imagecreatefromstring(file_get_contents($tmp)), $path)) {
							$success = true;
						}
					}
					if ($success = true) {
						//if(imagepng(imagecreatefromstring(file_get_contents($tmp)), $path)){
						//Skalieren des Bildes
						list($iwidth, $iheight) = getimagesize($path);
						$r = $iwidth / $iheight;
						if ($iwidth != 1200 || $iheight != 1200) {
							if (1 > $r) {
								$newwidth = 1200 * $r; //1200px
								$newheight = 1200;
								$xshift = (1200 - $newwidth) / 2;
								$yshift = 0;
							} else {
								$newheight = 1200 / $r;
								$newwidth = 1200;
								$xshift = 0;
								$yshift = (1200 - $newheight) / 2;;
							}
							$src = imagecreatefrompng($path);
							//$dst = imagecreatetruecolor($newwidth, $newheight);
							$dst = imagecreatetruecolor(1200, 1200);
							//imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
							//imagealphablending($dst, false);
							//imagesavealpha($dst, true);

							imagealphablending($dst, false);
							imagesavealpha($dst, true);
							$bgcolor = imagecolorallocatealpha($dst, 0, 0, 0, 127);
							imagefill($dst, 0, 0, $bgcolor);

							imagecopyresampled($dst, $src, $xshift, $yshift, 0, 0, $newwidth, $newheight, $iwidth, $iheight);

							imagealphablending($dst, true);
							if (imagepng($dst, $path)) {
								echo "<img id='newlogo' src='$path' />";
							}
						} else {
							echo "<img id='newlogo' src='$path' />";
						}
						//	}
					} else {
						echo 'invalid';
					}
				}

				break; // Ende Logo Upload
		} //Ende Switch $do

		?>

		<!-- Feedback Overlay Anfang -->
		<div class="modal fade feedback" tabindex="-1" role="dialog" aria-labelledby="messanger" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="messanger"></h5>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary btn-reload" data-dismiss="modal">Schliessen</button>
					</div>
				</div>
			</div>
		</div>
		<!-- Feedback Overlay Ende -->
		<!-- Logo Upload Overlay Anfang -->
		<div class="modal fade logoupload" tabindex="-1" role="dialog" aria-labelledby="logouploadheader" aria-hidden="true">
			<div class="modal-dialog  modal-xl">
				<div class="modal-content">
					<div class="modal-header" id="logouploadheader">
						<h5 class="modal-title">Upload Organisationslogo</h5>
					</div>
					<div class="modal-body">
						Das Organisationslogo wird primär für die Einsatzberichte benötigt.<br>Das Organisationslogo muss eine Auflösung von mindestens 1200x1200px haben. Es wird auf diese Größe skaliert. Ist das Bildverhältnis nicht 1:1 werden transparente Ränder ergänzt.
						<br>Unterstützte Dateiformate sind JPEG, PNG, GIF und BMP.
						<div id="err"></div>
						<div id="preview" class="rounded ml-auto mr-auto mt-4 mb-4 d-block"><img id="currentorglogo" src="orglogos/logoupload.png"></div>
						<form id="logouploadform" enctype="multipart/form-data">
							<input type="hidden" id="uOrg" name="uOrg" value=""></input>
							<div class="input-group">
								<div class="custom-file">
									<input id="uploadlogo" type="file" class="custom-file-input" accept="image/*" name="image">
									<label class="custom-file-label" for="uploadlogo">Organisationslogo wählen</label>
								</div>
								<div class="input-group-append">
									<button type="submit" class="btn btn-primary submitlogoupload">Upload</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<!-- Logo Upload Overlay Ende -->
		<script>
			//Zufälligen Token erstellen - Buchstabe O fehlt.
			<?php //if($do != ""){?>
			let make_token = function(length) {
				var result = '';
				var characters = 'ABCDEFGH0123456789IJKLMNPQRSTUVWXYZ0123456789abcdefghijklmn0123456789opqrstuvwxyz0123456789';
				var charactersLength = characters.length;
				for (var i = 0; i < length; i++) {
					result += characters.charAt(Math.floor(Math.random() * charactersLength));
				}
				return result;
			}

			//Sonderzeichen durch HTML Code ersetzen - insbesondere für JSON

			let escapeHTML = function(text) {
				var map = {
					'&': '&amp;',
					'<': '&lt;',
					'>': '&gt;',
					'"': '&quot;',
					"'": '&#039;'
				};
				return text.replace(/[&<>"']/g, function(m) {
					return map[m];
				});
			}
			//Anführungszeichen vertauschen falls JSON Probleme

			let swapQuote = function(text) {
				var map = {
					'"': "'",
					"'": '"'
				};
				return text.replace(/["']/g, function(m) {
					return map[m];
				});
			}
			//OID erstellen - Keine Zahlen

			let make_oid = function(length) {
				var result = '';
				var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
				var charactersLength = characters.length;
				for (var i = 0; i < length; i++) {
					result += characters.charAt(Math.floor(Math.random() * charactersLength));
				}
				return result;
			}
			var checkl = checkle = checkc = checkn = checkm = checklogin = checkdnr = true;
		<?php //} ?>

			$(function() {
				let admintab = window.location.hash;
				if(admintab){
					$('#adminTab .nav-link.active').removeClass('active');
					$('.tab-pane.show.active').removeClass('show active');
					$('#adminTab '+admintab+'-tab').addClass('active');
					$(admintab).tab('show');console.log(admintab);
				}
				<?php if ($userlevel == 0 && is_numeric($userlevel)) { ?>
					//Umstellen der OID für DEV User
					$("#OID_select").on('click', 'a', function(e) {
						e.preventDefault();
						var oidsel = $(this).attr('data-oid');
						console.log(oidsel);
						//window.location.href = window.location.href.replace( /[\&#].*|$/, "?OID="+oidsel )
						var newhref = window.location.href;
						var temp = newhref.split(".");
						temp = temp.slice(0, -1);
						window.location.href = temp.join(".") + ".php?OID=" + oidsel;
					});
				<?php } ?>
				$("#adminTab").on('click', '.nav-link', function() {
					window.location.hash = $(this).attr('href');
				});
			});
		</script>

		<script type="text/javascript" src="vendor/js/trix.js"></script>
		<script type="text/javascript" src="js/admin.min.js"></script>
	</body>

	</html>
<?php
}
//Ende If Ausgabe nur bei userlevel kleiner 4
?>
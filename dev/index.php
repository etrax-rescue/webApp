<?php 
session_start();
session_gc();

if(isset($_SESSION["etrax"]["UID"]) && isset($_SESSION["etrax"]["EID"])) { //redirect auf einsatzstart.php wenn man bereits angemeldet ist
	header('Location: einsatz-start.php');
}elseif(isset($_SESSION["etrax"]["UID"]) && !isset($_SESSION["etrax"]["EID"])){
	header('Location: einsatzwahl.php');
}
$_SESSION['stillworking'] = time();
$_SESSION["etrax"]["strokewidth"] = isset($_SESSION["etrax"]["strokewidth"]) ? $_SESSION["etrax"]["strokewidth"] : 1;

require_once './vendor/autoload.php';
use Ahc\Jwt\JWT;

require "../../secure/info.inc.php";
require "../../secure/secret.php";
require "include/startseitentexte.php"; //Textelemente auf der Startseite
require "include/include.php"; //Textelemente auf der Startseite
include "include/verschluesseln.php";

// https://github.com/adhocore/php-jwt
// Instantiate with key, algo, maxAge (s) and leeway (s).
$jwt = new JWT($jwtsecret, 'HS256', 86400, 10);

if(isset($_POST["submited"])){//print_r($_POST);
	
	
	//API Key
	$login = htmlspecialchars($_POST["username"], ENT_QUOTES);
	$postOID = htmlspecialchars($_POST["oid"], ENT_QUOTES);
	$islicense = true;
		
	$isuser = $ispwd = false;
	//$user_sql = $db->prepare("SELECT EID,OID,UID,FID,username,data FROM user WHERE OID LIKE '".$postOID."'");
	$user_sql = $db->prepare("SELECT EID,OID,UID,FID,username,data,aktiveEID FROM user WHERE username = ? "); //Username wird direkt geprüft
	$user_sql->bindParam(1,$uname_temp, PDO::PARAM_STR);
	$uname_temp = hash("sha256",$postOID."-".$login,false);
	$user_sql->execute();
	//ErrorInfo
	$errorInfo = $user_sql->errorInfo();
	echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
	//$user_sql->execute($user_sql->errorInfo());
	while ($rowuser = $user_sql->fetch(PDO::FETCH_ASSOC)){
		//$usersalt = preg_split ('/:/' , $rowuser["username"]);
		//$username_str = htmlspecialchars(stripslashes($login), ENT_QUOTES).$usersalt[1];
		//$username = md5($username_str);
		
		//Prüfung ob Organisation eine gültige Lizenz hat
		$org_sql = $db->prepare("SELECT Laufzeit FROM lizenzen WHERE OID = ? ");
		$org_sql->bindParam(1,$rowuser["OID"], PDO::PARAM_STR);
		$org_sql->execute();
		//ErrorInfo
		$errorInfo = $org_sql->errorInfo();
		echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
		$laufzeit = 0;
		while ($roworg = $org_sql->fetch(PDO::FETCH_ASSOC)){
			$laufzeit = (strtotime($roworg["Laufzeit"]) > $laufzeit) ? strtotime($roworg["Laufzeit"]) : $laufzeit;
		}
		$islicense = $laufzeit >= time() ? true : false;
		//DEV hat dadurch kein Ablaufdatum
		$islicense = $rowuser["OID"] == "DEV" ? true : $islicense;
		if($rowuser["username"] == hash("sha256",$postOID."-".$login,false) && $islicense){
			$isuser = true;
			$userdata_decrypted = json_decode(substr(string_decrypt($rowuser["data"]), 1, -1));
			$user_passwort = $userdata_decrypted->pwd;
			$salt = preg_split ('/:/' , $user_passwort);
			$user_passwort = md5(htmlspecialchars(stripslashes($_POST["password"]), ENT_QUOTES).$salt[1]);
			if($salt[0] == $user_passwort){
				$ispwd = true;
				$_SESSION["etrax"]["adminEID"] = $user_eid = $rowuser["EID"];
				$_SESSION["etrax"]["adminOID"] = $_SESSION["etrax"]["OID"] = $user_OID = $rowuser["OID"];
				$_SESSION["etrax"]["adminID"] = $_SESSION["etrax"]["UID"] = $_SESSION["etrax"]["id"] = $user_UID = $rowuser["UID"];
				$_SESSION["etrax"]["FID"] = $user_FID = $rowuser["FID"];
				$_SESSION["etrax"]["name"] = $user_name = isset($userdata_decrypted->name) ? $userdata_decrypted->name : "";
				$_SESSION["etrax"]["username"] = $user_username = isset($username) ? $username : "";
				$_SESSION["etrax"]["dienstnummer"] = $user_dienstnummer = isset($userdata_decrypted->dienstnummer) ? $userdata_decrypted->dienstnummer : "";
				$_SESSION["etrax"]["typ"] = $user_typ = isset($userdata_decrypted->typ) ? $userdata_decrypted->typ : "";
				$_SESSION["etrax"]["email"] = $user_email = isset($userdata_decrypted->email) ? $userdata_decrypted->email : "";
				$_SESSION["etrax"]["telefon"] = $user_telefon = isset($userdata_decrypted->telefon) ? $userdata_decrypted->telefon : "";
				$_SESSION["etrax"]["bos"] = $user_bos = isset($userdata_decrypted->bos) ? $userdata_decrypted->bos: "";
				$_SESSION["etrax"]["einsatzfaehig"] = $user_einsatzfaehig = isset($userdata_decrypted->einsatzfaehig) ? $userdata_decrypted->einsatzfaehig : 0;
				$_SESSION["etrax"]["kommentar"] = $user_kommentar = isset($userdata_decrypted->kommentar) ? $userdata_decrypted->kommentar : "";
				$_SESSION["etrax"]["notfallkontakt"] = $user_notfallkontakt = isset($userdata_decrypted->notfallkontakt) ? $userdata_decrypted->notfallkontakt : "";
				$_SESSION["etrax"]["notfallinfo"] = $user_notfallinfo = isset($userdata_decrypted->notfallinfo) ? $userdata_decrypted->notfallinfo : "";
				$_SESSION["etrax"]["ausbildungen"] = $user_ausbildungen = isset($userdata_decrypted->ausbildungen) ? $userdata_decrypted->ausbildungen : "";
				$_SESSION["etrax"]["aunit"]='ha';
				$_SESSION["etrax"]["afactor"]='10000';
				$_SESSION["etrax"]["lunit"]='m';
				$_SESSION["etrax"]["lfactor"]='1';
				$_SESSION["etrax"]["token"] = $jwt->encode([
					'uid' => $rowuser["UID"]
				]);
				//setcookie("uid", $user_UID, 0, "/");
				$FID = explode(".",$user_FID);
				if(isset($FID[1])){
					$_SESSION["etrax"]["userlevel"] = $FID[0];
					$_SESSION["etrax"]["userrechte"] = $FID[1];
					$_SESSION["etrax"]["usertype"] = "administrator";
					$_SESSION["etrax"]["etraxadmin"] = true;
					$_SESSION["etrax"]["mapadmin"] = true;
					$_SESSION["etrax"]["googleAPI"] = $API_KEYS["google_api_places"];
					$_SESSION["etrax"]["aktiveEID"] = $rowuser["EID"];
					//setcookie("mapadmin", true, 0, "/");
					echo '<script>window.location.href = "einsatzwahl.php"</script>';
				}else{
					$_SESSION["etrax"]["etraxadmin"] = false;
					$_SESSION["etrax"]["mapadmin"] = false;
					$_SESSION["etrax"]["usertype"] = "registered";
					$_SESSION["etrax"]["aktiveEID"] = $rowuser["aktiveEID"];
					echo '<script>window.location.href = "einsatzwahl.php"</script>';
				}
				break; //Bei Erfolg wird die Schleife verlassen
			}
		}
	}
	if(!$isuser){
		$error= $islicense ? "User, Passwort oder Organisation ist falsch!" : "Die Organisation verfügt über keine gültige Lizenz für eTrax | rescue!"; 
	}elseif(!$ispwd){
		$error= $islicense ? "User, Passwort oder Organisation ist falsch!" : "Die Organisation verfügt über keine gültige Lizenz für eTrax | rescue!";
	}elseif(!$islicense){
		$error="Die Organisation verfügt über keine gültige Lizenz für eTrax | rescue!"; 
	}
}
include("include/header.html");
?>
		<script src="vendor/js/jquery-3.5.1.min.js"></script>
		<script src="vendor/js/bootstrap.bundle.min.js"></script>
		<?php
		if(!isset($_SESSION["etrax"]["UID"])){
		?>
		<script>
			sessionStorage.clear();
		</script>
		<?php } ?>
	</head>
	<body class="background home">
	<div id="mama">
		<div class="qrbtn">
		<span class="material-icons">
			<img src="img/qr_code.svg" alt="qr-Code für Appzuweisung">
		</span>
		</div>
		<div class="loginbtn">
			<div class="show_login">Login</div>
		</div>
		<div class="containerlogo">
		<!-- Logo -->
			<div class="landinglogocontainer float-sm-none float-lg-left col-sm-12 col-lg-12 col-xl-12">
					<h1 class="landinglogo">
							<img loading="lazy" src="img/Logo-eTrax-rescue.png" class="stream" alt="eTrax | rescue Logo">
					</h1>
			</div>
			<!-- Text -->
			<div class="landingtextcontainer white float-sm-none float-lg-left col-sm-12 col-lg-12 col-xl-12 text-center">
				<div class="landingclaim  text-center">
					<h2 class="text-left text-lg-right"><b class="d-block d-lg-inline-block">Personensuchen</b> professionell abwickeln.</h2>
					<div class="text-left text-lg-right">Effizient | Standardisiert | Dokumentiert</div>
				</div>
			</div>
		</div>
			<!-- Footer -->
		<footer class="landingfooter d-flex flex-column">
			<!-- Powered by -->
			<div class="poweredby ml-auto">
				<div class="poweredbytext">Powered by:</div>
				<div class="netidee"><a href="https://netidee.at/etrax-rescue" target="_blank"><img src="img/netidee-logo.png" alt="netidee Logo"></a></div>
			</div>
			<ul class="landingfootercontainer nav d-flex justify-content-center">
				<li class="nav-item">
				<a class="nav-link" href="https://get.etrax.at" target="_blank">Informationen zu eTrax | rescue</a>
				</li>
				<li class="nav-item">
				<a class="nav-link license_login" href="#">Lizenzhinweise</a>
				</li>
				<li class="nav-item">
				<a class="nav-link ds_login" href="#">Datenschutzhinweis</a>
				</li>
				<li class="nav-item">
				<a class="nav-link imp_login" href="#">Impressum</a>
				</li>
			</ul>
		</footer>
	</div>
	<div class="modal fade feedback" tabindex="-1" role="dialog" aria-labelledby="messanger" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="messanger"></h5>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Schliessen</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade qr-modal" tabindex="-1" role="dialog" aria-labelledby="messanger" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="messanger">App verknüpfen</h5>
				</div>
				<div class="modal-content p-3">
					<p>Den QR-Code mit der App einscannnen und in der App auf verbinden klicken</p>
					<?php $host = explode(".", $_SERVER['HTTP_HOST']); 
					$subname = ($host[0] == "www" || $host[0] == "") ? "app" : "app".$host[0];
					?>
					<!--img id="appqr" src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=<?php echo "https://".$subname.".".$host[1].".".$host[2]; ?>" alt="QR-Code mit App-Schnittstellen URL"-->
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade login-modal" tabindex="-1" role="dialog" aria-labelledby="messanger" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<!--div ID login-->
				<div id="login">
					<h5 class=" text-center" id="etraxlogo"><img src="img/etrax.png" alt="eTrax" /></h5>
					<?php
					if(isset($error)){
					echo '<div class="alert">'.$error.'</div>';
					}
					?>
					<form id="loginform" class="mb-4" action="<?php //echo $_SERVER['PHP_SELF']?>" method="post">
						<input type="hidden" id="submited" name="submited" value="">
						<div class="mb-3">
							<label for="username" class="sr-only">Organisation:</label>
							<select name="oid" id="oid" class="custom-select">
								<option value="">Organisation wählen!</option>
								<?php
								
								$org_sql = $db->prepare("SELECT OID,data FROM organisation WHERE aktiv = 1");
								$org_sql->execute() or die(print_r($org_sql->errorInfo(), true));
								while ($roworg = $org_sql->fetch(PDO::FETCH_ASSOC)){
									$orgdata_decrypted = json_decode(substr(string_decrypt($roworg["data"]), 1, -1));
									echo '<option value="'.$roworg["OID"].'">'.$orgdata_decrypted->kurzname.'</option>';
								}
								
								?>
							</select>
						</div>
						<div class="input-group mb-3">
							<div class="input-group-prepend">
								<label for="username" class="input-group-text" id="usernamelabel">Username</label>
							</div>
							<input type="text" class="form-control" name="username" id="username" aria-label="Username" aria-describedby="usernamelabel" value="<?php if(isset($_POST["username"])){echo htmlspecialchars($_POST["username"], ENT_QUOTES);}?>">
						</div>
						<div class="input-group mb-3">
							<div class="input-group-prepend">
								<label for="password" class="input-group-text" id="pwlabel">Passwort</label>
							</div>
							<input type="password" class="form-control" name="password" id="password" aria-label="Passwort" aria-describedby="pwlabel">
						</div>
						<div class="submitter">
							<div class="mb-2">Zum Einloggen klicke auf<br><b class="submittext"></b></div>
							<a href="#"><i class="material-icons go1" >extension</i></a>
							<a href="#"><i class="material-icons go2" >android</i></a>
							<a href="#"><i class="material-icons go3" >cloud</i></a>
							<a href="#"><i class="material-icons go4" >https</i></a>
							<a href="#"><i class="material-icons go5" >train</i></a>
						</div>
					</form>
						<div class="emailreset text-info">Passwort vergessen</div>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade pwd-modal" tabindex="-1" role="dialog" aria-labelledby="pwdresettxt" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<!--div ID login-->
				<div id="pwdreset">
					<h5 class="text-center mt-4" id="pwdresettxt">Passwort Vergessen</h5>
					<p class="text-info resetinfo"></p>
					<div class="text-info m-4 p-2 rounded">
						Um ein neues Passwort zugeschickt zu bekommen, tragen Sie bitte ihre E-mailadresse ein. Wird ein übereinstimmender Eintrag gefunden, erhalten sie an diese Adresse einen Link zur Änderung des Passwortes zugeschickt. Bitte kontrollieren sie ggf. ihren Spamordner!
						<form id="resetform" class="m-4 text-center" action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
							<input type="hidden" id="resetpwd" name="resetpwd" value="true">
							<select name="oid4reset" id="oid4reset" class="form-control">
								<option value="">Organisation wählen!</option>
								<?php
								
								$org_sql = $db->prepare("SELECT OID,data FROM organisation WHERE aktiv = 1");
								$org_sql->execute() or die(print_r($org_sql->errorInfo(), true));
								while ($roworg = $org_sql->fetch(PDO::FETCH_ASSOC)){
									$orgdata_decrypted = json_decode(substr(string_decrypt($roworg["data"]), 1, -1));
									echo '<option value="'.$roworg["OID"].'">'.$orgdata_decrypted->kurzname.'</option>';
								}
								
								
								?>
							</select>
							<label for="email" class="sr-only">E-mailadresse:</label>
							<input type="email" class="form-control" size="24" maxlength="50" name="email2reset" id="email" placeholder="E-mailadresse" value=""  required tabindex="1">
							<button type="submit" class="form-control btn btn-primary reset">Passwort Reset anfordern</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		jQuery(function(){var s=Math.round(4*Math.random())+1;jQuery(".submittext").text(["das Puzzlestück","den Roboter","die Wolke","das Schloss","den Zug"][s-1]),jQuery("a .material-icons").click(function(a){if(a.preventDefault(),""!=jQuery("#oid").val()){if(jQuery("#oid").removeClass("alert"),jQuery(this).hasClass("go"+s)){var e=jQuery("#username").val();jQuery("#submited").val(e),jQuery("#loginform").submit()}}else jQuery("#oid").focus().addClass("alert")})});
	</script>
	<!-- Datenschutz Info -->
		<div class="modal fade datenschutz" tabindex="-1" role="dialog" aria-labelledby="datenschutz" aria-hidden="true">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="modal-title" id="datenschutz">Hinweise zum Datenschutz</h1>
					</div>
					<div class="modal-body">
						<?php
						echo $text["datenschutz"];
						?>
						
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary btn-reload" data-dismiss="modal">Schliessen</button>
					</div>
				</div>
			</div>
		</div>
	<!-- Datenschutz Info Ende -->
	<!-- Lizenz Info -->
		<div class="modal fade license" tabindex="-1" role="dialog" aria-labelledby="license" aria-hidden="true">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="modal-title" id="license">Lizenz Hinweise</h1>
					</div>
					<div class="modal-body">
						<?php
						echo $text["license"];
						?>
						
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary btn-reload" data-dismiss="modal">Schliessen</button>
					</div>
				</div>
			</div>
		</div>
	<!-- Lizenz Info Ende -->
	<!-- Impressum -->
		<div class="modal fade impressum" tabindex="-1" role="dialog" aria-labelledby="imp_h" aria-hidden="true">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="modal-title" id="imp_h">Impressum</h1>
					</div>
					<div class="modal-body">
						<?php
						echo $text["impressum"];
						?>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary btn-reload" data-dismiss="modal">Schliessen</button>
					</div>
				</div>
			</div>
		</div>
	<!-- Impressum Ende -->
	<!-- PWD Reset Info -->
		<div class="modal fade resetfeedback" tabindex="-1" role="dialog" aria-labelledby="reset_h" aria-hidden="true">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="modal-title" id="reset_h">Passwort Zurücksetzen</h1>
					</div>
					<div class="modal-body">
						<h5 class="bg-info m-4 p-4 rounded" id="resetverarbeitung">Ihre Anfrage wird gerade verarbeitet</h5>
						<h5 class="bg-success m-4 p-4 rounded" id="resetverarbeitet">Ihre Anfrage wurde verarbeitet. Bitte kontrollieren sie ihre Mailbox und überprüfen sie auch ihren Spam Ordner sollte kein Mail für das Einrichten eines neuen Passworts im Posteingang ankommen.</h5>
						<h5 class="bg-danger m-4 p-4 rounded text-white" id="emailerror"></h5>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary btn-reload" data-dismiss="modal">Schliessen</button>
					</div>
				</div>
			</div>
		</div>
	<!-- eTrax Info Ende -->
		<?php 
		//Neues Passwort vergeben
		if(isset($_POST["resetpwd"])){
			if(isset($_POST["email2reset"])){ ?>
				<script>
				// Modal für Feedback anzeigen
				jQuery(function() {
					jQuery('.resetfeedback').modal('show');
					jQuery('#resetverarbeitet, #emailerror').hide();
				});
				
				</script> 
					
				<?php
				function generate_string($input, $strength = 16) {
							$input_length = strlen($input);
							$random_string = '';
							for($i = 0; $i < $strength; $i++) {
								$random_character = $input[mt_rand(0, $input_length - 1)];
								$random_string .= $random_character;
							}
						 
							return $random_string;
						}
				
				$sendmail = false;
				$message_core = "";
				$email2reset = mb_strtolower(htmlspecialchars($_POST["email2reset"], ENT_QUOTES), 'UTF-8');
				$oid4reset = htmlspecialchars($_POST["oid4reset"], ENT_QUOTES);
				$db_mitglieder = $db->prepare("SELECT * FROM user WHERE OID = ? ");
				$db_mitglieder->bindParam(1,$oid4reset, PDO::PARAM_STR);
				$db_mitglieder->execute();
				//ErrorInfo
				$errorInfo = $db_mitglieder->errorInfo();
				echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
				$email_t = $uid_t = "";
				while ($res_mg = $db_mitglieder->fetch(PDO::FETCH_ASSOC)){
					$data_user_json = json_decode(substr(string_decrypt($res_mg['data']), 1, -1));
					$email_t = isset($data_user_json->email) ? $data_user_json->email : "";
					$username_t = isset($data_user_json->username) ? $data_user_json->username : "";
					$uid_t = $res_mg['UID'];
					if($email2reset == mb_strtolower($email_t, 'UTF-8')){
						//neu setzen des Passworts
						//Zufälligen Schlüssel erzeugen - credit: https://code.tutsplus.com/tutorials/generate-random-alphanumeric-strings-in-php--cms-32132
						$permitted_chars = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						
						
						$randomkey = generate_string($permitted_chars, 36).":".(time()+60*$resetlinkgueltigkeit);
						
						//Für den User wird ein neuer RandomKey angelegt und dieser per Mail verschickt
						$update = $db->prepare("UPDATE user SET `pwdresetkey`= ? WHERE `UID`= ? AND OID = ? ");
						$update->bindParam(1,$randomkey, PDO::PARAM_STR);
						$update->bindParam(2,$uid_t, PDO::PARAM_STR);
						$update->bindParam(3,$oid4reset, PDO::PARAM_STR);
						$update->execute() or die(print_r($update->errorInfo()));
						//ErrorInfo
						$errorInfo = $update->errorInfo();
						echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
						
						// Mail variablen
						$resetlink = empty($_SERVER['HTTPS']) ? 'http://'.$_SERVER["HTTP_HOST"].'/forgot.php?p='.$randomkey : 'https://'.$_SERVER["HTTP_HOST"].'/forgot.php?p='.$randomkey;
						$from = $mailsettings["from"];
						$to = $email2reset;
						$subject = 'Passwort Reset wurde angefordert';
						$message_core .= '
<br><h4>Username '.$username_t.'</h4>Um ihr Passwort neu zu setzen, folgen sie bitte bis '.date("d.m.Y H:i:s",time()+60*$resetlinkgueltigkeit).' diesem Link:
<a href="'.$resetlink.'" alt="Link folgen um das Passwort neu zu setzen">'.$resetlink.'</a><br>
						';
						$sendmail = true;
						
					?>
					<script>
					// Modal für Feedback anzeigen
					jQuery(function() {
						jQuery('#resetverarbeitung').hide();
						jQuery('#resetverarbeitet').show(); // Im Feedback Modal Verabeitungsabschluss anzeigen
					});
					</script>
					<?php
						
					}
					
				}
				
				//Anzeigen der Fehlermeldung
				if(!$sendmail){
					?>
					<script>
					jQuery(function() {
						jQuery('#resetverarbeitung').hide();
						jQuery('#resetverarbeitet').hide();
						jQuery('#emailerror').html('Die Emailadresse <?php echo $email2reset;?> existiert nicht, bitte überprüfen sie ihre eingabe').show();
					});
				</script>
				<?php

					$email2reset = "";
					$_POST["resetpwd"] = NULL;
				}
				
				//Email wird nach Durchlauf der Schleife geschickt, falls eine E-mailadresse bei mehreren Usern verwendet wird.
				if($sendmail){
					$separator = md5(time());
					$eol = PHP_EOL;

					// main header
					$headers  = "From: ".$from.$eol;
					$headers .= "MIME-Version: 1.0".$eol; 
					$headers .= "Content-Type: multipart/mixed; boundary=\"".$separator."\"";

					// message
					$message = '
					Auf '.$mailsettings["installation"].' wurde am '.date("d.m.Y").' um '.date("H:i:s").' ein Reset des Passworts angefordert. Es wurden folgende Usernamen für ihre E-mailadresse gefunden:'.$message_core.

'Sollten sie keinen Reset des Passworts angefordert haben, ignorieren sie das Mail bitte.';
					$body = "--".$separator.$eol;
					$body .= "Content-Type: text/html; charset=\"UTF-8\"".$eol;
					$body .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
					$body .= $message.$eol;
					// send message
					if (mail($to, $subject, $body, $headers)) {
						//echo "mail send ... OK";
					} else {
						//echo "mail send ... ERROR";
					}
				}
			
			}
			?>
			
			<?php 
		}
		?>
		<script>
		$(function() {
			$("body").on("click",".show_login",function(){
				$(".login-modal").modal('show');
			});
			$("body").on("click",".qrbtn",function(){
				$(".qr-modal").modal('show');
			});
			$("body").on("click",".emailreset",function(){
				$(".login-modal").modal('hide');
				$(".pwd-modal").modal('show');
			});
			<?php 
				if(isset($error)){ 
					echo '$(".login-modal").modal("show");';
				} 
			?>
			$("body").on("click",".ds_login",function(){
				$(".datenschutz").modal('show');
			});
			$("body").on("click",".license_login",function(){
				$(".license").modal('show');
			});
			$("body").on("click",".imp_login",function(){
				$(".impressum").modal('show');
			});
			$("body").on("click",".about_login",function(){
				$(".about").modal('show');
			});
			
			$("body").on("click",".show_contact",function(){
				var ccase = $(this).data('case');
				<?php 
				echo isset($text["email"][0]) ? 'var a = "'.$text["email"][0].'";' : 'var a = "";';
				echo isset($text["email"][1]) ? 'var b = "'.$text["email"][1].'";' : 'var b = "";';
				echo isset($text["email"][2]) ? 'var c = "'.$text["email"][2].'";' : 'var c = "";';
				echo isset($text["email"][4]) ? 'var d = "'.$text["email"][4].'";' : 'var d = "";';
				echo isset($text["email"][5]) ? 'var e = "'.$text["email"][5].'";' : 'var e = "";';
				?>
								
				if(ccase != "Info"){
					$(this).hide();
					$(".contactdata_"+ccase).append("Bei Fragen zum "+ccase+" kontaktieren sie bitte <a href='mailto:"+a+b+c+"@"+d+e+"'>"+a+b+c+"[at]"+d+e+"</a>");
				} else {
					$(".contactdata_about").append("<a href='mailto:"+a+b+c+"@"+d+e+"'>"+a+b+c+"[at]"+d+e+"</a>");
				}
			});
			
			<?php if(isset($_POST["submited"])){ ?>
				$("#mama").show();
			$db=null; //Reset der DB Verbindung
			<?php } ?>
		});
		</script>
	</body>
</html>
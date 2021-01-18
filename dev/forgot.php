<?php
$p = isset($_GET["p"]) ? htmlspecialchars($_GET["p"], ENT_QUOTES) : false;
$p_post = isset($_POST["p"]) ? htmlspecialchars($_POST["p"], ENT_QUOTES) : false;
$step = isset($_POST["step"]) ? htmlspecialchars($_POST["step"], ENT_QUOTES) : 0;
if($p != false || $p_post != false){
	
	//Prüfen ob die Organisation schon angelegt wurde
	$datei = "../../secure/info.inc.php";
	require($datei);
	require "../../secure/secret.php";
	require "include/verschluesseln.php";
	$errormsg = "";
	
	$tokencheck = false;
	$tokenexpired = true;
	$select = $db->prepare("SELECT COUNT(*) from user WHERE pwdresetkey = ? ");
	$select->bindParam(1,$p_temp, PDO::PARAM_STR);
	$p_temp = $p != false ? $p : $p_post;
	if($select->execute()) {
		if ($select->fetchColumn() > 0) {
			//token vorhanden
			$tokencheck = true;
			if($tokencheck){
				$tokensplit = $p != false ? explode(":",$p): explode(":",$p_post);
				$tokenexpired = $tokensplit[1] >= time() ? false : true;
			}
		} else { // token nicht vorhanden
			$tokencheck = false;
		}
	} else {
		$errormsg .= "SQL Error <br />";
		$errormsg .=  $select->queryString."<br />";
		$errormsg .=  $select->errorInfo()[2];
		$tokencheck = false;
	}

	echo $errormsg;
	
	//Passwort serverseitig prüfen
	$pwderrors = "";
	if($step == 1){
		$pwd = htmlspecialchars($_POST["syspwd"], ENT_QUOTES);
		$p = $p_post;
		
		if (strlen($pwd) < 8) {
			$pwderrors .= "<h5 class='bg-danger p-4 rounded'>Das Passwort ist zu kurz</h5>";
		}

		if (!preg_match("#[0-9]+#", $pwd)) {
			$pwderrors .= "<h5 class='bg-danger p-4 rounded'>Das Passwort enthält keine Zahl</h5>";
		}

		if (!preg_match("#[a-z]+#", $pwd)) {
			$pwderrors .= "<h5 class='bg-danger p-4 rounded'>Das Passwort enthält keine Kleinbuchstaben</h5>";
		}     
		if (!preg_match("#[A-Z]+#", $pwd)) {
			$pwderrors .= "<h5 class='bg-danger p-4 rounded'>Das Passwort enthält keine Großbuchstaben</h5>";
		}   
		$step = ($pwderrors == "") ? $step : 0;
	}
	

	require("include/header.html");
?>
	
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<style>
		body {
			background: url("v5/img/background-rescue.jpg") no-repeat center center fixed; 
			-webkit-background-size: cover;
			-moz-background-size: cover;
			-o-background-size: cover;
			background-size: cover;
		}
		/* The message box is shown when the user clicks on the password field */
		#message {
		  display:none;
		  background: #f1f1f1;
		  color: #000;
		  position: relative;
		  padding: 20px;
		  margin-top: 10px;
		}

		#message p {
		  padding: 10px 35px;
		  font-size: 18px;
		}

		/* Add a green text color and a checkmark when the requirements are right */
		.valid {
		  color: green;
		}

		.valid:before {
		  position: relative;
		  left: -35px;
		  content: "&#10004;";
		}

		/* Add a red text color and an "x" icon when the requirements are wrong */
		.invalid {
		  color: red;
		}

		.invalid:before {
		  position: relative;
		  left: -35px;
		  content: "&#10006;";
		}
	</style>
  </head>
  <body class="bg-light">
	<div class="rounded m-4" style="background-color:rgba(255,255,255,0.5);">
		<form target="_self" action="forgot.php" method="post">
			<div class="container">
				<div class="py-5 text-center">
					<img class="mb-4" src="img/etrax.png" alt="eTrax | rescue Logo">
					<h1>Passwort neu setzen</h1>
				</div>
		<?php
		$step = !$tokenexpired ? $step : 98;//Token ist abgelaufen
		$step = $tokencheck ? $step : 99;//Token wurde nicht gefunden
		switch($step){
			case "0": //Willkommenstext
			require "include/include.php";
			?>
				<?php echo $pwderrors; ?>
				<p class="lead"><h5 class="p-3">Um das Passwort zurückzusetzen, füllen sie Bitte das folgende Formular aus:</h5></p>
				
				
				<div class="form-group">
					<label for="oid">Organisation:</label>
						<select name="oid" id="oid" class="form-control">
							<option value="">Organisation wählen!</option>
							<?php
							
							$org_sql = $db->prepare("SELECT OID,data FROM organisation WHERE aktiv = 1");
							$org_sql->execute($org_sql->errorInfo());
							//ErrorInfo
							$errorInfo = $org_sql->errorInfo();
							echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
							while ($roworg = $org_sql->fetch(PDO::FETCH_ASSOC)){
								$orgdata_decrypted = json_decode(substr(string_decrypt($roworg["data"]), 1, -1));
								echo '<option value="'.$roworg["OID"].'">'.$orgdata_decrypted->kurzname.'</option>';
							}
							
							?>
						</select>
						<small class="text-info">Bitte wählen sie Ihre Organisation aus.</small>
				</div>
				<?php
				$db_mitglieder = $db->prepare("SELECT * FROM user WHERE pwdresetkey = ? ");
				$db_mitglieder->bindParam(1,$p, PDO::PARAM_STR);
				$db_mitglieder->execute();
				//ErrorInfo
				$errorInfo = $db_mitglieder->errorInfo();
				echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
				while ($res_mg = $db_mitglieder->fetch(PDO::FETCH_ASSOC)){
					$data_user_json = json_decode(substr(string_decrypt($res_mg['data']), 1, -1));
					$username = isset($data_user_json->username) ? $data_user_json->username : "";
				}
				?>
				<div class="form-group">
					<label for="sysuser">Username (Login)</label>
					<input type="text" name="" id="sysuser" class="form-control" placeholder="" disabled value="<?php echo $username; ?>">
				</div>
				<div class="form-group">
					<label for="syspwd">Passwort</label>
					<input type="password" name="syspwd" id="syspwd" class="form-control checkJSON" placeholder="Passwort" required value="">
					<small id="PasswortHelp" class="form-text">Das Passwort muss folgende Kriterien erfüllen:</small>
					<small id="letter" class="text-danger ml-4">Kleinbuchstaben</small><br>
					<small id="capital" class="text-danger ml-4">Großbuchstaben</small><br>
					<small id="number" class="text-danger ml-4">Zahlen</small><br>
					<small id="length" class="text-danger ml-4">Mindestens 8 Zeichen</small><br>
				</div>
				<div class="form-group">
					<label for="syspwd">Passwort wiederholen</label>
					<input type="password" name="resyspwd" id="resyspwd" class="form-control checkJSON" placeholder="Passwort wiederholen" required value="">
					<small id="match" class="text-danger ml-4">Die Passwörter müssen übereinstimmen</small><br>
				</div>
				
				<input type="hidden" name="step" value="<?php echo $step+1;?>"></input>
				<input type="hidden" name="p" value="<?php echo $p;?>"></input>
				<div class="mx-auto order-md-1 text-center m-4 pb-4">	
					<button type="submit" class="btn btn-secondary abschliessen" disabled>Passwort setzen</button>
				</div>
			
			
		<?php
			break;//default
			case "1":
			//Oroganisationsdaten holen
			$oid4reset = htmlspecialchars($_POST["oid"], ENT_QUOTES);
			$pwd = htmlspecialchars($_POST["syspwd"], ENT_QUOTES);
			$setsuccess = false;
			
			$db_mitglieder = $db->prepare("SELECT * FROM user WHERE OID = ? AND pwdresetkey = ? ");
			$db_mitglieder->bindParam(1,$oid4reset, PDO::PARAM_STR);
			$db_mitglieder->bindParam(2,$p_post, PDO::PARAM_STR);
			$db_mitglieder->execute();
			//ErrorInfo
			$errorInfo = $db_mitglieder->errorInfo();
			echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
			$email_t = $uid_t = "";
			while ($res_mg = $db_mitglieder->fetch(PDO::FETCH_ASSOC)){
				$data_user_json = json_decode(substr(string_decrypt($res_mg['data']), 1, -1));
				$email_t = isset($data_user_json->email) ? $data_user_json->email : "";
				$name = isset($data_user_json->name) ? $data_user_json->name : "";
				$dienstnummer = isset($data_user_json->dienstnummer) ? $data_user_json->dienstnummer : "";
				$typ = isset($data_user_json->typ) ? $data_user_json->typ : "";
				$pause = (isset($data_user_json->pause) && is_numeric($data_user_json->pause)) ? $data_user_json->pause : 0; 
				$username = isset($data_user_json->username) ? $data_user_json->username : "";
				$ausbildungen = isset($data_user_json->ausbildungen) ? $data_user_json->ausbildungen : "";
				$email = isset($data_user_json->email) ? $data_user_json->email : "";
				$bos   = isset($data_user_json->bos) ? $data_user_json->bos : "";
				$telefon = isset($data_user_json->telefon) ? $data_user_json->telefon : "";
				$einsatzfaehig = isset($data_user_json->einsatzfaehig) ? $data_user_json->einsatzfaehig : "0";
				$notfallkontakt = isset($data_user_json->notfallkontakt) ? $data_user_json->notfallkontakt : "";
				$notfallinfo = isset($data_user_json->notfallinfo) ? $data_user_json->notfallinfo : "";
				$kommentar = isset($data_user_json->kommentar) ? $data_user_json->kommentar : "";
				$uid_t = $res_mg['UID'];
				$pwdresetkey = $res_mg['pwdresetkey'];
				if($pwdresetkey == $p_post){
					//neu setzen des Passworts
					//PWD auf md5 mit Salt
					for ($s = '', $j = 0, $z = strlen($a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')-1; $j != 32; $x = rand(0,$z), $s .= $a{$x}, $j++); 
					$str = $pwd.$s;
					$strmd5 = md5($str);
					$newpassword = $strmd5.':'.$s;
					$pwd = $newpassword;
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
					$data[0]["pwd"] = $pwd;
					//JSON erzeugen
					$encrypted = string_encrypt(json_encode($data, JSON_UNESCAPED_UNICODE));
					$update = $db->prepare("UPDATE user SET `data`= ? ,`pwd`= ? ,`pwdresetkey`='' WHERE `UID`= ? AND OID = ? ");
					$update->bindParam(1,$encrypted, PDO::PARAM_STR);
					$update->bindParam(2,$newpassword, PDO::PARAM_STR);
					$update->bindParam(3,$uid_t, PDO::PARAM_STR);
					$update->bindParam(4,$oid4reset, PDO::PARAM_STR);
					$update->execute() or die(print_r($update->errorInfo()));
					//ErrorInfo
					$errorInfo = $update->errorInfo();
					echo strlen($errorInfo[2]) > 0 ? ("<br><b>MySQL Error:</b> ".$errorInfo[2]) : "";
					if($select->execute()) {
						$setsuccess = true;
					} else {
						$errormsg .= "SQL Error <br />";
						$errormsg .=  $select->queryString."<br />";
						$errormsg .=  $select->errorInfo()[2];
						$setsuccess = false;
					}
				}
			}
			
		?>
				
				<?php if($setsuccess){ ?>
					<div class="p-3 mb-2 bg-light rounded">
						<h3 class="p-3 mb-2 bg-success">Das Passwort wurde erfolgreich geändert.</h3>
						<h5>Link zum Login: <a href="index.php" target="_blank" alt="Link zur Startseite">&rarr; eTrax | rescue</a></h5>
						
					</div>
				<?php } else {?>
					<div class="p-3 mb-2 bg-light rounded">
						<h3 class="p-3 mb-2 bg-error">Das Passwort konnte nicht gesetzt werden.</h3>
						<p class="text-info">Der Server meldet:<br><?php echo $errormsg; ?></p></span>
						
					</div>
				<?php }?>
		<?php
			break;//Ende Schritt 2
			case "98": //Token ist abgelaufen
		?>	
				<h5 class="p-3 mb-2 bg-danger text-white rounded">Dieser Token ist abgelaufen.</h5>
				
				
		<?php
			break;//
			case "99": //Token existiert nicht
		?>	
				<h5 class="p-3 mb-2 bg-danger text-white rounded">Der übermittelte Token konnte nicht gefunden werden. Bitte kopieren sie den Link erneut und vergewissern sie sich, dass er vollständig ist.</h5>
				
				
		<?php
			break;//Ende Fehlerhafte ID
		} //Ende switch
		?>
			</div>
		</form>

		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="vendor/js/jquery-3.5.1.min.js"></script>
		<script src="v5/js/bootstrap.bundle.min.js"></script>
		<script>
			$('#syspwd').keyup(function(){
				var pwd = ($(this).val());
				var repwd = ($('#resyspwd').val());
				if (pwd.length >= 8) {
					$('#length').removeClass("text-danger");
					$('#length').addClass("text-success");
					var checkl = true;
				} else {
					$('#length').addClass("text-danger");
					$('#length').removeClass("text-success");
					var checkl = false;
				}
				if (pwd.match(/[a-z]/)) {
					$('#letter').removeClass("text-danger");
					$('#letter').addClass("text-success");
					var checkle = true;
				} else {
					$('#letter').addClass("text-danger");
					$('#letter').removeClass("text-success");
					var checkle = false;
				}
				if (pwd.match(/[A-Z]/)) {
					$('#capital').removeClass("text-danger");
					$('#capital').addClass("text-success");
					var checkc = true;
				} else {
					$('#capital').addClass("text-danger");
					$('#capital').removeClass("text-success");
					var checkc = false;
				}
				if (pwd.match(/\d/)) {
					$('#number').removeClass("text-danger");
					$('#number').addClass("text-success");
					var checkn = true;
				} else {
					$('#number').addClass("text-danger");
					$('#number').removeClass("text-success");
					var checkn = false;
				}
				
				if (pwd === repwd) {
					$('#match').removeClass("text-danger");
					$('#match').addClass("text-success");
					var checkm = true;
				} else {
					$('#match').addClass("text-danger");
					$('#match').removeClass("text-success");
					var checkm = false;
				}
				
				if(checkl && checkle && checkc && checkn && checkm){
					$('.abschliessen').attr("disabled",false)
					$('.abschliessen').addClass("btn-primary");
					$('.abschliessen').removeClass("btn-secondary");
				} else {
					$('.abschliessen').attr("disabled",true)
					$('.abschliessen').addClass("btn-secondary");
					$('.abschliessen').removeClass("btn-primary");
				}
			});
			
			$('#resyspwd').keyup(function(){
				var pwd = ($('#syspwd').val());
				var repwd = ($(this).val());
				
				if (pwd === repwd) {
					$('#match').removeClass("text-danger");
					$('#match').addClass("text-success");
					var checkrem = true;
				} else {
					$('#match').addClass("text-danger");
					$('#match').removeClass("text-success");
					var checkrem = false;
				}
				
				if(checkrem){
					$('.abschliessen').attr("disabled",false)
					$('.abschliessen').addClass("btn-primary");
					$('.abschliessen').removeClass("btn-secondary");
				} else {
					$('.abschliessen').attr("disabled",true)
					$('.abschliessen').addClass("btn-secondary");
					$('.abschliessen').removeClass("btn-primary");
				}
			});
			
			
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

			
		</script>
	</div>
	
  </body>
</html>

<?php
	
} else {
	echo "No Output here";
}//Falls kein GET p Wert übergeben wird, wird nichts angezeigt
	
	
?>
<?php
$id = isset($_GET["id"]) ? htmlspecialchars($_GET["id"]) : false;
$id_post = (isset($_POST["id"])) ? htmlspecialchars($_POST["id"]) : false;
$step = isset($_POST["step"]) ? htmlspecialchars($_POST["step"]) : 0;

//Zulässige Schlüssel für Pretest
$pretest['3bdsfszRjowH@etrax.at'] = array('oid' => 'bdsfs', 'token' => '3bdsfszRjowH', 'name' => 'A1');
$pretest['MdUcLhZu9UUn@etrax.at'] = array('oid' => 'MdUcL', 'token' => 'MdUcLhZu9UUn', 'name' => 'A2');
$pretest['azoeU15yIA9U@etrax.at'] = array('oid' => 'azoeU', 'token' => 'azoeU15yIA9U', 'name' => 'A3');
$pretest['yn7Jxarz2q3V@etrax.at'] = array('oid' => 'ynJxa', 'token' => 'yn7Jxarz2q3V', 'name' => 'A4');
$pretest['iHv74wg2lV6o@etrax.at'] = array('oid' => 'iHvwg', 'token' => 'iHv74wg2lV6o', 'name' => 'B1');
$pretest['B28FOkPWxvoP@etrax.at'] = array('oid' => 'BFOkP', 'token' => 'B28FOkPWxvoP', 'name' => 'B2');
$pretest['4PvZwZM7Y8AE@etrax.at'] = array('oid' => 'PvZwZ', 'token' => '4PvZwZM7Y8AE', 'name' => 'B3');
$pretest['Izmeca6BbJT8@etrax.at'] = array('oid' => 'Izmec', 'token' => 'Izmeca6BbJT8', 'name' => 'B4');
$pretest['pNBjKcuEEXaG@etrax.at'] = array('oid' => 'pNBjK', 'token' => 'pNBjKcuEEXaG', 'name' => 'B5');
$pretest['xUyZmt4mR0La@etrax.at'] = array('oid' => 'xUyZm', 'token' => 'xUyZmt4mR0La', 'name' => 'B6');
$pretest['7RpXRe2J2zBT@etrax.at'] = array('oid' => 'RpXRe', 'token' => '7RpXRe2J2zBT', 'name' => 'B7');
$pretest['vZr8ffN3nMAX@etrax.at'] = array('oid' => 'vZrff', 'token' => 'vZr8ffN3nMAX', 'name' => 'B8');
$pretest['hIm2iH3dPbpu@etrax.at'] = array('oid' => 'hImiH', 'token' => 'hIm2iH3dPbpu', 'name' => 'C1');
$pretest['C4w9NeA6noPA@etrax.at'] = array('oid' => 'CwNeA', 'token' => 'C4w9NeA6noPA', 'name' => 'C2');
$pretest['GrrNJ2smC1Aj@etrax.at'] = array('oid' => 'GrrNJ', 'token' => 'GrrNJ2smC1Aj', 'name' => 'C3');
$pretest['YNhggk6zsELr@etrax.at'] = array('oid' => 'YNhgg', 'token' => 'YNhggk6zsELr', 'name' => 'C4');
$pretest['TpzwvYLcZtIw@etrax.at'] = array('oid' => 'Tpzwv', 'token' => 'TpzwvYLcZtIw', 'name' => 'D1');
$pretest['a4pwTQyzgJ9Z@etrax.at'] = array('oid' => 'apwTQ', 'token' => 'a4pwTQyzgJ9Z', 'name' => 'E1');
$pretest['CsC3KAGY8yEs@etrax.at'] = array('oid' => 'CsCKA', 'token' => 'CsC3KAGY8yEs', 'name' => 'F1');
$pretest['n1eaiu2urUgv@etrax.at'] = array('oid' => 'neaiu', 'token' => 'n1eaiu2urUgv', 'name' => 'F2');
$pretest['cEFx7zLmnw2E@etrax.at'] = array('oid' => 'cEFxz', 'token' => 'cEFx7zLmnw2E', 'name' => 'F3');
$pretest['Lq8TxLMzHaI7@etrax.at'] = array('oid' => 'LqTxL', 'token' => 'Lq8TxLMzHaI7', 'name' => 'G1');
$pretest['YVNPAu2sYFSP@etrax.at'] = array('oid' => 'YVNPA', 'token' => 'YVNPAu2sYFSP', 'name' => 'G2');
$pretest['aY8wC6a1A1wg@etrax.at'] = array('oid' => 'aYwCa', 'token' => 'aY8wC6a1A1wg', 'name' => 'G3');
$pretest['mUqV47H5yenB@etrax.at'] = array('oid' => 'mUqVH', 'token' => 'mUqV47H5yenB', 'name' => 'G4');
$pretest['h5ieKaGfxfY2@etrax.at'] = array('oid' => 'hieKa', 'token' => 'h5ieKaGfxfY2', 'name' => 'G5');
$pretest['pSQ37AOqtE1a@etrax.at'] = array('oid' => 'pSQAO', 'token' => 'pSQ37AOqtE1a', 'name' => 'G6');
$pretest['zNLWCYvVkQQi@etrax.at'] = array('oid' => 'zNLWC', 'token' => 'zNLWCYvVkQQi', 'name' => 'G7');
$pretest['pwx9af28KlJ8@etrax.at'] = array('oid' => 'pwxaf', 'token' => 'pwx9af28KlJ8', 'name' => 'G8');
$pretest['wCrRK0w3H84h@etrax.at'] = array('oid' => 'wCrRK', 'token' => 'wCrRK0w3H84h', 'name' => 'G9');
$pretest['zeSlTuz4FjGJ@etrax.at'] = array('oid' => 'zeSlT', 'token' => 'zeSlTuz4FjGJ', 'name' => 'G10');
$pretest['U9DsTCtugzmV@etrax.at'] = array('oid' => 'UDsTC', 'token' => 'U9DsTCtugzmV', 'name' => 'G11');
$pretest['lSwxt37guUhA@etrax.at'] = array('oid' => 'lSwxt', 'token' => 'lSwxt37guUhA', 'name' => 'G12');
$pretest['faXoASuIPRo5@etrax.at'] = array('oid' => 'faXoA', 'token' => 'faXoASuIPRo5', 'name' => 'G13');
$pretest['RYplrrW7N3a2@etrax.at'] = array('oid' => 'RYplr', 'token' => 'RYplrrW7N3a2', 'name' => 'H1');
$pretest['R0lJCdWBnzFC@etrax.at'] = array('oid' => 'RlJCd', 'token' => 'R0lJCdWBnzFC', 'name' => 'H2');

//Prüfen ob Schlüssel für den PreTest existiert
$precheck = (!isset($pretest[$id]) &&  !isset($pretest[$id_post]))? false : true;

//Zufälligen Schlüssel erzeugen - credit: https://code.tutsplus.com/tutorials/generate-random-alphanumeric-strings-in-php--cms-32132
$permitted_chars = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

function generate_string($input, $strength = 16) {
	$input_length = strlen($input);
	$random_string = '';
	for($i = 0; $i < $strength; $i++) {
		$random_character = $input[mt_rand(0, $input_length - 1)];
		$random_string .= $random_character;
	}
 
	return $random_string;
}
if($id != false || $id_post != false){
	
	//Prüfen ob die Organisation schon angelegt wurde
	$datei = "../../secure/info.inc.php";
	require($datei);
	require "../../secure/secret.php";
	require "include/verschluesseln.php";
	$idtocheck = $id != false ? $id : $id_post;
	$errormsg = "";
	if(isset($pretest[$idtocheck]["oid"])){
		$orgcheck = false;
		$select = $db->prepare("SELECT  COUNT(*) from organisation WHERE OID LIKE '".$pretest[$idtocheck]["oid"]."'");
		if($select->execute()) {
			if ($select->fetchColumn() > 0) {
				//OID vorhanden
				$orgcheck = false;
			} else { // OID noch nicht vorhanden
				$orgcheck = true;
			}
		} else {
			$errormsg .= "SQL Error <br />";
			$errormsg .=  $select->queryString."<br />";
			$errormsg .=  $select->errorInfo()[2];
			$orgcheck = false;
		}
	} else {
		$orgcheck = false;
	}
	echo $errormsg;
	$oidtemp = "XXX";
	
	
?>
<!doctype html>
<html lang="de">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<title>eTrax | rescue - PreTest einrichten</title>
	
	<style>
		body {
			background: url("v5/background-rescue-3.jpg") no-repeat center center fixed; 
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
		<form target="_self" action="pretest.php" method="post">
			<div class="container">
				<div class="py-5 text-center">
					<img class="mb-4" src="img/etrax.png" alt="eTrax | rescue Logo">
					<h1>Organisation und User für PreTest anlegen</h1>
				</div>
		<?php
		$step = $orgcheck ? $step : 98;//OID existiert bereits
		$step = $precheck ? $step : 99;//
		switch($step){
			case "0": //Willkommenstext
			require "include/include.php";
			?>
				<p class="lead"><h2 class="p3">Herzlich Willkommen!</h2>Durch das Ausfüllen der folgenden Maske wird eine Testorganisation und ein Benutzer für sie angelegt. Damit sie gleich loslegen können, werden für ihre Testorganisation folgende Inhalte angelegt:
				<ul>
					<li>Migliedertypen Hundeführer (HF), Helfer (H) und Sanitäter (SANI)</li>
					<li>20 zufällige Mitglieder</li>
					<li>Vefügbares Kartenmaterial für ihre Region</li>
				</ul></p>
				<p class="bg-warning rounded p-2">Bitte beachten sie, dass ihr Token nur einmalig zum Anlegen einer Organisation verwendet werden kann.</p>
				<input type="hidden" name="step" value="<?php echo $step+1;?>"</input>
				
				<h3>Informationen zur Organisation</h3>
				<p class="text-info">Bitte wählen sie das Land und Bundesland aus, damit wir ihnen für ihre Region verfügbares Kartenmaterial gleich richtig zuordnen können.</p>
				<div class="form-group">
					<label for="Land_<?php echo($oidtemp);?>">Land</label>
					<select name="Land" id="Land" size="1" class="form-control laenderwahl input_oid_<?php echo($oidtemp);?>"  autofocus>
							<option value="">Land auswählen</option>';
							<?php
							foreach($org_land as $land){
								echo '<option value="'.$land.'">'.$land.'</option>';
							}
							?>
					</select>
				</div>
				<div class="form-group">
					<label for="Bundesland_<?php echo($oidtemp);?>">Bundesland</label>
					<select disabled name="Bundesland" id="Bundesland" size="1" class="form-control bundeslaenderwahl">
							<?php
								echo "<option class='' value=''>Zuerst das Land wählen</option>";
								foreach($org_land as $land){
									foreach($org_bland[$land] as $bland){
										echo "<option class='Land_".$land."' value='".$bland."'>".$bland."</option>";
									}
								}
							
								
							?>
					</select>
				</div>
				<h3>Ihre Logindaten</h3>
				<p class="text-info">Bitte füllen sie die folgenden Felder aus um einen initialen User für eTrax | rescue anzulegen. Merken Sie sich bitte den Login sowie das Passwort, um sich anschließend auf https://test.etrax.at anmelden zu können.</p>
				<div class="form-group">
					<label for="sysname">Vorname Nachname</label>
					<input type="text" name="sysname" id="sysname" class="form-control checkJSON" placeholder="Vorname Nachname" required value="">
				</div>
				<div class="form-group">
					<label for="sysuser">Username (Login)</label>
					<input type="text" name="sysuser" id="sysuser" class="form-control checkJSON" placeholder="Username" required value="">
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
				<div class="form-group">
					<label for="dbhost">E-mailadresse</label>
					<input type="email" name="sysemail" id="sysemail" class="form-control checkJSON" placeholder="Email" required value="">
				</div>
				
				
				
				<input type="hidden" name="step" value="<?php echo $step+1;?>"</input>
				<input type="hidden" name="id" value="<?php echo $id;?>"</input>
				<div class="mx-auto order-md-1 text-center mt-4">	
					<button type="submit" class="btn btn-primary abschliessen" disabled>Abschließen</button>
					
				</div>
			
			
		<?php
			break;//default
			case "1":
			$land = htmlspecialchars($_POST["Land"],ENT_QUOTES);
			$bundesland = htmlspecialchars($_POST["Bundesland"],ENT_QUOTES);
			$name = htmlspecialchars($_POST["sysname"],ENT_QUOTES);
			$username = htmlspecialchars($_POST["sysuser"],ENT_QUOTES);
			$syspwd = htmlspecialchars($_POST["syspwd"],ENT_QUOTES);
			$email = htmlspecialchars($_POST["sysemail"],ENT_QUOTES);
			
			//Oroganisationsdaten holen
			$OID = $pretest[$id_post]["oid"];
			$orgtoken = $pretest[$id_post]["token"];
			$orgname = $pretest[$id_post]["name"];
			
			//Für Random Mitglieder
			// 120 Vornamen
			$vornamen = array('Markus', 'Barbara', 'Gabriel', 'Josef', 'Bettina', 'Fabian', 'Sophia', 'Sabrina', 'Tamara', 'Lukas', 'Samuel', 'Lara', 'Hanna', 'Katharina', 'Vanessa', 'Moritz', 'Felix', 'Jana', 'Laura', 'Marie', 'Elisabeth', 'Julian', 'Thomas', 'Magdalena', 'Christoph', 'Christian', 'Robert', 'Nicole', 'Eva', 'Leonie', 'Lisa', 'Sabine', 'Philip', 'Martin', 'Clemens', 'Claudia', 'Maximilian', 'Kevin', 'Kerstin', 'Alina', 'Nina', 'Bianca', 'Philipp', 'Elias', 'Simon', 'Selina', 'Martina', 'Mario', 'Jennifer', 'Patrick', 'Manuel', 'Jonas', 'Sebastian', 'Michaela', 'Valentina', 'Hannah', 'Melanie', 'Tanja', 'Maria', 'Christina', 'Sandra', 'Marlene', 'Michael', 'Johanna', 'Jürgen', 'Alexander', 'Viktoria', 'Oliver', 'Stefan', 'Paul', 'Georg', 'Jan', 'Marco', 'Leon', 'Matthias', 'Daniela', 'Theresa', 'Sarah', 'Rene', 'Peter', 'Andrea', 'Valentin', 'Nico', 'Sophie', 'Anna', 'Noah', 'Stefanie', 'Jasmin', 'Carina', 'Wolfgang', 'Christopher', 'Michelle', 'Mathias', 'Nadine', 'Lorenz', 'Anja', 'Benjamin', 'Daniel', 'Lea', 'David', 'Victoria', 'Dominik', 'Isabella', 'Elena', 'Bernhard', 'Jakob', 'Tobias', 'Niklas', 'Katrin', 'Emma', 'Alexandra', 'Verena', 'Luca', 'Raphael', 'Andreas', 'Marcel', 'Johannes', 'Julia', 'Lena', 'Florian');
			// 50 Nachnamen
			$nachnamen = array('Gruber', 'Egger', 'Huber', 'Brunner', 'Wagner', 'Schmidt', 'Mueller', 'Weiss', 'Pichler', 'Auer', 'Moser', 'Wallner', 'Steiner', 'Aigner', 'Mayer', 'Wolf', 'Berger', 'Ebner', 'Bauer', 'Binder', 'Hofer', 'Lang', 'Eder', 'Lechner', 'Fuchs', 'Haas', 'Schmid', 'Schuster', 'Leitner', 'Strasser', 'Schwarz', 'Wieser', 'Winkler', 'Haider', 'Maier', 'Stadler', 'Weber', 'Lehner', 'Schneider', 'Koller', 'Fischer', 'Holzer', 'Mayr', 'Mair', 'Reiter', 'Graf', 'Wimmer', 'Riegler', 'Baumgartner', 'Boehm');
			// Funktionen
			$funktionen = array('HF', 'H', 'SANI');
			
			/*$datei = "../../secure/info.inc.php";
			require($datei);
			require "../../secure/secret.php";
			require "include/verschluesseln.php";
			*/
			//$db = new PDO('mysql:host=' . $dbinfo[0] . ';dbname=' . $dbinfo[3] . ';charset=utf8', $dbinfo[1], $dbinfo[2]);
			//$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$errormsg = "";
			$admin = $user = $org = false;
			//Admin Userdaten erzeugen
				$dnr = generate_string($permitted_chars, 5);
				$UID = $OID."-".$dnr;
				
				//Username sha256
				$username_sha256 = hash("sha256",$OID."-".$username,false);
			
				
				//PWD auf md5 mit Salt
				for ($s = '', $j = 0, $z = strlen($a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')-1; $j != 32; $x = rand(0,$z), $s .= $a{$x}, $j++); 
				$str = $syspwd.$s;
				$strmd5 = md5($str);
				$newpassword = $strmd5.':'.$s;
				$pwd = string_encrypt($newpassword);
				
				//Werte im JSON
				$data = array();
				$data[0]["name"] = $name;
				$data[0]["pwd"] = $pwd; //Passwort einfügen
				$data[0]["username"] = $username;
				$data[0]["dienstnummer"] = $dnr;
				$data[0]["email"] = $email;
				$data[0]["einsatzfaehig"] = "0";
				
				$encrypted = string_encrypt(json_encode($data, JSON_UNESCAPED_UNICODE));
				$insert = $db->prepare("INSERT INTO user (`UID`, `OID`, `data`, `username`, `pwd`, `FID`, `EID`) VALUES ('".$UID."','".$OID."','".$encrypted."','".$username_sha256."','".$pwd."', '3.1', '0')");
				//echo("INSERT INTO user (`UID`, `OID`, `data`, `username`, `pwd`, `FID`, `EID`) VALUES ('".$UID."','".$OID."','".$encrypted."','".$username_md5."','".$pwd."', '3.1', '0')");
				
			if($insert->execute()) {
				$admin = true;			
			} else {
				$errormsg .= "SQL Error <br />";
				$errormsg .=  $insert->queryString."<br />";
				$errormsg .=  $insert->errorInfo()[2];
				$admin = false;
			}
			
			//Random Userdaten erzeugen
			$dnr = rand(1,9).rand(1,9).rand(1,9);
			for ($i = 1; $i <= 20; $i++) {
				$dnr2 = ($i <= 9) ? $dnr."0".$i : $dnr.$i;
				$UID = $OID."-".$dnr2;
				$randusername = $orgname."-".(($i <= 9) ? $dnr."0".$i : $dnr.$i);
				//Zufallspasswort
				$pwdx = generate_string($permitted_chars, 12);
				
				//Username md5
				for ($s = '', $j = 0, $z = strlen($a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')-1; $j != 32; $x = rand(0,$z), $s .= $a{$x}, $j++); 
				$str = $randusername.$s;
				$strmd5 = md5($str);
				$randusername_md5 = $strmd5.':'.$s;
				
				//PWD auf md5 mit Salt
				for ($s = '', $j = 0, $z = strlen($a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')-1; $j != 32; $x = rand(0,$z), $s .= $a{$x}, $j++); 
				$str = $pwdx.$s;
				$strmd5 = md5($str);
				$newpassword = $strmd5.':'.$s;
				$pwd = string_encrypt($newpassword);
				
				//Werte im JSON
				$data = array();
				
				$nn = $nachnamen[rand(0,49)];
				$data[0]["name"] = $vornamen[rand(0,119)]." ".$nn;
				$data[0]["pwd"] = $pwd; //Passwort einfügen
				$data[0]["username"] = $randusername;
				$data[0]["dienstnummer"] = ($i <= 9) ? $dnr."0".$i : $dnr.$i;
				$data[0]["typ"] = $funktionen[rand(0,2)];
				$data[0]["pause"] = ($data[0]["typ"] == "HF") ? 3600 : 0;
				$data[0]["email"] = "test@etrax.at";
				$data[0]["einsatzfaehig"] = "1";
				$data[0]["notfallkontakt"] = $vornamen[rand(0,119)]." ".$nn." Tel. 01 234 56 78";
				
				$encrypted = string_encrypt(json_encode($data, JSON_UNESCAPED_UNICODE));
				$insert = $db->prepare("INSERT INTO user (`UID`, `OID`, `data`, `username`, `pwd`, `FID`, `EID`) VALUES ('".$UID."','".$OID."','".$encrypted."','".$randusername_md5."','".$pwd."', '10', '-1')");
				
				
				if($insert->execute()) {
					$user = true;			
				} else {
					$errormsg .= "SQL Error <br />";
					$errormsg .=  $insert->queryString."<br />";
					$errormsg .=  $insert->errorInfo()[2];
					$user = false;
				}
			}
			
			//Kartendefinition
			$mapselection = '[{"kartenname":"OpenTopoMap","name":"otm","printname":"etraxtopo","type":"xyz","attributions":"Kartendaten:%20%C2%A9%20%3Ca%20href=%22https://openstreetmap.org/copyright%22%3EOpenStreetMap%3C/a%3E-Mitwirkende,%20%3Ca%20href=%22http://viewfinderpanoramas.org%22%3ESRTM%3C/a%3E%20%7C%20Kartendarstellung:%20%C2%A9%20%3Ca%20href=%22https://opentopomap.org%22%3EOpenTopoMap%3C/a%3E%20(%3Ca%20href=%22https://creativecommons.org/licenses/by-sa/3.0/%22%3ECC-BY-SA%3C/a%3E","url":"//opentopomap.org/{z}/{x}/{y}.png"},{"kartenname":"OpenTopoMap","name":"otm","printname":"opentopomap","type":"xyz","attributions":"Kartendaten:%20%C2%A9%20%3Ca%20href=%22https://openstreetmap.org/copyright%22%3EOpenStreetMap%3C/a%3E-Mitwirkende,%20%3Ca%20href=%22http://viewfinderpanoramas.org%22%3ESRTM%3C/a%3E%20%7C%20Kartendarstellung:%20%C2%A9%20%3Ca%20href=%22https://opentopomap.org%22%3EOpenTopoMap%3C/a%3E%20(%3Ca%20href=%22https://creativecommons.org/licenses/by-sa/3.0/%22%3ECC-BY-SA%3C/a%3E","url":"//opentopomap.org/{z}/{x}/{y}.png"},{"kartenname":"OpenStreetMap","name":"osm","printname":"openstreetmap","type":"osm","attributions":"%3Ci%20class=%22material-icons%22%3Elayers%3C/i%3E%20Tiles%20%C2%A9%20%3Ca%20href=%22http://www.openstreetmap.org/copyright%22%3EOpenStreetMap-Mitwirkende%3C/a%3E","url":"//tile.openstreetmap.org/{z}/{x}/{y}.png"}]';
			if($land == "Österreich"){
				$mapselection = '[{"kartenname":"OpenTopoMap","name":"otm","printname":"etraxtopo","type":"xyz","attributions":"Kartendaten:%20%C2%A9%20%3Ca%20href=%22https://openstreetmap.org/copyright%22%3EOpenStreetMap%3C/a%3E-Mitwirkende,%20%3Ca%20href=%22http://viewfinderpanoramas.org%22%3ESRTM%3C/a%3E%20%7C%20Kartendarstellung:%20%C2%A9%20%3Ca%20href=%22https://opentopomap.org%22%3EOpenTopoMap%3C/a%3E%20(%3Ca%20href=%22https://creativecommons.org/licenses/by-sa/3.0/%22%3ECC-BY-SA%3C/a%3E","url":"//opentopomap.org/{z}/{x}/{y}.png"},{"kartenname":"OpenTopoMap","name":"otm","printname":"opentopomap","type":"xyz","attributions":"Kartendaten:%20%C2%A9%20%3Ca%20href=%22https://openstreetmap.org/copyright%22%3EOpenStreetMap%3C/a%3E-Mitwirkende,%20%3Ca%20href=%22http://viewfinderpanoramas.org%22%3ESRTM%3C/a%3E%20%7C%20Kartendarstellung:%20%C2%A9%20%3Ca%20href=%22https://opentopomap.org%22%3EOpenTopoMap%3C/a%3E%20(%3Ca%20href=%22https://creativecommons.org/licenses/by-sa/3.0/%22%3ECC-BY-SA%3C/a%3E","url":"//opentopomap.org/{z}/{x}/{y}.png"},{"kartenname":"Basemap standard","name":"bmc","printname":"basemap_color","type":"xyz","attributions":"%3Ci%20class=%22material-icons%22%3Elayers%3C/i%3E%20Tiles%20%C2%A9%20%3Ca%20href=%22//www.basemap.at/%22%3Ewww.basemap.at%3C/a%3E%20(STANDARD)","url":"//maps2.wien.gv.at/basemap/geolandbasemap/normal/google3857/{z}/{y}/{x}.png"},{"kartenname":"Basemap Orthofoto","name":"bmf","printname":"basemap_ofoto","type":"xyz","attributions":"%3Ci%20class=%22material-icons%22%3Elayers%3C/i%3E%20Tiles%20%C2%A9%20%3Ca%20href=%22//www.basemap.at/%22%3Ewww.basemap.at%3C/a%3E%20(Orthofoto)","url":"//maps2.wien.gv.at/basemap/bmaporthofoto30cm/normal/google3857/{z}/{y}/{x}.jpeg"},{"kartenname":"Basemap grau","name":"bmg","printname":"basemap_grau","type":"xyz","attributions":"%3Ci%20class=%22material-icons%22%3Elayers%3C/i%3E%20Tiles%20%C2%A9%20%3Ca%20href=%22//www.basemap.at/%22%3Ewww.basemap.at%3C/a%3E%20(GRAU)","url":"//maps2.wien.gv.at/basemap/bmapgrau/normal/google3857/{z}/{y}/{x}.png"},{"kartenname":"OpenStreetMap","name":"osm","printname":"openstreetmap","type":"osm","attributions":"%3Ci%20class=%22material-icons%22%3Elayers%3C/i%3E%20Tiles%20%C2%A9%20%3Ca%20href=%22http://www.openstreetmap.org/copyright%22%3EOpenStreetMap-Mitwirkende%3C/a%3E","url":"//tile.openstreetmap.org/{z}/{x}/{y}.png"}]';
			}
			
			//Funktionen der Organisation
			$orgfunktionen = '{"0":{"lang":"Hundeführer","kurz":"HF","app":true},"1":{"lang":"Helfer","kurz":"H","app":true},"2":{"lang":"Sanitäter","kurz":"SANI","app":false}}';
			//Freigabe der Organisation
			$orgfreigabe = '{"DEV":"1"}';
			
			//Organisation erzeugen
				
				//Werte im JSON
				$data = array();
				$data[0]["bezeichnung"] = "Pretest ".$orgname;
				$data[0]["kurzname"] = $orgname; 
				$data[0]["administrator"] = $name;
				$data[0]["land"] = $land;
				$data[0]["bundesland"] = $bundesland;
				$data[0]["adresse"] = $data[0]["ansprechperson"] = $data[0]["datenschutzbeauftragter"] = "";
				
				$encrypted = string_encrypt(json_encode($data, JSON_UNESCAPED_UNICODE));
				$insert = $db->prepare("INSERT INTO organisation (`OID`, `data`, `maps`, `token`, `funktionen`, `orgfreigabe`, `aktiv`, `usersync`, `appsettings`) VALUES ('".$OID."', '".$encrypted."','".$mapselection."','".$orgtoken."','".$orgfunktionen."','".$orgfreigabe."','1', '0', '{\"readposition\":\"30\",\"distance\":\"50\",\"updateinfo\":\"30\"}')");
				//echo('INSERT INTO organisation (`OID`, `data`, `token`, `aktiv`, `usersync`, `appsettings`) VALUES ("DEV", "'.$encrypted.'","'.generate_string($permitted_chars, 8).'","1", "0", "{\"readposition\":\"30\",\"distance\":\"50\",\"updateinfo\":\"30\"}")');
			
				if($insert->execute()) {
					$org = true;			
				} else {
					$errormsg .= "SQL Error <br />";
					$errormsg .=  $insert->queryString."<br />";
					$errormsg .=  $insert->errorInfo()[2];
					$org = false;
				}
			
			//Lizenz anlegen
				$insert = $db->prepare("INSERT INTO lizenzen (`SID`, `OID`, `Bezeichnung`, `Laufzeit`) VALUES ('1', '".$OID."', 'Lizenz für Pretest - ".$orgname."','".date("Y-m-d",(time()+3600*24*14))." 23:59:00')");
				//echo('INSERT INTO organisation (`OID`, `data`, `token`, `aktiv`, `usersync`, `appsettings`) VALUES ("DEV", "'.$encrypted.'","'.generate_string($permitted_chars, 8).'","1", "0", "{\"readposition\":\"30\",\"distance\":\"50\",\"updateinfo\":\"30\"}")');
			
				if($insert->execute()) {
					$orglic = true;			
				} else {
					$errormsg .= "SQL Error <br />";
					$errormsg .=  $insert->queryString."<br />";
					$errormsg .=  $insert->errorInfo()[2];
					$orglic = false;
				}
		?>
				
				<?php if($admin){ ?>
					
				<?php } else { ?>
				<h5 class="p-3 mb-2 bg-danger text-white rounded">Der initiale User [<?php echo $username; ?>] konnte nicht angelegt werden. Bei Problemen kontaktieren sie bitte support@etrax.at.</h5>
				<p class="text-info"><?php echo $errormsg; ?></p>
				<?php } // kein erfolgreiches Anlegen der User ?>
				<?php if($user){ ?>
					<?php } else { ?>
				<h5 class="p-3 mb-2 bg-danger text-white rounded">Die zufälligen Mitglieder konnten nicht angelegt werden. Bei Problemen kontaktieren sie bitte support@etrax.at.</h5>
				<p class="text-info"><?php echo $errormsg; ?></p>
				<?php } // kein erfolgreiches Anlegen der User ?>
				<?php if($org){ ?>
					
				<?php } else { ?>
				<h5 class="p-3 mb-2 bg-danger text-white rounded">Die Organisation [<?php echo $orgname; ?>] konnte nicht angelegt werden. Bei Problemen kontaktieren sie bitte support@etrax.at.</h5>
				<p class="text-info"><?php echo $errormsg; ?></p>
				<?php } // kein erfolgreiches Anlegen der User ?>
				<?php if($orglic){ ?>
					
				<?php } else { ?>
				<h5 class="p-3 mb-2 bg-danger text-white rounded">Der Testzeitraum konnte nicht angelegt werden. Bei Problemen kontaktieren sie bitte support@etrax.at.</h5>
				<p class="text-info"><?php echo $errormsg; ?></p>
				<?php } // kein erfolgreiches Anlegen der User ?>
				
				<?php if($user && $admin && $org && $orglic){ ?>
					<div class="p-3 mb-2 bg-light rounded">
						<h5 class="p-3 mb-2 text-success">Eine Testorganisation wurde für sie angelegt. Bitte nützen sie die folgenden Daten zum Login:</h5>
						<h3 class="p-3 mb-2 bg-dark text-white rounded">Übersicht über ihre Logindaten</h3>
						<h5>Name ihrer Testorganisation: <?php echo $orgname; ?></h5>
						<h5>Username: <?php echo $username; ?></h5>
						<h5>Ende des Testzeitraums: <?php echo date("d.m.Y",(time()+3600*24*14))." 23:59"; ?></h5>
						<h5>Link zum Login: <a href="index.php" target="_blank" alt="https://test.etrax.at">&rarr; eTrax | rescue</a></h5>
						<h4 class="m-4 text-info text-right">Wir wünschen ihnen viel Spaß beim Testen.</h4>
					</div>
				<?php }?>
		<?php
			break;//Ende Schritt 2
			case "98": //OID ist bereits angelegt
		?>	
				<h5 class="p-3 mb-2 bg-danger text-white rounded">Dieser Token wurde bereits verwendet. Bei Problemen kontaktieren sie bitte support@etrax.at.</h5>
				
				
		<?php
			break;//Ende Fehlerhafte ID
			case "99": //Fehlerhafte ID
		?>	
				<h5 class="p-3 mb-2 bg-danger text-white rounded">Die übermittelte ID <?php echo $id; ?> ist nicht für einen PreTest freigeschaltet. Bei Problemen kontaktieren sie bitte support@etrax.at.</h5>
				
				
		<?php
			break;//Ende Fehlerhafte ID
		} //Ende switch
		?>
			</div>
		</form>

		<!-- Optional JavaScript -->
		
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
		<script>
			//Organisation - Bundesland wählen
			$(function () {
				$(".laenderwahl").change(function () {
					var land = $("#Land").val();
					//$("option[class='Land_"+land+"']").remove();
					$("#Bundesland option").show();
					$(".Kartenwahl").show();
					$("#Bundesland option[class!='Land_" + land + "']").hide();
					$(".kartenwahl option[class!='Karte_" + land + "']").hide();
					$(".kartenwahl option[class='Karte_world']").show();
					$(".Kartenwahl").hide();
					$(".Karte_" + land).show();
					$(".Karte_world").show();
					$("#Bundesland").prop("disabled", false);
				});
			});
			
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
				} else {
					$('.abschliessen').attr("disabled",true)
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
				} else {
					$('.abschliessen').attr("disabled",true)
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
  </body>
</html>

<?php
	
} //Falls kein GET id Wert übergeben wird, wird nichts angezeigt
	
	
?>
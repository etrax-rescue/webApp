<?php
(file_exists("done.php") ? header('Location: ../index.php') : '');
$step = isset($_GET["step"]) ? htmlspecialchars($_GET["step"]) : 0;
$install = true;
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

?>
<!doctype html>
<html lang="de">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>eTrax | rescue - Ersteinrichtung</title>
	
  </head>
  <body class="bg-light">
	<form target="_self" action="index.php" method="get">
		<div class="container">
			<div class="py-5 text-center">
				<img class="mb-4" src="etrax_rescue_logo.svg" alt="eTrax | rescue Logo" height="150">
				<h1>Ersteinrichtung</h1>
			</div>
	<?php
	switch($step){
		case "0": //Willkommenstext
		$warning1 = (is_file( "../../../secure/secret.php")) ? true : false;
		$time = time();
		if($warning1) {
			//Laden des aktuellen Inhalts
			$current = file_get_contents("../../../secure/secret.php");
			// Schreibt den Inhalt in eine Backupdatei
			file_put_contents("../../../secure/secret_bu-".$time.".php", $current);
		}
		$warning2 = (is_file( "../../../secure/info.inc.php")) ? true : false;
		if($warning2) {
			//Laden des aktuellen Inhalts
			$current = file_get_contents("../../../secure/info.inc.php");
			// Schreibt den Inhalt in eine Backupdatei
			file_put_contents("../../../secure/info.inc_bu-".$time.".php", $current);
		}
		$warning = ($warning1 || $warning2) ? true : false;
		?>
			<p class="lead">In den nächsten Schritten wird eTrax | rescue eingerichtet. Bitte vergewissern sie sich, dass sie das Verzeichnis <b>/install</b> inklusive aller Dateien nach der Installation löschen.</p>
			<input type="hidden" name="step" value="<?php echo $step+1;?>"</input>
			<?php if($warning){ ?>
				<h5 class="p-3 mb-2 bg-warning text-dark rounded">Die Datei <?php echo $warning1 ? '"<b>secret.php</b>"' : ''; ?> <?php echo ($warning1 && $warning2) ? ' und ' : ''; ?> <?php echo $warning2 ? '"<b>info.inc.php</b>"' : ''; ?> ist im Verzeichnis <b>secure</b> bereits vorhanden. Diese <?php echo ($warning1 && $warning2) ? ' werden ' : 'wird'; ?> im Zuge der Einrichtung überschrieben. Die <?php echo ($warning1 && $warning2) ? ' Dateien wurden' : 'Datei wurde'; ?> mit dem Namen <?php echo $warning1 ? '"<b>secret_bu-'.$time.'.php</b>"' : ''; ?> <?php echo ($warning1 && $warning2) ? ' und ' : ''; ?> <?php echo $warning2 ? '"<b>info.inc_bu-'.$time.'.php</b>"' : ''; ?> gespeichert.</h5>
			<?php } ?>
			<div class="mx-auto order-md-1 text-center mt-4">	
				<button type="submit" class="btn btn-primary">Einrichtung starten</button>
				
			</div>
		
		
	<?php
		break;//default
		case "1":
	?>	
			<h3 class="p-3 mb-2 bg-primary text-white rounded"><b>Schritt 1: </b>Überprüfen der erforderlichen 3rd Party Libraries</h3>
			<input type="hidden" name="step" value="<?php echo $step+1;?>"</input>
			<h5 class="p-2 mt-3">Bootstrap</h5>
			<ul>
				<?php $check_t = is_file( "../vendor/js/bootstrap.bundle.min.js") ? true : false; $install = $check_t ? $install : false;?>
				<li class="<?php echo $check_t ? "text-success" : "text-danger"; ?>">vendor/js/bootstrap.bundle.min.js</li>
				<?php $check_t = is_file( "../vendor/js/bootstrap.bundle.min.js.map") ? true : false; $install = $check_t ? $install : false;?>
				<li class="<?php echo $check_t ? "text-success" : "text-danger"; ?>">vendor/js/bootstrap.bundle.min.js.map</li>
			</ul>
			<h5 class="p-2 mt-3">jQuery</h5>
			<ul>
				<?php $check_t = is_file( "../vendor/js/jquery-3.5.1.min.js") ? true : false; $install = $check_t ? $install : false;?>
				<li class="<?php echo $check_t ? "text-success" : "text-danger"; ?>">vendor/js/jquery-3.5.1.min.js</li>
			</ul>
			<h5 class="p-2 mt-3">Trix Editor</h5>
			<ul>
				<?php $check_t = is_file( "../vendor/js/trix.js") ? true : false; $install = $check_t ? $install : false;?>
				<li class="<?php echo $check_t ? "text-success" : "text-danger"; ?>">vendor/js/trix.js</li>
				<?php $check_t = is_file( "../vendor/css/trix.css") ? true : false; $install = $check_t ? $install : false;?>
				<li class="<?php echo $check_t ? "text-success" : "text-danger"; ?>">vendor/css/trix.css</li>
			</ul>
			<h5 class="p-2 mt-3">TCPDF</h5>
			<ul>
				<?php $check_t = is_file( "../vendor/tcpdf/tcpdf.php") ? true : false; $install = $check_t ? $install : false;?>
				<li class="<?php echo $check_t ? "text-success" : "text-danger"; ?>">vendor/tcpdf/tcpdf.php</li>
			</ul>
			<?php if($install){ ?>
			<div class="mx-auto order-md-1 text-center mt-4">	
				<button type="submit" class="btn btn-primary">Fortsetzen</button>
				
			</div>
			<?php } ?>
	<?php
		break;//Ende Schritt 1
		case "2":
	?>	
	<h3 class="p-3 mb-2 bg-primary text-white rounded"><b>Schritt 2: </b>Überprüfen der Schreibrechte</h3>
			<input type="hidden" name="step" value="<?php echo $step+1;?>"</input>
			<p class="text-info">Es wird versucht die erforderlichen Schreibrechte zu setzen. Wenn das nicht möglich ist, müssen diese manuell auf 0755 gesetzt werden und diese Seite <a href="index.php?step=2" target="_self">neu geladen</a> werden.</p>
			<h5 class="p-2 mt-3">Secure Verzeichnis</h5>
			<ul>
				<?php
				if (!file_exists('../../../secure')) {
					mkdir('../../../secure', 0755, true);
				}
				if (!file_exists('../../../secure/data')) {
					mkdir('../../../secure/data', 0755, true);
				}
				if(is_file( "../../../secure/info.inc.php")){
					$check_t = chmod( "../../../secure/info.inc.php", 0755) ? true : false; $install = $check_t ? $install : false;
				} else {
					$check_t = !is_file( "../../../secure/info.inc.php") ? true : false; $install = $check_t ? $install : false;					
				}					
				?>
				<li class="<?php echo $check_t ? "text-success" : "text-danger"; ?>">secure/info.inc.php</li>
				<?php
				if(is_file( "../../../secure/secret.php")){
					$check_t = chmod( "../../../secure/secret.php", 0755) ? true : false; $install = $check_t ? $install : false;
				} else {
					$check_t = !is_file( "../../../secure/secret.php") ? true : false; $install = $check_t ? $install : false;					
				}
				?>
				<li class="<?php echo $check_t ? "text-success" : "text-danger"; ?>">secure/secret.php</li>
				<?php $check_t = chmod( "../../../secure/data", 0755) ? true : false; $install = $check_t ? $install : false;?>
				<li class="<?php echo $check_t ? "text-success" : "text-danger"; ?>">secure/data</li>
				
			</ul>
			<h5 class="p-2 mt-3">Webroot</h5>
			<ul>
				<?php $check_t = chmod( "../gpximport", 0755) ? true : false; $install = $check_t ? $install : false;?>
				<li class="<?php echo $check_t ? "text-success" : "text-danger"; ?>">webroot/v5/gpximport</li>
				<?php $check_t = chmod( "../orglogos", 0755) ? true : false; $install = $check_t ? $install : false;?>
				<li class="<?php echo $check_t ? "text-success" : "text-danger"; ?>">webroot/v5/orglogos</li>
				
			</ul>
			<?php if($install){ ?>
			<div class="mx-auto order-md-1 text-center mt-4">	
				<button type="submit" class="btn btn-primary">Fortsetzen</button>
				
			</div>
			<?php } ?>
	<?php
		break;//Ende Schritt 2
		case "3":
	?>	
	<h3 class="p-3 mb-2 bg-primary text-white rounded"><b>Schritt 3: </b>Anlegen eines zufälligen Schlüssels</h3>
			<input type="hidden" name="step" value="<?php echo $step+1;?>"</input>
			<?php
				$secretkey = base64_encode(random_bytes(48));//generate_string($permitted_chars, 64);
				$jwtsecret = base64_encode(random_bytes(48));
				//Erstellen der Datei secret.php
				$datei = "../../../secure/secret.php";
$inhalt = '<?php
$secretkey = "'.$secretkey.'";
$jwtsecret = "'.$jwtsecret.'";

//Verschlüsselte Datenbankeinträge
$tobecrypted = array(
	"adminuser.login",
	"adminuser.name",
	"einsatzgruppen.name",
	"einteilung.name",
	"einteilung.dienstnummer",
	"externeuser.name",
	"externeuser.dienstnummer",
	"externeuser.email",
	"gesuchteperson.alarmiertname",
	"gesuchteperson.alarmierttelefon",
	"gesuchteperson.alarmiertdatum",
	"gesuchteperson.alarmiertzeit",
	"gesuchteperson.alarmiertvermisst",
	"gesuchteperson.gesuchtbild",
	"gesuchteperson.gesuchtname",
	"gesuchteperson.gesuchtadresse",
	"gesuchteperson.gesuchtalter",
	"gesuchteperson.gesuchttelefon",
	"gesuchteperson.gesuchtbeschreibung",
	"gesuchteperson.kontaktname",
	"gesuchteperson.kontaktadresse",
	"gesuchteperson.kontakttelefon",
	"gruppenexport.einsatzort",
	"gruppenexport.name",
	"gruppenexport.kommandant",
	"gruppenexporteinteilung.name",
	"organisation.bezeichnung",
	"organisation.kurzname",
	"organisation.adresse",
	"organisation.datenschutzbeauftragter",
	"organisation.administrator",
	"organisation.ansprechperson",
	"personen_im_einsatz.organisation",
	"personen_im_einsatz.name",
	"personen_im_einsatz.dienstnummer",
	"personen_im_einsatz.typ",
	"pois.name",
	"pois.beschreibung",
	"protokoll_ereignis.text",
	"protokoll_funk.betreff",
	"protokoll_funk.text",
	"protokoll_personen_im_einsatz.organisation",
	"protokoll_personen_im_einsatz.name",
	"settings.einsatz",
	"user.name",
	"user.dienstnummer",
	"user.typ",
	"user.username-old",
	"user.email",
	"user.bos",
	"user.telefon",
	"user.notfallkontakt",
	"user.notfallinfo",
	"user.kommentar"
);
?>';
				$install = file_put_contents($datei, $inhalt) === false ? false : true;
				chmod($datei,0644);

	//Erstellen der .htaccess Dateien
	
$inhalt = 'RewriteEngine On
RewriteBase /


RewriteRule ^\.htaccess$ - [F]

<Files ~ "^.ht">
Order allow,deny
Deny from all
</Files>

<IfModule autoindex>
  IndexIgnore *
</IfModule>

#SetEnvIfNoCase X-Requested-With XMLHttpRequest ajax
#Allow from env=ajax

RewriteCond %{HTTP_REFERER} !^(http://localhost/|https://'.$_SERVER["HTTP_HOST"].'|http://'.$_SERVER["HTTP_HOST"].').*$ [NC]
RewriteRule .(js|css|php)$ [R,L]

ErrorDocument 404 /index.php

<IfModule mod_headers.c>
	Header set Cache-Control "no-cache, no-store, must-revalidate"
	Header set Pragma "no-cache"
	Header set Expires 0
</IfModule>

# Block any script trying to base64_encode data within the URL.
RewriteCond %{QUERY_STRING} base64_encode[^(]*\([^)]*\) [OR]
# Block any script that includes a <script> tag in URL.
RewriteCond %{QUERY_STRING} (<|%3C)([^s]*s)+cript.*(>|%3E) [NC,OR]
# Block any script trying to set a PHP GLOBALS variable via URL.
RewriteCond %{QUERY_STRING} GLOBALS(=|\[|\%[0-9A-Z]{0,2}) [OR]
# Block any script trying to modify a _REQUEST variable via URL.
RewriteCond %{QUERY_STRING} _REQUEST(=|\[|\%[0-9A-Z]{0,2})
# Return 403 Forbidden header and show the content of the root home page';
				
				//htaccess dateien schreiben:
				$dateien = array("../gpx/.htaccess","../img/.htaccess","../api/.htaccess","../pdf/.htaccess");
				$htaccess = $htaccess_t = 1;
				$htaccess_error = "";
				foreach ($dateien as &$dat) {
					$htaccess_t = file_put_contents($dat, $inhalt) === false ? 0 : 1;
					$htaccess = $htaccess * $htaccess_t;
					$htaccess_error .= $htaccess_t == 1 ? '<h5 class="p-3 mb-2 bg-danger text-white rounded">Die Datei '.$dat.' konnte nicht erstellt werden.</h5>' : '';
					chmod($dat,0644);
				}
			?>
			<p class="text-info">Diese Schlüssel bilden die Grundlage zur Verschlüsselung der Daten und werden zufällig erzeugt. Bitte <b>drucken sie diese Seite aus oder speichern sie als PDF ab</b> und bewahren sie an einem sicheren Ort auf. Ohne diese ist eine Wiederherstellung der Daten unmöglich. Die Zahl Null ist nicht enthalten.</p>
			<h5 class="p-2 mt-3">Key:</h5>
			<ul>
				<li class="text-success"><?php echo $secretkey; ?></li>
			</ul>
			<h5 class="p-2 mt-3">Jwtsecret:</h5>
			<ul>
				<li class="text-success"><?php echo $jwtsecret; ?></li>
			</ul>
			
			<?php if($install){ ?>
			
			<h5 class="p-3 mb-2 bg-success text-white rounded">Die Datei <?php echo $datei; ?> wurde erfolgreich erzeugt.</h5>
			<div class="mx-auto order-md-1 text-center mt-4">	
				<button type="submit" class="btn btn-primary">Fortsetzen</button>
				
			</div>
			<?php } else { ?>
			<h5 class="p-3 mb-2 bg-danger text-white rounded">Die Datei <?php echo $datei; ?> konnte nicht erstellt werden.</h5>
			<?php }?>
	<?php
		break;//Ende Schritt 3
		case "4":
	?>	
	<h3 class="p-3 mb-2 bg-primary text-white rounded"><b>Schritt 4: </b>Verbindung zur Datenbank</h3>
			<input type="hidden" name="step" value="<?php echo $step+1;?>"</input>
			
			<p class="text-info">Bitte tragen sie hier die Verbindungsdaten zu ihrer MySQL Datenbank ein.</p>
			
			<div class="form-group">
				<label for="dbhost">Host</label>
				<input type="text" name="dbhost" id="dbhost" class="form-control" placeholder="Hostname" required autofocus>
			</div>
			<div class="form-group">
				<label for="dbuser">Username</label>
				<input type="text" name="dbuser" id="dbuser" class="form-control" placeholder="Username" required>
			</div>
			<div class="form-group">
				<label for="dbpwd">Passwort</label>
				<input type="text" name="dbpwd" id="dbpwd" class="form-control" placeholder="Passwort" required>
			</div>
			<div class="form-group">
				<label for="dbuse">Datenbankname</label>
				<input type="text" name="dbuse" id="dbuse" class="form-control" placeholder="Name der Datenbank" required>
			</div>
						
			<div class="mx-auto order-md-1 text-center mt-4">	
				<button type="submit" class="btn btn-primary">Verbindung testen</button>
				
			</div>
			
	<?php
		break;//Ende Schritt 4
		case "5":
		$dbhost = htmlspecialchars($_GET["dbhost"]);
		$dbuser = htmlspecialchars($_GET["dbuser"]);
		$dbpwd = htmlspecialchars($_GET["dbpwd"]);
		$dbuse = htmlspecialchars($_GET["dbuse"]);
		
		$dbinfo = [$dbhost,$dbuser,$dbpwd,$dbuse];
		try {
			$db = new PDO('mysql:host=' . $dbinfo[0] . ';dbname=' . $dbinfo[3] . ';charset=utf8', $dbinfo[1], $dbinfo[2]);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$install = true;
		} catch(PDOException $e) {
			$install = false;
		}
	?>
	<h3 class="p-3 mb-2 bg-primary text-white rounded"><b>Schritt 4: </b>Verbindung zur Datenbank überprüfen</h3>
			
			<?php if($install){ ?>
				<input type="hidden" name="dbhost" value="<?php echo $dbhost; ?>"</input>
				<input type="hidden" name="dbuser" value="<?php echo $dbuser; ?>"</input>
				<input type="hidden" name="dbpwd" value="<?php echo $dbpwd; ?>"</input>
				<input type="hidden" name="dbuse" value="<?php echo $dbuse; ?>"</input>
				
				<h5 class="p-3 mb-2 bg-success text-white rounded">Die Verbindung zur Datenbank konnte erfolgreich aufgebaut werden.</h5>
				
				<?php
				//Erstellen der Datei info.inc.php
					$datei = "../../../secure/info.inc.php";
$inhalt = '<?php
$dbinfo = array("'.$dbhost.'","'.$dbuser.'","'.$dbpwd.'","'.$dbuse.'");
$db = new PDO("mysql:host='.$dbhost.';dbname='.$dbuse.';charset=utf8", "'.$dbuser.'","'.$dbpwd.'");

$datapath = "../../../secure/data/";
	
?>';
				$install = file_put_contents($datei, $inhalt) === false ? false : true;
					chmod($datei,0644);
				if($install){ ?>
				
				<h5 class="p-3 mb-2 bg-success text-white rounded">Die Datei <?php echo $datei; ?> wurde erfolgreich erzeugt.</h5>
				<p class="text-info">Im nächsten Schritt werden die Tabellen in der Datenbank angelegt. Dieser Schritt kann einige Sekunden dauern.</p>
				<input type="hidden" name="step" value="<?php echo $step+1;?>"</input>
				<div class="mx-auto order-md-1 text-center mt-4">	
					<button type="submit" class="btn btn-primary">Fortsetzen</button>
					
				</div>
				<?php } else { ?>
				<h5 class="p-3 mb-2 bg-danger text-white rounded">Die Datei <?php echo $datei; ?> konnte nicht erstellt werden.</h5>
				<?php }?>
			
			<?php } else { ?>
			<h5 class="p-3 mb-2 bg-danger text-white rounded">Es konnte keine Verbindung zur Datenbank hergestellt werden. Bitte überprüfen sie die eingegebenen Daten.</h5>
			
			<input type="hidden" name="step" value="<?php echo $step;?>"</input>
			<div class="form-group">
				<label for="dbhost">Host</label>
				<input type="text" name="dbhost" id="dbhost" class="form-control" placeholder="Hostname" required autofocus value="<?php echo $dbhost;?>">
			</div>
			<div class="form-group">
				<label for="dbuser">Username</label>
				<input type="text" name="dbuser" id="dbuser" class="form-control" placeholder="Username" required value="<?php echo $dbuser;?>">
			</div>
			<div class="form-group">
				<label for="dbpwd">Passwort</label>
				<input type="text" name="dbpwd" id="dbpwd" class="form-control" placeholder="Passwort" required value="<?php echo $dbpwd;?>">
			</div>
			<div class="form-group">
				<label for="dbuse">Datenbankname</label>
				<input type="text" name="dbuse" id="dbuse" class="form-control" placeholder="Name der Datenbank" required value="<?php echo $dbuse;?>">
			</div>
						
			<div class="mx-auto order-md-1 text-center mt-4">	
				<button type="submit" class="btn btn-primary">Verbindung testen</button>
				
			</div>
			<?php } // keine erfolgreiche Verbindung zur DB ?>
			
	<?php
		break;//Ende Schritt 5
		case "6":
		
		$datei = "../../../secure/info.inc.php";
		require($datei);
		
	
		$db = new PDO('mysql:host=' . $dbinfo[0] . ';dbname=' . $dbinfo[3] . ';charset=utf8', $dbinfo[1], $dbinfo[2]);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$errormsg = "";
		//Anlegen der Datenbank
		$setDB = $db->prepare("
			SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
			START TRANSACTION;
			SET time_zone = '+00:00';
			
			
			--
			-- Tabellenstruktur für Tabelle `lizenzen`
			--

			CREATE TABLE `lizenzen` (
			  `ID` int(11) NOT NULL,
			  `SID` int(11) NOT NULL,
			  `OID` varchar(11) NOT NULL,
			  `Bezeichnung` mediumtext NOT NULL,
			  `Laufzeit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

			-- --------------------------------------------------------

			--
			-- Tabellenstruktur für Tabelle `organisation`
			--

			CREATE TABLE `organisation` (
			  `ID` int(11) NOT NULL,
			  `OID` varchar(30) NOT NULL,
			  `data` varchar(5000) NOT NULL,
			  `maps` text NOT NULL,
			  `logo` text NOT NULL,
			  `token` varchar(8) NOT NULL,
			  `usersync` int(1) NOT NULL DEFAULT '0',
			  `orgfreigabe` varchar(500) NOT NULL,
			  `status` varchar(10000) NOT NULL,
			  `funktionen` varchar(500) NOT NULL,
			  `suchef` text NOT NULL,
			  `suchem` text NOT NULL,
			  `suchep` text NOT NULL,
			  `suchew` text NOT NULL,
			  `appsettings` text NOT NULL,
			  `einheiten` text NOT NULL,
			  `aktiv` int(11) NOT NULL,
			  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

			-- --------------------------------------------------------

			--
			-- Tabellenstruktur für Tabelle `ressourcen`
			--

			CREATE TABLE `ressourcen` (
			  `ID` int(11),
			  `RID` varchar(100),
			  `OID` varchar(30),
			  `data` mediumtext,
			  `typ` varchar(10),
			  `aktiveEID` varchar(10),
			  `lastupdate` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

			-- --------------------------------------------------------

			--
			-- Tabellenstruktur für Tabelle `settings`
			--

			CREATE TABLE `settings` (
			  `EID` int(11) NOT NULL,
			  `data` mediumtext NOT NULL,
			  `pois` mediumtext NOT NULL,
			  `gruppen` mediumtext NOT NULL,
			  `gesucht` mediumtext NOT NULL,
			  `suchgebiete` mediumtext NOT NULL,
			  `einteilung` mediumtext NOT NULL,
			  `personen_im_einsatz` mediumtext NOT NULL,
			  `protokoll` mediumtext NOT NULL,
			  `messages` mediumtext NOT NULL,
			  `checkliste` mediumtext NOT NULL,
			  `einsatzbericht` longtext NOT NULL,
			  `orginfo` mediumtext NOT NULL,
			  `typ` varchar(30) NOT NULL,
			  `lastupdate` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

			-- --------------------------------------------------------

			--
			-- Tabellenstruktur für Tabelle `status`
			--

			CREATE TABLE `status` (
			  `ID` int(11) NOT NULL,
			  `EID` int(11) NOT NULL,
			  `OID` varchar(100) NOT NULL,
			  `UID` varchar(100) NOT NULL,
			  `status` int(11) NOT NULL,
			  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

			-- --------------------------------------------------------

			--
			-- Tabellenstruktur für Tabelle `suchprofile`
			--

			CREATE TABLE `suchprofile` (
			  `ID` int(11) NOT NULL,
			  `cid` varchar(10) NOT NULL,
			  `suchprofildata` text NOT NULL,
			  `distanzen` varchar(300) NOT NULL,
			  `name` varchar(300) NOT NULL,
			  `beschreibung` text NOT NULL,
			  `wahrsch` varchar(100) NOT NULL
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

			-- --------------------------------------------------------

			--
			-- Tabellenstruktur für Tabelle `tracking`
			--

			CREATE TABLE `tracking` (
			  `ID` int(11) NOT NULL,
			  `EID` varchar(100) NOT NULL,
			  `OID` varchar(50) NOT NULL,
			  `UID` varchar(100) NOT NULL,
			  `lat` varchar(30) NOT NULL,
			  `lon` varchar(30) NOT NULL,
			  `timestamp` varchar(100) NOT NULL,
			  `hdop` varchar(100) NOT NULL,
			  `altitude` varchar(100) NOT NULL,
			  `speed` varchar(100) NOT NULL,
			  `oidmitglied` varchar(50) NOT NULL,
			  `nummer` varchar(100) NOT NULL,
			  `timestamp_server` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `herkunft` varchar(30) NOT NULL,
			  `gruppe` varchar(30) NOT NULL
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

			-- --------------------------------------------------------

			--
			-- Tabellenstruktur für Tabelle `user`
			--

			CREATE TABLE `user` (
			  `ID` int(11) NOT NULL,
			  `UID` varchar(100) NOT NULL,
			  `OID` varchar(30) NOT NULL,
			  `FID` varchar(11) NOT NULL DEFAULT '10',
			  `EID` varchar(100) NOT NULL DEFAULT '-1',
			  `username` varchar(100) NOT NULL,
			  `data` mediumtext NOT NULL,
			  `aktiveEID` varchar(10) NOT NULL,
			  `pwd` varchar(300) NOT NULL,
			  `pwdresetkey` varchar(60) NOT NULL,
			  `token` char(64),
			  `token_expiration_date` timestamp,
			  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

			-- --------------------------------------------------------

			--
			-- Tabellenstruktur für Tabelle `workstations`
			--

			CREATE TABLE `workstations` (
			  `ID` int(11) NOT NULL,
			  `SID` int(11) NOT NULL,
			  `bezeichnung` mediumtext NOT NULL,
			  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

			--
			-- Indizes der exportierten Tabellen
			--

			
			--
			-- Indizes für die Tabelle `lizenzen`
			--
			ALTER TABLE `lizenzen`
			  ADD PRIMARY KEY (`ID`);

			--
			-- Indizes für die Tabelle `organisation`
			--
			ALTER TABLE `organisation`
			  ADD PRIMARY KEY (`ID`),
			  ADD UNIQUE KEY `OID` (`OID`);

			--
			-- Indizes für die Tabelle `ressourcen`
			--
			ALTER TABLE `ressourcen`
			  ADD PRIMARY KEY (`ID`),
			  ADD UNIQUE KEY `RID` (`RID`);

			--
			-- Indizes für die Tabelle `settings`
			--
			ALTER TABLE `settings`
			  ADD PRIMARY KEY (`EID`),
			  ADD UNIQUE KEY `EID` (`EID`);

			--
			-- Indizes für die Tabelle `status`
			--
			ALTER TABLE `status`
			  ADD PRIMARY KEY (`ID`);

			--
			-- Indizes für die Tabelle `tracking`
			--
			ALTER TABLE `tracking`
			  ADD PRIMARY KEY (`ID`);

			--
			-- Indizes für die Tabelle `user`
			--
			ALTER TABLE `user`
			  ADD PRIMARY KEY (`ID`),
			  ADD UNIQUE KEY `ID` (`ID`),
			  ADD UNIQUE KEY `UID` (`UID`),
			  ADD UNIQUE KEY `username` (`username`);

			--
			-- Indizes für die Tabelle `workstations`
			--
			ALTER TABLE `workstations`
			  ADD PRIMARY KEY (`ID`),
			  ADD UNIQUE KEY `ID` (`ID`),
			  ADD UNIQUE KEY `SID` (`SID`);

			--
			-- AUTO_INCREMENT für exportierte Tabellen
			--

			--
			-- AUTO_INCREMENT für Tabelle `lizenzen`
			--
			ALTER TABLE `lizenzen`
			  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

			--
			-- AUTO_INCREMENT für Tabelle `organisation`
			--
			ALTER TABLE `organisation`
			  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

			--
			-- AUTO_INCREMENT für Tabelle `ressourcen`
			--
			ALTER TABLE `ressourcen`
			  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

			--
			-- AUTO_INCREMENT für Tabelle `settings`
			--
			ALTER TABLE `settings`
			  MODIFY `EID` int(11) NOT NULL AUTO_INCREMENT;

			--
			-- AUTO_INCREMENT für Tabelle `status`
			--
			ALTER TABLE `status`
			  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

			--
			-- AUTO_INCREMENT für Tabelle `tracking`
			--
			ALTER TABLE `tracking`
			  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

			--
			-- AUTO_INCREMENT für Tabelle `user`
			--
			ALTER TABLE `user`
			  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

			--
			-- AUTO_INCREMENT für Tabelle `workstations`
			--
			ALTER TABLE `workstations`
			  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
			COMMIT;
			
			--
			-- Daten für Tabelle `workstations`
			--

			INSERT INTO `workstations` (`SID`, `bezeichnung`) VALUES
			(1, 'HOST');

			INSERT INTO `suchprofile` (`ID`, `cid`, `suchprofildata`,`distanzen`, `name`, `beschreibung`, `wahrsch`) VALUES
			(1, '1_1', '[{\"cid\": \"1_1\",\r\n	\"distanzen\": \"100,500,700,2000,5000\",\r\n	\"name\": \"Kinder, 1-16, Farmland\",\r\n	\"beschreibung\": \"Kinder deren physiologisches Alter im Bereich 1 - 16 Jahre liegt und die eine altergerechte geistige Entwicklung durchlebt haben. Sollte es bei der Entwicklung Auff\\u00e4lligkeiten gegeben haben ist ggf. die Kategorie <u><b>Entwicklungsst\\u00f6rungen<\\/b><\\/u> geeigneter.<br><br>In dieser Unterkategorie sind vermisste Kinder in l\\u00e4ndlichen Gebieten im Bereich von Bauernh\\u00f6fen, etc. zugeordnet.\",\r\n	\"wahrsch\": \"20,40,60,80,100\"}]', '100,500,700,2000,5000', 'Kinder, 1-16, Farmland', 'Kinder deren physiologisches Alter im Bereich 1 - 16 Jahre liegt und die eine altergerechte geistige Entwicklung durchlebt haben. Sollte es bei der Entwicklung Auffälligkeiten gegeben haben ist ggf. die Kategorie <u><b>Entwicklungsstörungen</b></u> geeigneter.<br><br>In dieser Unterkategorie sind vermisste Kinder in ländlichen Gebieten im Bereich von Bauernhöfen, etc. zugeordnet.', '20,40,60,80,100'),
			(2, '1_2', '[{\"cid\": \"1_2\",\r\n	\"distanzen\": \"580,1100,2200,5000,9200\",\r\n	\"name\": \"Kinder, 1-16, anderes Gel\\u00e4nde\",\r\n	\"beschreibung\": \"Kinder deren physiologisches Alter im Bereich 1 - 16 Jahre liegt und die eine altergerechte geistige Entwicklung durchlebt haben. Sollte es bei der Entwicklung Auff\\u00e4lligkeiten gegeben haben ist ggf. die Kategorie <u><b>Entwicklungsst\\u00f6rungen<\\/b><\\/u> geeigneter.<br><br>In dieser Kategorie sind vermisste Kinder in allen anderen Gebieten zugeordnet. <b> Im Zweifelsfall bei Kindern diese Kategorie w\\u00e4hlen!<\\/b>\",\r\n	\"wahrsch\": \"30,50,70,80,90\"}]', '580,1100,2200,5000,9200', 'Kinder, 1-16, anderes Gelände', 'Kinder deren physiologisches Alter im Bereich 1 - 16 Jahre liegt und die eine altergerechte geistige Entwicklung durchlebt haben. Sollte es bei der Entwicklung Auffälligkeiten gegeben haben ist ggf. die Kategorie <u><b>Entwicklungsstörungen</b></u> geeigneter.<br><br>In dieser Kategorie sind vermisste Kinder in allen anderen Gebieten zugeordnet. <b> Im Zweifelsfall bei Kindern diese Kategorie wählen!</b>', '30,50,70,80,90'),
			(3, '2_1', '[{\"cid\": \"2_1\",\r\n	\"distanzen\": \"500,1000,2000,5200,8800\",\r\n	\"name\": \"Demente, Farmland\",\r\n	\"beschreibung\": \"In diese Kategorie fallen alle vermissten Personen mit Formen von Demenz bzw. Alzheimer.<br><br>Die Detailauswahl bezieht sich auf Vermisste die im l\\u00e4ndlichen Umfeld (Bauernh\\u00f6fe) abg\\u00e4ngig sind. <b>Im Zweifelsfall ist diese Kategorie zu w\\u00e4hlen!<\\/b>\",\r\n	\"wahrsch\": \"20,40,60,80,90\"}]', '500,1000,2000,5200,8800', 'Demente, Farmland', 'In diese Kategorie fallen alle vermissten Personen mit Formen von Demenz bzw. Alzheimer.<br><br>Die Detailauswahl bezieht sich auf Vermisste die im ländlichen Umfeld (Bauernhöfe) abgängig sind. <b>Im Zweifelsfall ist diese Kategorie zu wählen!</b>', '20,40,60,80,90'),
			(4, '2_2', '[{\"cid\": \"2_2\",\r\n	\"distanzen\": \"500,1000,1700,4000,8700\",\r\n	\"name\": \"Demente, urbaner Raum\",\r\n	\"beschreibung\": \"In diese Kategorie fallen alle vermissten Personen mit Formen von Demenz bzw. Alzheimer.<br><br>Die Detailauswahl bezieht sich auf Vermisste die im urbanen Gebiet abg\\u00e4ngig sind.\",\r\n	\"wahrsch\": \"30,40,50,70,90\"}]', '500,1000,1700,4000,8700', 'Demente, urbaner Raum', 'In diese Kategorie fallen alle vermissten Personen mit Formen von Demenz bzw. Alzheimer.<br><br>Die Detailauswahl bezieht sich auf Vermisste die im urbanen Gebiet abgängig sind.', '30,40,50,70,90'),
			(5, '2_3', '[{\"cid\": \"2_3\",\r\n	\"distanzen\": \"640,1000,2000,2500,9900\",\r\n	\"name\": \"Demente, anderes Gel\\u00e4nde\",\r\n	\"beschreibung\": \"In diese Kategorie fallen alle vermissten Personen mit Formen von Demenz bzw. Alzheimer.<br><br>Die Detailauswahl bezieht sich auf Vermisste die in anderen Gebieten abg\\u00e4ngig sind. <b>ACHTUNG: Kleinster Suchradius der Kategorie Demente!<\\/b>\",\r\n	\"wahrsch\": \"20,40,60,80,100\"}]', '640,1000,2000,2500,9900', 'Demente, anderes Gelände', 'In diese Kategorie fallen alle vermissten Personen mit Formen von Demenz bzw. Alzheimer.<br><br>Die Detailauswahl bezieht sich auf Vermisste die in anderen Gebieten abgängig sind. <b>ACHTUNG: Kleinster Suchradius der Kategorie Demente!</b>', '20,40,60,80,100'),
			(6, '3_1', '[{\"cid\": \"3_1\",\r\n	\"distanzen\": \"500,1000,2500,5400,15000\",\r\n	\"name\": \"Depression bzw. Suizidverdacht\",\r\n	\"beschreibung\": \"In diese Kategorie fallen alle Vermissten welche absichtlich verschwunden sind. Gr\\u00fcnde daf\\u00fcr sind:<br> -) Androhung von Suizid<br> -) Diagnostizierte oder vermutete Depression<br> -) Stress<br><br>Bei Verdacht von Drogenmissbrauch gibt es eine eigene Kategorie <u><b>Drogenkonsum<\\/b><\\/u>.\",\r\n	\"wahrsch\": \"30,50,70,80,90\"}]', '500,1000,2500,5400,15000', 'Depression bzw. Suizidverdacht', 'In diese Kategorie fallen alle Vermissten welche absichtlich verschwunden sind. Gründe dafür sind:<br> -) Androhung von Suizid<br> -) Diagnostizierte oder vermutete Depression<br> -) Stress<br><br>Bei Verdacht von Drogenmissbrauch gibt es eine eigene Kategorie <u><b>Drogenkonsum</b></u>.', '30,50,70,80,90'),
			(7, '4_1', '[{\"cid\": \"4_1\",\r\n	\"distanzen\": \"240,760,2600,10000,35000\",\r\n	\"name\": \"Entwicklungsst\\u00f6rungen\",\r\n	\"beschreibung\": \"In diese Kategorie fallen Vermisste deren physiologisches und mentales Alter nicht \\u00fcbereinstimmen (St\\u00f6rung der Entwicklung).\",\r\n	\"wahrsch\": \"20,40,60,80,100\"}]', '240,760,2600,10000,35000', 'Entwicklungsstörungen', 'In diese Kategorie fallen Vermisste deren physiologisches und mentales Alter nicht übereinstimmen (Störung der Entwicklung).', '20,40,60,80,100'),
			(8, '5_1', '[{\"cid\": \"5_1\",\r\n	\"distanzen\": \"360,1700,3100,9900,500000\",\r\n	\"name\": \"Verschiedene - Alle anderen F\\u00e4lle\",\r\n	\"beschreibung\": \"Dieser Kategorie werden alle Vermissten zugeordnet, die keiner der anderen Kategorien entsprechen.\",\r\n	\"wahrsch\": \"20,40,60,80,100\"}]', '360,1700,3100,9900,500000', 'Verschiedene - Alle anderen Fälle', 'Dieser Kategorie werden alle Vermissten zugeordnet, die keiner der anderen Kategorien entsprechen.', '20,40,60,80,100'),
			(9, '6_1', '[{\"cid\": \"6_1\",\r\n	\"distanzen\": \"1000,2600,3500,4600,18000\",\r\n	\"name\": \"Gef\\u00fchrte Gruppen\",\r\n	\"beschreibung\": \"Bei dieser Kategorie handelt es sich um Vermisste die einer gef\\u00fchrten Gruppe (z.B. Expeditionsgruppe) angeh\\u00f6ren sowie Mitglieder ungef\\u00fchrter Gruppen (z.B. Wandergruppen).<br><br><b>F\\u00fcr Wanderer (Einzelpersonen) gibt es eine eigene Kategorie.<\\/b>\",\r\n	\"wahrsch\": \"20,40,60,80,100\"}]', '1000,2600,3500,4600,18000', 'Geführte Gruppen', 'Bei dieser Kategorie handelt es sich um Vermisste die einer geführten Gruppe (z.B. Expeditionsgruppe) angehören sowie Mitglieder ungeführter Gruppen (z.B. Wandergruppen).<br><br><b>Für Wanderer (Einzelpersonen) gibt es eine eigene Kategorie.</b>', '20,40,60,80,100'),
			(10, '7_1', '[{\"cid\": \"7_1\",\r\n	\"distanzen\": \"460,1400,2000,3600,50000\",\r\n	\"name\": \"Andere geistige Beeintr\\u00e4chtigung, Farmland\",\r\n	\"beschreibung\": \"In diese Kategorie fallen Vermisste mit ausgepr\\u00e4gter mentaler Beeintr\\u00e4chtigung die nicht in die Kategorien <b> Demenz, Entwicklungsst\\u00f6rungen, Psychisch erkrankte oder Drogenkonsum<\\/b> fallen. <br><br>In dieser Unterkategorie sind Vermisste in l\\u00e4ndlichen Gebieten im Bereich von Bauernh\\u00f6fen, etc. zugeordnet.\",\r\n	\"wahrsch\": \"20,40,60,80,100\"}]', '460,1400,2000,3600,50000', 'Andere geistige Beeinträchtigung, Farmland', 'In diese Kategorie fallen Vermisste mit ausgeprägter mentaler Beeinträchtigung die nicht in die Kategorien <b> Demenz, Entwicklungsstörungen, Psychisch erkrankte oder Drogenkonsum</b> fallen. <br><br>In dieser Unterkategorie sind Vermisste in ländlichen Gebieten im Bereich von Bauernhöfen, etc. zugeordnet.', '20,40,60,80,100'),
			(11, '7_2', '[{\"cid\": \"7_2\",\r\n	\"distanzen\": \"0,480,1000,9400\",\r\n	\"name\": \"Andere geistige Beeintr\\u00e4chtigung, urbaner Raum\",\r\n	\"beschreibung\": \"In diese Kategorie fallen Vermisste mit ausgepr\\u00e4gter mentaler Beeintr\\u00e4chtigung die nicht in die Kategorien <b> Demenz, Entwicklungsst\\u00f6rungen, Psychisch erkrankte oder Drogenkonsum<\\/b> fallen. <br><br>In dieser Unterkategorie sind Vermisste in urbanen Gebieten zugeordnet. <b>Im Zweifelsfall ist diese Unterkategorie zu w\\u00e4hlen (gr\\u00f6\\u00dfter Suchradius)<\\/b>.\",\r\n	\"wahrsch\": \"20,40,60,80,100\"}]', '0,480,1000,9400', 'Andere geistige Beeinträchtigung, urbaner Raum', 'In diese Kategorie fallen Vermisste mit ausgeprägter mentaler Beeinträchtigung die nicht in die Kategorien <b> Demenz, Entwicklungsstörungen, Psychisch erkrankte oder Drogenkonsum</b> fallen. <br><br>In dieser Unterkategorie sind Vermisste in urbanen Gebieten zugeordnet. <b>Im Zweifelsfall ist diese Unterkategorie zu wählen (größter Suchradius)</b>.', '20,40,60,80,100'),
			(12, '7_3', '[{\"cid\": \"7_3\",\r\n	\"distanzen\": \"500,2000,4100,40000\",\r\n	\"name\": \"Andere geistige Beeintr\\u00e4chtigung, anderes Gel\\u00e4nde\",\r\n	\"beschreibung\": \"In diese Kategorie fallen Vermisste mit ausgepr\\u00e4gter mentaler Beeintr\\u00e4chtigung die nicht in die Kategorien <b> Demenz, Entwicklungsst\\u00f6rungen, Psychisch erkrankte oder Drogenkonsum<\\/b> fallen. <br><br>In dieser Unterkategorie sind Vermisste zugeordnet auf die weder im l\\u00e4ndlichen Gebiet noch im urbanen Gebiet zutrifft.\",\r\n	\"wahrsch\": \"25,50,75,100\"}]', '500,2000,4100,40000', 'Andere geistige Beeinträchtigung, anderes Gelände', 'In diese Kategorie fallen Vermisste mit ausgeprägter mentaler Beeinträchtigung die nicht in die Kategorien <b> Demenz, Entwicklungsstörungen, Psychisch erkrankte oder Drogenkonsum</b> fallen. <br><br>In dieser Unterkategorie sind Vermisste zugeordnet auf die weder im ländlichen Gebiet noch im urbanen Gebiet zutrifft.', '25,50,75,100'),
			(13, '8_1', '[{\"cid\": \"8_1\",\r\n	\"distanzen\": \"500,1000,2200,3700,12000\",\r\n	\"name\": \"Psychisch erkrankte\",\r\n	\"beschreibung\": \"Dieser Kategorie sind vermisste Personen mit diagnostizierter psychischer Erkrankung zuzuordnen. Die Vermissten befinden sich derzeit in Behandlung, egal ob zu Hause oder in einer Krankenanstalt.\",\r\n	\"wahrsch\": \"30,50,60,70,90\"}]', '500,1000,2200,3700,12000', 'Psychisch erkrankte', 'Dieser Kategorie sind vermisste Personen mit diagnostizierter psychischer Erkrankung zuzuordnen. Die Vermissten befinden sich derzeit in Behandlung, egal ob zu Hause oder in einer Krankenanstalt.', '30,50,60,70,90'),
			(14, '9_1', '[{\"cid\": \"9_1\",\r\n	\"distanzen\": \"800,5000,9000,45000\",\r\n	\"name\": \"Drogenkonsum, Farmland\",\r\n	\"beschreibung\": \"In dieser Kategorie handelt es sich um Vermisste deren Verschwinden in Zusammenhang mit dem Missbrauch von Drogen oder Alkohol steht.<br><br>Die Unterkategorie bezieht sich auf Vermisste im l\\u00e4ndlichen Raum (z.B. Bauernh\\u00f6fe, etc.).<b> Im Zweifelsfall ist diese Kategorie zu w\\u00e4hlen (gr\\u00f6\\u00dferer Radius)<\\/b>.\",\r\n	\"wahrsch\": \"25,50,75,100\"}]', '800,5000,9000,45000', 'Drogenkonsum, Farmland', 'In dieser Kategorie handelt es sich um Vermisste deren Verschwinden in Zusammenhang mit dem Missbrauch von Drogen oder Alkohol steht.<br><br>Die Unterkategorie bezieht sich auf Vermisste im ländlichen Raum (z.B. Bauernhöfe, etc.).<b> Im Zweifelsfall ist diese Kategorie zu wählen (größerer Radius)</b>.', '25,50,75,100'),
			(15, '9_2', '[{\"cid\": \"9_2\",\r\n	\"distanzen\": \"200,800,2000,7500\",\r\n	\"name\": \"Drogenkonsum, anderes Gel\\u00e4nde\",\r\n	\"beschreibung\": \"In dieser Kategorie handelt es sich um Vermisste deren Verschwinden in Zusammenhang mit dem Missbrauch von Drogen oder Alkohol steht.<br><br>Die Unterkategorie bezieht sich auf Vermisste in anderen Gebieten als dem l\\u00e4ndlichen Raum.\",\r\n	\"wahrsch\": \"25,50,75,100\"}]', '200,800,2000,7500', 'Drogenkonsum, anderes Gelände', 'In dieser Kategorie handelt es sich um Vermisste deren Verschwinden in Zusammenhang mit dem Missbrauch von Drogen oder Alkohol steht.<br><br>Die Unterkategorie bezieht sich auf Vermisste in anderen Gebieten als dem ländlichen Raum.', '25,50,75,100'),
			(16, '10_1', '[{\"cid\": \"10_1\",\r\n	\"distanzen\": \"1000,1600,3200,7800\",\r\n	\"name\": \"Wanderer, W, Heidelandschaft\",\r\n	\"beschreibung\": \"Hierbei handelt es sich um Vermisste die \\u00e4lter als 16 Jahre sind und zu Erholungszwecken Wandern gegangen sind. Sollte die Suche <b>mehrere Vermisste  betreffen so ist die Kategorie <u><b>Gef\\u00fchrte Gruppen<\\/b><\\/u> zu beachten.<br><br>Die Unterkategorie bezieht sich auf <b>weibliche Wanderer in Heide(\\u00e4hnlichen)Landschaften<\\/b>.\",\r\n	\"wahrsch\": \"25,50,75,100\"}]', '1000,1600,3200,7800', 'Wanderer, W, Heidelandschaft', 'Hierbei handelt es sich um Vermisste die älter als 16 Jahre sind und zu Erholungszwecken Wandern gegangen sind. Sollte die Suche <b>mehrere Vermisste  betreffen so ist die Kategorie <u><b>Geführte Gruppen</b></u> zu beachten.<br><br>Die Unterkategorie bezieht sich auf <b>weibliche Wanderer in Heide(ähnlichen)Landschaften</b>.', '25,50,75,100'),
			(17, '10_2', '[{\"cid\": \"10_2\",\r\n	\"distanzen\": \"1000,2000,3700,5100,10000\",\r\n	\"name\": \"Wanderer, M, Heidelandschaft\",\r\n	\"beschreibung\": \"Hierbei handelt es sich um Vermisste die \\u00e4lter als 16 Jahre sind und zu Erholungszwecken Wandern gegangen sind. Sollte die Suche <b>mehrere Vermisste<7b> betreffen so ist die Kategorie <u><b>Gef\\u00fchrte Gruppen<\\/b><\\/u> zu beachten.<br><br>Die Unterkategorie bezieht sich auf <b>m\\u00e4nnliche Wanderer in Heide(\\u00e4hnlichen)Landschaften. Im Zweifelsfall ist diese Kategorie zu w\\u00e4hlen (gr\\u00f6\\u00dfter Suchradius der Kategorie).<\\/b>\",\r\n	\"wahrsch\": \"20,30,50,70,90\"}]', '1000,2000,3700,5100,10000', 'Wanderer, M, Heidelandschaft', 'Hierbei handelt es sich um Vermisste die älter als 16 Jahre sind und zu Erholungszwecken Wandern gegangen sind. Sollte die Suche <b>mehrere Vermisste<7b> betreffen so ist die Kategorie <u><b>Geführte Gruppen</b></u> zu beachten.<br><br>Die Unterkategorie bezieht sich auf <b>männliche Wanderer in Heide(ähnlichen)Landschaften. Im Zweifelsfall ist diese Kategorie zu wählen (größter Suchradius der Kategorie).</b>', '20,30,50,70,90'),
			(18, '10_3', '[{\"cid\": \"10_3\",\r\n	\"distanzen\": \"760,2200,3600,5600\",\r\n	\"name\": \"Wanderer, Felslandschaften\",\r\n	\"beschreibung\": \"Hierbei handelt es sich um Vermisste die \\u00e4lter als 16 Jahre sind und zu Erholungszwecken Wandern gegangen sind. Sollte die Suche <b>mehrere Vermisste<7b> betreffen so ist die Kategorie <u><b>Gef\\u00fchrte Gruppen<\\/b><\\/u> zu beachten.<br><br>Die Unterkategorie bezieht sich auf Wanderer die in <b>Felslandschaften<\\/b>  (z.B. Schutthalden, Gebirge, etc.) unterwegs waren.\",\r\n	\"wahrsch\": \"20,40,60,80,100\"}]', '760,2200,3600,5600', 'Wanderer, Felslandschaften', 'Hierbei handelt es sich um Vermisste die älter als 16 Jahre sind und zu Erholungszwecken Wandern gegangen sind. Sollte die Suche <b>mehrere Vermisste<7b> betreffen so ist die Kategorie <u><b>Geführte Gruppen</b></u> zu beachten.<br><br>Die Unterkategorie bezieht sich auf Wanderer die in <b>Felslandschaften</b>  (z.B. Schutthalden, Gebirge, etc.) unterwegs waren.', '20,40,60,80,100'),
			(19, '10_4', '[{\"cid\": \"10_4\",\r\n	\"distanzen\": \"500,1000,2100,3000\",\r\n	\"name\": \"Wanderer, anderes Gel\\u00e4nde\",\r\n	\"beschreibung\": \"Hierbei handelt es sich um Vermisste die \\u00e4lter als 16 Jahre sind und zu Erholungszwecken Wandern gegangen sind. Sollte die Suche <b>mehrere Vermisste<7b> betreffen so ist die Kategorie <u><b>Gef\\u00fchrte Gruppen<\\/b><\\/u> zu beachten.<br><br>Die Unterkategorie bezieht sich auf alle nicht den anderen Unterkategorien zuordenbaren Wanderer.\",\r\n	\"wahrsch\": \"25,50,75,100\"}]', '500,1000,2100,3000', 'Wanderer, anderes Gelände', 'Hierbei handelt es sich um Vermisste die älter als 16 Jahre sind und zu Erholungszwecken Wandern gegangen sind. Sollte die Suche <b>mehrere Vermisste<7b> betreffen so ist die Kategorie <u><b>Geführte Gruppen</b></u> zu beachten.<br><br>Die Unterkategorie bezieht sich auf alle nicht den anderen Unterkategorien zuordenbaren Wanderer.', '25,50,75,100');

			
			");
		if($setDB->execute()) {
			$install = true;			
		} else {
			$errormsg .= "SQL Error <br />";
			$errormsg .=  $setDB->queryString."<br />";
			$errormsg .=  $setDB->errorInfo()[2];
			$install = false;
		}
		
		
	?>	
			
		<?php if($install){ ?>
			<h5 class="p-3 mb-2 bg-success text-white rounded">Die Datenbank wurde erfolgreich angelegt.</h5>
			
			<h3 class="p-3 mt-4 mb-2 bg-primary text-white rounded"><b>Schritt 5: </b>Administrator anlegen</h3>
			<p class="text-info">Bitte füllen sie die folgenden Felder aus um einen initialen User für eTrax | rescue anzulegen.</p>
			<div class="form-group">
				<label for="sysname">Vorname Nachname</label>
				<input type="text" name="sysname" id="sysname" class="form-control" placeholder="Vorname Nachname" required autofocus value="">
			</div>
			<div class="form-group">
				<label for="sysuser">Username (Login)</label>
				<input type="text" name="sysuser" id="sysuser" class="form-control" placeholder="Username" required value="">
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
				<input type="email" name="sysemail" id="sysemail" class="form-control" placeholder="Email" required value="">
			</div>
			<div class="form-group form-check">
				<input type="checkbox" class="form-check-input" id="testdata" name="testdata" checked>
				<label class="form-check-label" for="testdata">Testorganisation mit Random Daten anlegen</label>
				<small class="text-info"><br>Für die Organisation wird ein Administrator mit dem selben Username und Passwort angelegt wie oberhalb festgelegt.</small>
			  </div>
			
			
			
			<input type="hidden" name="step" value="<?php echo $step+1;?>"</input>
			<div class="mx-auto order-md-1 text-center mt-4">	
				<button type="submit" class="btn btn-primary abschliessen" disabled>Fortsetzen</button>
				
			</div>
			
		<?php } else { ?>
		<h5 class="p-3 mb-2 bg-danger text-white rounded">Die Datenbank konnte nicht eingerichtet werden.</h5>
		<p class="text-info"><?php echo $errormsg; ?></p>
		<?php } // keine erfolgreiche Einrichtung der DB ?>
			
	<?php
		break;//Ende Schritt 6
		case "7":
		$name = htmlspecialchars($_GET["sysname"],ENT_QUOTES);
		$username = htmlspecialchars($_GET["sysuser"],ENT_QUOTES);
		$syspwd = htmlspecialchars($_GET["syspwd"],ENT_QUOTES);
		$email = htmlspecialchars($_GET["sysemail"],ENT_QUOTES);
		
		$datei = "../../../secure/info.inc.php";
		require($datei);
		require "../../../secure/secret.php";
		require "../include/verschluesseln.php";

		//$db = new PDO('mysql:host=' . $dbinfo[0] . ';dbname=' . $dbinfo[3] . ';charset=utf8', $dbinfo[1], $dbinfo[2]);
		//$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		//Userdaten erzeugen
			$UID = "DEV-".generate_string($permitted_chars, 5);
			
			//Username sha256
			$username_sha256 = hash("sha256","DEV-".$username,false);
			
			//PWD auf md5 mit Salt
			for ($s = '', $j = 0, $z = strlen($a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')-1; $j != 32; $x = rand(0,$z), $s .= $a{$x}, $j++); 
			$str = $syspwd.$s;
			$strmd5 = md5($str);
			$newpassword = $strmd5.':'.$s;
			$pwd = $newpassword;
			
			//Werte im JSON
			$data = array();
			$data[0]["name"] = $name;
			$data[0]["pwd"] = $pwd; //Passwort einfügen
			$data[0]["username"] = $username;
			$data[0]["email"] = $email;
			$data[0]["einsatzfaehig"] = "0";
			
			$encrypted = string_encrypt(json_encode($data, JSON_UNESCAPED_UNICODE));
			$insert = $db->prepare("INSERT INTO user (`UID`, `OID`, `data`, `username`, `pwd`, `FID`, `EID`) VALUES ('".$UID."','DEV','".$encrypted."','".$username_sha256."','".$pwd."', '0.0', '0')");
			
		if($insert->execute()) {
			$install = true;			
		} else {
			$errormsg .= "SQL Error <br />";
			$errormsg .=  $insert->queryString."<br />";
			$errormsg .=  $insert->errorInfo()[2];
			$install = false;
		}
		//Organisation erzeugen
			$UID = "DEV-".generate_string($permitted_chars, 5);
			
			//Werte im JSON
			$data = array();
			$data[0]["bezeichnung"] = "Development Team";
			$data[0]["kurzname"] = "Admin"; //Passwort einfügen
			$data[0]["administrator"] = $name;
			$data[0]["adresse"] = $data[0]["ansprechperson"] = $data[0]["datenschutzbeauftragter"] = "";
			
			$encrypted = string_encrypt(json_encode($data, JSON_UNESCAPED_UNICODE));
			$insert = $db->prepare('INSERT INTO organisation (`OID`, `data`, `token`, `aktiv`, `usersync`, `appsettings`) VALUES ("DEV", "'.$encrypted.'","'.generate_string($permitted_chars, 8).'","1", "0", "{\"readposition\":\"30\",\"distance\":\"50\",\"updateinfo\":\"30\"}")');
		
			if($insert->execute()) {
				$install = true;			
			} else {
				$errormsg .= "SQL Error <br />";
				$errormsg .=  $insert->queryString."<br />";
				$errormsg .=  $insert->errorInfo()[2];
				$install = false;
			}
		
	//Random Organisation erzeugen
	if(!empty($_GET["testdata"])){
		$land = "Österreich";
		$bundesland = "Niederösterreich";
		
		//Oroganisationsdaten holen
		$OID = "o815";
		$orgtoken = "Xv5dgnlB";
		$orgname = "Test 1";
		
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
			$pwd = $newpassword;
			
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
		$dnr3 = rand(1,9).rand(1,9).rand(1,9);
		for ($i = 1; $i <= 20; $i++) {
			
			$dnr = ($i <= 9) ? ($dnr3."0".$i) : ($dnr3.$i);
			$UID = $OID."-".$dnr;
			$randusername = $orgname.(($i <= 9) ? ($dnr3."0".$i) : ($dnr3.$i));
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
		$mapselection = '[{"kartenname":"OpenTopoMap","name":"otm","printname":"opentopomap","type":"xyz","attributions":"Kartendaten:%20%C2%A9%20%3Ca%20href=%22https://openstreetmap.org/copyright%22%3EOpenStreetMap%3C/a%3E-Mitwirkende,%20%3Ca%20href=%22http://viewfinderpanoramas.org%22%3ESRTM%3C/a%3E%20%7C%20Kartendarstellung:%20%C2%A9%20%3Ca%20href=%22https://opentopomap.org%22%3EOpenTopoMap%3C/a%3E%20(%3Ca%20href=%22https://creativecommons.org/licenses/by-sa/3.0/%22%3ECC-BY-SA%3C/a%3E","url":"//opentopomap.org/{z}/{x}/{y}.png"},{"kartenname":"OpenStreetMap","name":"osm","printname":"openstreetmap","type":"osm","attributions":"%3Ci%20class=%22material-icons%22%3Elayers%3C/i%3E%20Tiles%20%C2%A9%20%3Ca%20href=%22http://www.openstreetmap.org/copyright%22%3EOpenStreetMap-Mitwirkende%3C/a%3E","url":"//tile.openstreetmap.org/{z}/{x}/{y}.png"}]';
		if($land == "Österreich"){
			$mapselection = '[{"kartenname":"OpenTopoMap","name":"otm","printname":"opentopomap","type":"xyz","attributions":"Kartendaten:%20%C2%A9%20%3Ca%20href=%22https://openstreetmap.org/copyright%22%3EOpenStreetMap%3C/a%3E-Mitwirkende,%20%3Ca%20href=%22http://viewfinderpanoramas.org%22%3ESRTM%3C/a%3E%20%7C%20Kartendarstellung:%20%C2%A9%20%3Ca%20href=%22https://opentopomap.org%22%3EOpenTopoMap%3C/a%3E%20(%3Ca%20href=%22https://creativecommons.org/licenses/by-sa/3.0/%22%3ECC-BY-SA%3C/a%3E","url":"//opentopomap.org/{z}/{x}/{y}.png"},{"kartenname":"Basemap standard","name":"bmc","printname":"basemap_color","type":"xyz","attributions":"%3Ci%20class=%22material-icons%22%3Elayers%3C/i%3E%20Tiles%20%C2%A9%20%3Ca%20href=%22//www.basemap.at/%22%3Ewww.basemap.at%3C/a%3E%20(STANDARD)","url":"//maps2.wien.gv.at/basemap/geolandbasemap/normal/google3857/{z}/{y}/{x}.png"},{"kartenname":"Basemap Orthofoto","name":"bmf","printname":"basemap_ofoto","type":"xyz","attributions":"%3Ci%20class=%22material-icons%22%3Elayers%3C/i%3E%20Tiles%20%C2%A9%20%3Ca%20href=%22//www.basemap.at/%22%3Ewww.basemap.at%3C/a%3E%20(Orthofoto)","url":"//maps2.wien.gv.at/basemap/bmaporthofoto30cm/normal/google3857/{z}/{y}/{x}.jpeg"},{"kartenname":"Basemap grau","name":"bmg","printname":"basemap_grau","type":"xyz","attributions":"%3Ci%20class=%22material-icons%22%3Elayers%3C/i%3E%20Tiles%20%C2%A9%20%3Ca%20href=%22//www.basemap.at/%22%3Ewww.basemap.at%3C/a%3E%20(GRAU)","url":"//maps2.wien.gv.at/basemap/bmapgrau/normal/google3857/{z}/{y}/{x}.png"},{"kartenname":"OpenStreetMap","name":"osm","printname":"openstreetmap","type":"osm","attributions":"%3Ci%20class=%22material-icons%22%3Elayers%3C/i%3E%20Tiles%20%C2%A9%20%3Ca%20href=%22http://www.openstreetmap.org/copyright%22%3EOpenStreetMap-Mitwirkende%3C/a%3E","url":"//tile.openstreetmap.org/{z}/{x}/{y}.png"}]';
		}
		
		//Funktionen der Organisation
		$orgfunktionen = '{"0":{"lang":"Hundeführer","kurz":"HF","app":true},"1":{"lang":"Helfer","kurz":"H","app":true},"2":{"lang":"Sanitäter","kurz":"SANI","app":false}}';
		//Freigabe der Organisation
		$orgfreigabe = '{"DEV":"1"}';
		
		//Organisation erzeugen
			
			//Werte im JSON
			$data = array();
			$data[0]["bezeichnung"] = "Trainingsdaten ".$orgname;
			$data[0]["kurzname"] = $orgname; 
			$data[0]["administrator"] = $name;
			$data[0]["land"] = $land;
			$data[0]["bundesland"] = $bundesland;
			$data[0]["adresse"] = "";
			$data[0]["ansprechperson"] = $vornamen[rand(0,119)]." ".$nachnamen[rand(0,49)];
			$data[0]["datenschutzbeauftragter"] = $vornamen[rand(0,119)]." ".$nachnamen[rand(0,49)];
			
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
			$insert = $db->prepare("INSERT INTO lizenzen (`SID`, `OID`, `Bezeichnung`, `Laufzeit`) VALUES ('1', '".$OID."', 'Lizenz für Pretest - ".$orgname."','".date("Y-m-d",(time()+3600*24*365*10))." 23:59:00')");
			//echo('INSERT INTO organisation (`OID`, `data`, `token`, `aktiv`, `usersync`, `appsettings`) VALUES ("DEV", "'.$encrypted.'","'.generate_string($permitted_chars, 8).'","1", "0", "{\"readposition\":\"30\",\"distance\":\"50\",\"updateinfo\":\"30\"}")');
		
			if($insert->execute()) {
				$orglic = true;			
			} else {
				$errormsg .= "SQL Error <br />";
				$errormsg .=  $insert->queryString."<br />";
				$errormsg .=  $insert->errorInfo()[2];
				$orglic = false;
			}
	} else {
		$admin = $user = $org = $orglic = true;
	}//Ende Testdaten anlegen
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
						<h5 class="p-3 mb-2 text-success">Eine Testorganisation wurde angelegt.</h5>
						<h3 class="p-3 mb-2 bg-dark text-white rounded">Übersicht über die Logindaten</h3>
						<h5>Name der Testorganisation: <?php echo $orgname; ?></h5>
						<h5>Username: <?php echo $username; ?></h5>
					</div>
				<?php }?>
		<?php
			
	?>
			
			<?php if($install){ ?>
				<h5 class="p-3 mb-2 bg-success text-white rounded">Der initiale User [<?php echo $username; ?>] wurde erfolgreich angelegt.</h5>
				<h3 class="p-3 mt-4 mb-2 bg-primary text-white rounded"><b>Schritt 6: </b>API Keys eintragen </h3>
				<p class="text-info">Um die Adresssuche mittels Google Maps nutzen zu können, müssen sie ihren Google API Key für Google Places eintragen. Wie sie zu einem Google API Key kommen finden sie <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank" alt="Google Get an API Key">hier beschrieben.</a></p>
				
				<div class="form-group">
					<label for="apikey">Google Places API Key</label>
					<input type="text" name="apikey" id="apikey" class="form-control" placeholder="API Key im Format RanD0mnuM8er5-l1k3_Th15 einfügen" autofocus value="">
				</div>
				
				
				<input type="hidden" name="step" value="<?php echo $step+1;?>"</input>
				<div class="mx-auto order-md-1 text-center mt-4">	
					<button type="submit" class="btn btn-primary abschliessen">Fortsetzen</button>
					
				</div>
				
			<?php } else { ?>
			<h5 class="p-3 mb-2 bg-danger text-white rounded">Der initiale User [<?php echo $username; ?>] konnte nicht angelegt werden.</h5>
			<p class="text-info"><?php echo $errormsg; ?></p>
			<?php } // kein erfolgreiches Anlegen der User ?>
			
	<?php
		break;//Ende Schritt 7
		case "8":
		$apikey = $name = htmlspecialchars($_GET["apikey"],ENT_QUOTES);
		//Updaten der Datei info.inc.php
		$datei = "../../../secure/info.inc.php";
		chmod($datei,0755);
		$inhalt = file_get_contents($datei);
		$inhalt = str_replace("?>","",$inhalt);
		$inhalt .= '
		// Google API Keys
		$API_KEYS["google_api_places"] = "'.$apikey.'&libraries=places";
		$lon_default = 16.334;
		$lat_default = 48.3036;
		?>';
		$install = file_put_contents($datei, $inhalt) === false ? false : true;
		chmod($datei,0644);
	?>
	<?php if($install){ ?>
				<h5 class="p-3 mb-2 bg-success text-white rounded">Die erforderlichen API Keys wurden eingetragen.</h5>
				<h3 class="p-3 mt-4 mb-2 bg-primary text-white rounded"><b>Einrichtung abgeschlossen: </b>API Keys eintragen </h3>
				<p class="text-info">Die Einrichtung wurde erfolgreich abeschlossen. Bitte vergessen sie nicht, das Verzeichnis <b>install</b> mit allen enthaltenen Dateien zu löschen.</a></p>
				
				<h1 class="p-3 mt-4 mb-2 text-success text-right">&rarr; <a href="../index.php" target="_self">eTrax | rescue</a></h1>
				
				
				
			<?php 
			$_SESSION["etrax"] = [];
			//Erstellen des Files done.php um sicherzustellen, dass das Installationsfile nicht mehr aufgerufen werden kann
			file_put_contents("done.php", "<?php //Installation fertiggestellt ".date("d.m.Y H:i:s")." ?>");
			chmod("done.php",0644);
			} else { ?>
			<h5 class="p-3 mb-2 bg-danger text-white rounded">Die Eintragung der API Keys war nicht erfolgreich.</h5>
			<?php } // keine erfolgreiche Eintragung der API Keys ?>
	<?php
		break;
	} //Ende switch
	?>
		</div>
	</form>

    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
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
  </body>
</html>

<?php
	
	
	
?>

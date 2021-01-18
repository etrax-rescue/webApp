<?php
session_start();
if (!isset($_SESSION["etrax"]["usertype"])) {
	header("Location: index.php");
}
require "../../../secure/info.inc.php";
require "../../../secure/secret.php";
require "../include/verschluesseln.php";

$EID = $_SESSION["etrax"]["EID"];
$suchtyp = '';
//Suchtyp überprüfen

if ((isset($_REQUEST['suchtyp']) ? true : false)) {
	$suchtyp = $_REQUEST['suchtyp'];
}
$baseURL = "../";

function getImage($src)
{
	$file = '../../../secure/data/' . $_SESSION["etrax"]["EID"] . '/' . $src;
	if (file_exists($file)) {
		header('Content-Type: image/jpeg');
		header('Expires: Sat, 12 Sep 1996 05:00:00 GMT');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		$img = file_get_contents($file);
	} else {
		$img = file_get_contents('../img/no-pic.jpg');
	}
	$image = 'data:image/jpeg;base64,' . base64_encode($img);
	return $image;
}

$gesuchtfoto = getImage('/gesucht.jpg');

$mapbaseURL = "";
define("sessionstart", false);
require $baseURL . "include/sessionhandler.php"; //Der Sessionhandler schreibt die Sessionwerte neu

if ($USER["lesen"]) { //Mindestens Leserechte werden benötigt um etwas angezeigt zu bekommen.
	$alarmiertname = $alarmierttelefon = $alarmiertdatum = $alarmiertzeit = $alarmiertvermisst = $gesuchtbild = $gesuchtname = $gesuchtadresse = $gesuchtalter = $gesuchttelefon = $gesuchtbeschreibung = $kontaktname = $kontaktadresse = $kontakttelefon = $gesuchtsvnr = $gesuchterkrankungen = $gesuchtbeschreibungextern = $gesuchtbeschreibungintern = "";
	$sql_suchtyp = $db->prepare("SELECT gesucht FROM settings WHERE EID = " . $EID . "");
	$sql_suchtyp->execute($sql_suchtyp->errorInfo());
	while ($sqlsuchtyp = $sql_suchtyp->fetch(PDO::FETCH_ASSOC)) {
		($sqlsuchtyp['gesucht'] != null) ? $gesucht_json = json_decode(substr(string_decrypt($sqlsuchtyp['gesucht']), 1, -1)) : $gesucht_json = [];
	}

	$typselect = $typpdf = $aktivertyp = $suchtypname = $suchtypbeschreibung = "";

	$personenbeschreibung = $gesucht_json;
	//Loop durch das gesucht array, holt den Wert und weißt ihn der Variablen mit Namen $pkey zu
	$suchtyp = (isset($personenbeschreibung->suchtyp)) ? $personenbeschreibung->suchtyp : '';

	echo "<script>";

	$searchtyp = $db->prepare("SELECT id,cid,distanzen,name,beschreibung FROM suchprofile");
	$searchtyp->execute($searchtyp->errorInfo());
	while ($rowsuchtyp = $searchtyp->fetch(PDO::FETCH_ASSOC)) {
		if ($rowsuchtyp['cid'] == $suchtyp) {
			$suchtypname = "" . $rowsuchtyp['name'] . "";
			$suchtypbeschreibung = "" . $rowsuchtyp['beschreibung'] . "";
			$typpdf = "<a href='" . $mapbaseURL . "typ/Vermisstentyp_CID_" . $rowsuchtyp['cid'] . ".pdf' target='_blank'><i class='material-icons'>info</i></a>";
			$typselect = $typselect . '<option value="' . $rowsuchtyp['cid'] . '" selected>' . $rowsuchtyp['name'] . '</option>';
		} else {
			$typselect = $typselect . '<option value="' . $rowsuchtyp['cid'] . '">' . $rowsuchtyp['name'] . '</option>';
		}

		echo "var typetext" . $rowsuchtyp['cid'] . " = '" . $rowsuchtyp['beschreibung'] . "';";
	}
	echo "</script>";

	if ($_SESSION["etrax"]["mapadmin"]) {

		foreach ($personenbeschreibung as $pkey => $pvalue) {

			if ($pvalue) {
				//$$pkey = decryptdb($pvalue,'gesuchteperson',$pkey);
				$$pkey = $pvalue;
				//echo $$pkey."<br>";
			} else {
				$$pkey = "";
			}
		}

		$abgaenig = (explode(" ", $alarmiertvermisst));
		$alarmierung = (explode(" ", $alarmiertdatum));
?>
	<div class="modal-header">
		<h5 class="modal-title mr-auto" id="vermisst">Gesuchte Person:</h5>
		<?php if ($USER["gleich"]) { ?>
			<button class="btn btn-primary gesucht-bearbeiten mr-2" type="button">bearbeiten</button>
			<button class="btn btn-primary eingabe mr-2" id="zielpersonsubmit" tabindex="15" type="button">speichern</button>
			<button class="btn btn-secondary abbrechen mr-2" tabindex="16" type="button">abbrechen</button>
		<?php } ?>
			<button type="button" class="btn btn-secondary schliessen" data-dismiss="modal" type="button">Schliessen</button>
	</div>
	<div class="modal-body">
		<div class="gesucht">
			<div class="d-flex flex-row">
				<div class="mr-auto">
					<img src="<?php echo $gesuchtfoto; ?>" class="zielperson mb-2">
				</div>
			</div>
			<form id="personenbild" class="eingabe" action="<?php echo $mapbaseURL; ?>api/upload_image.php" method="post" enctype="multipart/form-data">
				<input type="hidden" name="eid" value="<?php echo $EID; ?>">
				<input type="hidden" name="name" value="gesucht">
				<div class="form-group">
					<b>Foto der gesuchten Person:</b>
					<div class="custom-file">
						<input type="file" id="upload_image" name="upload_image"  class="custom-file-input" accept="image/*;capture=camera">
						<label class="custom-file-label" for="inputGroupFile01">abfotografieren oder hochladen</label>
					</div>
				</div>
				<div class="form-group">
					<input type="submit" name="form_submit" class="btn btn-primary upload_foto" value="Bild speichern" />
				</div>
			</form>
			<div id="personenbeschreibung">
				<div class="form-group row">
					<label for="galter" class="col-sm-2 col-form-label"><b>Suchtyp:</b></label>
					<div class="col-sm-9">
						<select disabled class="custom-select form-control-plaintext" id="cid">
							<?php echo $typselect; ?>
						</select>
					</div>
					<div class="col-sm-1">
						<?php echo $typpdf; ?>
					</div>
				</div>
				<div class="typbeschreibung border border-red color-red col-sm-12 text-wrap bg-light text-reset mb-2 p-2 text-break"><?php echo $suchtypbeschreibung; ?></div>
				<div class="form-group row">
					<label for="gname" class="col-sm-2 col-form-label"><b>Name:</b></label>
					<div class="col-sm-6">
						<input readonly class="mb-2 form-control-plaintext checkJSON" name="gname" id="gname" type="text" value="<?php echo $gesuchtname; ?>" tabindex="1">
					</div>
					<label for="galter" class="col-sm-2 col-form-label"><b>Alter:</b></label>
					<div class="col-sm-2">
						<input readonly class="mb-2 form-control-plaintext checkJSON" name="galter" id="galter" type="text" value="<?php echo $gesuchtalter; ?>" tabindex="2">
					</div>
					<label for="gsvnr" class="col-sm-2 col-form-label"><b>SV-Nr.:</b></label>
					<div class="col-sm-3">
						<input readonly class="mb-2 form-control-plaintext checkJSON" name="gsvnr" id="gsvnr" type="text" value="<?php echo $gesuchtsvnr; ?>" tabindex="2">
					</div>
					<label for="ggebdatum" class="col-sm-3 col-form-label"><b>Geburtsdatum:</b></label>
					<div class="col-sm-4">
						<input readonly class="mb-2 form-control-plaintext checkJSON" name="ggebdatum" id="ggebdatum" type="date" value="<?php echo $gesuchtgebdatum; ?>" tabindex="2">
					</div>
				</div>
				<div class="form-group row">
					<label for="gadresse" class="col-sm-2 col-form-label"><b>Adresse:</b></label>
					<div class="col-sm-10">
						<textarea readonly class="mb-2 form-control-plaintext checkJSON" name="gadresse" id="gadresse" cols="50" rows="2" tabindex="3"><?php echo $gesuchtadresse; ?></textarea>
					</div>
				</div>
				<?php if ($_SESSION["etrax"]["mapadmin"]) { ?>
					<div class="form-group row">
						<label for="gtelefon" class="col-sm-2 col-form-label"><b>Telefon:</b></label>
						<div class="col-sm-10">
							<input readonly class="mb-2 form-control-plaintext checkJSON" name="gtelefon" id="gtelefon" type="text" value="<?php echo $gesuchttelefon; ?>" tabindex="4">
						</div>
					</div>
					<div class="form-group row">
						<label for="gerkrankungen" class="col-sm-2 col-form-label"><b>Erkrankungen:</b></label>
						<div class="col-sm-10">
							<textarea readonly class="mb-2 form-control-plaintext checkJSON" class="form-control" name="gerkrankungen" id="gerkrankungen" cols="50" rows="2" tabindex="5"><?php echo $gesuchterkrankungen; ?></textarea>
						</div>
					</div>
					<div class="form-group row">
						<label for="gbeschreibungext" class="col-sm-2 col-form-label"><b>Beschreibung Externe:</b></label>
						<div class="col-sm-10">
							<textarea readonly class="mb-2 form-control-plaintext checkJSON" class="form-control" name="gbeschreibungext" id="gbeschreibungext" cols="50" rows="2" tabindex="5" aria-describedby="allbeschHelp"><?php echo $gesuchtbeschreibungextern; ?></textarea>
							<small id="allbeschHelp" class="form-text text-muted">
								Diese Information wird auf die Handzettel gedruckt und steht "öffentlich" zur Verfügung.
							</small>
						</div>
					</div>
					<div class="form-group row">
						<label for="gbeschreibung" class="col-sm-2 col-form-label"><b>Beschreibung Suchteams:</b></label>
						<div class="col-sm-10">
							<textarea readonly class="mb-2 form-control-plaintext checkJSON" class="form-control" name="gbeschreibung" id="gbeschreibung" cols="50" rows="2" tabindex="5" aria-describedby="gesbeschHelp"><?php echo $gesuchtbeschreibung; ?></textarea>
							<small id="gesbeschHelp" class="form-text text-muted">
								Diese Information wird auf die Suchgebietskarten gedruckt und ist für alle Teilnehmer am Einsatz ersichtlich.
							</small>
						</div>
					</div>
					<div class="form-group row">
						<label for="gbeschreibungint" class="col-sm-2 col-form-label"><b>Int. Beschr.:</b></label>
						<div class="col-sm-10">
							<textarea readonly class="mb-2 form-control-plaintext checkJSON" class="form-control" name="gbeschreibungint" id="gbeschreibungint" cols="50" rows="2" tabindex="5" aria-describedby="intbeschHelp"><?php echo $gesuchtbeschreibungintern; ?></textarea>
							<small id="intbeschHelp" class="form-text text-muted">
								Diese Information steht nur der Einsatzleitung zur Verfügung und erscheint im Einsatzbericht.
							</small>
						</div>
					</div>
					<h3>Abgänig seit:</h3>
					<div class="form-group row">
						<label for="avermisst" class="col-sm-2 col-form-label"><b>Datum:</b></label>
						<div class="col-sm-4">
							<input readonly class="mb-2 form-control-plaintext checkJSON" name="avermisstdate" id="avermisstdate" type="date" value="<?php echo $abgaenig[0]; ?>" tabindex="6">
						</div>
						<label for="avermisst" class="col-sm-2 col-form-label"><b>Uhrzeit:</b></label>
						<div class="col-sm-4">
							<input readonly class="mb-2 form-control-plaintext checkJSON" name="avermissttime" id="avermissttime" type="time" value="<?php echo $abgaenig[1]; ?>" tabindex="7">
						</div>
					</div>
					<h3>Alarmierung erfolgte von/durch:</h3>
					<div class="form-group row">
						<label for="aname" class="col-sm-2 col-form-label"><b>Name:</b></label>
						<div class="col-sm-10">
							<input readonly class="mb-2 form-control-plaintext checkJSON" name="aname" id="aname" type="text" value="<?php echo $alarmiertname; ?>" tabindex="8">
						</div>
					</div>
					<div class="form-group row">
						<label for="atelefon" class="col-sm-2 col-form-label"><b>Telefon:</b></label>
						<div class="col-sm-10">
							<input readonly class="mb-2 form-control-plaintext checkJSON" name="atelefon" id="atelefon" type="text" value="<?php echo $alarmierttelefon; ?>" tabindex="9">
						</div>
					</div>
					<div class="form-group row">
						<label for="adatum" class="col-sm-2 col-form-label"><b>Datum:</b></label>
						<div class="col-sm-4">
							<input readonly class="mb-2 form-control-plaintext checkJSON" name="adatum" id="adatum" type="date" value="<?php echo $alarmierung[0]; ?>" tabindex="10">
						</div>
						<label for="azeit" class="col-sm-2 col-form-label"><b>Uhrzeit:</b></label>
						<div class="col-sm-4">
							<input readonly class="mb-2 form-control-plaintext checkJSON" name="azeit" id="azeit" type="time" value="<?php echo $alarmierung[1]; ?>" tabindex="11">
						</div>
					</div>
					<h3>Angehörige/Kontaktperson:</h3>
					<div class="form-group row">
						<label for="kname" class="col-sm-2 col-form-label"><b>Name:</b></label>
						<div class="col-sm-10">
							<input readonly class="mb-2 form-control-plaintext checkJSON" name="kname" id="kname" type="text" value="<?php echo $kontaktname; ?>" tabindex="12">
						</div>
					</div>
					<div class="form-group row">
						<label for="ktelefon" class="col-sm-2 col-form-label"><b>Telefon:</b></label>
						<div class="col-sm-10">
							<input readonly class="mb-2 form-control-plaintext checkJSON" readonly class="mb-2 form-control-plaintext" name="ktelefon" id="ktelefon" type="text" value="<?php echo $kontakttelefon; ?>" tabindex="13">
						</div>
					</div>
					<div class="form-group row">
						<label for="kadresse" class="col-sm-2 col-form-label"><b>Adresse:</b></label>
						<div class="col-sm-10">
							<textarea readonly class="mb-2 form-control-plaintext checkJSON" name="kadresse" id="kadresse" cols="50" rows="5" tabindex="14"><?php echo $kontaktadresse; ?></textarea>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
		<?php
		if (strpos($_SERVER['HTTP_REFERER'], 'mapview')) {
		$mapbaseURL = "../";
		echo '<script src="vendor/js/jquery-3.5.1.min.js"></script>';
		}
		?>
		<script src="vendor/js/jquery.form.min.js"></script>
		<script src="js/gesucht.js"></script>
		<script>
			//window.gpic = "<?php echo ($gesuchtbild != '' ? 'data:image/png;base64,' . $gesuchtbild : ''); ?>";
			//window.wpath = "<?php echo $baseURL; ?>";
			sessionStorage.setItem('gesuchtname', '<?php echo ($gesuchtname != '' ? $gesuchtname : ''); ?>');
			sessionStorage.setItem('gesuchtbeschreibung', '<?php echo ($gesuchtbeschreibung != '' ? $gesuchtbeschreibung : ''); ?><br>' + $('#cid option:selected').text());
			sessionStorage.setItem('gesuchtbild', '<?php echo ($gesuchtbild != '' ? $gesuchtbild : ''); ?>');
		</script>
<?php }
} else {
	echo "Sie verfügen nicht über die notwendigen Rechte um den Inhalt angezeigt zu bekommen.";
} ?>
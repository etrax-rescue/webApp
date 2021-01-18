# eTrax | rescue - Verwaltung und Livetracking für Personensuchen

## Development Environment Setup

> __Wichtig__: Für die Entwicklung von eTrax | rescue muss [node.js](https://nodejs.org/) installiert sein

* Repository auschecken
* Alle nötigen __node-Pakete__ sind im __package.json__-File angeführt 
* Development Files liegen im __dev__ Verzeichnis
* Beim Building-Prozess werden die erzeugten Dateien in ein Verzeichnis mit dem Namen __v5__ geschrieben
* Der Building-Prozess wird durch __gulp__ gesteuert
	* __webpack-stream__ importiert die js Files in ol.js (import jQuery from 'jquery')
	
### Installation des Development Environments

* Um die benötigten __node-Pakete__ zu installieren ein Terminal Fenster öffnen und __npm install__ eingeben
	
### Anstoßen des build Prozesses

* Zum Builden im Terminal __npm run build__ eingeben
* Mit __npm run watch__ wird die __watcher__ Funktion von __gulp__ aktiviert, die Änderungen in den Development Files verfolgt und den Build anstößt wenn Änderungen gespeichert werden

## Setup der Webapplikation

### Systemvoraussetzungen
Voraussetzungen zur Installation:
* MySQL (5.7.28 oder höher) - 1 Datenbank 
* PHP (7.3 oder höher)
* Zur Nutzung der APP bzw. BOS-Schnittstelle muss jeweils 1 Subdomain angelegt werden können

### Download
* Aktuellste Release im Repository https://github.com/etrax-rescue/webapp/releases oder
* Builden der aktuellen Version (siehe oberhalb)

### Setup

#### Vorbereitung

Der Inhalte des release Verzeichnisses muss in ein Verzeichnis am Server gelegt werden (z.B. webroot), sodass auf gleicher Ebene beim Setupvorgang noch ein Verzeichnis erstellt werden kann.

| **Level 1** | **Level 2** | **Level 3**    |
| :---------- | :---------- | :------------- |
| webroot     |             |                |
|             | bos_import  |                |
|             |             | data           |
|             | v5          |                |
|             |             | css            |
|             |             | datenschutz    |
|             |             | ...            |
|             |             | vendor         |
| _secure_    |             |                |
|             | _data_      |                |
|             |             | _info.inc.php_ |
|             |             | _secret.php_   |

Inhalt und Verzeichnis 'secure' werden vom Installationsskript automatisch angelegt. Der Name und die relative Lage des Verzeichnisses darf nicht verändert werden, da sonst die relativen Bezüge nicht mehr stimmen.

#### Domain und Subdomain einrichten

* **Startseite**: Das Hauptverzeichnis für die Applikation ist **/v5** 
* **BOS-Schnittstelle:** Nutzung ist optional; Weiterleitung auf **/bos_import**

#### Setup durchführen

1. Adresse für die Startseite aufrufen und **/install** hinzufügen (z.B. https\://etrax.at/install)
2. Das Setupskript ausführen
3. Das Verzeichnis **/install** vom Webserver löschen

Im Zuge des Setups wird das Verzeichnis **/secure** angelegt. Darin gibt es das Verzeichnis **/data**, in welchem für jeden Einsatz ein eigenes Verzeichnis mit der ID des Einsatzes angelegt wird. In der Datei **info.inc.php** sind die Zugangsdaten für die MySQL Datenbank sowie der Google Places API Key für die Adressuche gespeichert. Die Datei **secret.php** enthätl den private Key für die Verschlüsselung der Daten. **Das Gesamte Verzeichnis /secure muss außerhalb des webzugriffes liegen.**

## **Wichtigste Anpassungsmöglichkeiten**

### Startseitentexte

Auf der Startseite gibt es die **Lizenzhinweise**, **Datenschutzhinweis** und **Impressum**. Diese drei Seiten öffnen als B**ootstrap Modal**. Die angezeigten Inhalte können im Verzeichnis **/v5/inc/startseitentexte.php** bearbeitet werden.

**Kontakt E-mailaresse:**

Bei den Datenschutzhinweisen und im Impressum wird eine E-mailadresse angezeigt. Diese wird aus einem Array aufgebaut. Die Aufteilung muss genau wie im Beispiel unterhalb erfolgen (3 Teile vor dem @, 2 Teile danach).

```
$text["email"] = array('sup','po','rt','@','etr','ax.at');

```

**Startseitentexte:**

```
$text["datenschutz"] = ''; //Information zum Datenschutz im öffentlich sichtbaren Bereich der Seite (Startseite)
$text["datenschutz_user"] = ''; //Information zum Datenschutz, die User nach dem Login angezeigt bekommen
$text["impressum"] = ''; //Impressum der Website.
$text["license"] = ''; //Information zu den verwendeten Lizenzen.

```

Die Formatierung kann mittels HTML und Bootstrap erfolgen.

### Kartenmaterial

Verfügbares Kartenmaterial kann in der Datei **/v5/inc/include.php** definiert werden. Für jedes Kartenmaterial sind folgende Informationen einzutragen (am Beispiel der Open Topomap):

```
$path['opentopomap'] = array('path' => 'https://opentopomap.org/', //wie url, aber ohne z, x, y Verzeichnis
    'url' => '//opentopomap.org/{z}/{x}/{y}.png', //Link zum Tile ohne http: oder https:
    'name' => 'OpenTopoMap',
    'name_js' => 'otm', //Kürzel, unique
    'printname' => 'opentopomap', //Selber Name wie der Key
    'printable' => true,
    'dir' => 'xy', //Kann xy oder yx sein - siehe url Key ({x}/{y}.png --> xy
    'type' => 'xyz',
    'copyright' => 'Grundkarte: opentopomap.org - CC-BY-SA', //Text, der bei den Kartenausdrucken erscheint
    'attributions' => "Kartendaten: &copy; <a href='https://openstreetmap.org/copyright'>OpenStreetMap</a>-Mitwirkende, <a href='http://viewfinderpanoramas.org'>SRTM</a> | Kartendarstellung: &copy; <a href='https://opentopomap.org'>OpenTopoMap</a> (<a href='https://creativecommons.org/licenses/by-sa/3.0/'>CC-BY-SA</a>", //Text der im Kartenfenster angezeigt wird
    'zlim' => 17, //Maximales Zoom Level
    'format' => 'png', //meist PNG oder JPEG
    'land' => 'world', //Für weltweite Karte 'world' oder einen Wert aus dem Array $org_land
    'org' => '', //Wenn nur für gewisse Organisationen verfügbar dann OIDs mit ; separiert anführen
    'demotile' => 'https://opentopomap.org/15/17859/11347.png'); //Für die Kartenauswahl im Adminbereich

```

###  Ressourcen

Die für die Organisationen verfügbaren Kategorien von Ressourcen können in der Datei **/v5/inc/include.php** definiert werden.

```
$ressource["NEU"] = array('typ_kurz' => "NEU", 'typ_lang' => "Neu angelegte Ressource"); //Muss vorhanden sein
$ressource["R-KFZ"] = array('typ_kurz' => "R-KFZ", 'typ_lang' => "Kraftfahrzeug");

```

Jede angelegte Ressource steht als neue Auswahl im Select zur Verfügung. Der` 'typ_kurz' `muss unique sein.

### App Einstellungen im Administrationsbereich

Die für die Organisationen verfügbaren Werte für die individuelle Konfiguration der App können in der Datei **/v5/inc/include.php** definiert werden.

```
//Aufzeichnungshäufigkeit von Trackingpunkten
$app_position["15"] = array('time' => "15", 'text' => "15 Sekunden");
$app_position["30"] = array('time' => "30", 'text' => "30 Sekunden");
...	
// Minimalabstand zwischen Trackingpunkten
$app_distance["25"] = array('distance' => "25", 'text' => "25 Meter");
$app_distance["50"] = array('distance' => "50", 'text' => "50 Meter");
...
//Aktualisierungshäufigkeit der Informationen zur vermissten Person
$app_suchinfo["15"] = array('time' => "15", 'text' => "15 Minuten");
$app_suchinfo["30"] = array('time' => "30", 'text' => "30 Minuten");
...

```

### Anpassung von Karten bzw. Einsatzbericht

Alle Berichte und Karten, die im Format PDF erstellt werden, sind im Verzeichnis **/v5/pdf** zu finden. Die PDF Erstellung erfolgt mittels TCPDF. Die Formatierung erfolgt mittels css Styles, die jeweils im Anfangsbereich der Dateien definiert sind.

* **einsatzberichtpdf.php:** Alle Elemente des Einsatzberichtes.
* **handzettel.php**: Handzettel mit Informationen zur vermissten Person
* **mapawesome.php:** Lagekarte bestehend aus mehreren Seiten
* **suchgebiet_als_pdf_senden.php:** Suchgebietskarte welche per E-mail verschickt wird
* **suchgebietpdf.php:** Suchgebietskarte für den Ausdruck

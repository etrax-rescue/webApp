<?php
 //Einige Settings die included werden
 
 //Länderliste
 $org_land = $org_bland = array();
 $org_land = array("Österreich", "Deutschland", "Schweiz");
 $org_bland["Österreich"] = array("Burgenland", "Kärnten", "Niederösterreich", "Oberösterreich", "Salzburg", "Steiermark", "Tirol", "Vorarlberg", "Wien");
 $org_bland["Deutschland"] = array("Baden-Württemberg", "Bayern", "Berlin", "Brandenburg", "Bremen", "Hamburg", "Hessen", "Mecklenburg-Vorpommern", "Niedersachsen", "Nordrhein-Westfalen", "Rheinland-Pfalz", "Saarland", "Sachsen", "Sachsen-Anhalt", "Schleswig-Holstein", "Thüringen");
 $org_bland["Schweiz"] = array("Aargau","Appenzell Ausserrhoden","Appenzell Innerrhoden","Basel-Landschaft","Basel-Stadt","Bern","Freiburg","Genf","Glarus","Graubünden","Jura","Luzern","Neuenburg","Nidwalden","Obwalden","Schaffhausen","Schwyz","Solothurn","St. Gallen","Tessin","Thurgau","Uri","Waadt","Wallis","Zug","Zürich");
 
 //System Einstellungen
	//Email
	$mailsettings = array('from' => 'eTrax | rescue',
						'email' => "support@etrax.at",
						'installation' => "eTrax | rescue");
	
 
 //Karten definieren
/*$path['etraxtopo'] = array('path' => 'https://etrax.at/etraxtopo/',
							'url' => '//etrax.at/etraxtopo/{z}/{x}/{y}.png',
							'name' => 'eTrax Topomap',
							'name_js' => 'etm',
							'printname' => 'etraxtopo', //Selber Name wie der Key
							'printable' => true,
							'dir' => 'xy',
							'type' => 'xyz',
							'copyright' => 'Grundkarte: etrax.at - CC-BY-SA', //Text der auf Karten ausgedruckt wird
							'attributions' => "<i class='material-icons'>layers</i> etrax.at - CC-BY-SA<a href='//www.etrax.at/'>www.etrax.at</a>", //Text der im Kartenfenster angezeigt wird
							'zlim' => 16,
							'format' => 'png',
							'land' => 'Österreich', //Für weltweite Karte 'world'
							'org' => '', //Wenn nur für gewisse Organisationen verfügbar dann OIDs mit ; separiert anführen
							'demotile' => 'https://etrax.at/etraxtopo/15/17859/11347.png'); //Für die Kartenauswahl
*/						
$path['opentopomap'] = array('path' => 'https://opentopomap.org/',
							'url' => '//opentopomap.org/{z}/{x}/{y}.png',
							'name' => 'OpenTopoMap',
							'name_js' => 'otm',
							'printname' => 'opentopomap', //Selber Name wie der Key
							'printable' => true,
							'dir' => 'xy',
							'type' => 'xyz',
							'copyright' => 'Grundkarte: opentopomap.org - CC-BY-SA',
							'attributions' => "Kartendaten: &copy; <a href='https://openstreetmap.org/copyright'>OpenStreetMap</a>-Mitwirkende, <a href='http://viewfinderpanoramas.org'>SRTM</a> | Kartendarstellung: &copy; <a href='https://opentopomap.org'>OpenTopoMap</a> (<a href='https://creativecommons.org/licenses/by-sa/3.0/'>CC-BY-SA</a>", //Text der im Kartenfenster angezeigt wird
							'zlim' => 17,
							'format' => 'png',
							'land' => 'world', //Für weltweite Karte 'world'
							'org' => '', //Wenn nur für gewisse Organisationen verfügbar dann OIDs mit ; separiert anführen
							'demotile' => 'https://opentopomap.org/15/17859/11347.png'); //Für die Kartenauswahl
$path['basemap_grau'] = array('path' => 'https://maps2.wien.gv.at/basemap/bmapgrau/normal/google3857/',
							'url' => '//maps2.wien.gv.at/basemap/bmapgrau/normal/google3857/{z}/{y}/{x}.png',
							'name' => 'Basemap grau',
							'name_js' => 'bmg',
							'printname' => 'basemap_grau', //Selber Name wie der Key
							'printable' => true,
							'dir' => 'yx',
							'type' => 'xyz',
							'copyright' => 'Grundkarte: basemap.at - Basemap grau - CC-BY',
							'attributions' => "<i class='material-icons'>layers</i> Tiles © <a href='//www.basemap.at/'>www.basemap.at</a> (GRAU)", //Text der im Kartenfenster angezeigt wird
							'zlim' => 20,
							'format' => 'png',
							'land' => 'Österreich', //Für weltweite Karte 'world'
							'org' => '', //Wenn nur für gewisse Organisationen verfügbar dann OIDs mit ; separiert anführen
							'demotile' => 'https://maps2.wien.gv.at/basemap/bmapgrau/normal/google3857/15/11347/17859.png'); //Für die Kartenauswahl
$path['basemap_color'] = array('path' => 'https://maps2.wien.gv.at/basemap/geolandbasemap/normal/google3857/',
							'url' => '//maps2.wien.gv.at/basemap/geolandbasemap/normal/google3857/{z}/{y}/{x}.png',
							'name' => 'Basemap standard',
							'name_js' => 'bmc',
							'printname' => 'basemap_color', //Selber Name wie der Key
							'printable' => true,
							'dir' => 'yx',
							'type' => 'xyz',
							'copyright' => 'Grundkarte: basemap.at - Basemap standard - CC-BY',
							'attributions' => "<i class='material-icons'>layers</i> Tiles © <a href='//www.basemap.at/'>www.basemap.at</a> (STANDARD)", //Text der im Kartenfenster angezeigt wird
							'zlim' => 20,
							'format' => 'png',
							'land' => 'Österreich', //Für weltweite Karte 'world'
							'org' => '', //Wenn nur für gewisse Organisationen verfügbar dann OIDs mit ; separiert anführen
							'demotile' => 'https://maps2.wien.gv.at/basemap/geolandbasemap/normal/google3857/15/11347/17859.png'); //Für die Kartenauswahl
$path['basemap_ofoto'] = array('path' => 'https://maps2.wien.gv.at/basemap/bmaporthofoto30cm/normal/google3857/',
							'url' => '//maps2.wien.gv.at/basemap/bmaporthofoto30cm/normal/google3857/{z}/{y}/{x}.jpeg',
							'name' => 'Basemap Orthofoto',
							'name_js' => 'bmf',
							'printname' => 'basemap_ofoto', //Selber Name wie der Key
							'printable' => true,
							'dir' => 'yx',
							'type' => 'xyz',
							'copyright' => 'Grundkarte: basemap.at - Basemap Orthofoto 30cm - CC-BY',
							'attributions' => "<i class='material-icons'>layers</i> Tiles © <a href='//www.basemap.at/'>www.basemap.at</a> (Orthofoto)", //Text der im Kartenfenster angezeigt wird
							'zlim' => 20,
							'format' => 'jpeg',
							'land' => 'Österreich', //Für weltweite Karte 'world'
							'org' => '', //Wenn nur für gewisse Organisationen verfügbar dann OIDs mit ; separiert anführen
							'demotile' => 'https://maps2.wien.gv.at/basemap/bmaporthofoto30cm/normal/google3857/15/11347/17859.jpeg'); //Für die Kartenauswahl
$path['openstreetmap'] = array('path' => 'https://tile.openstreetmap.org/',
							'url' => '//tile.openstreetmap.org/{z}/{x}/{y}.png',
							'name' => 'OpenStreetMap',
							'name_js' => 'osm',
							'printname' => 'openstreetmap', //Selber Name wie der Key
							'printable' => false,
							'dir' => 'xy',
							'type' => 'osm',
							'copyright' => 'Grundkarte: OpenStreetMap-Mitwirkende - CC-BY-SA',
							'attributions' => "<i class='material-icons'>layers</i> Tiles © <a href='http://www.openstreetmap.org/copyright'>OpenStreetMap-Mitwirkende</a>", //Text der im Kartenfenster angezeigt wird
							'zlim' => 19,
							'format' => 'png',
							'land' => 'world', //Für weltweite Karte 'world'
							'org' => '', //Wenn nur für gewisse Organisationen verfügbar dann OIDs mit ; separiert anführen
							'demotile' => 'https://tile.openstreetmap.org/15/17859/11347.png'); //Für die Kartenauswahl
							
/*$path['etraxtopolokal'] = array('path' => 'http://localhost/v5/etraxtopo/',
							'url' => '//localhost/v5/etraxtopo/{z}/{x}/{y}.png',
							'name' => 'eTrax Topomap lokal',
							'name_js' => 'etm',
							'printname' => 'etraxtopolokal', //Selber Name wie der Key
							'printable' => true,
							'dir' => 'xy',
							'type' => 'xyz',
							'copyright' => 'Grundkarte: etrax.at - CC-BY-SA', //Text der auf Karten ausgedruckt wird
							'attributions' => "<i class='material-icons'>layers</i> etrax.at - CC-BY-SA<a href='//www.etrax.at/'>www.etrax.at</a>", //Text der im Kartenfenster angezeigt wird
							'zlim' => 14,
							'format' => 'png',
							'land' => 'Österreich', //Für weltweite Karte 'world'
							'org' => '', //Wenn nur für gewisse Organisationen verfügbar dann OIDs mit ; separiert anführen
							'demotile' => '//localhost/v5/etraxtopo/15/17859/11347.png'); //Für die Kartenauswahl	*/						


//Papierformat
$papier["A4"] = array('width' => 210,
						'height' => 297,
						'format' => "A4",
						'orientation' => "P");
$papier["A4q"] = array('width' => 297,
						'height' => 210,
						'format' => "A4",
						'orientation' => "L");
$papier["A3"] = array('width' => 297,
						'height' => 420,
						'format' => "A3",
						'orientation' => "P");
$papier["A3q"] = array('width' => 420,
						'height' => 297,
						'format' => "A3",
						'orientation' => "L");
						
//Verfügbare Ressourcen
$ressource["NEU"] = array('typ_kurz' => "NEU", 'typ_lang' => "Neu angelegte Ressource");
$ressource["R-KFZ"] = array('typ_kurz' => "R-KFZ", 'typ_lang' => "Kraftfahrzeug");
$ressource["R-ANH"] = array('typ_kurz' => "R-ANH", 'typ_lang' => "Anhänger");
$ressource["R-AUSR"] = array('typ_kurz' => "R-AUSR", 'typ_lang' => "Ausrüstungsgegenstand");
$ressource["R-GER"] = array('typ_kurz' => "R-GER", 'typ_lang' => "Geruchsträger");

//Dauer in Minuten die der zugeschickte Resetlink für das Passwort gültig ist.
$resetlinkgueltigkeit = 15; //Minuten


//Einheiten
$einheit_flaeche["m"] = array('unit' => "m&sup2;", 'factor' => "1");
$einheit_flaeche["ha"] = array('unit' => "ha", 'factor' => "10000");
$einheit_flaeche["km"] = array('unit' => "km&sup2;", 'factor' => "1000000");
$einheit_laenge["m"] = array('unit' => "m", 'factor' => "1");
$einheit_laenge["km"] = array('unit' => "km", 'factor' => "1000");

//Werte für die App Einstellungen im Administrationsbereich
	$app_position["15"] = array('time' => "15", 'text' => "15 Sekunden");
	$app_position["30"] = array('time' => "30", 'text' => "30 Sekunden");
	$app_position["45"] = array('time' => "45", 'text' => "45 Sekunden");
	$app_position["60"] = array('time' => "60", 'text' => "1 Minute");
	$app_position["90"] = array('time' => "90", 'text' => "1,5 Minuten");
	$app_position["120"] = array('time' => "120", 'text' => "2 Minuten");

	$app_distance["25"] = array('distance' => "25", 'text' => "25 Meter");
	$app_distance["50"] = array('distance' => "50", 'text' => "50 Meter");
	$app_distance["75"] = array('distance' => "75", 'text' => "75 Meter");
	$app_distance["100"] = array('distance' => "100", 'text' => "100 Meter");
	$app_distance["150"] = array('distance' => "150", 'text' => "150 Meter");

	$app_suchinfo["15"] = array('time' => "15", 'text' => "15 Minuten");
	$app_suchinfo["30"] = array('time' => "30", 'text' => "30 Minuten");
	$app_suchinfo["45"] = array('time' => "45", 'text' => "45 Minuten");
	$app_suchinfo["60"] = array('time' => "60", 'text' => "60 Minuten");


						
						
?>
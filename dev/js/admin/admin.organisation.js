jQuery(function() {
	$(".org_update").hide();
	//Organisation Bearbeitung aktivieren
	$("body").on("click", ".org_bearbeiten", function () {
		var oid = $(this).data("oid");
		$(".org_bearbeiten").hide();
		$(".org_speichern_" + oid).show();
		$(".function_org_del_" + oid).show();
		$(".input_oid_" + oid).prop("disabled", false);
	});
	//Organisation - Bundesland wählen
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
	//Organisation Bearbeitung speichern
	$("body").on("click", ".org_update", function () {
		var oid = $(this).data("oid");
		var otoken = $(this).data("token"); //Der bisherige Organisationstoken
		var id = $(this).data("id"); //ID in der DB
		var atoken = $("#token_oid").data("atoken").split(","); //Alle vorkommenden Token
		var aoid = $("#token_oid").data("aoid").split(",");
		//Werte holen
		var bezeichnung = $("#Bezeichnung_" + oid).val();
		var kurzname = $("#Kürzel_" + oid).val();
		var adresse = $("#Anschrift_" + oid).val();
		var notrufnummer = $("#Notrufnummer_" + oid).val();
		var land = $("#Land").val();
		var bland = $("#Bundesland").val();
		var ansprechperson = $("#Ansprechperson_" + oid).val();
		var datenschutzbeauftragter = $("#Datenschutzbeauftragter_" + oid).val();
		var administrator = $("#Administrator_" + oid).val();
		var text_ws = $("#" + oid + "_trix_weg").val();
		var text_fs = $("#" + oid + "_trix_flach").val();
		var text_ps = $("#" + oid + "_trix_punkt").val();
		var text_mt = $("#" + oid + "_trix_mantrail").val();
		var readposition = $("#readposition_" + oid + "").val();
		var distance = $("#distance_" + oid + "").val();
		var updateinfo = $("#updateinfo_" + oid + "").val();
		var aunit = $("#area_" + oid + "  option:selected").data("unit");
		var afactor = $("#area_" + oid + "  option:selected").data("factor");
		var lunit = $("#length_" + oid + "  option:selected").data("unit");
		var lfactor = $("#length_" + oid + "  option:selected").data("factor");
		console.log("fläche: "+ aunit + "Länge: " + lunit + "");
		var karte1 = $("#Karte_1").val();
		var karte2 = $("#Karte_2").val();
		var karte3 = $("#Karte_3").val();
		var karte4 = $("#Karte_4").val();
		var karte5 = $("#Karte_5").val();
		var karte6 = $("#Karte_6").val();
		var woid = $("#OID_" + oid).val(); //der Wert aus dem Inputfeld
		if (aoid.includes(woid)) {
			if (oid != woid) {
				alert("OID vorhanden");
				return;
			}
		}
		if (oid == "") { //Falls keine OID eingetragen wird, wird eine erstellt
			oid = make_oid(4);
			if (aoid.includes(oid)) {
				oid = make_oid(4);
			}
			if (aoid.includes(oid)) {
				oid = make_oid(4);
			}
			if (aoid.includes(oid)) {
				oid = make_oid(4);
			}
		}
		var token = $("#Token_" + oid).val();
		if ($("#Usersync_" + oid).is(':checked')) {
			var usersync = "1";
		} else {
			var usersync = "0";
		}
		var orgObj = new Object();
		for (k = 0; k < (aoid.length - 2); k++) {
			orgObj["DEV"] = "1";
			if ($("#orgfreigabe-" + oid + "-" + k).is(':checked')) {
				var oidtemp = $("#orgfreigabe-" + oid + "-" + k).val();
				if (oidtemp != "DEV") { //DEV bekommt immer das Recht eingetragen
					orgObj["" + oidtemp + ""] = "1";
				}
			}
		}
		var orgfreigabe = JSON.stringify(orgObj);
		console.log(orgfreigabe);
		// Funktionen
		var funktionenObj = new Object();
		var funktionenObjCheck = new Object();
		var funktionenObj2 = new Object();
		var funktionenObj3 = new Object();
		$('input[name="lang_' + oid + '[]"]').each(function (key, value) {
			var lang_temp = $(this).val();
			if (lang_temp != "") {
				funktionenObj[key] = escapeHTML(lang_temp);
				funktionenObjCheck[key] = true;
			} else {
				funktionenObjCheck[key] = false;
			}
		});
		$('input[name="kurz_' + oid + '[]"]').each(function (key, value) {
			if (funktionenObjCheck[key] == true) {
				var kurz_temp = escapeHTML($(this).val());
				if (kurz_temp == "") {
					kurz_temp = funktionenObj[key].substr(0, 4).toUpperCase();
				} else {
					kurz_temp = kurz_temp.substr(0, 4).toUpperCase();
				}
				funktionenObj2[key] = kurz_temp;
			}
		});
		$('input[name="app_' + oid + '[]"]').each(function (key, value) {
			if (funktionenObjCheck[key] == true) {
				if ($(this).is(':checked')) {
					funktionenObj3[key] = {
						"lang": funktionenObj[key],
						"kurz": funktionenObj2[key],
						"app": true
					};
				} else {
					funktionenObj3[key] = {
						"lang": funktionenObj[key],
						"kurz": funktionenObj2[key],
						"app": false
					};
				}
			}
		});
		var funktionen = JSON.stringify(funktionenObj3);
		//Statusmeldungen
		var bosObj = new Object();
		var allObj = new Object();
		var appObj = new Object();
		var statusObj = new Object();
		var statustext = ['Anmeldung', 'In Anreise', 'Am Berufungsort', 'Ins Suchgebiet', 'Beginn Suche', 'Ende Suche', 'Warten auf Transport', 'Rückweg zur Einsatzleitung', 'Pause', 'Am Heimweg', 'Abmeldung', 'Fund lebend, RD benötigt', 'Fund lebend, kein RD benötigt', 'Fund tot', 'Sprechwunsch'];
		for (l = 1; l <= 15; l++) {
			stexttemp = escapeHTML($("#" + oid + "_statustext_" + l + "").val());
			if ($("#" + oid + "_usestatus_" + l + "").is(':checked')) {
				susetemp = true;
				// Genauigkeit setzen:
				strackingtemp = $("#" + oid + "_usetracking_" + l + "").val();
			} else {
				susetemp = false;
				strackingtemp = "0"; //Wird ein Status nicht verwendet, ist wird die Genauigkeit auch auf 0 gesetzt.
			}
			/*if ($("#" + oid + "_usetracking_" + l + "").is(':checked')) {
				if (susetemp == true) {
					strackingtemp = true;
				} else {
					strackingtemp = false; //Wenn Status für APP nicht verfügbar ist Tracking auch nicht möglich
				}
			} else {
				strackingtemp = false;
			}
			*/
			if ($("#" + oid + "_usedoku_" + l + "").is(':checked')) {
				if (susetemp == true) {
					sdokutemp = true;
				} else {
					sdokutemp = false; //Wenn Status für APP nicht verfügbar ist die Dokumentation auch nicht möglich
				}
			} else {
				sdokutemp = false;
			}
			if (stexttemp != "") {
				bosObj["" + stexttemp + ""] = l;
			}
			allObj["" + l + ""] = {
				"text": stexttemp,
				"use": susetemp,
				"tracking": strackingtemp,
				"doku": sdokutemp
			};
			appObj["" + l + ""] = {
				"text": statustext[(l - 1)],
				"use": susetemp,
				"tracking": strackingtemp,
				"doku": sdokutemp
			};
		}
		statusObj["bos"] = bosObj;
		statusObj["all"] = allObj;
		statusObj["app"] = appObj;
		var status = JSON.stringify(statusObj);
		if (token == "") {
			token = make_token(8);
		}
		if (otoken != token) {
			if (atoken.includes(token)) {
				token = make_token(8);
			}
			if (atoken.includes(token)) {
				token = make_token(8);
			}
			if (atoken.includes(token)) {
				token = make_token(8);
			}
		}
		//Einheiten
		var unitsObj = new Object();
		unitsObj = {
			"aunit": aunit,
			"afactor": afactor,
			"lunit": lunit,
			"lfactor": lfactor
		};
		var units_select = JSON.stringify(unitsObj);
		//APP Setting
		var appsettingsObj = new Object();
		appsettingsObj = {
			"readposition": readposition,
			"distance": distance,
			"updateinfo": updateinfo
		};
		var appsettings = JSON.stringify(appsettingsObj);
		//Kartenmaterial
		var k_n = 0;
		var kartenObj = new Object();
		var kartenauswahl = "";
		for (l = 1; l <= 6; l++) {
			var karte = $("#Karte_" + l).val();
			if (karte != "") {
				kartenObj = {
					"kartenname": $("#Karte_" + l + "  option:selected").data("kartenname"),
					"name": $("#Karte_" + l + "  option:selected").data("name"),
					"printname": $("#Karte_" + l + "  option:selected").data("printname"),
					"type": $("#Karte_" + l + "  option:selected").data("type"),
					"attributions": encodeURI(swapQuote($("#Karte_" + l + "  option:selected").data("attributions"))),
					"url": $("#Karte_" + l + "  option:selected").data("url")
				};
				k_n = k_n + 1;
				kartenauswahl = kartenauswahl.concat(",", JSON.stringify(kartenObj));
			}
		}
		var kartenauswahl = "[" + kartenauswahl.substr(1) + "]";
		// json Node to be changed (nodename: value)
		var json_nodes = {
			bezeichnung: "" + bezeichnung + "",
			kurzname: "" + kurzname + "",
			adresse: "" + adresse + "",
			notrufnummer: "" + notrufnummer + "",
			land: "" + land + "",
			bundesland: "" + bland + "",
			ansprechperson: "" + ansprechperson + "",
			datenschutzbeauftragter: "" + datenschutzbeauftragter + "",
			administrator: "" + administrator + "",
			insert_OID: "" + woid + "",
			insert_maps: "" + kartenauswahl + "",
			insert_token: "" + token + "",
			insert_usersync: "" + usersync + "",
			insert_orgfreigabe: "" + orgfreigabe + "",
			insert_status: "" + status + "",
			insert_funktionen: "" + funktionen + "",
			insert_suchef: "" + text_fs + "",
			insert_suchem: "" + text_mt + "",
			insert_suchep: "" + text_ps + "",
			insert_suchew: "" + text_ws + "",
			insert_appsettings: "" + appsettings + "",
			insert_einheiten: "" + units_select + "",
			insert_aktiv: "1"
		};console.log(json_nodes);
		if (confirm("Sollen die Änderungen gespeichert werden?")) {
			$.ajax({
				url: "api/read_write_db.php",
				type: 'post',
				data: {
					database: {
						type: "json_admin",
						action: "update",
						table: "organisation",
						column: "data"
					},
					select: {
						ID: "" + id + ""
					},
					values: '',
					json_nodes: json_nodes
				},
				success: function (msg) {
					$(".modal.feedback").modal('show').find("h5").html("Die Änderungen wurden gespeichert.<br>");
					setTimeout(function(){
							$(".modal.feedback").modal('hide');
							location.reload();
					}, 2000);
				}
			});
		} else {
			return false;
		}
	});
	//Neue Organisation anlegen
	$("body").on("click", ".org_add", function () {
		var atoken = $("#token_oid").data("atoken").split(",");
		var aoid = $("#token_oid").data("aoid").split(","); //Liste aller angelegten OIDs
		//alert($("#token_oid").data("atoken"));
		//OID erzeugen
		var oid = make_oid(4);
		if (aoid.includes(oid)) {
			oid = make_oid(4);
		}
		if (aoid.includes(oid)) {
			oid = make_oid(4);
		}
		if (aoid.includes(oid)) {
			oid = make_oid(4);
		}
		//Token erzeugen
		token = make_token(8);
		if (atoken.includes(token)) {
			token = make_token(8);
		}
		if (atoken.includes(token)) {
			token = make_token(8);
		}
		if (atoken.includes(token)) {
			token = make_token(8);
		}
		//Appsettings Default
		var appsettingsObj = new Object();
		appsettingsObj = {
			"readposition": 30,
			"distance": 50,
			"updateinfo": 30
		};
		var appsettings = JSON.stringify(appsettingsObj);
		//für read_write_db
		var database = {
			type: "json",
			// defining datatype json/single value (json/val)
			action: "insert",
			//action read or write
			table: "organisation",
			// DB Table
			column: "data" // DB Table column for jsons to be changed
		};
		// Entries to be display (key: value) 
		// bei json auslesen nur 1 Eintrag!
		var select = {}
		// json Node to be changed (nodename: value)
		var json_nodes = {
			bezeichnung: "Neue Organisation",
			kurzname: "OrgNeu",
			adresse: "",
			ansprechperson: "",
			datenschutzbeauftragter: "",
			administrator: ""
		};
		// direct changes in db ( fieldname: value)
		var db_vars = {
			OID: "" + oid + "",
			token: "" + token + "",
			aktiv: "1",
			usersync: "0",
			appsettings: "" + appsettings + ""
		};
		if (confirm("Sollen eine neue Organisation angelegt werden?")) {
			$.ajax({
				url: "api/read_write_db.php",
				type: 'post',
				data: {
					database: database,
					select: select,
					values: db_vars,
					json_nodes: json_nodes
				},
				success: function (msg) {
					$(".modal.feedback").modal('show').find("h5").html("Die Organisation wurde angelegt.<br>" + msg);
					setTimeout(function () {
						$(".modal.feedback").modal('hide');
						location.reload();
					}, 2000);
				}
			});
		} else {
			return false;
		}
	});
	//Tooltip
	$('[data-toggle="tooltip"]').tooltip();
	//Logo Upload anzeigen
	$("body").on("click", ".btn-logoupload", function () {
		var oid = $(this).data("oid");
		$(".modal.logoupload").modal("show");
		$("#uOrg").val(oid);
		$.ajax({
			url: 'orglogos/' + oid + '.png',
			type: 'HEAD',
			error: function () {
				//file not exists
			},
			success: function () {
				//file exists
				$("#currentorglogo").removeAttr("src").attr("src", "orglogos/" + oid + ".png");
			}
		});
	});
	//Logo Upload durchführen
	$(document).ready(function (e) {
		$("#logouploadform").on('submit', (function (e) {
			e.preventDefault();
			var oid = $("#uOrg").val();
			let t = new Date().getTime();
			$("#currentorglogo").attr("src", "orglogos/" + oid + ".png?t=" + t);
			//$(".modal.logoupload").modal("show");
			$.ajax({
				url: "admin.index.php?do=logo",
				type: "POST",
				data: new FormData(this),
				contentType: false,
				cache: false,
				processData: false,
				beforeSend: function () {
					$("#preview").fadeOut();
					$("#err").fadeOut();
				},
				success: function (data) {
					if (data == 'invalid') {
						// Falsches Dateiformat
						$("#err").html("Unzulässiges Dateiformat!<br>Es dürfen nur .jpg, .jpeg oder .png Dateien hochgeladen werden.").fadeIn();
					} else {
						// Hochgeladende Datei anzeigen
						$("#preview").html(data).fadeIn();
						$("#logouploadform")[0].reset();
						let t = new Date().getTime();
						$("#currentorglogo").attr("src", "orglogos/" + oid + ".png?t="+t);
					}
				},
				error: function (e) {
					$("#err").html(e).fadeIn();
				}
			});
		}));
	});
});
//Funktionen zur Organisation hinzufügen
$(".function_org_add").click(function () {
	var oid = $(this).data("oid");
	var appendtext = "<td><input class='checkJSON' name='lang_" + oid + "[]' style='width:100%;' type='text'></td><td><input class='checkJSON' name='kurz_" + oid + "[]' style='width:100%;' type='text'></td><td><input name='app_" + oid + "[]' type='checkbox'></td><td></td>";
	$('.functiontable_' + oid).append("<tr>" + appendtext + "</tr>");
});
//Funktionen Löschen
$(".function_org_del").click(function () {
	$(this).closest('tr').remove();
});

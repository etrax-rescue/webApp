$(function () {	
	//Einklappen - Ausklappen User
	$("body").on("click", ".user_enlarge", function () {
		var oid = $(this).data("oid");
		var enl = $(this).data("show");
		$("#user_" + oid).toggleClass('col-sm-12');
		$("#user_" + oid).toggleClass('col-sm-6');
		$("#user_" + oid).toggleClass('col-lg-12');
		$("#user_" + oid).toggleClass('col-lg-4');
		$("#user_" + oid).toggleClass('col-xl-12');
		$("#user_" + oid).toggleClass('col-xl-3');
		$(".user_list").toggle();
		$("#user_" + oid).toggle();
		$("#" + oid + "_user_details").toggle();
	});
	//Userliste anzeigen
	$("body").on("click", ".user_ausklappen", function () { 
		$(".userlist").toggle();
	});


	//*********************************
	//******* IMPORTDIALOG ************
	//*********************************
	//Importeinstellungen ausklappen
	$("body").on("click", ".settings_show", function () {
		var oid = $(this).data("show");
		$("#settings_show_" + oid).toggle();
	});
	//Import Dialog anzeigen
	$("body").on("click", ".user_show_import", function () {
		var oid = $(this).data("oid");
		$(".modal." + oid).modal("show");
		//$("#"+oid).toggle();
		//$("#"+oid+"_p").toggle();
	});
	//Importvorschau ausführen
	$("body").on("click", ".user_import_preview", function () {
		var fieldnames = ["Name", "Dienstnummer", "Typ", "Username", "Passwort", "E-Mailadresse", "BOS-Kennung", "Telefonnummer", "Notfallkontakt", "Notfallinfo", "Kommentar", "Pause", "Ausbildung"];
		var oid = $(this).data("oid");
		//$(".modal.user_" + oid + "_add").modal("hide");
		//$(".modal.importpreview").modal("show").css('overflow-y', 'auto !important').tooltip();
		setTimeout(function(){ $('.modal.importpreview').modal('show').tooltip() }, 500);
		$(".modal.user_" + oid + "_add").modal('hide');
		
		var iSep = $("#user_" + oid + "_iSep").val();
		iSep = iSep.substr(0, 5); //Spaltentrennzeichen. Länge wird auf 5 gekürzt um exploits zu verhindern
		if (iSep == "TAB") {
			iSep = "	"; //Wandelt das Wort TAB in "	" um.
		}
		var iLine = $("#user_" + oid + "_iLine").val();
		iLine = iLine.substr(0, 5); //Linientrennzeichen. Länge wird auf 5 gekürzt um exploits zu verhindern
		var iEnc = $("#user_" + oid + "_iEnc").val();
		iEnc = iEnc.substr(0, 5); //Textmaskierung. Länge wird auf 5 gekürzt um exploits zu verhindern
		var iEsc = $("#user_" + oid + "_iEsc").val();
		iEsc = iEsc.substr(0, 5); //Escape Zeichen. Länge wird auf 5 gekürzt um exploits zu verhindern
		var iDel = $("#user_" + oid + "_iDel").val();
		iDel = iDel.substr(0, 1); //User vor Import löschen. Länge wird auf 1 gekürzt um exploits zu verhindern
		var iPwUp = $("#user_" + oid + "_iPwUp").val();
		iPwUp = iPwUp.substr(0, 1); //Passwörter Updaten. Länge wird auf 1 gekürzt um exploits zu verhindern
		var iOrg = $("#user_" + oid + "_iOrg").val();
		iOrg = iOrg.substr(0, 10); //Organisations-ID. Länge wird auf 10 gekürzt um exploits zu verhindern
		var funktionen_k = $(".alle_funktionen_kurz_" + oid + "").val(); //Alle Funktionen der Organisation Lange Bezeichnung
		if (funktionen_k.includes(";")) {
			funktionen_k = funktionen_k.split(";");
		} else {
			var funktionen_k = [];
		}
		//In Zeilen aufteilen
		switch (iLine) {
		case "n":
			var user = $("#user_" + oid + "_user").val().split("\n");
			break;
		case "nr":
			var user = $("#user_" + oid + "_user").val().split("\n\r");
			break;
		case "r":
			var user = $("#user_" + oid + "_user").val().split("\r");
			break;
		case "rn":
			var user = $("#user_" + oid + "_user").val().split("\r\n");
			break;
		case "br":
			var user = $("#user_" + oid + "_user").val().split("<br>");
			break;
		}
		//Für jede Zeile eine Tabellenzeile anlegen
		var checked = "";
		var appendtext = appendtext_cb = "";
		var field = user_name = "";
		var usercount = 0;
		//Zeilen aus Tabelle löschen
		var table = $('.previewtable');
		table.find("tbody tr").remove();
		user.forEach((row, index) => {
			checked = error_info = "";
			var cols = row.split(iEnc + iSep + iEnc); //Aufsplitten der Spalten entsprechend der Trennzeichen
			var req = false; //Variable für Pflichtfelder
			if ( !! cols[0] && !! cols[1] && !! cols[2] && !! cols[3] && !! cols[4] && !! cols[5]) {
				req = true;
			} else {
				//Fehlerbeschreibungen
				if (cols[0] == "") {
					error_info = error_info + "Das Namensfeld darf nicht leer sein.<br/>";
				}
				if (cols[1] == "") {
					error_info = error_info + "Die Dienstnummer darf nicht leer sein.<br/>";
				}
				if (cols[2] == "") {
					error_info = error_info + "Der Typ darf nicht leer sein.<br/>";
				}
				if (cols[3] == "") {
					error_info = error_info + "Der Username darf nicht leer sein.<br/>";
				}
				if (cols[4] == "") {
					error_info = error_info + "Das Passwort darf nicht leer sein.<br/>";
				}
			}
			var pwda = cols[4];
			if (pwda) {
				if (pwda.length < 8 || pwda.match(/[A-z]/) === false || pwda.match(/[A-Z]/) === false || pwda.match(/\d/) === false) {
					var req = false;
					error_info = error_info + "Die Passwortkriterien sind nicht erfüllt.<br/>";
				}
			}
			if (!funktionen_k.includes(cols[2])) {
				var req = false;
				error_info = error_info + "Der Typ entspricht nicht den für die Organisation angelegten Typen.<br/>";
			}
			//console.log(!!cols[0]);
			appendtext = "";
			cols.forEach((col, ind) => {
				field = col.replace(iEsc + iEnc, "%|%"); // Die Kombination EscapeZeichen + Enclosure wird auf %|% geändert
				field = field.replace(iEnc, ""); //Die verbleibendenen Textmaskierungen entfernt
				field = field.replace("%|%", iEsc + iEnc); //Die Kombination EscapeZeichen + Enclosure wird auf %|% rückgewandelt 
				field = field.replace(iEsc, ""); //Das Escape Zeichen entfernt 
				//Passwort überprüfen	
				if (ind == 4) {
					var pwd = field;
					//if(pwd){
					if (pwd.length < 8 || pwd.match(/[A-z]/) === false || pwd.match(/[A-Z]/) === false || pwd.match(/\d/) === false) {
						//alert("Das Passwort muss mindestens 8 Zeichen lang sein und 1 Großbuchstaben, 1 Kleinbuchstaben und 1 Zahl enthalten");
						var eCol = "#ff6659";
					} else {
						var eCol = "";
					}
					//}
				}
				if (ind == 0) {
					user_name = field;
				}
				
				var x = ind + 1;
				//appendtext = appendtext + "<td><input type='text' id='i_" + x + "[" + index + "]' name='i_" + x + "[]' size = '8' value='" + field + "' style='background-color:" + eCol + ";'></input></td>";
				appendtext = appendtext + "<div class='form-group row'><label  class='col-sm-3 col-form-label' for='i_" + x + "[" + index + "]'>" + fieldnames[ind] + "</label><div class='col-sm-9'><input type='text' id='i_" + x + "[" + index + "]' name='i_" + x + "[]' value='" + field + "' class=' form-control-plaintext'></input></div></div>";
			});
			if (req == true) {
				checked = "checked";
			}
			if (req == false) {
				checked = "";
				console.log(error_info);
				error_info = "<button class='error_info' style='border:0px;background-color:rgba(0,0,0,0);' title='" + error_info + "'  data-html='true' data-toggle='tooltip' data-placement='bottom'><i class='material-icons' style='color:#D3302F;'>error</i></button>";
			}
			//appendtext_cb = "<td><input type='checkbox' id='iImport[" + index + "]' name='iImport[]' value='" + index + "' " + checked + "></input>" + error_info + "</td>";
			appendtext_cb = "<input type='checkbox' id='iImport[" + index + "]' name='iImport[]' value='" + index + "' " + checked + "></input>" + error_info + "";
			//$('.previewtable').append("<tr>" + appendtext_cb + appendtext + "</tr>");
			$('.previewtable').append(" <div class='card'><div class='card-header' id='heading_" + usercount +"'><h5 class='mb-0'>" + appendtext_cb + " <a class='btn btn-link collapsed' data-toggle='collapse' href='#collapse_" + usercount +"' aria-expanded='false' aria-controls='collapse_" + usercount +"' role='button'>" + user_name +"</a></h5></div><div id='collapse_" + usercount +"' class='collapse' aria-labelledby='heading_" + usercount +"' data-parent='#accordion'><div class='card-body'>" + appendtext + "</div></div></div>");
		usercount = usercount + 1;
		});
		
		$('#iDel').val(iDel);
		$('#iPwUp').val(iPwUp);
		$('#iOrg').val(iOrg);
		//Tooltip fix
		$(".error_info").hover(function () {
			$(this).tooltip('show');
		});
		
	});
	//Userimport durchführen
	$("#userimportform").on("submit", function (event) {
		event.preventDefault();
		var datastring = $(this).serialize();
		$.ajax({
			type: "POST",
			url: "admin.index.php?do=import",
			data: datastring,
			//dataType: "json",
			success: function (msg) {
				//var obj = jQuery.parseJSON(data); //if the dataType is not specified as json uncomment this
				$(".modal.importpreview").modal('hide');
				$(".modal.feedback").modal('show').find("h5").html(msg);
	/*setTimeout(function(){
											$(".modal.feedback").modal('hide');
											location.reload();
											//window.location.href = window.location.href.replace( /[\&#].*|$/, "?oid="+oid );
									}, 2000);*/
			},
			error: function () {
				alert('');
			}
		});
	});

	//*************************************************
	//******* USER ANZEIGEN und BEARBEITEN ************
	//*************************************************
	//User Details anzeigen
	$(".showuser button").click(function() {
		let name,dienstnummer,typ,fid,username,pwd,pause,ausbildungen,email,einsatzfaehig,bos,telefon,notfallkontakt,notfallinfo,kommentar,oid,usersync,funktionen_k,funktionen_l;
		let uid = $(this).data("uid");
		console.log(user_arr);
		$.each(user_arr, function(key, val){
			if(key == uid){
				name = val.name;
				dienstnummer = val.dienstnummer;
				typ = val.typ;
				fid = val.FID;
				username = val.username;
				pwd = val.password;
				pause = val.pause;
				ausbildungen = val.ausbildungen;
				email = val.email;
				einsatzfaehig = val.einsatzfaehig;
				$('#einsatzfaehig option[value=' + einsatzfaehig + ']').attr('selected', 'selected');
				bos = val.bos;
				telefon = val.telefon;
				notfallkontakt = val.notfallkontakt;
				notfallinfo = val.notfallinfo;
				kommentar = val.kommentar;
				oid = val.OID;
				usersync = val.usersync;
				funktionen_k = val.fun_list_kurz; //Alle Funktionen der Organisation Lange Bezeichnung
				if (funktionen_k.includes(";")) {
					funktionen_k = funktionen_k.split(";");
				} else {
					funktionen_k = [funktionen_k];
				}
				funktionen_l = val.fun_list_lang; //Alle Funktionen der Organisation Lange Bezeichnung
				if (funktionen_l.includes(";")) {
					funktionen_l = funktionen_l.split(";");
				} else {
					funktionen_l = [funktionen_l];
				}
			}
		});
		$(".x_name").val(name);
		$(".x_dienstnummer").val(dienstnummer);
		$(".x_typ").val(typ);
		$(".x_username").val(username);
		$(".x_pwd").val(pwd);
		$(".x_pwd_old").val(pwd);
		$(".x_pause").val(pause);
		$(".x_ausbildungen").val(ausbildungen);
		$(".x_email").val(email);
		$(".x_einsatzfaehig").val(einsatzfaehig);
		$(".x_bos").val(bos);
		$(".x_telefon").val(telefon);
		$(".x_notfallkontakt").val(notfallkontakt);
		$(".x_notfallinfo").val(notfallinfo);
		$(".x_kommentar").val(kommentar);
		$(".x_uid").val(uid);
		$(".x_oid").val(oid);
		$(".x_fid").val(fid);
		if (usersync == "1") {
			$(".user_modify").hide();
			$(".user_modify_save").hide();
			$(".user_modify_delete").hide();
			$(".x_user_edit").prop("disabled", true);
		} else {
			$(".user_modify").show();
			$(".user_modify_save").hide();
			$(".user_modify_delete").show();
			$(".x_user_edit").prop("disabled", true);
		}
		if (Array.isArray(funktionen_k)) { //Nur wenn es angelgte Funktionen bei der Organisation gibt
			$('#typ').children('option:not(:first)').remove();
			for (k = 0; k < (funktionen_k.length); k++) {
				if (funktionen_k[k] == typ) {
					var selected_t = "selected";
				} else {
					var selected_t = "";
				}
				$('#typ').append("<option value='" + funktionen_k[k] + "' " + selected_t + ">" + funktionen_l[k] + "</option>");
			}
		}
		$(".modal.usermodal").modal("show");
	});
	//User Bearbeiten aktivieren
	$("body").on("click", ".user_modify", function () {
		$(".user_modify").hide();
		$(".user_modify_save").show();
		$(".x_user_edit").prop("disabled", false);
	});
	//User Details updaten
	$(".user_modify_save").click(function () {
		var name = $(".x_name").val();
		var dienstnummer = $(".x_dienstnummer").val();
		var typ = $(".x_typ").val();
		var username = $(".x_username").val();
		var pause = $(".x_pause").val();
		var ausbildungen = $(".x_ausbildungen").val();
		var pwd = $(".x_pwd").val();
		var pwdold = $(".x_pwd_old").val();
		var email = $(".x_email").val();
		var einsatzfaehig = $(".x_einsatzfaehig").val();
		var bos = $(".x_bos").val();
		var telefon = $(".x_telefon").val();
		var notfallkontakt = $(".x_notfallkontakt").val();
		var notfallinfo = $(".x_notfallinfo").val();
		var kommentar = $(".x_kommentar").val();
		var oid = $(".x_oid").val();
		var uid = $(".x_oid").val() + "-" + dienstnummer;
		var uid_alt = $(".x_uid").val();
		if (pwd !== pwdold) {
			var pwd_set = true;
			if (pwd.length < 8 || pwd.match(/[A-z]/) === false || pwd.match(/[A-Z]/) === false || pwd.match(/\d/) === false) {
				alert("Das Passwort muss mindestens 8 Zeichen lang sein und 1 Großbuchstaben, 1 Kleinbuchstaben und 1 Zahl enthalten");
				return false;
			} else {
				var pwd_check = true;
			}
		} else {
			var pwd_set = false;
		}
		//für read_write_db
		var database = {
			type: "json_admin",
			action: "update",
			table: "user",
			column: "data" 
		};
		// Entries to be display (key: value) 
		// bei json auslesen nur 1 Eintrag!
		var select = {
			UID: uid_alt
		}
		// json Node to be changed (nodename: value)
		var json_nodes = {
			name: "" + name + "",
			dienstnummer: "" + dienstnummer + "",
			typ: "" + typ + "",
			username: "" + username + "",
			email: "" + email + "",
			bos: "" + bos + "",
			telefon: "" + telefon + "",
			notfallkontakt: "" + notfallkontakt + "",
			notfallinfo: "" + notfallinfo + "",
			kommentar: "" + kommentar + "",
			einsatzfaehig: "" + einsatzfaehig + "",
			pause: "" + pause * 60 + "",
			ausbildungen: "" + ausbildungen + "",
			sha256_username: "" + oid + "-" + username + ""
		};
		// direct changes in db ( fieldname: value)
		
		if (pwd !== pwdold) {
			json_nodes.md5_pwd = "" + pwd + "";
		}else{
			json_nodes.pwd = "" + pwdold + "";
		}
		if (pwd_check === true || pwd_set === false) {
			if (confirm("Sollen die Änderungen des Users gespeichert werden?")) {
				$.ajax({
					url: "api/read_write_db.php",
					type: 'post',
					data: {
						database: database,
						select: select,
						values: '',
						json_nodes: json_nodes
					},
					success: function (msg) {
						$(".modal.feedback").modal('show').find("h5").html("Die Änderungen wurden gespeichert!<br>");
						setTimeout(function () {
							$(".modal.feedback").modal('hide');
							//location.reload();
							//window.location.href = window.location.href.replace( /[\&#].*|$/, "?oid="+oid );
						}, 2000);
					}
				});
			} else {
				return false;
			}
		}
	});
	//User Löschen
	$(".user_modify_delete").click(function () {
		var uid = $(".x_uid").val();
		var fid = $(".x_fid").val();
		if (fid.includes(".")) {
			fid = fid.split(".");
			fid = fid[0];
		}
		var name = $(".x_name").val();
		//für read_write_db
		if (fid >= 8 && fid <= 10) { //Nur User bzw. Temporärer Admin
			var database = {
				type: "json_admin",
				// defining datatype json/single value (json/val)
				action: "delete",
				//action read or write
				table: "user",
				// DB Table
				column: "data" // DB Table column for jsons to be changed
			};
			var confirmtext = "Soll der User " + name + " dauerhaft gelöscht werden?";
		}
		if (fid >= 0 && fid < 8) { //Auch Administratorenrechte vorhanden
			var database = {
				type: "json_admin",
				// defining datatype json/single value (json/val)
				action: "update",
				//action read or write
				table: "user",
				// DB Table
				column: "data" // DB Table column for jsons to be changed
			};
			var confirmtext = "Soll der User " + name + " dauerhaft gelöscht werden? \n Die Administratorenrechte bleiben dadurch erhalten.";
		}
		// Entries to be display (key: value) 
		// bei json auslesen nur 1 Eintrag!
		var select = {
			UID: uid
		}
		// json Node to be changed (nodename: value)
		var json_nodes = {
			dienstnummer: "",
			typ: "",
			bos: "",
			telefon: "",
			notfallkontakt: "",
			notfallinfo: "",
			kommentar: "",
			ausbildungen: ""
		};
		// direct changes in db ( fieldname: value)
		var db_vars = {
			einsatzfaehig: "0",
			pause: "0"
		};
		if (confirm(confirmtext)) {
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
					$(".modal.feedback").modal('show').find("h5").html("Der User " + name + " wurde gelöscht!<br>" + msg);
					setTimeout(function () {
						$(".modal.feedback").modal('hide');
						//location.reload();
						//window.location.href = window.location.href.replace( /[\&#].*|$/, "?oid="+oid );
					}, 2000);
				}
			});
		} else {
			return false;
		}
	});
});

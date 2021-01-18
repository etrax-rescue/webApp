$(function () {	
	//reload Button
	$("body").on("click", ".btn-reload", function () {
		location.reload();
	}) 
	//Verhindern der Eingabe von " und \ für JSON
	$(document).ready(function () {
		$(".checkJSON").keypress(function (e) {
			var keyCode = e.which;
			// Unzulässige Zeichen 
			if (keyCode <= 31 || keyCode == 34 || keyCode == 92) {
				e.preventDefault();
				$(".modal.feedback").modal('show').find("h5").html("In diesem Feld dürfen die Zeichen \", \\ und Tabulator nicht eingegeben werden!");
				setTimeout(function () {
					$(".modal.feedback").modal('hide');
				}, 2000);
			}
		});
	});


	//Einklappen - Ausklappen Admin
	$("body").on("click", ".admin_enlarge", function () {
		var oid = $(this).data("oid");
		var enl = $(this).data("show");
		$("#admin_" + oid).toggleClass('col-sm-12');
		$("#admin_" + oid).toggleClass('col-sm-6');
		$("#admin_" + oid).toggleClass('col-lg-12');
		$("#admin_" + oid).toggleClass('col-lg-4');
		$("#admin_" + oid).toggleClass('col-xl-12');
		$("#admin_" + oid).toggleClass('col-xl-3');
		$(".admin_list").toggle();
		$("#admin_" + oid).toggle();
		$("#" + oid + "_admin_details").toggle();
	});
	//Adminliste anzeigen
	$("body").on("click", ".admin_ausklappen", function () {
		$(".adminlist").toggle();
	});
	//Admin Details anzeigen
	
	let eids,eidstext,uid,name,dienstnummer,typ,fid,username,pwd,pause,ausbildungen,email,einsatzfaehig,bos,telefon,notfallkontakt,notfallinfo,kommentar,oid,usersync,funktionen_k,funktionen_l;
	$(".showadmin a").click(function () {
		eids = String($(this).data("eids"));
		eidstext = String($(this).data("eidstext"));
		uid = $(this).data("uid");
		console.log(admin_arr);
		$.each(admin_arr, function(key, val){
			if(key == uid){
				name = val.name;
				login = val.login;
				dienstnummer = val.dienstnummer;
				typ = val.typ;
				fid = val.FID;
				fid = fid.split(".");
				let ulevel = fid[0];
				let urecht = fid[1];
				username = val.username;
				pwd = val.password;
				pause = val.pause;
				ausbildungen = val.ausbildungen;
				email = val.email;
				einsatzfaehig = val.einsatzfaehig;
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

				$(".admin_eids").empty();
				//if (eids.includes(",")) {
				if (eidstext.includes("§§")) {
					//var eids = $(this).data("eids").split(",");
					var eids = $(this).data("eidstext").split("§§");
					var eidl = eids.length;
					for (var i = 0; i < (eidl-1); i++) {
						var eidt = eids[i].split("%%");
						//$(".admin_eids").append("<li># " + eids[i] + "<input type='hidden' name='eid_list_" + oid + "[]' value='" + eids[i] + "'></input><button disabled class='admin_modify_remove_eid y_admin_edit' title='Berechtigung für diesen Einsatz entfernen' data-toggle='tooltip' data-placement='bottom' data-eid='" + eids[i] + "' data-uid='" + uid + "' style='border:0px;background-color:rgba(0,0,0,0);'><i class='material-icons' style='color:#D3302F;'>delete</i></button></li>");
						$(".admin_eids").append("<li># " + eidt[0] + " - " + eidt[1] + " <input type='hidden' name='eid_list_" + oid + "[]' value='" + eidt[0] + "'></input><button disabled class='admin_modify_remove_eid y_admin_edit' title='Berechtigung für diesen Einsatz entfernen' data-toggle='tooltip' data-placement='bottom' data-eid='" + eidt[0] + "' data-uid='" + uid + "' style='border:0px;background-color:rgba(0,0,0,0);'><i class='material-icons' style='color:#D3302F;'>delete</i></button></li>");
					}
				} else {
					var eids = $(this).data("eids");
					if (eids == "0") {
						$(".admin_eids").append("<li># 0 <input type='hidden' name='eid_list_" + oid + "[]' value='0'></input>Berechtigt für alle Einsätze</li>");
					} else {
						//$(".admin_eids").append("<li>" + eids + "</li>");
						$(".admin_eids").append("<li>" + eidstext + "</li>");
					}
				}
				$("#adminlevel").val(ulevel); //Option in Select wählen
				$("#zugriffsrecht").val(urecht); //Option in Select wählen
				$(".y_name").val(name);
				$(".y_loginname").val(username);
				$(".y_username_old").val(login);
				$(".y_email").val(email);
				$(".y_uid").val(uid);
				$(".y_eids").val(eids);
				$(".y_userlevelalt").val(ulevel);
				$(".y_oid").val(oid);
				$(".y_pwd").val(pwd);
				$(".y_pwd_old").val(pwd);
				$(".admin_modify").show();
				$(".admin_modify_save").hide();
				$(".admin_modify_delete").hide();
				$(".y_admin_edit").prop("disabled", true);
				$(".modal.adminmodal").modal("show");
			}
		});
	});
	//Admin Bearbeiten aktivieren
	$("body").on("click", ".admin_modify", function () {
		$(".admin_modify").hide();
		$(".admin_modify_save").show();
		$(".admin_modify_delete").show();
		$(".y_admin_edit").prop("disabled", false);
	});
	//Admin Details updaten
	$(".admin_modify_save").click(function () {
		//var eids = String($(".y_eids").val());
		var eids = "";
		var name = $(".y_name").val();
		var ulevelalt = $(".y_userlevelalt").val();
		var login = $(".y_loginname").val();
		var pwd = $(".y_pwd").val();
		var pwdold = $(".y_pwdold").val();
		var email = $(".y_email").val();
		var uid = $(".y_uid").val();
		var oid = $(".y_oid").val();
		var ulevel = $(".y_adminlevel").val();
		if (ulevel == null) {
			var ulevel = ulevelalt;
		}
		var urechte = $(".y_zugriffsrecht").val();
		var fid = ulevel + "." + urechte;
		if (ulevelalt == 8 && ulevelalt != ulevel) { //nur wenn eine Änderung von Temporärer Admin auf anderen Admintyp erfolgt
		}
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
		//EIDs anpassen
		$('input[name="eid_list_' + oid + '[]"]').each(function (key, value) {
			eids = eids + "," + $(this).val();
		});
		eids = eids.substring(1);
		if (pwd_check === true || pwd_set === false) {
			if (confirm("Sollen die Änderungen des Administrators gespeichert werden?")) {
				// json Node to be changed (nodename: value)
				var json_nodes = {
					name: "" + name + "",
					username: "" + login + "",
					email: "" + email + "",
					dienstnummer: "" + dienstnummer + "",
					typ: "" + typ + "",
					bos: "" + bos + "",
					telefon: "" + telefon + "",
					notfallkontakt: "" + notfallkontakt + "",
					notfallinfo: "" + notfallinfo + "",
					kommentar: "" + kommentar + "",
					einsatzfaehig: "" + einsatzfaehig + "",
					pause: "" + pause * 60 + "",
					ausbildungen: "" + ausbildungen + "",
					FID: "" + fid + "",
					EID: "" + eids + "",
					sha256_username: "" + oid + "-" + login + ""
				};
				console.log(json_nodes);
				if (pwd !== pwdold) {
					json_nodes.md5_pwd = "" + pwd + "";
				}
				if (ulevelalt == 8 && ulevelalt != ulevel) { //Bei Update Temporärer Admin wird EID auf 0 gesetzt
					db_vars.eid = "0";
				}
				$.ajax({
					url: "api/read_write_db.php",
					type: 'post',
					data: {
						database: {
							type: "json_admin",
							action: "update",
							table: "user",
							column: "data" 
						},
						select: {
							UID: uid
						},
						values: '',
						json_nodes: json_nodes
					}
				})
				.done(function () {
					$.ajax({
						url: "api/read_write_db.php",
						type: 'post',
						data: {
							database: {
								type: "no-json",
								action: "update",
								table: "user",
								column: "data" 
							},
							select: {
								UID: uid
							},
							values: '',
							json_nodes: {
								FID: $('.y_adminlevel').val() +'.'+$('.y_zugriffsrecht').val(),
								EID: "0"
							}
						}
					})
					.done(function () {
						$(".modal.feedback").modal('show').find("h5").html("Die Änderungen wurden gespeichert!");
						setTimeout(function () {
							$(".modal.feedback").modal('hide');
							//location.reload();
							//window.location.href = window.location.href.replace( /[\&#].*|$/, "?oid="+oid );
						}, 2000);
					});
				});
			} else {
				return false;
			}
		}
	});
	//Neue Administrator anlegen
	$("body").on("click", ".admin_add", function () {
		var oid = $(this).data("oid");
		//UID erzeugen
		var dnr = make_token(5);
		var uid = oid + "-" + dnr;
		//Username erzeugen
		var uname = "Login-" + make_token(4);
		//Passwort erzeugen
		var pwd = make_token(12);
		//für read_write_db
		var database = {
			type: "json",
			action: "insert",
			table: "user",
			column: "data"
		};
		// Entries to be display (key: value) 
		// bei json auslesen nur 1 Eintrag!
		var select = {
			UID: "" + uid + ""
		}
		// json Node to be changed (nodename: value)
		var json_nodes = {
			name: "Administrator NEU",
			dienstnummer: "" + dnr + "",
			username: "" + uname + "",
			md5_pwd: "" + pwd + "",
			//Erzeugt ein zufälliges Passwort mit 12 Zeichen Länge um den Account zu schützen
			einsatzfaehig: "0",
			email: "email@adresse"
		};
		// direct changes in db ( fieldname: value)
		var db_vars = {
			OID: "" + oid + "",
			UID: "" + uid + "",
			FID: "6.1",
			EID: "0",
			md5_pwd: "" + pwd + "",
			sha256_username: "" + oid + "-" + uname + ""
		};
		if (confirm("Soll eine neuer Administrator angelegt werden?")) {
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
					$(".modal.feedback").modal('show').find("h5").html("Ein neuer Administrator wurde angelegt!<br>");
					setTimeout(function () {
						$(".modal.feedback").modal('hide');
						location.reload();
						//window.location.href = window.location.href.replace( /[\&#].*|$/, "?oid="+oid );
					}, 2000);
				}
			});
		} else {
			return false;
		}
	});
	//Temporärer Admin Berechtigung löschen 
	$("body").on("click", ".admin_modify_remove_eid", function () {
		$(this).closest('li').remove();
	});
	//Admin Löschen
	$(".admin_modify_delete").click(function () {
		var uid = $(".y_uid").val();
		var name = $(".y_name").val();
		//für read_write_db
		var database = {
			type: "json_admin",
			// defining datatype json/single value (json/val)
			action: "delete",
			//action read or write
			table: "user",
			// DB Table
			column: "data" // DB Table column for jsons to be changed
		};
		// Entries to be display (key: value) 
		// bei json auslesen nur 1 Eintrag!
		var select = {
			UID: uid
		}
		// json Node to be changed (nodename: value)
		var json_nodes = {
			name: "" + name + ""
		};
		// direct changes in db ( fieldname: value)
		var db_vars = {
			UID: uid
		};
		if (confirm("Soll der Administrator " + name + " inklusive des damit verbundenen Users dauerhaft gelöscht werden?")) {
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
					$(".modal.feedback").modal('show').find("h5").html("Der Administrator " + name + " wurde gelöscht.<br>");
					setTimeout(function () {
						//$(".modal.feedback").modal('hide');
						//location.reload();
						//window.location.href = window.location.href.replace( /[\&#].*|$/, "?oid="+oid );
					}, 2000);
				}
			});
		} else {
			return false;
		}
	});
	//Admin Funktion entfernen
	$(".admin_modify_downgrade").click(function () {
		var uid = $(".y_uid").val();
		var name = $(".y_name").val();
		//für read_write_db
		var database = {
			type: "no-json",
			action: "update",
			table: "user",
			column: "data"
		};
		// Entries to be display (key: value) 
		// bei json auslesen nur 1 Eintrag!
		var select = {
			UID: "" + uid + ""
		}
		var json_nodes = {
			FID: "10",
			EID: "-1"
		};
		if (confirm("Sollen dem Administrator " + name + " die Administratorenrechte entzogen werden?")) {
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
					$(".modal.feedback").modal('show').find("h5").html("Dem User " + name + " wurden die Administratorenrechte entzogen.<br>");
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
	//User to Admin upgrade Liste anzeigen
	$(".admin_upgrade").click(function () {
		var oid = $(this).data("oid");
		$(".modal.admin_upgrade_modal_" + oid).modal("show");
	});
	//User to Admin upgrade ausführen
	$(".upgrade_user_to_admin").click(function () {
		var uid = $(this).attr('data-uid');
		var name = $(this).attr('data-name');
		if (confirm("Soll der User " + name + " zum Administrator gemacht werden?")) {
			//für read_write_db
			var database = {
				type: "no-json",
				action: "update",
				table: "user",
				column: "data"
			};
			// Entries to be display (key: value) 
			// bei json auslesen nur 1 Eintrag!
			var select = {
				UID: "" + uid + ""
			}
			var json_nodes = {
				FID: "6.1",
				EID: "0"
			};
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
					$(".modal.admin_upgrade_modal_" + oid).modal("hide");
					$(".modal.feedback").modal('show').find("h5").html(name + " wurde zum Administrator gemacht.<br>");
					setTimeout(function () {
						$(".modal.feedback").modal('hide');
						location.reload();
						//window.location.href = window.location.href.replace( /[\&#].*|$/, "?oid="+oid );
					}, 2000);
				}
			});
		} else {
			return false;
		}
	});
});

//Ressourcen
//Neue Ressource anlegen
$(function () {	
	$("body").on("click", ".res_show_import", function () {
		var oid = $(this).data("oid");
		//RID erzeugen
		var rid = oid + "-" + make_token(6);
		//für read_write_db
		var database = {
			type: "json_admin",
			// defining datatype json/single value (json/val)
			action: "insert",
			//action read or write
			table: "ressourcen",
			// DB Table
			column: "data" // DB Table column for jsons to be changed
		};
		// Entries to be display (key: value) 
		// bei json auslesen nur 1 Eintrag!
		var select = {
			RID: "" + rid + ""
		}
		// json Node to be changed (nodename: value)
		var json_nodes = {
			name: "Neue Ressource",
			typ: "NEU"
		};
		// direct changes in db ( fieldname: value)
		var db_vars = {
			OID: "" + oid + "",
			RID: "" + rid + "",
			typ: "NEU"
		};
		if (confirm("Soll eine neue Ressource angelegt werden?")) {
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
					$(".modal.feedback").modal('show').find("h5").html("Eine neue Ressource wurde angelegt!<br>");
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
	//Ressourcen Details anzeigen
	$(".showres button").click(function () {
		var oid = $(this).data("oid");
		var name = $(this).data("name");
		var kennung = $(this).data("kennung");
		var typ = $(this).data("typ");
		var typ_lang = $(this).data("typ_lang");
		var rid = $(this).data("rid");
		var beschreibung = $(this).data("beschreibung");
		$('#res_typ option[value=' + typ + ']').attr('selected', 'selected');
		var typ_k = $(".alle_ressourcen_kurz_" + oid + "").val(); //Alle Typen der Organisation kurze Bezeichnung
		if (typ_k.includes(";")) {
			typ_k = typ_k.split(";");
		}
		var typ_l = $(".alle_ressourcen_lang_" + oid + "").val(); //Alle Ressourcentypen der Organisation Lange Bezeichnung
		if (typ_l.includes(";")) {
			typ_l = typ_l.split(";");
		}
		$(".z_name").val(name);
		$(".z_kennung").val(kennung);
		$(".z_typ").val(typ);
		$(".z_username").val(username);
		$(".z_beschreibung").val(beschreibung);
		$(".z_rid").val(rid);
		$(".z_oid").val(oid);
		$(".user_modify").show();
		$(".user_modify_save").hide();
		$(".user_modify_delete").show();
		$(".z_user_edit").prop("disabled", true);
		for (k = 0; k < (typ_k.length); k++) {
			if (typ_k[k] != "NEU") {
				if (typ_k[k] == typ) {
					var selected_t = "selected";
				} else {
					var selected_t = "";
				}
				$('#res_typ').append("<option value='" + typ_k[k] + "' " + selected_t + ">" + typ_l[k] + "</option>");
			}
		}
		$(".modal.resmodal").modal("show");
	});
	//Ressourcen Bearbeiten aktivieren
	$("body").on("click", ".res_modify", function () {
		$(".res_modify").hide();
		$(".res_modify_save").show();
		$(".z_res_edit").prop("disabled", false);
	});
	//Ressourcen Details updaten
	$(".res_modify_save").click(function () {
		var name = $(".z_name").val();
		var kennung = $(".z_kennung").val();
		var typ = $(".z_typ").val();
		var typ_lang = $(".z_typ_lang").val();
		var beschreibung = $(".z_beschreibung").val();
		var rid = $(".z_rid").val();
		//für read_write_db
		var database = {
			type: "json_admin",
			// defining datatype json/single value (json/val)
			action: "update",
			//action read or write
			table: "ressourcen",
			// DB Table
			column: "data" // DB Table column for jsons to be changed
		};
		// Entries to be display (key: value) 
		// bei json auslesen nur 1 Eintrag!
		var select = {
			RID: rid
		}
		// json Node to be changed (nodename: value)
		var json_nodes = {
			name: "" + name + "",
			kennung: "" + kennung + "",
			typ: "" + typ + "",
			typ_lang: "" + typ_lang + "",
			beschreibung: "" + beschreibung + ""
		};
		// direct changes in db ( fieldname: value)
		var db_vars = {
			typ: "" + typ + ""
		};
		if (confirm("Sollen die Änderungen an der Ressource gespeichert werden?")) {
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
					$(".modal.feedback").modal('show').find("h5").html("Die Änderungen wurden gespeichert!<br>");
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
	//Ressourcen Löschen
	$(".res_modify_delete").click(function () {
	var rid = $(".z_rid").val();
	var name = $(".z_name").val();
	//für read_write_db
	var database = {
		type: "json_admin",
		// defining datatype json/single value (json/val)
		action: "delete",
		//action read or write
		table: "ressourcen",
		// DB Table
		column: "data" // DB Table column for jsons to be changed
	};
	var confirmtext = "Soll die Ressource " + name + " dauerhaft gelöscht werden?";
	// Entries to be display (key: value) 
	// bei json auslesen nur 1 Eintrag!
	var select = {
		RID: rid
	}
	// json Node to be changed (nodename: value)
	var json_nodes = {
		name: ""
	};
	// direct changes in db ( fieldname: value)
	var db_vars = {
		RID: rid
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
				$(".modal.feedback").modal('show').find("h5").html("Die Ressource " + name + " wurde gelöscht!<br>");
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
});
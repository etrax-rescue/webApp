var root = "",
	wp = "api",
	slsh = "/",
	rwdb = "read_write_db.php",
	neueEID = window.neueEID,
	eid = "";

//Sonderzeichen durch HTML Code ersetzen - insbesondere für JSON
var escapeHTML = function(text) {
	var map = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#039;',
		"\\": ''
	};
	return text.replace(/[&<>"'\\]/g, function(m) { return map[m]; });
}

//Aktuelle Zeit
function getcurrentTime() {
	var today = new Date();
	var ss = String(today.getSeconds()).padStart(2, '0');
	var mm = String(today.getMinutes()).padStart(2, '0');
	var hh = String(today.getHours()).padStart(2, '0');
	var dd = String(today.getDate()).padStart(2, '0');
	var MM = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
	var yyyy = today.getFullYear();
	var currenttime = yyyy + '-' + MM + '-' + dd + ' ' + hh + ':' + mm + ':' + ss;

	return currenttime;
}

let database_call = function(db,a,t,c,s,val,json,callback,return_val,type){
	var jsonval = (val) ? val : '';
	var jsondata = (json) ? json : '';//JSON.stringify(json)
	var select = (s) ? s : '';//JSON.stringify(json)
	var type = (type) ? false : true;//JSON.stringify(json)
	//console.log('Values:' + val + ', json_nodes:' + jsondata);
	let database = {action: a,type: t,table: db,column: c};
	jQuery.ajax({
		url: root+wp+slsh+rwdb,
		type: "post",
		async: type,
		scriptCharset: "utf-8" ,
		data: {
			database: database,
			select: select,
			values: jsonval,
			json_nodes: jsondata
		}
	}).done(function(data){
		let value = (return_val) ? data : '';
		(callback) ? callback(value) : '';
	});
}
	
jQuery(function() {
		
		
	jQuery.ajax({
		url: root+'api/get_session_in_js.php',
		type: 'post',
		success: function (data) {
			if(data == 'loggedout'){
				alert('Aus Sicherheitsgründen wurden sie nach 30 Minuten ohne Aktion automatisch ausgelogged. Sie werden auf die Startseite weitergeleitet')
				window.location.href = 'index.php';
			}else{
				let etrax_session = JSON.parse(data);
				let Admineid = etrax_session.adminEID;
				oid = etrax_session.OID;
				fid = etrax_session.FID;
				eid = etrax_session.aktiveEID;
				Userlevel = parseInt(fid.split(".")[0]);
				Userfid = parseInt(fid.split(".")[1]);
			}			
		}
	});

	let timestamp = function() {
		let d = new Date().getTime();
		return d;
	}
	
	// Autologout
	let refresh_Session = function(){
	jQuery.ajax({
		url: root+'api/get_session_in_js.php',
		type: 'post',
		success: function (data) {
			if(data == 'loggedout'){
				alert('Aus Sicherheitsgründen wurden sie nach 30 Minuten ohne Aktion automatisch ausgelogged. Sie werden auf die Startseite weitergeleitet')
				window.location.href = 'index.php';
			}
		}
	});
}
	jQuery('.servicediv #login').on('click','a',function(){
		refresh_Session();
	});
		
	//Logout
	jQuery('body').on('click','.logout',function(e) {
		e.preventDefault();
		jQuery.ajax({
			url: root+wp+slsh+'updatesession.php',
			type: "post",
			data: {
				sessionname: 'destroy'
			}
		}).done(function(data){
			window.location.href = 'index.php';
		});
	});
		
	jQuery("body").attr("id","einsatzstart").addClass("background");
	let einsatzname = sessionStorage.getItem("einsatzname");			
		
	// Zeit immer 2 stellig
	function immer_zweistellig(n) {
		return (n < 10 ? '0' : '') + n;
	}
	
	//Einblenden/ausblenden der Mitgliederliste
	let Mitgliederliste_einblenden = function(){
		var offset = jQuery("#mailliste").offset();
		console.log(offset.left);
		if(offset.left == -600){
			jQuery("#mailliste").animate({left:'+=600px'}, {duration: 1000, easing: "easeOutExpo"});
		}
	}
	
	jQuery("#newadmin").focus(function(){
		Mitgliederliste_einblenden();
	});
	
	jQuery("#closelist").click(function(){
		jQuery("#mailliste").animate({left:'-=600px'}, {duration: 1000, easing: "easeOutExpo"});
	});
	
	//user filtern
	jQuery("#memberbox").keyup(function(){
		var valThis = jQuery(this).val().toLowerCase();
		if(valThis == ""){
			jQuery("#mitgliederliste > li").show();           
		} else {
			jQuery("#mitgliederliste > li").each(function(){
				var text = jQuery(this).text().toLowerCase();
				(text.indexOf(valThis) >= 0) ? jQuery(this).show() : jQuery(this).hide();
			});
		};
	});
	
	//Einsatz löschen
	jQuery(".deleteeinsatz").click(function(){
		jQuery(".modal").modal('hide');
		jQuery(".modal.loescheeinsatz").modal('show');
	});

	//Einsatzes endgültig löschen
	let Einsatz_loeschen = function(){
		var error = "";
		
		var delete_in_db = ["einsatzgruppen","einteilung","externeuser","gesuchteperson","personen_im_einsatz","pois","protokoll_ereignis","protokoll_funk","settings","suchgebiet","tracking"];
		jQuery(".modal.loescheeinsatz").modal('hide');
		delete_in_db.forEach(function(db,i){
			//für read_write_db
			var database = {
				type: "no-json",		// defining datatype json/single value (json/val)
				action: "delete",	//action read or write
				table: ""+db+"",	// DB Table
				column: "data"		// DB Table column for jsons to be changed
			};
			// bei json auslesen nur 1 Eintrag!
			var select = {
				EID: +eid
			}
			// json Node to be changed (nodename: value)
			var json_nodes = {};
			// direct changes in db ( fieldname: value)
			var db_vars = {};
			
			jQuery.ajax({
				url: wp+"/"+rwdb,
				type: 'post',
				data: {
						database: database,
						select: select,
						values: db_vars,
						json_nodes: json_nodes
					}
			}).done(function( msg ) {
				jQuery(".modal.feedback").modal('show').find("h5").html(msg);
				error = error + msg;
			});
			if(delete_in_db.length == i+1){
				jQuery(".modal.feedback").modal('show').find(".modal-header").html("<a href='einsatzwahl.php'>Zurück zur Einsatzauswahl</a> "+error);
				jQuery(".modal.feedback").find(".modal-footer").hide();
				//window.location.href = "einsatzwahl.php";
			}
		});
	}
		
	jQuery("#einsatzloeschen").click(function(){
		Einsatz_loeschen();
	});



	//Sicherheitscheck Einsatzende
	jQuery("#einsatzendesetzen .beenden").click(function(){
		jQuery(".modal.einsatzende").modal('show');
	});
	
	// Personen aus dem Einsatz nehmen
	let delete_User_from_Einsatz = function(data) {
		let json_nodes = {
			status: "ausser Dienst",
			gruppe:"",
			abgemeldet: Math.floor(Date.now() / 1000)
		};
		let personen_data = JSON.parse(data);
		let num = 0;
		jQuery(personen_data).each(function(i,val){
			database_call('settings','update','json_update','personen_im_einsatz',{EID: eid},''+val.UID+'',json_nodes,'',false);
		});
		database_call('user','mysql_update','','aktiveEID',{aktiveEID: eid},'NULL','','',false);
		Einsatzende_protokollieren();
	}

	//Einsatzende protokollieren
	let Einsatzende_protokollieren = function() {console.log('Einsatzende_protokollieren');
		let ins_Protokoll_schreiben = function(json) {
			let protokoll_entries = JSON.parse(json);console.log(Object.keys(protokoll_entries).length);
			let spruch_id = (Object.keys(protokoll_entries).length != '') ? Object.keys(protokoll_entries).length : 0;
			var jetzt = new Date();
			var zeit = jetzt.getFullYear()+'-'+immer_zweistellig(jetzt.getMonth()+1)+'-'+immer_zweistellig(jetzt.getDate())+' '+immer_zweistellig(jetzt.getHours()) + ":" + immer_zweistellig(jetzt.getMinutes()) + ":" + immer_zweistellig(jetzt.getSeconds());
			let json_nodes = 
				{
					id:spruch_id,
					oid: oid,
					type: 'protokoll',
					phone: '',
					bos: '',
					name: '',
					read: true,
					betreff: '',
					deleted: '',
					text: 'Einsatzende: ' + einsatzname,
					zeit: zeit,
					funkmittel: ''
				}
			database_call('settings','update','json_append','protokoll',{EID: eid},spruch_id, json_nodes,'',false);
		}
		database_call('settings','read','json','protokoll',{EID: eid},'','',ins_Protokoll_schreiben,true);
	}

	
	//Das Ende des Einsatzes Protokollieren
	let Einsatz_beenden = function() {
		jQuery(".modal.einsatzende").modal('hide');
		database_call('settings','read','json','personen_im_einsatz',{EID: eid},'','',delete_User_from_Einsatz,true);
	}
	
	jQuery('#einsatzstart').on('click','#einsatzende',function() {
		database_call('settings','update','json','data',{EID: eid},'',{ende: ""+window.time+""},Einsatz_beenden,false);
	});
	
	//Umbenennen eines Einsatzes
	let Einsatz_umbenennen = function(that,focus) {
		if(focus){
			einsatzname = that.text();
			console.log(einsatzname);
		}else{
			var newname = that.text();
			console.log("Neuer ename: "+newname);
			if(einsatzname != newname){
				database_call('settings','update','json','data',{EID: eid},'',{einsatz: ""+escapeHTML(newname)+""},'',false);
			}
		}
	}
	jQuery(".ename_editable").focus(function() {
		Einsatz_umbenennen(jQuery(this),true);
	}).blur(function() {
		Einsatz_umbenennen(jQuery(this),false);
	});
	
	// Protokolldownload
	
	jQuery(".showprotokoll a").click(function(){
		jQuery(".protokolle").modal("show");
	});

	//Funkprotokoll
	
	//jQuery("#funkprotokolliert").load( "helper/funkprotokollladen.php" )
	
	jQuery("#funkid").focus(function(){
		jQuery(this).removeClass("error").val("");
	});
	
	// Funkprotokoll laden
	let Funkprotokoll_laden = function(data){console.log(eid);
		jQuery("#funkprotokolliert").html("");
		let spruch_data = JSON.parse(data);
		jQuery(spruch_data).each(function(i,val){
			if(val.data[0].type.indexOf('funk') !== -1){
				let quelle = (typeof val.data[0].funk === 'undefined' && val.data[0].bos != '') ? val.data[0].bos+', '+val.data[0].phone : val.data[0].funkmittel;
				jQuery("#funkprotokolliert").prepend('<li class="text text-left"><a href="javascript:;" class="funktoprotokoll" data-text="['+quelle+'] '+val.data[0].text+'" data-time="'+val.data[0].zeit+'" data-id="'+val.data[0].id+'" title="In das Ereignisprotokoll übernehmen"><i class="material-icons">input</i></a><a href="javascript:;" class="funktoprotokoll loeschen" data-id="'+val.data[0].id+'" title="Protokolleintrag löschen"><i class="material-icons">delete</i></a><span class="zeit">'+val.data[0].zeit+' </span><span class="inhalt">['+quelle+'] '+val.data[0].text+'</span></li>');
			}
		});
		jQuery(".modal.funkprotokollmodal").modal('show');
	}
	
	let Funkprotokoll_neu_laden = function(){
		database_call('settings','read','json','protokoll',{EID: eid},'','',Funkprotokoll_laden,true);
	}
	
	jQuery('body').on('click','.funkprotokoll',function() {
		Funkprotokoll_neu_laden();
	});

	
	// Protokoll laden
	let Protokoll_laden = function(data){
		jQuery("#protokolliert").html("");
		let protokoll_data = JSON.parse(data);
		jQuery(protokoll_data).each(function(i,val){
			if(val.data[0].type.indexOf('protokoll') !== -1 && val.data[0].oid == oid){
				let quelle = (val.data[0].bos != '') ? '['+val.data[0].bos+', '+val.data[0].phone+']' :'';
				jQuery("#protokolliert").prepend('<li class="text text-left '+val.data[0].deleted+'"><a href="javascript:;" class="loeschen" data-id="'+val.id+'" title="Aus dem Protokoll löschen"><i class="material-icons">delete</i></a><span class="zeit">'+val.data[0].zeit+' </span><b class="text-danger">'+val.data[0].betreff+' </b><span class="inhalt">'+quelle + val.data[0].text+'</span></li>');
			}
		});
		jQuery(".modal.protokollmodal").modal('show');
	}
	
	let Protokoll_neu_laden = function(){
		database_call('settings','read','json','protokoll',{EID: eid},'','',Protokoll_laden,true);
	}
	
	jQuery('body').on('click','.protokoll a',function() {
		Protokoll_neu_laden();
	});

	
	// Im Protokoll speichern
	let Im_Protokoll_speichern = function(that) {
		let entryType = that;
		let ins_Protokoll_schreiben = function(json) {
		let protokoll_entries = JSON.parse(json);
			let spruch_id = (Object.keys(protokoll_entries).length != '') ? Object.keys(protokoll_entries).length : 0;
			if(entryType == 'textsubmit'){
				if(jQuery("#protokolltext").val() !="" && (jQuery("#protokolltext").val() != "Text einfügen!")){
					var jetzt = new Date();
					var zeit = getcurrentTime();
					var text = jQuery('#protokolltext').val();
					let json_nodes = 
						{
							id:spruch_id,
							oid: oid,
							type: 'protokoll',
							phone: '',
							bos: '',
							name: '',
							read: true,
							betreff: '',
							deleted: '',
							text: text,
							zeit: zeit,
							funkmittel: ''
						}
					database_call('settings','update','json_append','protokoll',{EID: eid},spruch_id, json_nodes,Protokoll_neu_laden,false);
					jQuery("#protokolltext").val('');
					jQuery("#protokolltext").addClass("ok")
				}else{
					jQuery("#protokolltext").addClass("error")
					jQuery("#protokolltext").val('Text einfügen!');
				}
			}else{
				timer = setTimeout(Uhrzeit_anzeigen, 1000);
				if(jQuery("#funkprotokolltext").val() !="" && jQuery("#funkid").val() !="" && (jQuery("#funkprotokolltext").val() != "Text einfügen!")){
					//let spruch_id = jQuery('#funkprotokolliert li').length + 1;
					let text = jQuery("#funkprotokolltext").val();
					let zeit = jQuery("#ftime").val();
					let funkid = jQuery("#funkid").val();
					let json_nodes = 
						{
							id:spruch_id,
							oid: oid,
							type: 'funk',
							phone: '',
							bos: '',
							name: '',
							read: true,
							betreff: 'Spruch',
							text: text,
							zeit: zeit,
							funkmittel: funkid
						}
					database_call('settings','update','json_append','protokoll',{EID: eid},spruch_id, json_nodes,Funkprotokoll_neu_laden,false);
					
					jQuery("#funkprotokolltext, #funkid").val('');
					jQuery("#funkprotokolltext, #funkid").addClass("ok")
				}else if(jQuery("#funkid").val() ==""){
					jQuery("#funkid").addClass("error");
					jQuery("#funkid").val('Funkmittel einfügen!');
				}else if(jQuery("#funkprotokolltext").val() ==""){
					jQuery("#funkprotokolltext").addClass("error");
					jQuery("#funkprotokolltext").val('Text einfügen!');
				}
			}
		}
		database_call('settings','read','json','protokoll',{EID: eid},'','',ins_Protokoll_schreiben,true);
	};
	
	jQuery('body').on('click','.protokollieren',function() {
		Im_Protokoll_speichern(jQuery(this).attr('id'));
	});	

	// Nachricht löschen
	let Protokolleintrag_loeschen = function(that){
		let deleted = (that.parent().hasClass('deleted')) ? '' : 'deleted';
		database_call('settings','update','json_update','protokoll',{EID: eid},that.attr('data-id'), {'deleted': deleted},Protokoll_neu_laden,false);
	}
	
	jQuery("#protokolliert").on("click","a",function() {
		Protokolleintrag_loeschen($(this));
	});
		
	//Übertrag Funkprotokoll -> Ereignisprotokoll
	let Funk_in_Protokoll_schreiben = function(that) {
		jQuery(".modal.funkprotokollmodal").modal('hide');
		database_call('settings','update','json_update','protokoll',{EID: eid},that.attr('data-id'), {type: 'funk,protokoll'},'',false);
	}
	
	//In Funkprotokoll löschen
	let in_Funk_loeschen = function(that) {
		jQuery(".modal.funkprotokollmodal").modal('hide');
		database_call('settings','update','json_delete','protokoll',{EID: eid}, that.attr("data-fid"),'',Funkprotokoll_neu_laden,false);
	}
		
	jQuery("#funkprotokolliert").on("click","a.funktoprotokoll",function() {
		if(jQuery(this).hasClass('loeschen')){
			in_Funk_loeschen(jQuery(this));
		}else{
			Funk_in_Protokoll_schreiben(jQuery(this));
		}
	});
	
	//filtern der Protokoll Liste
	let Liste_filtern = function(that){
		var valThis = that.val().toLowerCase();
		var thatID = that.attr("data-listID");
		if(valThis == ""){
			jQuery(thatID+" > li").show();           
		} else {
			jQuery(thatID+" > li").each(function(){
				var text = jQuery(this).text().toLowerCase();
				(text.indexOf(valThis) >= 0) ? jQuery(this).show() : jQuery(this).hide();
			});
		};
	}
	
	jQuery(".sortliste").keyup(function(){
		Liste_filtern(jQuery(this));
	});
	
	jQuery('body').on('click','.settings a',function() {
		jQuery(".settingsmodal .modal-body").load( "api/einstellungen.php?t="+timestamp(), function() {
			jQuery(".settingsmodal").modal("show");
		});
	});

	jQuery(".settingsmodal").on('hide.bs.modal', function (e) {
		jQuery(".settingsmodal .modal-body").empty();
	});
	
	jQuery('body').on('click','.open-gesucht a',function() {
		$(".gesuchtePerson .modal-body").load("api/gesucht.php?t="+timestamp(), function(responseTxt, statusTxt, xhr){
			if(statusTxt == "success"){
				jQuery(".gesuchtePerson").modal("show");
			}
		});
	});
	
	jQuery('body').on('click','.checkliste a',function() {
		jQuery(".checklisteEinsatz").modal("show");
		jQuery(".checklisteEinsatz .modal-body").load( "api/checkliste.php?t="+timestamp(), function() {
			jQuery(".checklisteEinsatz").modal("show");
		});
	});
	jQuery('body').on('click','.showbericht a',function() {
		jQuery(".berichtEinsatz .modal-body").load( "api/einsatzbericht.php?t="+timestamp(), function() {
			jQuery(".berichtEinsatz").modal("show");
		});
	});
	jQuery('body').on('click','#einsatzberichtsubmit',function() {
		jQuery(".berichtEinsatz .modal-body #einsatzbericht").submit();
	});
	
	/*var timer;
	function timeval(){
		var today = new Date();
		var date = today.getFullYear()+'-'+('0'+(today.getMonth()+1)).slice(-2)+'-'+('0'+today.getDate()).slice(-2);
		var time = today.getHours() + ":" + ('0'+today.getMinutes()).slice(-2) + ":" + ('0'+today.getSeconds()).slice(-2);
		var dateTime = date+' '+time;
		jQuery("#time").val(dateTime);
		timer = setTimeout(timeval, 1000);
	}
	timeval();*/
	
	// Funkprotokoll Timer
	
	var timer;
	let Uhrzeit_anzeigen = function(){
		var today = new Date();
		var date = today.getFullYear()+'-'+('0'+(today.getMonth()+1)).slice(-2)+'-'+('0'+today.getDate()).slice(-2);
		var time = today.getHours() + ":" + ('0'+today.getMinutes()).slice(-2) + ":" + ('0'+today.getSeconds()).slice(-2);
		var dateTime = date+' '+time;
		jQuery("#ftime").val(dateTime);
		timer = setTimeout(Uhrzeit_anzeigen, 1000);
	}
	Uhrzeit_anzeigen();
	jQuery('body').on('click','#changetime',function() {
		Uhrzeit_anzeigen();
	});
	jQuery('body').on('focus','#funkid,#funkprotokolltext,#ftime',function() {
		clearTimeout(timer);
	});
	
	jQuery('[data-toggle="tooltip"]').tooltip();
	
	//Organisationsberechtigungen ändern - NEU
	jQuery("#changeleadsettings input").change(function(){
		var eid = sessionStorage.getItem("eid"); //EID des Einsatzes
		var oids = $(".saveleadsettings").data('oids');
		oids = oids.split(',');
		console.log(oids);
		var Ogleich = [];
		var Ozeichnen = [];
		var Ozuweisen = [];
		var Osehen = [];
		for(k = 0; k < (oids.length); k++	){
			if($(".Ogleich_"+oids[k]).is(':checked')){
				Ogleich[k] = Ozeichnen[k] = Ozuweisen[k] = Osehen[k] = oids[k];
				$(".Ozeichnen_"+oids[k]).prop('checked', true).prop( "disabled", true );
				$(".Ozuweisen_"+oids[k]).prop('checked', true).prop( "disabled", true );
				$(".Osehen_"+oids[k]).prop('checked', true).prop( "disabled", true );
			} else {
				$(".Ozeichnen_"+oids[k]).prop( "disabled", false );
				$(".Ozuweisen_"+oids[k]).prop( "disabled", false );
				$(".Osehen_"+oids[k]).prop( "disabled", false );
			}
			if($(".Ozeichnen_"+oids[k]).is(':checked')){
				Ozeichnen[k] = Osehen[k] = oids[k];
				$(".Osehen_"+oids[k]).prop('checked', true).prop( "disabled", true );
			} else {
				if(!$(".Ozuweisen_"+oids[k]).is(':checked')){
					$(".Osehen_"+oids[k]).prop( "disabled", false );
				}
			}
			if($(".Ozuweisen_"+oids[k]).is(':checked')){
				Ozuweisen[k] = Osehen[k] = oids[k];
				$(".Osehen_"+oids[k]).prop('checked', true).prop( "disabled", true );
			} else {
				if(!$(".Ozeichnen_"+oids[k]).is(':checked')){
					$(".Osehen_"+oids[k]).prop( "disabled", false );
				}
			}
			if($(".Osehen_"+oids[k]).is(':checked')){ Osehen[k] = oids[k];}
		}
		Ogleich = Ogleich.join();
		console.log("Gleich "+Ogleich);
		Ozeichnen = Ozeichnen.join();
		console.log("Zeichnen "+Ozeichnen);
		Ozuweisen = Ozuweisen.join();
		console.log("Zuweisen "+Ozuweisen);
		Osehen = Osehen.join();
		console.log("Sehen "+Osehen);
		
		//für read_write_db
		var database = {
			type: "no-json",		// defining datatype json/single value (json/val)
			action: "update",	//action read or write
			table: "settings",	// DB Table
			column: "data"		// DB Table column for jsons to be changed
		};
		// bei json auslesen nur 1 Eintrag!
		var select = {
			EID: ""+eid+""
		}
		// json Node to be changed (nodename: value)
		var json_nodes = {
		};
		// direct changes in db ( fieldname: value)
		var db_vars = {
			Ogleich: ""+Ogleich+"",
			Ozeichnen: ""+Ozeichnen+"",
			Ozuweisen: ""+Ozuweisen+"",
			Osehen: ""+Osehen+""
		};
		
		jQuery.ajax({
			url:wp+"/"+rwdb,
			type: 'post',
			data: {
					database: database,
					select: select,
					values: db_vars,
					json_nodes: json_nodes
				}
		}).done(function(e) {
			console.log(e);
		});
		//console.log(jQuery(this).val(),jQuery(this).attr("name"));
		//var senddata = "{"+jQuery(this).attr("name")+": "+jQuery(this).val()+"}";
	});
	
	/*jQuery("#changeleadsettings input").change(function(){
		var data = comma = "";
		var senddata = {
				table: "settings",
				where: "EID = '"+eid+"'"
			};
		jQuery("input[type=checkbox]."+jQuery(this).attr("name")+":checked").each(function(){
			data += comma + jQuery(this).val();
			comma = ",";
		});
		senddata[jQuery(this).attr("name")] = data;
		console.log(senddata);
		jQuery.ajax({
			url: "write/updatedb.php",
			type: "post",
			data: senddata
		}).done(function(e) {
			console.log(e);
		});
		//console.log(jQuery(this).val(),jQuery(this).attr("name"));
		//var senddata = "{"+jQuery(this).attr("name")+": "+jQuery(this).val()+"}";
	});*/
	jQuery(".saveleadsettings").click(function(e){
		jQuery(".modal.settingsmodal").modal("hide");
	});
});
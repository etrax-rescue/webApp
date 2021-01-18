const  wp = "api",
		us = 'updatesession.php',
		rwdb = "read_write_db.php",
		sec = "decrypt",
		get = "?href",
		ending = ".txt",
		root = "",
		slsh = "/";
		
let database_call = function(db,a,t,c,s,val,json,callback,return_val){
	let jsonval = (val) ? val : '';
	let jsondata = (json) ? json : '';//JSON.stringify(json)
	let select = (s) ? s : '';//JSON.stringify(json)
	let database = {action: a,type: t,table: db,column: c};
	jQuery.ajax({
		url: root+wp+slsh+rwdb,
		type: "post",
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

$(function() {
	let googleapikey,OID,lon_default,lat_default,eid,aktive_eid;
	let Get_Session_vars = function(){
		jQuery.ajax({
			url: 'api/get_session_in_js.php',
			type: 'post',
			success: function (data) {
				if(data != 'loggedout'){
					let etrax_session = JSON.parse(data);
					eid = etrax_session.EID;
					aktive_eid = parseInt(etrax_session.aktiveEID);
					googleapikey = etrax_session.googleAPI;
					lon_default = etrax_session.lon_default;
					lat_default = etrax_session.lat_default;
					OID = etrax_session.OID;
					start_scripts();
				}else{
					alert('Aus Sicherheitsgründen wurden sie nach 30 Minuten ohne Aktion automatisch ausgelogged. Sie werden auf die Startseite weitergeleitet')
					window.location.href = 'index.php';
				}
			}
		});
	}
	Get_Session_vars();

	let start_scripts = function(){
		jQuery("body").addClass("background einsatzwahl");
		sessionStorage.setItem("eid","");
		sessionStorage.setItem("einsatzname","");
		

		//Einschränkung der Suche mit Google Adresssuchen
		let lonmin,
			lonmax,
			latmin,
			latmax,
			elselon,
			elselat,
			neueEID = window.neueEID;
		//Sonderzeichen durch HTML Code ersetzen - insbesondere für JSON
		function escapeHTML(text) {
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
		

		
		//Tooltip
		$('[data-toggle="tooltip"]').tooltip();

			
		//Logout
		jQuery('body').on('click','.logout',function(e) {
			e.preventDefault();
			jQuery.ajax({
				url: root+wp+slsh+us,
				type: "post",
				data: {
					sessionname: 'destroy'
				}
			}).done(function(data){
				window.location.href = 'index.php';
			});
		});


		//Auswahl des Eindatzes
		let neuerEinsatz = function(){
				$(".modal.einsatzneu").modal('show');
		}

		$('body').on('click','#einsatzneu',function(){
			neuerEinsatz();
		});

		let einsatzwahl = function(that){
			jQuery.ajax({
				url: root+wp+slsh+us,
				type: "post",
				data: {
					sessionname: 'EID',
					sessionval: that.attr("data-eID")
				}
			}).done(function(){
				sessionStorage.setItem("eid",that.attr("data-eID"));
				sessionStorage.setItem("einsatzname",that.attr("data-name"));
				window.location.href = 'einsatz-start.php';
			});
			
		}

		$('body').on('click','.einsatz-start',function(){
			einsatzwahl($(this));
		});

		//Funktion zum Adresssuchen via Google API
		function initialize(that) {
			var options = {
				componentRestrictions: {country: ['at', 'de', 'ch']}
			};
			var input = document.getElementById(that);
			var autocomplete = new google.maps.places.Autocomplete(input, options);
			google.maps.event.addListener(autocomplete, 'place_changed', function () {
				var place = autocomplete.getPlace();
				$("#einsatzlat").val(place.geometry.location.lat());
				$("#einsatzlon").val(place.geometry.location.lng());
			});
		}
		
		$('body').on('focus','#einsatzname',function(){
			var id = $(this).attr("id");
			if(googleapikey != ''){
				initialize(id);
			}
		});
		
		$("#einsatzbeginn").on("click","button", function(){
			if($(this).hasClass('beginn')){
				Einsatz_anlegen($(this));
			}else{
				$(".modal.einsatzneu").modal('hide');
			}
		});	
		
		let Einsatz_anlegen = function(that){
			let e_typ,text,lon,lat,starttime,neueEID,eOID;
			if($("#einsatzname").val() != ""){
				e_typ = $(".e_typ input:checked").val();
				text = $("#einsatzname").val();
				lon = ($("#einsatzlon").val() != '') ? $("#einsatzlon").val() : lon_default;
				lat = ($("#einsatzlat").val() != '') ? $("#einsatzlat").val() : lat_default;
				starttime = ($('#einsatzstart').val() != '') ? $('#einsatzstart').val() : window.time;
				eOID = $('#primorg  option:selected').val();
				var error = "";
				$(".modal.einsatzneu").modal('hide');
				sessionStorage.setItem("centerX",lat);
				sessionStorage.setItem("centerY",lon);
				sessionStorage.setItem("einsatzname",text);
							
				// checking if Google found location
				
				// direct changes in db ( fieldname: value)
				let einsatz_vars = {
					'OID': eOID,
					'Ogleich': OID,
					'Ozeichnen': OID,
					'Ozuweisen': OID,
					'Osehen': OID,
					'einsatz': text,
					'anfang': starttime,
					'ende': '',
					'elon': lon,
					'elat': lat,
					'trackstyle': 'line',
					'centerlon': lon,
					'centerlat': lat,
					'zoom': 10,
					'trackreload': 1,
					'minspeed' :'0.001',
					'maxspeed' :'3.3335',
					'minpunkte': 5,
					'trackpause': 3600,
					'newtrackloading': 60000,
					'trackstart': Math.round((new Date()).getTime() / 1000),
					'token': window.md5time,
					'HFquote': 2.5,
					'restrictedExtent': 0,
					'suchtyp': '',
					'ppilat': '',
					'ppilon': ''
				};
				sessionStorage.setItem('mapcenterX', lon);
				sessionStorage.setItem('mapcenterY', lat);
				let poi_vars = {"type":"FeatureCollection","features":[
					{"type": "Feature",
						"properties": {
							"name": "Einsatz Startpunkt",
							"color": "#c00",
							"beschreibung": "",
							"img": "",
							"poi": Date.now()
							},
						"geometry": {
							"type": "Point",
							"coordinates": {
								"0": lon,
								"1": lat
							}
						}
					}
				]};
				let search_vars = {
					"type":"FeatureCollection",
					"features":[{
						"type":"Feature",
						"properties":{
							"name":"Gruppe0",
							"typ":"EL",
							"id":"area51",
							"color":"0",
							"beschreibung":"0",
							"img":"",
							"gruppe":"e_group0",
							"OID":"0",
							"strokecolor":"0",
							"strokewidth":"2",
							"fillcolor":"0",
							"status":"",
							"masse":""
							},
						"geometry":
							{
							"type":"Point",
							"coordinates":[
								lon,
								lat
								]
							}
						}
					]
				}
				let checkliste_eintrag = {
					'bo_100':'',
					'ea_100':'',
					'bo_1':false,
					'bo_2':false,
					'bo_3':false,
					'bo_4':false,
					'bo_5':false,
					'bo_6':false,
					'bo_7':false,
					'bo_8':false,
					'bo_9':false,
					'bo_10':false,
					'bo_11':false,
					'bo_12':false,
					'bo_13':false,
					'bo_14':false,
					'bo_15':false,
					'st_1':false,
					'st_2':false,
					'pf_1':false,
					'pf_2':false,
					'pf_3':false,
					'pf_4':false,
					'pf_5':false,
					'pf_6':false,
					'pf_7':false,
					'pf_8':false,
					'pf_9':false,
					'pf_10':false,
					'ea_1':false,
					'ea_2':false,
					'ea_3':false,
					'ea_4':false,
					'ee_1':false,
					'ee_2':false,
					'ee_3':false
				};
				
				let einsatz_data = 
					{
						id:0,
						data:[{
							oid: OID,
							type: 'protokoll',
							phone: '',
							bos: '',
							name: '',
							read: true,
							betreff: '',
							deleted: '',
							text: 'Einsatzbeginn: ' + text,
							zeit: starttime,
							funkmittel: ''
						}]
					}
				database_call('settings','insert','','','',{typ : e_typ, data : [einsatz_vars], protokoll : [einsatz_data] ,pois : poi_vars, suchgebiete : search_vars, checkliste:[checkliste_eintrag], gesucht:[{gesuchtbild: "img/no-pic.jpg"}]},Einsatz_angelegt,false);
			}else{
				$("#einsatzname").focus().css('background','#f4cbcb').attr('placeholder','Einsatzort fehlt');
			}
		}

		let Einsatz_angelegt = function(){
			setTimeout(
				function() 
				{
					location.reload();
				}, 1000);
		}
		
		

		//User Details anzeigen
		let showuserdetails = function(that) {		
			var usersync = that.data("usersync");
			if(usersync == "1"){
				$(".user_modify").hide();
				$(".user_modify_save").hide();
				//$(".user_modify_delete").hide();
				$(".x_user_edit").prop("disabled",true);
				$(".usersync_info").show();
			} else {
				$(".user_modify").show();
				$(".user_modify_save").hide();
				$(".user_modify_delete").show();
				$(".x_user_edit").prop("disabled",true);
				$(".usersync_info").hide();
			}	
			$(".modal.usermodal").modal("show");
		}
		
		$(".showuserdetails").click(function() {
			showuserdetails($(this));
		});
		
		//User Bearbeiten aktivieren
		$("body").on("click",".user_modify",function(){
			$(".user_modify").hide();
			$(".user_modify_save").show();
			$(".x_user_edit").prop("disabled",false);
		});
		
		
		
		//User Details updaten
		let user_modify_save = function() {
			var name = $(".x_name").val();
			var dienstnummer = $(".x_dienstnummer").val();
			var typ = $(".x_typ").val();
			var username = $(".x_username").val();
			var pwd = $(".x_pwd").val();
			var pwdold = $(".x_pwd_old").val();
			var email = $(".x_email").val();
			var einsatzfaehig = $(".x_einsatzfaehig").val();
			var bos = $(".x_bos").val();
			var telefon = $(".x_telefon").val();
			var notfallkontakt = $(".x_notfallkontakt").val();
			var notfallinfo = $(".x_notfallinfo").val();
			var kommentar = $(".x_kommentar").val();
			var uid = $(".x_oid").val()+"-"+dienstnummer;
			var uid_alt = $(".x_uid").val();
			if(pwd !== pwdold){
				var pwd_set = true;
				if(pwd.length < 8 || pwd.match(/[A-z]/) === false || pwd.match(/[A-Z]/) === false || pwd.match(/\d/) === false){
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
				type: "json",		// defining datatype json/single value (json/val)
				action: "update",	//action read or write
				table: "user",	// DB Table
				column: "data"		// DB Table column for jsons to be changed
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
				email: ""+email+"",
				bos: ""+bos+"",
				telefon: ""+telefon+"",
				notfallkontakt: ""+notfallkontakt+"",
				notfallinfo: ""+notfallinfo+""
			};
			// direct changes in db ( fieldname: value)
			var db_vars = {};
			if(pwd !== pwdold){
				json_nodes.md5_pwd = ""+pwd+"";
			}else{
				json_nodes.pwd = ""+pwdold+"";
			}
			
			if(pwd_check === true || pwd_set === false){
							
				if (confirm("Sollen die Änderungen des Users gespeichert werden?")) {
				
						$.ajax({
							url: wp+"/"+rwdb,
							type: 'post',
							data: {
								database: database,
								select: select,
								values: '',
								json_nodes: json_nodes
							}
						}).done(function() {
						//window.location.reload();
						$(".user_modify").show();
						$(".user_modify_save").hide();
						let newdata = {
							email: ""+email+"",
							bos: ""+bos+"",
							telefon: ""+telefon+"",
							notfallkontakt: ""+notfallkontakt+"",
							notfallinfo: ""+notfallinfo+""
						}
						let key;
						for (key in newdata) {
							$.ajax({
								url: wp+"/"+us,
								type: 'post',
								data: {
									sessionname: key,
									sessionval: newdata[key]
								}
							});
						}
					});
				}
			} else {
				return false;
			}
		}
		
		$(".user_modify_save").click(function() {
			user_modify_save();
		});
		$('.category .grid').masonry({
			columnWidth: '.einsatz-card',
			itemSelector: '.einsatz-card',
			percentPosition: true
		})


		$('.pwdcheck').keyup(function(){
			var pwd = ($(this).val());
			var repwd = ($('.repwdcheck').val());
			if (pwd.length >= 8) {
				$('.length').removeClass("text-danger");
				$('.length').addClass("text-success");
				var checkl = true;
			} else {
				$('.length').addClass("text-danger");
				$('.length').removeClass("text-success");
				var checkl = false;
			}
			if (pwd.match(/[a-z]/)) {
				$('.letter').removeClass("text-danger");
				$('.letter').addClass("text-success");
				var checkle = true;
			} else {
				$('.letter').addClass("text-danger");
				$('.letter').removeClass("text-success");
				var checkle = false;
			}
			if (pwd.match(/[A-Z]/)) {
				$('.capital').removeClass("text-danger");
				$('.capital').addClass("text-success");
				var checkc = true;
			} else {
				$('.capital').addClass("text-danger");
				$('.capital').removeClass("text-success");
				var checkc = false;
			}
			if (pwd.match(/\d/)) {
				$('.number').removeClass("text-danger");
				$('.number').addClass("text-success");
				var checkn = true;
			} else {
				$('.number').addClass("text-danger");
				$('.number').removeClass("text-success");
				var checkn = false;
			}
			
			if (pwd === repwd) {
				$('.match').removeClass("text-danger");
				$('.match').addClass("text-success");
				var checkm = true;
			} else {
				$('.match').addClass("text-danger");
				$('.match').removeClass("text-success");
				var checkm = false;
			}
			
			if (pwd.length > 0) {
				if(checkl && checkle && checkc && checkn && checkm){
					$('.abschliessen').attr("disabled",false)
					$('.abschliessen').addClass("btn-primary");
					$('.abschliessen').removeClass("btn-secondary");
				} else {
					$('.abschliessen').attr("disabled",true)
					$('.abschliessen').addClass("btn-secondary");
					$('.abschliessen').removeClass("btn-primary");
				}
			} else { //Wenn das Passwortfeld leer ist, dann wird das Passwort nicht geändert
				$('.abschliessen').attr("disabled",false)
			}
		});
		
		$('.repwdcheck').keyup(function(){
			var pwd = ($('.pwdcheck').val());
			var repwd = ($(this).val());
			
			if (pwd === repwd) {
				$('.match').removeClass("text-danger");
				$('.match').addClass("text-success");
				var checkrem = true;
			} else {
				$('.match').addClass("text-danger");
				$('.match').removeClass("text-success");
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

		let suchgebiete_anzeigen = function(data){
			jQuery('.suchgebiete .card-body').empty();
			let gruppenliste = [];		
			if(data !== '' && data !== 'undefined' && data !== 'null'){
				let searcharea_json = JSON.parse(data);
				jQuery(searcharea_json.features).each(function(i,val){
					let areaType = val.properties.typ,
						name = val.properties.name,
						id = val.properties.id,
						color = val.properties.color,
						gruppe = val.properties.gruppe;	

					if(id != 'area51' && areaType != 'Übersichtskarte' && gruppe != 'e_group0'){
						gruppenliste.push('<a class="area_download" href="gpx/suchgebiet-gpx-download.php?SID='+id+'&name='+ areaType +'-'+gruppe+'&title='+ areaType +' '+ name +'" target="_blank" title="Suchgebiet Download"><i class="material-icons mr-1">cloud_download</i><span style="color:' + color + '">'+ name +': '+ areaType +'</span></a><br>');
					}
				});
				gruppenliste.forEach(function(entry){
					jQuery('.suchgebiete .card-body').append(entry);
				});
			}
		}
		
		database_call('settings','read','json','suchgebiete',{EID: aktive_eid},'','',suchgebiete_anzeigen,true);

		// Einsatz löschen

		$('body').on('click','.e-delete',function(e){
			confirm("Einsatz " + $(this).attr("data-ename") + " löschen?");
			e.preventDefault();
			let eid_num = $(this).attr("data-eid");
			$.ajax({
				url: wp+slsh+'cronjob.php',
				type: "post",
				scriptCharset: "utf-8" ,
				data: {
					EID: eid_num
				}
			}).done(function(data){
				$('#e'+eid_num+'-card').remove();
				location.reload();
			});
		});

		// Track importieren


		let Trackimport_Gruppen_anzeigen = function(data){console.log(data);
			var gruppenoptions = "<option value='' selected>Gruppe auswählen!</option>";
			jQuery(".modal.trackimport").modal('show');
			let gruppen = JSON.parse(data);
			jQuery.each(gruppen, function(i,val){
				if(val.typ !== 'material'){
					gruppenoptions += "<option value='"+val.id+"' style='color:"+val.color+"'>"+val.name+"</option>";
				}
				
			});
			jQuery("#gpxgruppe").html(gruppenoptions);
		}
		database_call('settings','read','json','gruppen',{EID: aktive_eid},'','',Trackimport_Gruppen_anzeigen,true);

		let GPS_Track_importieren = function(){
			if(jQuery("#gpxfile").val() && jQuery("#gpxgruppe").val()){
				let file_data = jQuery('#gpxfile').prop('files')[0],
					form_data = new FormData(),
					UID = jQuery("#gpxsender").val(),
					DNR = jQuery("#gpxsenderDNR").val(),
					OID = jQuery("#gpxsenderOID").val(),
					sender = jQuery("#gpxsendername").val();
				form_data.append("file", file_data);
				form_data.append("EID", aktive_eid);
				form_data.append("gpxsender", sender);
				form_data.append("gpxsenderUID", UID);
				form_data.append("gpxsenderOID", OID);
				form_data.append("gpxsenderDNR", DNR);
				form_data.append("gpxgruppe", jQuery("#gpxgruppe").val());
				
				jQuery.ajax({
					type: 'POST',          
					url: wp+slsh+"trackimport.php",
					dataType: 'script',
					cache: false,
					processData: false,
					contentType: false,
					data: form_data,
					beforeSend: function(){
						jQuery('.modal.feedback h5').html('Trackdaten werden geladen!');
						jQuery(".modal.feedback").modal("show");
					},
					complete: function(){
						jQuery('.modal.feedback h5').html('Trackdaten wurden hochgeladen!');
						jQuery(".modal.feedback").modal("show");
					},
					success: function () {
						jQuery('.modal.feedback h5').html('Track gespeichert!');
						jQuery(".modal.feedback").modal("show").delay("500");
					},
					error: function (error) {
						jQuery('.modal.feedback h5').html('Ein Fehler ist aufgetreten:'+error);
						jQuery(".modal.feedback").modal("show");
					}
				}).done(function(e){
						jQuery(".modal.feedback").modal("hide");
						jQuery(".modal.trackimport").modal("hide");
						database_call('settings','update','json_update','personen_im_einsatz',{EID: eid},UID,{sender: "active"},'',false);
				});
				return false;
			}else{
				jQuery("#gpxtext").addClass("error")
				jQuery("#gpxtext").html('<strong>Sender ausfüllen, Gruppe wählen oder File auswählen!</strong>');
			}
		}
		
		jQuery(document).on('submit','form#gpximporter',function(event){
			event.preventDefault();
			GPS_Track_importieren();
		});
	}
});
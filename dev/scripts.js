import jQuery from 'jquery';
import 'bootstrap';
import jQueryBridget from 'jquery-bridget';
import Masonry from 'masonry-layout';
import Map from 'ol/Map.js';
import View from 'ol/View.js';
import {unByKey} from 'ol/Observable.js';
import Overlay from 'ol/Overlay.js';
import {getArea, getLength} from 'ol/sphere.js';
import {Group as LayerGroup, Tile as TileLayer, Vector as VectorLayer} from 'ol/layer';
import {OSM as OSM, XYZ as XYZ, Vector as VectorSource} from 'ol/source';
import WMTS, {optionsFromCapabilities} from 'ol/source/WMTS.js';
import WMTSTileGrid from 'ol/tilegrid/WMTS.js';
import {fromLonLat as fromLonLat, get as getProjection} from 'ol/proj.js';
import {Select, Draw, Modify, Snap} from 'ol/interaction';
import {GeoJSON} from 'ol/format';
import MultiPoint from 'ol/geom/MultiPoint.js';
import WMTSCapabilities from 'ol/format/WMTSCapabilities.js';
import {Circle, GeometryType, LineString, Polygon, Point} from 'ol/geom.js';
import {circular as circularPolygon} from 'ol/geom/Polygon.js';
import {Circle as CircleStyle, Fill, Stroke, Style, Text, Icon} from 'ol/style';
import Graticule from 'ol/Graticule.js';
import {getWidth, getBottomLeft, getBottomRight, getTopLeft, getTopRight, getCenter} from 'ol/extent.js';
import Projection from 'ol/proj/Projection.js';
import {defaults as defaultControls, OverviewMap, ScaleLine} from 'ol/control';
import MousePosition from 'ol/control/MousePosition.js';
import {createStringXY,toStringXY} from 'ol/coordinate.js';
import BaseLayer from 'ol/layer/Base.js';
import {register} from 'ol/proj/proj4.js';
import proj4 from 'proj4';
import {DEVICE_PIXEL_RATIO} from 'ol/has.js';
import {getZoom} from 'ol/View.js';
import {addFeature, addFeatures} from 'ol/source/Vector.js';
import Feature from 'ol/Feature.js';
//import {xhr} from 'ol/featureloader';
//import {bbox} from 'ol/loadingstrategy';
import {getText, setText} from 'ol/style/Style.js';
import {click} from 'ol/events/condition.js';
import domtoimage from 'dom-to-image';

jQueryBridget( 'masonry', Masonry, jQuery );

var settings,tracktimer,timer,hash,anreise,adminname,fid,oid,oname,oshortname,Orgfid,Userfid,eid,uid,mapcenterX,mapcenterY,zoom,minpunkte,minspeed,maxspeed,trackpause,trackstart,einsatz,anfang,ende,eX,eY,newtrackloading,readposition,distance,updateinfo,HFquote,restrictedExtent,suchtyp,ppilat,ppilon,radien,perzentilen,typname,beschreibung,sec,root,read,readpath,writepath,ending,get,lead,Ogleich,Ozeichnen,Ozuweisen,Osehen,neuegruppeID,alertloading,gesuchtbild,gesuchtname,gesuchtbeschreibung,Orgfunction,ausbildungen;



// wenn anreisekarte dann gibt es einen Hashwert
if(window.location.hash) {
	hash = window.location.hash.substring(1);
	if(hash == "anreise"){
		anreise = true;
		jQuery(".navbar").addClass("d-none");
	}else{
		anreise = false;
	}
}

//Hash Links blocken
jQuery("a[href='#']").click(function(e){
	e.preventDefault();
});

// Tooltip 
jQuery('body').tooltip({
	selector: '.showtooltip',
	container: 'body',
	html: true
});

// Popover
jQuery('body .modal').popover({
	title: 'Gruppe zuweisen',
	placement: 'right',
	content: 'data-content',
	html: true,
	selector: '.openpopover'
});

var myDefaultWhiteList = jQuery.fn.popover.Constructor.Default.whiteList;
myDefaultWhiteList.i = ['data-group','data-gruppenID','data-old-color','data-color'];
myDefaultWhiteList.a = ['href','data-gruppenID','data-group','data-state','data-db','data-field','data-value'];

// Wert aus dem Sessionstorage holen
function getsessionStorage(item){
	return(sessionStorage.getItem(item));
}

// Wert in den Sessionstorage schreiben
function setsessionStorage(item,val){
		sessionStorage.setItem(item, val);
}

adminname = "";
Orgfid = 10;

root = "";
readpath = "api/";
writepath = "api/";
sec = "decrypt";
get = "?href";
ending = ".txt";
const wp = "api";
const slsh = "/";
const rwdb = "read_write_db.php";
const ui = "upload_image.php";


// db 				Database
// a				action (update, insert, read, delete)
// t 				type
// c 				table column
// s 				select, $skey => $sentry for update to find correct row
// val 				values, for sending single value
// json 			jsondata, key <> value pairs to send json data
// callback 		callback function after successfull ajax
// return_val 		return values from ajax 0 = no, 1 = yes

let database_call = function(db,a,t,c,s,val,json,callback,return_val,type){
	var jsonval = (val) ? val : '';
	var jsondata = (json) ? json : '';
	var select = (s) ? s : '';
	var type = (type) ? false : true;
	let database = {table: db,column: c,action: a,type: t};
	jQuery.ajax({
		url: root+wp+slsh+rwdb,
		type: "post",
		async: type,
		scriptCharset: "utf-8" ,
		data: {
			database: database,
			select: select,
			values: jsonval,
			json_nodes: jsondata,
			uid: uid
		}
	}).done(function(data){
		let value = (return_val) ? data : '';
		if(callback){
			callback(value)
		}else if(return_val){
			return data;
		}
	});
}

function messanger(text,type){
	jQuery(".modal."+type).modal('show').find("h5").html(text);
}
let googleapikey = '',
	aunit = '',
	afactor = '',
	lunit = '',
	lfactor = '',
	mapadmin = false,
	aktive_EID = false,
	strokewidth;
let Get_Session_vars = function(){
	jQuery.ajax({
		url: root+'api/get_session_in_js.php',
		type: 'post',
		success: function (data) {
			if(data != 'loggedout'){
				let etrax_session = JSON.parse(data);
				console.log(etrax_session);
				
				strokewidth = etrax_session.strokewidth;
				googleapikey = etrax_session.googleAPI;
				let Admineid = etrax_session.adminEID;
				eid = etrax_session.EID;
				uid = etrax_session.UID;
				oid = etrax_session.OID;
				fid = etrax_session.FID;
				aktive_EID = etrax_session.aktiveEID;
				oname = etrax_session.ORGname;
				oshortname = etrax_session.ORGnameshort;
				//Userfid = etrax_session.userrechte;
				aunit = etrax_session.aunit;
				afactor = etrax_session.afactor;
				lunit = etrax_session.lunit;
				lfactor = etrax_session.lfactor;
				Orgfunction = etrax_session.USER;
				mapadmin = etrax_session.mapadmin
				if("DEV" == oid){
					Orgfid = 0;
				}else if(Admineid == 0 || Admineid == eid){
					if(Orgfunction.einsatzleitung || Orgfunction.gleich){
						Orgfid = 1;
					}else if(Orgfunction.zeichnen){
						Orgfid = 2;
					}else if(Orgfunction.zuweisen){
						Orgfid = 3;
					}else if(Orgfunction.lesen){
						Orgfid = 4;
					}else{
						Orgfid = 5;
					}
					
				}else{
					Orgfid = 6;
				}
				if(mapadmin){
					database_call('settings','read','json','data',{EID: eid},'','',Get_all_Settings,true);
				}else{
					alert('Sie haben keine ausreichende Berechtigung für diese Seite. Sie werden weitergeleitet');
					window.location.href = './einsatzwahl.php';
				}
			}else{
				alert('Aus Sicherheitsgründen wurden sie nach 30 Minuten ohne Aktion automatisch ausgelogged. Sie werden auf die Startseite weitergeleitet');
				window.location.href = './index.php';
			}
		}
	});
}
Get_Session_vars();

aktive_EID ? window.location.href = 'index.php' : '';

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

jQuery('body').on('click','.navbar-toggler',function(){
	refresh_Session();
});

//(uid != '') ? '':window.location.href = 'index.php';

let Get_all_Settings = function(data){
	jQuery(function(){
		settings = JSON.parse(data);
		let obj = settings[0];console.log(obj);
		lead = obj.OID;
		Ogleich = obj.Ogleich;
		Ozeichnen = obj.Ozeichnen;
		Ozuweisen = obj.Ozuweisen;
		Osehen = obj.Osehen;
		mapcenterX = obj.centerlon;
		mapcenterY = obj.centerlat;
		zoom = obj.zoom;
		minpunkte = obj.minpunkte;
		minspeed = obj.minspeed;
		maxspeed = obj.maxspeed;
		trackpause = obj.trackpause;
		trackstart = obj.trackstart;
		einsatz = obj.einsatz;
		anfang = obj.anfang;
		ende = obj.ende;
		eX = obj.elon;
		eY = obj.elat;
		newtrackloading = obj.newtrackloading;
		readposition = obj.readposition;
		distance = obj.distance;
		updateinfo = obj.updateinfo;
		HFquote = obj.HFquote;
		restrictedExtent = obj.restrictedExtent;
		suchtyp = obj.suchtyp;
		ppilon = obj.ppilon;
		ppilat = obj.ppilat;
		alertloading = 10000;
		Kartenset_einlesen();
		viewoptions();
	});
}

//messanger("Settings werden eingelesen!","messanger");

function messangerhide(){
	jQuery("#messanger").modal('hide');
}
loader("show");

function loader(state){
	if(state == "show"){
		jQuery("#loader").show();
	}else{
		jQuery("#loader").hide();
		//messangerhide();
	}
}

let viewoptions = function(){console.log(Orgfunction.dev,Orgfunction.einsatzleitung,Orgfunction.gleich,Orgfunction.zeichnen,Orgfunction.zuweisen);
	if(!Orgfunction.dev && !Orgfunction.einsatzleitung && !Orgfunction.gleich){
		if(Orgfunction.zeichnen){
			jQuery('#mainNav .dropdown-item.set-EL, #mainNav .dropdown-item.set-ppi').remove();
		}else if(Orgfunction.zuweisen){
			jQuery('#mainNav .dropdown-item.set-EL, #mainNav .dropdown-item.set-ppi, #mainNav .dropdown-item.set-poi, #suchgebiete .groupselected, #suchgebiete .deletearea, #poiListing .deletePOI').remove();
		}else{
			jQuery('#mainNav .dropdown-item.set-EL, #mainNav .dropdown-item.set-ppi, #mainNav .dropdown-item.set-poi, #suchgebiete .groupselected, #suchgebiete .deletearea, #poiListing .deletePOI, #suchgebiete .suchgebiet-form, #mainNav .dropdown-item.tracksettings, #mainNav .dropdown-item.importtracks ,.newGroup, .modal.trackimport, .modal.tracksettings').remove();
		}
	}
}

let basiskarte,
	basiskarten = [],
	defaultmap,
	printMap,
	Basiskarten_einlesen = function(data){
	//Basiskarten aus der DB anzeigen
	let type,
		b_karte,
		karten_data = JSON.parse(data);
	jQuery(karten_data).each(function(i,val){
		if(val.type == "xyz"){
			type = new XYZ();
		}else if(val.type == "osm"){
			type = new OSM();
		}
		type.setAttributions(decodeURI(val.attributions));
		type.setUrl(val.url);
		b_karte = type;
		basiskarten[val.name] = type;
		if(i == 0){
			basiskarte = b_karte,
			defaultmap = val.name,
			printMap = val.printname;//Definiert den Namen der Basiskarte für den Ausdruck, wird durch die Kartenwahl geändert
		}
		jQuery('#mainNav .navbar-nav .basiskarten').append('<a href="#" class="dropdown-item changeMap" data-layer="'+ val.name +'" data-printlayer="'+ val.printname +'">'+ val.kartenname +'</a>');
	});
	Suchprofil_data();
}

let Kartenset_einlesen = function(){
	database_call('organisation','read','json','maps',{OID: oid},'','',Basiskarten_einlesen,true);
}


let Suchprofil_data = function(){
	if(suchtyp != ''){
		database_call('suchprofile','read','json','suchprofildata',{cid: suchtyp},'','',Suchprofile_einlesen,true);
	}else{
		var now = new Date().getTime();
		jQuery(".vermisst .modal-content").load("api/gesucht.php?t="+now, function(responseTxt, statusTxt, xhr){
			if(statusTxt == "success"){
				database_call('organisation','read','json','maps',{OID: oid},'','',showmap,false);
			}
		});
	}
}

let Suchprofile_einlesen = function(data){
	let suchprofil_data = JSON.parse(data);
	jQuery(suchprofil_data).each(function(i,val){console.log(val);
		radien = val.distanzen;
		perzentilen = val.wahrsch;
		typname = val.name;
		beschreibung = val.beschreibung;
	});
	var now = new Date().getTime();
	jQuery(".vermisst .modal-content").load("api/gesucht.php?t="+now, function(responseTxt, statusTxt, xhr){
		if(statusTxt == "success"){
			database_call('organisation','read','json','maps',{OID: oid},'','',showmap,false);
		}
	});
}

//Mitgliederliste
 let Mitgliederliste_anzeigen = function(data){
	let userdata = JSON.parse(data);
	//jQuery(userdata).each(function(id,userval){
		jQuery(userdata).each(function(i,value){
			let val = value[0],
				telnr = (val.telefon) ? ((val.telefon.startsWith("+43")) ? val.telefon.replace('+43', '') : val.telefon) : '',
				telefon = (val.telefon !== "") ? '<br>Tel: <a href="tel:+43'+telnr+'">+43'+telnr+'</a>' : '',
				email = (val.email !== "") ? '<br>E-mail: <a href="mailto:'+val.email+'">'+val.email+'</a>' : '',
				bos = (val.bos !== "") ? '<br><i>BOS: '+val.bos+'</i>' : '',
				notfallkontakt = (val.notfallkontakt !== '') ? '<br>Notfallkontakt: '+val.notfallkontakt : '',
				notfallinfo = (val.notfallinfo !== '' && (typeof val.notfallinfo !== 'undefined')) ? '<br>Notfallinfo: '+val.notfallinfo : '',
				kommentar = (val.kommentar !== '') ? '<br>Kommentar: '+val.kommentar : '';
			jQuery(".mitgliederliste #contactlist").append('<li class="formrow li'+val.UID+'"><b>'+val.typ+' '+val.dienstnummer+' '+val.name+'</b>'+telefon+email+bos+notfallkontakt+notfallinfo+kommentar+'</li>');
		});
	//});
}

jQuery(".nav-link.mitgliederliste").click(function(){
	database_call('user','read','json','data',{'OID': oid},'','',Mitgliederliste_anzeigen,true);
	jQuery(".modal.mitgliederliste").modal('show');
});


// Upload von Bildern
function uploadIMG(src, targetDiv){
	let file = src[0];
	if (file.type.match(/image.*/)) {
		let reader = new FileReader();
		reader.onload = function (readerEvent) {
			let image = new Image();
			image.src = readerEvent.target.result;
			image.onload = function (imageEvent) {

				// Add elemnt to page
				let imageElement = document.createElement('div');
				imageElement.classList.add('uploading');
				imageElement.innerHTML = '<span class="progress"><span></span></span>';
				let progressElement = imageElement.querySelector('span.progress span');
				progressElement.style.width = 0;
				//document.querySelector('form div.photos').appendChild(imageElement);

				// Resize image
				let mycanvas = document.createElement('canvas'),
					bigcanvas = document.createElement('canvas'),
					max_height = 400,
					max_width = 266,
					max_height_big = 800,
					max_width_big = 532,
					width = image.width,
					height = image.height;
				if(width > height){
					max_height = 266;
					max_width = 400;
					max_height_big = 532;
					max_width_big = 800;
				}
				mycanvas.width = max_width;
				mycanvas.height = max_height;
				mycanvas.getContext('2d').drawImage(image, 0, 0, max_width, max_height);

				let Pic = mycanvas.toDataURL("image/png");
				jQuery(targetDiv).html("<img src="+Pic+">");
				bigcanvas.width = max_width_big;
				bigcanvas.height = max_height_big;
				bigcanvas.getContext('2d').drawImage(image, 0, 0, max_width_big, max_height_big);
				let bigPic = bigcanvas.toDataURL("image/png");
				let sizing = [max_width_big, max_height_big];
				jQuery('#base64img').val(bigPic);
				jQuery('#base64size').val(sizing);
			};
		}
		reader.readAsDataURL(file);
	}
}

function showmap(){
	
	
// Schreibt die json Files neu und nach erfolgreichem Schreiben werden Suchgebiet- und Tracksourcen neu geladen mit Tracks_einlesen()

	let Tracks_einlesen = function(){
		jQuery.ajax({
			url: root+readpath+"readtracks.php",
			type: "post",
			data: {
				EID: eid,
				anfang: anfang,
				maxspeed: maxspeed,
				minspeed: minspeed,
				minpunkte: minpunkte,
				trackstart: trackstart,
				trackpause: trackpause,
				newtrackloading: newtrackloading
			}
		}).done(function(){
			(reloadTracks) ? reloadTracks(root+readpath+sec+'.php'+get+'=tracks&eid='+eid) : '';
		});
	}

	let Anreisetracks_einlesen = function(){
		jQuery.ajax({
			url: root+readpath+"readtracksanreise.php",
			type: "post",
			data: {
				EID: eid
			}
		});
	}
	let Trackjsons_neu_laden = function(){
		if(anreise){
			Anreisetracks_einlesen();
		}else{
			Tracks_einlesen();
		}
	}

	let Trackjsons_laden = function(){
		if(anreise){
			Anreisetracks_einlesen();
		}else{
			Tracks_einlesen();
		}
		loader("hide");
		tracktimer = setInterval(function(){ Trackjsons_neu_laden()},newtrackloading);//triggert den automatischen reload der json daten
	}
	Trackjsons_laden();


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

	//Timestamp
	let toTimestamp = function(strDate){
		var datum = Date.parse(strDate);
		return datum/1000;
	}
	
	// Auf Punkt zentrieren
	let centerTo = function(lon,lat){console.log(lon,lat);
		etraxmap.getView().setCenter(fromLonLat([parseFloat(lon),parseFloat(lat)]));
	}
	
	// Auf Area/Track zentrieren
	let centerOn = function(type,source){
		var extents = etraxmap.extent().boundingExtent(source)
		etraxmap.getView().fit(extents);
	}
	
	//Gruppeneinteilung und Personenzuweisung
	let Personen_im_Einsatz = function(){
		jQuery("#members").empty().html("");
		let datamembers = "";
		let memberOID = [];
		let orgname = [];
		let ausbildungen = "",komma = "";
		// List Personen im Einsatz erstellen
		let data = '';
		let Personen_im_Einsatz_data = function(data){
			let personen_data = JSON.parse(data);
			jQuery(personen_data).each(function(i,val){
				if(parseInt(val.status) >= 3 && parseInt(val.status) != 9 && parseInt(val.status) != 10 && !val.abgemeldet){
				//Ausbildung des Mitgliedes in das Ausbildungs-array pushen
					if(val.ausbildungen !== undefined && val.ausbildungen !== null && val.ausbildungen !== ''){
						ausbildungen = ausbildungen + komma + val.ausbildungen;
						komma = ";";
					}
					if(val.gruppe == 0){
						var musswarten = "";
						if(val.eingerueckt){
							var now = new Date().getTime();
							now = Math.round(now/1000);
							var waiting = (now - toTimestamp(val.eingerueckt))/60;
							if(waiting < (val.pause)){
								var colorhex = 60 - waiting;
								musswarten = "style='background:#cc0000"+Math.round(colorhex)+"; margin:4px 0; padding: 2px;'";
							}
						}
						let OID = val.OID;
						let UID = val.UID;
						let popover_bos = (val.bos) ? "BOS: "+val.bos+"<br>" : "";
						let popover_dienstnummer = (val.dienstnummer) ? "DNR: "+val.dienstnummer+"<br>" : "";
						let popover_info = (val.info) ? "Info: "+val.info : "";
						let popover_text = popover_bos + popover_dienstnummer + popover_info;
						let organisation = (val.orgname) ? " <span class='badge badge-info'>"+val.orgname+"</span>" : "";
						if (memberOID.indexOf(OID) === -1) {
							memberOID.push(OID);
							orgname[OID] = val.orgname;
						}
						if(Orgfunction.zeichnen){// lead
							//console.log("Lead");
							datamembers += "<div "+musswarten+" data-placement='right' title='"+popover_text+"' data-oid='"+OID+"' data-uid='"+UID+"' data-id='"+val.dienstnummer+"' data-name='"+val.name+"' data-typ='"+val.dienstnummer+"' data-info='"+val.info+"' data-ausbildung='"+val.ausbildungen+"' data-pause='"+val.pause+"' class='selectable d-block p-1 showtooltip org-"+OID+"'><i class='material-icons move'>check_box_outline_blank</i><i class='material-icons kommandant float-right'>star_border</i><i class='material-icons info float-right'>info</i><span class='badge badge-info'>"+val.typ+"</span> "+val.name+organisation+"</div>";
						}else if(Orgfunction.zuweisen){// Nur eigene OID
							//console.log("Nur eigene OID");
							datamembers += "<div "+musswarten+" data-placement='right' title='"+popover_text+"' data-oid='"+OID+"' data-uid='"+UID+"' data-id='"+val.dienstnummer+"' data-name='"+val.name+"' data-typ='"+val.dienstnummer+"' data-info='"+val.info+"' data-ausbildung='"+val.ausbildungen+"' data-pause='"+val.pause+"' class='selectable d-block p-1 org-"+OID+"'><i class='material-icons move'>check_box_outline_blank</i><i class='material-icons kommandant float-right'>star_border</i><span class='badge badge-info'>"+val.typ+"</span> "+val.name+organisation+"</div>";
						}else{
							//Nur lesen
							//console.log("Nur lesen");
							datamembers += "<div "+musswarten+" data-placement='right' title='"+popover_text+"' data-oid='"+OID+"' data-uid='"+UID+"' data-id='"+val.dienstnummer+"' data-name='"+val.name+"' data-typ='"+val.dienstnummer+"' data-info='"+val.info+"' data-ausbildung='"+val.ausbildungen+"' data-pause='"+val.pause+"' class='selectable d-block p-1 org-"+OID+"'><i class='material-icons kommandant float-right'>star_border</i><span class='badge badge-info'>"+val.typ+"</span> "+val.name+organisation+"</div>";
						}
					}
				}
			});
			let ausbildungen_array = ausbildungen.split(';');
			let uniqueAusbildungen = ausbildungen_array.filter((c, index) => {
				return ausbildungen_array.indexOf(c) === index;
			});	
			jQuery('#personenimeinsatz #memberList button').remove();
			if(uniqueAusbildungen[0] != ''){
				jQuery('#personenimeinsatz #memberList').append('<button type="button" class="btn btn-secondary ml-2 functionfilter float-right" data-ausbildung="blob">Auswahl löschen</button>');
			}
			jQuery(uniqueAusbildungen).each(function(i,val){
				if(val){
					jQuery('#personenimeinsatz #memberList').append('<button type="button" class="btn btn-primary ml-2 mr-2 functionfilter float-right" data-ausbildung="'+val+'">'+val+'</button>');
				}
			});
			memberOID.forEach(function(item) {
				jQuery("#members").prepend('<button class="orgfilter btn btn-success btn-sm mr-2" data-orgid="'+item+'">'+orgname[item]+'</button>');
			});
			jQuery("#members").append(datamembers);
			database_call('settings','read','json','gruppen',{EID: eid},'','',aktive_Gruppen_anzeigen,true);
		};
		database_call('settings','read','json','personen_im_einsatz',{EID: eid},'','',Personen_im_Einsatz_data,true);
	}
	jQuery('#mainNav').on('click','.personenimeinsatz',function(e){
		e.preventDefault();
		Personen_im_Einsatz();
	})
	
	let Gruppen_neu_laden = function(){
		database_call('settings','read','json','gruppen',{EID: eid},'','',aktive_Gruppen_anzeigen,true,true);
	}
	
	// Gruppeneinteilung
	let aktive_Gruppen_anzeigen = function(data){
		jQuery('.newGroup').show();
		let personen_einteilung,
			groupentry = "",
			sendet = "",
			ausbildungen = [],
			OID,
			gruppen = [];
		jQuery("#eGroups").empty();
		jQuery("#eGroups .groupmembers").empty();
		
		//Gruppen aus der DB anzeigen
		let gruppen_data = JSON.parse(data);
		jQuery(gruppen_data).each(function(i,val){
			if(val.aktuellerStatus != 'löschen'){
				let editable = "",
					setColor = "",
					gruppe = val.gruppe,
					commander = val.commander,
					gruppenID = val.id;
				OID = val.OID;
				if(gruppe == ""){
					gruppe = "Gruppe"+val.id;
				}
				let color = val.color;
				// Checkbox zum Person zuweisen nur wenn Gruppenstatus neu
				let addtoGroup = (val.aktuellerStatus == "neu") ? '<i class="material-icons add to-group" data-color="'+color+'">add_box</i>' : '';
				
				//Icons zum Farbeewchseln im Popup
				let setcolors =
					'<span><i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#007bff" class="c-007bff material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#6610f2" class="c-6610f2 material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#6f42c1" class="c-6f42c1 material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#e83e8c" class="c-e83e8c material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#dc3545" class="c-dc3545 material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#fd7e14" class="c-fd7e14 material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#ffc107" class="c-ffc107 material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#28a745" class="c-28a745 material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#20c997" class="c-20c997 material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#17a2b8" class="c-17a2b8 material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#4363d8" class="c-4363d8 material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#6c757d" class="c-6c757d material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#343a40" class="c-343a40 material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#000075" class="c-000075 material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#bfef45" class="c-bfef45 material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#f032e6" class="c-f032e6 material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#aaffc3" class="c-aaffc3 material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#e6beff" class="c-e6beff material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#ffe119" class="c-ffe119 material-icons colorswitch">add_box</i>'+
					'<i data-group="'+gruppe+'" data-old-color="'+color+'" data-color="#000000" class="c-000000 material-icons colorswitch">add_box</i></span>';
				
				let setStatus = 
					'<a href="#" data-group="'+gruppe+'" class="changegroupstate" data-state="neu">neu</a><br>'+
					'<a href="#" data-group="'+gruppe+'" class="changegroupstate" data-stateID="4" data-state="rückt aus">rückt aus</a><br>'+
					'<a href="#" data-group="'+gruppe+'" class="changegroupstate" data-stateID="5" data-state="beginnt Suche">beginnt Suche</a><br>'+
					'<a href="#" data-group="'+gruppe+'" class="changegroupstate" data-stateID="6" data-state="Suche beendet">Suche beendet</a><br>'+
					'<a href="#" data-group="'+gruppe+'" class="changegroupstate" data-stateID="7" data-state="wartet auf Transport">wartet auf Transport</a><br>'+
					'<a href="#" data-group="'+gruppe+'" class="changegroupstate" data-stateID="8" data-state="rückt ein">rückt ein</a><br>'+
					'<a href="#" data-group="'+gruppe+'" class="changegroupstate" data-state="zurück">zurück</a><br>'+
					'<a href="#" data-group="'+gruppe+'" class="changegroupstate" data-state="löschen">löschen</a>';
					
				//Je nach Berechtigung darf man Farbe Status und Namen der Gruppe ändern
				let farbeaendern = 'Farbe wählen <a href="#" class="closepopover"><i class="material-icons">highlight_off</i></a>';
				
				if(Orgfunction.einsatzleitung){
					editable = " contenteditable='true'";
					setColor = "<i class='color material-icons float-right openpopover' title='"+farbeaendern+"' data-content='"+setcolors+"' style='color:"+color+"'>colorize</i>";
				}else if(Orgfunction.zeichnen){//Orgfid == 3 && oid == OID
					editable = " contenteditable='true'";
					setColor = "<i class='color material-icons float-right openpopover' title='"+farbeaendern+"' data-content='"+setcolors+"' style='color:"+color+"'>colorize</i>";
				}else if(Orgfunction.zuweisen){//zuweisen & zeichnen
					//if(Orgfid == 3 && oid != OID){// Nur lesen
					editable = " contenteditable='false'";
					setColor = "";
					setStatus = "Du bist nicht berechtigt den Status der Gruppe zu ändern";
				}else{//Nur lesen || Userfid >= 4
					editable = " contenteditable='false'";
					setColor = "";
					setStatus = "Du bist nicht berechtigt den Status der Gruppe zu ändern";
				}

				let GroupStatusHeader = 'Gruppenstatus ändern <a href="#" class="closepopover"><i class="material-icons">highlight_off</i></a>';
				if(val.aktuellerStatus != "geloescht" && val.aktuellerStatus != "zurück"){
					groupentry += "<div id='"+gruppe+"' data-groupID='"+gruppenID+"' data-status='"+val.aktuellerStatus+"' data-zeit='"+val.zeit+"' data-oid='"+OID+"' data-color='"+color+"' style='color:"+color+";border-color:"+color+"!important' class='group align-self-start border rounded'>"+setColor+addtoGroup+"<span class='groupname font-weight-bold' title='Gruppenname ändern'"+editable+">"+val.name+"</span> <span title='Gruppenstatus ändern <a href=\"#\" class=\"closepopover float-right\"> <i class=\"material-icons\">highlight_off</i></a>' data-content='"+setStatus+"'  data-placement='bottom' class='openpopover font-weight-light status'><span class='badge badge-primary'>"+val.aktuellerStatus+"</span></span><div class='groupmembers'  data-groupID='"+gruppenID+"'></div></div>";
				}else if(val.aktuellerStatus == "zurück"){
					let membersback = "";
					jQuery.each(val.zugewiesen, function(i,memberdata){console.log(val.zugewiesen);
						membersback += "<div style='color:"+color+"' class='d-block p-1 back'><span class='badge badge-info'>"+memberdata.typ+"</span> "+memberdata.name+"</div>";
					});
					groupentry += "<div id='"+gruppe+"' data-groupID='"+gruppenID+"' data-status='"+val.aktuellerStatus+"' data-zeit='"+val.zeit+"' data-oid='"+OID+"' data-color='"+color+"' style='color:"+color+";border-color:"+color+"!important' class='group align-self-start border rounded'><span class='groupname font-weight-bold' data-toggle='collapse' href='#collapse-"+gruppenID+"' role='button' aria-expanded='false' aria-controls='collapse-"+gruppenID+"'>"+val.name+"</span> <span class='badge badge-primary'>"+val.aktuellerStatus+"</span><div class='groupmembers collapse' data-groupID='"+gruppenID+"' id='collapse-"+gruppenID+"'>"+membersback+"</div></div>";
				}

				//if(i+1 == gruppen.length){
				jQuery(".newGroup").attr("data-id",i + 2);
				jQuery("#eGroups").html(groupentry);
				//Personen aus der DB den Gruppen zuweisen
				//Alle Personen im Einsatz holen
				personen_einteilung = function(data) {
					let asignedMembers = [];
					let member = JSON.parse(data);
					// alle Gruppenmitglieder in ihre Gruppen schreiben
					jQuery.each(member, function(i,memberval){
						if(val.id == memberval.gruppe && val.aktuellerStatus != "zurück"){
							let Gruppe = jQuery('#eGroups #e_group-'+memberval.gruppe+' .groupmembers'),
								OID = memberval.OID,
								color = jQuery("#eGroups #e_group-"+memberval.gruppe).attr("data-color");

							sendet = "";
							
							// Gruppenkommandaten markieren
							let gruppencommander = (memberval.UID == commander) ?
								'<i style="color:'+color+'" class="material-icons kommandant incharge float-right">star</i>' :
								'<i style="color:'+color+'" class="material-icons kommandant float-right">star_border</i>';
							
							// Sender markieren
							if(memberval.sender == "active"){
								sendet = '<i style="color:'+color+'" class="material-icons float-right sending sendet">location_on</i>';
							}else if(memberval.sender == "inactive"){
								sendet = '<i style="color:'+color+'" class="material-icons float-right notsending sendet">location_off</i>';
							}
							let membercheckbox = (val.aktuellerStatus == "neu") ? '<i style="color:'+color+'" class="material-icons move">check_box_outline_blank</i> ' : '',
								popover_bos = (memberval.bos) ? "BOS: "+memberval.bos+"<br>" : "",
								popover_dienstnummer = (memberval.dienstnummer) ? "DNR: "+memberval.dienstnummer+"<br>" : "",
								popover_org = (memberval.orgname) ? "Info: "+memberval.orgname : "",
								popover_info = (memberval.info) ? "Info: "+memberval.info : "",
								popover_text = popover_bos + popover_dienstnummer + popover_org + popover_info,
								infolabel = '<i style="color:'+color+'" class="material-icons info float-right">info</i>';
							if(Orgfunction.einsatzleitung){
								// lead
								Gruppe.append("<div data-placement='left' title='"+popover_text+"' data-oid='"+OID+"' style='color:"+color+"' data-gid='"+val.id+"' data-uid='"+memberval.UID+"' data-id='"+memberval.dienstnummer+"' data-name='"+memberval.name+"' data-typ='"+memberval.typ+"' data-info='"+memberval.info+"' data-ausbildung='"+memberval.ausbildungen+"' class='selectable d-block showtooltip p-1'>"+infolabel+membercheckbox+gruppencommander+sendet+"<span class='badge badge-info'>"+memberval.typ+"</span> "+memberval.name+"</div>");
							}else if(Orgfunction.zeichnen){
								Gruppe.append("<div data-placement='top' title='"+popover_text+"' data-oid='"+OID+"' style='color:"+color+"' data-gid='"+val.id+"' data-uid='"+memberval.UID+"' data-id='"+memberval.dienstnummer+"' data-name='"+memberval.name+"' data-typ='"+memberval.typ+"' data-info='"+memberval.info+"' data-ausbildung='"+memberval.ausbildungen+"' class='selectable d-block showtooltip p-1'>"+infolabel+membercheckbox+gruppencommander+sendet+"<span class='badge badge-info'>"+memberval.typ+"</span> "+memberval.name+"</div>");
							}else if(Orgfunction.zuweisen){
								Gruppe.append("<div data-placement='top' title='"+popover_text+"' data-oid='"+OID+"' style='color:"+color+"' data-gid='"+val.id+"' data-uid='"+memberval.UID+"' data-id='"+memberval.dienstnummer+"' data-name='"+memberval.name+"' data-typ='"+memberval.typ+"' data-info='"+memberval.info+"' data-ausbildung='"+memberval.ausbildungen+"' class='selectable d-block showtooltip p-1'>"+infolabel+gruppencommander+sendet+memberval.dienstnummer+" "+memberval.name+"</div>");
							}else{
								Gruppe.append("<div data-placement='top' title='"+popover_text+"' data-oid='"+OID+"' style='color:"+color+"' class='selectable d-block showtooltip p-1'>"+infolabel+gruppencommander+sendet+memberval.dienstnummer+" "+memberval.name+"</div>");
							}
							
							let data = {"oid":memberval.OID,"uid":memberval.UID,"dienstnummer":memberval.dienstnummer,"typ":memberval.typ,"name":memberval.name,"info":memberval.info};
							asignedMembers.push(data);
						}
						
					});
						
					database_call('settings','update','json_update','gruppen',{EID: eid},''+val.id+'', {'zugewiesen': asignedMembers});
					gridContainer.masonry('reloadItems');
					gridContainer.masonry('layout');
				}
				database_call('settings','read','json','personen_im_einsatz',{EID: eid},'','',personen_einteilung,true);
			}
		});
		jQuery("#personenimeinsatz").modal("show");
	}
	// Gruppe zurück öffnen
	jQuery('#eGroups').on('click','.group[data-status="zurück"] .groupname',function(){
		jQuery(this).parent().toggleClass('ontop');
	});

	// Masonry bei Gruppeneinteilung
	var gridContainer = jQuery('#eGroups').masonry({
		itemSelector: '.group'
	});
	jQuery('#personenimeinsatz')
		.on('shown.bs.modal', function (e) {
			gridContainer.masonry();
		}).on('click','.move',function(){
		gridContainer.masonry('reloadItems');
		gridContainer.masonry('layout');
	});
	
	//Info zu Personen/Material hinzufügen
	jQuery(".memberList").on("click",".selectable .info",function(){
		if(Orgfunction.zuweisen){
			let info_txt = (jQuery(this).parent().attr('data-info') != '' && jQuery(this).parent().attr('data-info') != 'undefined') ? jQuery(this).parent().attr('data-info') : '';
			jQuery('.modal.userinfo').modal("show");
			jQuery('.modal.memberList').modal("hide");
			jQuery('.modal.userinfo .saveinfo').attr('data-UID',jQuery(this).parent().attr('data-uid'));
			jQuery(".modal.userinfo #userinfo").val(info_txt);
			jQuery(".modal.userinfo .modal-title").text('Informationen zu '+jQuery(this).parent().attr('data-name'));
		}
	});
	
	jQuery(".modal.userinfo").on("click",".btn",function(){
		let userID = jQuery(this).attr('data-uid');
		let info = jQuery(".modal.userinfo #userinfo").val();
		jQuery('.modal.userinfo').modal("hide");
		database_call('settings','update','json_update','personen_im_einsatz',{EID: eid},userID, {'info': info},Personen_im_Einsatz,false);
	});
	
	//Personen zum Gruppenkommandanten machen
	let Gruppenkommandanten_setzen = function(that){
		let userID = that.parent().attr("data-uid");
		let groupID = that.parent().attr("data-gid");
		database_call('settings','update','json_update','gruppen',{EID: eid},groupID, {'commander': userID});
		Personen_im_Einsatz();
	};
	
	jQuery(".memberList").on("click",".selectable .kommandant",function(){
		if(Orgfunction.zuweisen){
			Gruppenkommandanten_setzen(jQuery(this));
		}
	});
	
	//Personen als sender ein/ausblenden
	let Sender_setzen = function(that){
		let userID = that.parent().attr('data-uid');
		let is_sender = (that.hasClass('sending')) ? 'inactive' : 'active';
		database_call('settings','update','json_update','personen_im_einsatz',{EID: eid},userID, {'sender': is_sender});
		Tracks_einlesen();
		Personen_im_Einsatz();
	};
	
	jQuery(".memberList").on("click",".selectable .sendet",function(){
		if(Orgfunction.zuweisen){
			Sender_setzen(jQuery(this));
		}
	});
	
	//Personen nach Funktion Filtern
	let Funktion_filtern = function(that){
		let funktion = that.attr('data-ausbildung');
		jQuery('#personenimeinsatz .selectable').each(function(){
			let gesucht = jQuery(this).attr('data-ausbildung');
			(gesucht.indexOf(funktion) >= 0) ? jQuery(this).addClass('bg-success text-white') : jQuery(this).removeClass('bg-success text-white');
		});
	};
	
	jQuery("#personenimeinsatz").on("click",".functionfilter",function(){
		Funktion_filtern(jQuery(this));
	});
	
	//Organisationen anzeigen
	let Org_filtern = function(that){
		let orgid = that.attr('data-orgid');
		jQuery('#mList #members [data-oid="'+orgid+'"]').each(function(){
			(that.hasClass('btn-success')) ? jQuery(this).removeClass('d-block').addClass('d-none') : jQuery(this).addClass('d-block').removeClass('d-none');
		});
		that.hasClass('btn-success') ? that.removeClass('btn-success').addClass('btn-danger') : that.removeClass('btn-danger').addClass('btn-success');
	};
	
	jQuery("#members").on("click",".orgfilter",function(){
		Org_filtern(jQuery(this));
	});
	
	let neue_Gruppe_anlegen = function(that){
		let t = Math.floor(Date.now()/1000);
		let gruppennr = uid+'-'+t;
		let gruppenID = (that.attr("data-id")) ? that.attr("data-id") : Math.floor(Math.random() * 11);
		let gruppenname = ' '+oshortname+' '+ gruppenID;
		
		let json_nodes = 
		{
			'id':gruppennr,
			'OID':oid,
			'gruppe':'e_group-'+gruppennr,
			'name':'Gruppe'+gruppenname,
			'color':'#000000',
			'sender':'',
			'commander':'',
			'aktuellerStatus':'neu',
			'status':'',
			'zeit': getcurrentTime(),
			'zugewiesen':''
		}
		database_call('settings','update','json_append','gruppen',{EID: eid},gruppennr, json_nodes,Gruppen_neu_laden,false);
	}
	jQuery('#personenimeinsatz').on('click','.newGroup',function(e){
		jQuery(this).hide();
		neue_Gruppe_anlegen(jQuery(this));
	});
	
	let person_fuer_gruppenzuweisung_aktivieren = function(that){
	//Personen wählen um sie zuzuweisen
		jQuery(".newGroup i").hide();
		clearTimeout(timer);
		if(that.hasClass("selected")){
			that.removeClass("selected").find(".move").html("check_box_outline_blank");
		}else{
			that.addClass("selected").find(".move").html("check_box");
		}
		if(jQuery("#personenimeinsatz .selected").length > 0){
			jQuery("#personenimeinsatz .add").show();
		}else{
			jQuery("#personenimeinsatz .add").hide();
		}
	}
	jQuery(".memberList").on("click",".selectable .move",function(){
		person_fuer_gruppenzuweisung_aktivieren(jQuery(this).parent());
	});

	//Personen in die Gruppe laden
	let Person_Gruppe_zuweisen = function(data){
		let t = Math.floor(Date.now()/1000);
		let username = [];
		let usertime = [];
		let personen_data = JSON.parse(data);
		jQuery(personen_data).each(function(i,val){
			username[val.UID] = (val.UID);
			usertime[val.UID] = (val.zugewiesen);
		});
		jQuery(".newGroup i").show();
		jQuery("#personenimeinsatz").modal("handleUpdate");
		let add = (jQuery('.add.group-selected') != '' || jQuery('.add.group-selected').length > 0) ? jQuery('.add.group-selected') : false;
		let newGroupID,oldGroupID;
		let user_name = '',separator = '';
		jQuery('.add.group-selected').removeClass('group-selected');
		jQuery('#eGroups .group .add, #imEinsatz .add').hide();
		console.log(add);
		//Loop über ausgewählte Personen
		jQuery("#personenimeinsatz .selectable.selected")
			.each(function(){console.log(jQuery(this).attr('data-uid'));
				if(add.hasClass('to-group')){
					oldGroupID = jQuery(this).parent().attr('data-groupid');
					newGroupID = add.parent().attr('data-groupid');
				}else{
					oldGroupID = jQuery(this).parent().attr('data-groupid');
					newGroupID = '0';
				}
				console.log(oldGroupID,newGroupID);
				if(oldGroupID != newGroupID){
					console.log('Time: '+ sessionStorage.getItem('groupmodal_show') + ' > ' +  usertime[jQuery(this).attr('data-uid')]);
					if(!usertime[jQuery(this).attr('data-uid')] || sessionStorage.getItem('groupmodal_show') > usertime[jQuery(this).attr('data-uid')]){
						database_call('settings','update','json_update','personen_im_einsatz',{EID: eid},jQuery(this).attr('data-uid'),{'gruppe': ''+newGroupID+''},'',false,true);
						database_call('settings','update','json_update','personen_im_einsatz',{EID: eid},jQuery(this).attr('data-uid'),{'zugewiesen': ''+t+''},'',false,true);
					}else{
						user_name += separator+jQuery(this).attr('data-name');
					}
				}
				newGroupID = oldGroupID = '';
				separator = ', '
			})
			.promise()
			.done(function(){
				if(user_name != ''){
					jQuery('.modal.messanger').modal('show').find('#messangertitle').text(user_name+' wurden bereits zugewiesen, wenn nötig ist eine erneute Zuweisung jetzt möglich');
					//alert(user_name+' wurden bereits zugewiesen!');
					sessionStorage.setItem('groupmodal_show', Math.floor(Date.now()/1000));
				}
				Personen_im_Einsatz();
			}
		);
	}

	// Gruppen-modal öffnen
	jQuery('#personenimeinsatz').on('show.bs.modal', function (e) {
		sessionStorage.setItem('groupmodal_show', Math.floor(Date.now()/1000));
	});

	// Gruppen-modal schließen
	jQuery('#personenimeinsatz').on('hide.bs.modal', function (e) {
		sessionStorage.setItem('groupmodal_show', '');
	});

	// Personen einer Gruppe zuweisen
	jQuery('#personenimeinsatz').on('click','.add',function(){
		jQuery(this).addClass('group-selected');
		database_call('settings','read','json','personen_im_einsatz',{EID: eid},'','',Person_Gruppe_zuweisen,true);
	});
	
	
	//Gruppennamen ändern
	let Gruppe_umbenennen = function(that){
		database_call('settings','update','json_update','gruppen',{EID: eid},that.parent().attr('data-groupid'), {'name': ''+that.text()+''},Gruppen_neu_laden,false);
		database_call('settings','update','json_replace','suchgebiete',{EID: eid},that.parent().attr('id'), {'name': ''+that.text()+''},Suchgebiete_anzeigen,false);
	}
	
	jQuery('body').on('focusout','.groupname',function(){
		Gruppe_umbenennen(jQuery(this));
	});
  
	//Gruppenfarbe setzen
	let Gruppefarbe_setzten = function(that){
		jQuery('i.active').removeClass('active').html('add_box');
		that.addClass('active').html('check_box');
		let gruppenID = that.attr('data-group').replace('e_group-', '');
		jQuery('.popover').popover('hide')
		database_call('settings','update','json_update','gruppen',{EID: eid},gruppenID, {'color': ''+that.attr("data-color")+''},Gruppen_neu_laden,false);
		database_call('settings','update','json_replace','suchgebiete',{EID: eid},that.attr('data-group'), {'fillcolor': ''+that.attr("data-color")+'33','color': ''+that.attr("data-color")+'','strokecolor': ''+that.attr("data-color")+''},Suchgebiete_anzeigen,false);
	};
	
	jQuery('body').on('click','.colorswitch',function(){
		Gruppefarbe_setzten(jQuery(this));
	});
	
	//Gruppenstatus ändern
		
	let Gruppenstatus_setzten = function(that){
		let time = getcurrentTime();
		let newStatus = that.attr('data-state');
		let gruppenID = that.attr('data-group').replace('e_group-', '');
		let group_status = newStatus+'&&'+time+';';
		jQuery('.popover').popover('hide');
		
		let Gruppen_Status_history = function(data){
			let groupdata = JSON.parse(data);
			
			jQuery.each(groupdata, function(i,val){
				if(val.id == gruppenID){
					let status_history = (val.status != '') ? val.status : '';
					
					status_history = status_history + group_status;
					database_call('settings','update','json_update','gruppen',{EID: eid},''+val.id+'', {'status': status_history},Gruppen_neu_laden,false,false);
				}
			});
		}
		
		let Gruppen_History = function(){
			database_call('settings','read','json','gruppen',{EID: eid},gruppenID,'',Gruppen_Status_history,true);
			database_call('settings','update','json_replace','suchgebiete',{EID: eid},that.attr('data-group'), {'status': that.attr('data-state')},Suchgebiete_anzeigen,false,false);
		}
		
		if(newStatus == 'löschen'){
			let gruppen_user_count = 0;
			jQuery('#e_group-' + gruppenID + ' .groupmembers .selectable').each(function(){
				jQuery(this).addClass('selected');
			});
			gruppen_user_count = jQuery('#e_group-' + gruppenID + ' .groupmembers .selectable').length;console.log(gruppen_user_count);
			gruppen_user_count > 0 ? database_call('settings','read','json','personen_im_einsatz',{EID: eid},'','',Person_Gruppe_zuweisen,true) : Personen_im_Einsatz();
		}else if(newStatus != 'zurück' && newStatus != 'neu'){
			let gruppen_user_count = 0,
				asignedMembers = [];
			jQuery('#e_group-' + gruppenID + ' .groupmembers .selectable').each(function(){
				let data = {"oid":jQuery(this).attr('data-oid'),"uid":jQuery(this).attr('data-uid'),"dienstnummer":jQuery(this).attr('data-id'),"typ":jQuery(this).attr('data-typ'),"name":jQuery(this).attr('data-name'),"info":jQuery(this).attr('data-info')};
				asignedMembers.push(data);
			});
			database_call('settings','update','json_update','gruppen',{EID: eid},''+gruppenID+'', {'zugewiesen': asignedMembers});
		}else if(newStatus == 'zurück'){
			let gruppen_user_count = 0,
				asignedMembers = [];
			jQuery('#e_group-' + gruppenID + ' .groupmembers .selectable').each(function(){
				jQuery(this).addClass('selected');
				let data = {"oid":jQuery(this).attr('data-oid'),"uid":jQuery(this).attr('data-uid'),"dienstnummer":jQuery(this).attr('data-id'),"typ":jQuery(this).attr('data-typ'),"name":jQuery(this).attr('data-name'),"info":jQuery(this).attr('data-info')};
				asignedMembers.push(data);
			});
			database_call('settings','update','json_update','gruppen',{EID: eid},''+gruppenID+'', {'zugewiesen': asignedMembers});
			gruppen_user_count = jQuery('#e_group-' + gruppenID + ' .groupmembers .selectable').length;console.log(gruppen_user_count);
			gruppen_user_count > 0 ? database_call('settings','read','json','personen_im_einsatz',{EID: eid},'','',Person_Gruppe_zuweisen,true) : Personen_im_Einsatz();
		}
		
		database_call('settings','update','json_update','gruppen',{EID: eid},gruppenID, {'aktuellerStatus': newStatus},Gruppen_History,false);
	}
	
	jQuery('body').on('click','.changegroupstate',function(e){
		let status = jQuery(this).attr('data-state');
		if(status == "zurück"){
			if(window.confirm('Gruppenstatus auf "zurück" ändern?/nKeine weiteren Änderungen mehr möglich!')){
				Gruppenstatus_setzten(jQuery(this));
			}
		}else if(status == "löschen"){
			if(window.confirm('Gruppe löschen?')){
				Gruppenstatus_setzten(jQuery(this));
			}
		}else {
			Gruppenstatus_setzten(jQuery(this));
		}
	});


	let toUTM = function(coords){
		//Ausgabe UTM33U Koordinaten
		var XY = setnewProjection("EPSG:3857","ETRS89",coords);
		return [Math.round(XY[0]), Math.round(XY[1])];
	}
	let toDMG = function(coords){
		//Ausgabe Dezimalgrad Koordinaten
		var XY = setnewProjection("EPSG:3857","WGS84",coords);
		return [XY[0],XY[1]];
	}
	let UTMtoDMG = function(coords){
		//Ausgabe Dezimalgrad Koordinaten
		var XY = setnewProjection("ETRS89","WGS84",coords);
		return [XY[0],XY[1]];
	}
	let DMGtoUTM = function(coords){
		//Ausgabe Dezimalgrad Koordinaten
		var XY = setnewProjection("WGS84","ETRS89",coords);
		return [XY[0],XY[1]];
	}

	let fromUTM = function(coords){
		//Ausgabe aus UTM33U Koordinaten
		var XY = setnewProjection("ETRS89","EPSG:3857",coords);
		return [Math.round(XY[0]), Math.round(XY[1])];
	}
	let fromDMG = function(coords){
		//Ausgabe aus Dezimalgrad Koordinaten
		var XY = setnewProjection("WGS84","EPSG:3857",coords);
		return [XY[0],XY[1]];
	}
	
	// POI setzten 
	function Koordinaten_umrechnen(lon,lat){
		//Eingabe UTM33U Koordinaten
		var utmXY = setnewProjection("WGS84","ETRS89",[lon,lat]);
		jQuery("#poilonutm").val(Math.round(utmXY[0]));
		jQuery("#poilatutm").val(Math.round(utmXY[1]));
		
		var utmX = lon.toString().split(".");
		var utmY = lat.toString().split(".");
		//Dezimalgrad Koordinaten
		//X
		jQuery("#poilond1").val(utmX[0]);
		jQuery("#poilond2").val(utmX[1]);
		//Y
		jQuery("#poilatd1").val(utmY[0]);
		jQuery("#poilatd2").val(utmY[1]);
	}
	
	if(getsessionStorage("mapcenterX") && getsessionStorage("mapcenterY") && getsessionStorage("mapcenterX") != "0" && getsessionStorage("mapcenterY") != "0"){
		mapcenterX = getsessionStorage("mapcenterX");
		mapcenterY = getsessionStorage("mapcenterY");
	}else{
		setsessionStorage("mapcenterX", 15.478);
		setsessionStorage("mapcenterY", 48.8554);
	}
	if(sessionStorage.getItem("zoom")){
		zoom = getsessionStorage("zoom");
	}else{
		zoom = 14
		setsessionStorage("zoom", 14);
	}
		  
	//UTM Definitionen - Automatisch für alle Zonen N - kann man zwischen -180 und 180 festlegen --> alle. Oder z.B. -6 und + 24
	var utm_temp,x;
	for (x = -6; x <= 24; x += 6) { //6 steht für die 6 Grad Sprünge im UTM Gitter
		//UTM Namen für proj4 Definition
		utm_temp = (x/6+31);
		proj4.defs("UTM"+utm_temp,"+proj=utm +zone="+utm_temp+" +ellps=WGS84 +datum=WGS84 +units=m +no_defs");
	}
		  
	proj4.defs("WGS84","+proj=longlat +datum=WGS84 +no_defs");
	proj4.defs("ETRS89","+proj=utm +zone=33 +ellps=GRS80 +towgs84=0,0,0,0,0,0,0 +units=m +no_defs");
	register(proj4);
	/*
	var UTM32coords = new Projection({
			code: "EPSG:32632",
			extent: [1599000,5956000,1918000,6281000]
		  });*/
	var projETRS89 = getProjection("ETRS89");
		projETRS89.setExtent([1599000,5956000,1918000,6281000]);
	/*var WGS84coords = new Projection({
			code: "EPSG:4326",
			extent: [1599000,5956000,1918000,6281000]
		  });
	*/

	var setnewProjection = function (fromProjection,toProjection,coordinates){
		return proj4(fromProjection, toProjection, coordinates);
	}
		

	var mousePositionControl = new MousePosition({
		coordinateFormat: createStringXY(0),
		projection: "ETRS89",
		className: 'mouse-position',
		target: document.getElementById('mouse-position'),
		undefinedHTML: '&nbsp;'
	});
	

	let base = new TileLayer({
		source: basiskarte,
		isBaseLayer: true,
		visible: true
	});
	// Kartentyp wechseln wenn eine andere Kartenansicht gewählt wurde
	if(sessionStorage.getItem('maptype') != null ||sessionStorage.getItem('maptype') != undefined ){
		base.setSource(basiskarten[sessionStorage.getItem('maptype')]);
	}else{
		sessionStorage.setItem('maptype', defaultmap);
	}

	let Karte_wechseln = function(that){
		let map_type = that.attr("data-layer");
		sessionStorage.setItem('maptype', map_type);
		printMap = that.attr("data-printlayer");
		base.setSource(basiskarten[map_type]);
	}
	
	jQuery('#mainNav').on('click','.changeMap',function(e){
		e.preventDefault();
		Karte_wechseln(jQuery(this));
	});

	var source = new VectorSource();

	var vector = new VectorLayer({
		source: source,
		projection: "ETRS89",
		style: new Style({
			fill: new Fill({
				color: 'rgba(255, 255, 255, 0.2)'
			}),
				stroke: new Stroke({
				color: '#ffcc33',
				width: 2
			}),
				image: new CircleStyle({
				radius: 7,
				fill: new Fill({
					color: '#ffcc33'
				})
			})
		})
	});
	
	// PIOs einfügen
	let newPOIs_styles = function(feature) {
		let imgsrc = (feature.get('name') == 'Personenfund') ? 'img/fund.png' : 'img/pin.png';
		let poisstyle = [new Style({
				image: new Icon({
					anchor: [10,20],
					anchorXUnits: 'pixels',
					anchorYUnits: 'pixels',
					color: feature.get('color'),
					src: imgsrc
				}),
				zIndex: 15
			})
			];
		return poisstyle;
	};
	let poissource = new VectorSource({
		url: root+wp+slsh+rwdb+'?type=json&action=read&table=settings&column=pois&EID='+eid+'&json_nodes&values',
		format: new GeoJSON()
	});
	let pois = new VectorLayer({
		source: poissource,
		name: "poi",
		style: newPOIs_styles
	});

	// Einfügen der Suchgebiete als json
	let Searchareas = function(feature){
		let s_color = feature.get('strokecolor');
		let f_color = feature.get('fillcolor');
		let areastyle = '';
		if(feature.get('typ') == 'Punktsuche'){
			areastyle = [
				new Style({
					image: new Icon({
						anchor: [10,20],
						anchorXUnits: 'pixels',
						anchorYUnits: 'pixels',
						color: f_color,
						src: 'img/pointsearch.png'
					}),
					zIndex: 9
				})
			];
		}else{
			areastyle = [
				new Style({
					stroke: new Stroke({
						color:s_color,
						lineDash: [feature.get('lineDash')],
						width: feature.get('strokewidth')
					}),
					fill: new Fill({
						color:f_color
					}),
					zIndex: 9
				})
			];
		}
		if(feature.get('typ') == 'Übersichtskarte' || feature.get('typ') =='Mantrailer'){
			areastyle = '';
		}
		return areastyle;
	};
	let searchareasource = new VectorSource({
		url: root+wp+slsh+rwdb+'?type=json&action=read&table=settings&column=suchgebiete&EID='+eid+'&json_nodes&values',
		format: new GeoJSON()
	});
	
	let searcharea = new VectorLayer({
		declutter: true,
		name: "searcharea",
		projection: "ETRS89",
		source: searchareasource,
		style: Searchareas
	});
	
	// Einfügen der Usertracks als json
	let usertracks = function(feature) {
		let coordinates = feature.getGeometry().getCoordinates()[0];
		let len = coordinates.length;
		if (len > 0) {
			coordinates = coordinates.slice(0,1);
		}
		let tstyles;
		if(feature.get('strokewidth') != 0){
			tstyles = [
				new Style({
					stroke:new Stroke({
						width: feature.get('strokewidth'),
						color: feature.get('strokecolor')
					})
				}),
				new Style({
					image: new Icon({
						anchor: [0.5,25],
						anchorXUnits: 'fraction',
						anchorYUnits: 'pixels',
						color: feature.get('strokecolor'),
						src: feature.get('img'),
						opacity: feature.get('opacity')
					}),
					geometry: function() {
						return new MultiPoint(coordinates);
					}
				})
			];
		}else{
			tstyles = [
				new Style({
					image: new Icon({
						anchor: [0.5,25],
						anchorXUnits: 'fraction',
						anchorYUnits: 'pixels',
						color: feature.get('strokecolor'),
						imgSize: [20, 20],
						src: feature.get('img'),
						opacity: feature.get('opacity')
					}),
					geometry: function() {
						return new MultiPoint(coordinates);
					}
				})
			];
		}
		auf_Track_zentrieren(feature);
		return tstyles;
	};
	
	let reloadTracks = function(jsonUrl){
		jQuery('#loader').show();
		jQuery.ajax({
			url: jsonUrl
		}).done(function() {
			let trackssource = new VectorSource({
				url: jsonUrl,
				format: new GeoJSON()
			});
			tracks.setSource(trackssource);
			jQuery('#loader').hide();
		});
		
	};
	/*let loadTracks = function(){console.log('trackssource');
		let trackssource = new VectorSource({
			format: new GeoJSON(),
			loader: function(extent, resolution, projection){
				let jsonUrl = root+readpath+sec+'.php'+get+'=tracks&eid='+eid;
				let xhr = new XMLHttpRequest();
				xhr.open('GET', jsonUrl);
				let onError = function() {
					console.log(xhr.status);
				};
				xhr.onerror = onError;
				xhr.onload = function() {
					if (xhr.status == 200) {
						jQuery('#loader').show();
						let newFeatures = new GeoJSON().readFeatures(xhr.responseText);
						newFeatures.each(function( index ) {console.log($(this));
							$(this).setStyle(usertracks($(this)));
						});
						trackssource.addFeatures(newFeatures);console.log(trackssource);
						tracks.setSource(trackssource);
						jQuery('#loader').hide();
					}else{
						onError();
					}
				};
				xhr.send();
			}
		});
	};*/
	
	let tracks = new VectorLayer({
		declutter: true,
		name: 'usertrack',
		projection: "ETRS89",
		zIndex: 13,
		style: usertracks
	});
	//loadTracks();
	let reloadTrackstimer = setInterval(reloadTracks(root+readpath+sec+'.php'+get+'=tracks&eid='+eid), newtrackloading);

	// Zentrieren auf Usertracks
	let Usertrack_array = [];
	if(sessionStorage.getItem("alltrackshidden") === null){
		sessionStorage.setItem("alltrackshidden","");
	}
	if(sessionStorage.getItem("hiddentracks") === null){
		sessionStorage.setItem("hiddentracks","");
	}
	let auf_Track_zentrieren = function(feature){
		let alltrackshidden = sessionStorage.getItem("alltrackshidden");
		let hiddentracks = sessionStorage.getItem("hiddentracks");
		let track_nr = feature.get('tracknr');
		let trackname = feature.get('id');
		let trackername = feature.get('name');
		let groupID = feature.get('gid');
		let userID = feature.get('uid');
		if(!Usertrack_array.includes(track_nr)){
			let checked = (!hiddentracks.includes(track_nr)) ? 'check_box' : 'check_box_outline_blank';
			let tooltip = (!hiddentracks.includes(track_nr)) ? 'Track ausblenden' : 'Track einblenden';
			let show = (!hiddentracks.includes(track_nr)) ? 'show' : '';
			let all_checked = (!alltrackshidden.includes('headerid'+groupID+'_'+userID)) ? 'check_box' : 'check_box_outline_blank';
			let all_tooltip = (!alltrackshidden.includes('headerid'+groupID+'_'+userID)) ? 'Track ausblenden' : 'Track einblenden';
			let all_show = (!alltrackshidden.includes('headerid'+groupID+'_'+userID)) ? 'show' : '';
			Usertrack_array.push(track_nr);
			if(jQuery("#tracklist .singletrack."+groupID+"."+userID).length == 0){
				jQuery("#tracklist").append('<button class="btn btn-light w-100 d-flex mt-1" type="button" data-toggle="collapse" data-target="#collapse_'+groupID+'_'+userID+'" aria-expanded="false" aria-controls="collapse_'+groupID+'_'+userID+'"><a href="javascript:;" id="headerid'+groupID+'_'+userID+'" class="flex-shrink-1 showtooltip hiding_all '+all_show+'" data-original-title="'+all_tooltip+'" data-collapsid="#collapse_'+groupID+'_'+userID+'" data-headerid="#headerid_'+groupID+'_'+userID+'"><i class="material-icons">'+all_checked+'</i></a><span class="w-100 text-left">Track '+trackname+' von '+trackername+'</span></button><div class="collapse" id="collapse_'+groupID+'_'+userID+'"></div>');
			}
			jQuery("#tracklist #collapse_"+groupID+"_"+userID).append("<div class='singletrack "+groupID+" "+userID+"'><a href='javascript:;' class='showtooltip hiding "+show+"' data-original-title='"+tooltip+"' data-id='"+track_nr+"' data-gid='"+groupID+"' data-uid='"+userID+"'><i class='material-icons'>"+checked+"</i></a> <a href='javascript:;' class='centertrack showtooltip' data-original-title='auf Track zentrieren' data-id='"+trackname+"' data-tlon='"+feature.get('tracklon')+"' data-tlat='"+feature.get('tracklat')+"'><i class='material-icons zoomin'>zoom_in</i></a>Track "+track_nr+" "+trackname+" von "+trackername+" um "+feature.get('time')+"<div>");
		}
	}
		
	jQuery("#tracklist").on("click",".centertrack",function(){
			etraxmap.getView().setCenter(fromLonLat([parseFloat(jQuery(this).attr("data-tlon")),parseFloat(jQuery(this).attr("data-tlat"))]));
			jQuery(".modal.usertracks").modal('hide');
	});
	// Ende

	// Einfügen der Anreisetracks als json
	let anreisetracktext = new Text({
		font: 'bold 14px "Open Sans", "Arial Unicode MS", "sans-serif"',
		placement: 'line',
		textBaseline: 'hanging',
		fill: new Fill({
			color: '#000'
		}),
		stroke: new Stroke({
			color: '#fff',
			width: 2
		})
	});

	/*var anreisetrackfill = new Fill();

	var anreisetrackstroke = new Stroke({
		width: 2
	});
	var anreisetrackstyles = new Style({
			text: anreisetracktext,
			stroke: anreisetrackstroke
		});*/
	let newanreisetrackstyles = function(feature) {
		var coordinates = feature.getGeometry().getCoordinates()[0];
		var len = coordinates.length;
		if (len > 0) {
			var new_coordinates = coordinates.slice(0,1);
		}
		var anreisetrackstyles = [new Style({
				stroke:new Stroke({
					width: 2,
					color: feature.get('strokecolor')
				}),
			}),
			new Style({
				image: new Icon({
					anchor: [9,18],
					anchorXUnits: 'pixels',
					anchorYUnits: 'pixels',
					color: feature.get('strokecolor'),
					src: 'img/gk.png'
				}),
				geometry: function() {
					return new MultiPoint(new_coordinates);
				}
			})
		];
		return anreisetrackstyles;
	};

	var anreisetrackssource = new VectorSource({
		url: root+readpath+sec+'.php'+get+'=anreisetracks&eid='+eid,
		format: new GeoJSON()
	});

	var anreisetracks = new VectorLayer({
		declutter: true,
		name: "anreisetracks",
		projection: "ETRS89",
		zIndex: 13,
		source: anreisetrackssource,
		style: newanreisetrackstyles
	});

	let reloadanreiseTracks = function(){
		anreisetracks.setSource(new VectorSource({
			url: root+readpath+sec+'.php'+get+'=anreisetracks&eid='+eid,
			format: new GeoJSON()
		}));
	};
	let reloadanreiseTrackstimer = setInterval(reloadanreiseTracks, newtrackloading);

	// Neu Laden der json Files
	let POIs_anzeigen = function(){
		pois.setSource(new VectorSource({
			url: root+wp+slsh+rwdb+'?type=json&action=read&table=settings&column=pois&EID='+eid+'&json_nodes&values',
			format: new GeoJSON()
		}));
	};
	
	let Suchgebiete_anzeigen = function(){
		searcharea.setSource(new VectorSource({
			url: root+wp+slsh+rwdb+'?type=json&action=read&table=settings&column=suchgebiete&EID='+eid+'&json_nodes&values',
			format: new GeoJSON()
		}));
	};
	
	
	// Einsatzleitung setzen
	let Einsatzleitung = new VectorLayer({
		source: new VectorSource({
			features: [
				new Feature({
					geometry: new Point(fromLonLat([parseFloat(eX),parseFloat(eY)])),
					name: 'Einsatzleitung',
					beschreibung: 'Einsatz: '+eid+'<br>'+einsatz,
					img:''
				})
			]
		}),
		style: new Style({
			image: new Icon({
				anchor: [18,40],
				anchorXUnits: 'pixels',
				anchorYUnits: 'pixels',
				src: 'img/leitstelle.png'
			})
		})
	});
	

	if(suchtyp !="" && ppilat !=""){
		var PPI = new VectorLayer({
			name: "PPI",
			source: new VectorSource({
				features: [new Feature({
					geometry: new Point(fromLonLat([parseFloat(ppilon),parseFloat(ppilat)])),
					name: getsessionStorage('gesuchtname'),
					beschreibung: getsessionStorage('gesuchtbeschreibung'),
					img: getsessionStorage('gesuchtbild')
				})]
			}),
			style: new Style({
				image: new Icon({
					anchor: [13,13],
					anchorXUnits: 'pixels',
					anchorYUnits: 'pixels',
					src: 'img/ppi.png'
				})
			})
		});
	// Radien zeichnen
		var perzentillenArray = JSON.parse("["+perzentilen+"]");
		var circlecolor = perzentillenArray.reverse();
		var ppiRadiusStyle = [];
		var ppiTextStyle = [];
		var offsetText,centerCoords;
		perzentillenArray.forEach(function(perzentile,i){
			ppiRadiusStyle[i] = new Style({
				stroke: new Stroke({
					color: '#f508f7'+circlecolor[i],
					width: 2,
				})
			});
			ppiTextStyle[i] = new Style({
				text: new Text({
					text: ""+circlecolor[i]+"%",
					textAlign: 'left',
					placement: 'point',
					font: 'bold 12px "Open Sans", "Arial Unicode MS", "sans-serif"',
					fill: new Fill({
						color: '#f508f7'
					}),
					stroke: new Stroke({
						color: '#fff',
						width: 2
					})
				})
			});
		});
	
	
		var tX;
		var radienArray = JSON.parse("["+radien+"]");
		var rLength = radienArray.length-1;
		var ppiRadiusSource = new VectorSource();
		var ppiRadiusFeature = [];
		var ppiTextSource = new VectorSource();
		var ppiTextFeature = [];
		radienArray.forEach(function(radius,i) {
			ppiRadiusFeature[i] = new Feature({
				geometry: new Circle(fromLonLat([parseFloat(ppilon),parseFloat(ppilat)]),radius)
			});
			ppiRadiusFeature[i].setStyle(ppiRadiusStyle[i]);
			ppiRadiusSource.addFeature(ppiRadiusFeature[i]);
			//Text
			//tX = parseFloat(ppilon) - (radius/111120);
			ppiTextFeature[i] = new Feature(
				new Point(fromLonLat([parseFloat(ppilon) - (parseFloat(radius)/111120),parseFloat(ppilat)]))
			);
			ppiTextFeature[i].setStyle(ppiTextStyle[rLength-i]);
			ppiTextSource.addFeatures([ppiTextFeature[i]]);
		});
		var PPIRadius = new VectorLayer({
			name: "PPIRadius",
			source: ppiRadiusSource
		});
		var PPIText = new VectorLayer({
			declutter: true,
			name: "PPIText",
			source: ppiTextSource
		});
	}
	


	// Kartenlayer für die index.html erstellen
	let maplayer;
	if(suchtyp !="" && ppilat !=""){
		if(searcharea){
			maplayer = [base,vector,PPIRadius,PPIText,PPI,Einsatzleitung,searcharea,tracks,pois];
		}else{
			maplayer = [base,vector,PPIRadius,PPIText,PPI,Einsatzleitung,tracks,pois];
		}
	}else{
		if(anreise){
			maplayer = [base,vector,Einsatzleitung,anreisetracks];
		}else{
			if(searcharea){
				maplayer = [base,vector,Einsatzleitung,searcharea,tracks,pois];
			}else{
				maplayer = [base,vector,Einsatzleitung,tracks,pois];
			}
		}
	}
	var view = new View({
		center:fromLonLat([parseFloat(mapcenterX),parseFloat(mapcenterY)]),
		zoom: zoom
	})

	let etraxmap;

	let Karte_laden = function(){
		etraxmap = new Map({
			controls: defaultControls().extend([
				mousePositionControl,
				new ScaleLine({
					units: 'metric'
				})
			]),
			target: 'mapdiv',
			layers: maplayer,
			view: view
		});
	}

	Karte_laden();
	
	//Alle Layer vor das Grid legen
	maplayer.forEach(
		function(item) {
			let zIndex = 10;
			let layer = item.get('name');
			if(layer == 'poi'){
				zIndex = 25;
			}else if(layer == 'usertrack'){
				zIndex = 20;
			}else if(layer == 'searcharea'){
				zIndex = 15;
			}
			item.setZIndex(zIndex);
		}
	);
	base.setZIndex(0);
	

	// Tracks ausblenden
	let hideUsertracks = function(){
		maplayer.forEach(
			function(item) {
				let layer = item.get('name');
				if(layer == 'usertrack'){
					if(item.getOpacity() == 1){
						item.setOpacity(0);
					}else{
						item.setOpacity(1);
					}
				}
			}
		);
	}
	jQuery('#mainNav').on('click','.hidetracks',function(){
		hideUsertracks();
	});

	//Gruppenoverlay
	var element = document.getElementById('elementinfo');
	var gruppeninfo = new Overlay({
		element: element,
		positioning: 'bottom-center',
		stopEvent: false
	});
	etraxmap.addOverlay(gruppeninfo);

	// display popup on click
	let Popup_anzeigen = function(evt) {
		jQuery(element).popover('dispose');
		if(!jQuery("body").hasClass("poi") && !jQuery("body").hasClass("drawArea")){
			let feature = etraxmap.forEachFeatureAtPixel(evt.pixel,
				function(feature) {
					return feature;
				});
				
			if(feature) {console.log(feature);
				if(feature.get('name')){
					let delete_icon = ''
					if(feature.get('popover') == 'delete' && Orgfid <= 1){
						delete_icon = " <a href='#' data-original-title='Suchgebiet löschen' data-db='"+feature.get('db')+"' data-field='and_id' data-value='"+feature.get('id')+"' class='deletetype float-right showtooltip'><i class='material-icons'>delete</i></a>"
					}
					let content = '';
					let title = '';
					let coordinates = evt.coordinate;
					let print_icon = (feature.get('typ')) ? " <a href='pdf/suchgebietpdf.php?SID="+feature.get('id')+"&map="+printMap+"' target='_blank' class='float-right'><i class='material-icons'>print</i>" : "";
					title = "<span style='color:"+feature.get('color')+"'>"+feature.get('name')+"</span> <a href='#' class='closepopover float-right'><i class='material-icons'>highlight_off</i></a>"+delete_icon+print_icon;
					let XY = 0;
					if(title !== undefined){
						if(jQuery.isArray(feature.getGeometry().getCoordinates()[0])){
							XY = feature.getGeometry().getCoordinates()[0][0];
						}else if(!jQuery.isArray(feature.getGeometry().getCoordinates()[0])){
							XY = feature.getGeometry().getCoordinates();
						}
					}
					if(XY != 0){
						//let status_text = jQuery('#' + feature.get('gruppe')).attr('data-status');
						let status_text = feature.get('status');
						(status_text) ? content += "Gruppenstatus: " + status_text + "<br>" : "";
						content += "RW: "+toUTM(XY)[0]+" HW: "+toUTM(XY)[1]+"<br>RW: "+toDMG(XY)[0].toFixed(3)+" HW: "+toDMG(XY)[1].toFixed(4)+"<br>";
						(feature.get('beschreibung')) ? content += feature.get('beschreibung') : "";
						let unit = (feature.get('typ') == 'Suchgebiet') ? aunit : lunit;
						let factor = (feature.get('typ') == 'Suchgebiet') ? afactor : lfactor;
						(feature.get('masse')) ? content += " " + runden(feature.get('masse'),unit,factor) : "";
						(feature.get('img') && !feature.get('img').includes("gk.png")) ? content += "<br><a class='bigImg' data-header='"+feature.get('name')+"' href='api/get_image.php?type=poi&img="+feature.get('img')+"_big.jpg''><img src='api/get_image.php?type=poi&img="+feature.get('img')+".jpg'></a>" : "";
						gruppeninfo.setPosition(coordinates);
					}
					
					if(title !== undefined){
						jQuery(element).popover({
							placement: 'top',
							html: true,
							title:title,
							content: content
						});
						jQuery(element).popover('show');
					}else{
						jQuery(element).popover('dispose');
					}
				} else {
					jQuery(element).popover('dispose');
				}
			}
		}
	}
	
	etraxmap.on('click', function(evt) {
		Popup_anzeigen(evt);
		Suchgebiete_anzeigen();
	});
	
	etraxmap.on('movestart', function(evt) {
		jQuery(element).popover('dispose');
	}); 
	
	let Bigpicture_anzeigen = function(that) {
		let header = that.attr('data-header');
		let src = jQuery(that).attr('href');
		jQuery(element).popover('dispose');
		jQuery('.ImgModal').modal('hide');
		jQuery('.ImgModal #ImgModalheader').html(header);
		jQuery('.ImgModal .imagelayer').html('<img src="'+src+'">');
		jQuery('.ImgModal').modal('show')
	}
	
	jQuery(document).on("click", ".bigImg", function(event) {
		event.preventDefault();
		Bigpicture_anzeigen(jQuery(this));
	});

	//if(!jQuery("body").hasClass("handout")){// JS das nur in der index.html angezeigt werden
		
		  
		/**
		* Format length output.
		* @param {module:ol/geom/LineString~LineString} line The line.
		* @return {string} The formatted length.
		*/
		let sketch, 
			drawer,
			helpTooltipElement, 
			helpTooltip, 
			measureTooltipElement, 
			draw,
			snap,
			modify,
			listener,
			continuePolygonMsg = 'Für die Eckpunkte des Suchgebietes in die Karte klicken',
			continueLineMsg = 'Für die Punkte der Wegsuche in die Karte klicken',
			pointCoords0 = "",
			pointCoords1 = "",
			areaType = "",
			gruppengroesse = "",
			size = "",
			nameType = "",
			areaCoords = [];
		

		let formatLength = function(line) {
			let length = getLength(line);
			let output;
			output = (Math.round(length * 100) / 100);
			return output;
		};

		let formatArea = function(polygon) {
			let area = getArea(polygon);
			let output = (Math.round(area * 100) / 100);
			return output;
		};
		

		let addInteraction_draw = function() {	
			let type = jQuery("#searchtype").val();
			//etraxmap.removeInteraction(draw);
			var pointerMoveHandler = function(evt) {
				if (evt.dragging) {
					return;
				}
				/** @type {string} */
				var helpMsg = 'In die Karte klicken und '+ type +'zeichnen';

				if (sketch) {
					var geom = (sketch.getGeometry());
					if (geom instanceof Polygon) {
						helpMsg = continuePolygonMsg;
					} else if (geom instanceof LineString) {
						helpMsg = continueLineMsg;
					}
				}
			};
			etraxmap.on('pointermove', pointerMoveHandler);		
				
			if(type == 'area'){
				type = 'Polygon';
			}else if(type == 'point'){
				type = 'Point';
			}else{
				type = 'LineString';
			}
			draw = new Draw({
				source: source,
				type: type,
				style: new Style({
					fill: new Fill({
						color: 'rgba(255, 255, 255, 0.2)'
					}),
					stroke: new Stroke({
						color: 'rgba(0, 0, 0, 0.5)',
						lineDash: [10, 10],
						width: 2
					}),
					image: new CircleStyle({
						radius: 5,
						stroke: new Stroke({
							color: 'rgba(0, 0, 0, 0.7)'
						}),
						fill: new Fill({
							color: 'rgba(255, 255, 255, 0.2)'
						})
					})
				})
			});
			etraxmap.addInteraction(draw);
			snap = new Snap({source: source});
			etraxmap.addInteraction(snap);
			modify = new Modify({source: source});
			etraxmap.addInteraction(modify);

			//Suchgebiet_zeichnen();
			//createHelpTooltip();
			
			
			areaCoords = [];
			draw.on('drawstart',
			function(evt) {
				sketch = evt.feature;
				drawer = evt.target;
				/** @type {module:ol/coordinate~Coordinate|undefined} */
				var tooltipCoord = evt.coordinate;
				listener = sketch.getGeometry().on('change', function(evt) {
					var geom = evt.target, output, neededHF;
					if (geom instanceof Polygon) {
						areaType = "Polygon";
						output = runden(formatArea(geom),aunit,afactor);
						size = formatArea(geom);
						gruppengroesse = "2HF 2H 1GK";
						if(parseFloat(output) > 5){
							neededHF = Math.round(parseFloat(output)/HFquote);
							if(neededHF < 20){
								gruppengroesse = neededHF+"HF "+neededHF+"H 1GK";
							}else{
								gruppengroesse = "2HF 2H 1GK";
							}
						}
						output = ', Größe: ' + output;
						tooltipCoord = geom.getInteriorPoint().getCoordinates();
					} else if (geom instanceof LineString) {
						areaType = "MultiLineString";
						output = runden(formatLength(geom),lunit,lfactor);
						output = ', Länge: ' + output;
						size = formatLength(geom);
						gruppengroesse = "2HF 1H 1GK";
						if(parseFloat(output)/1000 > 5){
							neededHF = Math.round((parseFloat(output)/1000)/(HFquote+0.5));
							if(neededHF < 20){
							gruppengroesse = neededHF+"HF 1H 1GK";
							}else{
								gruppengroesse = "2HF 2H 1GK";
							}
						}
						tooltipCoord = geom.getLastCoordinate();
						if(jQuery("#searchtype").val() == "areaoverview"){
							output = "Ecke unten rechts wählen";
						}
						if(jQuery("#searchtype").val() == "trail"){
							output = "Kartenausschnitt wählen";
						}
					} 
					jQuery('#savegebiete #searchAreas .size').html(output);
				});
			}, this);
		}

		
		let Suchgebiet_speichern = function(){
				draw = false;
				jQuery("body").removeClass("drawArea");
				let typ = jQuery(".suchgebiet-form #searchtype option:selected" ).text();
				let color = jQuery(".suchgebiet-form #grouparea option:selected" ).data("color");
				let strokecolor = (typ != 'Übersichtskarte') ? color : 'transparent';
				let strokewidth = (typ != 'Übersichtskarte') ? 2 : 0;
				let Typname = 'NotPoint';
				if(typ == 'Punktsuche'){
					Typname = 'Point';
					areaType = 'Point';
				}
				size = (typ == 'Mantrailer') ? '' : size;
				
				let json_data = {
					"type": "Feature",
					"properties": {
						"name":jQuery(".suchgebiet-form #grouparea option:selected" ).text(),
						"typ": typ,
						"id": "SID" + Date.now(),
						"color": color,
						"beschreibung": jQuery(".suchgebiet-form #searchtype option:selected" ).text() + " " + jQuery(".suchgebiet-form #grouparea option:selected" ).text(),
						"img": "",
						"gruppe": jQuery(".suchgebiet-form #grouparea").val(),
						"OID": oid,
						"strokecolor": strokecolor,
						"strokewidth": strokewidth,
						"fillcolor": color+"33",
						"status": "",
						"masse": size,
						"db": "suchgebiete",
						"popover": "delete"
						},
					"geometry": {
						"type": areaType,
						"coordinates": areaCoords
					}
				};
				jQuery(".modal.searchAreas").modal('hide');
				jQuery("#area").hide();
				jQuery(".start-measure").show();
				jQuery("#area").html("");
				jQuery(".tooltip").hide();
				areaCoords = "";
				areaType = "";
				size = "";
				gruppengroesse = "";
				
				database_call('settings','update','json_append','suchgebiete',{EID: eid},Typname,json_data,Suchgebiete_anzeigen,false);
			}
				
			jQuery('#savegebiete').on('click','.save-area',function(){
				let XY = sketch.getGeometry().getCoordinates();
				if(jQuery(this).attr('data-type') == 'point'){
					areaCoords.push(setnewProjection("EPSG:3857","WGS84",XY));
				}else{
					let coordsArray = (jQuery(this).attr('data-type') == 'area') ? XY[0] : XY;
					coordsArray.forEach(function(item){
						areaCoords.push(setnewProjection("EPSG:3857","WGS84",item));
					});
				}
				etraxmap.removeInteraction(draw);
				etraxmap.removeInteraction(snap);
				etraxmap.removeInteraction(modify);
				Suchgebiet_speichern();
			});
			
			jQuery('#savegebiete').on('click','.stop-measure',function(){
				etraxmap.removeInteraction(draw);
				etraxmap.removeInteraction(snap);
				etraxmap.removeInteraction(modify);
				jQuery("body").removeClass("drawArea");
				jQuery("#area").hide();
				jQuery("#area").html("");
				//draw.clear();
				areaCoords = "";
				areaType = "";
				size = "";
				gruppengroesse = "";
			});
		
		jQuery('#suchgebiete').on('change','#searchtype', function(){
			jQuery('.start-measure').show();
		});
			
		jQuery('#suchgebiete').on('click','.start-measure',function(e) {
			e.preventDefault();
			etraxmap.removeInteraction(draw);
			etraxmap.removeInteraction(snap);
			etraxmap.removeInteraction(modify);
			if(jQuery("#searchtype").val() != ""){
				addInteraction_draw();
				jQuery("#suchgebiete").modal("hide");
				jQuery("#savegebiete #searchAreas .typ").html(jQuery("#searchtype option:selected").text());
				jQuery("#savegebiete").modal("show");
				jQuery("#savegebiete .save-area").attr("data-type",jQuery("#searchtype").val());
				jQuery(".tooltip.tooltip-measure").show();
				jQuery("body").addClass("drawArea");
			}else{
				jQuery("#searchtype").popover('show');
				jQuery(".tooltip.tooltip-measure").hide();
				jQuery(".stop-measure").hide();
			}
		});
		jQuery("body").on("click","#savegebiete .stop-measure", function(){
			jQuery("#savegebiete,#area").hide();
			jQuery(".start-measure").show();
			areaCoords = [];
			jQuery("#area").html("");
			jQuery(".tooltip").hide();
			jQuery("#searchtype").selectedIndex = 0;
			etraxmap.removeInteraction(draw);
			etraxmap.removeInteraction(snap);
			etraxmap.removeInteraction(modify);
			sketch = null;
			measureTooltipElement = null;
			//Suchgebiet_zeichnen();
			unByKey(listener);
		});	
		
		jQuery("body").on("click","#suchgebiete .save-area", function(){
			etraxmap.removeInteraction(draw);
			sketch = null;
			measureTooltipElement = null;
			unByKey(listener);
			jQuery(".start-measure").show();
		});
		
		jQuery('body').on('change','#searchtype', function(){
			etraxmap.removeInteraction(draw);
		});
	//}
	// Ende der Scripts nur für die index.html
	
	// Umrechnung der Einheiten
	function runden(num,unit,factor){
		let value =(Math.round(num)/100)*100;
		value = (value / factor);
		let newval = (unit != 'm' && unit != 'm&sup2;') ? value.toFixed(1) : Math.round(value);
		return(newval + unit);
	}


	// Suchgebiete
	jQuery('.navbar').on('click','.nav-link.suchgebiete',function(){
		Suchgebiete_Modal_anzeigen();
		jQuery('.modal.searchAreas').modal('show');
		jQuery('.start-measure').hide();
	});
		
	let Suchgebiete_Modal_anzeigen = function(){		
		database_call('settings','read','json','suchgebiete',{EID: eid},'','',Suchgebiete_auflisten,true);
	}
	
	let Suchgebiete_auflisten = function(data){
		let gruppenliste = [], overviewMapliste = [], gruppenselect = ['<option value="Gruppe0" selected>Gruppe wählen</option>'];
		let gruppengroesse,einheit,neededHF;
		jQuery("#showAreas table").html('');
		jQuery("#grouparea").html('');
		jQuery(".groupselected").html('');
		
		
		let Map_extents = etraxmap.getView().calculateExtent(etraxmap.getSize()),
			view_left_top = setnewProjection("EPSG:3857","WGS84", [Map_extents[0], Map_extents[3]]),
			view_right_bottom = setnewProjection("EPSG:3857","WGS84", [Map_extents[2], Map_extents[1]]);
		gruppenliste.push('<tr>'+
			'<td colspan="9"><p>Lagekarte <a href="pdf/mapawesome.php?SID=&format=A4q&map='+printMap+'&ul='+view_left_top+'&lr='+view_right_bottom+'" target="_blank"><i class="material-icons">print</i></a> momentane Kartenansicht <a href="pdf/suchgebietpdf.php?SID=&map='+printMap+'&ul='+view_left_top+'&lr='+view_right_bottom+'" target="_blank"> <i class="material-icons">print</i></span></a></p></td>'+
			'</tr>');
			
		if(data !== '' && data !== 'undefined' && data !== 'null'){
			let searcharea_json = JSON.parse(data);
			jQuery(searcharea_json.features).each(function(i,val){
				
				let deleteButton,
					chooseGruoup,
					masse = val.properties.masse,
					areaType = val.properties.typ,
					color = val.properties.color,
					coordinates = val.geometry.coordinates,
					name = val.properties.name,
					id = val.properties.id,
					gruppe = val.properties.gruppe,
					status = val.properties.status;
				
					//gruppe = (gruppe == "e_group0") ? "Keine Zuweisung" : val.properties.gruppe;

				if(Orgfunction.einsatzleitung){// lead
					deleteButton = '<a href="#" title="Suchgebiet löschen" data-db="suchgebiete" data-field="and_id" data-value="'+id+'" class="deletearea showtooltip" data-original-title="Suchgebiet löschen"><i class="material-icons">delete</i></a>';
					chooseGruoup = '<select class="groupselected custom-select showtooltip" style="border:1px solid ' + color + '" data-id="'+i+'" data-status="'+status+'" data-gruppenID="'+gruppe+'" data-typ="'+areaType+'" data-size="'+masse+'" data-original-title="Gruppenzuweisung ändern"></select>';
				}else if(Orgfunction.zuweisen){
					chooseGruoup = '<select class="groupselected custom-select showtooltip" style="border:1px solid ' + color + '" data-id="'+i+'" data-status="'+status+'" data-gruppenID="'+gruppe+'" data-typ="'+areaType+'" data-size="'+masse+'" data-original-title="Gruppenzuweisung ändern"></select>';
				}
				if(id != 'area51'){
					if(areaType != 'Übersichtskarte'){
						if(areaType == 'Suchgebiet' || areaType == 'Wegsuche'){ //Für Suchgebiet und Wegsuche werden Fläche/Länge angezeigt
							let einheit = (areaType == 'Suchgebiet') ? aunit : lunit,
								factor = (areaType == 'Suchgebiet') ? afactor : lfactor,
								groupname,group = "";

							group = (name === null) ? "nicht zugewiesen" : name;
							groupname = group.replace(" ","_");
							gruppenliste.push('<tr class="' + val.properties.status + '">'+
							'<td><a href="#" class="zoomon showtooltip" data-koordinaten="' + coordinates + '" data-original-title="Auf das Suchgebiet zoomen"><i class="material-icons zoomin">zoom_in</i></a></td>'+
							'<td><a alt="drucken" class="showtooltip" href="pdf/suchgebietpdf.php?SID='+id+'&map='+printMap+'" target="_blank" data-original-title="Suchgebiet drucken"><i class="material-icons">print</i></span></a></td>'+
							'<td><a class="area_by_mail showtooltip" href="#" data-SID="'+id+'" data-typ='+ areaType +' data-gruppenID="'+gruppe+'" data-map="'+printMap+'"  data-original-title="Suchgebiet per Mail schicken"><i class="material-icons">mail</i></span></a></td>'+
							'<td><a class="area_download showtooltip" href="gpx/suchgebiet-gpx-download.php?SID='+id+'&name='+ areaType +'-'+gruppe+'&title='+ areaType +' '+ name +'" target="_blank" data-original-title="Suchgebiet Download"><i class="material-icons">cloud_download</i></span></a></td>'+
							'<td>'+chooseGruoup+'</td>'+
							'<td><span style="color:' + color + '">'+ areaType +'</span></td>'+
							'<td style="text-align:right">'+runden(masse,einheit,factor)+'</td>'+
							'<td>'+deleteButton+'</td>'+
							'</tr>');
						} else {
							let einheit = (areaType == 'Suchgebiet') ? aunit : lunit,
								factor = (areaType == 'Suchgebiet') ? afactor : lfactor,
								groupname,group = "";

							group = (name === null) ? "nicht zugewiesen" : name;
							groupname = group.replace(" ","_");
							gruppenliste.push('<tr class="' + val.properties.status + '">'+
							'<td><a href="#" class="zoomon showtooltip" data-koordinaten="' + coordinates + '" data-original-title="Auf das Suchgebiet zoomen"><i class="material-icons zoomin">zoom_in</i></a></td>'+
							'<td><a alt="drucken" class="showtooltip" href="pdf/suchgebietpdf.php?SID='+id+'&map='+printMap+'" target="_blank"  data-original-title="Suchgebiet drucken"><i class="material-icons">print</i></span></a></td>'+
							'<td><a class="area_by_mail showtooltip" href="#" data-SID="'+id+'" data-typ='+ areaType +' data-gruppenID="'+gruppe+'" data-map="'+printMap+'"  data-original-title="Suchgebiet per Mail schicken"><i class="material-icons">mail</i></span></a></td>'+
							'<td><a class="area_download showtooltip" href="gpx/suchgebiet-gpx-download.php?SID='+id+'&name='+ areaType +'-'+gruppe+'&title='+ areaType +' '+ name +'" target="_blank"  data-original-title="Suchgebiet Download"><i class="material-icons">cloud_download</i></span></a></td>'+
							'<td>'+chooseGruoup+'</td>'+
							'<td><span style="color:' + color + '">'+ areaType +'</span></td>'+
							'<td style="text-align:right"></td>'+
							'<td>'+deleteButton+'</td>'+
							'</tr>');
						}						
					}else{
						let group = (name === null) ? "nicht zugewiesen" : name,
						groupname = group.replace(" ","_");

						overviewMapliste.push('<tr class="' + val.properties.status + '">'+
						'<td></td>'+
						'<td><a alt="drucken" href="pdf/suchgebietpdf.php?SID='+id+'&map='+printMap+'" target="_blank" title="Suchgebiet drucken"><i class="material-icons">print</i></span></a></td>'+
						'<td><a class="area_by_mail" href="#" data-SID="'+id+'" data-gruppenID="'+gruppe+'" data-map="'+printMap+'" title="Suchgebiet per Mail schicken"><i class="material-icons">mail</i></span></a></td>'+
						'<td colspan="3">' + areaType + '</td>'+
						'<td>'+deleteButton+'</td>'+
						'</tr>'); 
					}
				}
				
				//gruppenselect.push('<option value="'+ gruppe +'" style="color:' + color + '" data-color=' + color + ' data-OID=' + OID + ' data-gruppe=' + gruppe + '>'+name+'</option>');
			});
			gruppenliste.forEach(function(entry){
				jQuery("#showAreas table").append(entry);
			});
			overviewMapliste.forEach(function(entry){
				jQuery("#showAreas table").append(entry);
			});
		}
		jQuery("#searchtype").val("");
		
		database_call('settings','read','json','gruppen',{EID: eid},'','',Gruppenselect_fuellen,true);
	}
	
	let Gruppenselect_fuellen = function(gruppen){
		let gruppenjson = JSON.parse(gruppen),
			selectoptions = "<option value='e_group0' data-color='#FFCC33' data-fillcolor='#FFCC3333'>Gruppe auswählen</option>",
			selected,
			disabled;
		jQuery(gruppenjson).each(function(i,val){
			if(Orgfunction.einsatzleitung || Orgfunction.gleich){// lead
				if(val.aktuellerStatus != "löschen"){
					selectoptions += "<option value='"+val.gruppe+"' style='color:" + val.color + "' data-status='"+val.aktuellerStatus+"' data-color='"+ val.color +"' data-fillcolor='"+ val.color +"33' data-OID='"+ val.OID +"'>"+val.name+"</option>";
				}
			}
		});
		jQuery("#grouparea").html(selectoptions);
		jQuery(".groupselected").html(selectoptions);

		jQuery(".groupselected").each(function(){
			let gid = jQuery(this).attr('data-gruppenid');
			jQuery(this).val(gid).change();
			if(jQuery(this).attr('data-status') == "zurück"){
				jQuery(this).attr('disabled', true);
			}
		});

	}
	
	
	let Suchgebiet_senden = function(personen){
		jQuery('.modal.searchAreas').modal('hide');
		let gruppenID = jQuery('#mailer #groupmemberlist').attr('data-gruppenID'),
			gruppenjson = JSON.parse(personen);
		jQuery('#mailer #groupmemberlist').empty();
		jQuery(gruppenjson).each(function(i,val){
			if('e_group'+val.gruppe == gruppenID && val.email){
				jQuery('#mailer #groupmemberlist').append('<li class="form-row"><div class="custom-control custom-checkbox"><input class="custom-control-input" type="checkbox" value="'+val.email+'" id="'+val.UID+'" required=""><label class="custom-control-label" for="'+val.UID+'">'+val.name+'</label></div></div></li>');
			}
		});
		jQuery('#mailer').modal('show');
	}
	
	jQuery("body").on("click",".area_by_mail",function(e){
		e.preventDefault();
		jQuery('#groupmemberlist')
			.attr('data-gruppenID',jQuery(this).attr('data-gruppenID'))
			.attr('data-SID',jQuery(this).attr('data-SID'));
		jQuery('#suchgebiet_senden')
			.attr('data-SID',jQuery(this).attr('data-SID'))
			.attr('data-map',jQuery(this).attr('data-map'))
			.attr('data-typ',jQuery(this).attr('data-typ'));
		database_call('settings','read','json','personen_im_einsatz',{EID: eid},'','',Suchgebiet_senden,true);
	});
	
	jQuery("body").on("click","#mailer #suchgebiet_senden",function(e){
		e.preventDefault();
		let mails = "";
		jQuery('#mailer #groupmemberlist input:checked').each(function(){
			mails += jQuery(this).val() + ';';
		});
		let Map_extents = etraxmap.getView().calculateExtent(etraxmap.getSize());
		let view_left_top = setnewProjection("EPSG:3857","WGS84", [Map_extents[0], Map_extents[3]]);//Links-Oben
		let view_right_bottom = setnewProjection("EPSG:3857","WGS84", [Map_extents[2], Map_extents[1]]);//Rechts-Unten
		let printMap = jQuery(this).attr('data-map');
		let sid = jQuery(this).attr('data-sid');
		let typ = jQuery(this).attr('data-typ');
		jQuery.ajax({
			url: 'pdf/suchgebiet_als_pdf_senden.php',
			type: "get",
			data: {
				SID: sid,
				typ: typ,
				map: printMap,
				send_to:mails
			}
		}).done(function(e) {
		});
	});
	
	//Gruppenzuweisen ändern
	let Suchgebiet_Gruppenzuweisung_aendern = function(that){
		jQuery(".modal.searchAreas").modal('hide');
		let strokecolor = that.find("option:selected").attr('data-color');
		let strokewidth = 2;
		var json_nodes = {
			id: that.attr('data-id'),
			gruppe: that.find("option:selected").val(), 
			name: that.find("option:selected").text(), 
			color: that.find("option:selected").attr('data-color'),
			strokecolor: strokecolor,
			strokewidth: strokewidth,
			fillcolor: that.find("option:selected").attr('data-fillcolor'),
			beschreibung: that.attr('data-typ') + " " + jQuery(this).find("option:selected").text(), 
			oid: that.find("option:selected").attr('data-OIS'),
			altegruppe: that.attr("data-groupid"),
			status: that.find("option:selected").attr('data-status')
		};
		database_call('settings','update','json_update','suchgebiete',{EID: eid},'', json_nodes,Suchgebiete_anzeigen,false);
	}
	
	jQuery("#showAreas").on("change",".groupselected",function(){
		Suchgebiet_Gruppenzuweisung_aendern(jQuery(this));
	});
	
	let Suchgebiet_loeschen = function(that){
		jQuery(".modal.searchAreas").modal('hide');
		var json_nodes = that.attr("data-value");
		database_call('settings','update','json_delete','suchgebiete',{EID: eid},'', json_nodes,Suchgebiete_anzeigen,false);
	}
	
	jQuery("body").on("click","#showAreas .deletearea,.popover .deletetype",function(e){
		e.preventDefault();
		jQuery('.popover.show').popover('dispose');
		if(jQuery(this).attr('data-db') == 'suchgebiete'){
			if(window.confirm("Suchgebiet löschen?")){
				Suchgebiet_loeschen(jQuery(this));
			}
		}else{
			if(window.confirm("POI löschen?")){
				POI_loeschen(jQuery(this));
			}
		}
	});
	
	jQuery(".modal.searchAreas").on("click",".zoomon",function(e){
		e.preventDefault();
		jQuery(".modal.searchAreas").modal('hide');
		var coords = jQuery(this).attr("data-koordinaten");
		coords = coords.split(",");
		centerTo(coords[0],coords[1]);
	});
	
	
	// User Interaktionen mit der Karte triggern
	etraxmap.on('moveend', function() {
		refresh_Session();
		// Zoom ändern
		setsessionStorage("zoom", etraxmap.getView().getZoom());
		jQuery(".zoom").html(etraxmap.getView().getZoom());
		// Karte verschieben
		var mapcenter = toDMG(etraxmap.getView().getCenter());
		var mapcenterUTM = toUTM(etraxmap.getView().getCenter());
		jQuery(".mapcenter").html("<span class='badge badge-light mr-2'>Kartenmitte:</span>RW: "+mapcenter[0].toFixed(3)+", HW: "+mapcenter[1].toFixed(4)+" <span class='badge badge-light mr-2'>UTM:</span>RW: "+mapcenterUTM[0].toFixed(0)+", HW: "+mapcenterUTM[1].toFixed(0));
		setsessionStorage("mapcenterX", mapcenter[0].toFixed(3));
		setsessionStorage("mapcenterY", mapcenter[1].toFixed(4));
	});

	// Punkt setzten bei klick
	jQuery(".set-poi").click(function() {
		jQuery("body").addClass("poi");
	});
	let POI_Koordinaten_einfügen = function(event) {
		if(jQuery("body").hasClass("poi")){
			var XY = toUTM(event.coordinate);
			var newPOIcoords = toDMG(event.coordinate);
			var button = jQuery(event.relatedTarget);
			jQuery(".modal.setPOI #poilatutm").val(newPOIcoords[1]);
			jQuery(".modal.setPOI #poilonutm").val(newPOIcoords[0]);
			jQuery(".modal.setPOI").modal('show');
			Koordinaten_umrechnen(newPOIcoords[0],newPOIcoords[1]);
		}
		jQuery("body").removeClass("poi");
	}
	
	etraxmap.on('click', function(event) {
		POI_Koordinaten_einfügen(event);
	});
	// POI speichern und Bild hochladen
	jQuery('#poiImage').on('change','#upload_poi_image',function(e){
		let t = new Date().getTime();
		jQuery("#poiImage .poiImagename").val('poi_'+uid+'_'+t);
		uploadIMG(e.target.files, "#poiImgpreview");
	});

	let POIimg_speichern = function(){
		let postData = jQuery('#poiImage').serialize();
		jQuery.ajax({    
			type: 'POST',  
			url: root+wp+slsh+ui,  
			data:postData,
			success:function(data){
				POIs_anzeigen();
				jQuery("#poiImage #upload_poi_image").val('');
				jQuery("#poiImage .poiImagename").val('');
				jQuery('#base64img').val('');
				jQuery('#poiImgpreview').empty();
			}
		});
	}
	let POI_speicher = function(){
		//writePOIs();
		let t = new Date().getTime();
		let imgsrc = jQuery("#poiImage .poiImagename").val();
		jQuery("#poiImage .eid").val(eid);
		var json_data = {
			"type": "Feature",
			"properties": {
				"name": jQuery("#poiForm #poiname").val(),
				"uid" : uid,
				"oid" : oid,
				"color": "#c00",
				"beschreibung": jQuery("#poiForm #poidesc").val(),
				"img": imgsrc,
				"poi": t,
				"id": t,
				"db": "pois",
				"popover": "delete"
				},
			"geometry": {
				"type": "Point",
				"coordinates": {
					"0": parseFloat(jQuery("#poiForm #poilond1").val()+"."+jQuery("#poiForm #poilond2").val()),
					"1": parseFloat(jQuery("#poiForm #poilatd1").val()+"."+jQuery("#poiForm #poilatd2").val())}
				}
			};
		jQuery(".modal.setPOI #poiname").val("");
		jQuery(".modal.setPOI #poidesc").val("");
		
		jQuery(".modal.setPOI").modal('hide');
		database_call('settings','update','json_append','pois',{EID: eid},'',json_data,POIimg_speichern,false);
	}
	
	jQuery('.setPOI').on('click','.savePOI',function(){
		POI_speicher();
	});
	
	//Durchrechnung der eingegebenen Koordinaten
	let POI_Koordinaten_berechnen = function(that){
		let coordtype = that.data('type');
		if(coordtype == 'utm'){
			let lonlat = [parseFloat(jQuery(".modal.setPOI #poilonutm").val()),parseFloat(jQuery(".modal.setPOI #poilatutm").val())];
			let POIcoords = toDMG(fromUTM(lonlat));
			Koordinaten_umrechnen(POIcoords[0],POIcoords[1]);
		}else{
			let lon = parseFloat(jQuery(".modal.setPOI #poilond1").val()+"."+jQuery(".modal.setPOI #poilond2").val());
			let lat = parseFloat(jQuery(".modal.setPOI #poilatd1").val()+"."+jQuery(".modal.setPOI #poilatd2").val());
			Koordinaten_umrechnen(lon,lat);
		}
	}
	
	jQuery('.poiKoordinaten').on('focusout','input',function(){
		POI_Koordinaten_berechnen(jQuery(this));
	});
	
	let POI_Liste_laden = function(){
		jQuery("#poiListing").empty();
		database_call('settings','read','json','pois',{EID: eid},'','',POIs_auflisten,true);
	}
	
	let POIs_auflisten = function(data) {
		var img = beschreibung = "";
		var poicount = 0;
		var pois = JSON.parse(data);
		jQuery.each(pois.features, function(i,poi){console.log(poi.properties)
			img = beschreibung = "";
			if(poi.properties.beschreibung != ""){
				beschreibung = "<br>"+poi.properties.beschreibung;
			}
			if(poi.properties.img){
				img = "<br><a class='bigImg' data-header='"+poi.properties.name+"' href='api/get_image.php?type=poi&img="+poi.properties.img+"_big.jpg'><img src='api/get_image.php?type=poi&img="+poi.properties.img+".jpg'></a>";
			}
			var coords = poi.geometry.coordinates;
			var UTMcoords = setnewProjection("WGS84","ETRS89",[parseFloat(coords[0]), parseFloat(coords[1])]);
			jQuery("#poiListing").append("<li class='m-4'><button type='button' class='btn btn-danger float-right deletePOI' data-id='"+poi.properties.poi+"'>löschen</button><a href='javascript:;' class='centerPOI showtooltip' data-coords-lon='"+coords[0]+"' data-coords-lat='"+coords[1]+"' data-original-title='Auf den POI zoomen'><i class='material-icons zoomin'>zoom_in</i> <strong>"+poi.properties.name+"</strong></a><br>"+UTMcoords[0].toFixed(0)+", "+UTMcoords[1].toFixed(0)+""+img+beschreibung+"</li>"); 
			poicount+=1;
		});
		viewoptions();
		jQuery(".modal.poiListmodal").modal('show');
	}// Befüllen der POI Liste damit wir einen Index für neue POIs schreiben können
	
	let POI_loeschen = function(that){
		database_call('settings','update','json_delete','pois',{EID: eid},'',that.attr('data-id'),POIs_anzeigen,false);
		jQuery(".modal.poiListmodal").modal('hide');
	}
	
	jQuery("#poiListing").on("click",".deletePOI", function(){
		POI_loeschen(jQuery(this));
	});
	
	jQuery(".navbar-nav").on('click','.poiList',function(){
		POI_Liste_laden();
	});
	
	jQuery("#poiListing,#alertdiv").on("click",".centerPOI",function(){
		jQuery(".modal.poiListmodal").modal('hide');
		centerTo(jQuery(this).attr("data-coords-lon"),jQuery(this).attr("data-coords-lat"));
	});
	
	jQuery(".navbar-nav").on('click','.center-EL',function(){
		centerTo(eX,eY);
	});
			
		
	// Ab Hier habe ich meinen Code eingebaut
	
	///Wechsel der Projection für die Mouseover Coordinaten
	var lon_temp,lonlat_temp;
	lon_temp = 0;
	etraxmap.on('pointermove', function(evt) {
        lonlat_temp = setnewProjection("EPSG:3857","WGS84",evt.coordinate);
		lonlat_temp = (Math.floor(lonlat_temp[0]/6))+31;
		if(lonlat_temp != lon_temp){ 
			lon_temp = lonlat_temp; //Wechsel erforderlich
			mousePositionControl.setProjection("UTM"+lon_temp);
		} else {
			//Kein Wechsel erforderlich
		}	
		
      });
	/// Ende Mouseovercoordinaten Projection wechsel
	
	
	etraxmap.on('moveend', function(evt) {
		Karte_bewegen();
	}); 
	  
	
	//+++++ Grid PT ++++++ Beginn
	//****************************************************
	var OL,OLx,OLy,OLxy,OR,ORx,ORy,ORxy,UL,ULx,ULy,ULxy,UR,URx,URy,URxy,scalex,scaley,OLxO,OLyO,hor,ver,xmin,xmax,xfrom,xfrom_temp,xto,xto_temp,yfrom,yto,ymin,ymax,xstep,ystep,lwidth,i,ii,wert,v2,v1,x,y,zlevel,hauptint,hilfsint,vector_pt,style_pt,vector_pt,source_pt,geojsonObject_pt,layer_pt,v1x,v1y,v2x,v2y,h1,h2,h1x,h1y,h2x,h2y,style_hauptint,style_hilfsint,style_labels,hauptint_vectorSource,hauptint_vector,hilfsint_vector,labels_vector,arr_temp,arr_utmcheck,utmstreifen,xl_utm, utm_name_temp;
	//UTM Grid selber zeichnen
	var olw = jQuery("#map").width();
	var olh = jQuery("#map").height();
	//arrays für die zoomlevels
	hauptint = new Array (100000,100000,100000,100000,100000,100000,100000,1000000,100000,100000,100000,100000,100000,10000,10000,10000,10000,100000,10000,10000,10000,10000,10000,10000,10000,10000,10000,100000,10000,10000);
	hauptint[19] = 1000;
	hauptint[18] = 1000;
	hauptint[17] = 1000;
	hauptint[16] = 1000;
	hauptint[15] = 1000;
	hauptint[14] = 2000;
	hauptint[13] = 5000;
	hauptint[12] = 10000;
	hauptint[11] = 10000;
	hauptint[10] = 20000;
	hauptint[9] = 50000;
	hauptint[8] = 100000;
	hilfsint = new Array (1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,10,1,1,1,1,1,1,1,1,1,1,1,1);
	hilfsint[19] = 20;
	hilfsint[18] = 10;
	hilfsint[17] = 4;
	hilfsint[16] = 4;
	hilfsint[15] = 2;
	hilfsint[14] = 2;
	hilfsint[13] = 2;
	hilfsint[12] = 2;
	hilfsint[11] = 1;
	hilfsint[10] = 2;
	hilfsint[9] = 2;
	hilfsint[8] = 2;
	
	
	let Karte_bewegen = function(){
		var etraxmapextent = etraxmap.getView().calculateExtent(etraxmap.getSize());
		zlevel = etraxmap.getView().getZoom();
		//Ermitteln der betroffenen UTM Streifen
		OLxy = setnewProjection("EPSG:3857","WGS84",getTopLeft(etraxmapextent));
		ORxy = setnewProjection("EPSG:3857","WGS84",getTopRight(etraxmapextent));
		ULxy = setnewProjection("EPSG:3857","WGS84",getBottomLeft(etraxmapextent));
		URxy = setnewProjection("EPSG:3857","WGS84",getBottomRight(etraxmapextent));
		
		xmin = Math.floor(Math.min(OLxy[0],ULxy[0],ORxy[0],URxy[0])/6)*6; //Ergibt den minimalen linken Längengrad des UTM Streifens im View + 6 ist rechter Rand
		xmax = Math.floor(Math.max(OLxy[0],ULxy[0],ORxy[0],URxy[0])/6)*6; //Ergibt den maximalen linken Längengrad des UTM Streifens im View + 6 ist rechter Rand
		ymin = Math.floor(Math.min(OLxy[1],ULxy[1],ORxy[1],URxy[1])); //Ergibt den minimalen Breitengrad des UTM Streifens im View
		ymax = Math.ceil(Math.max(OLxy[1],ULxy[1],ORxy[1],URxy[1])); //Ergibt den maximalen Breitengrad des UTM Streifens im View
		// Styledefinitionen
				style_hauptint = new Style({
					stroke: new Stroke({
						color: 'black',
						width: 3
					  }),
					text: new Text({
						  font: 'bold 15px Calibri,sans-serif',
					  overflow: true,
					  textAlign: 'right',
					  placement: 'point',
					  textBaseline: 'middle',
					  rotation: '0',
					  fill: new Fill({
						color: '#000'
					  }),
					  stroke: new Stroke({
						color: '#fff',
						width: 3
					  })
					})
				  });
											
				//leeres geoJson Objekt
				geojsonObject_pt = {
						'type': 'FeatureCollection',
						'crs': {
						  'type': 'name',
						  'properties': {
							'name': 'EPSG:3857'
						  }
						},
						'features': []
					  };
				
				//Erstellen einer neuen Vector Source - das geoJson Objekt ggf. leer einfügen
				hauptint_vectorSource = new VectorSource({ //Hauptintervall - ganze 1000er
						features: (new GeoJSON()).readFeatures(geojsonObject_pt)
					  });
				
		
		//Schleife, die alle vorkommenden UTM Streifen durchläuft
		for (xl_utm = xmin; xl_utm <= xmax; xl_utm += 6) { //6 steht für die 6 Grad Sprünge im UTM Gitter
			//UTM Namen für proj4 Definition
			utm_name_temp = "UTM"+(xl_utm/6+31);
			//jQuery(window).on('resize load', function() {
				//Eckpunkte in Darstellung - aktuell fixe Koordinaten
				OLxy = setnewProjection("EPSG:3857",utm_name_temp,getTopLeft(etraxmapextent));
				ORxy = setnewProjection("EPSG:3857",utm_name_temp,getTopRight(etraxmapextent));
				ULxy = setnewProjection("EPSG:3857",utm_name_temp,getBottomLeft(etraxmapextent));
				URxy = setnewProjection("EPSG:3857",utm_name_temp,getBottomRight(etraxmapextent));
				OLx = OLxy[0];
				OLy = OLxy[1];
				ORx = ORxy[0];
				ORy = ORxy[1];
				ULx = ULxy[0];
				ULy = ULxy[1];
				URx = URxy[0];
				URy = URxy[1];
				
				//Versuch mit Layer
				etraxmap.removeLayer(hauptint_vector);
				//Offset oben Links berechnen
				OLxO = (Math.ceil(OLx/hauptint[zlevel])*hauptint[zlevel]); //Offset Oben Links in horizontaler Richtung
				OLyO = (Math.floor(ULy/hauptint[zlevel])*hauptint[zlevel]); //Offset Oben Links in vertikaler Richtung
				
				//sauberere Version neu
				
				
				//Hinzufügen einer neuen Linie - müsste in der for Schleife erfolgen
				//Steigung berechnen
				hor = (ORy - OLy)/(ORx - OLx)
				ver = (ULx - OLx)/(ULy - OLy)
				//Grenzen und Schritte für das das Grid berechnen
				xfrom = (OLxO-hauptint[zlevel]);
				xto = (ORx+hauptint[zlevel]);
				xstep = (hauptint[zlevel]/hilfsint[zlevel]);
				yfrom = (OLyO-hauptint[zlevel]);
				yto = (OLy+hauptint[zlevel]);
				ystep = (hauptint[zlevel]/hilfsint[zlevel]);
				
				//UTM Gittergrenze einzeichnen
				arr_temp = new Array();
				ii = 0;
					for (y = ymin; y <= ymax; y += Math.ceil(((ymax-ymin)/10)*1000)/1000) {
							arr_temp[ii] = setnewProjection("WGS84","EPSG:3857",[xl_utm, y]);
							ii++;
						}
				hauptint_vectorSource.addFeature(
					new Feature({
						geometry:new LineString([arr_temp[0],arr_temp[1],arr_temp[2],arr_temp[4],arr_temp[5],arr_temp[6],arr_temp[7],arr_temp[8],arr_temp[9]]), 
						labelPoint: new Point(arr_temp[0]),
						name: '',
						lwidth: '3', 
						rot: "0"/*4.71239*/, 
						utmstreifen: "UTM"+(xl_utm/6+30)+"N | "+"UTM"+(xl_utm/6+31)+"N Zonengrenze"
					})
				);
				
				//Vertikale Linien
				i = 0;
				for (x = xfrom; x < xto; x += xstep) {
					//10 Stützpunkte für Linien
						arr_temp = new Array();
						ii = 0;
						for (y = yfrom; y <= yto; y += Math.floor((yto-yfrom)/10)) {
							arr_temp[ii] = setnewProjection(utm_name_temp,"EPSG:3857",[x, y]);
							ii++;
						}
					//Mit IF abfangen ob Wert zwischen 12 und 18 Grad liegt (UTM33N Streifen)
					arr_utmcheck = setnewProjection(utm_name_temp,"WGS84",[x, (OLy+hauptint[zlevel])]);
					if(arr_utmcheck[0] >= xl_utm && arr_utmcheck[0] <= (xl_utm+6)){
						//UTM Streifen Namen
						utmstreifen = (Math.floor(arr_utmcheck[0]/6)+31); //Ergebnis ist z.B. 33
						if (arr_utmcheck[1] >= 0) {
								utmstreifen = utmstreifen + "N"; //Nordhalbkugel
							} else {
								utmstreifen = utmstreifen + "S"; //Südhalbkugel
							}	 //Ergebnis ist z.B. 33N
						//Switch für gerade 1000er = Hauptintervall
						if (Number.isInteger(x/1000)) {	
							//hauptint_vectorSource.addFeature(new Feature({geometry:new LineString([setnewProjection(utm_name_temp,"EPSG:3857",[x, (OLy+hauptint[zlevel])]), setnewProjection(utm_name_temp,"EPSG:3857",[x,(ULy-hauptint[zlevel])])]), labelPoint: new Point(setnewProjection(utm_name_temp,"EPSG:3857",[x, (OLy+hauptint[zlevel])])),name: '',lwidth: '3'}));
							hauptint_vectorSource.addFeature(
								new Feature({
									geometry:new LineString([arr_temp[0],arr_temp[1],arr_temp[2],arr_temp[4],arr_temp[5],arr_temp[6],arr_temp[7],arr_temp[8],arr_temp[9],arr_temp[10]]), 
									labelPoint: new Point(setnewProjection(utm_name_temp,"EPSG:3857",[x, (OLy+hauptint[zlevel])])),
									name: '',
									img: '',
									beschreibung: '',
									lwidth: '3', 
									utmstreifen: ''
								})
							);
						} else {
							//hauptint_vectorSource.addFeature(new Feature({geometry:new LineString([setnewProjection(utm_name_temp,"EPSG:3857",[x, (OLy+hauptint[zlevel])]), setnewProjection(utm_name_temp,"EPSG:3857",[x,(ULy-hauptint[zlevel])])]), labelPoint: new Point(setnewProjection(utm_name_temp,"EPSG:3857",[x, (OLy+hauptint[zlevel])])),name: '',lwidth: '1'}));
							hauptint_vectorSource.addFeature(
								new Feature({
									geometry:new LineString([arr_temp[0],arr_temp[1],arr_temp[2],arr_temp[4],arr_temp[5],arr_temp[6],arr_temp[7],arr_temp[8],arr_temp[9],arr_temp[10]]), 
									labelPoint: new Point(setnewProjection(utm_name_temp,"EPSG:3857",[x, (OLy+hauptint[zlevel])])),
									name: '',
									img: '',
									beschreibung: '',
									lwidth: '1', 
									utmstreifen: ''
								})
							);
						}
							hauptint_vectorSource.addFeature(
								new Feature({
									geometry:new Point(setnewProjection(utm_name_temp,"EPSG:3857",[x, (OLy+(x-OLx)*hor-(OLy-ULy)*0.03)])), 
									labelPoint: new Point(setnewProjection(utm_name_temp,"EPSG:3857",[x, (OLy)])),
									name: x, 
									img: '',
									beschreibung: '',
									rot: "0"/*4.71239*/, 
									textAlign: 'center', 
									utmstreifen: utmstreifen
								})
							);	
					} //Ende IF für Streifenbreite	
					i++;
				}	
				//Horizontale Linien
				i = 0;
				
				for (y = yfrom; y < yto ; y += ystep) {
					
					//Mit IF abfangen ob Wert zwischen 12 und 18 Grad liegt (UTM33N Streifen)
					arr_utmcheck = setnewProjection(utm_name_temp,"WGS84",[xfrom, y]);
					xfrom_temp = new Array();
					if(arr_utmcheck[0] < xl_utm){
							xfrom_temp = setnewProjection("WGS84",utm_name_temp,[xl_utm, arr_utmcheck[1]]) //Liegt der x Wert außerhalb der Grenze wird diese auf die 12 Grad Linie gelegt
						} else {
							xfrom_temp[0] = xfrom;
						}//Ende IF für Streifenbreite
					arr_utmcheck = setnewProjection(utm_name_temp,"WGS84",[xto, y]);
					xto_temp = new Array();
					if(arr_utmcheck[0] > (xl_utm+6)){
							xto_temp = setnewProjection("WGS84",utm_name_temp,[(xl_utm+6), arr_utmcheck[1]])
						}	 else {
							xto_temp[0] = xto;
						}//Ende IF für Streifenbreite
					
					//10 Stützpunkte für Linien
						arr_temp = new Array();
						ii = 0;
						for (x = xfrom_temp[0]; x <= xto_temp[0]; x += Math.floor((xto_temp[0]-xfrom_temp[0])/10)) {
							arr_temp[ii] = setnewProjection(utm_name_temp,"EPSG:3857",[x, y]);
							ii++;
						}
						//Stützpunkte Ende
					
					//Switch für gerade 1000er = Hauptintervall
					if (Number.isInteger(y/1000)) {	
						//hauptint_vectorSource.addFeature(new Feature({geometry:new LineString([setnewProjection(utm_name_temp,"EPSG:3857",[(OLx-hauptint[zlevel]), y]), setnewProjection(utm_name_temp,"EPSG:3857",[(ORx+hauptint[zlevel]),y])]), labelPoint: new Point(setnewProjection(utm_name_temp,"EPSG:3857",[x, (OLy+hauptint[zlevel])])),name: '',lwidth: '3'}));
						hauptint_vectorSource.addFeature(
							new Feature({
								geometry:new LineString([arr_temp[0],arr_temp[1],arr_temp[2],arr_temp[4],arr_temp[5],arr_temp[6],arr_temp[7],arr_temp[8],arr_temp[9],arr_temp[10]]), 
								labelPoint: new Point(setnewProjection(utm_name_temp,"EPSG:3857",[x, (OLy+hauptint[zlevel])])),
								name: '',
								img: '',
								beschreibung: '',
								lwidth: '3', 
								utmstreifen: ''
							})
						);
					} else {
						//hauptint_vectorSource.addFeature(new Feature({geometry: new LineString([setnewProjection(utm_name_temp,"EPSG:3857",[(OLx-hauptint[zlevel]), y]), setnewProjection(utm_name_temp,"EPSG:3857",[(ORx+hauptint[zlevel]),y])]), labelPoint: new Point(setnewProjection(utm_name_temp,"EPSG:3857",[x, (OLy+hauptint[zlevel])])),name: '',lwidth: '1'}));
						hauptint_vectorSource.addFeature(
							new Feature({
								geometry: new LineString([arr_temp[0],arr_temp[1],arr_temp[2],arr_temp[4],arr_temp[5],arr_temp[6],arr_temp[7],arr_temp[8],arr_temp[9],arr_temp[10]]), 
								labelPoint: new Point(setnewProjection(utm_name_temp,"EPSG:3857",[x, (OLy+hauptint[zlevel])])),
								name: '',
								img: '',
								beschreibung: '',
								lwidth: '1', 
								utmstreifen: ''
							})
						);
					}
						//Label platzieren
						if(((OLx+(y-ULy)*ver)+(ORx-OLx)*0.03) > xfrom_temp[0]){
							hauptint_vectorSource.addFeature(
								new Feature({
									geometry: new Point(setnewProjection(utm_name_temp,"EPSG:3857",[((OLx+(y-ULy)*ver)+(ORx-OLx)*0.03), y])), 
									labelPoint: new Point(setnewProjection(utm_name_temp,"EPSG:3857",[x, (OLy)])),
									name: y, 
									img: '',
									beschreibung: '',
									rot: "0", 
									textAlign: 'left', 
									utmstreifen: ''
								})
							);
						} else {	
							hauptint_vectorSource.addFeature(
								new Feature({
									geometry: new Point(setnewProjection(utm_name_temp,"EPSG:3857",[((xfrom_temp[0]+(y-ULy)*ver)), y])), 
									labelPoint: new Point(setnewProjection(utm_name_temp,"EPSG:3857",[x, (OLy)])),
									name: y, 
									img: '',
									beschreibung: '',
									rot: "0", 
									textAlign: 'left', 
									utmstreifen: ''
								})
							);
						}
					i++;
				}	
			} // Ende der Schleife für die UTM Streifen

			//Vector Layer erzeugen 
			hauptint_vector = new VectorLayer({
				  declutter: true,
				  source: hauptint_vectorSource,
				  style: function(feature) {
					style_hauptint.getText().setText(feature.get('utmstreifen')+" "+("   ".substr(0, v1 = 3 - (v2 = feature.get('name').toString().length % 3)) + feature.get('name')).replace(/(.{3})/g, ".$1").substr(v1 + (v2 ? 1 : 2)));
					style_hauptint.getText().setRotation(feature.get('rot'));				
					style_hauptint.getText().setTextAlign(feature.get('textAlign'));
					style_hauptint.getStroke().setWidth(feature.get('lwidth'));					
					return style_hauptint;
				  }
				})
			
			etraxmap.addLayer(hauptint_vector);
			hauptint_vector.setZIndex(0);
			//etraxmap.getLayers().setAt(0, hauptint_vector);
			//sauberer Version neu ende
			
	
	}	
	//+++++ Grid PT ++++++ Ende
	//****************************************************
	
	
	//user filtern
	jQuery('#mfilter').keyup(function(){
	   var valThis = jQuery(this).val().toLowerCase();
		if(valThis == ""){
			jQuery('.contactlist > li').show();           
		} else {
			jQuery('.contactlist > li').each(function(){
				var text = jQuery(this).text().toLowerCase();
				(text.indexOf(valThis) >= 0) ? jQuery(this).show() : jQuery(this).hide();
			});
	   };
	});
			
	jQuery(".filtering").keyup(function(){
		var target = jQuery(this).attr("data-target");
		var valThis = jQuery(this).val().toLowerCase();
		if(valThis == ""){
			jQuery(target).show();           
		} else {
			jQuery(target).each(function(){
				var text = jQuery(this).text().toLowerCase();
				(text.indexOf(valThis) >= 0) ? jQuery(this).show() : jQuery(this).hide();
			});
		};
	});
	
	
	jQuery('body').on('click','.closepopover',function(e){
		e.preventDefault();
		jQuery(this).closest('.popover.show').popover('dispose');
	});
	
	// Protokoll
	// Funkprotokoll laden
	let Funkprotokoll_laden = function(data){
		jQuery("#funkprotokolliert").html("");
		let spruch_data = JSON.parse(data);
		jQuery(spruch_data).each(function(i,val){
			if(val.data[0].type.indexOf('funk') !== -1 && val.data[0].oid == oid){
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
	
	jQuery('body').on('click','.nav-link.protokoll',function() {
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
		database_call('settings','update','json_update','protokoll',{EID: eid},that.attr('data-id'), {type: 'funk,protokoll'},Protokoll_neu_laden,false);
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
	jQuery(".sortliste").keyup(function(){
	   var valThis = jQuery(this).val().toLowerCase();
	   var that = jQuery(this).attr("data-listID");
		if(valThis == ""){
			jQuery(that+" > li").show();           
		} else {
			jQuery(that+" > li").each(function(){
				var text = jQuery(this).text().toLowerCase();
				(text.indexOf(valThis) >= 0) ? jQuery(this).show() : jQuery(this).hide();
			});
	   };
	});
	
	// BOS/APP Pushbenachrichtigungen
	let read_messages = function(){
		let Pushnachrichten_anzeigen = function(data){
			let message = "";
			let notification_title = "";
			let notification_text = "";
			let message_data = JSON.parse(data);
			jQuery(message_data).each(function(i,val){
					if(!val.data[0].read && val.data[0].oid == oid){
						message += "<div id='bosmessage-"+val.data[0].id+"' class='bos-message mb-2'>";
						message += "<div class='h4'>"+val.data[0].betreff+"</div>";
						message += "<div class='text'>"+val.data[0].text+"</div>";
						message += "<div class='small'>"+val.data[0].zeit+"</div>";
						message += '<button type="button" data-id="'+val.id+'" data-name="'+val.data[0].name+'" class="btn btn-success markasread">gelesen</button>';
						message += '<div class="clearfix border-bottom mt-2"></div>';
						message += "</div>";
						notification_title = val.data[0].kurztext;
						notification_text += val.data[0].name+" [TEL:"+val.data[0].phone+", BOS:"+val.data[0].bos+"] "+val.data[0].text+" um "+val.data[0].zeit;
					}
			});
			
			jQuery('#alertdiv').html(message);
			if(message){
				jQuery('.alertmodal').modal('show');
				POIs_anzeigen();
			}else{
				jQuery('.alertmodal').modal('hide');
			}
		}
		jQuery.ajax({
			url: root+readpath+"readstatus.php",
			type: "post",
			data: {
				EID: eid,
				trackstart: trackstart,
				UID: uid
			}
		}).done(function(){
			database_call('settings','read','json','protokoll',{EID: eid},'','',Pushnachrichten_anzeigen,true);
		});
	}
	
	read_messages();
	
	if(!anreise){
		setInterval(function(){read_messages()},alertloading);
	}
	
	let Nachricht_als_gelesen_markieren = function(that){
		let message = $('bosmessage-'+that.attr('data-id')).html();
		database_call('settings','update','json_update','protokoll',{EID: eid},that.attr('data-id'),{'read': true},read_messages,false);
	}
		
	jQuery(".alertmodal #alertdiv").on("click",".markasread",function () {
		Nachricht_als_gelesen_markieren($(this));
	});
	
	//Adresssuche
	if(googleapikey == 'undefined' && googleapikey == ''){
		jQuery('.nav-link.addresse').parent().hide();
	}

	jQuery('.nav-item').on('click','.nav-link.addresse',function(){
		var mapcenter = toDMG(etraxmap.getView().getCenter());
		var mapcenterUTM = toUTM(etraxmap.getView().getCenter());console.log(etraxmap.getView().getCenter());
		jQuery('.modal.adresssuche .coordsuche-rw').val(mapcenterUTM[0].toFixed(0));
		jQuery('.modal.adresssuche .coordsuche-hw').val(mapcenterUTM[1].toFixed(0));
		jQuery('.modal.adresssuche .coordsuche-lon').val(mapcenter[0].toFixed(3));
		jQuery('.modal.adresssuche .coordsuche-lat').val(mapcenter[1].toFixed(4));
		jQuery(".modal.adresssuche").modal('show');
	});
	
	function Adresssuche_initialize_autocomplete(that) {
		var options = {
			componentRestrictions: {country: 'at'}
		};
		var input = document.getElementById(that);
		var autocomplete = new google.maps.places.Autocomplete(input, options);
	}
	
	let Adressesuch_submit = function(adresse){
		var geocoder = new google.maps.Geocoder();
		geocoder.geocode({ 'address': adresse }, function (result, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				var geocodingAPI = "https://maps.googleapis.com/maps/api/geocode/json?address="+adresse+"&key="+googleapikey+"&sensor=true";
				jQuery.getJSON(geocodingAPI, function (json) {
					etraxmap.getView().setCenter(fromLonLat([parseFloat(json.results[0].geometry.location.lng),parseFloat(json.results[0].geometry.location.lat)]));
				});
				jQuery(".modal.adresssuche").modal("hide");
			}else{
				alert("Fehler bei der Adresssuche");
			}
		});
		return true;
	}
	jQuery(".adressegesucht").focus(function(){
		var id = jQuery(this).attr("id");
		Adresssuche_initialize_autocomplete(id);
	});
	
	jQuery(".adresssuche").on('click','.adressesuchen',function(){
		var adresse = jQuery(".adresssuche #adressegesucht").val();
		Adressesuch_submit(adresse);
		jQuery("#adresssuche").slideToggle();
	});
	
	jQuery(".adresssuche").on('click','.coordsuche',function(){
		if(jQuery(this).hasClass('utm')){
			let coords = UTMtoDMG([parseFloat(jQuery('.modal.adresssuche .coordsuche-rw').val()),parseFloat(jQuery('.modal.adresssuche .coordsuche-hw').val())]);
			console.log(coords);
			centerTo(parseFloat(coords[0]),parseFloat(coords[1]));
		}else if(jQuery(this).hasClass('gm')){
			centerTo(jQuery('.modal.adresssuche .coordsuche-lon').val(),jQuery('.modal.adresssuche .coordsuche-lat').val());
		}else{
			let GLon = parseInt(jQuery('.modal.adresssuche .coordsuche-GLon').val());
			let MLon = parseInt(jQuery('.modal.adresssuche .coordsuche-MLon').val());
			let SLon = parseInt(jQuery('.modal.adresssuche .coordsuche-SLon').val());
			let GLat = parseInt(jQuery('.modal.adresssuche .coordsuche-GLat').val());
			let MLat = parseInt(jQuery('.modal.adresssuche .coordsuche-MLat').val());
			let SLat = parseInt(jQuery('.modal.adresssuche .coordsuche-SLat').val());

			let lon_coords = (((SLon/60) + MLon)/60) + GLon;
			let lat_coords = (((SLat/60) + MLat)/60) + GLat;
			centerTo(lat_coords,lon_coords);
		}
		jQuery('.modal.adresssuche').modal('hide');
	});
	
	//Suchprofil
	jQuery('body').on('click','.suchprofil',function(){
		jQuery(".modal.vermisst").modal('show');
	});
	//PPI setzen
	let PPI_setzen = function(){
		if(PPI){
			PPI.setVisible(false);
			PPIRadius.setVisible(false);
			PPIText.setVisible(false);
		}
		jQuery("body").addClass("ppi");
	}
	jQuery(".set-ppi").click(function() {
		PPI_setzen();
	});
	
	let PPI_neu_setzen = function(){
		location.reload();
	}
	
	etraxmap.on('click', function(event) {
		if(jQuery("body").hasClass("ppi")){
			var newPPIcoords = setnewProjection("EPSG:3857","WGS84",event.coordinate);
			database_call('settings','update','json_update','data',{EID: eid},'',{'ppilat': newPPIcoords[1], 'ppilon': newPPIcoords[0]},PPI_neu_setzen,false);
		}
		
	});
	
	//Einsatzleitung setzen
	let Einsatzleitung_positionieren = function(){
		jQuery("body").addClass("el");
		if(PPI){
			PPI.setVisible(false);
			PPIRadius.setVisible(false);
			PPIText.setVisible(false);
		}
	}
	jQuery(".set-EL").click(function() {
		Einsatzleitung_positionieren();
	});
	
	let Einsatzleitung_neu_positionieren = function(){
		location.reload();
	}
	
	etraxmap.on('click', function(event) {
		if(jQuery("body").hasClass("el")){
			var newELcoords = setnewProjection("EPSG:3857","WGS84",event.coordinate);
			database_call('settings','update','json_update','data',{EID: eid},'',{'elat': newELcoords[1], 'elon': newELcoords[0]},Einsatzleitung_neu_positionieren,false);
		}		
	});
	
	
	// Tracks
	
	jQuery("#Egroups").change(function(){
		var color = jQuery("option:selected",this).attr("data-color");
		var gruppe = jQuery("option:selected",this).val();
		jQuery("#gpxcolor").val(jQuery("option:selected",this).attr("data-color"));
		jQuery("#gpxgruppe").val(jQuery("option:selected",this).val());
	});

	// Tracks anzeigen
	
	jQuery('#mainNav').on('click','.usertracks',function(){
		jQuery(".modal.usertracks").modal('show');
	});

	function zoomtotrack(tname){
			var tname = source.getFeatures()[0];
			var polygon = /** @type {module:ol/geom/SimpleGeometry~SimpleGeometry} */ (tname.getGeometry());
			view.fit(polygon, {padding: [170, 50, 30, 150]});
	}


	

	//Alle Tracks einer Gruppe ausblenden
	jQuery('.usertracks #tracklist').on('click','.hiding_all', function(evt){
		evt.preventDefault();
		let e = jQuery(this),
			collapse = e.attr('data-collapsid');
		console.log($(this));
		if(e.hasClass('show')){
			$(collapse + ' .hiding.show').each(function(){
				$(this).removeClass('show').attr('data-original-title','Track einblenden').find('i').html('check_box_outline_blank');
			});
			e.removeClass('show').attr('data-original-title','Track einblenden').find('i').html('check_box_outline_blank');
		}else{
			$(collapse + ' .hiding').each(function(){
				$(this).addClass('show').attr('data-original-title','Track ausblenden').find('i').html('check_box');
			});
			e.addClass('show').attr('data-original-title','Track ausblenden').find('i').html('check_box');
		}
		e.tooltip('hide');
	});

	//Tracks ausblenden
	jQuery('.usertracks #tracklist').on('click','.hiding', function(){
		let e = jQuery(this);
		if(e.hasClass('show')){
			e.removeClass('show').attr('data-original-title','Track einblenden').find('i').html('check_box_outline_blank');
		}else{
			e.addClass('show').attr('data-original-title','Track ausblenden').find('i').html('check_box');
		}
		e.tooltip('hide');
	});



	let toggleTrack = function(){
		let atid,alltrackshidden = [],gid,uid,tid,hiddentracks = [];
		jQuery('.usertracks #tracklist .hiding_all').each(function(i){
			atid = jQuery(this).attr('id');
			if(!jQuery(this).hasClass('show')){
				let aht = alltrackshidden.push(atid);
			}
		});
		jQuery('.usertracks #tracklist .hiding').each(function(i){
			gid = jQuery(this).attr('data-gid');
			uid = jQuery(this).attr('data-uid');
			tid = jQuery(this).attr('data-id');
			if(!jQuery(this).hasClass('show')){
				let ht = hiddentracks.push(tid);
			}
		});
		jQuery.ajax({
			url: root+writepath+"updatesession.php",
			type: "post",
			data: {
				sessionname: "hiddentracks",
				sessionval: hiddentracks
			}
		}).done(function(e) {
			sessionStorage.setItem("hiddentracks", hiddentracks);
			sessionStorage.setItem("alltrackshidden", alltrackshidden);
			Tracks_einlesen();
			location.reload();
		});
	}

	jQuery('.usertracks').on('click','.trackselect', function(){
		toggleTrack();
	});
	
	// GPX Import Start
	jQuery('#mainNav').on('click','.importtracks',function(){
		Trackimport_Mitglieder_laden();
	});
	
	let Trackimport_Mitglieder_laden = function(){
		database_call('settings','read','json','personen_im_einsatz',{EID: eid},'','',aktive_Mitglieder_anzeigen,true);
	}

	let aktive_Mitglieder_anzeigen = function(data){
		var trackeroptions = "<option value='' selected>Sender auswählen!</option>";
		jQuery(".modal.trackimport").modal('show');
		let mitglied = JSON.parse(data);
		jQuery.each(mitglied, function(i,val){
			if(val.typ !== 'material'){
				trackeroptions += "<option value='"+val.UID+"' data-oid='"+val.OID+"' data-dienstnummer='"+val.dienstnummer+"'>"+val.name+"</option>";
			}
			
		});
		jQuery("#gpxsender").html(trackeroptions);
		Trackimport_Gruppen_laden();
	}
	
	let Trackimport_Gruppen_laden = function(){
		database_call('settings','read','json','gruppen',{EID: eid},'','',Trackimport_Gruppen_anzeigen,true);
	}

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
	
	let GPS_Track_importieren = function(){
		if(jQuery("#gpxfile").val() && jQuery("#gpxgruppe").val()){
			  let file_data = jQuery('#gpxfile').prop('files')[0];
			  let form_data = new FormData();
			  let UID = jQuery("#gpxsender").val();
			  form_data.append("file", file_data);
			  form_data.append("EID", eid);
			  form_data.append("gpxsender", jQuery("#gpxsender option:selected").text());
			  form_data.append("gpxsenderUID", UID);
			  form_data.append("gpxsenderOID", jQuery("#gpxsender option:selected").attr('data-oid'));
			  form_data.append("gpxsenderDNR", jQuery("#gpxsender option:selected").attr('data-dienstnummer'));
			  form_data.append("gpxgruppe", jQuery("#gpxgruppe").val());
			  
			  jQuery.ajax({
				type: 'POST',          
				url: root+writepath+"trackimport.php",
				dataType: 'script',
				cache: false,
				processData: false,
				contentType: false,
				data: form_data,
				beforeSend: function(){
					jQuery('.modal.messanger h5').html('Trackdaten werden geladen!');
					jQuery(".modal.messanger").modal("show");
				},
				complete: function(){
					jQuery('.modal.messanger h5').html('Trackdaten wurden hochgeladen!');
					jQuery(".modal.messanger").modal("show");
				},
				success: function () {
					jQuery('.modal.messanger h5').html('Track gespeichert!');
					jQuery(".modal.messanger").modal("show").delay("500");
				},
				error: function (error) {
					jQuery('.modal.messanger h5').html('Ein Fehler ist aufgetreten:'+error);
					jQuery(".modal.messanger").modal("show");
				}
			  }).done(function(e){
					jQuery(".modal.messanger").modal("hide");
					jQuery(".modal.trackimport").modal("hide");
					database_call('settings','update','json_update','personen_im_einsatz',{EID: eid},UID,{sender: "active"},Tracks_einlesen,false);
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
	// GPX Import Ende

	//Einstellen der Trackstärke
	jQuery('#strokewidth').val(strokewidth).change();

	jQuery('body').on('click','.tracksettings',function(){
		jQuery(".modal.tracksettings").modal('show');
	});

	let Usertrack_Settings_aendern = function(that){
		var sessionname = that.attr("data-session");
		var value = jQuery("#strokewidth").val();
		jQuery.ajax({
			url: root+writepath+"updatesession.php",
			type: "post",
			data: {
				sessionname: sessionname,
				sessionval: value
			}
		}).done(function(e) {
			jQuery(".modal.tracksettings").modal("hide");
			location.reload();
		});
	}
	
	jQuery('body').on('click','.savetracksettings',function(e){
		Usertrack_Settings_aendern(jQuery(this));
	});
	
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

	// mapinfo actions

	let is_dragging = function() {
		jQuery(document).mousemove(function(e){
			jQuery('.dragging #mapInfo').css({'top':e.pageY - 24,'left':e.pageX - 107});
		});
	}

	jQuery('body').on('click','#mapInfo.floating .move',function(e){
		if(jQuery('body').hasClass('dragging')){
			jQuery('body').removeClass('dragging');
			jQuery('#mapInfo.floating .move').css('cursor','pointer');
			jQuery('#mapInfo').css({'top':e.pageY - 24,'left':e.pageX - 107});
		}else{
			jQuery('body').addClass('dragging');
			jQuery('#mapInfo.floating .move').css('cursor','move');
			is_dragging();
		}
	});

	jQuery('body').on('click','#mapInfo .menu .move',function() {
		jQuery('#mapInfo').addClass('floating');
	});
	jQuery('body').on('click','#mapInfo .menu .fixed',function() {
		jQuery('#mapInfo').removeClass('floating').removeAttr('style');
	});
	
	// Navigation
	
	jQuery('.navbar-nav a').not('.dropdown-toggle').click(function(){
		jQuery('.navbar-collapse').collapse('hide');
	});
	
	//Document title
	jQuery(document).attr("title", einsatz + ", eTrax|rescue");

	// Google Api einbau
	function loadScript() {
		var s = document.createElement('script');
		s.src = '//maps.googleapis.com/maps/api/js?key=' + googleapikey +'&libraries=places';
		jQuery("head").append(s);
	}

	loadScript();
		
	//Shortcuts
	jQuery(document).keydown(function(e){
		/*
		if(e.altKey && e.keyCode == 49){// alt + 1
			base.setSource(Object.keys(basiskarten)[0]);
		}
		if(e.altKey && e.keyCode == 50){// alt + 2
			base.setSource(bmf);
		}
		if(e.altKey && e.keyCode == 51){// alt + 3
			base.setSource(bmc);
		}
		if(e.altKey && e.keyCode == 52){// alt + 4
			base.setSource(otm);
		}
		if(e.altKey && e.keyCode == 53){// alt + 5
			base.setSource(osm);
		}
		if(e.altKey && e.keyCode == 54){// alt + 6
			base.setSource(bmg);
		}
		*/
		if(e.keyCode == 123){// alt + F12
			jQuery(".modal.messanger").modal('show').find("h5").html("Sorry, damit kann ich nicht dienen!");
		}
		if(e.altKey && e.keyCode == 65){// alt + a
			jQuery(".modal.adresssuche").modal('show');
		}
		if(e.altKey && e.keyCode == 69){// alt + e
			e.preventDefault();
			protokolltext();
		}
		if(e.altKey && e.keyCode == 70){// alt + f
			e.preventDefault();
			FunkProtokoll_neu_laden();
			jQuery(".modal.funkprotokollmodal").modal('show');
		}
		if(e.altKey && e.keyCode == 71){// alt + g
			aktive_Gruppen_anzeigen();
		}
		if(e.altKey && e.keyCode == 73){// alt + i
			trackimporter();
		}
		if(e.altKey && e.keyCode == 76){// alt + l
			Einsatzleitung_positionieren();
		}
		if(e.altKey && e.keyCode == 78){// alt + n
			jQuery("body").addClass("poi");
		}
		if(e.altKey && e.keyCode == 80){// alt + p
			POI_Liste_laden();
		}
		if(e.altKey && e.keyCode == 83){// alt + s
			e.preventDefault();
			Suchgebiete_Modal_anzeigen();
			jQuery('.modal.searchAreas').modal('show');
			jQuery('.start-measure').hide();
		}
		if(e.altKey && e.keyCode == 84){// alt + t
			jQuery(".modal.usertracks").modal('show');
		}
		if(e.altKey && e.keyCode == 85){// alt + u
			jQuery(".modal.mitgliederliste").modal('show');
		}
		if(e.altKey && e.keyCode == 86){// alt + v
			jQuery(".modal.vermisst").modal('show');
		}
		if(e.altKey && e.keyCode == 40){// alt + v
			jQuery(".modal.searchAreas").modal('show');
		}
		if(e.altKey && e.keyCode == 89){// alt + y
			PPI_setzen();
		}
		if(e.altKey && e.keyCode == 90){// alt + z
			centerTo(eX,eY);
		}
		if(e.altKey && e.keyCode == 113){// alt + F2
			//readAreas();
		}
		if(e.altKey && e.keyCode == 187){// alt + +
			var z = etraxmap.getView().getZoom();
			z += 1;
			etraxmap.getView().setZoom(z);
		}
		if(e.altKey && e.keyCode == 189){// alt + -
			var z = etraxmap.getView().getZoom();
			z -= 1;
			etraxmap.getView().setZoom(z);
		}
		if(e.altKey && e.keyCode == 72){
			e.preventDefault();
			hideUsertracks();
		};
	});
}
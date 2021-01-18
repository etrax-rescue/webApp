var eid = "",
g = "gesucht.php",
wp,
rp,
root = '',
rwdb = "read_write_db.php",
Pic = "";

wp = rp = root + "api/";

var Get_Session_vars = function(){
	jQuery.ajax({
		url: root+'api/get_session_in_js.php',
		type: 'post',
		success: function (data) {
			let etrax_session = JSON.parse(data);
			eid = etrax_session.EID;
		}
	});
}
Get_Session_vars();
	
var call_table_settings = function(a,t,c,val,json,callback,return_val){
	var jsondata = (json) ? json : '';
	var jsonval = (val) ? val : '';
	let database = {action: a,type: t,table: 'settings',column: c};
	jQuery.ajax({
		url: wp+rwdb,
		type: "post",
		scriptCharset: "utf-8" ,
		data: {
			database: database,
			select: {
				EID: eid
			},
			values: jsonval,
			json_nodes: jsondata
		}
	}).done(function(data){
		let value = (return_val) ? data : '';
		(callback) ? callback(value) : '';
		
	});
}

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

$(function() {
	$("#cid").change(function() {
		console.log($( this ).val());
		var typetext = eval("typetext"+$( this ).val());
		$(".typbeschreibung").html(typetext).show();
	});

	$(".ausgabe,.eingabe,.abbrechen,#upload_image").hide();

	$('.gesuchtePerson').on('click', '.gesucht-bearbeiten', function () {
		$("#personenbeschreibung select").removeAttr("disabled");
		$("#personenbeschreibung .form-control-plaintext").each(function(){
			$(this).removeAttr("readonly").removeClass("form-control-plaintext").addClass("form-control");
		});
		$(".eingabe,.abbrechen,#upload_image").show();
		$(".gesucht-bearbeiten,.gesucht .schliessen,.gesucht .ausgabe,.gesucht .upload_foto").hide();
	});
	
	$('.gesuchtePerson').on('click','.abbrechen',function(){
		$("#personenbeschreibung select").prop("disabled","disabled");
		$("#personenbeschreibung .form-control").each(function(){
			$(this).attr("readonly","readonly").addClass("form-control-plaintext").removeClass("form-control");
		});
		$("#zielpersonsubmit,.abbrechen,.foto,#upload_image").hide();
		$(".gesucht-bearbeiten,.schliessen").show();
	});
	
	// POI speichern und Bild hochladen
	$('.gesuchtePerson').on('change','#upload_image',function(e){
		uploadIMG(e.target.files, 'img.zielperson');
		$('img.zielperson').removeClass('d-none');
		$('.upload_foto').show();
		$("#upload_image").hide();
	});

	//Beschreibung der Zielperson speichern
	$('.gesuchtePerson').on('click','#zielpersonsubmit',function() {
		$(".ausgabe").show();
		$(".protokoll").hide();

		let json_nodes = {
			suchtyp: ""+$('#cid').val()+"",
			gesuchtname: ""+escapeHTML($('#gname').val())+"",
			gesuchtalter:  ""+escapeHTML($('#galter').val())+"",
			gesuchtgebdatum:  ""+escapeHTML($('#ggebdatum').val())+"",
			gesuchtsvnr:  ""+escapeHTML($('#gsvnr').val())+"",
			gesuchtadresse:  ""+escapeHTML($('#gadresse').val())+"",
			gesuchttelefon:  ""+escapeHTML($('#gtelefon').val())+"",
			gesuchterkrankungen:  ""+escapeHTML($('#gerkrankungen').val())+"",
			gesuchtbeschreibung:  ""+escapeHTML($('#gbeschreibung').val())+"",
			gesuchtbeschreibungextern:  ""+escapeHTML($('#gbeschreibungext').val())+"",
			gesuchtbeschreibungintern:  ""+escapeHTML($('#gbeschreibungint').val())+"",
			alarmiertname:  ""+escapeHTML($('#aname').val())+"",
			alarmierttelefon:  ""+escapeHTML($('#atelefon').val())+"",
			alarmiertdatum:  ""+escapeHTML($('#adatum').val()+" "+$('#azeit').val())+"",
			alarmiertzeit:  ""+escapeHTML($('#azeit').val())+"",
			alarmiertvermisst:  ""+escapeHTML($('#avermisstdate').val())+" "+escapeHTML($('#avermissttime').val())+"",
			kontaktname:  ""+escapeHTML($('#kname').val())+"",
			kontakttelefon:  ""+escapeHTML($('#ktelefon').val())+"",
			kontaktadresse:  ""+escapeHTML($('#kadresse').val())+""
		};
			
		call_table_settings('update','json','gesucht','', json_nodes,update_Suchtyp_in_Data,false);
	});
	
	let update_Suchtyp_in_Data = function(){
		call_table_settings('update','json_update','data','', {'suchtyp': ''+$('#cid').val()+''},update_modal,false);
	}
	
	let update_modal = function(){
		$.ajax({
			type: "POST",
			url: rp+g,
			cache: false,
			dataType: "html",
			success: function(data) {
				$(".gesuchtePerson .modal-body").html(data);
				gesuchte_Person_neu_laden();
			}
		});
	}

	let gesuchte_Person_neu_laden = function(){
		$.ajax({
			url : root+'api/get_image.php?img=/gesucht.jpg',
			dataType:"binary",
			success: function(data){
				const uri = window.URL || window.webkitURL;
				let src = uri.createObjectURL(data);
				$("img.zielperson").attr("src", src);
			}
		});
	}

	$('#personenbild').ajaxForm(function(result) {
		console.log(result);
		gesuchte_Person_neu_laden();
		$('.upload_foto, #upload_image').hide();
	});

	function uploadIMG(src, targetDiv){
		let uploadimage =  src[0];
		if (uploadimage.type.match(/^image\//)) {

		let reader = new FileReader();
			reader.onload = function (e) {
				let image = new Image();
				image.src = e.target.result;
				image.onload = function (imageEvent) {

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
					jQuery(targetDiv).attr('src',Pic);
				}
			}
			reader.readAsDataURL(uploadimage);
		}
	}
	gesuchte_Person_neu_laden();
});

function hasGetUserMedia() {
	// Note: Opera builds are unprefixed.
	return !!(navigator.getUserMedia || navigator.webkitGetUserMedia ||
			navigator.mozGetUserMedia || navigator.msGetUserMedia);
}

if (hasGetUserMedia()) {
	// Good to go!
} else {
	alert('getUserMedia() is not supported in your browser');
}
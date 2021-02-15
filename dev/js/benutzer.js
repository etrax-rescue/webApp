const slsh = "/";
const wp = "api";
const rp = "read";
const rwdb = "read_write_db.php";

let database_call = function(db,a,t,c,s,val,json,callback,return_val){
	var jsonval = (val) ? val : '';
	var jsondata = (json) ? json : '';
	var select = (s) ? s : '';
	let database = {table: db,column: c,action: a,type: t};
	$.ajax({
		url: wp+slsh+rwdb,
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

let eid = "";
let Get_Session_vars = function(){
	$.ajax({
		url: 'api/get_session_in_js.php',
		type: 'post',
		success: function (data) {
			let etrax_session = JSON.parse(data);
			eid = etrax_session.EID;
		}
	});
}
Get_Session_vars();

jQuery(function() {
	
// Mitglied aktivieren und in personen_im_einsatz speichern

	let Mitglied_in_Einsatz_nehmen = function(item){
		item.removeClass("nochimeinsatz")
			.fadeOut()
			.prependTo($("#imEinsatz .members"))
			.removeClass("btn-outline-secondary text-left")
			.addClass("success list-group-item mt-2 w-100 d-flex")
			.fadeIn()
			.find("i").remove();
		item.append('<i class="moveright material-icons ml-auto">arrow_forward</i>');
		item.find(".typcn-warning").remove();
		let hasmoveleft = item.find(".moveleft").length;
		if(hasmoveleft == 0){
			item.prepend('<i class="moveleft material-icons">arrow_back</i>');
		}
		
		//var eid = <?php echo $EID;?>;
		let oldeid = item.attr("data-oldeid");
		let uid = item.attr("data-uid");
		let json_nodes = {
			UID: item.attr("data-uid"),
			OID: item.attr("data-oid"),
			orgname: item.attr("data-orgname"),
			dienstnummer: item.attr("data-dienstnummer"),
			name: item.attr("data-name"),
			phone: item.attr("data-phone"),
			email: item.attr("data-email"),
			bos: item.attr("data-bos"),
			typ: item.attr("data-typ"),
			pause: item.attr("data-pause"),
			sender: item.attr("data-sendet"),
			ausbildungen: item.attr("data-ausbildungen"),
			gruppe:"0",
			status: 3,
			info: "",
			aktivierungszeit: Math.floor(Date.now() / 1000),
			eingerueckt:"",
			inPause:"",
			ausPause:"",
			abgemeldet:""
		};
		let update_settings = function(data){
			if(data){
				let member = JSON.parse(data);
				let m = [],g = [];
				$.each(member, function(i,memberval){
					m.push(memberval.UID);
					g[m] = memberval.gruppe;
				});
				m.includes(uid) ? 
				((g[m] == '' || g[m] == "0" || g[m]  == null) ? database_call('settings','update','json_update','personen_im_einsatz',{EID: eid},uid,{gruppe:"0",status: 3,aktivierungszeit: Math.floor(Date.now() / 1000),abgemeldet:""},'',false) : $('.modal.feedback').modal('show').find('.modal-title').text(item.attr("data-name") + ' ist schon im Dienst und einer Gruppe zugewiesen')) : 
				database_call('settings','update','json_append','personen_im_einsatz',{EID: eid},uid,json_nodes,'',false);
			}
		}
		database_call('user','update','no-json','aktiveEID',{UID: item.attr('data-uid')},'',{aktiveEID: eid},'',false);
		database_call('settings','read','json','personen_im_einsatz',{EID: eid},'','',update_settings,true);
	}

	
	
	// Autologout
	let refresh_Session = function(){
		jQuery.ajax({
			url: 'api/get_session_in_js.php',
			type: 'post',
			success: function (data) {
				if(data == 'loggedout'){
					alert('Aus Sicherheitsgründen wurden sie nach 30 Minuten ohne Aktion automatisch ausgelogged. Sie werden auf die Startseite weitergeleitet')
					window.location.href = 'index.php';
				}
			}
		});
	}

	// Mitglied aus dem Einsatz nehmen und im Table einteilung löschen
	let Mitglied_aus_Einsatz_nehmen = function(target,item) {
		let uid = item.attr("data-uid");
		let oid = item.attr("data-oid");
		let editicon = (oid != 'Material') ? '<i title="Mitglied editieren" class="edituser material-icons">mode_edit</i>' : '';
		if(target == "#members"){
			item.attr("data-oldeid","");
			item.removeClass("btn-outline-success")
				.addClass("mitglied btn-outline-secondary text-left")
				.fadeIn()
				.find("i").remove();
			item.prepend(editicon).append('<i class="moveright material-icons ml-auto">arrow_forward</i>').appendTo($("#collapse"+oid)).fadeIn();
			let json_nodes = {
				status: 11,
				gruppe:"",
				abgemeldet: Math.floor(Date.now() / 1000)
			};
			database_call('user','update','no-json','aktiveEID',{UID: item.attr('data-uid')},'',{aktiveEID: ''},'',false);	
			database_call('settings','update','json_update','personen_im_einsatz',{EID: eid},item.attr('data-uid'),json_nodes,'',false);

			
		}else if(target == "#inPause"){
			item.addClass("btn-outline-danger")
				.removeClass("mitglied btn-outline-success w-100 text-left")
				.fadeOut()
				.find("i").remove();
			item.prepend('<i class="moveleft material-icons">arrow_back</i>')	
				.prependTo($(target+" .members")).fadeIn();
			$("#inPause .members .moveright").hide();
			
			let json_nodes = {
				status: 9,
				inPause: Math.floor(Date.now() / 1000)
			};
			database_call('settings','update','json_update','personen_im_einsatz',{EID: eid},uid,json_nodes,'',false);
			
		}else{
			item.addClass("btn-outline-success w-100")
				.removeClass("mitglied btn-outline-danger")
				.fadeOut()
				.find("i").remove();
			item.append('<i class="moveright material-icons">arrow_forward</i>')
			item.prepend('<i class="moveleft material-icons">arrow_back</i>')
				.find(".pause").remove();
			item.prependTo($(target+" .members")).fadeIn();
			
			let json_nodes = {
				status: "im Dienst",
				ausPause: Math.floor(Date.now() / 1000)
			};
			database_call('settings','update','json_update','personen_im_einsatz',{EID: eid},uid,json_nodes,'',false);
		}
	}

	let locationreload = function(){
		location.reload();
	}

	$("#memberlist").on("click","#members li .moveright",function() {
		$("body").animate({
			"scrollTop": 0
		}, 500);
		var item = $(this).parent();
		item.addClass("choosen");
		$(this).remove();
		Mitglied_in_Einsatz_nehmen(item);
		refresh_Session();
		ImEinsatzcount();
	});

	$(".popuptext").on("click",".deaktivieren",function() {
		$(".popuptext").modal("hide");
		$(".choosen").removeClass(".choosen");
	});
	$(".popuptext").on("click",".aktivieren",function() {
		var item = $("#memberlist").find(".choosen");
		$(".popuptext").modal("hide");
		$(item).remove();
		Mitglied_in_Einsatz_nehmen(item);
		refresh_Session();
	});

	$("#imEinsatz").on("click",".members li .moveleft",function() {
		var item = $(this).parent();
		var target = "#members";
		Mitglied_aus_Einsatz_nehmen(target,item);
		refresh_Session();
		ImEinsatzcount();
	});


	$("#inPause").on("click",".members li .moveleft",function() {
		var item = $(this).parent();
		var target = "#imEinsatz";
		Mitglied_aus_Einsatz_nehmen(target,item);
		refresh_Session();
		ImEinsatzcount();
	});

	$("#imEinsatz").on("click",".members li .moveright",function() {
		var item = $(this).parent();
		var target = "#inPause";
		Mitglied_aus_Einsatz_nehmen(target,item);
		refresh_Session();
		ImEinsatzcount();
	});
					
	$("#imEinsatz").on("click","h4",function(){
		$("#imEinsatz .members").toggle();
	});

	//Laden der aktiven Trackingusers
	//$("#tracker").load( "activetrackinguser.php" )

	//user filtern
	$('body').on('keyup','.listfilter',function(){
		var target = $(this).attr('data-target');
		var valThis = $(this).val();
		if(valThis == ""){
			$(target).show();           
		} else {
			$(target).each(function(){
				var targettext = $(".user",this).text().toLowerCase();
				(targettext.indexOf(valThis) >= 0) ? $(this).addClass('d-flex').removeClass('d-none') : $(this).removeClass('d-flex').addClass('d-none');
			});
		};
	});

	//user filtern
	$('body').on('keyup','#einsatzusersort',function(){
		var valThis = $(this).val().toLowerCase();
		if(valThis == ""){
			$('#imEinsatz .members > li').show();           
		} else {
			$('#imEinsatz .members > li').each(function(){
				var text = $(".user",this).text().toLowerCase();
				(text.indexOf(valThis) >= 0) ? $(this).addClass('d-flex').removeClass('d-none') : $(this).removeClass('d-flex').addClass('d-none');
			});
		};
	});

	//Bearbeitungsscreen aufrufen
	let Mitglied_bearbeiten = function(that){
		$(".changeuser span").hide();
		let uid = that.parent().attr("data-uid");
		let oid = that.parent().attr("data-oid");
		let userID = that.parent().attr("data-dienstnummer");
		let userName = that.parent().attr("data-name");
		let userTyp = that.parent().attr("data-typ");
		let newTyp = '',newPause = '';
		
		$(".changeuser .org-" + oid).show();
		$(".changeuser").modal("show").find("h5").html(userID+" "+userName);
		$("#changeUser").attr('data-userID',userID);
		$("#changeUser .org-" + oid + " .usertyp").val(userTyp).prop('selected', true);
		
		$('#changeUser').on('change','.usertyp',function(){
			newTyp = $("#changeUser .org-" + oid + " .usertyp option:selected").val();
			newPause = (newTyp != "HF") ? 0 : 60;
		});
		$('.changeuser').on('click','.btn-primary',function(){
			console.log(newTyp,newPause);
			$(".changeuser").modal("hide");
			$("#members li[data-uid='"+uid+"']").attr("data-typ",newTyp).attr("data-pause",newPause);
			$("#members li[data-uid='"+uid+"'] .typ").html(newTyp);
		});
	}

	$("#memberlist").on("click",".mitglied i.edituser",function(){
		Mitglied_bearbeiten($(this));
	});

	//und user zurückschicken
	var id = sessionStorage.getItem("edituser");
	if(id){
		if(id == ""){
			$('#members > li').show();           
		} else {
			$('#members > li').each(function(){
				var text = $(this).text().toLowerCase();
				(text.indexOf(id) >= 0) ? $(this).show() : $(this).hide();
				$("#usersort").val(id);
			});
		};
	}


	//Liste neu laden
	$('.userlist').on('click','.material-icons.sync',function(){
		sessionStorage.edituser=("");
		$('#members ul li,.members > li').each(function(){
			$(this).addClass('d-flex').removeClass('d-none');
			$(".sorterinput").val("");
		});
	});
	// Warninginfo anzeigen
	$(".material-icons:contains('error_outline'),.material-icons:contains('info_outline')").mouseenter(function(e){
		var warningwidth = $(".warininginfo").width();
		var warningheight = $(".warininginfo").height();
		$(".warininginfo").html($(this).attr("data-text"));
		$(".warininginfo").css({"left": (e.pageX-warningwidth/2)+"px","top" : (e.pageY-40-warningheight)+"px"}).show();
	}).mouseleave(function(){
		$(".warininginfo").hide();
	});
	// User bearbeiten anzeigen
	$('.userlist').on('keydown','#usersort',function(){
		$("#edituser").show();
	});
	$('.userlist').on('focus','#usersort',function(){
		$(".collapse").collapse('show');
	});
	$('[data-toggle="tooltip"]').tooltip();

	// Anzahl HF, H
	let ImEinsatzcount = function(){
		let all_personal = $('#imEinsatz .members li').length;
		let HFcount = $('#imEinsatz .members li[data-typ="HF"]').length;
		$('#imEinsatz h4 .HFcount').html(HFcount);
		$('#imEinsatz h4 .Hcount').html(all_personal - HFcount);
	}

	let countMembers = setInterval(ImEinsatzcount,60000);
});
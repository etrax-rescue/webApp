
$(function () {	
	//$('.pwdcheck').keyup(function(){
	function checkpwd(pwd, repwd){
		//var pwd = ($(this).val());
		//var repwd = ($('.repwdcheck').val());
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
			if(checkl && checkle && checkc && checkn && checkm && checklogin && checkdnr){
				$('.abschliessen').attr("disabled",false)
				$('.abschliessen').addClass("btn-primary");
				$('.abschliessen').removeClass("btn-secondary");
			} else {
				$('.abschliessen').attr("disabled",true)
				$('.abschliessen').addClass("btn-secondary");
				$('.abschliessen').removeClass("btn-primary");
			}
		} else { //Wenn das Passwortfeld leer ist, dann wird das Passwort nicht geändert
			if(checklogin){
				$('.abschliessen').attr("disabled",false);
			} else {
				$('.abschliessen').attr("disabled",true);
			}
		}
	//});
	};
	
	//$('.repwdcheck').keyup(function(){
	function checkrepwd(pwd,repwd){
		//var pwd = ($('.pwdcheck').val());
		//var repwd = ($(this).val());
		
		if (pwd === repwd) {
			$('.match').removeClass("text-danger");
			$('.match').addClass("text-success");
			var checkrem = true;
		} else {
			$('.match').addClass("text-danger");
			$('.match').removeClass("text-success");
			var checkrem = false;
		}
		
		if(checkrem && checklogin){
			$('.abschliessen').attr("disabled",false)
			$('.abschliessen').addClass("btn-primary");
			$('.abschliessen').removeClass("btn-secondary");
		} else {
			$('.abschliessen').attr("disabled",true)
			$('.abschliessen').addClass("btn-secondary");
			$('.abschliessen').removeClass("btn-primary");
		}
	//});
	};
	$('.pwdcheck').keyup(function(){
		var pwd = ($(this).val());
		var repwd = ($('.repwdcheck').val());
		checkpwd(pwd,repwd);
	});
	
	$('.repwdcheck').keyup(function(){
		var pwd = ($('.pwdcheck').val());
		var repwd = ($(this).val());
		checkrepwd(pwd,repwd);
	});
	$('.pwdcheckadmin').keyup(function(){
		var pwd = ($(this).val());
		var repwd = ($('.repwdcheckadmin').val());
		checkpwd(pwd,repwd);
	});
	
	$('.repwdcheckadmin').keyup(function(){
		var pwd = ($('.pwdcheckadmin').val());
		var repwd = ($(this).val());
		checkrepwd(pwd,repwd);
	});
	
	//Funktion zum Prüfen ob Username schon vorhanden ist
	function checkusername(username,username_old){
		var usernames = ($('#alleusernamen').val());
		
		if (usernames.includes(";")) {
			usernames = usernames.split(";");
		} else {
			usernames = [usernames];
		}
		if (Array.isArray(usernames)) {
			//es gibt den Usernamen
			if (usernames.includes(username) && username != username_old) {
				$('.loginerror').show();
				checklogin = false;
				$('.abschliessen').attr("disabled",true)
				$('.abschliessen').addClass("btn-secondary");
				$('.abschliessen').removeClass("btn-primary");
			} else {
				$('.loginerror').hide();
				checklogin = true;
				if(checkl && checkle && checkc && checkn && checkm && checklogin && checkdnr){
					$('.abschliessen').attr("disabled",false)
					$('.abschliessen').addClass("btn-primary");
					$('.abschliessen').removeClass("btn-secondary");
				} else {
					$('.abschliessen').attr("disabled",true)
					$('.abschliessen').addClass("btn-secondary");
					$('.abschliessen').removeClass("btn-primary");
				}
			}
		}
	};
	
	$('.usernamecheck').keyup(function(){
		var username = ($('#username').val());
		var username_old = ($('.x_username_old').val());
		checkusername(username,username_old);		
	});
	$('.usernamecheckadmin').keyup(function(){
		var username = ($('#y_loginname').val());
		var username_old = ($('.y_username_old').val());
		checkusername(username,username_old);
	});
	
	//Funktion zum Prüfen ob die Dienstnummer schon vorhanden ist
	function checkdienstnummer(dnr,dnr_old){
		var dienstnummern = ($('#alledienstnummern').val());
		
		if (dienstnummern.includes(";")) {
			dienstnummern = dienstnummern.split(";");
		} else {
			dienstnummern = [dienstnummern];
		}
		if (Array.isArray(dienstnummern)) {
			//es gibt den Usernamen
			if ((dienstnummern.includes(dnr) && dnr != dnr_old) || dnr.length == 0) {
				$('.dienstnummererror').show();
				checkdnr = false;
				$('.abschliessen').attr("disabled",true)
				$('.abschliessen').addClass("btn-secondary");
				$('.abschliessen').removeClass("btn-primary");
			} else {
				$('.dienstnummererror').hide();
				checkdnr = true;
				if(checkl && checkle && checkc && checkn && checkm && checklogin && checkdnr){
					$('.abschliessen').attr("disabled",false)
					$('.abschliessen').addClass("btn-primary");
					$('.abschliessen').removeClass("btn-secondary");
				} else {
					$('.abschliessen').attr("disabled",true)
					$('.abschliessen').addClass("btn-secondary");
					$('.abschliessen').removeClass("btn-primary");
				}
			}
		}
	};
	
	$('.dienstnummercheck').keyup(function(){
		var dnr = ($('#dienstnummer').val());
		var dnr_old = ($('.x_dienstnummer_old').val());
		checkdienstnummer(dnr,dnr_old);		
	});
	
});

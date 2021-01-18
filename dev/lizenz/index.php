<?php 
require "../include/startseitentexte.php"; 
?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<title>eTrax|rescue</title>
		<meta charset="UTF-8">
		<meta name="description" content="GPS Einsatztrackingtool">
		<meta name="author" content="Phlipp Toscani & Nicolaus Piso">
		<meta name="creator" content="Nicolaus Piso">
		<meta name="robots" content="noindex,nofollow">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="format-detection" content="telephone=yes">
		<link rel="shortcut icon" href="../img/icon.png" type="image/png">
		<link rel="stylesheet" href="../css/styles.css">
		<script src="../vendor/js/jquery-3.5.1.min.js"></script>
	</head>
	<body class="background modal-open">
	<div id="mama">
		<div class="modal datenschutz show d-block" tabindex="-1" role="dialog" aria-labelledby="datenschutz" aria-hidden="false">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="modal-title" id="datenschutz">Lizenz Hinweise</h1>
					</div>
					<div class="modal-body">
					<?php
						echo $text["license"];
						?>
				</div>
			</div>
		</div>
	</div>
	<script>
	$(function() {
		$("body").on("click",".show_contact",function(){
			var ccase = $(this).data('case');
			var a = "sup";
			var b = "por";
			var c = "t";
			var d = "etra";
			var e = "x.at";
			if(ccase != "Info"){
				$(this).hide();
				$(".contactdata_"+ccase).append("Bei Fragen zum "+ccase+" kontaktieren sie bitte <a href='mailto:"+a+b+c+"@"+d+e+"'>"+a+b+c+"[at]"+d+e+"</a>");
			} else {
				$(".contactdata_about").append("<a href='mailto:"+a+b+c+"@"+d+e+"'>"+a+b+c+"[at]"+d+e+"</a>");
			}
		});
		
		
	});
	</script>
	</body>
</html>
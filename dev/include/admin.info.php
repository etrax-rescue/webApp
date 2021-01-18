<?php
//Infobar für Develper
if(strpos($_SESSION["etrax"]["name"],"Piso")!==false || strpos($_SESSION["etrax"]["name"],"Toscani")!==false){ ?>
	<div class="fixed-bottom">
		<div class="btn-group dropup">
		  <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="min-width: 250px;">
			Developerinfo
		  </button>
		  <div class="dropdown-menu" style="font-size:.7em;min-width: 250px;padding-left:15px;line-height:.75em;">
			<p></p>
			<p><?php echo "EID - Session: ".$EID;?></p>
			<p><?php echo "OID - Session: ".$_SESSION["etrax"]["OID"];?></p>
			<p><?php echo "Userlevel - Session: ".$_SESSION["etrax"]["userlevel"];?></p>
			<p><?php echo "Userrechte - Session: ".$_SESSION["etrax"]["userrechte"];?></p>
			<p><?php echo "FID - Session: ".$_SESSION["etrax"]["FID"];?></p>
			<p><?php echo "UID - Session: ".$_SESSION["etrax"]["UID"];?></p>
			<p><?php echo "usertype - Session: ".$_SESSION["etrax"]["usertype"];?></p>
			<p><?php echo "adminEID - Session: ".$_SESSION["etrax"]["adminEID"];?></p>
			<p><?php echo "adminOID - Session: ".$_SESSION["etrax"]["adminOID"];?></p>
			<p><?php echo "adminID - Session: ".$_SESSION["etrax"]["adminID"];?></p>
			<p><?php echo "name - Session: ".$_SESSION["etrax"]["name"];?></p>
			<p><?php echo "dienstnummer - Session: ".$_SESSION["etrax"]["dienstnummer"];?></p>
			<?php if($_SESSION["etrax"]["etraxadmin"]){ ?>
				<p>etraxadmin - Session: true</p>
			<?php } else { ?>
				<p>etraxadmin - Session: false</p>
			<?php }  ?>
			<?php if($_SESSION["etrax"]["mapadmin"]){ ?>
				<p>mapadmin - Session: true</p>
			<?php } else { ?>
				<p>mapadmin - Session: false</p>
			<?php }  ?>
			<?php if($_SESSION["etrax"]["USER"]["dev"]){ ?>
				<p>USER[dev] - Session: true</p>
			<?php } else { ?>
				<p>USER[dev] - Session: false</p>
			<?php }  ?>
			<?php if($_SESSION["etrax"]["USER"]["einsatzleitung"]){ ?>
				<p>USER[einsatzleitung] - Session: true</p>
			<?php } else { ?>
				<p>USER[einsatzleitung] - Session: false</p>
			<?php }  ?>
			<?php if($_SESSION["etrax"]["USER"]["gleich"]){ ?>
				<p>USER[gleich] - Session: true</p>
			<?php } else { ?>
				<p>USER[gleich] - Session: false</p>
			<?php }  ?>
			<?php if($_SESSION["etrax"]["USER"]["zeichnen"]){ ?>
				<p>USER[zeichnen] - Session: true</p>
			<?php } else { ?>
				<p>USER[zeichnen] - Session: false</p>
			<?php }  ?>
			<?php if($_SESSION["etrax"]["USER"]["zuweisen"]){ ?>
				<p>USER[zuweisen] - Session: true</p>
			<?php } else { ?>
				<p>USER[zuweisen] - Session: false</p>
			<?php }  ?>
			<?php if($_SESSION["etrax"]["USER"]["gleich"]){ ?>
				<p>USER[gleich] - Session: true</p>
			<?php } else { ?>
				<p>USER[gleich] - Session: false</p>
			<?php }  ?>
			<p><?php echo "Flächen Einheit: ".$_SESSION["etrax"]["aunit"];?></p>
			<p><?php echo "Flächen Faktor: ".$_SESSION["etrax"]["afactor"];?></p>
			<p><?php echo "Längen Einheit: ".$_SESSION["etrax"]["lunit"];?></p>
			<p><?php echo "Längen Faktor: ".$_SESSION["etrax"]["lfactor"];?></p>
			</div>
		</div>
		
	</div>
	<?php }	?>
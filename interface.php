<?php
session_start();
include 'php/affichageSelection.php';
/*if(isset($_POST['sauvegarder'])){
	enregistrementFichier($groupe,"");
}
if(!isset($_POST['sauvegarder'])){*/
?>
<html lang="fr">
<?php include 'php/head.php' ?>

<body>
<div class="container" id="app">
	<header class="d-flex flex-column justify-content-center mb-3 pb-2 border-bottom">
		<div class="d-flex flex-row align-items-center mb-3">
			<img alt="FIDOCL" src="images/fidocl.jpg" height="120px"/>	
			<div class="d-flex flex-wrap fs-4 col-md-9 ps-3">Moyennes de croissances pour un groupe d'exploitations</div>
		</div>
		<div class="d-flex flex-row justify-content-center align-items-center mt-3 mb-4">		
			<a class="btn btn-outline-light" href="interfaceCaracteristique.php">Choisir une caracteristique de parcelle</a>
		</div>
		<form method="post" action="interface.php">
			<div class="d-flex flex-row align-items-center justify-content-between mb-3">	
				<img alt="vache" src="images/vache.jpg" height="120px" class="mr-2"/>
				<div class="col-md-5 px-2">
				<label for="choisirGroupe">Choisir un groupe</label>	
				<select class="form-control mt-2" id="choisirGroupe" name="groupe">
				<option value="" <?php if($groupe == ""){ echo 'selected="selected"' ;} ?>>Groupe à choisir</option>
					<optgroup label="Groupes régionaux">
						<?php foreach($groupes["Region"] as $gr) { ?>
						<option value='<?php echo $gr ; ?>' <?php if($groupe == $gr){ echo 'selected="selected"' ;} ?>><?php echo nomGroupe($gr); ?></option>
						<?php } ?>
					</optgroup>
					<optgroup label="Groupes départementaux">
						<?php foreach($groupes["Departement"] as $gr) { ?>
						<option value='<?php echo $gr ; ?>' <?php if($groupe == $gr){ echo 'selected="selected"' ;} ?>><?php echo nomGroupe($gr); ?></option>
						<?php } ?>
					</optgroup>
				</select>
				</div>
				<div class="col-md-5 px-2">
					<div class="infobulle">
						<div class="infobulle-texte" id="aide" style="display:none;">Maintenez Ctrl pour selectionner plusieurs valeurs</div>
					</div>
					<label id="selectionAnnees" for="choisirAnnee" onmouseover="afficher_aide()" onmouseout="afficher_aide()">Choisir une/plusieurs années</label>						
					<select multiple id="choisirAnnee" name="annee[]" class="form-control mt-2" size="3">
						<option value="" <?php if($annee == ""){ echo 'selected="selected"' ;} ?>>Année(s) à choisir</option>
						<?php foreach($annees as $an) { ?>
						<option value='<?php echo $an ; ?>' <?php if(is_array($annee)) {if(in_array($an,$annee)){ echo 'selected="selected"' ;}} ?>><?php echo $an; ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="d-flex flex-row justify-content-center align-items-center">		
				<button type="submit" class="btn btn-dark" name="envoi" value="Afficher résultats">Afficher résultats</button>
			</div>
		</form>	
	</header>	

	<?php if($groupe!=="") { ?>
	<div class="d-flex justify-content-center align-items-center mb-3">
		<a class="btn btn-secondary" href="fichierInter.xlsx" download="Moyennes croissances <?php echo $groupe ?>">Sauvegarder résultats</a>
		<!--<form method="post" action="interface.php">
			<button type="submit" class="btn btn-secondary" name="sauvegarder" value="Enregistrer fichier Excel">Sauvegarder résultats</button>
		</form>-->
	</div>
	<ul class="nav nav-tabs">
		<li class="nav-item"><a class="nav-link active" href="interface.php">Affichage par semaines</a></li>
		<li class="nav-item"><a a class="nav-link" href="interfaceDecade.php">Affichage par décades</a></li>
	</ul>
	<?php } ?>	
		
	<div class="mt-3" id="tableau">	
	<?php
//}
		if(isset($_SESSION["Decade"]) && isset($_SESSION["groupe"])){
			unset($_SESSION["Decade"]);
			$fichierFinal=lireFichier('fichierInter.xlsx',false);
			$tabAffiche;
			for($i=0; $i<$fichierFinal->getSheetCount();$i+=2){
				$tabAffiche[]=$i;
			}
			afficheFichier($fichierFinal,$tabAffiche,false);
		}
		
		
		else if($groupe !== "" && $annee !== ""){
			$exploitations=selectionExploitations('./Ressources/LISTE_EXPLOITATIONS.xlsx',$groupe);
			//var_dump($exploitations);
			$fichiers=selectionFichier($annee);
			//var_dump($fichiers);
			$fichierInter=rassembleFichiers($fichiers, $exploitations, null, null);
			//var_dump($fichierInter);
			$fichierFinal=calculMoyenne($fichierInter,$groupe,null);

			$tabAffiche;
			for($i=0; $i<$fichierFinal->getSheetCount();$i+=2){
				$tabAffiche[]=$i;
			}
			afficheFichier($fichierFinal,$tabAffiche,false);
			//La partie du dessous je peux la mettre dans afficheFichier non ?
			$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($fichierFinal);
			$writer->save("fichierInter.xlsx");
		}?>
    </div>
</div>
<script src="js/fonctions.js"></script>		
</body>
</html>
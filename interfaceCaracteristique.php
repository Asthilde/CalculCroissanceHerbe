<?php
session_start();
include 'php/affichageSelection.php';
/*if(isset($_POST['sauvegarder'])){
	enregistrementFichier("",$caracteristique);
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
			<div class="d-flex flex-wrap fs-4 col-md-9 ps-3">Moyennes de croissances pour une caractéristique de parcelles</div>
		</div>
        <div class="d-flex flex-row justify-content-center align-items-center mt-3 mb-4">		
			<a class="btn btn-outline-light" href="interface.php">Choisir un groupe d'exploitations</a>
		</div>
		<form method="post" action="interfaceCaracteristique.php">
			<div class="d-flex flex-row align-items-center justify-content-between mb-3">	
                <img alt="parcelle" src="images/parcelle.jpg" height="120px" class="mr-2"/>
				<div class="col-md-6 px-2">
                    <div class="infobulle">
						<div class="infobulle-texte" id="aide" style="display:none;">Maintenez Ctrl pour selectionner plusieurs valeurs</div>
					</div>
                    <label id="selectionCaracteristiques" for="choisirCaracteristique" onmouseover="afficher_aide()" onmouseout="afficher_aide()">Choisir une/plusieurs caractéristiques</label>	
					<select multiple class="form-control mt-2" id="choisirCaracteristique" for="choisirCaracteristiques" name="caracteristique[]">
						<option value="" <?php if($caracteristique == ""){ echo 'selected="selected"' ;} ?>>Caractéristique(s) de parcelle à choisir</option>
						<?php foreach($caracteristiques as $carac) { ?>
						<option value='<?php echo $carac ; ?>' <?php if(is_array($caracteristique)){if(in_array($carac,$caracteristique)){ echo 'selected="selected"' ;}} ?>><?php echo $carac; ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="col-md-4 px-2">
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

	<?php if($caracteristique!=="") { ?>
	<div class="d-flex justify-content-center align-items-center mb-3">
		<a class="btn btn-secondary" href="fichierInter2.xlsx" download="Moyennes croissances parcelles <?php echo $caracteristique[0] ?>">Sauvegarder résultats</a>	
		<!--<form method="post" action="interface.php">
			<button type="submit" class="btn btn-secondary" name="sauvegarder" value="Enregistrer fichier Excel">Sauvegarder résultats</button>
		</form>-->
	</div>
	<ul class="nav nav-tabs">
		<li class="nav-item"><a class="nav-link active" href="interfaceCaracteristique.php">Affichage par semaines</a></li>
		<li class="nav-item"><a a class="nav-link" href="interfaceCaracteristiqueDecade.php">Affichage par décades</a></li>
	</ul>
	<?php } ?>	
		
	<div class="mt-3" id="tableau">	
	<?php
//}
	//Faire la page version Decade et vérifier l'envoi des infos + problèmes éventuels liés au passage de caractéristiques à groupe
	//var_dump($caracteristique);
		if(isset($_SESSION["Decade"]) && isset($_SESSION["caracteristique"])){
			unset($_SESSION["Decade"]);
			$fichierFinal=lireFichier('fichierInter.xlsx',false);
			$tabAffiche;
			for($i=0; $i<$fichierFinal->getSheetCount();$i+=2){
				$tabAffiche[]=$i;
			}
			afficheFichier($fichierFinal,$tabAffiche,false);
		}
		
		else if($annee !== "" && $caracteristique != ""){
			$fichiers=selectionFichier($annee);
			//Pour le moment une seule caractéristique prise en compte !
			foreach($caracteristique as $caracChoisie){
				$parcelles = categorisationParcelles("ExtractionDonneesCheptel_L070-FR-38523088_2022-12-18.xlsx",$caracChoisie);
				$fichierInter = trouverMesuresParcelles($fichiers,$parcelles);
				$fichierFinal = calculMoyenneParcelles($fichierInter, $caracChoisie);
			}
			
			$tabAffiche;
			for($i=0; $i<$fichierFinal->getSheetCount();$i+=2){
				$tabAffiche[]=$i;
			}
			
			afficheFichier($fichierFinal,$tabAffiche,true);
			//La partie du dessous je peux la mettre dans afficheFichier non ?
			$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($fichierFinal);
			$writer->save("fichierInter2.xlsx");
		}?>
    </div>
</div>
<script src="js/fonctions.js"></script>		
</body>
</html>
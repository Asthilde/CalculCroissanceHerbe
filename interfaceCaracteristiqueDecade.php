<?php
session_start();
include 'php/affichageSelection.php';
if(isset($_SESSION['caracteristique'])) {
	$caracteristique=$_SESSION['caracteristique'];
	$_SESSION["Decade"] = true;
}
else{
	$caracteristique="";
}
if(isset($_SESSION['annee'])) {
	$annee=$_SESSION['annee'];
	$_SESSION["Decade"] = true;
}
else{
	$annee="";
}
if($caracteristique!=="" && $annee!==""){
	$fichierFinal=lireFichier('fichierInter2.xlsx',false);
}
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
		<div class="d-flex flex-row align-items-center justify-content-between mb-3">	
			<img alt="vache" src="images/vache.jpg" height="120px" class="mr-2"/>
			<div class="col-md-8 px-2">
			<p class="fs-5 pb-1">Caractéristique selectionnée : 
                <?php if($caracteristique !==""){ 
					foreach($caracteristique as $carac) { echo $carac.' ';}} 
					else {echo "";} ?></p>
			<p class="fs-5">Année(s) selectionnée(s) : 
				<?php if($annee !==""){ 
					foreach($annee as $an) { echo $an.' ';}} 
					else {echo "";} ?></p>
		</div>
	</header>	

	<?php if($caracteristique!=="") { ?>
	<div class="d-flex justify-content-center align-items-center mb-3">
		<a class="btn btn-secondary" href="fichierInter2.xlsx" download="Moyennes croissances parcelles <?php echo $caracteristique[0] ?>">Sauvegarder résultats</a>	
		<!--<form method="post" action="interface.php">
			<button type="submit" class="btn btn-secondary" name="sauvegarder" value="Enregistrer fichier Excel">Sauvegarder résultats</button>
		</form>-->
	</div>
	<ul class="nav nav-tabs">
		<li class="nav-item"><a class="nav-link" href="interfaceCaracteristique.php">Affichage par semaines</a></li>
		<li class="nav-item"><a a class="nav-link active" href="interfaceCaracteristiqueDecade.php">Affichage par décades</a></li>
	</ul>
	<?php } ?>	

    <div class="mt-3" id="tableau">	
        <?php
		if(isset($fichierFinal)){
			$tabAffiche;
			for($i=1; $i<$fichierFinal->getSheetCount();$i+=2){
				$tabAffiche[]=$i;
			}
			afficheFichier($fichierFinal,$tabAffiche,false);
		}
		else{
			echo 'Aucune caractéristique ni aucune année n\'ont été selectionnées. Veuillez retourner sur <a href="interfaceCaracteristique.php"> l\'affichage par semaine. </a>';
		}?>
    </div>
</div>
</body>
</html>
<?php
include './fonctions.php';
$groupes=array("Region" => array(), "Departement" => array());
$annees=array();
$caracteristiques=array();
if(isset($_POST['groupe'])){
	$groupe=$_POST['groupe'];
	setcookie('gr',$groupe,time()+3600);
	$_SESSION["groupe"] = $_POST['groupe'];
}
else if(isset($_COOKIE['gr'])){
	$_SESSION["groupe"] = $_COOKIE['gr'];
	$groupe=$_COOKIE['gr'];
}
else {
	$groupe="";
}
if(isset($_POST['annee'])){
	$annee=$_POST['annee'];
	foreach($annee as $pos => $an){
		setcookie('an'.$pos,$an,time()+3600);
	}
	$_SESSION["annee"] = $_POST['annee'];
}
else if(isset($_COOKIE['an0'])){
	$tab=array($_COOKIE['an0']);
	for($i=1; $i<10; $i++) {
		if(isset($_COOKIE['an'.$i])){
			$tab[]=$_COOKIE['an'.$i];
		}
	}
	$_SESSION["annee"] = $tab;
	$annee=$tab;
}
else {
	$annee="";
}
if(isset($_POST['caracteristique']) && !$_POST['caracteristique'][0]==""){
	$caracteristique=$_POST['caracteristique'];
	foreach($caracteristique as $pos => $carac){
		setcookie('carac'.$pos,$carac,time()+3600);
	}
	$_SESSION["caracteristique"] = $_POST['caracteristique'];
}
else if(isset($_COOKIE['carac0'])){
	$tab1=array($_COOKIE['carac0']);
	for($i=1; $i<10; $i++) {
		if(isset($_COOKIE['carac'.$i])){
			$tab1[]=$_COOKIE['carac'.$i];
		}
	}
	$_SESSION["caracteristique"] = $tab1;
	$caracteristique=$tab1;
}
else {
	$caracteristique="";
}
if($annee==="" && $groupe===""){ 
	session_unset();
	session_destroy();    
}
//On regarde si le fichier LISTE_EXPLOITATIONS a été modifié il y a 10 minutes ou moins
if(!isset($_COOKIE["Groupe_0"]) || filemtime('./Ressources/LISTE_EXPLOITATIONS.xlsx')>time()-36000){
	$groupes=affichageGroupes();
}
else if(isset($_COOKIE["Groupe_0"])) {
	foreach($_COOKIE as $cookie => $valeur){
		if(str_contains($cookie,"SousGroupe_")){
			$groupes["Departement"][] = $_COOKIE[$cookie];
		}
		else if(str_contains($cookie,"Groupe_")){
			$groupes["Region"][] = $_COOKIE[$cookie];
		}
	}
	asort($groupes["Region"]);
    asort($groupes["Departement"]);
}
if(!isset($_COOKIE["Annee_0"]) || filemtime('./Ressources')>time()-36000) {
	$annees=affichageAnnees();
}
else if(isset($_COOKIE["Annee_0"])){
	foreach($_COOKIE as $cookie => $valeur){
		if(str_contains($cookie,"Annee_")){
			$annees[] = $valeur;
		}
	}
	asort($annees);
}
//Penser à remodifier quand doc final avec la liste des exploitations obtenu
if(!isset($_COOKIE["Carac_0"]) || filemtime('./Ressources/ExtractionDonneesCheptel_L070-FR-38523088_2022-12-18.xlsx')>time()-36000) {
	$caracteristiques=affichageCaracteristiques();
}
else if(isset($_COOKIE["Carac_0"])){
	foreach($_COOKIE as $cookie => $valeur){
		if(str_contains($cookie,"Carac_")){
			$caracteristiques[] = $valeur;
		}
	}
	asort($caracteristiques);
}
?>
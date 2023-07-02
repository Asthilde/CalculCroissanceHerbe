<?php
require './vendor/autoload.php';
require './fonctions.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;


/*Test affichageGroupes
$tabgroupes=affichageGroupes();
var_dump($tabgroupes);*/

/*Test fonction filemtime
if(filemtime('./Ressources/LISTE_EXPLOITATIONS.xlsx') > time()-60){
    echo "true";
}
else {
    echo "false";
}*/

/*Test affichageAnnees
//var_dump(affichageAnnees());
if(filemtime('./Ressources')<time()-36000) {
    echo "true";
}
else {
    echo "false";
}*/

/* Test filtre de lecture des colonnes d'un fichier Excel 
$spreadsheet=lireFichier('./Ressources/Croissances 2019 ADICE.xlsx',true);
$writer = IOFactory::createWriter($spreadsheet, 'Html');
$message = $writer->save('php://output');
echo $message;
*/

/*Test selectionFichiers dans dossier Ressources avec une année
$tabFichiers=selectionFichier(2020);
var_dump($tabFichiers);*/


/*Test pour mettre dans un tableau associatif à 2 dimensions les fichiers selon année
$tab = array ();
$tab1 = array();
$tab["cle1"]=array("val1");
var_dump($tab);
$case=$tab["cle1"];
$case[]="val2";
$tab["cle1"]=$case;
var_dump($tab);
selectionFichier('./Ressources');
*/

/*Test nomGroupe
//$groupe="Groupe_Coteaux séchants";
$groupe="SousGroupe_Monts du Beaujolais";
$nom=nomGroupe($groupe);
//echo $nom;*/

/* Test pour transformer colonne en valeur numérique
$val = chr(2 + ord('A') - 1);
$valSuiv = chr(3+ ord('A') -1);
echo $val.$valSuiv;*/

/*Test selectionExploitations
$groupe="SousGroupe_Monts du Lyonnais";
$tabExploit=selectionExploitations('./Ressources/LISTE_EXPLOITATIONS.xlsx',$groupe);
var_dump($tabExploit);
//Test combiné
$tabExploit=selectionExploitations('./Ressources/LISTE_EXPLOITATIONS.xlsx',$_POST['groupe']);
var_dump($tabExploit);*/


/*Test de calculCroissance
$croissance = calculCroissance('19/06/2020','25/06/2020',8.4,10.3);
$croissance = calculCroissance('19/06/2020','19/06/2020',8.4,10.3);
$croissance = calculCroissance('19/06/2020','25/06/2020',9.5,6.3);
echo $croissance;*/

/*Test de rassembleFichier
$groupe="SousGroupe_Monts du Lyonnais";
$exploitations=selectionExploitations('./Ressources/LISTE_EXPLOITATIONS.xlsx',$groupe);
$fichiers=selectionFichier('./Ressources');
$spreadsheet=rassembleFichiers($fichiers, $exploitations);
$writer = IOFactory::createWriter($spreadsheet, 'Html');
$message = $writer->save('php://output');
echo $message;
*/

/*Test de triDates
$tab=array('12/08/2020','11/08/2020','20/09/2020','19/03/2020');
echo triDates($tab);
*/

/*Test pour comparaisonDates
$dateDebut="01/03/2019";
$date="02/02/2019";
$dateFin="07/03/2019";
echo comparaisonDate($dateDebut, $dateFin, $date);
*/

/*Test pour jourFinPeriode
$date="02/02/2019";
$date="29/12/2019";
$date="27/02/2020";
echo $date.' - '.jourFinPeriode($date,7);
*/

/*Test pour concatTab
$tab1=array('12/05/2019' => array('FR38550630'));
$tab2=array('12/05/2019' => array('FR26000001'))
var_dump(concatTab($tab1, $tab2));*/

/*Test pour calculPeriode et calculMoyenne
$tab=array("01/02/2019" => array(50,6), "03/02/2019" => array(10,1), "09/02/2019" => array(30,2));
var_dump(calculPeriode($tab,7));

$groupe="SousGroupe_Coteaux";
$exploitations=selectionExploitations('./Ressources/LISTE_EXPLOITATIONS.xlsx',$groupe);
$fichiers=selectionFichier('./Ressources');
$fInter=rassembleFichiers($fichiers, $exploitations);
$fichierFinal=calculMoyenne($fInter,$groupe);
$writer = IOFactory::createWriter($fichierFinal, 'Html');
$message = $writer->save('php://output');
echo $message;
*/

/*Test pour rechercherLigne
var_dump(rechercherLigne(lireFichier('./Ressources/ExtractionDonneesCheptel_L070-FR-38523088_2022-12-18.xlsx',false)->getSheet(0),"Mes parcelles"));*/

/*Test pour rechercherColonne
var_dump(rechercherColonne(lireFichier('./Ressources/ExtractionDonneesCheptel_L070-FR-38523088_2022-12-18.xlsx',false)->getSheet(0),10,"Type"));*/

/*Test pour trouverListeCaracteristiques
var_dump(trouverListeCaracteristiques("ExtractionDonneesCheptel_L070-FR-38523088_2022-12-18.xlsx"));*/

/*Test pour affichageCaracteristiques
var_dump(affichageCaracteristiques());*/

/*Test pour affichageValeursCaracteristique
var_dump(affichageValeursCaracteristique("Hydromorphie Sol"));*/

/*Test pour categorisationParcelles
var_dump(categorisationParcelles("ExtractionDonneesCheptel_L070-FR-38523088_2022-12-18.xlsx","Potentiel de pousse"));
$parcelles = categorisationParcelles("ExtractionDonneesCheptel_L070-FR-38523088_2022-12-18.xlsx","Potentiel de pousse");
foreach($parcelles as $valeurParcelle => $nomParcelle){
    echo $nomParcelle.'-';
}*/

/*Test pour trouverMesuresParcelles*/
$annees=selectionFichier(array("2020"));
$parcelles=categorisationParcelles("ExtractionDonneesCheptel_L070-FR-38523088_2022-12-18.xlsx","Potentiel de pousse");
//var_dump($annees);
//var_dump($parcelles);
//trouverMesuresParcelles($annees,$parcelles);
//echo 'testé';

/*Test pour calculMoyenneParcelle*/
$fichierInter = trouverMesuresParcelles($annees,$parcelles);
$fichierFinal = calculMoyenneParcelles($fichierInter, "Potentiel de pousse");

/*Test pour afficheFichiers*/
$tabAffiche;
for($i=0; $i<$fichierFinal->getSheetCount();$i+=2){
    $tabAffiche[]=$i;
}
?>
<html>
    <body>
<?php
afficheFichier($fichierFinal, $tabAffiche, true);
?>
</body>
</html>
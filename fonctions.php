<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**Légende
 * Pour avoir le 1er sheet, l'indice est 0.
 * Pour avoir la première ligne et colonne d'un fichier, l'indice est 1.
 */

/**
 * Renvoie un mot donné en paramètre avec une majuscule en première lettre et le reste en minuscule
 */
function transformCasse($elem) {
	$elem = substr(ucfirst($elem),0,1) . strtolower(substr($elem,1));
	return $elem;
}

/**
 * Classe permettant de créer un filtre pour lire un fichier Excel
 */
class MonFiltre implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {
    /**
     * Lit un fichier Excel, selon une ligne, une colonne particulière
     * Retourne un tableau de booléen permettant de savoir quelles cases sont à lire
     */
    public function readCell($column, $row, $worksheetName = '')
    {
        $ligneDebut = 2;
        $colonnes = ['A','C','D','F','G','I','J'];
        //  Lecture des colonnes pertinentes : pas le lot, la date médiane et la croissance
        if($row >= $ligneDebut) {
            if (in_array($column,$colonnes)) {
                return true;
            }
        }
        return false;   
    }
}

/**
 * Lit et renvoie un fichier Excel en appliquant ou non un filtre
 * Prend en paramètre le chemin du fichier et un booléen pour savoir si le filtre doit être appliqué
 * Renvoie le fichier Excel sous forme de tableur
 */
function lireFichier($nomFichier, $boolFiltre){
    $filtre = new MonFiltre();
    $inputFileName = $nomFichier;
    $inputFileType = IOFactory::identify($inputFileName);
    $reader = IOFactory::createReader($inputFileType);
    if($boolFiltre) {
        $reader->setReadFilter($filtre);
    }
    $spreadsheet = $reader->load($inputFileName);
    return($spreadsheet);
}

/**
 * Lit un fichier et trouve le numéro de la ligne contenant la case demandée en entrée
 * Prend en paramètre le worksheet correspondant et le nom de la case recherchée
 * Renvoie le numéro de la ligne, sinon renvoie NULL
 */
function rechercherLigne($worksheet, $nomLigne){
    $highestRow = $worksheet->getHighestRow();
    $row=1;
    while($row < $highestRow && trim(($worksheet->getCellByColumnAndRow(1, $row)->getValue())) != $nomLigne){
        $row++;
    }
    if($row < $highestRow){
        return $row;
    }
    return NULL;
}

/**
 * Lit un fichier et trouve le numéro de la colonne contenant la case demandée en entrée
 * Prend en paramètre le worksheet correspondant, le numéro de la ligne de recherche et le nom de la case recherchée
 * Renvoie le numéro de la colonne, sinon renvoie NULL
 */
function rechercherColonne($worksheet, $numLigne, $nomColonne){
    $highestColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($worksheet->getHighestColumn());
    $column=1;
    while($column < $highestColumn && $worksheet->getCellByColumnAndRow($column, $numLigne)->getValue()!==NULL && trim(($worksheet->getCellByColumnAndRow($column, $numLigne)->getValue())) != $nomColonne){
        $column++;
    }
    if($column < $highestColumn){
        return $column;
    }
    return NULL;
}

/**
 * Crée des cookies correspondant aux groupes départementaux et régionaux contenus dans le fichier LISTE_EXPLOITATIONS
 * Renvoie un tableau contenant tous les groupes triés par niveau (région ou département) et par ordre alphabétique
 */
function affichageGroupes() {
    $tabGroupes=array("Region" => array(), "Departement" => array());
    $fichier=lireFichier('./Ressources/LISTE_EXPLOITATIONS.xlsx',false);
    $worksheet=$fichier->getSheet(1);
    $highestRow = $worksheet->getHighestRow();
    for ($row = 2; $row <= $highestRow; ++$row) {
        $groupe = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
        if(!in_array("Groupe_".transformCasse($groupe),$tabGroupes["Region"]) && $groupe!=""){
            $tabGroupes["Region"][] = "Groupe_".transformCasse($groupe);
        }
        $groupe = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
        if(!in_array("SousGroupe_".transformCasse($groupe),$tabGroupes["Departement"]) && $groupe!=""){
            $tabGroupes["Departement"][] = "SousGroupe_".transformCasse($groupe);
        }
    }
    asort($tabGroupes["Region"]);
    asort($tabGroupes["Departement"]);
    for($i=0; $i<count($tabGroupes["Region"])+count($tabGroupes["Departement"]);$i++){
        if($i<count($tabGroupes["Region"])){
            setcookie("Groupe_".$i, $tabGroupes["Region"][$i], time()+2678400);
        }
        else{
            setcookie("SousGroupe_".$i, $tabGroupes["Departement"][$i-count($tabGroupes["Region"])], time()+2678400);
        }
    }
    return($tabGroupes);
}

/**
 * Crée des cookies correspondant aux années dont on a les fichiers de mesures
 * Renvoie un tableau contenant toutes les années pour lesquelles des fichiers de mesures sont disponibles triées par ordre croissant
 */
function affichageAnnees() {
    $rep=opendir('./Ressources');
    $annees=array();
    while($fichier = readdir($rep)) {
        if(stripos($fichier,'Croissances 20') !== false && stripos($fichier,'xlsx') !== false) {
            $annee=substr($fichier,strlen('Croissances '),4);
            if(!in_array($annee, $annees)) {
                $annees[] = $annee;
            }
        }
        }
    closedir($rep);
    $i=0;
    foreach($annees as $an){
        setcookie("Annee_".$i, $an, time()+2678400);
        $i++;
    }
    asort($annees);
    return($annees);
}

//Penser à remodifier quand doc final avec la liste des parcelles obtenu
/**
 * Crée des cookies correspondant aux caractéristiques des parcelles dont on a les fichiers de mesures
 * Renvoie un tableau contenant toutes les caractéristiques pour lesquelles des fichiers de mesures sont disponibles triées par ordre croissant
 */
function affichageCaracteristiques() {
    $caracteristiques=trouverListeCaracteristiques("ExtractionDonneesCheptel_L070-FR-38523088_2022-12-18.xlsx");
    $i=0;
    foreach($caracteristiques as $carac){
        setcookie("Carac_".$i, $carac, time()+2678400);
        $i++;
    }
    return $caracteristiques;
}

//Penser à remodifier quand doc final avec la liste des parcelles obtenu
/**
 * Crée des cookies correspondant aux valeurs de la caractéristique des parcelles choisie
 * Renvoie un tableau contenant toutes les valeurs de la caractéristique choisie pour lesquelles des mesures sont disponibles 
 * Si le nom de la caractéristique n'est pas trouvée, renvoie NULL
 */
function affichageValeursCaracteristique($caracChoisie) {
    $fichier=lireFichier('./Ressources/ExtractionDonneesCheptel_L070-FR-38523088_2022-12-18.xlsx',false);
    $worksheet=$fichier->getSheet(0);
    $highestRow = $worksheet->getHighestRow();
    $row=rechercherLigne($worksheet, "Mes parcelles");
    $row++;
    $column=rechercherColonne($worksheet,$row,$caracChoisie);
    $highestColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($worksheet->getHighestColumn());
    if($column != NULL) {
        $valeurs=array();
        $row++;
        while($row<$highestRow && $worksheet->getCellByColumnAndRow($column, $row)->getValue()!=NULL){
            $valLue=trim(($worksheet->getCellByColumnAndRow($column, $row)->getValue()));
            if(!in_array($valLue,$valeurs)){
                $valeurs[]=$valLue;
            }
            $row++;
        }
    }
    return $valeurs;
}

/**
 * Cherche la liste des caractéristiques des parcelles
 * Prend en paramètre le nom du fichier avec son extension contenant la liste des parcelles et leurs caractéristiques
 * Renvoie un tableau contenant le nom de toutes les caractéristiques
 */
function trouverListeCaracteristiques($nomFichier){
    $fichier=lireFichier('./Ressources/'.$nomFichier,false);
    $worksheet=$fichier->getSheet(0);
    $caracteristiques=array();
    $row=rechercherLigne($worksheet, "Mes parcelles");
    $row++;
    $highestColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($worksheet->getHighestColumn());
    $column = rechercherColonne($worksheet,$row,"Date de fin");
    $column++;
    if($column != NULL && $row != NULL){
        while($column < $highestColumn && $worksheet->getCellByColumnAndRow($column, $row)->getValue()!==NULL){
            $caracteristique=trim($worksheet->getCellByColumnAndRow($column, $row)->getValue());
            if(!in_array($caracteristique,$caracteristiques)){
                $caracteristiques[] = $caracteristique;
            }
            $column++;
        }
    }
    asort($caracteristiques);
    return $caracteristiques;
}

/**
 * Fonction qui renvoie la liste des fichiers Excel correspondant aux années selectionnées
 * Prend en paramètre un tableau des années à afficher
 * Renvoie un tableau associatif avec comme clé l'année et comme valeurs le chemin vers les fichiers correspondants
 */
function selectionFichier($an){
    $rep=opendir('./Ressources');
    $tabFichier=array();
    while($fichier = readdir($rep)) {
        if(stripos($fichier,'Croissances 20') !== false && stripos($fichier,'xlsx') !== false) {
            $annee=substr($fichier,strlen('Croissances 20'),2);
            if(in_array('20'.$annee, $an)) {
            if(array_key_exists($annee, $tabFichier)) {
                $listeFichiers = $tabFichier[$annee];
                $listeFichiers[] = './Ressources/'.$fichier;
                $tabFichier[$annee] = $listeFichiers;
            }
            else {
                $tabFichier[$annee]=array('./Ressources/'.$fichier);
            }
        }
        }
    }
    closedir($rep);
    //var_dump($tabFichier);
    return($tabFichier);
}

/**
 * Donne le nom du groupe ou sous groupe sans son préfixe
 * Prend en paramètre le nom du groupe selectionné dans le formulaire
 * Renvoie le nom du groupe/sous-groupe seul
 */
function nomGroupe($nom){
    if(str_contains($nom, "SousGroupe")){
        return(substr($nom, strlen('SousGroupe_')));
    }
    return(substr($nom, strlen('Groupe_')));
}

/**
 * Selectionne les exploitations qui appartiennent à un groupe/sous-groupe donné
 * Prend en paramètre le chemin du fichier contenant les informations et le groupe choisi
 * Renvoie un tableau contenant les exploitation correspondantes
 */
function selectionExploitations($fichier,$groupe) {
    $spreadsheet=lireFichier($fichier, false);
    $worksheet=$spreadsheet->getSheet(1);
    $highestRow = $worksheet->getHighestRow();
    $listeExploitations = array();
    if(str_contains($groupe, "SousGroupe")){
        $nomGr = substr($groupe, strlen('SousGroupe_'));
        $col = 4;
    }
    else {
        $nomGr = substr($groupe, strlen('Groupe_'));
        $col = 3;
    }
    for ($row = 2; $row <= $highestRow; ++$row) {
        $valeurGroupe = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
        if(stripos($groupe,$valeurGroupe) && strlen($nomGr) == strlen($valeurGroupe)){
            $listeExploitations[]=$worksheet->getCellByColumnAndRow(1, $row)->getValue();
        }
    }
    //var_dump($listeExploitations);
    return($listeExploitations);
}

/**
 * Catégorise les parcelles qui ont une même valeur pour une caractéristique donnée
 * Prend en paramètre le nom du fichier contenant les informations et la caractéristique choisie
 * Renvoie un tableau ayant comme clé les valeurs de la caractéristiques et comme valeur un tableau contenant les parcelles associées
 */
function categorisationParcelles($nomFichier,$caracteristique) {
    $spreadsheet=lireFichier('./Ressources/'.$nomFichier, false);
    $worksheet=$spreadsheet->getSheet(0);
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($worksheet->getHighestColumn());
    $listeValeurs=affichageValeursCaracteristique($caracteristique);
    $row=rechercherLigne($worksheet,"Mes parcelles");
    $row++;
    $columnP=rechercherColonne($worksheet, $row, "Parcelle");    
    $columnC=rechercherColonne($worksheet, $row, $caracteristique); 
    $row++;
    //Parcours des parcelles et rangement de ces dernières par valeurs
    $listeParcelles = array();
    if($listeValeurs!== NULL){
        foreach($listeValeurs as $valeur){
            $listeParcelles[$valeur] = array();
        }
        $nomParcelle = trim(($worksheet->getCellByColumnAndRow($columnP, $row)->getValue()));
        $nomValeur = trim(($worksheet->getCellByColumnAndRow($columnC, $row)->getValue()));
        while($nomParcelle!==NULL && $nomValeur!==NULL && $row<$highestRow) {
            if(in_array($nomValeur,$listeValeurs)){
                $listeParcelles[$nomValeur][]=$nomParcelle;
            }
            $row++;
            $nomParcelle = trim(($worksheet->getCellByColumnAndRow($columnP, $row)->getValue()));
            $nomValeur = trim(($worksheet->getCellByColumnAndRow($columnC, $row)->getValue()));
        }
    }
    return $listeParcelles;
}

/**
 * Rassemble les fichiers contenant les mesures et les exploitations à selectionner et renvoie un tableur contenant les lignes correspondant aux exploitations selectionnées.
 * Prend en paramètre la liste des fichiers à lire et la liste des exploitations à selectionner
 * Renvoie un fichier Excel sous forme de tableur contenant les lignes selectionnées, une feuille de calcul par année est créée
 */
function rassembleFichiers($listeFichiers, $exploitations, $valeurCarac, $parcelles) {
    \PhpOffice\PhpSpreadsheet\Calculation\Functions::setReturnDateType(
        \PhpOffice\PhpSpreadsheet\Calculation\Functions::RETURNDATE_EXCEL
    );
    //Prend plusieurs fichier pour créer le fichier intermédiaire
    $spreadsheetRes = new Spreadsheet();
    $i=0;
    foreach($listeFichiers as $annee => $fichiers) {
        //var_dump($fichiers);
        if($i != 0){
            $spreadsheetRes->createSheet();
        }
        $sheetRes = $spreadsheetRes->getSheet($i);
        if($parcelles === null)
            $sheetRes->setTitle("20".$annee);
        else
            $sheetRes->setTitle("20".$annee."-".$valeurCarac);
        $sheetRes->setCellValue('A1', 'Numéro exploitation');
        $sheetRes->setCellValue('B1', 'Groupe');
        $sheetRes->setCellValue('C1', 'Nom parcelle');
        $sheetRes->setCellValue('D1', 'Date J');
        $sheetRes->setCellValue('E1', 'Date J-n');
        $sheetRes->setCellValue('F1', 'Hauteur jour J');
        $sheetRes->setCellValue('G1', 'Hauteur jour J-n');
        $sheetRes->setCellValue('H1', 'Croissance');
        $nvLigne=2;
        foreach($fichiers as $fichier){
            //var_dump($fichier);
            $spreadsheet=lireFichier($fichier,true);
            $worksheet=$spreadsheet->getSheet(0);
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                
            for ($row = 2; $row <= $highestRow; ++$row) {
                $rechercheGroupe;
                if($parcelles===null){
                    $valGroupe = $worksheet->getCellByColumnAndRow(1, $row)->getValue(); 
                    $rechercheGroupe = true;
                }
                else{
                    $nomParcelle = trim($worksheet->getCellByColumnAndRow(4, $row)->getValue()); 
                    $rechercheGroupe = false;
                }
                if(($rechercheGroupe && in_array($valGroupe, $exploitations)) || (!$rechercheGroupe && in_array($nomParcelle, $parcelles))) {
                    $idxCol=1; 
                    for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                        $nvCol=chr($idxCol + ord('A') - 1);
                        $value = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                        //On vérifie si DateJ et Date J-n sont différentes sinon on ne met pas la ligne
                        $d1 = $worksheet->getCellByColumnAndRow(7, $row)->getCalculatedValue();
                        $d2 = $worksheet->getCellByColumnAndRow(6, $row)->getCalculatedValue();
                        $nbJours = \PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Difference::interval($d1, $d2);
                        if($nbJours == 0){
                            $nvLigne--;
                            break;
                        }
                        else{
                            if ($col != 2 && $col != 5 && $col != 8) {
                                $sheetRes->setCellValue($nvCol.$nvLigne, $value);
                                $idxCol++;
                            }
                            if($col == $highestColumnIndex){
                                $h1 = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                                $h2 = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                                $d1 = $worksheet->getCellByColumnAndRow(7, $row)->getCalculatedValue();
                                $d2 = $worksheet->getCellByColumnAndRow(6, $row)->getCalculatedValue();
                                $value = calculCroissance($d1, $d2, $h1, $h2);
                                $sheetRes->setCellValue('H'.$nvLigne, $value);
                                $sheetRes->setCellValue('I'.$nvLigne, $d1);
                            }
                        }
                    }
                    $nvLigne++;
                }
            }
        }
        $i++;
    }
    return($spreadsheetRes);
}

//Prévoir de le complexifier pour prendre en compte plusieurs caractéristiques
/**
 * Lit tous les fichiers contenant les mesures et les parcelles à selectionner et renvoie un tableur contenant les lignes correspondant aux parcelles de la caractéristique selectionnée
 * Prend en paramètre la liste des fichiers à lire et le tableau des parcelles à selectionner selon la valeur de leur caractéristique
 * Renvoie un fichier Excel sous forme d'un tableur contenant les lignes selectionnées, une feuille de calcul par année et par valeur de caractéristique est créée
 */
function trouverMesuresParcelles($listeFichiers, $listeParcelles) {
    
\PhpOffice\PhpSpreadsheet\Calculation\Functions::setReturnDateType(
    \PhpOffice\PhpSpreadsheet\Calculation\Functions::RETURNDATE_EXCEL
);
    //Prend plusieurs fichiers pour créer le fichier intermédiaire
    $spreadsheetRes = new Spreadsheet();
    $i=0;
    foreach($listeFichiers as $annee => $fichiers) {
        foreach($listeParcelles as $valeurCarac => $parcelles){
            if($i != 0){
                $spreadsheetRes->createSheet();
            }
            $sheetRes = $spreadsheetRes->getSheet($i);
            $sheetRes->setTitle("20".$annee."-".$valeurCarac);
            $sheetRes->setCellValue('A1', 'Numéro exploitation');
            $sheetRes->setCellValue('B1', 'Groupe');
            $sheetRes->setCellValue('C1', 'Nom parcelle');
            $sheetRes->setCellValue('D1', 'Date J');
            $sheetRes->setCellValue('E1', 'Date J-n');
            $sheetRes->setCellValue('F1', 'Hauteur jour J');
            $sheetRes->setCellValue('G1', 'Hauteur jour J-n');
            $sheetRes->setCellValue('H1', 'Croissance');
            $nvLigne=2;
            foreach($fichiers as $fichier){
                //var_dump($fichier);
                $spreadsheet=lireFichier($fichier,true);
                $worksheet=$spreadsheet->getSheet(0);
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                for ($row = 2; $row <= $highestRow; ++$row) {
                    $nomParcelle = trim($worksheet->getCellByColumnAndRow(4, $row)->getValue()); 
                    if(in_array($nomParcelle, $parcelles)) {
                        $idxCol=1; 
                        for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                            $nvCol=chr($idxCol + ord('A') - 1);
                            $value = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                            //On vérifie si DateJ et Date J-n sont différentes sinon on ne met pas la ligne
                            $d1 = $worksheet->getCellByColumnAndRow(7, $row)->getCalculatedValue();
                            $d2 = $worksheet->getCellByColumnAndRow(6, $row)->getCalculatedValue();
                            $nbJours = \PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Difference::interval($d1, $d2);
                            if($nbJours == 0){
                                $nvLigne--;
                                break;
                            }
                            else{
                                if ($col != 2 && $col != 5 && $col != 8) {
                                    $sheetRes->setCellValue($nvCol.$nvLigne, $value);
                                    $idxCol++;
                                }
                                if($col == $highestColumnIndex){
                                    $h1 = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                                    $h2 = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                                    $d1 = $worksheet->getCellByColumnAndRow(7, $row)->getCalculatedValue();
                                    $d2 = $worksheet->getCellByColumnAndRow(6, $row)->getCalculatedValue();
                                    $value = calculCroissance($d1, $d2, $h1, $h2);
                                    //echo '<br/>';
                                    $sheetRes->setCellValue('H'.$nvLigne, $value);
                                    $sheetRes->setCellValue('I'.$nvLigne, $d1);
                                }
                            }
                        }
                        $nvLigne++;
                    }
                }
            }
            $i++;
        }
    }
    //$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheetRes);
	//$writer->save("fichierInterTest.xlsx");
    return($spreadsheetRes);
}

/** 
 * Calcule la croissance d'herbe pour une date donnée en fonction des hauteurs d'herbes de date J et date J-n
 * Prend en paramètre les dates J et J-n et leurs hauteurs d'herbes correspondantes
 * Renvoie la valeur de croissance correspondante
 */
function calculCroissance($date1, $date2, $hauteur1, $hauteur2) {
    $nbJours = \PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Difference::interval($date1, $date2);
    $croissance = -1;
    $mesure = $hauteur2 - $hauteur1;
    if($nbJours != 0){
        if($mesure >= 0 && $mesure < 15) {
            $croissance = $mesure*250/$nbJours;
        }
        else if($mesure >= 15 && $mesure <= 20) {
            $croissance = $mesure*210/$nbJours;
        }
        else if($mesure >20) {
            $croissance = $mesure*180/$nbJours;
        }
    }
    //echo $date1 . ' - '. $date2 . ' = ' .$nbJours. ' -- '. $mesure . ' _ '.$croissance . '<br/>';
    $croissance=number_format($croissance, 2);
    return($croissance);
}

/**
 * Trie les dates d'un tableau en ordre croissant
 * Prend un tableau ayant pour clé des dates et le tri en fonction des dates
 * Retourne un tableau trié par dates
 */
function triDates($tab){
    $tabInter=array();
    foreach ($tab as $date => $value) {
        if(!str_contains($date,"/")){
            $date1=\PhpOffice\PhpSpreadsheet\Calculation\TextData::TEXTFORMAT($date,"mm/dd/yyyy");
            //echo $date1 . '\n';
            $d = explode("/", $date1);
            $nvDate = $d[2] . "/" . $d[0] . "/" . $d[1];
            $tabInter[$nvDate] = $value;
        }
        else{
        $d = explode("/", $date);
        $nvDate = $d[2] . "/" . $d[1] . "/" . $d[0];
        $tabInter[$nvDate] = $value;
        }
    }
    ksort($tabInter);
    $tab=array();
    foreach ($tabInter as $date => $value) {
        $d = explode("/", $date);
        $nvDate = $d[2] . "/" . $d[1] . "/" . $d[0];
        $tab[$nvDate]=$value;
    }
    return($tab);
}

/**
 * Permet de situer une date par rapport à un intervalle donné
 * Si la date est comprise dans l'intervalle renvoie 0;
 * Si la date est avant l'intervalle, renvoie -1;
 * Si la date est après l'intervalle, renvoie 1;
 */
function comparaisonDates($dDebut,$dFin,$d){
    $dateDebut = date_create_from_format("d/m/Y", $dDebut)->format("Y-m-d");
     $date = date_create_from_format("d/m/Y", $d)->format("Y-m-d");
     $dateFin = date_create_from_format("d/m/Y", $dFin)->format("Y-m-d");
     if($dateDebut<=$date && $date<$dateFin){
        return 0;
     }
     else if ($dateDebut>$date) {
         return -1;
     }
     else if($date>=$dateFin) {
        return 1;
     }
}

/**
 * Donne le nombre d'exploitations différentes dans un tableau contenant plusieurs exploitations
 * Prend en paramètre un tableau contenant plusieurs exploitations 
 * Renvoie le nombre d'exploitations différentes que le tableau contient
 */
function nombreExploitations($tab){
    $exploitations=array_count_values($tab);
    $nbExploitations=count(array_keys($exploitations));
    return $nbExploitations;
}
/**
 * Détermine la date du dernier jour d'une période donnée commençant à une date précise
 * Prend en paramètre une date de début de période en format jj/mm/aaaa et une durée de période en nombre de jours
 * Renvoie la date de la fin de la période correspondante sous le même format
 */
function jourFinPeriode($dDebut,$periode) {
    $dateDebut=date_create_from_format("d/m/Y", $dDebut)->format("Y-m-d");
    $dateFin=date_create($dateDebut);
    date_add($dateFin, date_interval_create_from_date_string($periode." days"));
    $dateFin=date_format($dateFin,"d/m/Y");
    return($dateFin);
}

/**
 * Concatene les valeurs de deux tableaux dans un seul tableau
 * Prend en paramètre les deux tableaux
 * Renvoie le tableau contenant les éléments des deux tableaux concaténés
 */
function concatTab($tab1,$tab2){
    $tabRes=array();
    foreach($tab1 as $val1){
        $tabRes[]=$val1;
    }
    foreach($tab2 as $val2){
        $tabRes[]=$val2;
    }
    return $tabRes;
}

/**
 * Calcule le nombre de mesures, d'exploitations, la moyenne de croissance sur une année selon une période donnée
 * Prend en paramètres le tableau des moyennes de croissances triées par date et la période considérée (semaine ou décade)
 * Renvoie un tableau contenant le nombre de mesures, d'exploitations et la moyenne de croissance pour chaque dates d'une période selectionnée sur l'année
 */
function calculPeriode($tab,$per){
    $tabDates=array_keys($tab);
    $annee=substr($tabDates[0],-4);
    $dateDebut="01/01/".$annee;
    $dateAffich=jourFinPeriode($dateDebut,$per-1);
    $dateFin=jourFinPeriode($dateDebut,$per);
    $date=$tabDates[0];
    $indiceSemaine=comparaisonDates($dateDebut, $dateFin,$date);
    while($indiceSemaine>0){
        $tabInter[$dateAffich][0]="";
        $tabInter[$dateAffich][1]="";
        $tabInter[$dateAffich][2]="";
        $dateDebut=$dateFin;
        $dateAffich=jourFinPeriode($dateDebut,$per-1);
        $dateFin=jourFinPeriode($dateDebut,$per);
        $indiceSemaine = comparaisonDates($dateDebut, $dateFin,$date);
    }
    //On a trouvé la date de début, maintenant on cherche à rassembler les dates par semaine commune
    $j=0;
    while($j<count($tabDates)){
        $indiceSemaine = comparaisonDates($dateDebut, $dateFin,$tabDates[$j]);
        $exploitations=array();
        if($indiceSemaine==0) {
            $nbMesures=$tab[$tabDates[$j]][1];
            $nbDates=1;
            $sommeMoy=$tab[$tabDates[$j]][0];
            $exploitations=$tab[$tabDates[$j]][2];
            if($j==count($tabDates)-1){ //Si la nouvelle date est la dernière mesure effectuée
                $croissance=number_format($sommeMoy, 2);
                $tabInter[$dateAffich][0] = $croissance;
                $tabInter[$dateAffich][1] = $nbMesures;
                $tabInter[$dateAffich][2]=count($exploitations);
                $j++;
                $dateDebut=$dateFin;
                $dateAffich=jourFinPeriode($dateDebut,$per-1);
                $dateFin=jourFinPeriode($dateDebut,$per);
            }
            else{ //Sinon on regarde s'il y a d'autres mesures dans la même période 
                $j2=($j+1);
                $indicePeriode = comparaisonDates($dateDebut, $dateFin,$tabDates[$j2]);
                $tabConcat=array();
                while($indicePeriode==0 && $j2<count($tabDates)){
                    $sommeMoy+=$tab[$tabDates[$j2]][0];
                    $nbMesures+=$tab[$tabDates[$j2]][1];
                    $tabConcatInter=concatTab($exploitations,$tab[$tabDates[$j2]][2]);
                    $tabConcat=concatTab($tabConcat,$tabConcatInter);
                    $nbDates++;
                    $j2++;
                    if($j2==count($tabDates)){
                        break;
                    }
                    else{
                        $indicePeriode = comparaisonDates($dateDebut, $dateFin,$tabDates[$j2]);
                    }
                }
                $croissance = $sommeMoy/$nbDates;
                $croissance=number_format($croissance, 2);
                $tabInter[$dateAffich][0] = $croissance;
                $tabInter[$dateAffich][1] = $nbMesures;
                if(count($tabConcat) == 0) { //S'il n'y a pas d'autres mesures sur la même période
                    $tabInter[$dateAffich][2] = nombreExploitations($exploitations);
                }
                else{
                    $tabInter[$dateAffich][2] = nombreExploitations($tabConcat);
                }
                $j=$j2;
                $dateDebut=$dateFin;
                $dateAffich=jourFinPeriode($dateDebut,$per-1);
                $dateFin=jourFinPeriode($dateDebut,$per);
            }
        }
        else if($indiceSemaine>0){
            $tabInter[$dateAffich][0]="";
            $tabInter[$dateAffich][1]="";
            $tabInter[$dateAffich][2]="";
            $dateDebut=$dateFin;
            $dateAffich=jourFinPeriode($dateDebut,$per-1);
            $dateFin=jourFinPeriode($dateDebut,$per);
        }
        else {
            $j++;
        }
    }
    while(substr($dateFin,-4)==$annee){
        $tabInter[$dateAffich][0]="";
        $tabInter[$dateAffich][1]="";
        $tabInter[$dateAffich][2]="";
        $dateDebut=$dateFin;
        $dateAffich=jourFinPeriode($dateDebut,$per-1);
        $dateFin=jourFinPeriode($dateDebut,$per);
    }
    return($tabInter);
}

/**
 * Met en forme une feuille de calcul dans un fichier Excel donné
 * Prend en paramètre le tableau des noms à donner à chaque feuille de calcul, la feuille de calcul à mettre en forme, le numéro de la feuille lue sur le fichier intermédiaire et l'indice de la feuille en train d'être mise en forme
 * Renvoie la feuille de calcul mise en forme
 */
function creationSpreadsheet($nomsSheet,$worksheetRes,$indice,$nbFeuille,$choixGroupe) {
    $worksheetRes->getColumnDimension('A')->setWidth(20);
    $worksheetRes->getColumnDimension('B')->setWidth(20);
    $worksheetRes->getColumnDimension('C')->setWidth(20);
    $worksheetRes->getColumnDimension('D')->setWidth(20);
    $worksheetRes->getColumnDimension('E')->setWidth(20);
    $worksheetRes->getColumnDimension('F')->setWidth(20);
    if($choixGroupe) {
        $worksheetRes->setCellValue('A1', 'Groupe');
        $worksheetRes->setCellValue('D1', 'Nombre d\'exploitations');
        $worksheetRes->setCellValue('E1', 'Nombre de mesures');
        $worksheetRes->setCellValue('F1', 'Moyennes croissance');
    }
    else{
        $worksheetRes->setCellValue('A1', 'Caractéristique');
        $worksheetRes->setCellValue('D1', 'Nombre de mesures');
        $worksheetRes->setCellValue('E1', 'Moyennes croissance');
    }
    if($nbFeuille%2 == 0) {
        $worksheetRes->setTitle($nomsSheet[$indice]." Semaine");
        $worksheetRes->setCellValue('B1', 'Numéro de la semaine');
        $worksheetRes->setCellValue('C1', 'Date début semaine');
    }
    else{
        $worksheetRes->setTitle($nomsSheet[$indice]." Décade");
        $worksheetRes->setCellValue('B1', 'Numéro de la décade');
        $worksheetRes->setCellValue('C1', 'Date début décade');
    }
    return $worksheetRes;
}

/**
 * Créé le fichier Excel résultat contenant les moyennes de croissance pour chaque période pour le groupe et l'année demandés par l'utilisateur
 * Prend en paramètres la feuille de calcul intermédiaire créée et le groupe choisi par l'utilisateur
 * Renvoie le fichier Excel final
 */
function calculMoyenne($spreadsheet,$groupe) {
    $groupe=nomGroupe($groupe);
    $nomsSheet = $spreadsheet->getSheetNames();
    $spreadsheetRes = new Spreadsheet();
    $p=0;
    for($i=0; $i<$spreadsheet->getSheetCount(); $i++) {
        //Lecture du spreadsheet donné et mise des valeurs dans tableau
        $worksheet=$spreadsheet->getSheet($i);
        $highestRow = $worksheet->getHighestRow();
        $dates=array();         
        for ($row = 2; $row <= $highestRow; ++$row) {
            $valDate = (string)$worksheet->getCellByColumnAndRow(4, $row)->getValue(); 
            $valCroissance = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
            $valExploitation = (string)$worksheet->getCellByColumnAndRow(1, $row)->getValue();
            $exploitations=array();
                if(in_array($valDate,array_keys($dates))) {
                    if($valCroissance>=0){
                        $dates[$valDate][0]+=$valCroissance;
                    }
                    $dates[$valDate][1]++;
                    $exploitations =  $dates[$valDate][2];
                    $exploitations[] = $valExploitation;
                    $dates[$valDate][2]= $exploitations;
                }
                else {
                    if($valCroissance>=0){
                        $dates[$valDate][0]=$valCroissance;
                    }
                    else{
                        $dates[$valDate][0]=0;
                    }
                    $dates[$valDate][1]=1;
                    $exploitations[] = $valExploitation;
                    $dates[$valDate][2]= $exploitations;
                }
            }
        $tabTrie=triDates($dates);

        //Création du spreadsheet et worksheet résultat
        if($i != 0){
            $spreadsheetRes->createSheet();
        }
        $sheetRes = $spreadsheetRes->getSheet($p);
        //Cas particulier : pas de mesures faites
        if(empty($tabTrie)){ 
            $sheetRes->setTitle($nomsSheet[$i]." Semaine");
            $sheetRes->setCellValue('A1', 0);
            $p++;
            $spreadsheetRes->createSheet();
            $sheetRes = $spreadsheetRes->getSheet($p);
            $sheetRes->setTitle($nomsSheet[$i]." Décade");
            $sheetRes->setCellValue('A1', 0);
            $p++;
        }
        else {
            //Calcul des moyennes de croissances pour les dates triées
            $tabInter2=array();
            foreach($tabTrie as $date => $tabValeurs) {
                $moy=$tabValeurs[0]/$tabValeurs[1];
                $moy=number_format($moy, 2);
                $nbExploitation=array_count_values($tabValeurs[2]);
                $exploitations=array_keys($nbExploitation);
                $tabInter2[$date] = array($moy, $tabValeurs[1],$exploitations);
            }
            $sheetRes=creationSpreadsheet($nomsSheet,$sheetRes,$i,$p,true);
            $tabSem=calculPeriode($tabInter2,7);
            $j=2;
            foreach($tabSem as $date => $tabValeurs) {
                $sheetRes->setCellValue('A' . $j, $groupe);
                $sheetRes->setCellValue('B' . $j, $j-1);
                $sheetRes->setCellValue('C' . $j, $date);
                $sheetRes->setCellValue('D' . $j, $tabValeurs[2]);
                $sheetRes->setCellValue('E' . $j, $tabValeurs[1]);
                $sheetRes->setCellValue('F' . $j, $tabValeurs[0]);
                $j++;
            }
            $p++;
            $spreadsheetRes->createSheet();
            $sheetRes = $spreadsheetRes->getSheet($p);
            $sheetRes=creationSpreadsheet($nomsSheet,$sheetRes,$i,$p,true);
            $tabDecade=calculPeriode($tabInter2,10);
            $j=2;
            foreach($tabDecade as $date => $tabValeurs) {
                $sheetRes->setCellValue('A' . $j, $groupe);
                $sheetRes->setCellValue('B' . $j, $j-1);
                $sheetRes->setCellValue('C' . $j, $date);
                $sheetRes->setCellValue('D' . $j, $tabValeurs[2]);
                $sheetRes->setCellValue('E' . $j, $tabValeurs[1]);
                $sheetRes->setCellValue('F' . $j, $tabValeurs[0]);
                $j++;
            }
            $p++;     
        }
    }
    return($spreadsheetRes);
}

/**
 * Créé le fichier Excel résultat contenant les moyennes de croissance pour chaque période pour la caractéristique et l'année demandées par l'utilisateur
 * Prend en paramètres la feuille de calcul intermédiaire créée et les caractéristiques choisies par l'utilisateur
 * Renvoie le fichier Excel final
 */
function calculMoyenneParcelles($spreadsheet,$caracteristique) {
    $nomsSheet = $spreadsheet->getSheetNames();
    $spreadsheetRes = new Spreadsheet();
    $p=0;
    for($i=0; $i<$spreadsheet->getSheetCount(); $i++) {
        //Lecture du spreadsheet donné et mise des valeurs dans tableau
        $worksheet=$spreadsheet->getSheet($i);
        $highestRow = $worksheet->getHighestRow();
        $dates=array();   
        //$dates est un tableau multidimensionnel ayant pour clé la date de mesure et pour valeur un tableau contenant la somme des croissances, le nombre de parcelles, un tabeau avec le nom des parcelles     
        for ($row = 2; $row <= $highestRow; ++$row) {
            $valDate = (string)$worksheet->getCellByColumnAndRow(4, $row)->getValue(); 
            $valCroissance = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
            $valParcelle = (string)$worksheet->getCellByColumnAndRow(3, $row)->getValue();
            $parcelles=array();
                if(in_array($valDate,array_keys($dates))) {
                    if($valCroissance>=0){
                        $dates[$valDate][0]+=$valCroissance;
                    }
                    $dates[$valDate][1]++;
                    $parcelles =  $dates[$valDate][2];
                    $parcelles[] = $valParcelle;
                    $dates[$valDate][2]= $parcelles;
                }
                else {
                    if($valCroissance>=0){
                        $dates[$valDate][0]=$valCroissance;
                    }
                    else{
                        $dates[$valDate][0]=0;
                    }
                    $dates[$valDate][1]=1;
                    $parcelles[] = $valParcelle;
                    $dates[$valDate][2]= $parcelles;
                }
            }
        $tabTrie=triDates($dates);

        //Création du spreadsheet et worksheet résultat
        if($i != 0){
            $spreadsheetRes->createSheet();
        }
        $sheetRes = $spreadsheetRes->getSheet($p);
        //Cas particulier : pas de mesures faites
        if(empty($tabTrie)){ 
            $sheetRes->setTitle($nomsSheet[$i]." Semaine");
            $sheetRes->setCellValue('A1', 0);
            $p++;
            $spreadsheetRes->createSheet();
            $sheetRes = $spreadsheetRes->getSheet($p);
            $sheetRes->setTitle($nomsSheet[$i]." Décade");
            $sheetRes->setCellValue('A1', 0);
            $p++;
        }
        else {
            //Calcul des moyennes de croissances pour les dates triées
            $tabInter2=array();
            foreach($tabTrie as $date => $tabValeurs) {
                $moy=$tabValeurs[0]/$tabValeurs[1];
                $moy=number_format($moy, 2);
                $nbParcelle=array_count_values($tabValeurs[2]);
                $parcelle=array_keys($nbParcelle);
                $tabInter2[$date] = array($moy, $tabValeurs[1],$parcelle);
            }
            //var_dump($tabInter2);
            //echo '<br/>';
            $sheetRes=creationSpreadsheet($nomsSheet,$sheetRes,$i,$p,false);
            $tabSem=calculPeriode($tabInter2,7);
            $j=2;
            foreach($tabSem as $date => $tabValeurs) {
                $sheetRes->setCellValue('A' . $j, $caracteristique.'_'.$nomsSheet[$i]);
                $sheetRes->setCellValue('B' . $j, $j-1);
                $sheetRes->setCellValue('C' . $j, $date);
                $sheetRes->setCellValue('D' . $j, $tabValeurs[1]);
                $sheetRes->setCellValue('E' . $j, $tabValeurs[0]);
                $j++;
            }
            $p++;
            $spreadsheetRes->createSheet();
            $sheetRes = $spreadsheetRes->getSheet($p);
            $sheetRes=creationSpreadsheet($nomsSheet,$sheetRes,$i,$p,false);
            $tabDecade=calculPeriode($tabInter2,10);
            $j=2;
            foreach($tabDecade as $date => $tabValeurs) {
                $sheetRes->setCellValue('A' . $j, $caracteristique.'_'.$nomsSheet[$i]);
                $sheetRes->setCellValue('B' . $j, $j-1);
                $sheetRes->setCellValue('C' . $j, $date);
                $sheetRes->setCellValue('D' . $j, $tabValeurs[1]);
                $sheetRes->setCellValue('E' . $j, $tabValeurs[0]);
                $j++;
            }
            $p++;     
        }
    }
    //$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheetRes);
	//$writer->save("fichierFinalTest.xlsx");
    return($spreadsheetRes);
}

/**
 * Affiche sur la page web le fichier Excel résultat
 * Prend en paramètre le fichier Excel, les numéros des worksheet à afficher (paires = semaine / impaires = décades) et un booléen qui indique si le fichier à afficher est celui d'un choix de caractéristiques
 * Renvoie l'affichage d'un tableau HTML contenant les valeurs du fichier Excel
 */
function afficheFichier($spreadsheet,$tabFichiers,$choixCaracteristiques) {
    $nomsPages=$spreadsheet->getSheetNames();
    $nbTab=$spreadsheet->getSheetCount();
    $nomAnnee="";
        for($i=0; $i<($nbTab/2); $i++) {
            if($tabFichiers[0]==0){
                $nomAnnee=substr($nomsPages[2*$i],0,-8);
            }
            else{
                $nomAnnee=substr($nomsPages[(2*$i)+1],0,-7);
            }
            $worksheet = $spreadsheet->getSheet($tabFichiers[$i]); 
            if($worksheet->getCellByColumnAndRow(1, 1)->getValue() == 0){
                echo '<p id="pasMesure">Aucune mesure n\'a été faite pour les exploitations du groupe sélectionné lors de l\'année '.$nomAnnee.'.</p>';
            }
            else {
            $highestRow = $worksheet->getHighestRow(); 
            $highestColumn = $worksheet->getHighestColumn(); 
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5
            //Pour ne pas avoir une colonne vide (du au fichier creationSpreadSheet qui est calqué sur 6 colonnes)
            if($choixCaracteristiques){
                $highestColumnIndex = 5;
            }
            echo '<h2 id="annee">Année '. $nomAnnee . '</h2>';
            echo '<table>' . "\n";
            for ($row = 1; $row <= $highestRow; ++$row) {
                    echo '<tr>' . PHP_EOL;
                    for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                        $value = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                        if($row==1){
                            echo '<th>' . $value . '</th>' . PHP_EOL;

                        }
                        else {
                            echo '<td>' . $value . '</td>' . PHP_EOL;
                        }
                    }
                    echo '</tr>' . PHP_EOL;
            }
            echo '</table>' . PHP_EOL;
        }
    }
}

/**
 * Enregistre le fichier Excel résultat, lu dans l'interface en cours de fonctionnement
 * Prend en paramètre le fichier Excel à enregistrer et le groupe selectionné par l'utilisateur
 * Renvoie un formulaire d'enregistrement du fichier Excel correspondant
 */
function enregistrementFichier($groupe,$caracteristique) {
    if($caracteristique==""){
        $nomGroupe=nomGroupe($groupe);
        $nomFichier = "Moyennes croissances ".$nomGroupe;//.".xlsx";
    }
    else {
        $nomFichier = "Moyennes croissances parcelles ".$caracteristique[0];
    }
    $spreadsheet = lireFichier("fichierInter.xlsx",false);
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename=\"$nomFichier\"");
    header('Cache-Control: max-age=0');
    //header('Expires: Fri, 11 Nov 2011 11:11:11 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    //header('Pragma: public');
    $writer->save('php://output');
}
?>
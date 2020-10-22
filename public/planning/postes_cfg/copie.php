<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/postes_cfg/copie.php
Création : mai 2011
Dernière modification : 29 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet de copier un tableau existant. Affiche un formulaire demandant le nom du nouveau tableau. Insère les informations
dans la base de données après validation

Page appelée par la fonction JavaScript "popup", qui ouvre cette page dans un cadre flottant, lors du click sur l'icône copie
de la page "planning/postes_cfg/index.php"
*/

require_once "class.tableaux.php";

// Initilisation des variables
$confirm=filter_input(INPUT_GET, "confirm", FILTER_SANITIZE_STRING);
$CSRFToken=filter_input(INPUT_GET, "CSRFToken", FILTER_SANITIZE_STRING);
$nom=trim(filter_input(INPUT_GET, "nom", FILTER_SANITIZE_STRING));
$numero1=filter_input(INPUT_GET, "numero", FILTER_SANITIZE_NUMBER_INT);

$confirm=filter_var($confirm, FILTER_CALLBACK, array("options"=>"sanitize_on"));

if ($confirm) {
    //		Copie des horaires
    $values=array();
    $db->select2("pl_poste_horaires", array("debut","fin","tableau"), array("numero"=>$numero1), "ORDER BY `tableau`,`debut`,`fin`");
    if ($db->result) {
        echo "<br/><br/><b>Copie en cours. Veuillez patienter ...</b>\n";
        $db2=new db();
        $db2->select2("pl_poste_tab", array(array("name"=>"MAX(tableau)","as"=>"tableau"),"site"));
        $numero2=$db2->result[0]['tableau']+1;
        foreach ($db->result as $elem) {
            if (array_key_exists('tableau', $elem)) {
                $values[]=array(":debut"=>$elem['debut'], ":fin"=>$elem['fin'], ":tableau"=>$elem['tableau'], ":numero"=>$numero2);
            }
        }
        $req="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`,`fin`,`tableau`,`numero`) VALUES (:debut, :fin, :tableau, :numero);";
        $db2=new dbh();
        $db2->CSRFToken = $CSRFToken;
        $db2->prepare($req);
        foreach ($values as $elem) {
            $db2->execute($elem);
        }

        // Récupération du site
        $db2=new db();
        $db2->select2("pl_poste_tab", "site", array("tableau"=>$numero1));
        $site=$db2->result[0]["site"];

        // Enregistrement du nouveau tableau
        $db2=new db();
        $db2->CSRFToken = $CSRFToken;
        $db2->insert("pl_poste_tab", array("nom"=>$nom ,"tableau"=>$numero2, "site"=>$site));
    } else {		// par sécurité, si pas d'horaires à  copier, on stop le script pour éviter d'avoir une incohérence dans les numéros de tableaux
        echo "<script type='text/javaScript'>parent.location.href='index.php?page=planning/postes_cfg/modif.php&cfg-type=horaires&numero=$numero';</script>\n";
        exit;
    }

    //		Copie des lignes
    $values=array();
    $db->select2("pl_poste_lignes", array("tableau","ligne","poste","type"), array("numero"=>$numero1), "ORDER BY `tableau`,`ligne`");
    if ($db->result) {
        foreach ($db->result as $elem) {
            if (array_key_exists('ligne', $elem)) {
                $values[]=array(":tableau"=>$elem['tableau'], ":ligne"=>$elem['ligne'], ":poste"=>$elem['poste'], ":type"=>$elem['type'],
      "numero"=>$numero2);
            }
        }
        $req="INSERT INTO `{$dbprefix}pl_poste_lignes` (`tableau`,`ligne`,`poste`,`type`,`numero`) ";
        $req.="VALUES (:tableau, :ligne, :poste, :type, :numero)";
        $db2=new dbh();
        $db2->CSRFToken = $CSRFToken;
        $db2->prepare($req);
        foreach ($values as $elem) {
            $db2->execute($elem);
        }
    }

    //		Copie des cellules grises
    $values=array();
    $db->select2("pl_poste_cellules", array("ligne","colonne","tableau"), array("numero"=>$numero1), "ORDER BY `tableau`,`ligne`,`colonne`");
    if ($db->result) {
        foreach ($db->result as $elem) {
            if (array_key_exists('ligne', $elem) and array_key_exists('colonne', $elem)) {
                $values[]=array(":ligne"=>$elem['ligne'], ":colonne"=>$elem['colonne'], ":tableau"=>$elem['tableau'], ":numero"=>$numero2);
            }
        }
        $req="INSERT INTO `{$dbprefix}pl_poste_cellules` (`ligne`,`colonne`,`tableau`,`numero`) ";
        $req.="VALUES (:ligne, :colonne, :tableau, :numero)";
        $db2=new dbh();
        $db2->CSRFToken = $CSRFToken;
        $db2->prepare($req);
        foreach ($values as $elem) {
            $db2->execute($elem);
        }
    }

    // Retour à  la page principale
    echo "<script type='text/javaScript'>parent.location.href='{$config['URL']}/framework?cfg-type=horaires&numero=$numero2';</script>\n";
} else {
    echo "<h3>Copie du tableau</h3>\n";
    echo "<form name='form' action='index.php' method='get'>\n";
    echo "<input type='hidden' name='CSRFToken' value='$CSRFSession' />\n";
    echo "<input type='hidden' name='page' value='planning/postes_cfg/copie.php' />\n";
    echo "<input type='hidden' name='menu' value='off' />\n";
    echo "<input type='hidden' name='confirm' value='on' />\n";
    echo "<input type='hidden' name='numero' value='$numero1' />\n";
    echo "Nom du nouveau tableau<br/>\n";
    echo "<input type='text' name='nom' />\n";
    echo "<br/><br/><br/>\n";
    echo "<input type='button' value='Annuler' onclick='popup_closed();'/>\n";
    echo "&nbsp;&nbsp;<input type='submit' value='Copier' />\n";
    echo "</form>\n";
}

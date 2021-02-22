<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/postes_cfg/ajax.infos.php
Création : 20 février 2016
Dernière modification : 15 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Met à jour les informations générales du tableau sélectionné
Appelé en Ajax via la fonction tableauxInfos à partir de la page infos.php (dans modif.php)
*/

ini_set('display_errors', 0);

session_start();

include "../../include/config.php";
include "class.tableaux.php";

$id=filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
$CSRFToken = filter_input(INPUT_GET, "CSRFToken", FILTER_SANITIZE_STRING);
$nombre=filter_input(INPUT_GET, "nombre", FILTER_SANITIZE_NUMBER_INT);
$nom=filter_input(INPUT_GET, "nom");
$site=filter_input(INPUT_GET, "site", FILTER_SANITIZE_NUMBER_INT);

// Ajout
if (!$id) {

  // Recherche du numero de tableau à utiliser
    $db=new db();
    $db->select2("pl_poste_tab", array(array("name"=>"MAX(tableau)","as"=>"numero")));
    $numero=$db->result[0]["numero"]+1;

    // Insertion dans la table pl_poste_tab
    $insert=array("nom"=>trim($nom), "tableau"=>$numero, "site"=>"1");
    if ($site) {
        $insert["site"]=$site;
    }
  
    $db=new db();
    $db->sanitize_string = false;
    $db->CSRFToken = $CSRFToken;
    $db->insert("pl_poste_tab", $insert);

    $t=new tableau();
    $t->id = $numero;
    $t->CSRFToken = $CSRFToken;
    $t->setNumbers($nombre);

    echo json_encode((int) $numero);
}

// Modification
else {
    $t=new tableau();
    $t->id=$id;
    $t->CSRFToken = $CSRFToken;
    $t->setNumbers($nombre);

    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->sanitize_string = false;
    $db->update("pl_poste_tab", array("nom"=>trim($nom)), array("tableau"=>$id));

    if ($site) {
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->update('pl_poste_tab', array('site'=>$site), array('tableau'=>$id));
    }

    echo json_encode("OK");
}

<?php
/**
Planning Biblio, Version 2.6.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planningHebdo/ajax.verifPlannings.php
Création : 2 octobre 2013
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Recherche les plannings enregistrés afin d'éviter les conflits lors de l'enregistrement d'un nouveau planning.
Fichier appelé en arrière plan par la fonction JS plHebdoVerifForm (js/script.planningHebdo.js)
*/

session_start();
include "../include/config.php";

// Initialisation des variables
$debut=filter_input(INPUT_GET, "debut", FILTER_CALLBACK, array("options"=>"sanitize_dateSQL"));
$fin=filter_input(INPUT_GET, "fin", FILTER_CALLBACK, array("options"=>"sanitize_dateSQL"));
$id=filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
$perso_id=filter_input(INPUT_GET, "perso_id", FILTER_SANITIZE_NUMBER_INT);
$exception = filter_input(INPUT_GET, "exception", FILTER_SANITIZE_NUMBER_INT);

// Filtre permettant de ne rechercher que les plannings de l'agent sélectionné
$perso_id=$perso_id?$perso_id:$_SESSION['login_id'];

// Personalisation du message de retour
$autre_agent=$perso_id!=$_SESSION['login_id']?nom($perso_id):false;

// Filtre permettant de ne pas regarder l'actuel planning et les plannings remplacant celui-ci
$ignore_id = $id?" AND `id`<>'$id' AND `remplace`<>'$id' " : null;

// Filtre permettant de ne pas regarder le planning remplacé par le planning sélectionné
$remplace=null;
if ($id) {
    $db=new db();
    $db->select("planning_hebdo", "remplace", "`id`='$id'");
    if ($db->result[0]['remplace']) {
        $remplace=" AND `id`<>'{$db->result[0]['remplace']}' AND `remplace`<>'{$db->result[0]['remplace']}' ";
    }
}

$filter = "perso_id='$perso_id' AND `debut`<='$fin' AND `fin`>='$debut'";
if ($exception) {
    $filter .= " AND id <> $exception";
} elseif ($id) {
    $filter .= " AND exception <> $id";
}

$db=new db();
$db->select("planning_hebdo", "*", "$filter $ignore_id $remplace ");

$result=array();
if (!$db->result) {
    $result=array("retour"=>"OK");
} else {
    $result=array("retour"=>"NO","debut"=>$db->result[0]['debut'],"fin"=>$db->result[0]['fin'], "autre_agent"=>$autre_agent);
}
echo json_encode($result);

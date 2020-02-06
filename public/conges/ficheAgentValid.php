<?php
/**
Planning Biblio, Plugin Congés Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/ficheAgentValid.php
Création : 15 janvier 2014
Dernière modification : 10 février 2018
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Etienne Cavalié

Description :
Fichier permettant de mettre à jour les crédits congés des agents lors de la modification de leur fiche
Inclus dans le fichier personnel/valid.php
*/

// Include
include_once "conges/class.conges.php";


$conges_credit = filter_input(INPUT_POST, 'conges_credit', FILTER_SANITIZE_STRING);
$conges_reliquat = filter_input(INPUT_POST, 'conges_reliquat', FILTER_SANITIZE_STRING);
$conges_anticipation = filter_input(INPUT_POST, 'conges_anticipation', FILTER_SANITIZE_STRING);
$recup = filter_input(INPUT_POST, 'comp_time', FILTER_SANITIZE_STRING);
$conges_annuel = filter_input(INPUT_POST, 'conges_annuel', FILTER_SANITIZE_STRING);
$conges_credit_min = filter_input(INPUT_POST, 'conges_credit_min', FILTER_SANITIZE_STRING);
$conges_reliquat_min = filter_input(INPUT_POST, 'conges_reliquat_min', FILTER_SANITIZE_STRING);
$conges_anticipation_min = filter_input(INPUT_POST, 'conges_anticipation_min', FILTER_SANITIZE_STRING);
$recup_min = filter_input(INPUT_POST, 'comp_time_min', FILTER_SANITIZE_STRING);
$conges_annuel_min = filter_input(INPUT_POST, 'conges_annuel_min', FILTER_SANITIZE_STRING);

// Mise à jour des crédits dans la table personnel
$credits=array();
$credits["conges_credit"] = $conges_credit + $conges_credit_min;
$credits["conges_reliquat"] = $conges_reliquat + $conges_reliquat_min;
$credits["conges_anticipation"] = $conges_anticipation + $conges_anticipation_min;
$credits["comp_time"] = $recup + $recup_min;
$credits["conges_annuel"] = $conges_annuel + $conges_annuel_min;

if ($action=="modif") {
    $update=array_merge($update, $credits);
} else {
    $insert=array_merge($insert, $credits);
}

// Ajout d'un ligne d'information dans la liste des congés
$c=new conges();
$c->perso_id=$id;
$c->CSRFToken = $CSRFToken;
$c->maj($credits, $action);

 <?php
/**
Planning Biblio, Plugin Congés Version 2.4.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/ficheAgentValid.php
Création : 15 janvier 2014
Dernière modification : 28 octobre 2016
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Etienne Cavalié

Description :
Fichier permettant de mettre à jour les crédits congés des agents lors de la modification de leur fiche
Inclus dans le fichier personnel/valid.php
*/

// Include
include_once "conges/class.conges.php";


$congesCredit = filter_input(INPUT_POST, 'congesCredit', FILTER_SANITIZE_STRING);
$congesReliquat = filter_input(INPUT_POST, 'congesReliquat', FILTER_SANITIZE_STRING);
$congesAnticipation = filter_input(INPUT_POST, 'congesAnticipation', FILTER_SANITIZE_STRING);
$recupSamedi = filter_input(INPUT_POST, 'recupSamedi', FILTER_SANITIZE_STRING);
$congesAnnuel = filter_input(INPUT_POST, 'congesAnnuel', FILTER_SANITIZE_STRING);
$congesCreditMin = filter_input(INPUT_POST, 'congesCreditMin', FILTER_SANITIZE_STRING);
$congesReliquatMin = filter_input(INPUT_POST, 'congesReliquatMin', FILTER_SANITIZE_STRING);
$congesAnticipationMin = filter_input(INPUT_POST, 'congesAnticipationMin', FILTER_SANITIZE_STRING);
$recupSamediMin = filter_input(INPUT_POST, 'recupSamediMin', FILTER_SANITIZE_STRING);
$congesAnnuelMin = filter_input(INPUT_POST, 'congesAnnuelMin', FILTER_SANITIZE_STRING);

// Mise à jour des crédits dans la table personnel
$credits=array();
$credits["congesCredit"]=$congesCredit + $congesCreditMin;
$credits["congesReliquat"]=$congesReliquat + $congesReliquatMin;
$credits["congesAnticipation"]=$congesAnticipation + $congesAnticipationMin;
$credits["recupSamedi"]=$recupSamedi + $recupSamediMin;
$credits["congesAnnuel"]=$congesAnnuel + $congesAnnuelMin;

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

?>
<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2019 Jérôme Combes

Fichier : public/personnel/ajax.delete.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Supprime les agents sélectionnés à partir de la liste des agents (fichier personnel/index.php).
Les agents ne sont pas supprimés définitivement, ils sont marqués comme supprimés dans la table personnel (champ supprime=1)

Ce script est appelé par la fonction JS personnel/js/index.js : agent_list
*/

session_start();

require_once(__DIR__.'/../include/config.php');
require_once "class.personnel.php";

$list = filter_input(INPUT_POST, 'list', FILTER_SANITIZE_STRING);
$CSRFToken = filter_input(INPUT_POST, 'CSRFToken', FILTER_SANITIZE_STRING);

$list = html_entity_decode($list, ENT_QUOTES|ENT_IGNORE, 'UTF-8');

// prohibits removal of admin and "tout le monde"
$tab = array();
$tmp = json_decode($list);
foreach ($tmp as $elem) {
  if ($elem > 2) {
    $tab[] = $elem;
  }
}

$list = join(',', $tab);

if ($_SESSION['perso_actif']=="Supprimé") {
    $p=new personnel();
    $p->CSRFToken = $CSRFToken;
    $p->delete($list);
} else {
    // TODO : demander la date de suppression en popup
    // Date de suppression
    $date = date('Y-m-d');

    // Mise à jour de la table personnel
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->update("personnel", array("supprime"=>"1","actif"=>"Supprim&eacute;","depart"=>$date), array("id"=>"IN$list"));

    // Mise à jour de la table pl_poste
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->update('pl_poste', array('supprime'=>1), array('perso_id' => "IN$list", 'date' =>">$date"));

    // Mise à jour de la table responsables
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->delete("responsables", array('responsable' => "IN$list"));
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->delete("responsables", array('perso_id' => "IN$list"));
}

echo json_encode('ok');
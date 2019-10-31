<?php
/**
Planning Biblio, Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : personnel/suppression-liste.php
Création : mai 2011
Dernière modification : 25 janvier 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Supprime les agents sélectionnés à partir de la liste des agents (fichier personnel/index.php).
Les agents ne sont pas supprimés définitivement, ils sont marqués comme supprimés dans la table personnel (champ supprime=1)

Cette page est appelée par le fichier index.php
*/

require_once "class.personnel.php";

$post=filter_input_array(INPUT_POST, FILTER_SANITIZE_NUMBER_INT);
$CSRFToken = filter_input(INPUT_POST, 'CSRFToken', FILTER_SANITIZE_STRING);

foreach ($post as $key => $value) {
    if (substr($key, 0, 3)=="chk") {
        // Disallow admin deletion
        if ($value != 1) {
            $liste[]=$value;
        }
    }
}
$liste=join($liste, ",");
if ($_SESSION['perso_actif']=="Supprimé") {
    $p=new personnel();
    $p->CSRFToken = $CSRFToken;
    $p->delete($liste);
} else {
    // TODO : demander la date de suppression en popup
    // Date de suppression
    $date = date('Y-m-d');

    // Mise à jour de la table personnel
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->update("personnel", array("supprime"=>"1","actif"=>"Supprim&eacute;","depart"=>$date), array("id"=>"IN$liste"));

    // Mise à jour de la table pl_poste
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->update('pl_poste', array('supprime'=>1), array('perso_id' => "IN$liste", 'date' =>">$date"));
  
    // Mise à jour de la table responsables
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->delete("responsables", array('responsable' => "IN$liste"));
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->delete("responsables", array('perso_id' => "IN$liste"));
}

echo "<script type='text/JavaScript'>document.location.href='index.php?page=personnel/index.php';</script>\n";

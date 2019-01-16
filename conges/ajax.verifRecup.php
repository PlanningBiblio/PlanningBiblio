<?php
/*
Planning Biblio, Plugin Congés Version 1.5.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/ajax.verifRecup.php
Création : 18 septembre 2013
Dernière modification : 5 novembre 2014
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Vérifie si le jour demandé à déjà fait l'objet d'une demande de récuperation.
Appelé en arrière plan par la fonction JS verifRecup()
*/

session_start();
ini_set('display_errors', 0);
ini_set('error_reporting', E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

include "../../include/config.php";
include "../../personnel/class.personnel.php";
include "class.conges.php";

$date=dateFr($_GET['date']);
$perso_id=is_numeric($_GET['perso_id'])?$_GET['perso_id']:$_SESSION['login_id'];

$db=new db();
$db->select("recuperations", null, "`perso_id`='$perso_id' AND (`date`='$date' OR `date2`='$date')");
if ($db->result) {
    echo "Demande";
}

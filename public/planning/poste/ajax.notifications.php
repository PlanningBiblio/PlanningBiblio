<?php
/**
Planning Biblio, Version 2.7.01
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/poste/ajax.notifications.php
Création : 24 septembre 2015
Dernière modification : 30 septembre 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Envoie les notifications aux agents lorsque des plannings les concernant sont validés ou modifiés

Page appelée en ajax lors du click sur les cadenas de la page planning/poste/index.php
(événement $("#icon-lock").click, page planning/poste/js/planning.js)
*/

session_start();
require_once "../../include/config.php";
require_once "class.planning.php";

// Initialisation des variables
$CSRFToken = filter_input(INPUT_GET, "CSRFToken", FILTER_SANITIZE_STRING);
$date=filter_input(INPUT_GET, "date", FILTER_CALLBACK, array("options"=>"sanitize_dateSQL"));
$site=filter_input(INPUT_GET, "site", FILTER_SANITIZE_NUMBER_INT);

// Envoi des notification
if ($config['Planning-Notifications']) {
    $p=new planning();
    $p->date=$date;
    $p->site=$site;
    $p->CSRFToken = $CSRFToken;
    $p->notifications();
}
echo json_encode("ok");

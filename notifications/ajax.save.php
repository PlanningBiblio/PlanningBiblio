<?php
/**
Planning Biblio, Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : notifications/ajax.save.php
Création : 16 janvier 2018
Dernière modification : 16 janvier 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet l'enregistrement des responsables hierarchiques pour les notifications et la validation niveau 1 des absences et congés
Page appelée en ajax par le fichier notifications.js lors du click sur le bouton "Modifier" de la page notifications/index.php
*/

session_start();

require_once __DIR__."/../include/config.php";
require_once __DIR__."/../personnel/class.personnel.php";

$agents = filter_input(INPUT_POST, 'agents', FILTER_SANITIZE_STRING);
$responsables = filter_input(INPUT_POST, 'responsables', FILTER_SANITIZE_STRING);
$notifications = filter_input(INPUT_POST, 'notifications', FILTER_SANITIZE_STRING);
$CSRFToken = filter_input(INPUT_POST, 'CSRFToken', FILTER_SANITIZE_STRING);

$agents = html_entity_decode($agents, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
$responsables = html_entity_decode($responsables, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
$notifications = html_entity_decode($notifications, ENT_QUOTES|ENT_IGNORE, 'UTF-8');

$agents = json_decode($agents);
$responsables = json_decode($responsables);
$notifications = json_decode($notifications);

$p = new personnel();
$p->CSRFToken = $CSRFToken;
$p->updateResponsibles($agents, $responsables, $notifications);

return json_encode('ok');

?>
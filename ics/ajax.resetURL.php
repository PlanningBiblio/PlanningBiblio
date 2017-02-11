<?php
/**
Planning Biblio, Version 2.5.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : ics/ajax.resetURL.php
Création : 26 juillet 2016
Dernière modification : 10 février 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Redéfini un nouveau code ICS pour l'agent sélectionné, ce qui génère une nouvelle URL ICS et rend inaccessible le calendrier via l'ancienne URL.
Script appelé en ajax via la fonction resetICSURL() (js/script.js)
*/

session_start();

require_once "../include/config.php";
require_once "../personnel/class.personnel.php";

$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
$CSRFToken = filter_input(INPUT_POST, 'CSRFToken', FILTER_SANITIZE_STRING);

$db = new db();
$db->CSRFToken = $CSRFToken;
$db->update2("personnel",array("codeICS"=>null), array("id"=>$id));

$p = new personnel();
$p->CSRFToken = $CSRFToken;
$url = $p->getICSURL($id);
$url = html_entity_decode($url,ENT_QUOTES|ENT_IGNORE,'UTF-8');

echo json_encode(array("url" => $url));

?>
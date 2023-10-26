<?php
/**
Planning Biblio, Version 2.6.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : ics/ajax.resetURL.php
Création : 26 juillet 2016
Dernière modification : 21 avril 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Redéfini un nouveau code ICS pour l'agent sélectionné, ce qui génère une nouvelle URL ICS et rend inaccessible le calendrier via l'ancienne URL.
Script appelé en ajax via la fonction resetICSURL() (js/script.js)
*/

require_once(__DIR__ . '/../../init/init_ajax.php');
require_once(__DIR__ . '/../personnel/class.personnel.php');

$id = $request->get('id');
$CSRFToken = $request->get('CSRFToken');

$id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);

$db = new db();
$db->CSRFToken = $CSRFToken;
$db->update("personnel", array("code_ics"=>null), array("id"=>$id));

$p = new personnel();
$p->CSRFToken = $CSRFToken;
$url = $p->getICSURL($id);
$url = html_entity_decode($url, ENT_QUOTES|ENT_IGNORE, 'UTF-8');

echo json_encode(array("url" => $config['URL'] . $url));

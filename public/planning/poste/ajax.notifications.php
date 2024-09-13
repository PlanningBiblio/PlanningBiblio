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

Page appelée en ajax lors du click sur les cadenas de la page /index
(événement $("#icon-lock").click, page planning/poste/js/planning.js)
*/

require_once(__DIR__ . '/../../../init/init_ajax.php');
require_once('class.planning.php');

// Initialisation des variables
$CSRFToken = $request->get('CSRFToken');
$date = $request->get('date');
$site = $request->get('site');

$date = filter_var($date, FILTER_CALLBACK, array('options' => 'sanitize_dateSQL'));
$site = filter_var($site, FILTER_SANITIZE_NUMBER_INT);

// Envoi des notification
if ($config['Planning-InitialNotification'] != '-2' or $config['Planning-ChangeNotification'] != '-2' ) {
    $p=new planning();
    $p->date=$date;
    $p->site=$site;
    $p->CSRFToken = $CSRFToken;
    $p->notifications();
}
echo json_encode("ok");

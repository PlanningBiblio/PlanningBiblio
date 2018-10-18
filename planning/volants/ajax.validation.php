<?php
/**
Planning Biblio, Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/volants/ajax.validation.php
Création : 7 avril 2018
Dernière modification : 7 avril 2018
@author Jérôme Combes <jerome@planningbiblio.fr>


Description :
Enregistrement des informations dans la base de données
Appelée en ajax par l'action click sur le bouton "Valider" (submit) de la page planning/volants/index.php (JS : js/volants.php)
*/

session_start();

require_once __DIR__.'/../../include/config.php';
require_once 'class.volants.php';


$CSRFToken = filter_input(INPUT_POST, 'CSRFToken', FILTER_SANITIZE_STRING);
$date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
$ids = filter_input(INPUT_POST, 'ids', FILTER_SANITIZE_STRING);

$ids = html_entity_decode($ids, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
$ids = json_decode($ids, true);

$v = new volants();
$v->set($date, $ids, $CSRFToken);

if ($v->error) {
    echo json_encode(array('error' => $v->error));
} else {
    echo json_encode('ok');
}

<?php
/**
Planno
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

Création : 23 juillet 2013
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier executant des tâches planifiées quotidiennement pour le module planningHebdo.
*/

require_once(__DIR__ . '/../../../public/planningHebdo/class.planningHebdo.php');
require_once(__DIR__ . '/../../../public/include/db.php');

$CSRFSession = $GLOBALS['CSRFSession'];

$p=new planningHebdo();
$p->debut=date("Y-m-d");
$p->valide=true;
$p->ignoreActuels=true;
$p->fetch();
foreach ($p->elements as $elem) {
    $id=$elem['id'];
    $perso_id=$elem['perso_id'];
    $db=new db();
    $db->CSRFToken = $CSRFSession;
    $db->update('planning_hebdo', array('actuel'=>0), array('perso_id'=>$perso_id));
    $db=new db();
    $db->CSRFToken = $CSRFSession;
    $db->update('planning_hebdo', array('actuel'=>1), array('id'=>$id));
}

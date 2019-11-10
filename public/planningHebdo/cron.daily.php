<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planningHebdo/cron.daily.php
Création : 23 juillet 2013
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier executant des taches planifiées quotidiennement pour le module planningHebdo.
Page appelée par le fichier include/cron.php
*/

require_once "class.planningHebdo.php";

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

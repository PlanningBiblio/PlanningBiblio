<?php
/**
Planning Biblio, Version 2.6.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : plugins/planningHebdo/cron.daily.php
Création : 23 juillet 2013
Dernière modification : 21 avril 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier executant des taches planifiées quotidiennement pour le plugin planningHebdo.
Page appelée par le fichier include/cron.php
*/

require_once "class.planningHebdo.php";

$p=new planningHebdo();
$p->debut=date("Y-m-d");
$p->valide=true;
$p->ignoreActuels=true;
$p->fetch();
foreach($p->elements as $elem){
  $id=$elem['id'];
  $perso_id=$elem['perso_id'];
  $db=new db();
  $db->update("planning_hebdo","`actuel`='0'","`perso_id`='$perso_id'");
  $db=new db();
  $db->update("planning_hebdo","`actuel`='1'","`id`='$id'");
}

?>
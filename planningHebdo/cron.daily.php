<?php
/**
Planning Biblio, Version 2.5.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : plugins/planningHebdo/cron.daily.php
Création : 23 juillet 2013
Dernière modification : 19 novembre 2016
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
  $db->update("planningHebdo","`actuel`='0'","`perso_id`='$perso_id'");
  $db=new db();
  $db->update("planningHebdo","`actuel`='1'","`id`='$id'");
}

?>
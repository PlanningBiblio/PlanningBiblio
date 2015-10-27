<?php
/*
Planning Biblio, Plugin planningHebdo Version 1.4.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2013-2015 - Jérôme Combes

Fichier : plugins/planningHebdo/cron.daily.php
Création : 23 juillet 2013
Dernière modification : 22 avril 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

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
  $temps=serialize($elem['temps']);
  $db=new db();
  $db->update("personnel","`temps`='$temps'","`id`='$perso_id'");
  $db=new db();
  $db->update("planningHebdo","`actuel`='0'","`perso_id`='$perso_id'");
  $db=new db();
  $db->update("planningHebdo","`actuel`='1'","`id`='$id'");
}

?>
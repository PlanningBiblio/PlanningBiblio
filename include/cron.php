<?php
/*
Planning Biblio, Version 1.7.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : include/cron.php
Création : 23 juillet 2013
Dernière modification : 24 juillet 2013
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Script executant les taches planifiées enregistrées dans la table cron
A améliorer :  pour le moment, chaque ligne est executée à la première connexion de la journée, 
  sans vérifier les paramétres heures, minutes, dom, mon et dow

Page appelée par le fichier index.php
*/

// Dates
$dateCron=date('Y-m-d');
$dom=date("j");
$mon=date("n");

// Daily Cron
$dbCron=new db();
$dbCron->select("cron","*","dom='*' and mon='*' and dow='*' and last<'$dateCron'");
if($dbCron->result){
  foreach($dbCron->result as $elemCron){
    include $elemCron['command'];
    $dbCron2=new db();
    $dbCron2->update("cron","`last`=SYSDATE()","`id`='{$elemCron['id']}'");
  }
}

// Yearly Cron
$dbCron=new db();
$dbCron->select("cron","*","dom='$dom' AND mon='$mon' and last<'$dateCron'");
if($dbCron->result){
  foreach($dbCron->result as $elemCron){
    include $elemCron['command'];
    $dbCron2=new db();
    $dbCron2->update("cron","`last`=SYSDATE()","`id`='{$elemCron['id']}'");
  }
}

?>
<?php
/**
Planning Biblio, Version 2.7.05
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : include/cron.php
Création : 23 juillet 2013
Dernière modification : 28 novembre 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

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
    $dbCron2->CSRFToken = $CSRFSession;
    $dbCron2->update('cron', array('last'=>'SYSDATE'), array('id'=>$elemCron['id']));
  }
}

// Absences : Met à jour la table absences avec les événements récurrents sans date de fin (J + 2ans)
// 1 fois par jour
require_once __DIR__."/../absences/class.absences.php";
$a = new absences();
$a->CSRFToken = $CSRFSession;
$a->ics_update_table();


// Yearly Cron
$dbCron=new db();
$dbCron->select("cron","*","dom='$dom' AND mon='$mon' and last<'$dateCron'");
if($dbCron->result){
  foreach($dbCron->result as $elemCron){
    include $elemCron['command'];
    $dbCron2=new db();
    $dbCron2->CSRFToken = $CSRFSession;
    $dbCron2->update('cron', array('last'=>'SYSDATE'), array('id'=>$elemCron['id']));
  }
}

?>